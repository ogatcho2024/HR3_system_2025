<?php

use Illuminate\Support\Facades\Route;

// Include migration routes for production deployment
require __DIR__.'/migration.php';

// Admin migration routes for login rate limiting
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/migration-status', [\App\Http\Controllers\MigrationController::class, 'showStatus'])->name('migration.status');
    Route::post('/migration-create', [\App\Http\Controllers\MigrationController::class, 'createTable'])->name('migration.create');
});
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\EmployeeSelfServiceController;
use App\Http\Controllers\LeaveManagementController;
use App\Http\Controllers\AdminEmployeeController;
use App\Http\Controllers\EmployeeManagementController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ShiftManagementController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\ActivityController;


Route::get('/', [LandingController::class, 'main'])->name('landing');


Route::get('/sendotp', function () {
    // You can load a view or redirect elsewhere
    return view('auth.otp');
});

Route::get('/load-content/{section}', function ($section) {
    return view($section); // looks for attendanceTimeTracking.blade.php in views/
});

// Route::get('/', function () {
//     return view('dashboard');
// })->name('dashboard');

// Route::get('/attendanceTimeTracking', function () {
//     return view('attendanceTimeTracking');
// })->name('attendanceTimeTracking');

Route::get('/activities', [ActivityController::class, 'index'])->name('activities');

Route::get('/dashb', [\App\Http\Controllers\AttendanceController::class, 'dashboard'])->name('dashb');
Route::get('/workScheduleShiftManagement', [ShiftManagementController::class, 'index'])->name('workScheduleShiftManagement')->middleware('auth');

// Shift Management API Routes (Public for testing)
Route::prefix('shift-management/api')->name('shift-management.api.')->group(function () {
    Route::get('templates', [ShiftManagementController::class, 'getShiftTemplates'])->name('templates');
    Route::post('templates', [ShiftManagementController::class, 'store'])->name('templates.store');
    Route::get('templates/{id}', [ShiftManagementController::class, 'show'])->name('templates.show');
    Route::put('templates/{id}', [ShiftManagementController::class, 'update'])->name('templates.update');
    Route::delete('templates/{id}', [ShiftManagementController::class, 'destroy'])->name('templates.destroy');
    Route::patch('templates/{id}/toggle-status', [ShiftManagementController::class, 'toggleStatus'])->name('templates.toggle-status');
    
    // Employee Assignment Routes (TEMPORARY: Public for debugging)
    Route::get('available-employees', [ShiftManagementController::class, 'getEmployeesForAssignment'])->name('available-employees');
    Route::post('assignments', [ShiftManagementController::class, 'storeAssignment'])->name('assignments.store');
    Route::post('assignments/reassign', [ShiftManagementController::class, 'reassignEmployee'])->name('assignments.reassign');
    Route::put('assignments/{id}', [ShiftManagementController::class, 'updateAssignment'])->name('assignments.update');
    Route::delete('assignments/{id}', [ShiftManagementController::class, 'removeAssignment'])->name('assignments.destroy');
    
    // Calendar Data
    Route::get('calendar-data', [ShiftManagementController::class, 'getShiftCalendarDataApi'])->name('calendar-data');
    
    // Shift Request Actions
    Route::patch('shift-requests/{id}/approve', [ShiftManagementController::class, 'approveShiftRequest'])->name('shift-requests.approve');
    Route::patch('shift-requests/{id}/reject', [ShiftManagementController::class, 'rejectShiftRequest'])->name('shift-requests.reject');
});

// Shift Request Management Routes (Protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    Route::prefix('shift-management')->name('shift-management.')->group(function () {
        Route::post('/requests/create', [ShiftManagementController::class, 'createShiftRequest'])->name('requests.create');
    });
});
Route::view('/employeeSelfService', 'employeeSelfService')->name('employeeSelfService');
Route::get('/timeSheetManagement', [\App\Http\Controllers\TimesheetController::class, 'managementDashboard'])->name('timeSheetManagement');
Route::view('/leaveManagement', 'leaveManagement')->name('leaveManagement');
Route::view('/attendanceTimeTracking', 'attendanceTimeTracking')->name('attendanceTimeTracking');



Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('login.throttle')->name('login.submit');
Route::post('/login/block-time', [AuthController::class, 'getBlockTime'])->name('login.block-time');

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// OTP Verification Routes (require authentication but not OTP verification)
Route::middleware(['auth'])->group(function () {
    Route::get('/otp/verify', [OTPController::class, 'showForm'])->name('otp.show');
    Route::post('/otp/verify', [OTPController::class, 'verify'])->name('otp.verify');
    Route::get('/otp/resend', [OTPController::class, 'resend'])->name('otp.resend');
});

