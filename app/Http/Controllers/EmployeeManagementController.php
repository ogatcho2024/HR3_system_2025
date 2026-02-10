<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\ShiftRequest;
use App\Models\Alert;
use App\Models\Timesheet;
use App\Services\AuditLogService;
use App\Services\AlertNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeManagementController extends Controller
{
    protected $auditLog;
    protected AlertNotificationService $alertNotifications;

    public function __construct(AuditLogService $auditLog, AlertNotificationService $alertNotifications)
    {
        $this->auditLog = $auditLog;
        $this->alertNotifications = $alertNotifications;
    }

    private function ensureEmployeeManagementWriteAccess(string $action): void
    {
        $user = Auth::user();
        if ($user && $user->isStaff()) {
            $this->auditLog->logOther("Unauthorized staff attempt: {$action}", [
                'user_id' => $user->id,
                'account_type' => $user->account_type,
            ]);
            abort(403, 'Unauthorized access. Staff are view-only.');
        }
    }
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
     * Display all employees and their user account setup status
     * Reads from employees table and checks if they have user accounts
     */
    public function employees(Request $request): View
    {
        // Base query - from employees table with user relationship
        $query = Employee::with('user');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('employee_id', 'LIKE', "%{$search}%")
                  ->orWhere('position', 'LIKE', "%{$search}%")
                  ->orWhere('department', 'LIKE', "%{$search}%")
                  ->orWhere('manager_name', 'LIKE', "%{$search}%")
                  // Also search in related user data if exists
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('lastname', 'LIKE', "%{$search}%")
                                ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter by user account status
        if ($request->filled('profile_status')) {
            $status = $request->get('profile_status');
            if ($status === 'incomplete') {
                // Employees without user accounts
                $query->whereDoesntHave('user');
            } elseif ($status === 'complete') {
                // Employees with user accounts
                $query->whereHas('user');
            }
        }
        
        // Filter by department
        if ($request->filled('department')) {
            $department = $request->get('department');
            $query->where('department', $department);
        }

        // Order by most recent first
        $employees = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get all departments from employees table for filter dropdown
        $departments = Employee::whereNotNull('department')
                              ->distinct()
                              ->pluck('department')
                              ->filter()
                              ->sort()
                              ->map(function($dept) {
                                  return (object) ['department_name' => $dept];
                              });

        return view('employee-management.employees.index', compact('employees', 'departments'));
    }

    /**
     * Show employee profile setup form
     */
    public function showProfileSetup(User $user): View
    {
        $this->ensureEmployeeManagementWriteAccess('view profile setup');
        // Get all active departments for the dropdown
        $departments = Department::active()->orderBy('department_name')->get();
        
        return view('employee-management.employees.setup', compact('user', 'departments'));
    }

    /**
     * Store or update employee profile
     */
    public function storeProfile(Request $request, User $user): RedirectResponse
    {
        $this->ensureEmployeeManagementWriteAccess('store profile');
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
     * Create a user account for an employee who doesn't have one
     */
    public function createUserAccount(Request $request, Employee $employee): RedirectResponse
    {
        $this->ensureEmployeeManagementWriteAccess('create user account');
        // Check if employee already has a user account
        if ($employee->user) {
            return redirect()->route('employee-management.employees')
                ->with('error', 'This employee already has a user account.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255', 
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'account_type' => 'required|in:Super admin,Admin,Staff,Employee',
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('profile-photos', 'public');
                $validatedData['photo'] = $photoPath;
            }

            // Set default values
            $validatedData['password'] = bcrypt($validatedData['password']);
            $validatedData['position'] = $employee->position;
            $validatedData['email_verified_at'] = now();

            // Create user account
            $user = User::create($validatedData);
            
            // Update employee record to link to user
            $employee->update(['user_id' => $user->id]);

            // Log account creation
            $this->auditLog->logAccountCreated(
                $user->id,
                [
                    'name' => $user->name,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                    'position' => $user->position
                ],
                "Created user account for employee {$employee->employee_id}: {$user->name} {$user->lastname}"
            );

            return redirect()->route('employee-management.employees')
                ->with('success', "User account created successfully for {$employee->employee_id}.");
        } catch (\Exception $e) {
            return redirect()->route('employee-management.employees')
                ->with('error', 'An error occurred while creating the user account. Please try again.');
        }
    }

    /**
     * Update user account information
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $this->ensureEmployeeManagementWriteAccess('update user account');
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'account_type' => 'required|in:Super admin,Admin,Staff,Employee',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            // Capture old values for audit log
            $oldData = [
                'name' => $user->name,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'account_type' => $user->account_type,
                'position' => $user->position
            ];

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }
                
                // Store new photo
                $photoPath = $request->file('photo')->store('profile-photos', 'public');
                $validatedData['photo'] = $photoPath;
            }

            // Update user
            $user->update($validatedData);

            // Log account update
            $this->auditLog->logAccountUpdated(
                $user->id,
                $oldData,
                [
                    'name' => $user->name,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                    'position' => $user->position
                ],
                "Updated user account: {$user->name} {$user->lastname}"
            );

            // Log specific changes
            if ($oldData['email'] !== $user->email) {
                $this->auditLog->logEmailChanged($user, $oldData['email'], $user->email);
            }
            if ($oldData['account_type'] !== $user->account_type) {
                $this->auditLog->logRoleChanged(
                    $user->id,
                    $oldData['account_type'],
                    $user->account_type,
                    "{$user->name} {$user->lastname}"
                );
            }

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
        $this->ensureEmployeeManagementWriteAccess('delete user account');
        try {
            $userName = $user->name . ' ' . ($user->lastname ?? '');
            
            // Capture user data for audit log before deletion
            $userData = [
                'name' => $user->name,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'account_type' => $user->account_type,
                'position' => $user->position
            ];
            $userId = $user->id;
            
            // Delete associated employee record if it exists
            if ($user->employee) {
                $user->employee->delete();
            }
            
            // Delete the user account
            $user->delete();
            
            // Log account deletion
            $this->auditLog->logAccountDeleted($userId, $userData, "Deleted user account: {$userName}");
            
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
        $alertTypes = config('alerts.types', []);
        $alertPriorities = config('alerts.priorities', []);
        $alertTargetRoles = User::query()
            ->whereNotNull('account_type')
            ->distinct()
            ->orderBy('account_type')
            ->pluck('account_type')
            ->filter()
            ->mapWithKeys(function ($role) {
                $label = ucwords(str_replace(['_', '-'], ' ', $role));
                return [$role => $label];
            })
            ->toArray();

        return view('employee-management.alerts.create', compact(
            'alertTypes',
            'alertPriorities',
            'alertTargetRoles'
        ));
    }

    /**
     * Store new alert
     */
    public function storeAlert(Request $request): RedirectResponse
    {
        $alertTypes = config('alerts.types', []);
        $alertPriorities = config('alerts.priorities', []);
        $alertTargetRoles = User::query()
            ->whereNotNull('account_type')
            ->distinct()
            ->orderBy('account_type')
            ->pluck('account_type')
            ->filter()
            ->mapWithKeys(function ($role) {
                $label = ucwords(str_replace(['_', '-'], ' ', $role));
                return [$role => $label];
            })
            ->toArray();

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => ['required', Rule::in(array_keys($alertTypes))],
            'priority' => ['required', Rule::in(array_keys($alertPriorities))],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_roles' => 'nullable|array',
            'target_roles.*' => [Rule::in(array_keys($alertTargetRoles))],
        ]);

        $validatedData['created_by'] = Auth::id();
        $validatedData['is_active'] = true;

        $alert = Alert::create($validatedData);
        $this->alertNotifications->dispatchIfEligible($alert);

        return redirect()->route('employee-management.alerts')
            ->with('success', 'Alert created successfully!');
    }

    /**
     * Show edit alert form
     */
    public function editAlert(Alert $alert): View
    {
        $alertTypes = config('alerts.types', []);
        $alertPriorities = config('alerts.priorities', []);
        $alertTargetRoles = User::query()
            ->whereNotNull('account_type')
            ->distinct()
            ->orderBy('account_type')
            ->pluck('account_type')
            ->filter()
            ->mapWithKeys(function ($role) {
                $label = ucwords(str_replace(['_', '-'], ' ', $role));
                return [$role => $label];
            })
            ->toArray();

        return view('employee-management.alerts.edit', compact(
            'alert',
            'alertTypes',
            'alertPriorities',
            'alertTargetRoles'
        ));
    }

    /**
     * Update alert
     */
    public function updateAlert(Request $request, Alert $alert): RedirectResponse
    {
        $alertTypes = config('alerts.types', []);
        $alertPriorities = config('alerts.priorities', []);
        $alertTargetRoles = User::query()
            ->whereNotNull('account_type')
            ->distinct()
            ->orderBy('account_type')
            ->pluck('account_type')
            ->filter()
            ->mapWithKeys(function ($role) {
                $label = ucwords(str_replace(['_', '-'], ' ', $role));
                return [$role => $label];
            })
            ->toArray();

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => ['required', Rule::in(array_keys($alertTypes))],
            'priority' => ['required', Rule::in(array_keys($alertPriorities))],
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_roles' => 'nullable|array',
            'target_roles.*' => [Rule::in(array_keys($alertTargetRoles))],
        ]);

        $alert->update($validatedData);
        $this->alertNotifications->dispatchIfEligible($alert->fresh());

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
