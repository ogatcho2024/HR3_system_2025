<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSync;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSyncController extends Controller
{
    /**
     * Receive user data from external service (webhook endpoint).
     * 
     * Expected payload:
     * {
     *   "user": {
     *     "external_user_id": "123",
     *     "email": "john@example.com",
     *     "name": "John",
     *     "lastname": "Doe",
     *     "phone": "+1234567890",
     *     "position": "Manager",
     *     "account_type": "Employee",
     *     "role": "employee",
     *     "is_active": true,
     *     ...
     *   },
     *   "api_version": "1.0",
     *   "source_service": "admin.cranecali-ms.com"
     * }
     */
    public function receiveUserData(Request $request): JsonResponse
    {
        try {
            // Validate the incoming data
            $validator = $this->validateUserData($request->all());
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userData = $request->input('user');
            $externalUserId = $userData['external_user_id'];
            
            DB::beginTransaction();
            
            // Find or create sync record
            $syncRecord = UserSync::byExternalId($externalUserId)->first();
            if (!$syncRecord) {
                $syncRecord = UserSync::create([
                    'external_user_id' => $externalUserId,
                    'external_data' => $userData,
                    'sync_status' => 'pending',
                    'source_service' => $request->input('source_service', config('user_sync.source_service')),
                    'api_version' => $request->input('api_version')
                ]);
            } else {
                $syncRecord->updateExternalData($userData);
            }
            
            // Process the sync (UPSERT user)
            $result = $this->upsertUser($syncRecord, $userData);
            
            if ($result['success']) {
                $syncRecord->markAsSynced();
                DB::commit();
                
                $this->logSync('success', 'User synced successfully', [
                    'external_user_id' => $externalUserId,
                    'user_id' => $syncRecord->user_id,
                    'email' => $userData['email'] ?? null,
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'User synced successfully',
                    'data' => [
                        'external_user_id' => $externalUserId,
                        'user_id' => $syncRecord->user_id,
                        'action' => $result['action'], // 'created' or 'updated'
                    ]
                ]);
            } else {
                $syncRecord->markAsFailed($result['error']);
                DB::rollback();
                
                $this->logSync('error', 'User sync failed', [
                    'external_user_id' => $externalUserId,
                    'error' => $result['error']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'User sync failed',
                    'error' => $result['error']
                ], 400);
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            
            $this->logSync('error', 'User sync exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error during sync',
                'error' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Batch sync multiple users.
     * 
     * Expected payload:
     * {
     *   "users": [
     *     { "external_user_id": "123", "email": "...", ... },
     *     { "external_user_id": "456", "email": "...", ... }
     *   ],
     *   "api_version": "1.0",
     *   "source_service": "admin.cranecali-ms.com"
     * }
     */
    public function batchSync(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'users' => 'required|array|min:1|max:100',
                'users.*.external_user_id' => 'required|string|max:255',
                'users.*.email' => 'required|email|max:255',
                'users.*.name' => 'required|string|max:255',
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
            
            $users = $request->input('users');
            $results = [];
            $successCount = 0;
            $failureCount = 0;
            
            foreach ($users as $userData) {
                try {
                    // Validate individual user data
                    $userValidator = $this->validateUserData(['user' => $userData]);
                    if ($userValidator->fails()) {
                        $failureCount++;
                        $results[] = [
                            'external_user_id' => $userData['external_user_id'] ?? 'unknown',
                            'success' => false,
                            'error' => 'Validation failed: ' . $userValidator->errors()->first()
                        ];
                        continue;
                    }
                    
                    DB::beginTransaction();
                    
                    $externalUserId = $userData['external_user_id'];
                    
                    // Find or create sync record
                    $syncRecord = UserSync::byExternalId($externalUserId)->first();
                    if (!$syncRecord) {
                        $syncRecord = UserSync::create([
                            'external_user_id' => $externalUserId,
                            'external_data' => $userData,
                            'sync_status' => 'pending',
                            'source_service' => $request->input('source_service', config('user_sync.source_service')),
                            'api_version' => $request->input('api_version')
                        ]);
                    } else {
                        $syncRecord->updateExternalData($userData);
                    }
                    
                    $result = $this->upsertUser($syncRecord, $userData);
                    
                    if ($result['success']) {
                        $syncRecord->markAsSynced();
                        DB::commit();
                        $successCount++;
                        $results[] = [
                            'external_user_id' => $externalUserId,
                            'success' => true,
                            'action' => $result['action']
                        ];
                    } else {
                        $syncRecord->markAsFailed($result['error']);
                        DB::rollback();
                        $failureCount++;
                        $results[] = [
                            'external_user_id' => $externalUserId,
                            'success' => false,
                            'error' => $result['error']
                        ];
                    }
                    
                } catch (\Exception $e) {
                    DB::rollback();
                    $failureCount++;
                    $results[] = [
                        'external_user_id' => $userData['external_user_id'] ?? 'unknown',
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            $this->logSync('info', 'Batch sync completed', [
                'total' => count($users),
                'successful' => $successCount,
                'failed' => $failureCount
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Batch sync completed: {$successCount} successful, {$failureCount} failed",
                'summary' => [
                    'total' => count($users),
                    'successful' => $successCount,
                    'failed' => $failureCount
                ],
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            $this->logSync('error', 'Batch sync exception', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error during batch sync',
                'error' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get sync status for monitoring (requires authentication).
     */
    public function getSyncStatus(Request $request): JsonResponse
    {
        try {
            $query = UserSync::query();
            
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
            
            $syncRecords = $query->with('user:id,name,lastname,email')->paginate(50);
            
            // Calculate statistics
            $stats = [
                'total' => UserSync::count(),
                'pending' => UserSync::pending()->count(),
                'synced' => UserSync::synced()->count(),
                'failed' => UserSync::failed()->count(),
                'last_sync' => UserSync::latest('last_sync_at')->value('last_sync_at')
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
     * Retry failed syncs (requires authentication).
     */
    public function retryFailedSyncs(Request $request): JsonResponse
    {
        try {
            $maxRetries = config('user_sync.max_retry_attempts', 3);
            
            $failedSyncs = UserSync::failed()
                ->where('sync_attempts', '<', $maxRetries)
                ->get();
            
            $retryResults = [];
            $successCount = 0;
            
            foreach ($failedSyncs as $syncRecord) {
                DB::beginTransaction();
                
                try {
                    $result = $this->upsertUser($syncRecord, $syncRecord->external_data);
                    
                    if ($result['success']) {
                        $syncRecord->markAsSynced();
                        DB::commit();
                        $successCount++;
                        $retryResults[] = [
                            'external_user_id' => $syncRecord->external_user_id,
                            'success' => true
                        ];
                    } else {
                        $syncRecord->markAsFailed($result['error']);
                        DB::rollback();
                        $retryResults[] = [
                            'external_user_id' => $syncRecord->external_user_id,
                            'success' => false,
                            'error' => $result['error']
                        ];
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    $syncRecord->markAsFailed($e->getMessage());
                    $retryResults[] = [
                        'external_user_id' => $syncRecord->external_user_id,
                        'success' => false,
                        'error' => $e->getMessage()
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

    /**
     * UPSERT user - create new or update existing.
     * This is the core sync logic with field protection.
     */
    private function upsertUser(UserSync $syncRecord, array $userData): array
    {
        try {
            // Find existing user by email (unique identifier)
            $existingUser = User::where('email', $userData['email'])->first();
            
            // Prepare syncable data (only allowed fields)
            $syncableFields = config('user_sync.syncable_fields');
            $syncData = [];
            
            foreach ($syncableFields as $field) {
                if (array_key_exists($field, $userData) && $userData[$field] !== null) {
                    // Type conversion for specific fields
                    if ($field === 'date_of_birth' && !empty($userData[$field])) {
                        $syncData[$field] = Carbon::parse($userData[$field])->format('Y-m-d');
                    } elseif ($field === 'is_active') {
                        $syncData[$field] = (bool) $userData[$field];
                    } else {
                        $syncData[$field] = $userData[$field];
                    }
                }
            }
            
            if ($existingUser) {
                // UPDATE existing user (only syncable fields)
                $existingUser->update($syncData);
                
                // Update sync record with user ID
                $syncRecord->update(['user_id' => $existingUser->id]);
                
                return [
                    'success' => true,
                    'action' => 'updated',
                    'user_id' => $existingUser->id
                ];
            } else {
                // CREATE new user
                $defaults = config('user_sync.new_user_defaults');
                
                // Generate secure random password
                if (config('user_sync.generate_random_password')) {
                    $passwordPrefix = config('user_sync.password_prefix', 'sync_');
                    $syncData['password'] = bcrypt($passwordPrefix . Str::random(32));
                }
                
                // Merge with defaults (syncData takes precedence)
                $newUserData = array_merge($defaults, $syncData);
                
                // Ensure required fields
                if (!isset($newUserData['email']) || !isset($newUserData['name'])) {
                    return [
                        'success' => false,
                        'error' => 'Missing required fields: email and name are mandatory'
                    ];
                }
                
                $newUser = User::create($newUserData);
                
                // Update sync record with user ID
                $syncRecord->update(['user_id' => $newUser->id]);
                
                return [
                    'success' => true,
                    'action' => 'created',
                    'user_id' => $newUser->id
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to upsert user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate user data against configuration rules.
     */
    private function validateUserData(array $data)
    {
        $rules = [];
        $configRules = config('user_sync.validation_rules');
        
        // Prefix all rules with 'user.'
        foreach ($configRules as $field => $rule) {
            $rules["user.{$field}"] = $rule;
        }
        
        return Validator::make($data, $rules);
    }

    /**
     * Log sync events with proper context.
     */
    private function logSync(string $level, string $message, array $context = []): void
    {
        if (!config('user_sync.logging.enabled')) {
            return;
        }
        
        $channel = config('user_sync.logging.channel', 'stack');
        
        Log::channel($channel)->{$level}('[UserSync] ' . $message, $context);
    }
}