// Protected routes that require both auth and 2FA verification
Route::middleware(['auth', \App\Http\Middleware\Ensure2FAVerified::class])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'showMainPage'])->name('dashboard');
});

// Profile route
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');

// Settings route
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

// Logout (must be POST)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

//For Successfully registered users
Route::get('/success', function () {
    return view('auth.success');
})->name('auth.success');

Route::resource('posts',PostController::class);

// Employee Self Service Routes (Protected by auth and 2FA middleware)
Route::middleware(['auth', \App\Http\Middleware\Ensure2FAVerified::class])->group(function () {
    Route::prefix('employee')->name('employee.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [EmployeeSelfServiceController::class, 'index'])->name('dashboard');
        
        // Profile Management
        Route::get('/profile', [EmployeeSelfServiceController::class, 'profile'])->name('profile');
        Route::put('/profile', [EmployeeSelfServiceController::class, 'updateProfile'])->name('profile.update');
        
        // Leave Requests
        Route::get('/leave-requests', [EmployeeSelfServiceController::class, 'leaveRequests'])->name('leave-requests');
        Route::get('/leave-requests/create', [EmployeeSelfServiceController::class, 'createLeaveRequest'])->name('leave-requests.create');
        Route::post('/leave-requests', [EmployeeSelfServiceController::class, 'storeLeaveRequest'])->name('leave-requests.store');
        
        // Timesheets
        Route::get('/timesheets', [EmployeeSelfServiceController::class, 'timesheets'])->name('timesheets');
        Route::get('/timesheets/create', [EmployeeSelfServiceController::class, 'createTimesheet'])->name('timesheets.create');
        Route::post('/timesheets', [EmployeeSelfServiceController::class, 'storeTimesheet'])->name('timesheets.store');
        Route::get('/timesheets/{timesheet}/edit', [EmployeeSelfServiceController::class, 'editTimesheet'])->name('timesheets.edit');
        Route::put('/timesheets/{timesheet}', [EmployeeSelfServiceController::class, 'updateTimesheet'])->name('timesheets.update');
        Route::patch('/timesheets/{timesheet}/submit', [EmployeeSelfServiceController::class, 'submitTimesheet'])->name('timesheets.submit');
    });
});

// Notification Routes (Protected by auth and 2FA middleware)
Route::middleware(['auth', \App\Http\Middleware\Ensure2FAVerified::class])->group(function () {
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // List all notifications
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        
        // Show single notification
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        
        // AJAX routes for notification management
        Route::get('/recent/json', [NotificationController::class, 'getRecent'])->name('recent');
        Route::get('/count/json', [NotificationController::class, 'getCount'])->name('count');
        
        // Mark as read/unread
        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::patch('/{notification}/unread', [NotificationController::class, 'markAsUnread'])->name('mark-unread');
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        
        // Delete notifications
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear/read', [NotificationController::class, 'clearRead'])->name('clear-read');
    });
});

// Leave Management Routes (Protected by auth and 2FA middleware - for HR/Managers)
Route::middleware(['auth', \App\Http\Middleware\Ensure2FAVerified::class])->group(function () {
    Route::prefix('leave-management')->name('leave-management.')->group(function () {
        // Dashboard and Overview
        Route::get('/dashboard', [LeaveManagementController::class, 'index'])->name('dashboard');
        
        // Admin Dashboard with comprehensive features
        Route::get('/admin-dashboard', [LeaveManagementController::class, 'adminDashboard'])->name('admin-dashboard');
        
        // Pending Requests Management
        Route::get('/pending-requests', [LeaveManagementController::class, 'pendingRequests'])->name('pending-requests');
        
        // All Requests with Filters
        Route::get('/all-requests', [LeaveManagementController::class, 'allRequests'])->name('all-requests');
        
        // Individual Request Actions
        Route::get('/requests/{leaveRequest}', [LeaveManagementController::class, 'show'])->name('requests.show');
        Route::post('/requests/create', [LeaveManagementController::class, 'createLeaveRequest'])->name('requests.create');
        Route::post('/requests/{leaveRequest}/approve', [LeaveManagementController::class, 'approveRequest'])->name('requests.approve');
        Route::post('/requests/{leaveRequest}/reject', [LeaveManagementController::class, 'rejectRequest'])->name('requests.reject');
        
        // Leave Balances Management
        Route::get('/leave-balances', [LeaveManagementController::class, 'leaveBalances'])->name('leave-balances');
        Route::post('/leave-balances/adjust', [LeaveManagementController::class, 'adjustLeaveBalance'])->name('adjust-balance');
        
        // Leave Calendar with Conflict Detection
        Route::get('/calendar', [LeaveManagementController::class, 'calendar'])->name('calendar');
        Route::post('/check-conflicts', [LeaveManagementController::class, 'checkLeaveConflicts'])->name('check-conflicts');
        
        // Reports and Analytics
        Route::get('/reports', [LeaveManagementController::class, 'generateReport'])->name('reports');
        Route::get('/reports-analytics', [LeaveManagementController::class, 'reportsAnalytics'])->name('reports-analytics');
        Route::get('/export-report', [LeaveManagementController::class, 'exportReport'])->name('export-report');
        Route::get('/export-pdf', [LeaveManagementController::class, 'exportLeaveReportsPDF'])->name('export-pdf');
        
        // Integration APIs
        Route::get('/payroll-integration', [LeaveManagementController::class, 'getPayrollIntegrationData'])->name('payroll-integration');
    });
});

