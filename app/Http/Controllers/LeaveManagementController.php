<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use App\Models\User;
use App\Models\Employee;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        
        return view('leave-management.pending-requests', compact('pendingRequests'));
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
        
        return view('leave-management.leave-balances', compact('leaveBalances', 'year'));
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
        
        return view('leave-management.calendar', compact('leaveRequests', 'month', 'year'));
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
