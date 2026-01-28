<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\QrAttendanceLog;
use Carbon\Carbon;

class QrAttendanceService
{
    /**
     * Cooldown period in minutes between scans.
     */
    const COOLDOWN_MINUTES = 5;

    /**
     * Maximum number of logs allowed per day.
     */
    const MAX_LOGS_PER_DAY = 2;

    /**
     * Check if employee is in cooldown period.
     * 
     * @param int $employeeId
     * @return int Remaining seconds in cooldown (0 if no cooldown)
     */
    public function checkCooldown(int $employeeId): int
    {
        $lastLog = QrAttendanceLog::forEmployee($employeeId)
            ->orderBy('scanned_at', 'desc')
            ->first();

        if (!$lastLog) {
            return 0;
        }

        $cooldownUntil = $lastLog->scanned_at->addMinutes(self::COOLDOWN_MINUTES);
        $now = Carbon::now();

        if ($now->lt($cooldownUntil)) {
            return $now->diffInSeconds($cooldownUntil);
        }

        return 0;
    }

    /**
     * Check if employee can log attendance.
     * 
     * @param int $employeeId
     * @return array ['allowed' => bool, 'reason' => string]
     */
    public function canLogAttendance(int $employeeId): array
    {
        // Check cooldown
        $cooldownSeconds = $this->checkCooldown($employeeId);
        if ($cooldownSeconds > 0) {
            $minutes = ceil($cooldownSeconds / 60);
            return [
                'allowed' => false,
                'reason' => "Please wait {$minutes} minute(s) before scanning again.",
            ];
        }

        // Check max logs for today
        $todayLogsCount = QrAttendanceLog::forEmployee($employeeId)
            ->today()
            ->count();

        if ($todayLogsCount >= self::MAX_LOGS_PER_DAY) {
            return [
                'allowed' => false,
                'reason' => 'Maximum attendance logs reached for today (2 entries: IN and OUT).',
            ];
        }

        return [
            'allowed' => true,
            'reason' => '',
        ];
    }

    /**
     * Determine the next log type (IN or OUT) for an employee.
     * 
     * @param int $employeeId
     * @return string|null 'IN', 'OUT', or null if max logs reached
     */
    public function getNextLogType(int $employeeId): ?string
    {
        $todayLogs = QrAttendanceLog::forEmployee($employeeId)
            ->today()
            ->orderBy('scanned_at', 'asc')
            ->get();

        $logsCount = $todayLogs->count();

        if ($logsCount === 0) {
            return 'IN';
        }

        if ($logsCount === 1) {
            $lastLog = $todayLogs->first();
            if ($lastLog->log_type === 'IN') {
                return 'OUT';
            } else {
                // If somehow first log is OUT, don't allow more logs
                return null;
            }
        }

        // Already has 2 logs
        return null;
    }

    /**
     * Get today's logs for an employee.
     * 
     * @param int $employeeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTodayLogs(int $employeeId)
    {
        return QrAttendanceLog::forEmployee($employeeId)
            ->today()
            ->orderBy('scanned_at', 'asc')
            ->get();
    }

    /**
     * Get attendance statistics for an employee.
     * 
     * @param int $employeeId
     * @param string|null $date
     * @return array
     */
    public function getAttendanceStats(int $employeeId, ?string $date = null): array
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();

        $logs = QrAttendanceLog::forEmployee($employeeId)
            ->onDate($targetDate)
            ->orderBy('scanned_at', 'asc')
            ->get();

        $stats = [
            'date' => $targetDate->format('Y-m-d'),
            'total_logs' => $logs->count(),
            'time_in' => null,
            'time_out' => null,
            'status' => 'No Record',
        ];

        if ($logs->count() > 0) {
            $timeInLog = $logs->where('log_type', 'IN')->first();
            $timeOutLog = $logs->where('log_type', 'OUT')->first();

            if ($timeInLog) {
                $stats['time_in'] = $timeInLog->scanned_at->format('H:i:s');
                $stats['status'] = 'Present';
            }

            if ($timeOutLog) {
                $stats['time_out'] = $timeOutLog->scanned_at->format('H:i:s');
            }
        }

        return $stats;
    }
}
