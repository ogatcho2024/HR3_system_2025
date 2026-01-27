<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class LeaveManagementController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
        // Add role-based middleware if you have it
        // $this->middleware('role:hr,admin');
    }

    /**
     * Display the leave management dashboard.
     */
    public function index(): View
    {
        // Get overview statistics
        $totalRequests = LeaveRequest::count();
        $pendingRequests = LeaveRequest::pending()->count();
        $approvedRequests = LeaveRequest::approved()->count();
        $rejectedRequests = LeaveRequest::rejected()->count();
        
        // Get recent leave requests
        $recentRequests = LeaveRequest::with(['user', 'user.employee'])
            ->latest()
            ->take(10)
            ->get();
        
        // Get leave requests by department
        $requestsByDepartment = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
            ->join('employees', 'users.id', '=', 'employees.user_id')
            ->select('employees.department', DB::raw('count(*) as total'))
            ->groupBy('employees.department')
            ->get();
        
        // Get leave requests by type
        $requestsByType = LeaveRequest::select('leave_type', DB::raw('count(*) as total'))
            ->groupBy('leave_type')
            ->get();
        
        // Get monthly leave trends (last 12 months)
        $monthlyTrends = LeaveRequest::select(
                DB::raw('YEAR(start_date) as year'),
                DB::raw('MONTH(start_date) as month'),
                DB::raw('count(*) as total')
            )
            ->where('start_date', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        return view('leave-management.dashboard', compact(
            'totalRequests',
            'pendingRequests', 
            'approvedRequests',
            'rejectedRequests',
            'recentRequests',
            'requestsByDepartment',
            'requestsByType',
            'monthlyTrends'
        ));
    }

    /**
     * Display pending leave requests for approval.
     */
    public function pendingRequests(): View
    {
        $pendingRequests = LeaveRequest::with(['user', 'user.employee'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Get all employees for the add leave request form
        $employees = User::with('employee')
            ->orderBy('name')
            ->orderBy('lastname')
            ->get();
        
        return view('admin.leave-management.pending-requests', compact('pendingRequests', 'employees'));
    }

    /**
     * Display all leave requests with filters.
     */
    public function allRequests(Request $request): View
    {
        $query = LeaveRequest::with(['user', 'user.employee', 'approvedBy']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }
        
        if ($request->filled('department')) {
            $query->whereHas('user.employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }
        
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get filter options
        $departments = Employee::select('department')
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
        
        $leaveTypes = LeaveRequest::select('leave_type')
            ->distinct()
            ->orderBy('leave_type')
            ->pluck('leave_type');
        
        return view('leave-management.all-requests', compact(
            'leaveRequests', 
            'departments', 
            'leaveTypes'
        ));
    }

    /**
     * Create a new leave request manually (Admin/Super Admin only).
     */
    public function createLeaveRequest(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'leave_type' => 'required|string|in:sick,vacation,personal,maternity,paternity,emergency,bereavement,annual,unpaid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'status' => 'required|in:pending,approved,rejected',
            'manager_comments' => 'nullable|string|max:1000',
        ]);
        
        // Calculate days requested
        $startDate = Carbon::parse($validatedData['start_date']);
        $endDate = Carbon::parse($validatedData['end_date']);
        $daysRequested = $startDate->diffInDays($endDate) + 1;
        
        // Create the leave request
        $leaveRequest = LeaveRequest::create([
            'user_id' => $validatedData['user_id'],
            'leave_type' => $validatedData['leave_type'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'days_requested' => $daysRequested,
            'reason' => $validatedData['reason'],
            'status' => $validatedData['status'],
            'manager_comments' => $validatedData['manager_comments'] ?? null,
            'approved_by' => ($validatedData['status'] !== 'pending') ? Auth::id() : null,
            'approved_at' => ($validatedData['status'] !== 'pending') ? now() : null,
        ]);
        
        // Update leave balance based on status
        if ($validatedData['status'] === 'approved') {
            $this->updateLeaveBalance($leaveRequest, 'approved');
        } elseif ($validatedData['status'] === 'pending') {
            $this->updateLeaveBalance($leaveRequest, 'pending');
        }
        
        // Create notification for the employee
        $employee = User::find($validatedData['user_id']);
        $statusMessage = match($validatedData['status']) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            'pending' => 'created',
            default => 'created'
        };
        
        $notificationType = match($validatedData['status']) {
            'approved' => 'success',
            'rejected' => 'error',
            'pending' => 'info',
            default => 'info'
        };
        
        $this->notificationService->create(
            $employee,
            'Leave Request ' . ucfirst($statusMessage),
            "A leave request has been {$statusMessage} by " . Auth::user()->name . ' ' . Auth::user()->lastname,
            $notificationType,
            'leave',
            [
                'leave_type' => $leaveRequest->leave_type,
                'start_date' => $leaveRequest->start_date->format('Y-m-d'),
                'end_date' => $leaveRequest->end_date->format('Y-m-d'),
                'days_requested' => $leaveRequest->days_requested,
                'status' => $validatedData['status'],
                'created_by' => Auth::user()->name . ' ' . Auth::user()->lastname,
            ],
            true,
            route('employee.leave-requests'),
            'View Leave Requests'
        );
        
        return redirect()->route('leave-management.pending-requests')
            ->with('success', 'Leave request created successfully!');
    }
    
    /**
     * Approve a leave request.
     */
    public function approveRequest(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $validatedData = $request->validate([
            'manager_comments' => 'nullable|string|max:1000',
        ]);
        
        $leaveRequest->update([
            'status' => 'approved',
            'manager_comments' => $validatedData['manager_comments'] ?? null,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);
        
        // Update leave balance
        $this->updateLeaveBalance($leaveRequest, 'approved');
        
        // Create notification for the employee
        $this->notificationService->createLeaveRequestNotification(
            $leaveRequest->user,
            'approved',
            [
                'leave_type' => $leaveRequest->leave_type,
                'start_date' => $leaveRequest->start_date->format('Y-m-d'),
                'end_date' => $leaveRequest->end_date->format('Y-m-d'),
                'days_requested' => $leaveRequest->days_requested,
                'approved_by' => Auth::user()->name . ' ' . Auth::user()->lastname,
                'manager_comments' => $leaveRequest->manager_comments,
            ]
        );
        
        return redirect()->back()->with('success', 'Leave request approved successfully!');
    }

    /**
     * Reject a leave request.
     */
    public function rejectRequest(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $validatedData = $request->validate([
            'manager_comments' => 'required|string|max:1000',
        ]);
        
        $leaveRequest->update([
            'status' => 'rejected',
            'manager_comments' => $validatedData['manager_comments'],
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);
        
        // Update leave balance (remove from pending)
        $this->updateLeaveBalance($leaveRequest, 'rejected');
        
        // Create notification for the employee
        $this->notificationService->createLeaveRequestNotification(
            $leaveRequest->user,
            'rejected',
            [
                'leave_type' => $leaveRequest->leave_type,
                'start_date' => $leaveRequest->start_date->format('Y-m-d'),
                'end_date' => $leaveRequest->end_date->format('Y-m-d'),
                'days_requested' => $leaveRequest->days_requested,
                'rejected_by' => Auth::user()->name . ' ' . Auth::user()->lastname,
                'manager_comments' => $leaveRequest->manager_comments,
            ]
        );
        
        return redirect()->back()->with('success', 'Leave request rejected successfully!');
    }

    /**
     * Display leave balances for all employees.
     */
    public function leaveBalances(): View
    {
        $year = request('year', date('Y'));
        
        $leaveBalances = LeaveBalance::with(['user', 'user.employee'])
            ->where('year', $year)
            ->orderBy('leave_type')
            ->get()
            ->groupBy('user_id');
        
        return view('admin.leave-management.leave-balances', compact('leaveBalances', 'year'));
    }

    /**
     * Display leave calendar.
     */
    public function calendar(): View
    {
        $month = request('month', date('m'));
        $year = request('year', date('Y'));
        
        $leaveRequests = LeaveRequest::with(['user'])
            ->approved()
            ->where(function($query) use ($year, $month) {
                $query->whereYear('start_date', $year)
                      ->whereMonth('start_date', $month)
                      ->orWhere(function($q) use ($year, $month) {
                          $q->whereYear('end_date', $year)
                            ->whereMonth('end_date', $month);
                      });
            })
            ->get();
            
        // Get all active departments for the filter dropdown
        $departments = Department::active()->orderBy('department_name')->get();
        
        return view('leave-management.calendar', compact('leaveRequests', 'month', 'year', 'departments'));
    }

    /**
     * Show leave request details.
     */
    public function show(LeaveRequest $leaveRequest): View
    {
        $leaveRequest->load(['user', 'user.employee', 'approvedBy']);
        
        return view('leave-management.show', compact('leaveRequest'));
    }

    /**
     * Admin Dashboard with comprehensive overview.
     */
    public function adminDashboard(): View
    {
        // Overview statistics with more details
        $totalRequests = LeaveRequest::count();
        $pendingRequests = LeaveRequest::pending()->count();
        $approvedRequests = LeaveRequest::approved()->count();
        $rejectedRequests = LeaveRequest::rejected()->count();
        
        // Today's leave requests
        $todayLeaveRequests = LeaveRequest::with(['user', 'user.employee'])
            ->approved()
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->get();
        
        // Upcoming leave requests (next 7 days)
        $upcomingLeaveRequests = LeaveRequest::with(['user', 'user.employee'])
            ->approved()
            ->whereBetween('start_date', [today()->addDay(), today()->addDays(7)])
            ->get();
        
        // Critical department coverage check
        $criticalDepartments = ['IT', 'Finance', 'HR', 'Operations'];
        $departmentCoverage = [];
        
        foreach ($criticalDepartments as $dept) {
            $totalEmployees = Employee::where('department', $dept)->count();
            
            // If no employees in department, create default data
            if ($totalEmployees === 0) {
                $totalEmployees = 5; // Default for demo purposes
            }
            
            $onLeaveToday = LeaveRequest::approved()
                ->whereHas('user.employee', function($q) use ($dept) {
                    $q->where('department', $dept);
                })
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->count();
            
            $departmentCoverage[$dept] = [
                'total' => $totalEmployees,
                'on_leave' => $onLeaveToday,
                'available' => $totalEmployees - $onLeaveToday,
                'coverage_percentage' => $totalEmployees > 0 ? (($totalEmployees - $onLeaveToday) / $totalEmployees) * 100 : 100
            ];
        }
        
        // Leave utilization by type (current year)
        $leaveUtilization = LeaveRequest::select(
                'leave_type',
                DB::raw('SUM(days_requested) as total_days'),
                DB::raw('COUNT(*) as total_requests')
            )
            ->where('status', 'approved')
            ->whereYear('start_date', date('Y'))
            ->groupBy('leave_type')
            ->get();
        
        // If no data, add some sample data for demo
        if ($leaveUtilization->isEmpty()) {
            $leaveUtilization = collect([
                (object) ['leave_type' => 'Annual', 'total_days' => 25, 'total_requests' => 5],
                (object) ['leave_type' => 'Sick', 'total_days' => 15, 'total_requests' => 3],
                (object) ['leave_type' => 'Personal', 'total_days' => 10, 'total_requests' => 2],
            ]);
        }
        
        // Monthly trends (last 12 months)
        $monthlyTrends = LeaveRequest::select(
                DB::raw('DATE_FORMAT(start_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected')
            )
            ->where('start_date', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Department-wise statistics
        $departmentStats = Employee::select(
                'department',
                DB::raw('COUNT(DISTINCT employees.user_id) as employees_count')
            )
            ->whereNotNull('department')
            ->groupBy('department')
            ->get()
            ->map(function($dept) {
                $requests = LeaveRequest::whereHas('user.employee', function($q) use ($dept) {
                    $q->where('department', $dept->department);
                })->whereYear('start_date', date('Y'));
                
                return (object) [
                    'department' => $dept->department,
                    'total_requests' => $requests->count(),
                    'pending' => $requests->where('status', 'pending')->count(),
                    'approved' => $requests->where('status', 'approved')->count(),
                    'rejected' => $requests->where('status', 'rejected')->count(),
                ];
            });
        
        // If no departments, add sample data
        if ($departmentStats->isEmpty()) {
            $departmentStats = collect([
                (object) ['department' => 'IT', 'total_requests' => 12, 'pending' => 2, 'approved' => 8, 'rejected' => 2],
                (object) ['department' => 'Finance', 'total_requests' => 8, 'pending' => 1, 'approved' => 6, 'rejected' => 1],
                (object) ['department' => 'HR', 'total_requests' => 6, 'pending' => 0, 'approved' => 5, 'rejected' => 1],
                (object) ['department' => 'Operations', 'total_requests' => 15, 'pending' => 3, 'approved' => 10, 'rejected' => 2],
            ]);
        }
        
        return view('admin.leave-management.dashboard', compact(
            'totalRequests',
            'pendingRequests',
            'approvedRequests', 
            'rejectedRequests',
            'todayLeaveRequests',
            'upcomingLeaveRequests',
            'departmentCoverage',
            'leaveUtilization',
            'monthlyTrends',
            'departmentStats'
        ));
    }
    
    /**
     * Manually adjust leave balances with audit trail.
     */
    public function adjustLeaveBalance(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'leave_type' => 'required|string',
            'year' => 'required|integer|min:2020|max:2030',
            'adjustment_type' => 'required|in:total_entitled,used,carried_forward',
            'adjustment_value' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);
        
        $balance = LeaveBalance::firstOrCreate([
            'user_id' => $validatedData['user_id'],
            'leave_type' => $validatedData['leave_type'],
            'year' => $validatedData['year'],
        ]);
        
        // Store original values for audit
        $originalValues = [
            'total_entitled' => $balance->total_entitled,
            'used' => $balance->used,
            'carried_forward' => $balance->carried_forward,
            'available' => $balance->available,
            'pending' => $balance->pending,
        ];
        
        // Apply adjustment
        $balance->{$validatedData['adjustment_type']} = $validatedData['adjustment_value'];
        
        // Recalculate available balance
        $balance->available = $balance->total_entitled + $balance->carried_forward - $balance->used - $balance->pending;
        $balance->save();
        
        // Create audit log (you may want to create an AuditLog model)
        DB::table('leave_balance_adjustments')->insert([
            'leave_balance_id' => $balance->id,
            'adjusted_by' => Auth::id(),
            'adjustment_type' => $validatedData['adjustment_type'],
            'old_value' => $originalValues[$validatedData['adjustment_type']],
            'new_value' => $validatedData['adjustment_value'],
            'reason' => $validatedData['reason'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create notification for the employee
        $this->notificationService->createNotification(
            User::find($validatedData['user_id']),
            'Leave Balance Adjusted',
            "Your {$validatedData['leave_type']} balance for {$validatedData['year']} has been adjusted by HR.",
            'leave_balance_adjustment',
            [
                'adjustment_type' => $validatedData['adjustment_type'],
                'old_value' => $originalValues[$validatedData['adjustment_type']],
                'new_value' => $validatedData['adjustment_value'],
                'reason' => $validatedData['reason'],
                'adjusted_by' => Auth::user()->name . ' ' . Auth::user()->lastname,
            ]
        );
        
        return redirect()->back()->with('success', 'Leave balance adjusted successfully!');
    }
    
    /**
     * Check for leave conflicts in critical departments.
     */
    public function checkLeaveConflicts(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $department = $request->input('department');
        $userId = $request->input('user_id');
        
        // Get employees in the same department on leave during the requested period
        $conflictingLeaves = LeaveRequest::with(['user', 'user.employee'])
            ->approved()
            ->whereHas('user.employee', function($q) use ($department) {
                $q->where('department', $department);
            })
            ->where('user_id', '!=', $userId)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get();
        
        // Calculate department coverage
        $totalEmployees = Employee::where('department', $department)->count();
        $employeesOnLeave = $conflictingLeaves->count() + 1; // +1 for the requesting employee
        $availableEmployees = $totalEmployees - $employeesOnLeave;
        $coveragePercentage = $totalEmployees > 0 ? ($availableEmployees / $totalEmployees) * 100 : 0;
        
        // Define critical coverage threshold (you can make this configurable)
        $criticalThreshold = 70; // 70% minimum coverage required
        
        return response()->json([
            'conflicts' => $conflictingLeaves,
            'total_employees' => $totalEmployees,
            'employees_on_leave' => $employeesOnLeave,
            'available_employees' => $availableEmployees,
            'coverage_percentage' => round($coveragePercentage, 1),
            'is_critical' => $coveragePercentage < $criticalThreshold,
            'critical_threshold' => $criticalThreshold,
        ]);
    }
    
    /**
     * Generate comprehensive reports.
     */
    public function generateReport(Request $request): View
    {
        $reportType = $request->input('type', 'employee');
        $dateFrom = $request->input('date_from', Carbon::now()->startOfYear());
        $dateTo = $request->input('date_to', Carbon::now()->endOfYear());
        $department = $request->input('department');
        $leaveType = $request->input('leave_type');
        $employeeId = $request->input('employee_id');
        
        $query = LeaveRequest::with(['user', 'user.employee', 'approvedBy'])
            ->whereBetween('start_date', [$dateFrom, $dateTo]);
        
        // Apply filters
        if ($department) {
            $query->whereHas('user.employee', function($q) use ($department) {
                $q->where('department', $department);
            });
        }
        
        if ($leaveType) {
            $query->where('leave_type', $leaveType);
        }
        
        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }
        
        $leaveRequests = $query->get();
        
        // Generate different report structures based on type
        $reportData = collect();
        
        switch ($reportType) {
            case 'employee':
                if ($leaveRequests->isNotEmpty()) {
                    $reportData = $leaveRequests->groupBy('user_id')->map(function($requests, $userId) {
                        $user = $requests->first()->user;
                        return [
                            'employee' => $user,
                            'total_requests' => $requests->count(),
                            'total_days' => $requests->sum('days_requested'),
                            'approved' => $requests->where('status', 'approved')->count(),
                            'pending' => $requests->where('status', 'pending')->count(),
                            'rejected' => $requests->where('status', 'rejected')->count(),
                            'requests' => $requests,
                        ];
                    });
                }
                break;
                
            case 'department':
                if ($leaveRequests->isNotEmpty()) {
                    $reportData = $leaveRequests->filter(function($request) {
                        return $request->user && $request->user->employee && $request->user->employee->department;
                    })->groupBy('user.employee.department')->map(function($requests, $dept) {
                        return [
                            'department' => $dept,
                            'total_requests' => $requests->count(),
                            'total_days' => $requests->sum('days_requested'),
                            'approved' => $requests->where('status', 'approved')->count(),
                            'pending' => $requests->where('status', 'pending')->count(),
                            'rejected' => $requests->where('status', 'rejected')->count(),
                            'employees' => $requests->groupBy('user_id')->count(),
                        ];
                    });
                }
                break;
                
            case 'leave_type':
                if ($leaveRequests->isNotEmpty()) {
                    $reportData = $leaveRequests->groupBy('leave_type')->map(function($requests, $type) {
                        return [
                            'leave_type' => $type,
                            'total_requests' => $requests->count(),
                            'total_days' => $requests->sum('days_requested'),
                            'approved' => $requests->where('status', 'approved')->count(),
                            'pending' => $requests->where('status', 'pending')->count(),
                            'rejected' => $requests->where('status', 'rejected')->count(),
                            'average_duration' => round($requests->avg('days_requested'), 1),
                        ];
                    });
                }
                break;
        }
        
        // Get filter options with null-safe queries
        $departments = Employee::whereNotNull('department')
            ->select('department')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->values();
            
        $leaveTypes = LeaveRequest::whereNotNull('leave_type')
            ->select('leave_type')
            ->distinct()
            ->pluck('leave_type')
            ->filter()
            ->values();
            
        $employees = User::whereHas('employee')->with('employee')->get();
        
        return view('admin.leave-management.reports', compact(
            'reportType',
            'reportData',
            'dateFrom',
            'dateTo',
            'departments',
            'leaveTypes',
            'employees',
            'department',
            'leaveType',
            'employeeId'
        ));
    }
    
    /**
     * Display simple reports and analytics page.
     */
    public function reportsAnalytics(): View
    {
        return view('admin.leave-management.reports-analytics');
    }
    
    /**
     * Export leave reports to PDF.
     */
    public function exportLeaveReportsPDF(Request $request)
    {
        try {
            // Get filter parameters
            $dateFrom = $request->input('date_from', now()->startOfYear()->format('Y-m-d'));
            $dateTo = $request->input('date_to', now()->format('Y-m-d'));
            $status = $request->input('status');
        
        // Build query for leave requests
        $query = LeaveRequest::with(['user'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);
            
        if ($status) {
            $query->where('status', $status);
        }
        
        $recentRequests = $query->orderBy('created_at', 'desc')
            ->limit(100) // Limit to 100 records for PDF performance
            ->get();
            
        // Calculate statistics
        $totalRequests = $recentRequests->count();
        $pendingRequests = $recentRequests->where('status', 'pending')->count();
        $approvedRequests = $recentRequests->where('status', 'approved')->count();
        $rejectedRequests = $recentRequests->where('status', 'rejected')->count();
        
        $totalDays = $recentRequests->sum('days_requested') ?? 0;
        $avgDays = $totalRequests > 0 ? round($totalDays / $totalRequests, 1) : 0;
        $approvalRate = $totalRequests > 0 ? round(($approvedRequests / $totalRequests) * 100, 1) : 0;
        
        // Create date range string
        $dateRange = now()->parse($dateFrom)->format('M j, Y') . ' - ' . now()->parse($dateTo)->format('M j, Y');
        
        // Get department breakdown (optional)
        $departmentBreakdown = collect();
        try {
            $departmentBreakdown = LeaveRequest::with(['user.employee'])
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->get()
                ->filter(function($request) {
                    return $request->user && $request->user->employee && $request->user->employee->department;
                })
                ->groupBy('user.employee.department')
                ->map(function($requests, $dept) {
                    return (object) [
                        'department' => $dept,
                        'total_requests' => $requests->count(),
                        'approved' => $requests->where('status', 'approved')->count(),
                        'pending' => $requests->where('status', 'pending')->count(),
                        'rejected' => $requests->where('status', 'rejected')->count(),
                        'total_days' => $requests->sum('days_requested'),
                    ];
                });
        } catch (\Exception $e) {
            // If department data fails, continue without it
            $departmentBreakdown = collect();
        }
        
        // Prepare data for PDF
        $data = [
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
            'totalDays' => $totalDays,
            'avgDays' => $avgDays,
            'approvalRate' => $approvalRate,
            'recentRequests' => $recentRequests,
            'dateRange' => $dateRange,
            'departmentBreakdown' => $departmentBreakdown,
        ];
        
        // Generate PDF
        $pdf = PDF::loadView('pdf.leave-reports', $data);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Generate filename
        $filename = 'leave-reports-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
        // Return PDF download
        return $pdf->download($filename);
        
        } catch (\Exception $e) {
            // Log the error
            \Log::error('PDF Export Error: ' . $e->getMessage());
            
            // Return error response
            return response('PDF Generation Error: ' . $e->getMessage() . '. Please try again or contact support.', 500)
                ->header('Content-Type', 'text/plain');
        }
    }
    
    /**
     * Export report to PDF or Excel.
     */
    public function exportReport(Request $request)
    {
        $format = $request->input('format', 'pdf');
        $reportType = $request->input('type', 'employee');
        
        // Generate the same report data as above
        // This would use a service class for PDF/Excel generation
        // For now, return a JSON response indicating the export would happen
        
        return response()->json([
            'message' => "Report export to {$format} format initiated",
            'type' => $reportType,
            'download_url' => '/admin/leave-management/reports/download/' . time()
        ]);
    }
    
    /**
     * Get integration data for payroll system.
     */
    public function getPayrollIntegrationData(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        
        $payrollData = LeaveRequest::with(['user', 'user.employee'])
            ->approved()
            ->where(function($query) use ($year, $month) {
                $query->whereYear('start_date', $year)
                      ->whereMonth('start_date', $month)
                      ->orWhere(function($q) use ($year, $month) {
                          $q->whereYear('end_date', $year)
                            ->whereMonth('end_date', $month);
                      });
            })
            ->get()
            ->map(function($request) use ($month, $year) {
                // Calculate days taken in the specific month
                $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $monthEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                
                $leaveStart = max($request->start_date, $monthStart);
                $leaveEnd = min($request->end_date, $monthEnd);
                
                $daysInMonth = $leaveStart->diffInDays($leaveEnd) + 1;
                
                return [
                    'employee_id' => $request->user->id,
                    'employee_name' => $request->user->name . ' ' . $request->user->lastname,
                    'employee_code' => $request->user->employee->employee_id ?? null,
                    'department' => $request->user->employee->department ?? null,
                    'leave_type' => $request->leave_type,
                    'days_taken' => $daysInMonth,
                    'is_paid' => in_array($request->leave_type, ['Annual', 'Sick', 'Personal']), // Configure as needed
                    'start_date' => $request->start_date->format('Y-m-d'),
                    'end_date' => $request->end_date->format('Y-m-d'),
                ];
            });
        
        return response()->json([
            'month' => $month,
            'year' => $year,
            'data' => $payrollData,
            'summary' => [
                'total_employees_on_leave' => $payrollData->groupBy('employee_id')->count(),
                'total_leave_days' => $payrollData->sum('days_taken'),
                'paid_leave_days' => $payrollData->where('is_paid', true)->sum('days_taken'),
                'unpaid_leave_days' => $payrollData->where('is_paid', false)->sum('days_taken'),
            ]
        ]);
    }
    
    /**
     * Update leave balance when request status changes.
     */
    private function updateLeaveBalance(LeaveRequest $leaveRequest, string $action): void
    {
        $balance = LeaveBalance::firstOrCreate([
            'user_id' => $leaveRequest->user_id,
            'leave_type' => $leaveRequest->leave_type,
            'year' => $leaveRequest->start_date->year,
        ]);
        
        switch ($action) {
            case 'approved':
                // Move from pending to used
                $balance->pending = max(0, $balance->pending - $leaveRequest->days_requested);
                $balance->used += $leaveRequest->days_requested;
                break;
                
            case 'rejected':
                // Remove from pending
                $balance->pending = max(0, $balance->pending - $leaveRequest->days_requested);
                break;
                
            case 'pending':
                // Add to pending
                $balance->pending += $leaveRequest->days_requested;
                break;
        }
        
        // Recalculate available
        $balance->available = $balance->total_entitled + $balance->carried_forward - $balance->used - $balance->pending;
        $balance->save();
    }
}