// Employee Management Routes (Main Access Point with 2FA)
Route::middleware(['auth', \App\Http\Middleware\Ensure2FAVerified::class])->group(function () {
    Route::prefix('employee-management')->name('employee-management.')->group(function () {
        // Main Dashboard
        Route::get('/', [EmployeeManagementController::class, 'dashboard'])->name('dashboard');
        
        // Employee Profile Management
        Route::get('/employees', [EmployeeManagementController::class, 'employees'])->name('employees');
        Route::get('/employees/{user}/setup', [EmployeeManagementController::class, 'showProfileSetup'])->name('employees.setup');
        Route::post('/employees/{user}/profile', [EmployeeManagementController::class, 'storeProfile'])->name('employees.store-profile');
        Route::post('/employees/{employee}/create-user', [EmployeeManagementController::class, 'createUserAccount'])->name('employees.create-user');
        Route::put('/users/{user}', [EmployeeManagementController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [EmployeeManagementController::class, 'deleteUser'])->name('users.delete');
        
        // Alert System
        Route::get('/alerts', [EmployeeManagementController::class, 'alerts'])->name('alerts');
        Route::get('/alerts/create', [EmployeeManagementController::class, 'createAlert'])->name('alerts.create');
        Route::post('/alerts', [EmployeeManagementController::class, 'storeAlert'])->name('alerts.store');
        Route::get('/alerts/{alert}/edit', [EmployeeManagementController::class, 'editAlert'])->name('alerts.edit');
        Route::put('/alerts/{alert}', [EmployeeManagementController::class, 'updateAlert'])->name('alerts.update');
        Route::delete('/alerts/{alert}', [EmployeeManagementController::class, 'deleteAlert'])->name('alerts.delete');
        
        // Pending Requests
        Route::get('/leave-requests', [EmployeeManagementController::class, 'pendingLeaveRequests'])->name('leave-requests');
        Route::get('/shift-requests', [EmployeeManagementController::class, 'pendingShiftRequests'])->name('shift-requests');
        
        // Combined Requests Management
        Route::get('/requests', [EmployeeManagementController::class, 'allRequests'])->name('requests');
        
        // Request Status Updates (API)
        Route::patch('/leave-requests/{leaveRequest}/status', [EmployeeManagementController::class, 'updateLeaveRequestStatus'])->name('leave-requests.update-status');
        Route::patch('/shift-requests/{shiftRequest}/status', [EmployeeManagementController::class, 'updateShiftRequestStatus'])->name('shift-requests.update-status');
        
        // Employee Portal
        Route::get('/employee-portal', [EmployeeManagementController::class, 'employeePortal'])->name('employee-portal');
    });
});

// Employee Dashboard Routes (Protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    Route::prefix('employee')->name('employee.')->group(function () {
        // Main Dashboard
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        
        // Leave Management
        Route::get('/leave-requests', [EmployeeDashboardController::class, 'leaveRequests'])->name('leave-requests');
        Route::post('/leave-requests', [EmployeeDashboardController::class, 'storeLeaveRequest'])->name('leave-requests.store');
        Route::delete('/leave-requests/{leaveRequest}', [EmployeeDashboardController::class, 'destroyLeaveRequest'])->name('leave-requests.destroy');
        
        // Shift Management
        Route::get('/shift-requests', [EmployeeDashboardController::class, 'shiftRequests'])->name('shift-requests');
        Route::post('/shift-requests', [EmployeeDashboardController::class, 'storeShiftRequest'])->name('shift-requests.store');
        Route::delete('/shift-requests/{shiftRequest}', [EmployeeDashboardController::class, 'destroyShiftRequest'])->name('shift-requests.destroy');
        
        // Reimbursements
        Route::get('/reimbursements', [EmployeeDashboardController::class, 'reimbursements'])->name('reimbursements');
        
        // Payroll & Payslips
        Route::get('/payroll', [EmployeeDashboardController::class, 'payroll'])->name('payroll');
        
        // Profile Management
        Route::get('/profile', [EmployeeDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [EmployeeDashboardController::class, 'updateProfile'])->name('profile.update');
        
        // Attendance & Performance Analytics
        Route::get('/attendance', [EmployeeDashboardController::class, 'attendance'])->name('attendance');
        
        // Work Schedule
        Route::get('/work-schedule', [EmployeeDashboardController::class, 'workSchedule'])->name('work-schedule');
    });
});

