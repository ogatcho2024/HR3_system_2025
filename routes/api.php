<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SimpleAuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeSyncController;
use App\Http\Controllers\Api\TimesheetController;
use App\Http\Controllers\TimesheetPayrollSyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned the "api" middleware group. Make something great!
|
*/

// Handle preflight OPTIONS requests
Route::options('/{any}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Request-With');
})->where('any', '.*');

// Test route
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => now()->toISOString()
    ]);
});

// Mobile App Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [SimpleAuthController::class, 'login']);
    
    Route::middleware('simple.api.auth')->group(function () {
        Route::get('/user', [SimpleAuthController::class, 'user']);
        Route::post('/logout', [SimpleAuthController::class, 'logout']);
    });
});

// Mobile App Employee Routes
Route::prefix('employee')->middleware('simple.api.auth')->group(function () {
    // Clock in/out
    Route::post('/clock-in', [EmployeeController::class, 'clockIn']);
    Route::post('/clock-out', [EmployeeController::class, 'clockOut']);
    Route::get('/clock-status', [EmployeeController::class, 'getClockStatus']);
    
    // Attendance
    Route::get('/attendance', [EmployeeController::class, 'getAttendance']);
    
    // Profile
    Route::put('/profile', [SimpleAuthController::class, 'updateProfile']);
});

// Legacy route for compatibility
Route::middleware('simple.api.auth')->get('/user', function (Request $request) {
    return $request->attributes->get('authenticated_user');
});

// Employee Sync API Routes (for external microservice integration)
Route::prefix('employee-sync')->group(function () {
    // Webhook endpoint for receiving employee data from external microservice
    Route::post('/webhook', [EmployeeSyncController::class, 'receiveEmployeeData'])
        ->middleware('api')
        ->name('employee-sync.webhook');
    
    // Batch sync endpoint for multiple employees
    Route::post('/batch', [EmployeeSyncController::class, 'batchSync'])
        ->middleware('api')
        ->name('employee-sync.batch');
    
    // Get sync status for monitoring
    Route::get('/status', [EmployeeSyncController::class, 'getSyncStatus'])
        ->middleware('simple.api.auth')
        ->name('employee-sync.status');
    
    // Retry failed syncs
    Route::post('/retry-failed', [EmployeeSyncController::class, 'retryFailedSyncs'])
        ->middleware('simple.api.auth')
        ->name('employee-sync.retry-failed');
});

// Timesheet API Routes (for subdomain system integration)
Route::prefix('timesheets')->middleware('simple.api.auth')->group(function () {
    // Get all timesheets with filtering and pagination
    Route::get('/', [TimesheetController::class, 'index'])
        ->name('api.timesheets.index');
    
    // Get a specific timesheet by ID
    Route::get('/{id}', [TimesheetController::class, 'show'])
        ->name('api.timesheets.show');
});

// Payroll Sync API Routes (for sending timesheets to external payroll system)
Route::prefix('payroll-sync')->middleware(['auth', 'simple.api.auth'])->group(function () {
    // Send single timesheet to payroll (synchronous)
    Route::post('/send/{timesheetId}', [TimesheetPayrollSyncController::class, 'sendTimesheet'])
        ->name('api.payroll-sync.send');
    
    // Queue single timesheet for async sending
    Route::post('/queue/{timesheetId}', [TimesheetPayrollSyncController::class, 'queueTimesheet'])
        ->name('api.payroll-sync.queue');
    
    // Send multiple timesheets (synchronous batch)
    Route::post('/send-batch', [TimesheetPayrollSyncController::class, 'sendBatch'])
        ->name('api.payroll-sync.send-batch');
    
    // Queue multiple timesheets (async batch)
    Route::post('/queue-batch', [TimesheetPayrollSyncController::class, 'queueBatch'])
        ->name('api.payroll-sync.queue-batch');
    
    // Auto-sync timesheets by date range
    Route::post('/sync-date-range', [TimesheetPayrollSyncController::class, 'autoSyncDateRange'])
        ->name('api.payroll-sync.date-range');
});

// User Sync API Routes (for receiving user data from admin.cranecali-ms.com)
Route::prefix('user-sync')->group(function () {
    // Webhook endpoint for receiving single user data (secured with signature verification)
    Route::post('/webhook', [App\Http\Controllers\Api\UserSyncController::class, 'receiveUserData'])
        ->middleware('webhook.signature')
        ->name('user-sync.webhook');
    
    // Batch sync endpoint for multiple users (secured with signature verification)
    Route::post('/batch', [App\Http\Controllers\Api\UserSyncController::class, 'batchSync'])
        ->middleware('webhook.signature')
        ->name('user-sync.batch');
    
    // Get sync status for monitoring (requires admin authentication)
    Route::get('/status', [App\Http\Controllers\Api\UserSyncController::class, 'getSyncStatus'])
        ->middleware('simple.api.auth')
        ->name('user-sync.status');
    
    // Retry failed syncs (requires admin authentication)
    Route::post('/retry-failed', [App\Http\Controllers\Api\UserSyncController::class, 'retryFailedSyncs'])
        ->middleware('simple.api.auth')
        ->name('user-sync.retry-failed');
});
