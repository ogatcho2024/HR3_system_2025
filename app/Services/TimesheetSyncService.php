<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Timesheet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TimesheetSyncService
{
    private function getStatusColumn(): string
    {
        return Schema::hasColumn('timesheets', 'Status') ? 'Status' : 'status';
    }

    public function syncFromAttendance(Attendance $attendance): ?Timesheet
    {
        if (empty($attendance->clock_in_time) || empty($attendance->clock_out_time)) {
            return null;
        }

        $hoursWorked = Attendance::calculateHoursFromTimes(
            $attendance->clock_in_time,
            $attendance->clock_out_time,
            $attendance->break_start,
            $attendance->break_end
        );

        if ($hoursWorked <= 0) {
            Log::warning('[TimesheetSync] Invalid hours computed', [
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'date' => $attendance->date,
                'clock_in' => $attendance->clock_in_time,
                'clock_out' => $attendance->clock_out_time,
            ]);
            return null;
        }

        $statusColumn = $this->getStatusColumn();
        $timesheet = Timesheet::where('user_id', $attendance->user_id)
            ->whereDate('work_date', $attendance->date)
            ->first();

        if ($timesheet && in_array(strtolower((string) $timesheet->$statusColumn), ['approved', 'rejected'], true)) {
            return $timesheet;
        }

        if (!$timesheet) {
            $timesheet = new Timesheet();
            $timesheet->user_id = $attendance->user_id;
            $timesheet->work_date = $attendance->date;
        }

        $timesheet->clock_in_time = $attendance->clock_in_time;
        $timesheet->clock_out_time = $attendance->clock_out_time;
        $timesheet->break_start = $attendance->break_start;
        $timesheet->break_end = $attendance->break_end;
        $timesheet->hours_worked = $hoursWorked;
        $timesheet->overtime_hours = max(0, $hoursWorked - 8.0);
        $timesheet->project_name = $timesheet->project_name ?: 'General Work';
        $timesheet->work_description = $timesheet->work_description ?: 'Daily work activities';

        $timesheet->$statusColumn = 'submitted';
        if (empty($timesheet->submitted_at)) {
            $timesheet->submitted_at = now();
        }

        $timesheet->save();

        return $timesheet;
    }
}