// Admin Routes (Protected by admin middleware)
Route::middleware(['admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        // Admin Dashboard
        Route::get('/dashboard', [AdminEmployeeController::class, 'dashboard'])->name('dashboard');
        
        // Employee Management
        Route::get('/employees', [AdminEmployeeController::class, 'employees'])->name('employees');
        Route::get('/employees/{user}/profile-setup', [AdminEmployeeController::class, 'showProfileSetup'])->name('employees.profile-setup');
        Route::post('/employees/{user}/profile', [AdminEmployeeController::class, 'storeProfile'])->name('employees.store-profile');
        
        // Alert System
        Route::get('/alerts', [AdminEmployeeController::class, 'alerts'])->name('alerts');
        Route::get('/alerts/create', [AdminEmployeeController::class, 'createAlert'])->name('alerts.create');
        Route::post('/alerts', [AdminEmployeeController::class, 'storeAlert'])->name('alerts.store');
        Route::get('/alerts/{alert}/edit', [AdminEmployeeController::class, 'editAlert'])->name('alerts.edit');
        Route::put('/alerts/{alert}', [AdminEmployeeController::class, 'updateAlert'])->name('alerts.update');
        Route::delete('/alerts/{alert}', [AdminEmployeeController::class, 'deleteAlert'])->name('alerts.delete');
        
        // Pending Requests Management
        Route::get('/leave-requests/pending', [AdminEmployeeController::class, 'pendingLeaveRequests'])->name('leave-requests.pending');
        Route::get('/shift-requests/pending', [AdminEmployeeController::class, 'pendingShiftRequests'])->name('shift-requests.pending');
    });
});

// Reports Routes (Protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        // Main Reports Dashboard
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        
        // Employee Reports
        Route::get('/employees', [ReportsController::class, 'employeeReport'])->name('employees');
        
        // Leave Reports
        Route::get('/leave', [ReportsController::class, 'leaveReport'])->name('leave');
        
        // Attendance/Timesheet Reports
        Route::get('/attendance', [ReportsController::class, 'attendanceReport'])->name('attendance');
        Route::get('/attendance/export', [ReportsController::class, 'exportAttendance'])->name('attendance.export');
        
        // Department Summary
        Route::get('/departments', [ReportsController::class, 'departmentReport'])->name('departments');
        
        // Monthly Summary
        Route::get('/monthly', [ReportsController::class, 'monthlyReport'])->name('monthly');
    });
});

// Audit Log Routes (Protected by auth, 2FA, and account type RBAC)
Route::middleware(['auth', \App\Http\Middleware\Ensure2FAVerified::class])->group(function () {
    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        // View-only routes: Super Admin and Admin can access
        Route::middleware(['account.type:Super admin,Admin'])->group(function () {
            // Main audit log viewer
            Route::get('/', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('index');
            
            // View specific audit log entry
            Route::get('/{id}', [\App\Http\Controllers\AuditLogController::class, 'show'])->name('show');
            
            // Export audit logs (CSV)
            Route::get('/export/csv', [\App\Http\Controllers\AuditLogController::class, 'export'])->name('export');
            
            // User activity timeline
            Route::get('/user/{userId}/activity', [\App\Http\Controllers\AuditLogController::class, 'userActivity'])->name('user-activity');
            
            // Security report
            Route::get('/security/report', [\App\Http\Controllers\AuditLogController::class, 'securityReport'])->name('security-report');
        });
        
        // Mutating routes: Super Admin only
        Route::middleware(['account.type:Super admin'])->group(function () {
            // Delete audit log entry (Super Admin only)
            Route::delete('/{id}', [\App\Http\Controllers\AuditLogController::class, 'destroy'])->name('destroy');
        });
    });
});

