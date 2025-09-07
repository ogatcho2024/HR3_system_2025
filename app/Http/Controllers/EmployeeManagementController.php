<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\ShiftRequest;
use App\Models\Alert;
use App\Models\Timesheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class EmployeeManagementController extends Controller
{
    /**
     * Display the main dashboard with card boxes
     */
    public function dashboard(): View
    {
        // Get counts for card boxes
        $pendingLeaveRequests = LeaveRequest::where('status', 'pending')->count();
        $pendingShiftRequests = ShiftRequest::where('status', 'pending')->count();
        $incompleteProfiles = User::whereDoesntHave('employee')->count();
        $activeAlerts = Alert::where('is_active', true)->count();

        // Get recent items for each category
        $recentLeaveRequests = LeaveRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();
            
        $recentShiftRequests = ShiftRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $incompleteProfileUsers = User::whereDoesntHave('employee')
            ->latest()
            ->take(5)
            ->get();

        return view('employee-management.dashboard', compact(
            'pendingLeaveRequests',
            'pendingShiftRequests',
            'incompleteProfiles',
            'activeAlerts',
            'recentLeaveRequests',
            'recentShiftRequests',
            'incompleteProfileUsers'
        ));
    }

    /**
     * Display all users (existing users have complete profiles, non-existing need setup)
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

        // Filter by profile status (all users in the users table have complete profiles)
        // For incomplete profiles, we would need to add logic to show potential users
        // For now, all users in the database are considered "complete"
        if ($request->filled('profile_status')) {
            $status = $request->get('profile_status');
            // Since all users in DB are complete, incomplete filter shows none
            if ($status === 'incomplete') {
                $query->whereRaw('1 = 0'); // No results
            }
            // Complete status shows all users (no additional filter needed)
        }
        
        // Filter by department (using employee relationship)
        if ($request->filled('department')) {
            $department = $request->get('department');
            $query->whereHas('employee', function($q) use ($department) {
                $q->where('department', $department);
            });
        }

        $employees = $query->paginate(15);

        return view('employee-management.employees.index', compact('employees'));
    }

    /**
     * Show employee profile setup form
     */
    public function showProfileSetup(User $user): View
    {
        return view('employee-management.employees.setup', compact('user'));
    }

    /**
     * Store or update employee profile
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
            'phone' => 'nullable|string|max:20',
        ]);

        // Update user phone
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

        return redirect()->route('employee-management.employees')
            ->with('success', 'Employee profile updated successfully!');
    }

    /**
     * Update user account information
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'account_type' => 'required|in:1,2,3',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($user->photo && \Storage::disk('public')->exists($user->photo)) {
                    \Storage::disk('public')->delete($user->photo);
                }
                
                // Store new photo
                $photoPath = $request->file('photo')->store('profile-photos', 'public');
                $validatedData['photo'] = $photoPath;
            }

            // Update user
            $user->update($validatedData);

            return redirect()->route('employee-management.employees')
                ->with('success', "User account for {$user->name} {$user->lastname} has been updated successfully.");
        } catch (\Exception $e) {
            return redirect()->route('employee-management.employees')
                ->with('error', 'An error occurred while updating the user account. Please try again.');
        }
    }

    /**
     * Delete a user account and their employee profile
     */
    public function deleteUser(User $user): RedirectResponse
    {
        try {
            $userName = $user->name . ' ' . ($user->lastname ?? '');
            
            // Delete associated employee record if it exists
            if ($user->employee) {
                $user->employee->delete();
            }
            
            // Delete the user account
            $user->delete();
            
            return redirect()->route('employee-management.employees')
                ->with('success', "User account for {$userName} has been deleted successfully.");
        } catch (\Exception $e) {
            return redirect()->route('employee-management.employees')
                ->with('error', 'An error occurred while deleting the user account. Please try again.');
        }
    }

    /**
     * Display alert management page
     */
    public function alerts(): View
    {
        $alerts = Alert::with('creator')->latest()->paginate(15);
        return view('employee-management.alerts.index', compact('alerts'));
    }

    /**
     * Show create alert form
     */
    public function createAlert(): View
    {
        return view('employee-management.alerts.create');
    }

    /**
     * Store new alert
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

        return redirect()->route('employee-management.alerts')
            ->with('success', 'Alert created successfully!');
    }

    /**
     * Show edit alert form
     */
    public function editAlert(Alert $alert): View
    {
        return view('employee-management.alerts.edit', compact('alert'));
    }

    /**
     * Update alert
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

        return redirect()->route('employee-management.alerts')
            ->with('success', 'Alert updated successfully!');
    }

    /**
     * Delete alert
     */
    public function deleteAlert(Alert $alert): RedirectResponse
    {
        $alert->delete();
        return redirect()->route('employee-management.alerts')
            ->with('success', 'Alert deleted successfully!');
    }

    /**
     * Display pending leave requests
     */
    public function pendingLeaveRequests(): View
    {
        $leaveRequests = LeaveRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('employee-management.leave-requests', compact('leaveRequests'));
    }

    /**
     * Display pending shift requests
     */
    public function pendingShiftRequests(): View
    {
        $shiftRequests = ShiftRequest::with(['user', 'swapWithUser'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('employee-management.shift-requests', compact('shiftRequests'));
    }

    /**
     * Display combined view of all pending requests (leave and shift)
     */
    public function allRequests(): View
    {
        // Get all leave requests grouped by status
        $pendingLeaveRequests = LeaveRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();
            
        $approvedLeaveRequests = LeaveRequest::with('user')
            ->where('status', 'approved')
            ->latest()
            ->get();
            
        $rejectedLeaveRequests = LeaveRequest::with('user')
            ->where('status', 'rejected')
            ->latest()
            ->get();

        // Get all shift requests grouped by status
        $pendingShiftRequests = ShiftRequest::with(['user', 'swapWithUser'])
            ->where('status', 'pending')
            ->latest()
            ->get();
            
        $approvedShiftRequests = ShiftRequest::with(['user', 'swapWithUser'])
            ->where('status', 'approved')
            ->latest()
            ->get();
            
        $rejectedShiftRequests = ShiftRequest::with(['user', 'swapWithUser'])
            ->where('status', 'rejected')
            ->latest()
            ->get();

        return view('employee-management.requests', compact(
            'pendingLeaveRequests', 'approvedLeaveRequests', 'rejectedLeaveRequests',
            'pendingShiftRequests', 'approvedShiftRequests', 'rejectedShiftRequests'
        ));
    }

    /**
     * Display the Employee Portal - self-service portal for employees
     */
    public function employeePortal(): View
    {
        // Get statistics
        $totalEmployees = User::whereHas('employee')->count();
        $pendingLeaveRequests = LeaveRequest::where('status', 'pending')->count();
        $draftTimesheets = Timesheet::where('status', 'draft')->count();
        $activeAlerts = Alert::where('is_active', true)->count();
        
        // Get current employee information
        $currentEmployee = Auth::user()->employee;
        
        // Get recent activities (mock data for now - you can replace with actual activity tracking)
        $recentActivities = collect([
            [
                'description' => 'Welcome to the Employee Portal!',
                'time' => now()->format('M j, Y g:i A')
            ],
            [
                'description' => 'Your profile was last updated',
                'time' => $currentEmployee ? $currentEmployee->updated_at->format('M j, Y g:i A') : 'Never'
            ],
            [
                'description' => 'System maintenance scheduled for this weekend',
                'time' => now()->subDays(2)->format('M j, Y g:i A')
            ]
        ]);
        
        // Get active company alerts
        $companyAlerts = Alert::where('is_active', true)
            ->latest()
            ->take(5)
            ->get();
            
        return view('employee-management.employee-portal', compact(
            'totalEmployees',
            'pendingLeaveRequests', 
            'draftTimesheets',
            'activeAlerts',
            'currentEmployee',
            'recentActivities',
            'companyAlerts'
        ));
    }

    /**
     * Approve or reject a leave request
     */
    public function updateLeaveRequestStatus(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500'
        ]);

        $leaveRequest->update([
            'status' => $request->status,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->status === 'rejected' ? $request->rejection_reason : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request ' . $request->status . ' successfully.',
            'status' => $request->status
        ]);
    }

    /**
     * Approve or reject a shift request
     */
    public function updateShiftRequestStatus(Request $request, ShiftRequest $shiftRequest)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500'
        ]);

        $shiftRequest->update([
            'status' => $request->status,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->status === 'rejected' ? $request->rejection_reason : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift request ' . $request->status . ' successfully.',
            'status' => $request->status
        ]);
    }
}
