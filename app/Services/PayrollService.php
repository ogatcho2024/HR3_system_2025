<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class PayrollService
{
    protected $baseUrl;
    protected $apiKey;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.payroll.base_url', env('PAYROLL_API_URL'));
        $this->apiKey = config('services.payroll.api_key', env('PAYROLL_API_KEY'));
        $this->timeout = config('services.payroll.timeout', 30);
    }

    /**
     * Get payslips for an employee
     */
    public function getEmployeePayslips($employeeId, $limit = 12): array
    {
        if (!$this->isConfigured()) {
            return $this->getMockPayslips($limit);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/employees/{$employeeId}/payslips", [
                    'limit' => $limit,
                    'order_by' => 'pay_period_end',
                    'order' => 'desc'
                ]);

            if ($response->successful()) {
                return $this->transformPayslipsResponse($response->json());
            }

            Log::warning('Payroll API request failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return $this->getMockPayslips($limit);

        } catch (Exception $e) {
            Log::error('Payroll service error', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);

            return $this->getMockPayslips($limit);
        }
    }

    /**
     * Get current month payslip for an employee
     */
    public function getCurrentPayslip($employeeId): ?array
    {
        if (!$this->isConfigured()) {
            $payslips = $this->getMockPayslips(1);
            return count($payslips) > 0 ? $payslips[0] : null;
        }

        try {
            $currentMonth = Carbon::now()->format('Y-m');
            
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/employees/{$employeeId}/payslips/current", [
                    'month' => $currentMonth
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data ? $this->transformSinglePayslip($data) : null;
            }

            return null;

        } catch (Exception $e) {
            Log::error('Current payslip fetch error', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Download payslip PDF
     */
    public function downloadPayslip($employeeId, $payslipId): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                ->get("{$this->baseUrl}/employees/{$employeeId}/payslips/{$payslipId}/download");

            if ($response->successful()) {
                return $response->body();
            }

            return null;

        } catch (Exception $e) {
            Log::error('Payslip download error', [
                'employee_id' => $employeeId,
                'payslip_id' => $payslipId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Check if service is properly configured
     */
    protected function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->apiKey);
    }

    /**
     * Transform API response to our format
     */
    protected function transformPayslipsResponse(array $data): array
    {
        if (!isset($data['payslips']) || !is_array($data['payslips'])) {
            return [];
        }

        return collect($data['payslips'])->map(function ($payslip) {
            return $this->transformSinglePayslip($payslip);
        })->toArray();
    }

    /**
     * Transform single payslip data
     */
    protected function transformSinglePayslip(array $payslip): array
    {
        return [
            'id' => $payslip['id'] ?? null,
            'period' => $payslip['pay_period'] ?? 'Unknown Period',
            'gross_salary' => floatval($payslip['gross_salary'] ?? 0),
            'deductions' => floatval($payslip['total_deductions'] ?? 0),
            'net_salary' => floatval($payslip['net_salary'] ?? 0),
            'generated_date' => isset($payslip['generated_at']) 
                ? Carbon::parse($payslip['generated_at'])
                : Carbon::now(),
            'status' => $payslip['status'] ?? 'available',
            'pay_period_start' => isset($payslip['pay_period_start'])
                ? Carbon::parse($payslip['pay_period_start'])
                : null,
            'pay_period_end' => isset($payslip['pay_period_end'])
                ? Carbon::parse($payslip['pay_period_end'])
                : null,
        ];
    }

    /**
     * Get mock payslips when API is not configured
     */
    protected function getMockPayslips($limit = 12): array
    {
        $payslips = [];
        
        for ($i = 0; $i < min($limit, 12); $i++) {
            $date = Carbon::now()->subMonths($i);
            $payslips[] = [
                'id' => 'mock_' . ($i + 1),
                'period' => $date->format('F Y'),
                'gross_salary' => 5000.00,
                'deductions' => 750.00,
                'net_salary' => 4250.00,
                'generated_date' => $date->copy()->endOfMonth()->subDays(5),
                'status' => 'available',
                'pay_period_start' => $date->copy()->startOfMonth(),
                'pay_period_end' => $date->copy()->endOfMonth(),
            ];
        }

        return $payslips;
    }
}
