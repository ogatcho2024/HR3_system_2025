<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\ShiftRequest;
use App\Models\Timesheet;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Display the main reports dashboard
     */
    public function index(): View
    {
        $today = Carbon::now()->toDateString();
        $thisMonth = Carbon::now();
        
        // Get summary statistics for the dashboard
        $stats = [
            'total_employees' => Employee::active()->count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'pending_leave_requests' => LeaveRequest::where('status', 'pending')->count(),
            'pending_shift_requests' => ShiftRequest::where('status', 'pending')->count(),
            'total_attendance_records' => Attendance::count(),
            'present_today' => Attendance::where('date', $today)->where('status', 'present')->count(),
            'late_today' => Attendance::where('date', $today)->where('status', 'late')->count(),
            'absent_today' => Employee::active()->count() - Attendance::where('date', $today)->count(),
            'attendance_rate_today' => $this->calculateDailyAttendanceRate($today),
            'total_hours_this_month' => Attendance::whereMonth('date', $thisMonth->month)
                ->whereYear('date', $thisMonth->year)
                ->sum('hours_worked'),
        ];

        return view('reports.index', compact('stats'));
    }

    /**
     * Employee Reports
     */
    public function employeeReport(Request $request): View
    {
        $query = Employee::with('user');

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by employment type
        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get filter options from Department model and existing data
        $departments = Department::active()->pluck('department_name')->merge(
            Employee::distinct()->pluck('department')->filter()
        )->unique()->sort()->values();
        $employmentTypes = Employee::distinct()->pluck('employment_type')->filter();

        return view('reports.employees', compact('employees', 'departments', 'employmentTypes'));
    }

    /**
     * Leave Reports
     */
    public function leaveReport(Request $request): View
    {
        $query = LeaveRequest::with(['user']);

        // Date range filter
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Leave type filter
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $stats = [
            'total_requests' => LeaveRequest::count(),
            'approved_requests' => LeaveRequest::where('status', 'approved')->count(),
            'pending_requests' => LeaveRequest::where('status', 'pending')->count(),
            'rejected_requests' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        // Get leave types for filter
        $leaveTypes = LeaveRequest::distinct()->pluck('leave_type')->filter();

        return view('reports.leave', compact('leaveRequests', 'stats', 'leaveTypes'));
    }

    /**
     * Attendance Reports - Real data from attendance table
     */
    public function attendanceReport(Request $request): View
    {
        $query = Attendance::with(['user.employee']);

        // Date range filter
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Department filter
        if ($request->filled('department')) {
            $query->whereHas('user.employee', function ($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(20);

        // Get comprehensive attendance statistics
        $today = Carbon::now()->toDateString();
        $thisMonth = Carbon::now();
        
        $stats = [
            'total_records' => Attendance::count(),
            'present_today' => Attendance::where('date', $today)->where('status', 'present')->count(),
            'late_today' => Attendance::where('date', $today)->where('status', 'late')->count(),
            'absent_today' => Employee::active()->count() - Attendance::where('date', $today)->count(),
            'on_break_today' => Attendance::where('date', $today)->where('status', 'on_break')->count(),
            'total_hours_this_month' => Attendance::whereMonth('date', $thisMonth->month)
                ->whereYear('date', $thisMonth->year)
                ->sum('hours_worked'),
            'avg_hours_per_day' => Attendance::whereMonth('date', $thisMonth->month)
                ->whereYear('date', $thisMonth->year)
                ->whereNotNull('hours_worked')
                ->avg('hours_worked'),
            'attendance_rate_this_month' => $this->calculateAttendanceRate($thisMonth),
            'overtime_hours_this_month' => Attendance::whereMonth('date', $thisMonth->month)
                ->whereYear('date', $thisMonth->year)
                ->sum('overtime_hours'),
            'perfect_attendance_employees' => $this->getPerfectAttendanceEmployees($thisMonth),
        ];

        // Get departments for filter from Department model and existing data
        $departments = Department::active()->pluck('department_name')->merge(
            Employee::distinct()->pluck('department')->filter()
        )->unique()->sort()->values();

        // Get attendance trends for the last 7 days
        $attendanceTrends = $this->getAttendanceTrends();
        
        // Get departmental breakdown
        $departmentalStats = $this->getDepartmentalAttendanceStats($thisMonth);
        
        // Get productivity metrics
        $productivityMetrics = $this->getProductivityMetrics($thisMonth);

        return view('reports.attendance', compact(
            'attendances', 
            'stats', 
            'departments', 
            'attendanceTrends',
            'departmentalStats',
            'productivityMetrics'
        ));
    }

    /**
     * Department Summary Report
     */
    public function departmentReport(): View
    {
        $departmentStats = Employee::selectRaw('
            department,
            COUNT(*) as total_employees,
            AVG(salary) as avg_salary,
            COUNT(CASE WHEN status = "active" THEN 1 END) as active_employees,
            COUNT(CASE WHEN status = "inactive" THEN 1 END) as inactive_employees
        ')
        ->groupBy('department')
        ->orderBy('total_employees', 'desc')
        ->get();

        return view('reports.departments', compact('departmentStats'));
    }

    /**
     * Monthly Summary Report
     */
    public function monthlyReport(Request $request): View
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $carbonMonth = Carbon::createFromFormat('Y-m', $month);

        $stats = [
            'new_employees' => Employee::whereMonth('hire_date', $carbonMonth->month)
                ->whereYear('hire_date', $carbonMonth->year)
                ->count(),
            
            'leave_requests' => LeaveRequest::whereMonth('created_at', $carbonMonth->month)
                ->whereYear('created_at', $carbonMonth->year)
                ->count(),
                
            'approved_leaves' => LeaveRequest::where('status', 'approved')
                ->whereMonth('created_at', $carbonMonth->month)
                ->whereYear('created_at', $carbonMonth->year)
                ->count(),
                
            'timesheets_submitted' => Timesheet::where('status', 'submitted')
                ->whereMonth('work_date', $carbonMonth->month)
                ->whereYear('work_date', $carbonMonth->year)
                ->count(),
        ];

        // Get monthly trends (last 12 months)
        $monthlyTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $trendMonth = Carbon::now()->subMonths($i);
            $monthlyTrends[] = [
                'month' => $trendMonth->format('Y-m'),
                'month_name' => $trendMonth->format('M Y'),
                'employees' => Employee::whereMonth('hire_date', $trendMonth->month)
                    ->whereYear('hire_date', $trendMonth->year)
                    ->count(),
                'leave_requests' => LeaveRequest::whereMonth('created_at', $trendMonth->month)
                    ->whereYear('created_at', $trendMonth->year)
                    ->count(),
            ];
        }

        return view('reports.monthly', compact('stats', 'monthlyTrends', 'month'));
    }

    /**
     * Calculate attendance rate for a given month
     */
    private function calculateAttendanceRate(Carbon $month): float
    {
        $totalEmployees = Employee::active()->count();
        
        if ($totalEmployees === 0) {
            return 0;
        }

        // Get working days in the month (exclude weekends)
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();
        $workingDays = 0;
        
        $current = $startOfMonth->copy();
        while ($current->lte($endOfMonth)) {
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        $expectedAttendanceRecords = $totalEmployees * $workingDays;
        $actualAttendanceRecords = Attendance::whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->whereIn('status', ['present', 'late'])
            ->count();
        
        return $expectedAttendanceRecords > 0 ? round(($actualAttendanceRecords / $expectedAttendanceRecords) * 100, 1) : 0;
    }

    /**
     * Get attendance trends for the last 7 days
     */
    private function getAttendanceTrends(): array
    {
        $trends = [];
        $totalEmployees = Employee::active()->count();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->toDateString();
            
            // Skip weekends for attendance calculations
            if (!$date->isWeekend()) {
                $presentCount = Attendance::where('date', $dateString)->where('status', 'present')->count();
                $lateCount = Attendance::where('date', $dateString)->where('status', 'late')->count();
                $attendanceRate = $totalEmployees > 0 ? round((($presentCount + $lateCount) / $totalEmployees) * 100, 1) : 0;
                
                $trends[] = [
                    'date' => $dateString,
                    'day_name' => $date->format('D'),
                    'formatted_date' => $date->format('M d'),
                    'present' => $presentCount,
                    'late' => $lateCount,
                    'absent' => $totalEmployees - Attendance::where('date', $dateString)->count(),
                    'attendance_rate' => $attendanceRate
                ];
            }
        }
        
        return $trends;
    }

    /**
     * Calculate attendance rate for a specific day
     */
    private function calculateDailyAttendanceRate(string $date): float
    {
        $totalEmployees = Employee::active()->count();
        
        if ($totalEmployees === 0) {
            return 0;
        }

        $presentCount = Attendance::where('date', $date)->where('status', 'present')->count();
        $lateCount = Attendance::where('date', $date)->where('status', 'late')->count();
        
        return round((($presentCount + $lateCount) / $totalEmployees) * 100, 1);
    }
    
    /**
     * Get employees with perfect attendance for the month
     */
    private function getPerfectAttendanceEmployees(Carbon $month): int
    {
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();
        
        // Count working days in the month
        $workingDays = 0;
        $current = $startOfMonth->copy();
        while ($current->lte($endOfMonth)) {
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        // Get employees who have perfect attendance (present on all working days)
        $perfectEmployees = Employee::active()
            ->with('user')
            ->get()
            ->filter(function ($employee) use ($month, $workingDays) {
                $attendanceCount = $employee->user->attendances()
                    ->whereMonth('date', $month->month)
                    ->whereYear('date', $month->year)
                    ->where('status', 'present')
                    ->count();
                    
                return $attendanceCount >= $workingDays;
            });
            
        return $perfectEmployees->count();
    }
    
    /**
     * Get departmental attendance statistics
     */
    private function getDepartmentalAttendanceStats(Carbon $month): array
    {
        $departments = Employee::distinct()->pluck('department')->filter();
        $stats = [];
        
        foreach ($departments as $department) {
            $departmentEmployees = Employee::where('department', $department)
                ->where('status', 'active')
                ->count();
                
            $presentRecords = Attendance::whereHas('user.employee', function ($q) use ($department) {
                    $q->where('department', $department);
                })
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->whereIn('status', ['present', 'late'])
                ->count();
                
            $totalHours = Attendance::whereHas('user.employee', function ($q) use ($department) {
                    $q->where('department', $department);
                })
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->sum('hours_worked');
                
            $avgHours = $departmentEmployees > 0 && $totalHours > 0 ? round($totalHours / $departmentEmployees, 2) : 0;
            
            $stats[] = [
                'department' => $department,
                'employees' => $departmentEmployees,
                'present_records' => $presentRecords,
                'total_hours' => $totalHours,
                'avg_hours_per_employee' => $avgHours,
                'attendance_rate' => $this->calculateDepartmentAttendanceRate($department, $month)
            ];
        }
        
        return $stats;
    }
    
    /**
     * Calculate attendance rate for a specific department
     */
    private function calculateDepartmentAttendanceRate(string $department, Carbon $month): float
    {
        $departmentEmployees = Employee::where('department', $department)
            ->where('status', 'active')
            ->count();
            
        if ($departmentEmployees === 0) {
            return 0;
        }
        
        // Get working days in the month
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();
        $workingDays = 0;
        
        $current = $startOfMonth->copy();
        while ($current->lte($endOfMonth)) {
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        $expectedRecords = $departmentEmployees * $workingDays;
        $actualRecords = Attendance::whereHas('user.employee', function ($q) use ($department) {
                $q->where('department', $department);
            })
            ->whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->whereIn('status', ['present', 'late'])
            ->count();
            
        return $expectedRecords > 0 ? round(($actualRecords / $expectedRecords) * 100, 1) : 0;
    }
    
    /**
     * Get productivity metrics
     */
    private function getProductivityMetrics(Carbon $month): array
    {
        $totalHours = Attendance::whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->sum('hours_worked');
            
        $overtimeHours = Attendance::whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->sum('overtime_hours');
            
        // SQLite-compatible way to calculate average times
        $attendanceRecords = Attendance::whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->whereNotNull('clock_in_time')
            ->whereNotNull('clock_out_time')
            ->get(['clock_in_time', 'clock_out_time']);
            
        $avgClockInSeconds = null;
        $avgClockOutSeconds = null;
        
        if ($attendanceRecords->count() > 0) {
            $clockInTimes = $attendanceRecords->pluck('clock_in_time')
                ->filter()
                ->map(function ($time) {
                    if (empty($time)) return 0;
                    $parts = explode(':', $time);
                    if (count($parts) < 2) return 0;
                    $hours = intval($parts[0] ?? 0);
                    $minutes = intval($parts[1] ?? 0);
                    $seconds = intval($parts[2] ?? 0);
                    return ($hours * 3600) + ($minutes * 60) + $seconds;
                })
                ->filter(function ($seconds) {
                    return $seconds > 0;
                });
                
            $clockOutTimes = $attendanceRecords->pluck('clock_out_time')
                ->filter()
                ->map(function ($time) {
                    if (empty($time)) return 0;
                    $parts = explode(':', $time);
                    if (count($parts) < 2) return 0;
                    $hours = intval($parts[0] ?? 0);
                    $minutes = intval($parts[1] ?? 0);
                    $seconds = intval($parts[2] ?? 0);
                    return ($hours * 3600) + ($minutes * 60) + $seconds;
                })
                ->filter(function ($seconds) {
                    return $seconds > 0;
                });
                
            $avgClockInSeconds = $clockInTimes->count() > 0 ? $clockInTimes->avg() : null;
            $avgClockOutSeconds = $clockOutTimes->count() > 0 ? $clockOutTimes->avg() : null;
        }
            
        return [
            'total_hours' => round($totalHours, 2),
            'overtime_hours' => round($overtimeHours, 2),
            'avg_clock_in' => $avgClockInSeconds ? 
                gmdate('H:i', $avgClockInSeconds) : '08:00',
            'avg_clock_out' => $avgClockOutSeconds ? 
                gmdate('H:i', $avgClockOutSeconds) : '17:00',
            'productivity_score' => $this->calculateProductivityScore($month)
        ];
    }
    
    /**
     * Calculate productivity score based on various factors
     */
    private function calculateProductivityScore(Carbon $month): float
    {
        $attendanceRate = $this->calculateAttendanceRate($month);
        $punctualityRate = $this->calculatePunctualityRate($month);
        $overtimeUtilization = $this->calculateOvertimeUtilization($month);
        
        // Weighted score: 50% attendance, 30% punctuality, 20% overtime management
        $score = ($attendanceRate * 0.5) + ($punctualityRate * 0.3) + ($overtimeUtilization * 0.2);
        
        return round($score, 1);
    }
    
    /**
     * Calculate punctuality rate (on-time vs late arrivals)
     */
    private function calculatePunctualityRate(Carbon $month): float
    {
        $totalAttendance = Attendance::whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->whereIn('status', ['present', 'late'])
            ->count();
            
        if ($totalAttendance === 0) {
            return 0;
        }
        
        $onTimeAttendance = Attendance::whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->where('status', 'present')
            ->count();
            
        return round(($onTimeAttendance / $totalAttendance) * 100, 1);
    }
    
    /**
     * Calculate overtime utilization efficiency
     */
    private function calculateOvertimeUtilization(Carbon $month): float
    {
        $totalOvertimeHours = Attendance::whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->sum('overtime_hours');
            
        $totalRegularHours = Attendance::whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->sum('hours_worked');
            
        // Ensure we're comparing as float and handle zero division case
        if (floatval($totalRegularHours) <= 0) {
            return 100; // No overtime is good
        }
        
        $overtimePercentage = ($totalOvertimeHours / $totalRegularHours) * 100;
        
        // Ideal is low overtime (under 10%), so invert the score
        return $overtimePercentage <= 10 ? 100 : max(0, 100 - ($overtimePercentage - 10) * 2);
    }
    
    /**
     * Export attendance report to Excel/CSV
     */
    public function exportAttendance(Request $request)
    {
        $query = Attendance::with(['user.employee'])
            ->select('attendances.*')
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->join('employees', 'users.id', '=', 'employees.user_id');
            
        // Apply filters
        if ($request->filled('start_date')) {
            $query->where('attendances.date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('attendances.date', '<=', $request->end_date);
        }
        
        if ($request->filled('status')) {
            $query->where('attendances.status', $request->status);
        }
        
        if ($request->filled('department')) {
            $query->where('employees.department', $request->department);
        }
        
        $attendances = $query->orderBy('attendances.date', 'desc')->get();
        
        // Generate CSV content
        $csvContent = "Employee Name,Email,Department,Date,Status,Clock In,Clock Out,Hours Worked,Overtime Hours,Notes\n";
        
        foreach ($attendances as $attendance) {
            $employee = $attendance->user->employee;
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,\"%s\"\n",
                $employee->first_name . ' ' . $employee->last_name,
                $attendance->user->email,
                $employee->department ?? 'N/A',
                $attendance->date,
                ucfirst($attendance->status),
                $attendance->clock_in_time ?? 'N/A',
                $attendance->clock_out_time ?? 'N/A',
                $attendance->hours_worked ?? '0',
                $attendance->overtime_hours ?? '0',
                $attendance->notes ?? ''
            );
        }
        
        $filename = 'attendance_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
