<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSync;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EmployeeSyncController extends Controller
{
    /**
     * Receive employee data from external microservice (webhook endpoint)
     */
    public function receiveEmployeeData(Request $request): JsonResponse
    {
        try {
            // Validate the incoming data
            $validator = Validator::make($request->all(), [
                'action' => 'required|string|in:create,update,delete',
                'employee' => 'required|array',
                'employee.external_id' => 'required|string',
                'employee.email' => 'required_if:action,create,update|email',
                'employee.name' => 'required_if:action,create,update|string',
                'employee.position' => 'nullable|string',
                'employee.department' => 'nullable|string',
                'employee.salary' => 'nullable|numeric',
                'employee.hire_date' => 'nullable|date',
                'employee.status' => 'nullable|string|in:active,inactive',
                'api_version' => 'nullable|string',
                'source_service' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $action = $request->input('action');
            $employeeData = $request->input('employee');
            $externalId = $employeeData['external_id'];
            
            DB::beginTransaction();
            
            // Find or create sync record
            $syncRecord = EmployeeSync::byExternalId($externalId)->first();
            if (!$syncRecord) {
                $syncRecord = EmployeeSync::create([
                    'external_id' => $externalId,
                    'external_data' => $employeeData,
                    'sync_status' => 'pending',
                    'source_service' => $request->input('source_service', 'employee-microservice'),
                    'api_version' => $request->input('api_version')
                ]);
            } else {
                $syncRecord->updateExternalData($employeeData);
            }
            
            // Process the sync based on action
            $result = $this->processSyncAction($action, $syncRecord, $employeeData);
            
            if ($result['success']) {
                $syncRecord->markAsSynced();
                DB::commit();
                
                Log::info('Employee sync successful', [
                    'action' => $action,
                    'external_id' => $externalId,
                    'employee_id' => $syncRecord->employee_id
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Employee synced successfully',
                    'data' => [
                        'external_id' => $externalId,
                        'employee_id' => $syncRecord->employee_id,
                        'action' => $action
                    ]
                ]);
            } else {
                $syncRecord->markAsFailed($result['error']);
                DB::rollback();
                
                Log::error('Employee sync failed', [
                    'action' => $action,
                    'external_id' => $externalId,
                    'error' => $result['error']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Employee sync failed',
                    'error' => $result['error']
                ], 400);
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Employee sync exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error during sync',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Batch sync multiple employees
     */
    public function batchSync(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'employees' => 'required|array|min:1|max:100',
                'employees.*.action' => 'required|string|in:create,update,delete',
                'employees.*.employee' => 'required|array',
                'employees.*.employee.external_id' => 'required|string',
                'api_version' => 'nullable|string',
                'source_service' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $employees = $request->input('employees');
            $results = [];
            $successCount = 0;
            $failureCount = 0;
            
            DB::beginTransaction();
            
            foreach ($employees as $employeeUpdate) {
                try {
                    $action = $employeeUpdate['action'];
                    $employeeData = $employeeUpdate['employee'];
                    $externalId = $employeeData['external_id'];
                    
                    // Find or create sync record
                    $syncRecord = EmployeeSync::byExternalId($externalId)->first();
                    if (!$syncRecord) {
                        $syncRecord = EmployeeSync::create([
                            'external_id' => $externalId,
                            'external_data' => $employeeData,
                            'sync_status' => 'pending',
                            'source_service' => $request->input('source_service', 'employee-microservice'),
                            'api_version' => $request->input('api_version')
                        ]);
                    } else {
                        $syncRecord->updateExternalData($employeeData);
                    }
                    
                    $result = $this->processSyncAction($action, $syncRecord, $employeeData);
                    
                    if ($result['success']) {
                        $syncRecord->markAsSynced();
                        $successCount++;
                        $results[] = [
                            'external_id' => $externalId,
                            'success' => true,
                            'action' => $action
                        ];
                    } else {
                        $syncRecord->markAsFailed($result['error']);
                        $failureCount++;
                        $results[] = [
                            'external_id' => $externalId,
                            'success' => false,
                            'error' => $result['error'],
                            'action' => $action
                        ];
                    }
                    
                } catch (\Exception $e) {
                    $failureCount++;
                    $results[] = [
                        'external_id' => $employeeData['external_id'] ?? 'unknown',
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Batch sync completed: {$successCount} successful, {$failureCount} failed",
                'summary' => [
                    'total' => count($employees),
                    'successful' => $successCount,
                    'failed' => $failureCount
                ],
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Batch employee sync exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error during batch sync',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get sync status for monitoring
     */
    public function getSyncStatus(Request $request): JsonResponse
    {
        try {
            $query = EmployeeSync::query();
            
            // Filter by status if provided
            if ($request->filled('status')) {
                $query->where('sync_status', $request->input('status'));
            }
            
            // Filter by source service
            if ($request->filled('source_service')) {
                $query->where('source_service', $request->input('source_service'));
            }
            
            // Get recent records (last 24 hours by default)
            $hours = $request->input('hours', 24);
            $query->where('updated_at', '>=', now()->subHours($hours));
            
            $syncRecords = $query->with('employee')->paginate(50);
            
            // Calculate statistics
            $stats = [
                'total' => EmployeeSync::count(),
                'pending' => EmployeeSync::pending()->count(),
                'synced' => EmployeeSync::synced()->count(),
                'failed' => EmployeeSync::failed()->count(),
                'deleted' => EmployeeSync::where('sync_status', 'deleted')->count(),
                'last_sync' => EmployeeSync::latest('last_sync_at')->value('last_sync_at')
            ];
            
            return response()->json([
                'success' => true,
                'data' => $syncRecords,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sync status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process sync action (create, update, delete)
     */
    private function processSyncAction(string $action, EmployeeSync $syncRecord, array $employeeData): array
    {
        try {
            switch ($action) {
                case 'create':
                    return $this->createEmployee($syncRecord, $employeeData);
                    
                case 'update':
                    return $this->updateEmployee($syncRecord, $employeeData);
                    
                case 'delete':
                    return $this->deleteEmployee($syncRecord, $employeeData);
                    
                default:
                    return ['success' => false, 'error' => 'Unknown action: ' . $action];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create new employee from external data
     */
    private function createEmployee(EmployeeSync $syncRecord, array $employeeData): array
    {
        try {
            // Check if user already exists by email
            $existingUser = User::where('email', $employeeData['email'])->first();
            if ($existingUser) {
                // Update existing user if needed
                $existingUser->update([
                    'name' => $employeeData['name'],
                    'email' => $employeeData['email']
                ]);
                $user = $existingUser;
            } else {
                // Create new user
                $user = User::create([
                    'name' => $employeeData['name'],
                    'email' => $employeeData['email'],
                    'password' => bcrypt('temp_password_' . $employeeData['external_id']), // Temporary password
                    'email_verified_at' => now()
                ]);
            }
            
            // Check if employee record already exists
            $existingEmployee = Employee::where('user_id', $user->id)->first();
            if ($existingEmployee) {
                // Update existing employee
                $employee = $this->updateEmployeeRecord($existingEmployee, $employeeData);
            } else {
                // Create new employee
                $employee = Employee::create([
                    'user_id' => $user->id,
                    'external_id' => $employeeData['external_id'],
                    'position' => $employeeData['position'] ?? null,
                    'department' => $employeeData['department'] ?? null,
                    'salary' => $employeeData['salary'] ?? null,
                    'hire_date' => isset($employeeData['hire_date']) ? Carbon::parse($employeeData['hire_date']) : null,
                    'status' => $employeeData['status'] ?? 'active'
                ]);
            }
            
            // Update sync record with employee ID
            $syncRecord->update(['employee_id' => $employee->id]);
            
            return ['success' => true, 'employee_id' => $employee->id];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Failed to create employee: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update existing employee from external data
     */
    private function updateEmployee(EmployeeSync $syncRecord, array $employeeData): array
    {
        try {
            if (!$syncRecord->employee_id) {
                // Try to find employee by external_id in employees table
                $employee = Employee::where('external_id', $employeeData['external_id'])->first();
                if (!$employee) {
                    // Employee doesn't exist locally, create it
                    return $this->createEmployee($syncRecord, $employeeData);
                }
                $syncRecord->update(['employee_id' => $employee->id]);
            } else {
                $employee = $syncRecord->employee;
                if (!$employee) {
                    return ['success' => false, 'error' => 'Employee not found locally'];
                }
            }
            
            // Update user data
            $employee->user->update([
                'name' => $employeeData['name'],
                'email' => $employeeData['email']
            ]);
            
            // Update employee data
            $this->updateEmployeeRecord($employee, $employeeData);
            
            return ['success' => true, 'employee_id' => $employee->id];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Failed to update employee: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete employee (soft delete)
     */
    private function deleteEmployee(EmployeeSync $syncRecord, array $employeeData): array
    {
        try {
            if ($syncRecord->employee_id) {
                $employee = $syncRecord->employee;
                if ($employee) {
                    // Soft delete employee (set status to inactive)
                    $employee->update(['status' => 'inactive']);
                    
                    // Optionally soft delete user as well
                    $employee->user->update(['email_verified_at' => null]); // Disable login
                }
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Failed to delete employee: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update employee record with external data
     */
    private function updateEmployeeRecord(Employee $employee, array $employeeData): Employee
    {
        $updateData = [
            'external_id' => $employeeData['external_id']
        ];
        
        if (isset($employeeData['position'])) {
            $updateData['position'] = $employeeData['position'];
        }
        
        if (isset($employeeData['department'])) {
            $updateData['department'] = $employeeData['department'];
        }
        
        if (isset($employeeData['salary'])) {
            $updateData['salary'] = $employeeData['salary'];
        }
        
        if (isset($employeeData['hire_date'])) {
            $updateData['hire_date'] = Carbon::parse($employeeData['hire_date']);
        }
        
        if (isset($employeeData['status'])) {
            $updateData['status'] = $employeeData['status'];
        }
        
        $employee->update($updateData);
        
        return $employee;
    }
    
    /**
     * Retry failed syncs
     */
    public function retryFailedSyncs(Request $request): JsonResponse
    {
        try {
            $failedSyncs = EmployeeSync::failed()
                ->where('sync_attempts', '<', 3)
                ->get();
            
            $retryResults = [];
            $successCount = 0;
            
            foreach ($failedSyncs as $syncRecord) {
                $result = $this->processSyncAction('update', $syncRecord, $syncRecord->external_data);
                
                if ($result['success']) {
                    $syncRecord->markAsSynced();
                    $successCount++;
                    $retryResults[] = [
                        'external_id' => $syncRecord->external_id,
                        'success' => true
                    ];
                } else {
                    $syncRecord->markAsFailed($result['error']);
                    $retryResults[] = [
                        'external_id' => $syncRecord->external_id,
                        'success' => false,
                        'error' => $result['error']
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Retry completed: {$successCount} successful out of " . count($failedSyncs),
                'results' => $retryResults
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry syncs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
