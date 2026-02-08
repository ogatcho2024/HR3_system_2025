<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\ShiftRequest;
use App\Models\Attendance;
use App\Models\Reimbursement;
use App\Services\PayrollService;
use Carbon\Carbon;

class EmployeeDashboardController extends Controller
{
    /**
     * Display the employee dashboard
     */
    public function index(): View
    {
        $user = Auth::user();
        $employee = $user->employee;
        $today = Carbon::today();
        $now = Carbon::now();
        
        // Get current month and week data
        $currentMonth = Carbon::now();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        
        // Today's Attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();
        
        // Today's Shift/Schedule
        $todayShift = null;
        if (class_exists('App\Models\ShiftAssignment') && $employee) {
            $todayShift = \App\Models\ShiftAssignment::where('employee_id', $employee->id)
                ->whereDate('assignment_date', $today)
                ->with('shiftTemplate')
                ->first();
        }
        
        // Check if today is leave, rest day, or holiday
        $todayLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();
        
        // Dashboard statistics
        $stats = [
            'pending_leave_requests' => LeaveRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'approved_leave_requests' => LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->count(),
            'pending_shift_requests' => ShiftRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'total_attendance_days' => Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->count(),
            'leave_balance' => $this->calculateLeaveBalance($user->id),
            'leave_balance_detailed' => $this->calculateDetailedLeaveBalance($user->id),
            'current_month' => $currentMonth->format('F Y'),
            'hours_this_week' => $this->calculateWeekHours($user->id, $startOfWeek, $endOfWeek),
        ];
        
        // Recent attendance records (last 5)
        $recentAttendanceRecords = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();
        
        // Recent leave requests (last 3)
        $recentLeaveRequests = LeaveRequest::where('user_id', $user->id)
            ->latest()
            ->take(3)
            ->get();
        
        // Recent shift requests (last 3)
        $recentShiftRequests = ShiftRequest::where('user_id', $user->id)
            ->latest()
            ->take(3)
            ->get();
        
        // Announcements (using Alert model)
        $announcements = \App\Models\Alert::active()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
        
        return view('employee.dashboard', compact(
            'user',
            'employee', 
            'stats',
            'todayAttendance',
            'todayShift',
            'todayLeave',
            'recentAttendanceRecords',
            'recentLeaveRequests',
            'recentShiftRequests',
            'announcements',
            'today',
            'now'
        ));
    }

    /**
     * Get today's attendance for the authenticated employee (JSON).
     */
    public function todayAttendance()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $todayLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        $timeIn = $attendance && $attendance->clock_in_time
            ? Carbon::parse($attendance->clock_in_time)->format('g:i A')
            : null;
        $timeOut = $attendance && $attendance->clock_out_time
            ? Carbon::parse($attendance->clock_out_time)->format('g:i A')
            : null;

        $hoursWorked = $attendance && $attendance->hours_worked !== null
            ? number_format((float) $attendance->hours_worked, 1)
            : 0;

        $payload = [
            'has_leave' => (bool) $todayLeave,
            'leave_type' => $todayLeave ? $todayLeave->leave_type : null,
            'status' => $attendance ? $attendance->status : null,
            'is_late' => $attendance ? (bool) ($attendance->is_late ?? false) : false,
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'hours_worked' => $hoursWorked,
        ];

