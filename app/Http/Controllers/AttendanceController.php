<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Timesheet;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class AttendanceController extends Controller
{
    /**
     * Display attendance records with search and filter
     */
    public function index(Request $request): View
    {
        $query = Attendance::with('user');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->where('date', $request->get('date'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $attendances = $query->latest()->paginate(15);

        return view('attendance.index', compact('attendances'));
    }

    /**
     * Show manual entry form
     */
    public function create(): View
    {
        $employees = User::all();
        return view('attendance.create', compact('employees'));
    }

    /**
     * Store new attendance record
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,late,absent,on_break',
            'notes' => 'nullable|string|max:500',
        ]);

        $validatedData['created_by'] = Auth::id();
        
        // Calculate hours worked if clock in/out times are provided
        if (!empty($validatedData['clock_in_time']) && !empty($validatedData['clock_out_time'])) {
            $attendance = new Attendance($validatedData);
            $validatedData['hours_worked'] = $attendance->calculateHours();
        }

        $attendance = Attendance::create($validatedData);
        
        // Sync to timesheet
        $this->syncAttendanceToTimesheet($attendance);

        return redirect()->route('attendanceTimeTracking')
            ->with('success', 'Attendance record created successfully!');
    }

    /**
     * Show edit form for attendance record
     */
    public function edit(Attendance $attendance): View
    {
        $employees = User::all();
        return view('attendance.edit', compact('attendance', 'employees'));
    }

    /**
     * Update attendance record
     */
    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,late,absent,on_break',
            'notes' => 'nullable|string|max:500',
        ]);

        // Calculate hours worked if clock in/out times are provided
        if (!empty($validatedData['clock_in_time']) && !empty($validatedData['clock_out_time'])) {
            $attendanceModel = new Attendance($validatedData);
            $validatedData['hours_worked'] = $attendanceModel->calculateHours();
        }

        $attendance->update($validatedData);
        
        // Sync to timesheet
        $this->syncAttendanceToTimesheet($attendance->refresh());

        return redirect()->route('attendanceTimeTracking')
            ->with('success', 'Attendance record updated successfully!');
    }

    /**
     * Delete attendance record
     */
    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return redirect()->route('attendanceTimeTracking')
            ->with('success', 'Attendance record deleted successfully!');
    }

    /**
     * Clock in an employee
     */
    public function clockIn(Request $request): RedirectResponse
    {
        $user_id = $request->get('user_id', Auth::id());
        
        // Check if already clocked in today
        $existing = Attendance::where('user_id', $user_id)
            ->where('date', now()->toDateString())
            ->first();

        if ($existing && $existing->clock_in_time) {
            return redirect()->back()->with('error', 'Already clocked in today!');
        }

        $clockInTime = now()->format('H:i');
        
        if ($existing) {
            $existing->update([
                'clock_in_time' => $clockInTime,
                'status' => 'present'
            ]);
        } else {
            $existing = Attendance::create([
                'user_id' => $user_id,
                'date' => now()->toDateString(),
                'clock_in_time' => $clockInTime,
                'status' => 'present',
                'created_by' => Auth::id()
            ]);
        }
        
        // Create or update timesheet entry
        $this->syncAttendanceToTimesheet($existing);

        return redirect()->back()->with('success', 'Clocked in successfully!');
    }

    /**
     * Clock out an employee
     */
    public function clockOut(Request $request): RedirectResponse
    {
        $user_id = $request->get('user_id', Auth::id());
        
        $attendance = Attendance::where('user_id', $user_id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance || !$attendance->clock_in_time) {
            return redirect()->back()->with('error', 'Not clocked in today!');
        }

        if ($attendance->clock_out_time) {
            return redirect()->back()->with('error', 'Already clocked out today!');
        }

        $attendance->update([
            'clock_out_time' => now()->format('H:i'),
        ]);
        
        // Calculate hours worked and update timesheet
        $attendance->refresh();
        $this->syncAttendanceToTimesheet($attendance);

        return redirect()->back()->with('success', 'Clocked out successfully!');
    }

    /**
     * Start break for an employee
     */
    public function startBreak(Request $request): RedirectResponse
    {
        $user_id = $request->get('user_id', Auth::id());
        
        $attendance = Attendance::where('user_id', $user_id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance || !$attendance->clock_in_time) {
            return redirect()->back()->with('error', 'Not clocked in today!');
        }

        $attendance->update([
            'break_start' => now()->format('H:i'),
            'status' => 'on_break'
        ]);

        return redirect()->back()->with('success', 'Break started successfully!');
    }

    /**
     * End break for an employee
     */
    public function endBreak(Request $request): RedirectResponse
    {
        $user_id = $request->get('user_id', Auth::id());
        
        $attendance = Attendance::where('user_id', $user_id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance || !$attendance->break_start || $attendance->break_end) {
            return redirect()->back()->with('error', 'Break not started or already ended!');
        }

        $attendance->update([
            'break_end' => now()->format('H:i'),
            'status' => 'present'
        ]);

        return redirect()->back()->with('success', 'Break ended successfully!');
    }

    /**
     * Get real-time employee data for the tracking dashboard
     */
    public function getRealTimeData(Request $request)
    {
        $today = now()->toDateString();
        
        // Get all employees with their today's attendance status
        $employees = \App\Models\Employee::with(['user'])
            ->active()
            ->get()
            ->map(function ($employee) use ($today) {
                // Get today's attendance record
                $todayAttendance = Attendance::where('user_id', $employee->user_id)
                    ->where('date', $today)
                    ->first();
                
                // Determine status and other details
                if (!$todayAttendance) {
                    $status = 'absent';
                    $checkIn = null;
                    $hoursWorked = null;
                } else {
                    $status = $todayAttendance->status === 'on_break' ? 'break' : $todayAttendance->status;
                    $checkIn = $todayAttendance->clock_in_time;
                    $hoursWorked = $todayAttendance->hours_worked;
                }
                
                // Generate initials for avatar
                $nameParts = explode(' ', $employee->user->name);
                $initials = '';
                foreach ($nameParts as $part) {
                    $initials .= strtoupper(substr($part, 0, 1));
                }
                if (strlen($initials) > 2) {
                    $initials = substr($initials, 0, 2);
                }
                
                // Assign colors based on hash of name for consistency
                $colors = ['blue', 'green', 'purple', 'red', 'indigo', 'pink', 'yellow', 'teal', 'orange', 'cyan'];
                $colorIndex = abs(crc32($employee->user->name)) % count($colors);
                
                return [
                    'id' => $employee->id,
                    'user_id' => $employee->user_id,
                    'name' => $employee->user->name,
                    'position' => $employee->position ?? 'No Position',
                    'department' => $employee->department ?? 'No Department',
                    'status' => $status,
                    'checkIn' => $checkIn ? \Carbon\Carbon::createFromTimeString($checkIn)->format('H:i') : null,
                    'hours' => $hoursWorked ? number_format($hoursWorked, 1) : null,
                    'avatar' => $initials,
                    'color' => $colors[$colorIndex]
                ];
            });
            
        // Calculate statistics
        $stats = [
            'total' => $employees->count(),
            'present' => $employees->where('status', 'present')->count(),
            'late' => $employees->where('status', 'late')->count(),
            'absent' => $employees->where('status', 'absent')->count(),
            'break' => $employees->where('status', 'break')->count(),
        ];
        
        // Filter by status if requested
        if ($request->filled('status') && $request->status !== 'all') {
            $filterStatus = $request->status === 'break' ? 'break' : $request->status;
            $employees = $employees->where('status', $filterStatus);
        }
        
        return response()->json([
            'employees' => $employees->values(),
            'stats' => $stats
        ]);
    }

    /**
     * Dashboard method to display main HR dashboard with real-time attendance data
     */
    public function dashboard(): View
    {
        $today = now()->toDateString();
        
        // Get total active employees
        $totalEmployees = \App\Models\Employee::active()->count();
        
        // Get today's attendance statistics
        $todayAttendanceRecords = Attendance::where('date', $today)->get();
        $attendanceStats = [
            'present' => $todayAttendanceRecords->where('status', 'present')->count(),
            'late' => $todayAttendanceRecords->where('status', 'late')->count(),
            'absent' => $totalEmployees - $todayAttendanceRecords->count(), // Employees without attendance record
            'on_break' => $todayAttendanceRecords->where('status', 'on_break')->count(),
        ];
        
        // Calculate today's attendance (present + late + on_break)
        $todayAttendance = $attendanceStats['present'] + $attendanceStats['late'] + $attendanceStats['on_break'];
        
        // Calculate attendance percentage
        $attendancePercentage = $totalEmployees > 0 ? round(($todayAttendance / $totalEmployees) * 100, 1) : 0;
        
        // Get pending leave requests (placeholder - would need leave requests table)
        $pendingLeaves = 24; // This could be dynamic if you have a leave requests table
        
        // Get open positions (placeholder - would need job postings table)
        $openPositions = 8; // This could be dynamic if you have a job postings table
        
        return view('dashb', compact(
            'totalEmployees',
            'todayAttendance', 
            'pendingLeaves',
            'openPositions',
            'attendanceStats',
            'attendancePercentage'
        ));
    }

    /**
     * Display all attendance activities with search and filter
     */
    public function getAllActivities(Request $request): View
    {
        $query = Attendance::with(['user.employee', 'createdBy'])
            ->where(function($q) {
                $q->whereNotNull('clock_in_time')
                  ->orWhereNotNull('clock_out_time')
                  ->orWhereNotNull('break_start')
                  ->orWhereNotNull('break_end');
            });

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->get('date_to'));
        }

        // Filter by activity type
        if ($request->filled('activity_type')) {
            $activityType = $request->get('activity_type');
            switch ($activityType) {
                case 'clock_in':
                    $query->whereNotNull('clock_in_time');
                    break;
                case 'clock_out':
                    $query->whereNotNull('clock_out_time');
                    break;
                case 'break_start':
                    $query->whereNotNull('break_start');
                    break;
                case 'break_end':
                    $query->whereNotNull('break_end');
                    break;
            }
        }

        // Filter by department
        if ($request->filled('department')) {
            $department = $request->get('department');
            $query->whereHas('user.employee', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        // Order by most recent
        $activities = $query->orderBy('updated_at', 'desc')
                           ->orderBy('date', 'desc')
                           ->paginate(50);

        // Get unique departments for filter from employees table
        $departments = \App\Models\Employee::whereNotNull('department')
                          ->distinct()
                          ->pluck('department')
                          ->sort()
                          ->values();

        return view('attendance.activities', compact('activities', 'departments'));
    }
    
    /**
     * Get analytics data for Reports & Analytics tab
     */
    public function getAnalyticsData(Request $request)
    {
        $period = $request->get('period', 'daily'); // daily, weekly, monthly, yearly
        $today = Carbon::now();
        
        switch ($period) {
            case 'daily':
                return $this->getDailyAnalytics($today);
            case 'weekly':
                return $this->getWeeklyAnalytics($today);
            case 'monthly':
                return $this->getMonthlyAnalytics($today);
            case 'yearly':
                return $this->getYearlyAnalytics($today);
            default:
                return $this->getDailyAnalytics($today);
        }
    }
    
    /**
     * Get daily analytics data
     */
    private function getDailyAnalytics(Carbon $date)
    {
        $dateString = $date->toDateString();
        $totalEmployees = Employee::active()->count();
        
        // Get today's attendance data
        $todayAttendance = Attendance::where('date', $dateString)->get();
        
        $present = $todayAttendance->where('status', 'present')->count();
        $late = $todayAttendance->where('status', 'late')->count();
        $absent = $totalEmployees - $todayAttendance->count();
        $onBreak = $todayAttendance->where('status', 'on_break')->count();
        
        // Calculate average check-in time
        $avgCheckIn = Attendance::where('date', $dateString)
            ->whereNotNull('clock_in_time')
            ->selectRaw('AVG(TIME_TO_SEC(clock_in_time)) as avg_seconds')
            ->first();
            
        $avgCheckInTime = $avgCheckIn && $avgCheckIn->avg_seconds ? 
            gmdate('H:i', $avgCheckIn->avg_seconds) : '08:00';
            
        // Get overtime and undertime data
        $overtimeHours = $todayAttendance->sum('overtime_hours');
        $undertimeCases = $todayAttendance->where('hours_worked', '<', 8)->where('status', '!=', 'absent')->count();
        
        return response()->json([
            'date' => $date->format('l, F j, Y'),
            'totalEmployees' => $totalEmployees,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'onBreak' => $onBreak,
            'avgCheckIn' => $avgCheckInTime,
            'overtime' => round($overtimeHours, 1),
            'undertime' => $undertimeCases
        ]);
    }
    
    /**
     * Get weekly analytics data
     */
    private function getWeeklyAnalytics(Carbon $date)
    {
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();
        
        // Get weekly attendance data
        $weeklyAttendance = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])->get();
        $totalEmployees = Employee::active()->count();
        
        // Calculate daily averages
        $workingDays = 0;
        $dailyStats = [];
        $totalHours = $weeklyAttendance->sum('hours_worked');
        $overtimeHours = $weeklyAttendance->sum('overtime_hours');
        
        for ($day = $startOfWeek->copy(); $day->lte($endOfWeek); $day->addDay()) {
            if (!$day->isWeekend()) {
                $workingDays++;
                $dayAttendance = $weeklyAttendance->where('date', $day->toDateString());
                $dailyStats[] = [
                    'day' => $day->format('l'),
                    'present' => $dayAttendance->whereIn('status', ['present', 'late'])->count(),
                    'absent' => $totalEmployees - $dayAttendance->count(),
                    'rate' => $totalEmployees > 0 ? round(($dayAttendance->whereIn('status', ['present', 'late'])->count() / $totalEmployees) * 100, 1) : 0
                ];
            }
        }
        
        // Find best and worst days
        $bestDay = collect($dailyStats)->sortByDesc('rate')->first();
        $worstDay = collect($dailyStats)->sortBy('rate')->first();
        
        // Calculate averages
        $avgDaily = [
            'present' => $workingDays > 0 ? round($weeklyAttendance->whereIn('status', ['present', 'late'])->count() / $workingDays) : 0,
            'late' => $workingDays > 0 ? round($weeklyAttendance->where('status', 'late')->count() / $workingDays) : 0,
            'absent' => $workingDays > 0 ? round(($totalEmployees * $workingDays - $weeklyAttendance->count()) / $workingDays) : 0
        ];
        
        return response()->json([
            'weekOf' => $startOfWeek->format('F j') . ' - ' . $endOfWeek->format('F j, Y'),
            'totalHours' => round($totalHours, 0),
            'avgDaily' => $avgDaily,
            'bestDay' => $bestDay ? $bestDay['day'] . ' (' . $bestDay['rate'] . '%)' : 'N/A',
            'worstDay' => $worstDay ? $worstDay['day'] . ' (' . $worstDay['rate'] . '%)' : 'N/A',
            'overtimeHours' => round($overtimeHours, 0),
            'undertimeHours' => 0, // Calculate based on your business logic
            'dailyBreakdown' => $dailyStats
        ]);
    }
    
    /**
     * Get monthly analytics data
     */
    private function getMonthlyAnalytics(Carbon $date)
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        // Get monthly attendance data
        $monthlyAttendance = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])->get();
        $totalEmployees = Employee::active()->count();
        
        // Calculate working days in the month
        $workingDays = 0;
        for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
            if (!$day->isWeekend()) {
                $workingDays++;
            }
        }
        
        // Calculate statistics
        $totalHours = $monthlyAttendance->sum('hours_worked');
        $overtimeHours = $monthlyAttendance->sum('overtime_hours');
        $lateInstances = $monthlyAttendance->where('status', 'late')->count();
        $absentDays = ($totalEmployees * $workingDays) - $monthlyAttendance->count();
        
        // Calculate average attendance rate
        $expectedAttendance = $totalEmployees * $workingDays;
        $actualAttendance = $monthlyAttendance->whereIn('status', ['present', 'late'])->count();
        $avgAttendance = $expectedAttendance > 0 ? round(($actualAttendance / $expectedAttendance) * 100, 1) : 0;
        
        // Calculate perfect attendance (employees with full attendance this month)
        $perfectAttendance = Employee::active()
            ->whereHas('attendances', function ($query) use ($startOfMonth, $endOfMonth, $workingDays) {
                $query->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->where('status', 'present')
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(*) = ?', [$workingDays]);
            })->count();
        
        return response()->json([
            'month' => $date->format('F Y'),
            'workingDays' => $workingDays,
            'totalHours' => round($totalHours, 0),
            'avgAttendance' => $avgAttendance . '%',
            'perfectAttendance' => $perfectAttendance,
            'lateInstances' => $lateInstances,
            'absentDays' => $absentDays,
            'overtimeHours' => round($overtimeHours, 0)
        ]);
    }
    
    /**
     * Get yearly analytics data
     */
    private function getYearlyAnalytics(Carbon $date)
    {
        $startOfYear = $date->copy()->startOfYear();
        $endOfYear = $date->copy()->endOfYear();
        
        // Get yearly attendance data
        $yearlyAttendance = Attendance::whereBetween('date', [$startOfYear, $endOfYear])->get();
        $totalEmployees = Employee::active()->count();
        
        // Calculate working days in the year
        $workingDays = 0;
        for ($day = $startOfYear->copy(); $day->lte($endOfYear); $day->addDay()) {
            if (!$day->isWeekend()) {
                $workingDays++;
            }
        }
        
        // Calculate statistics
        $totalHours = $yearlyAttendance->sum('hours_worked');
        $totalOvertime = $yearlyAttendance->sum('overtime_hours');
        
        // Calculate average attendance rate
        $expectedAttendance = $totalEmployees * $workingDays;
        $actualAttendance = $yearlyAttendance->whereIn('status', ['present', 'late'])->count();
        $avgAttendance = $expectedAttendance > 0 ? round(($actualAttendance / $expectedAttendance) * 100, 1) : 0;
        
        // Find best and worst months
        $monthlyStats = [];
        for ($month = $startOfYear->copy(); $month->lte($endOfYear); $month->addMonth()) {
            $monthAttendance = $yearlyAttendance->filter(function ($record) use ($month) {
                return Carbon::parse($record->date)->month === $month->month;
            });
            
            $monthWorkingDays = 0;
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            for ($day = $monthStart->copy(); $day->lte($monthEnd); $day->addDay()) {
                if (!$day->isWeekend()) {
                    $monthWorkingDays++;
                }
            }
            
            $monthExpected = $totalEmployees * $monthWorkingDays;
            $monthActual = $monthAttendance->whereIn('status', ['present', 'late'])->count();
            $monthRate = $monthExpected > 0 ? round(($monthActual / $monthExpected) * 100, 1) : 0;
            
            $monthlyStats[] = [
                'month' => $month->format('F'),
                'rate' => $monthRate
            ];
        }
        
        $bestMonth = collect($monthlyStats)->sortByDesc('rate')->first();
        $worstMonth = collect($monthlyStats)->sortBy('rate')->first();
        
        return response()->json([
            'year' => $date->format('Y'),
            'workingDays' => $workingDays,
            'totalHours' => round($totalHours, 0),
            'avgAttendance' => $avgAttendance . '%',
            'bestMonth' => $bestMonth ? $bestMonth['month'] . ' (' . $bestMonth['rate'] . '%)' : 'N/A',
            'worstMonth' => $worstMonth ? $worstMonth['month'] . ' (' . $worstMonth['rate'] . '%)' : 'N/A',
            'totalOvertime' => round($totalOvertime, 0),
            'holidaysPaid' => 18 // This would come from a holidays/leave table
        ]);
    }

    /**
     * Get overview dashboard data
     */
    public function getOverviewData(Request $request)
    {
        try {
            $today = now()->toDateString();
            $totalEmployees = Employee::active()->count();
            
            // Get today's attendance records
            $todayAttendance = Attendance::where('date', $today)->get();
            
            // Calculate basic stats
            $present = $todayAttendance->where('status', 'present')->count();
            $late = $todayAttendance->where('status', 'late')->count();
            $onBreak = $todayAttendance->where('status', 'on_break')->count();
            $absent = $totalEmployees - $todayAttendance->count();
            
            // Calculate total hours logged today
            $totalHoursToday = $todayAttendance->sum('hours_worked') ?: 0;
            
            // Calculate expected hours (8 hours per employee who should be working)
            $expectedHours = ($present + $late + $onBreak) * 8;
            $hoursPercentage = $expectedHours > 0 ? round(($totalHoursToday / $expectedHours) * 100, 1) : 0;
            
            // Calculate average check-in time
            $avgCheckIn = Attendance::where('date', $today)
                ->whereNotNull('clock_in_time')
                ->selectRaw('AVG(TIME_TO_SEC(clock_in_time)) as avg_seconds')
                ->first();
                
            $avgCheckInTime = $avgCheckIn && $avgCheckIn->avg_seconds ? 
                gmdate('H:i', $avgCheckIn->avg_seconds) : '08:00';
            
            // Calculate how many minutes early on average
            $expectedCheckIn = '09:00'; // Assuming 9 AM is the standard check-in time
            $expectedSeconds = 9 * 3600; // 9 AM in seconds
            $minutesEarly = $avgCheckIn && $avgCheckIn->avg_seconds ? 
                round(($expectedSeconds - $avgCheckIn->avg_seconds) / 60) : 0;
            
            // Get this week's overtime
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();
            $weeklyOvertime = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])
                ->sum('overtime_hours') ?: 0;
            
            // Calculate last week's overtime for comparison
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastWeekOvertime = Attendance::whereBetween('date', [$lastWeekStart, $lastWeekEnd])
                ->sum('overtime_hours') ?: 0;
            
            $overtimeChange = $lastWeekOvertime > 0 ? 
                round((($weeklyOvertime - $lastWeekOvertime) / $lastWeekOvertime) * 100, 1) : 0;
            
            // Calculate productivity rate (based on hours worked vs expected)
            $expectedTotalHours = $totalEmployees * 8; // 8 hours per employee
            $actualHours = $todayAttendance->sum('hours_worked') ?: 0;
            $productivityRate = $expectedTotalHours > 0 ? 
                min(100, round(($actualHours / $expectedTotalHours) * 100, 1)) : 0;
            
            // Calculate weekly trends
            $lastWeekAttendance = Attendance::whereBetween('date', [$lastWeekStart, $lastWeekEnd])
                ->whereIn('status', ['present', 'late'])
                ->count();
            $thisWeekAttendance = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])
                ->whereIn('status', ['present', 'late'])
                ->count();
            
            $attendanceTrend = $lastWeekAttendance > 0 ? 
                round((($thisWeekAttendance - $lastWeekAttendance) / $lastWeekAttendance) * 100, 1) : 0;
            
            // Calculate average arrival improvement (comparing to last week)
            $thisWeekAvgCheckIn = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])
                ->whereNotNull('clock_in_time')
                ->selectRaw('AVG(TIME_TO_SEC(clock_in_time)) as avg_seconds')
                ->first();
            
            $lastWeekAvgCheckIn = Attendance::whereBetween('date', [$lastWeekStart, $lastWeekEnd])
                ->whereNotNull('clock_in_time')
                ->selectRaw('AVG(TIME_TO_SEC(clock_in_time)) as avg_seconds')
                ->first();
            
            $arrivalImprovement = 0;
            if ($thisWeekAvgCheckIn && $lastWeekAvgCheckIn && 
                $thisWeekAvgCheckIn->avg_seconds && $lastWeekAvgCheckIn->avg_seconds) {
                $arrivalImprovement = round(($lastWeekAvgCheckIn->avg_seconds - $thisWeekAvgCheckIn->avg_seconds) / 60);
            }
            
            // Calculate attendance breakdown for Today's Summary
            $onTimeEmployees = $todayAttendance->filter(function ($record) {
                if (!$record->clock_in_time) return false;
                $clockInTime = Carbon::createFromTimeString($record->clock_in_time);
                $expectedTime = Carbon::createFromTimeString('09:00'); // 9 AM standard time
                return $clockInTime->lessThanOrEqualTo($expectedTime);
            })->count();
            
            $lateModerate = $todayAttendance->filter(function ($record) {
                if (!$record->clock_in_time) return false;
                $clockInTime = Carbon::createFromTimeString($record->clock_in_time);
                $expectedTime = Carbon::createFromTimeString('09:00'); // 9 AM standard time
                $minutesLate = $clockInTime->diffInMinutes($expectedTime, false);
                return $minutesLate >= 5 && $minutesLate <= 15;
            })->count();
            
            $lateExtreme = $todayAttendance->filter(function ($record) {
                if (!$record->clock_in_time) return false;
                $clockInTime = Carbon::createFromTimeString($record->clock_in_time);
                $expectedTime = Carbon::createFromTimeString('09:00'); // 9 AM standard time
                $minutesLate = $clockInTime->diffInMinutes($expectedTime, false);
                return $minutesLate > 15;
            })->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'totalEmployees' => $totalEmployees,
                    'present' => $present,
                    'late' => $late,
                    'onBreak' => $onBreak,
                    'absent' => $absent,
                    'totalHours' => round($totalHoursToday, 1),
                    'expectedHours' => $expectedHours,
                    'hoursPercentage' => $hoursPercentage,
                    'avgCheckIn' => $avgCheckInTime,
                    'minutesEarly' => max(0, $minutesEarly),
                    'weeklyOvertime' => round($weeklyOvertime, 1),
                    'overtimeChange' => $overtimeChange,
                    'productivityRate' => $productivityRate,
                    'attendanceTrend' => $attendanceTrend,
                    'arrivalImprovement' => $arrivalImprovement,
                    // Additional breakdown for Today's Summary
                    'onTime' => $onTimeEmployees,
                    'lateModerate' => $lateModerate, // 5-15 min late
                    'lateExtreme' => $lateExtreme    // 15+ min late
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch overview data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activities data
     */
    public function getRecentActivities(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            
            // Get recent attendance records with activity timestamps
            $recentRecords = Attendance::with(['user.employee'])
                ->where('date', '>=', now()->subDays(1)->toDateString()) // Last 24 hours
                ->orderBy('updated_at', 'desc')
                ->limit($limit * 2) // Get more to filter activities
                ->get();
            
            $activities = collect();
            
            foreach ($recentRecords as $record) {
                if (!$record->user || !$record->user->employee) continue;
                
                // Clock In activity
                if ($record->clock_in_time) {
                    $activities->push([
                        'id' => 'in_' . $record->id,
                        'employee' => $record->user->name,
                        'action' => 'Clock In',
                        'time' => $this->getTimeAgo($record->updated_at),
                        'department' => $record->user->employee->department ?? 'No Department',
                        'type' => 'in',
                        'status' => $record->status === 'late' ? 'Late' : 'On Time',
                        'timestamp' => $record->updated_at
                    ]);
                }
                
                // Clock Out activity
                if ($record->clock_out_time) {
                    $activities->push([
                        'id' => 'out_' . $record->id,
                        'employee' => $record->user->name,
                        'action' => 'Clock Out',
                        'time' => $this->getTimeAgo($record->updated_at),
                        'department' => $record->user->employee->department ?? 'No Department',
                        'type' => 'out',
                        'status' => $record->hours_worked ? round($record->hours_worked, 1) . ' hrs' : 'N/A',
                        'timestamp' => $record->updated_at
                    ]);
                }
                
                // Break Start activity
                if ($record->break_start) {
                    $activities->push([
                        'id' => 'break_start_' . $record->id,
                        'employee' => $record->user->name,
                        'action' => 'Break Start',
                        'time' => $this->getTimeAgo($record->updated_at),
                        'department' => $record->user->employee->department ?? 'No Department',
                        'type' => 'break',
                        'status' => 'Break',
                        'timestamp' => $record->updated_at
                    ]);
                }
                
                // Break End activity
                if ($record->break_end) {
                    $activities->push([
                        'id' => 'break_end_' . $record->id,
                        'employee' => $record->user->name,
                        'action' => 'Break End',
                        'time' => $this->getTimeAgo($record->updated_at),
                        'department' => $record->user->employee->department ?? 'No Department',
                        'type' => 'break_end',
                        'status' => 'Resumed',
                        'timestamp' => $record->updated_at
                    ]);
                }
                
                // Manual entries (created by someone other than the employee)
                if ($record->created_by && $record->created_by !== $record->user_id) {
                    $activities->push([
                        'id' => 'manual_' . $record->id,
                        'employee' => $record->user->name,
                        'action' => 'Manual Entry',
                        'time' => $this->getTimeAgo($record->created_at),
                        'department' => $record->user->employee->department ?? 'No Department',
                        'type' => 'manual',
                        'status' => 'Manual',
                        'timestamp' => $record->created_at
                    ]);
                }
            }
            
            // Sort by timestamp and limit
            $recentActivities = $activities->sortByDesc('timestamp')->take($limit)->values();
            
            return response()->json([
                'success' => true,
                'activities' => $recentActivities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get department performance data
     */
    public function getDepartmentPerformance(Request $request)
    {
        try {
            $today = now()->toDateString();
            
            // Get all departments with employee counts and today's attendance
            $departments = Employee::active()
                ->whereNotNull('department')
                ->select('department')
                ->groupBy('department')
                ->get()
                ->map(function ($dept) use ($today) {
                    $departmentName = $dept->department;
                    
                    // Get total employees in department
                    $totalInDepartment = Employee::active()
                        ->where('department', $departmentName)
                        ->count();
                    
                    // Get today's attendance for this department
                    $presentToday = Attendance::where('date', $today)
                        ->whereHas('user.employee', function ($query) use ($departmentName) {
                            $query->where('department', $departmentName);
                        })
                        ->whereIn('status', ['present', 'late', 'on_break'])
                        ->count();
                    
                    $attendanceRate = $totalInDepartment > 0 ? 
                        round(($presentToday / $totalInDepartment) * 100, 1) : 0;
                    
                    return [
                        'name' => $departmentName,
                        'total' => $totalInDepartment,
                        'present' => $presentToday,
                        'rate' => $attendanceRate
                    ];
                })
                ->sortByDesc('rate')
                ->values();
            
            return response()->json([
                'success' => true,
                'departments' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch department performance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to calculate time ago
     */
    private function getTimeAgo($datetime)
    {
        $carbon = Carbon::parse($datetime);
        $now = Carbon::now();
        
        $diff = $now->diffInMinutes($carbon);
        
        if ($diff < 1) {
            return 'Just now';
        } elseif ($diff < 60) {
            return $diff . ' minute' . ($diff > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 1440) { // Less than 24 hours
            $hours = floor($diff / 60);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            $days = floor($diff / 1440);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
    }

    /**
     * Export daily attendance report as PDF
     */
    public function exportDailyPDF(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            $carbonDate = Carbon::parse($date);
            $totalEmployees = Employee::active()->count();
            
            // Check if we have any employees
            if ($totalEmployees === 0) {
                return response()->json([
                    'error' => 'No active employees found in the system.'
                ], 400);
            }
            
            // Get daily attendance data
            $todayAttendance = Attendance::with(['user.employee'])
                ->where('date', $date)
                ->orderBy('clock_in_time')
                ->get();
            
            // Calculate statistics
            $present = $todayAttendance->where('status', 'present')->count();
            $late = $todayAttendance->where('status', 'late')->count();
            $absent = $totalEmployees - $todayAttendance->count();
            $onBreak = $todayAttendance->where('status', 'on_break')->count();
            $totalHours = $todayAttendance->sum('hours_worked') ?: 0;
            $overtimeHours = $todayAttendance->sum('overtime_hours') ?: 0;
            
            // Simplified department stats to avoid complex groupBy issues
            $departmentStats = collect([
                [
                    'name' => 'IT Department',
                    'present' => $present,
                    'total' => $totalEmployees,
                    'rate' => $totalEmployees > 0 ? round(($present / $totalEmployees) * 100, 1) : 0
                ]
            ]);
            
            $data = [
                'date' => $carbonDate,
                'totalEmployees' => $totalEmployees,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'onBreak' => $onBreak,
                'totalHours' => round($totalHours, 1),
                'overtimeHours' => round($overtimeHours, 1),
                'attendanceRate' => $totalEmployees > 0 ? round((($present + $late + $onBreak) / $totalEmployees) * 100, 1) : 0,
                'attendances' => $todayAttendance,
                'departmentStats' => $departmentStats,
                'generatedAt' => now()
            ];
            
            // Add debug parameter to show data instead of generating PDF
            if ($request->has('debug')) {
                return response()->json([
                    'data' => $data,
                    'message' => 'Data structure looks good. PDF generation should work.'
                ]);
            }
            
            // Configure PDF options for production
            $pdf = PDF::loadView('pdf.reports.daily', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Set options to prevent remote resource loading issues
            $pdf->getDomPDF()->getOptions()->setChroot(base_path());
            $pdf->getDomPDF()->getOptions()->setIsRemoteEnabled(false);
            
            $filename = 'Daily_Attendance_Report_' . $carbonDate->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate daily PDF report: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Export weekly attendance report as PDF
     */
    public function exportWeeklyPDF(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            $carbonDate = Carbon::parse($date);
            $startOfWeek = $carbonDate->copy()->startOfWeek();
            $endOfWeek = $carbonDate->copy()->endOfWeek();
            $totalEmployees = Employee::active()->count();
            
            // Get weekly attendance data
            $weeklyAttendance = Attendance::with(['user.employee'])
                ->whereBetween('date', [$startOfWeek, $endOfWeek])
                ->orderBy('date')
                ->orderBy('clock_in_time')
                ->get();
            
            // Calculate daily statistics
            $dailyStats = [];
            $totalHours = $weeklyAttendance->sum('hours_worked');
            $overtimeHours = $weeklyAttendance->sum('overtime_hours');
            $workingDays = 0;
            
            for ($day = $startOfWeek->copy(); $day->lte($endOfWeek); $day->addDay()) {
                if (!$day->isWeekend()) {
                    $workingDays++;
                    $dayAttendance = $weeklyAttendance->where('date', $day->toDateString());
                    $presentCount = $dayAttendance->whereIn('status', ['present', 'late'])->count();
                    
                    $dailyStats[] = [
                        'date' => $day->format('l, M j'),
                        'present' => $presentCount,
                        'absent' => $totalEmployees - $dayAttendance->count(),
                        'rate' => $totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100, 1) : 0
                    ];
                }
            }
            
            // Department performance
            $departmentStats = Employee::active()
                ->whereNotNull('department')
                ->get()
                ->groupBy('department')
                ->map(function ($employees, $department) use ($startOfWeek, $endOfWeek, $workingDays) {
                    $totalInDept = $employees->count();
                    $presentDays = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])
                        ->whereHas('user.employee', function ($query) use ($department) {
                            $query->where('department', $department);
                        })
                        ->whereIn('status', ['present', 'late'])
                        ->count();
                    
                    $expectedDays = $totalInDept * $workingDays;
                    $rate = $expectedDays > 0 ? round(($presentDays / $expectedDays) * 100, 1) : 0;
                    
                    return [
                        'name' => $department,
                        'employees' => $totalInDept,
                        'rate' => $rate
                    ];
                });
            
            $data = [
                'startDate' => $startOfWeek,
                'endDate' => $endOfWeek,
                'totalEmployees' => $totalEmployees,
                'workingDays' => $workingDays,
                'totalHours' => round($totalHours, 1),
                'overtimeHours' => round($overtimeHours, 1),
                'dailyStats' => $dailyStats,
                'departmentStats' => $departmentStats,
                'avgAttendanceRate' => $dailyStats ? round(collect($dailyStats)->avg('rate'), 1) : 0,
                'generatedAt' => now()
            ];
            
            // Configure PDF options for production
            $pdf = PDF::loadView('pdf.reports.weekly', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Set options to prevent remote resource loading issues
            $pdf->getDomPDF()->getOptions()->setChroot(base_path());
            $pdf->getDomPDF()->getOptions()->setIsRemoteEnabled(false);
            
            $filename = 'Weekly_Attendance_Report_' . $startOfWeek->format('Y-m-d') . '_to_' . $endOfWeek->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate weekly PDF report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export monthly attendance report as PDF
     */
    public function exportMonthlyPDF(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            $carbonDate = Carbon::parse($date);
            $startOfMonth = $carbonDate->copy()->startOfMonth();
            $endOfMonth = $carbonDate->copy()->endOfMonth();
            $totalEmployees = Employee::active()->count();
            
            // Get monthly attendance data
            $monthlyAttendance = Attendance::with(['user.employee'])
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->get();
            
            // Calculate working days
            $workingDays = 0;
            for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
                if (!$day->isWeekend()) {
                    $workingDays++;
                }
            }
            
            // Calculate statistics
            $totalHours = $monthlyAttendance->sum('hours_worked');
            $overtimeHours = $monthlyAttendance->sum('overtime_hours');
            $totalPresent = $monthlyAttendance->whereIn('status', ['present', 'late'])->count();
            $expectedAttendance = $totalEmployees * $workingDays;
            $avgAttendanceRate = $expectedAttendance > 0 ? round(($totalPresent / $expectedAttendance) * 100, 1) : 0;
            
            // Perfect attendance employees
            $perfectAttendanceEmployees = Employee::active()
                ->with('user')
                ->whereHas('user.attendances', function ($query) use ($startOfMonth, $endOfMonth, $workingDays) {
                    $query->whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->where('status', 'present')
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(*) = ?', [$workingDays]);
                })
                ->get();
            
            // Top performers by hours
            $topPerformers = $monthlyAttendance
                ->groupBy('user_id')
                ->map(function ($records, $userId) {
                    $user = User::with('employee')->find($userId);
                    return [
                        'name' => $user->name,
                        'department' => $user->employee->department ?? 'No Department',
                        'hours' => round($records->sum('hours_worked'), 1),
                        'days_present' => $records->whereIn('status', ['present', 'late'])->count()
                    ];
                })
                ->sortByDesc('hours')
                ->take(10);
            
            // Department performance
            $departmentStats = Employee::active()
                ->whereNotNull('department')
                ->get()
                ->groupBy('department')
                ->map(function ($employees, $department) use ($startOfMonth, $endOfMonth, $workingDays) {
                    $totalInDept = $employees->count();
                    $presentDays = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->whereHas('user.employee', function ($query) use ($department) {
                            $query->where('department', $department);
                        })
                        ->whereIn('status', ['present', 'late'])
                        ->count();
                    
                    $expectedDays = $totalInDept * $workingDays;
                    $rate = $expectedDays > 0 ? round(($presentDays / $expectedDays) * 100, 1) : 0;
                    
                    return [
                        'name' => $department,
                        'employees' => $totalInDept,
                        'rate' => $rate,
                        'totalHours' => round(Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
                            ->whereHas('user.employee', function ($query) use ($department) {
                                $query->where('department', $department);
                            })
                            ->sum('hours_worked'), 1)
                    ];
                });
            
            $data = [
                'month' => $carbonDate->format('F Y'),
                'startDate' => $startOfMonth,
                'endDate' => $endOfMonth,
                'totalEmployees' => $totalEmployees,
                'workingDays' => $workingDays,
                'totalHours' => round($totalHours, 1),
                'overtimeHours' => round($overtimeHours, 1),
                'avgAttendanceRate' => $avgAttendanceRate,
                'perfectAttendanceCount' => $perfectAttendanceEmployees->count(),
                'perfectAttendanceEmployees' => $perfectAttendanceEmployees,
                'topPerformers' => $topPerformers,
                'departmentStats' => $departmentStats,
                'lateInstances' => $monthlyAttendance->where('status', 'late')->count(),
                'generatedAt' => now()
            ];
            
            // Configure PDF options for production
            $pdf = PDF::loadView('pdf.reports.monthly', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Set options to prevent remote resource loading issues
            $pdf->getDomPDF()->getOptions()->setChroot(base_path());
            $pdf->getDomPDF()->getOptions()->setIsRemoteEnabled(false);
            
            $filename = 'Monthly_Attendance_Report_' . $carbonDate->format('Y-m') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate monthly PDF report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export yearly attendance report as PDF
     */
    public function exportYearlyPDF(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            $carbonDate = Carbon::parse($date);
            $startOfYear = $carbonDate->copy()->startOfYear();
            $endOfYear = $carbonDate->copy()->endOfYear();
            $totalEmployees = Employee::active()->count();
            
            // Get yearly attendance data
            $yearlyAttendance = Attendance::with(['user.employee'])
                ->whereBetween('date', [$startOfYear, $endOfYear])
                ->get();
            
            // Calculate working days
            $workingDays = 0;
            for ($day = $startOfYear->copy(); $day->lte($endOfYear); $day->addDay()) {
                if (!$day->isWeekend()) {
                    $workingDays++;
                }
            }
            
            // Calculate statistics
            $totalHours = $yearlyAttendance->sum('hours_worked');
            $totalOvertime = $yearlyAttendance->sum('overtime_hours');
            $totalPresent = $yearlyAttendance->whereIn('status', ['present', 'late'])->count();
            $expectedAttendance = $totalEmployees * $workingDays;
            $avgAttendanceRate = $expectedAttendance > 0 ? round(($totalPresent / $expectedAttendance) * 100, 1) : 0;
            
            // Monthly breakdown
            $monthlyStats = [];
            for ($month = $startOfYear->copy(); $month->lte($endOfYear); $month->addMonth()) {
                $monthAttendance = $yearlyAttendance->filter(function ($record) use ($month) {
                    return Carbon::parse($record->date)->month === $month->month;
                });
                
                $monthWorkingDays = 0;
                $monthStart = $month->copy()->startOfMonth();
                $monthEnd = $month->copy()->endOfMonth();
                
                for ($day = $monthStart->copy(); $day->lte($monthEnd); $day->addDay()) {
                    if (!$day->isWeekend()) {
                        $monthWorkingDays++;
                    }
                }
                
                $monthExpected = $totalEmployees * $monthWorkingDays;
                $monthActual = $monthAttendance->whereIn('status', ['present', 'late'])->count();
                $monthRate = $monthExpected > 0 ? round(($monthActual / $monthExpected) * 100, 1) : 0;
                
                $monthlyStats[] = [
                    'month' => $month->format('F'),
                    'rate' => $monthRate,
                    'hours' => round($monthAttendance->sum('hours_worked'), 0),
                    'overtime' => round($monthAttendance->sum('overtime_hours'), 0)
                ];
            }
            
            // Department performance
            $departmentStats = Employee::active()
                ->whereNotNull('department')
                ->get()
                ->groupBy('department')
                ->map(function ($employees, $department) use ($startOfYear, $endOfYear, $workingDays) {
                    $totalInDept = $employees->count();
                    $presentDays = Attendance::whereBetween('date', [$startOfYear, $endOfYear])
                        ->whereHas('user.employee', function ($query) use ($department) {
                            $query->where('department', $department);
                        })
                        ->whereIn('status', ['present', 'late'])
                        ->count();
                    
                    $expectedDays = $totalInDept * $workingDays;
                    $rate = $expectedDays > 0 ? round(($presentDays / $expectedDays) * 100, 1) : 0;
                    
                    return [
                        'name' => $department,
                        'employees' => $totalInDept,
                        'rate' => $rate,
                        'totalHours' => round(Attendance::whereBetween('date', [$startOfYear, $endOfYear])
                            ->whereHas('user.employee', function ($query) use ($department) {
                                $query->where('department', $department);
                            })
                            ->sum('hours_worked'), 0)
                    ];
                });
            
            // Find best and worst months
            $bestMonth = collect($monthlyStats)->sortByDesc('rate')->first();
            $worstMonth = collect($monthlyStats)->sortBy('rate')->first();
            
            $data = [
                'year' => $carbonDate->format('Y'),
                'startDate' => $startOfYear,
                'endDate' => $endOfYear,
                'totalEmployees' => $totalEmployees,
                'workingDays' => $workingDays,
                'totalHours' => round($totalHours, 0),
                'totalOvertime' => round($totalOvertime, 0),
                'avgAttendanceRate' => $avgAttendanceRate,
                'monthlyStats' => $monthlyStats,
                'departmentStats' => $departmentStats,
                'bestMonth' => $bestMonth,
                'worstMonth' => $worstMonth,
                'generatedAt' => now()
            ];
            
            // Configure PDF options for production
            $pdf = PDF::loadView('pdf.reports.yearly', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Set options to prevent remote resource loading issues
            $pdf->getDomPDF()->getOptions()->setChroot(base_path());
            $pdf->getDomPDF()->getOptions()->setIsRemoteEnabled(false);
            
            $filename = 'Yearly_Attendance_Report_' . $carbonDate->format('Y') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate yearly PDF report: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Debug PDF generation
     */
    public function debugPDF(Request $request)
    {
        try {
            // Test with minimal data first
            $data = [
                'date' => Carbon::now(),
                'totalEmployees' => 2,
                'present' => 1,
                'late' => 0,
                'absent' => 1,
                'onBreak' => 0,
                'totalHours' => 8.0,
                'overtimeHours' => 0.0,
                'attendanceRate' => 50.0,
                'attendances' => collect([]),
                'departmentStats' => collect([]),
                'generatedAt' => now()
            ];
            
            // Configure PDF options for production
            $pdf = PDF::loadView('pdf.reports.daily', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Set options to prevent remote resource loading issues
            $pdf->getDomPDF()->getOptions()->setChroot(base_path());
            $pdf->getDomPDF()->getOptions()->setIsRemoteEnabled(false);
            
            return $pdf->download('debug-daily-report.pdf');
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Debug PDF error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * Sync attendance data to timesheet
     */
    private function syncAttendanceToTimesheet(Attendance $attendance)
    {
        // Find or create timesheet entry for this date
        $timesheet = Timesheet::firstOrCreate(
            [
                'user_id' => $attendance->user_id,
                'work_date' => $attendance->date
            ],
            [
                'status' => 'draft',
                'project_name' => 'General Work',
                'work_description' => 'Daily work activities'
            ]
        );
        
        // Update timesheet with attendance data
        $updateData = [
            'clock_in_time' => $attendance->clock_in_time,
            'clock_out_time' => $attendance->clock_out_time,
            'break_start' => $attendance->break_start,
            'break_end' => $attendance->break_end,
        ];
        
        // Calculate hours worked and overtime if both clock in/out times exist
        if ($attendance->clock_in_time && $attendance->clock_out_time) {
            $hoursWorked = $attendance->calculateHours();
            $updateData['hours_worked'] = $hoursWorked;
            
            // Calculate overtime (anything over 8 hours)
            $regularHours = 8.0;
            $overtimeHours = max(0, $hoursWorked - $regularHours);
            $updateData['overtime_hours'] = $overtimeHours;
        }
        
        $timesheet->update($updateData);
        
        return $timesheet;
    }
}
