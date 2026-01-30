<?php

namespace App\Services;

use App\Models\Timesheet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TimesheetSenderService
{
    /**
     * Send timesheet to external payroll system.
     *
     * @param Timesheet $timesheet
     * @return array
     */
    public function sendToPayroll(Timesheet $timesheet): array
    {
        try {
            // Validate timesheet status
            if ($timesheet->status !== 'approved') {
                return [
                    'success' => false,
                    'status_code' => 400,
                    'message' => 'Only approved timesheets can be sent to payroll system.',
                    'timesheet_id' => $timesheet->id,
                ];
            }

            // Validate required fields
            $validation = $this->validateRequiredFields($timesheet);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'status_code' => 422,
                    'message' => 'Validation failed: ' . implode(', ', $validation['errors']),
                    'timesheet_id' => $timesheet->id,
                ];
            }

            // Prepare payload
            $payload = $this->preparePayload($timesheet);

            // Sign payload
            $signature = $this->signPayload($payload);

            // Send to external API
            $response = Http::timeout(config('payroll.timeout', 30))
                ->retry(config('payroll.retries', 3), config('payroll.retry_delay', 100))
                ->withHeaders([
                    'X-API-KEY' => config('payroll.api_key'),
                    'X-Signature' => $signature,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post(config('payroll.endpoint'), $payload);

            // Handle response
            if ($response->successful()) {
                Log::info('Timesheet successfully sent to payroll', [
                    'timesheet_id' => $timesheet->id,
                    'status_code' => $response->status(),
                    'response' => $response->json(),
                ]);

                return [
                    'success' => true,
                    'status_code' => $response->status(),
                    'message' => 'Timesheet successfully sent to payroll system.',
                    'timesheet_id' => $timesheet->id,
                    'payroll_response' => $response->json(),
                ];
            }

            // Handle failed response
            Log::error('Failed to send timesheet to payroll', [
                'timesheet_id' => $timesheet->id,
                'status_code' => $response->status(),
                'error' => $response->body(),
            ]);

            return [
                'success' => false,
                'status_code' => $response->status(),
                'message' => 'Failed to send timesheet to payroll system.',
                'timesheet_id' => $timesheet->id,
                'error' => $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Exception while sending timesheet to payroll', [
                'timesheet_id' => $timesheet->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'status_code' => 500,
                'message' => 'Exception occurred: ' . $e->getMessage(),
                'timesheet_id' => $timesheet->id,
            ];
        }
    }

    /**
     * Validate required fields.
     *
     * @param Timesheet $timesheet
     * @return array
     */
    protected function validateRequiredFields(Timesheet $timesheet): array
    {
        $errors = [];

        if (empty($timesheet->id)) {
            $errors[] = 'id is required';
        }

        if (empty($timesheet->user_id)) {
            $errors[] = 'user_id is required';
        }

        if (empty($timesheet->work_date)) {
            $errors[] = 'work_date is required';
        }

        if (empty($timesheet->clock_in_time)) {
            $errors[] = 'time_in (clock_in_time) is required';
        }

        if (empty($timesheet->clock_out_time)) {
            $errors[] = 'time_out (clock_out_time) is required';
        }

        if (empty($timesheet->hours_worked) && $timesheet->hours_worked !== 0) {
            $errors[] = 'total_hours (hours_worked) is required';
        }

        if (empty($timesheet->status)) {
            $errors[] = 'status is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Prepare payload for external API.
     *
     * @param Timesheet $timesheet
     * @return array
     */
    protected function preparePayload(Timesheet $timesheet): array
    {
        return [
            'id' => $timesheet->id,
            'user_id' => $timesheet->user_id,
            'work_date' => Carbon::parse($timesheet->work_date)->format('Y-m-d'),
            'time_in' => Carbon::parse($timesheet->clock_in_time)->format('H:i:s'),
            'time_out' => Carbon::parse($timesheet->clock_out_time)->format('H:i:s'),
            'total_hours' => (float) $timesheet->hours_worked,
            'overtime_hours' => (float) ($timesheet->overtime_hours ?? 0),
            'status' => $timesheet->status,
            'source_system' => config('payroll.source_system', 'HumanResources3'),
            'project_name' => $timesheet->project_name,
            'work_description' => $timesheet->work_description,
            'submitted_at' => $timesheet->submitted_at ? Carbon::parse($timesheet->submitted_at)->toIso8601String() : null,
            'approved_at' => $timesheet->approved_at ? Carbon::parse($timesheet->approved_at)->toIso8601String() : null,
            'approved_by' => $timesheet->approved_by,
            'timestamp' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Sign payload using HMAC-SHA256.
     *
     * @param array $payload
     * @return string
     */
    protected function signPayload(array $payload): string
    {
        $secret = config('payroll.api_secret');
        $data = json_encode($payload, JSON_UNESCAPED_SLASHES);
        return hash_hmac('sha256', $data, $secret);
    }

    /**
     * Send multiple timesheets in batch.
     *
     * @param \Illuminate\Support\Collection $timesheets
     * @return array
     */
    public function sendBatch($timesheets): array
    {
        $results = [
            'success_count' => 0,
            'failed_count' => 0,
            'results' => [],
        ];

        foreach ($timesheets as $timesheet) {
            $result = $this->sendToPayroll($timesheet);
            
            if ($result['success']) {
                $results['success_count']++;
            } else {
                $results['failed_count']++;
            }

            $results['results'][] = $result;
        }

        return $results;
    }
}