// Attendance Routes (Temporarily without auth for testing)
Route::prefix('attendance')->name('attendance.')->group(function () {
    // Real-time Data API (public for testing)
    Route::get('/real-time-data', [\App\Http\Controllers\AttendanceController::class, 'getRealTimeData'])->name('real-time-data');
    
    // Analytics Data API (public for testing)
    Route::get('/analytics-data', [\App\Http\Controllers\AttendanceController::class, 'getAnalyticsData'])->name('analytics-data');
    
    // Overview Data API (public for testing)
    Route::get('/overview-data', [\App\Http\Controllers\AttendanceController::class, 'getOverviewData'])->name('overview-data');
    
    // Recent Activities API (public for testing)
    Route::get('/recent-activities', [\App\Http\Controllers\AttendanceController::class, 'getRecentActivities'])->name('recent-activities');
    
    // Department Performance API (public for testing)
    Route::get('/department-performance', [\App\Http\Controllers\AttendanceController::class, 'getDepartmentPerformance'])->name('department-performance');
    
    // Employee timesheets data from attendance table
    Route::get('/employee-timesheets', [\App\Http\Controllers\AttendanceController::class, 'getEmployeeTimesheets']);
    
    // Update attendance record
    Route::put('/{attendance}', [\App\Http\Controllers\AttendanceController::class, 'update']);
    
    // Delete attendance record
    Route::delete('/{attendance}', [\App\Http\Controllers\AttendanceController::class, 'destroy']);
    
    // Simple attendance counts endpoint
    Route::get('/simple-counts', function() {
        $today = now()->toDateString();
        $todayAttendance = \App\Models\Attendance::whereDate('date', $today)->get();
        
        // Employees who have clocked in but not clocked out yet (still working)
        // This includes employees who are on break, since they haven't clocked out
        $clockedInRecords = $todayAttendance->whereNotNull('clock_in_time')->whereNull('clock_out_time');
        
        // Employees who have finished work (clocked out)
        $clockedOutRecords = $todayAttendance->whereNotNull('clock_in_time')->whereNotNull('clock_out_time');
        
        // Employees currently on break (they're still clocked in, but on break)
        $onBreakRecords = $todayAttendance->where('status', 'on_break');
        
        return response()->json([
            'debug' => [
                'today' => $today,
                'total_records' => $todayAttendance->count(),
                'clocked_in_records' => $clockedInRecords->map(function($record) {
                    return [
                        'user_name' => $record->user ? $record->user->name : 'Unknown',
                        'status' => $record->status,
                        'clock_in_time' => $record->clock_in_time,
                        'clock_out_time' => $record->clock_out_time
                    ];
                })->values()->all(),
                'clocked_out_records' => $clockedOutRecords->map(function($record) {
                    return [
                        'user_name' => $record->user ? $record->user->name : 'Unknown',
                        'status' => $record->status,
                        'clock_in_time' => $record->clock_in_time,
                        'clock_out_time' => $record->clock_out_time
                    ];
                })->values()->all(),
                'on_break_records' => $onBreakRecords->map(function($record) {
                    return [
                        'user_name' => $record->user ? $record->user->name : 'Unknown',
                        'status' => $record->status,
                        'clock_in_time' => $record->clock_in_time,
                        'clock_out_time' => $record->clock_out_time
                    ];
                })->values()->all()
            ],
            // Currently clocked in (includes those on break)
            'clockedIn' => $clockedInRecords->count(),
            // Finished work today
            'clockedOut' => $clockedOutRecords->count(),
            // Currently on break (subset of clocked in)
            'onBreak' => $onBreakRecords->count(),
            // Total active employees
            'totalEmployees' => \App\Models\Employee::active()->count()
        ]);
    });
    
    // Debug route to check attendance data
    Route::get('/debug-data', function() {
        $today = now()->toDateString();
        $allAttendanceRecords = \App\Models\Attendance::get();
        $todayAttendanceRecords = \App\Models\Attendance::where('date', $today)->get();
        $totalEmployeesActive = \App\Models\Employee::active()->count();
        $totalEmployeesAll = \App\Models\Employee::count();
        $totalUsers = \App\Models\User::count();
        
        $allEmployees = \App\Models\Employee::get();
        $activeEmployees = \App\Models\Employee::active()->get();
        
        // Test both old and new methods
        $clockedInOld = $todayAttendanceRecords->whereNotNull('clock_in_time')->whereNull('clock_out_time')->count();
        $clockedOutOld = $todayAttendanceRecords->whereNotNull('clock_in_time')->whereNotNull('clock_out_time')->count();
        
        // Test the new method with whereDate
        $todayAttendanceNew = \App\Models\Attendance::whereDate('date', $today)->get();
        $clockedInNew = $todayAttendanceNew->whereNotNull('clock_in_time')->whereNull('clock_out_time')->count();
        $clockedOutNew = $todayAttendanceNew->whereNotNull('clock_in_time')->whereNotNull('clock_out_time')->count();
        
        return [
            'debug_info' => [
                'today' => $today,
                'current_timestamp' => now()->toDateTimeString(),
                'total_employees_active' => $totalEmployeesActive,
                'total_employees_all' => $totalEmployeesAll,
                'total_users' => $totalUsers,
                'total_attendance_records' => $allAttendanceRecords->count(),
                'today_attendance_records' => $todayAttendanceRecords->count()
            ],
            'all_employees' => $allEmployees->map(function($e) {
                return [
                    'id' => $e->id,
                    'employee_id' => $e->employee_id,
                    'user_id' => $e->user_id,
                    'status' => $e->status,
                    'department' => $e->department,
                    'position' => $e->position,
                    'user_name' => $e->user ? $e->user->name : 'No User Found'
                ];
            }),
            'active_employees' => $activeEmployees->map(function($e) {
                return [
                    'id' => $e->id,
                    'employee_id' => $e->employee_id,
                    'user_id' => $e->user_id,
                    'status' => $e->status,
                    'department' => $e->department,
                    'position' => $e->position,
                    'user_name' => $e->user ? $e->user->name : 'No User Found'
                ];
            }),
            'today_attendance' => $todayAttendanceRecords->map(function($a) {
                return [
                    'id' => $a->id,
                    'user_id' => $a->user_id,
                    'date' => $a->date,
                    'clock_in_time' => $a->clock_in_time,
                    'clock_out_time' => $a->clock_out_time,
                    'status' => $a->status,
                    'user_name' => $a->user ? $a->user->name : 'No User Found'
                ];
            }),
            'all_attendance' => $allAttendanceRecords->map(function($a) {
                return [
                    'id' => $a->id,
                    'user_id' => $a->user_id,
                    'date' => $a->date,
                    'clock_in_time' => $a->clock_in_time,
                    'clock_out_time' => $a->clock_out_time,
                    'status' => $a->status,
                    'user_name' => $a->user ? $a->user->name : 'No User Found'
                ];
            }),
            'calculated_counts' => [
                'OLD_METHOD' => [
                    'clocked_in_current' => $clockedInOld,
                    'clocked_out_today' => $clockedOutOld
                ],
                'NEW_METHOD' => [
                    'clocked_in_current' => $clockedInNew,
                    'clocked_out_today' => $clockedOutNew
                ]
            ]
        ];
    });
});

