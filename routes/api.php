<?php

use App\Http\Controllers\Api\EmployeeSyncController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
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
        ->middleware('auth:sanctum')
        ->name('employee-sync.status');
    
    // Retry failed syncs
    Route::post('/retry-failed', [EmployeeSyncController::class, 'retryFailedSyncs'])
        ->middleware('auth:sanctum')
        ->name('employee-sync.retry-failed');
});