        Log::info('[EmployeeDashboard] today attendance', [
            'user_id' => $user->id,
            'date' => $today->toDateString(),
            'attendance_id' => $attendance ? $attendance->id : null,
            'payload' => $payload,
        ]);

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }
    
    /**
     * Show leave requests page
     */
    public function leaveRequests(): View
    {
        $user = Auth::user();
        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->latest()
            ->paginate(10);
            
        $leaveBalance = $this->calculateLeaveBalance($user->id);
        
        return view('employee.leave-requests', compact('leaveRequests', 'leaveBalance'));
    }
    
    /**
     * Store a new leave request
     */
    public function storeLeaveRequest(Request $request)
    {
        $user = Auth::user();
        
        $validatedData = $request->validate([
            'leave_type' => 'required|string|in:annual_leave,sick_leave,emergency_leave,maternity_paternity_leave,personal_leave',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);
        
        // Calculate days requested
        $startDate = Carbon::parse($validatedData['start_date']);
        $endDate = Carbon::parse($validatedData['end_date']);
        $daysRequested = $startDate->diffInDays($endDate) + 1;
        
        // Create leave request
        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => $validatedData['leave_type'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_requested' => $daysRequested,
            'reason' => $validatedData['reason'],
            'status' => 'pending',
        ]);
        
        return redirect()->route('employee.leave-requests')
            ->with('success', 'Leave request submitted successfully!');
    }
    
    /**
     * Cancel/delete a leave request
     */
    public function destroyLeaveRequest(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        
        // Ensure the user owns this request and it's still pending
        if ($leaveRequest->user_id !== $user->id) {
            return redirect()->route('employee.leave-requests')
                ->with('error', 'Unauthorized action.');
        }
        
        if ($leaveRequest->status !== 'pending') {
            return redirect()->route('employee.leave-requests')
                ->with('error', 'Cannot cancel a request that has already been processed.');
        }
        
        $leaveRequest->delete();
        
        return redirect()->route('employee.leave-requests')
            ->with('success', 'Leave request cancelled successfully.');
    }
    
    /**
     * Show shift requests page
     */
    public function shiftRequests(): View
    {
        $user = Auth::user();
        
        // Get shift requests separated by status
        $pendingShiftRequests = ShiftRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->paginate(10, ['*'], 'pending');
            
        $approvedShiftRequests = ShiftRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->paginate(10, ['*'], 'approved');
            
        $rejectedShiftRequests = ShiftRequest::where('user_id', $user->id)
            ->where('status', 'rejected')
            ->latest()
            ->paginate(10, ['*'], 'rejected');
        
        return view('employee.shift-requests', compact(
            'pendingShiftRequests', 'approvedShiftRequests', 'rejectedShiftRequests'
        ));
    }
    
    /**
     * Store a new shift request
     */
    public function storeShiftRequest(Request $request)
    {
        $user = Auth::user();
        
        $validatedData = $request->validate([
            'request_type' => 'required|string|in:swap,cover,overtime,schedule_change',
            'requested_date' => 'required|date|after_or_equal:today',
            'current_start_time' => 'nullable|date_format:H:i',
            'current_end_time' => 'nullable|date_format:H:i',
            'requested_start_time' => 'nullable|date_format:H:i',
            'requested_end_time' => 'nullable|date_format:H:i',
            'swap_with_user_id' => 'nullable|exists:users,id',
            'reason' => 'required|string|max:1000',
        ]);
        
        // Prepare data for insertion
        $data = [
            'user_id' => $user->id,
            'request_type' => $validatedData['request_type'],
            'requested_date' => $validatedData['requested_date'],
            'current_start_time' => $validatedData['current_start_time'] ?? null,
            'current_end_time' => $validatedData['current_end_time'] ?? null,
            'requested_start_time' => $validatedData['requested_start_time'] ?? null,
            'requested_end_time' => $validatedData['requested_end_time'] ?? null,
            'swap_with_user_id' => $validatedData['swap_with_user_id'] ?? null,
            'reason' => $validatedData['reason'],
            'status' => 'pending',
        ];
        
        ShiftRequest::create($data);
        
        return redirect()->route('employee.shift-requests')
            ->with('success', 'Shift request submitted successfully!');
    }
    
    /**
     * Cancel/delete a shift request
     */
    public function destroyShiftRequest(ShiftRequest $shiftRequest)
    {
        $user = Auth::user();
        
        // Ensure the user owns this request and it's still pending
        if ($shiftRequest->user_id !== $user->id) {
            return redirect()->route('employee.shift-requests')
                ->with('error', 'Unauthorized action.');
        }
        
        if ($shiftRequest->status !== 'pending') {
            return redirect()->route('employee.shift-requests')
                ->with('error', 'Cannot cancel a request that has already been processed.');
        }
        
        $shiftRequest->delete();
        
        return redirect()->route('employee.shift-requests')
            ->with('success', 'Shift request cancelled successfully.');
    }
    
    /**
     * Show reimbursements page
     */
    public function reimbursements(): View
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        // Get real reimbursement data
        $reimbursements = Reimbursement::where('user_id', $user->id)
            ->when($employee, function($query, $employee) {
                return $query->orWhere('employee_id', $employee->id);
            })
            ->with(['user', 'employee', 'approver'])
            ->latest('submitted_date')
            ->paginate(15);
        
        return view('employee.reimbursements', compact('reimbursements'));
    }
    
    /**
     * Show payroll page
     */
    public function payroll(PayrollService $payrollService): View
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        // Get payslips from external service or fallback to mock data
        $employeeId = $employee ? $employee->id : $user->id;
        $payslips = collect($payrollService->getEmployeePayslips($employeeId, 12));
        
        return view('employee.payroll', compact('payslips'));
    }
    
    /**
     * Show profile management page
     */
    public function profile(): View
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        return view('employee.profile', compact('user', 'employee'));
    }
    
    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|confirmed',
        ]);
        
        // Update user fields
        $user->name = $validatedData['name'];
        $user->lastname = $validatedData['lastname'];
        $user->email = $validatedData['email'];
        $user->phone = $validatedData['phone'] ?? null;
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                \Storage::disk('public')->delete($user->photo);
            }
            
            $path = $request->file('photo')->store('profile-photos', 'public');
            $user->photo = $path;
        }
        
        // Handle password change
        if ($request->filled('password')) {
            // Verify current password
            if (!\Hash::check($validatedData['current_password'], $user->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'Current password is incorrect.'])
                    ->withInput();
            }
            
            $user->password = \Hash::make($validatedData['password']);
        }
        
        $user->save();
        
        // Update employee fields if employee profile exists
        if ($employee) {
            $employee->emergency_contact_name = $validatedData['emergency_contact_name'] ?? null;
            $employee->emergency_contact_phone = $validatedData['emergency_contact_phone'] ?? null;
            $employee->save();
        }
        
        return redirect()->route('employee.profile')
            ->with('success', 'Profile updated successfully!');
    }
    
    /**
     * Show attendance analytics
     */
    public function attendance(): View
    {
        $user = Auth::user();
        $employee = $user->employee;
        $currentMonth = Carbon::now();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        
        // Get attendance data for current month
        $attendanceData = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'desc')
            ->get();
            
        // Calculate real performance statistics
        $totalWorkingDays = $startOfMonth->diffInDaysFiltered(function ($date) {
            return $date->isWeekday();
        }, $endOfMonth);
        
        $attendedDays = $attendanceData->where('status', 'present')->count();
        $lateDays = $attendanceData->where('is_late', true)->count();
        $overtimeHours = $attendanceData->sum('overtime_hours') ?? 0;
        
        // Calculate leave utilization for the year
        $totalLeaveEntitlement = 25;
        $usedLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentMonth->year)
            ->sum('days_requested');
            
        $performanceStats = [
            'attendance_rate' => $totalWorkingDays > 0 ? round(($attendedDays / $totalWorkingDays) * 100, 1) : 0,
            'punctuality_rate' => $attendedDays > 0 ? round((($attendedDays - $lateDays) / $attendedDays) * 100, 1) : 0,
            'overtime_hours' => $overtimeHours,
            'leave_utilization' => round(($usedLeave / $totalLeaveEntitlement) * 100, 1),
            'productivity_score' => $this->calculateProductivityScore($attendanceData, $totalWorkingDays)
        ];
        
        return view('employee.attendance', compact('attendanceData', 'performanceStats'));
    }
    
    /**
     * Show work schedule
     */
    public function workSchedule(): View
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        // Get real shift assignments from shift management system
        $schedule = collect();
        
        if (class_exists('App\Models\ShiftAssignment')) {
            // ShiftAssignment requires employee_id
            $employeeId = $employee ? $employee->id : null;
            
            if ($employeeId) {
                $schedule = \App\Models\ShiftAssignment::query()
                    ->where('employee_id', $employeeId)
                    ->with(['shiftTemplate'])
                    ->whereDate('assignment_date', '>=', Carbon::now())
                    ->whereDate('assignment_date', '<=', Carbon::now()->addDays(14))
                    ->orderBy('assignment_date')
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'date' => $assignment->assignment_date,
                            'shift' => $assignment->shiftTemplate->name ?? 'Regular',
                            'time' => ($assignment->shiftTemplate->start_time ?? '9:00') . ' - ' . ($assignment->shiftTemplate->end_time ?? '17:00'),
                            'location' => 'Office', // Default location as it's not in the table
                            'status' => $assignment->status ?? 'scheduled'
                        ];
                    });
            }
        }
        
        // Fallback to mock data if no shift management system or no employee profile
        if ($schedule->isEmpty()) {
            for ($i = 1; $i <= 7; $i++) {
                $schedule->push([
                    'date' => Carbon::now()->addDays($i),
                    'shift' => 'Morning',
                    'time' => '9:00 AM - 5:00 PM',
                    'location' => 'Office',
                    'status' => 'scheduled'
                ]);
            }
        }
        
        return view('employee.work-schedule', compact('schedule'));
    }
    
    /**
     * Calculate leave balance for employee
     */
    private function calculateLeaveBalance($userId): array
    {
        $totalLeaveEntitlement = 25; // Annual leave entitlement
        $usedLeave = LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->sum('days_requested');
            
        return [
            'total' => $totalLeaveEntitlement,
            'used' => $usedLeave,
            'remaining' => $totalLeaveEntitlement - $usedLeave
        ];
    }
    
    /**
     * Calculate productivity score based on attendance data
     */
    private function calculateProductivityScore($attendanceData, $totalWorkingDays): float
    {
        if ($totalWorkingDays == 0) {
            return 0;
        }
        
        $attendedDays = $attendanceData->where('status', 'present')->count();
        $lateDays = $attendanceData->where('status', 'late')->count();
        $absentDays = $attendanceData->where('status', 'absent')->count();
        $overtimeDays = $attendanceData->where('overtime_hours', '>', 0)->count();
        
        // Base score from attendance rate
        $baseScore = ($attendedDays / $totalWorkingDays) * 10;
        
        // Deduct points for lateness and absences
        $punctualityPenalty = (($lateDays + $absentDays) / max($totalWorkingDays, 1)) * 2;
        
        // Add bonus for overtime commitment
        $overtimeBonus = ($overtimeDays / max($attendedDays, 1)) * 0.5;
        
        $finalScore = max(0, min(10, $baseScore - $punctualityPenalty + $overtimeBonus));
        
        return round($finalScore, 1);
    }
    
    /**
     * Calculate detailed leave balance by type
     */
    private function calculateDetailedLeaveBalance($userId): array
    {
        $leaveTypes = [
            'annual_leave' => ['total' => 15, 'used' => 0],
            'sick_leave' => ['total' => 7, 'used' => 0],
            'emergency_leave' => ['total' => 3, 'used' => 0],
        ];
        
        // Calculate used leave per type
        $usedLeaves = LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->selectRaw('leave_type, SUM(days_requested) as total_days')
            ->groupBy('leave_type')
            ->get();
        
        foreach ($usedLeaves as $leave) {
            if (isset($leaveTypes[$leave->leave_type])) {
                $leaveTypes[$leave->leave_type]['used'] = $leave->total_days;
            }
        }
        
        // Calculate remaining
        foreach ($leaveTypes as $type => $balance) {
            $leaveTypes[$type]['remaining'] = $balance['total'] - $balance['used'];
        }
        
        return $leaveTypes;
    }
    
    /**
     * Calculate total hours worked for the week
     */
    private function calculateWeekHours($userId, $startOfWeek, $endOfWeek): float
    {
        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->get();
        
        $totalHours = 0;
        foreach ($attendances as $attendance) {
            if ($attendance->clock_in && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = Carbon::parse($attendance->clock_out);
                $totalHours += $clockIn->diffInHours($clockOut);
            } else {
                // Default 8 hours if no clock times
                $totalHours += 8;
            }
        }
        
        return round($totalHours, 1);
    }
}
