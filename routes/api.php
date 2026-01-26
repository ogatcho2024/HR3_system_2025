<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SimpleAuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeSyncController;
use App\Http\Controllers\Api\TimesheetController;
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