// Attendance Routes (Protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    Route::prefix('attendance')->name('attendance.')->group(function () {
        // Manual Entry
        Route::get('/manual-entry', [\App\Http\Controllers\AttendanceController::class, 'create'])->name('manual-entry');
        Route::post('/manual-entry', [\App\Http\Controllers\AttendanceController::class, 'store'])->name('manual-entry.store');
        
        // Clock In/Out Actions
        Route::post('/clock-in', [\App\Http\Controllers\AttendanceController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out', [\App\Http\Controllers\AttendanceController::class, 'clockOut'])->name('clock-out');
        Route::post('/start-break', [\App\Http\Controllers\AttendanceController::class, 'startBreak'])->name('start-break');
        Route::post('/end-break', [\App\Http\Controllers\AttendanceController::class, 'endBreak'])->name('end-break');
        
        // Bulk Actions
        Route::post('/bulk-clock-in', function(\Illuminate\Http\Request $request) {
            $employeeIds = $request->input('employee_ids', []);
            $today = now()->toDateString();
            $currentTime = now()->format('H:i:s');
            $successCount = 0;
            
            foreach ($employeeIds as $employeeId) {
                // Find the employee's user_id
                $employee = \App\Models\Employee::find($employeeId);
                if (!$employee || !$employee->user_id) {
                    continue;
                }
                
                // Check if attendance record exists for today
                $attendance = \App\Models\Attendance::where('user_id', $employee->user_id)
                    ->whereDate('date', $today)
                    ->first();
                
                if ($attendance) {
                    // Update existing record
                    if (!$attendance->clock_in_time) {
                        $attendance->update([
                            'clock_in_time' => $currentTime,
                            'status' => 'present'
                        ]);
                        $successCount++;
                    }
                } else {
                    // Create new attendance record
                    \App\Models\Attendance::create([
                        'user_id' => $employee->user_id,
                        'date' => $today,
                        'clock_in_time' => $currentTime,
                        'status' => 'present',
                        'created_by' => auth()->id()
                    ]);
                    $successCount++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => $successCount . ' employee(s) clocked in successfully',
                'employee_ids' => $employeeIds,
                'timestamp' => now()->format('H:i')
            ]);
        })->name('bulk-clock-in');
        
        Route::post('/bulk-clock-out', function(\Illuminate\Http\Request $request) {
            try {
                \Log::info('Bulk clock-out started', ['employee_ids' => $request->input('employee_ids', [])]);
                
                $employeeIds = $request->input('employee_ids', []);
                $today = now()->toDateString();
                $currentTime = now()->format('H:i:s');
                $successCount = 0;
                $errors = [];
                
                foreach ($employeeIds as $employeeId) {
                    try {
                        $employee = \App\Models\Employee::find($employeeId);
                        if (!$employee || !$employee->user_id) {
                            $errors[] = "Employee {$employeeId} not found or has no user";
                            continue;
                        }
                        
                        $attendance = \App\Models\Attendance::where('user_id', $employee->user_id)
                            ->whereDate('date', $today)
                            ->first();
                        
                        if ($attendance && $attendance->clock_in_time && !$attendance->clock_out_time) {
                            // Keep the existing status (present/late) - don't change it when clocking out
                            $attendance->update([
                                'clock_out_time' => $currentTime
                            ]);
                            $successCount++;
                            \Log::info("Clocked out employee {$employeeId}");
                        } else {
                            $errors[] = "Employee {$employeeId} not eligible for clock out";
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Error with employee {$employeeId}: " . $e->getMessage();
                        \Log::error("Error clocking out employee {$employeeId}", ['error' => $e->getMessage()]);
                    }
                }
                
                \Log::info('Bulk clock-out completed', [
                    'success_count' => $successCount,
                    'errors' => $errors
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => $successCount . ' employee(s) clocked out successfully',
                    'employee_ids' => $employeeIds,
                    'timestamp' => now()->format('H:i'),
                    'errors' => $errors,
                    'debug' => [
                        'total_requested' => count($employeeIds),
                        'success_count' => $successCount
                    ]
                ]);
            } catch (\Exception $e) {
                \Log::error('Bulk clock-out failed completely', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Server error: ' . $e->getMessage(),
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('bulk-clock-out');
        
        Route::post('/bulk-start-break', function(\Illuminate\Http\Request $request) {
            $employeeIds = $request->input('employee_ids', []);
            $today = now()->toDateString();
            $currentTime = now()->format('H:i:s');
            $successCount = 0;
            
            foreach ($employeeIds as $employeeId) {
                $employee = \App\Models\Employee::find($employeeId);
                if (!$employee || !$employee->user_id) {
                    continue;
                }
                
                $attendance = \App\Models\Attendance::where('user_id', $employee->user_id)
                    ->whereDate('date', $today)
                    ->first();
                
                if ($attendance && $attendance->clock_in_time && !$attendance->clock_out_time) {
                    $attendance->update([
                        'break_start' => $currentTime,
                        'status' => 'on_break'
                    ]);
                    $successCount++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => $successCount . ' employee(s) started break',
                'employee_ids' => $employeeIds,
                'timestamp' => now()->format('H:i')
            ]);
        })->name('bulk-start-break');
        
        Route::post('/bulk-end-break', function(\Illuminate\Http\Request $request) {
            $employeeIds = $request->input('employee_ids', []);
            $today = now()->toDateString();
            $currentTime = now()->format('H:i:s');
            $successCount = 0;
            
            foreach ($employeeIds as $employeeId) {
                $employee = \App\Models\Employee::find($employeeId);
                if (!$employee || !$employee->user_id) {
                    continue;
                }
                
                $attendance = \App\Models\Attendance::where('user_id', $employee->user_id)
                    ->whereDate('date', $today)
                    ->first();
                
                if ($attendance && $attendance->status === 'on_break') {
                    $attendance->update([
                        'break_end' => $currentTime,
                        'status' => 'present'
                    ]);
                    $successCount++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => $successCount . ' employee(s) ended break',
                'employee_ids' => $employeeIds,
                'timestamp' => now()->format('H:i')
            ]);
        })->name('bulk-end-break');
        
        // Activities View
        Route::get('/activities', [\App\Http\Controllers\AttendanceController::class, 'getAllActivities'])->name('all-activities');
        
        // PDF Export Routes
        Route::get('/export-daily-pdf', [\App\Http\Controllers\AttendanceController::class, 'exportDailyPDF'])->name('export-daily-pdf');
        Route::get('/export-weekly-pdf', [\App\Http\Controllers\AttendanceController::class, 'exportWeeklyPDF'])->name('export-weekly-pdf');
        Route::get('/export-monthly-pdf', [\App\Http\Controllers\AttendanceController::class, 'exportMonthlyPDF'])->name('export-monthly-pdf');
        Route::get('/export-yearly-pdf', [\App\Http\Controllers\AttendanceController::class, 'exportYearlyPDF'])->name('export-yearly-pdf');
        
        // Debug PDF Route
        Route::get('/debug-pdf', [\App\Http\Controllers\AttendanceController::class, 'debugPDF'])->name('debug-pdf');
        
        // Test Daily PDF Data Route
        Route::get('/test-daily-data', function() {
            return redirect()->to('/attendance/export-daily-pdf?debug=1');
        })->name('test-daily-data');
    });
});

// Timesheet Routes (Protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    Route::prefix('timesheets')->name('timesheets.')->group(function () {
        // API routes for employee timesheets
        Route::get('/employee-timesheets', [\App\Http\Controllers\TimesheetController::class, 'getEmployeeTimesheets'])->name('employee-timesheets');
        Route::get('/stats', [\App\Http\Controllers\TimesheetController::class, 'getTimesheetStats'])->name('stats');
        Route::put('/{timesheet}', [\App\Http\Controllers\TimesheetController::class, 'updateTimesheet'])->name('update');
        Route::delete('/{timesheet}', [\App\Http\Controllers\TimesheetController::class, 'deleteTimesheet'])->name('delete');
        
        // Approval/Rejection routes
        Route::patch('/{timesheet}/approve', [\App\Http\Controllers\TimesheetController::class, 'approveTimesheet'])->name('approve');
        Route::patch('/{timesheet}/reject', [\App\Http\Controllers\TimesheetController::class, 'rejectTimesheet'])->name('reject');
        Route::post('/bulk-approve', [\App\Http\Controllers\TimesheetController::class, 'bulkApproveTimesheets'])->name('bulk-approve');
        Route::post('/bulk-reject', [\App\Http\Controllers\TimesheetController::class, 'bulkRejectTimesheets'])->name('bulk-reject');
    });
});

