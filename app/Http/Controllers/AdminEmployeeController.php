<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\ShiftRequest;
use App\Models\Alert;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminEmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display the admin dashboard.
     */
    public function dashboard(): View
    {
        // Get pending counts for dashboard cards
        $pendingLeaveRequests = LeaveRequest::pending()->count();
        $pendingShiftRequests = ShiftRequest::pending()->count();
        $incompleteProfiles = User::whereDoesntHave('employee')->count();
        $activeAlerts = Alert::active()->count();

        // Get recent activity
        $recentLeaveRequests = LeaveRequest::with('user')
            ->pending()
            ->latest()
            ->take(5)
            ->get();
            
        $recentShiftRequests = ShiftRequest::with('user')
            ->pending()
            ->latest()
            ->take(5)
            ->get();

        $recentIncompleteProfiles = User::whereDoesntHave('employee')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'pendingLeaveRequests',
            'pendingShiftRequests', 
            'incompleteProfiles',
            'activeAlerts',
            'recentLeaveRequests',
            'recentShiftRequests',
            'recentIncompleteProfiles'
        ));
    }

    /**
     * Display all employees.
     */
    public function employees(Request $request): View
    {
        $query = User::with('employee');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('lastname', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filter by profile status
        if ($request->filled('profile_status')) {
            $status = $request->get('profile_status');
            if ($status === 'incomplete') {
                $query->whereDoesntHave('employee');
            } elseif ($status === 'complete') {
                $query->whereHas('employee');
            }
        }

        $employees = $query->paginate(15);

        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Show employee profile setup form.
     */
    public function showProfileSetup(User $user): View
    {
        return view('admin.employees.profile-setup', compact('user'));
    }

    /**
     * Store or update employee profile.
     */
    public function storeProfile(Request $request, User $user): RedirectResponse
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|string|max:50|unique:employees,employee_id,' . ($user->employee->id ?? 'NULL'),
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'manager_name' => 'nullable|string|max:255',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'employment_type' => 'required|in:full_time,part_time,contract,internship',
            'work_location' => 'required|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive,terminated',
            // User fields
            'phone' => 'nullable|string|max:20',
        ]);

        // Update user fields
        $user->update([
            'phone' => $validatedData['phone'],
            'position' => $validatedData['position'],
        ]);

        // Create or update employee record
        $employeeData = array_merge($validatedData, ['user_id' => $user->id]);
        unset($employeeData['phone']);

        if ($user->employee) {
            $user->employee->update($employeeData);
        } else {
            Employee::create($employeeData);
        }

        return redirect()->route('admin.employees')
            ->with('success', 'Employee profile updated successfully!');
    }

    /**
     * Display alert management page.
     */
    public function alerts(): View
    {
        $alerts = Alert::with('creator')
            ->latest()
            ->paginate(15);

        return view('admin.alerts.index', compact('alerts'));
    }

    /**
     * Show create alert form.
     */
    public function createAlert(): View
    {
        return view('admin.alerts.create');
    }

    /**
     * Store new alert.
     */
    public function storeAlert(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,error,success',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_roles' => 'nullable|array',
        ]);

        $validatedData['created_by'] = Auth::id();
        $validatedData['is_active'] = true;

        Alert::create($validatedData);

        return redirect()->route('admin.alerts')
            ->with('success', 'Alert created successfully!');
    }

    /**
     * Show edit alert form.
     */
    public function editAlert(Alert $alert): View
    {
        return view('admin.alerts.edit', compact('alert'));
    }

    /**
     * Update alert.
     */
    public function updateAlert(Request $request, Alert $alert): RedirectResponse
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,error,success',
            'priority' => 'required|in:low,medium,high,urgent',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_roles' => 'nullable|array',
        ]);

        $alert->update($validatedData);

        return redirect()->route('admin.alerts')
            ->with('success', 'Alert updated successfully!');
    }

    /**
     * Delete alert.
     */
    public function deleteAlert(Alert $alert): RedirectResponse
    {
        $alert->delete();

        return redirect()->route('admin.alerts')
            ->with('success', 'Alert deleted successfully!');
    }

    /**
     * Display pending leave requests.
     */
    public function pendingLeaveRequests(): View
    {
        $leaveRequests = LeaveRequest::with('user')
            ->pending()
            ->latest()
            ->paginate(15);

        return view('admin.leave-requests.pending', compact('leaveRequests'));
    }

    /**
     * Display pending shift requests.
     */
    public function pendingShiftRequests(): View
    {
        $shiftRequests = ShiftRequest::with(['user', 'swapWithUser'])
            ->pending()
            ->latest()
            ->paginate(15);

        return view('admin.shift-requests.pending', compact('shiftRequests'));
    }
}
