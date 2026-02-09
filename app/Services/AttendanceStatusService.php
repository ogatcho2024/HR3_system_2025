<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\ShiftAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceStatusService
{
    private int $graceMinutes;

    public function __construct(?int $graceMinutes = null)
    {
        $this->graceMinutes = $graceMinutes ?? (int) env('ATTENDANCE_GRACE_MINUTES', 15);
    }

    private function normalizeTimeValue(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (preg_match('/\d{4}-\d{2}-\d{2}/', $value)) {
            try {
                return Carbon::parse($value)->format('H:i:s');
            } catch (\Exception $e) {
                return null;
            }
        }

        return $value;
    }

    public function resolveForEmployee(Employee $employee, Carbon $date, ?Carbon $now = null): array
    {
        $now = $now ?? Carbon::now();

        $assignment = $this->getAssignmentForDate($employee, $date);
        $hasAssignment = $assignment !== null;

        $attendance = Attendance::where('user_id', $employee->user_id)
            ->whereDate('date', $date)
            ->first();

        $hasTimeIn = (bool) ($attendance && $attendance->clock_in_time);

        $onLeave = LeaveRequest::where('user_id', $employee->user_id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();

        $status = 'no_schedule';
        $isLate = false;

        if (!$hasAssignment) {
            $status = 'no_schedule';
        } elseif ($onLeave) {
            $status = 'on_leave';
        } elseif ($attendance && $attendance->status === 'on_break') {
            $status = 'break';
        } elseif ($hasTimeIn) {
            $status = $this->determineTimeInStatus($attendance->clock_in_time, $assignment, $date, $isLate);
        } else {
            $status = $this->determineNoTimeInStatus($assignment, $date, $now);
        }

        if (in_array($status, ['absent', 'no_schedule'], true)) {
            Log::info('[AttendanceStatus] computed', [
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id,
                'date' => $date->toDateString(),
                'has_assignment' => $hasAssignment,
                'has_time_in' => $hasTimeIn,
                'status' => $status,
            ]);
        }

        return [
            'status' => $status,
            'is_late' => $isLate,
            'attendance' => $attendance,
            'assignment' => $assignment,
            'has_assignment' => $hasAssignment,
            'has_time_in' => $hasTimeIn,
        ];
    }

    private function getAssignmentForDate(Employee $employee, Carbon $date): ?ShiftAssignment
    {
        $assignments = ShiftAssignment::with('shiftTemplate')
            ->where('employee_id', $employee->id)
            ->whereDate('assignment_date', $date)
            ->get();

        if ($assignments->isEmpty()) {
            return null;
        }

        return $assignments->sortBy(function ($assignment) {
            return $assignment->shiftTemplate->start_time ?? '23:59:59';
        })->first();
    }

    private function determineTimeInStatus(string $clockInTime, ShiftAssignment $assignment, Carbon $date, bool &$isLate): string
    {
        if (!$assignment->shiftTemplate || !$assignment->shiftTemplate->start_time) {
            return 'present';
        }

        $shiftStartTime = $this->normalizeTimeValue($assignment->shiftTemplate->start_time);
        if (!$shiftStartTime) {
            return 'present';
        }
        $shiftStart = Carbon::parse($date->toDateString() . ' ' . $shiftStartTime);
        $graceDeadline = $shiftStart->copy()->addMinutes($this->graceMinutes);
        $normalizedClockIn = $this->normalizeTimeValue($clockInTime) ?? $clockInTime;
        $clockInAt = Carbon::parse($date->toDateString() . ' ' . $normalizedClockIn);

        if ($clockInAt->lessThanOrEqualTo($graceDeadline)) {
            $isLate = false;
            return 'present';
        }

        $isLate = true;
        return 'late';
    }

    private function determineNoTimeInStatus(ShiftAssignment $assignment, Carbon $date, Carbon $now): string
    {
        if (!$assignment->shiftTemplate || !$assignment->shiftTemplate->start_time) {
            return 'scheduled';
        }

        $shiftStartTime = $this->normalizeTimeValue($assignment->shiftTemplate->start_time);
        if (!$shiftStartTime) {
            return 'scheduled';
        }
        $shiftStart = Carbon::parse($date->toDateString() . ' ' . $shiftStartTime);
        $graceDeadline = $shiftStart->copy()->addMinutes($this->graceMinutes);

        if ($date->isFuture()) {
            return 'scheduled';
        }

        if ($date->isToday() && $now->lessThanOrEqualTo($graceDeadline)) {
            return 'scheduled';
        }

        return 'absent';
    }
}