// Shift Management Routes (Protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    Route::prefix('shift-management')->name('shift-management.')->group(function () {
        // API endpoints for AJAX operations
        Route::get('/api/templates', [ShiftManagementController::class, 'getShiftTemplates'])->name('api.templates');
        Route::post('/api/templates', [ShiftManagementController::class, 'store'])->name('api.templates.store');
        Route::get('/api/templates/{id}', [ShiftManagementController::class, 'show'])->name('api.templates.show');
        Route::put('/api/templates/{id}', [ShiftManagementController::class, 'update'])->name('api.templates.update');
        Route::delete('/api/templates/{id}', [ShiftManagementController::class, 'destroy'])->name('api.templates.destroy');
        Route::patch('/api/templates/{id}/toggle-status', [ShiftManagementController::class, 'toggleStatus'])->name('api.templates.toggle-status');
        
        // Employee Assignment Routes
        Route::get('/api/employees', [ShiftManagementController::class, 'getEmployeesForAssignment'])->name('api.employees');
        Route::post('/api/assignments', [ShiftManagementController::class, 'storeAssignment'])->name('api.assignments.store');
        Route::put('/api/assignments/{id}', [ShiftManagementController::class, 'updateAssignment'])->name('api.assignments.update');
        Route::delete('/api/assignments/{id}', [ShiftManagementController::class, 'removeAssignment'])->name('api.assignments.destroy');
        
    // Shift calendar API endpoints
    Route::get('/api/calendar-data', [ShiftManagementController::class, 'getShiftCalendarDataApi'])->name('api.calendar-data');
    
    // Shift request API endpoints
    Route::patch('/api/shift-requests/{id}/approve', [ShiftManagementController::class, 'approveShiftRequest'])->name('api.shift-requests.approve');
    Route::patch('/api/shift-requests/{id}/reject', [ShiftManagementController::class, 'rejectShiftRequest'])->name('api.shift-requests.reject');
    });
});
