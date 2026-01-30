<?php

namespace App\Jobs;

use App\Models\Timesheet;
use App\Services\TimesheetSenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendTimesheetToPayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [30, 60, 120, 300, 600]; // 30s, 1m, 2m, 5m, 10m

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 5;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * The timesheet ID to send.
     *
     * @var int
     */
    protected $timesheetId;

    /**
     * Create a new job instance.
     *
     * @param int $timesheetId
     */
    public function __construct(int $timesheetId)
    {
        $this->timesheetId = $timesheetId;
    }

    /**
     * Execute the job.
     *
     * @param TimesheetSenderService $senderService
     * @return void
     * @throws Exception
     */
    public function handle(TimesheetSenderService $senderService): void
    {
        Log::info('[PayrollSync] Processing timesheet for payroll sync', [
            'timesheet_id' => $this->timesheetId,
            'attempt' => $this->attempts(),
        ]);

        // Load timesheet
        $timesheet = Timesheet::find($this->timesheetId);

        if (!$timesheet) {
            Log::error('[PayrollSync] Timesheet not found', [
                'timesheet_id' => $this->timesheetId,
            ]);
            return; // Don't retry if timesheet doesn't exist
        }

        // IDEMPOTENCY CHECK: Don't send if already sent
        if ($timesheet->sent_to_payroll) {
            Log::info('[PayrollSync] Timesheet already sent to payroll, skipping', [
                'timesheet_id' => $this->timesheetId,
                'payroll_sent_at' => $timesheet->payroll_sent_at,
            ]);
            return;
        }

        // SAFETY CHECK: Only send approved timesheets
        if ($timesheet->status !== 'approved') {
            Log::warning('[PayrollSync] Timesheet is not approved, skipping', [
                'timesheet_id' => $this->timesheetId,
                'status' => $timesheet->status,
            ]);
            return;
        }

        // Increment attempt counter BEFORE sending
        $timesheet->increment('payroll_send_attempts');

        Log::info('[PayrollSync] Sending timesheet to payroll system', [
            'timesheet_id' => $this->timesheetId,
            'attempt' => $timesheet->payroll_send_attempts,
        ]);

        try {
            // Call the sender service
            $result = $senderService->sendToPayroll($timesheet);

            // Check if send was successful
            if ($result['success']) {
                // SUCCESS: Mark as sent
                $timesheet->update([
                    'sent_to_payroll' => true,
                    'payroll_sent_at' => now(),
                    'payroll_last_error' => null, // Clear any previous errors
                ]);

                Log::info('[PayrollSync] ✓ Timesheet successfully sent to payroll', [
                    'timesheet_id' => $this->timesheetId,
                    'payroll_sent_at' => $timesheet->payroll_sent_at,
                    'total_attempts' => $timesheet->payroll_send_attempts,
                    'payroll_response' => $result['payroll_response'] ?? null,
                ]);
            } else {
                // FAILURE: Store error and throw exception to trigger retry
                $errorMessage = $result['message'] ?? 'Unknown error';
                $errorDetails = $result['error'] ?? '';

                $timesheet->update([
                    'payroll_last_error' => json_encode([
                        'message' => $errorMessage,
                        'details' => $errorDetails,
                        'status_code' => $result['status_code'] ?? null,
                        'attempted_at' => now()->toIso8601String(),
                        'attempt_number' => $timesheet->payroll_send_attempts,
                    ]),
                ]);

                Log::error('[PayrollSync] ✗ Failed to send timesheet to payroll', [
                    'timesheet_id' => $this->timesheetId,
                    'error_message' => $errorMessage,
                    'status_code' => $result['status_code'] ?? null,
                    'attempt' => $timesheet->payroll_send_attempts,
                ]);

                // Throw exception to trigger Laravel's retry mechanism
                throw new Exception("Failed to send timesheet to payroll: {$errorMessage}");
            }
        } catch (Exception $e) {
            // Store exception details
            $timesheet->update([
                'payroll_last_error' => json_encode([
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'attempted_at' => now()->toIso8601String(),
                    'attempt_number' => $timesheet->payroll_send_attempts,
                ]),
            ]);

            Log::error('[PayrollSync] ✗ Exception while sending timesheet to payroll', [
                'timesheet_id' => $this->timesheetId,
                'exception' => $e->getMessage(),
                'exception_class' => get_class($e),
                'attempt' => $timesheet->payroll_send_attempts,
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception): void
    {
        Log::error('[PayrollSync] ✗✗✗ Job permanently failed after all retries', [
            'timesheet_id' => $this->timesheetId,
            'exception' => $exception->getMessage(),
            'total_attempts' => $this->attempts(),
        ]);

        // Load timesheet and mark failure
        $timesheet = Timesheet::find($this->timesheetId);
        if ($timesheet) {
            $timesheet->update([
                'payroll_last_error' => json_encode([
                    'message' => 'Permanently failed after ' . $this->attempts() . ' attempts',
                    'last_exception' => $exception->getMessage(),
                    'failed_at' => now()->toIso8601String(),
                ]),
            ]);
        }

        // TODO: Send notification to admin about failed payroll sync
        // You can implement: Mail::to('admin@example.com')->send(new PayrollSyncFailed($timesheet));
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return int
     */
    public function backoff(): array
    {
        return $this->backoff;
    }
}
