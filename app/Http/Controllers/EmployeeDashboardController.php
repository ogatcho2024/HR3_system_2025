<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
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
        
        // Get current month data
        $currentMonth = Carbon::now();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        
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
            'current_month' => $currentMonth->format('F Y'),
        ];
        
        // Recent activities from real data
        $recentActivities = collect();
        
        // Add recent leave requests
        $recentLeaveRequests = LeaveRequest::where('user_id', $user->id)
            ->latest()
            ->take(3)
            ->get();
            
        foreach ($recentLeaveRequests as $request) {
            $recentActivities->push([
                'type' => 'leave',
                'title' => 'Leave Request ' . ucfirst($request->status),
                'description' => ucfirst(str_replace('_', ' ', $request->leave_type)) . ' leave for ' . $request->days_requested . ' day(s)',
                'date' => $request->created_at,
                'status' => $request->status,
                'icon' => 'calendar'
            ]);
        }
        
        // Add recent shift requests
        $recentShiftRequests = ShiftRequest::where('user_id', $user->id)
            ->latest()
            ->take(2)
            ->get();
            
        foreach ($recentShiftRequests as $request) {
            $recentActivities->push([
                'type' => 'shift',
                'title' => 'Shift Request ' . ucfirst($request->status),
                'description' => ucfirst(str_replace('_', ' ', $request->request_type)) . ' request for ' . $request->request_date->format('M j'),
                'date' => $request->created_at,
                'status' => $request->status,
                'icon' => 'clock'
            ]);
        }
        
        // Add recent attendance if available
        $recentAttendance = Attendance::where('user_id', $user->id)
            ->latest('date')
            ->first();
                
        if ($recentAttendance) {
            $recentActivities->push([
                'type' => 'attendance',
                'title' => 'Attendance Recorded',
                'description' => 'Status: ' . ucfirst($recentAttendance->status) . ' on ' . $recentAttendance->date->format('M j'),
                'date' => $recentAttendance->created_at,
                'status' => $recentAttendance->status,
                'icon' => 'clock'
            ]);
        }
        
        // Sort by date and take the most recent 5
        $recentActivities = $recentActivities->sortByDesc('date')->take(5);
        
        return view('employee.dashboard', compact('user', 'employee', 'stats', 'recentActivities'));
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
            // Try employee_id first, then fallback to user_id
            $employeeId = $employee ? $employee->id : null;
            $query = \App\Models\ShiftAssignment::query();
            
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            } else {
                $query->where('user_id', $user->id);
            }
            
            $schedule = $query
                ->with(['shiftTemplate'])
                ->whereDate('shift_date', '>=', Carbon::now())
                ->whereDate('shift_date', '<=', Carbon::now()->addDays(14))
                ->orderBy('shift_date')
                ->get()
                ->map(function ($assignment) {
                    return [
                        'date' => $assignment->shift_date,
                        'shift' => $assignment->shiftTemplate->name ?? 'Regular',
                        'time' => ($assignment->shiftTemplate->start_time ?? '9:00') . ' - ' . ($assignment->shiftTemplate->end_time ?? '17:00'),
                        'location' => $assignment->location ?? 'Office',
                        'status' => $assignment->status ?? 'scheduled'
                    ];
                });
        }
        
        // Fallback to mock data if no shift management system
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
}
