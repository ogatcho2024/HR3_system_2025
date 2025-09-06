<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeSync;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncEmployeesFromExternal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:sync 
                            {--url= : External microservice API URL}
                            {--token= : API authentication token}
                            {--service=employee-microservice : Source service name}
                            {--timeout=30 : Request timeout in seconds}
                            {--dry-run : Preview changes without applying them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync employee data from external microservice API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting employee sync from external microservice...');
        
        // Get configuration
        $apiUrl = $this->option('url') ?? config('services.employee_microservice.url');
        $apiToken = $this->option('token') ?? config('services.employee_microservice.token');
        $sourceService = $this->option('service');
        $timeout = (int) $this->option('timeout');
        $dryRun = $this->option('dry-run');
        
        if (!$apiUrl) {
            $this->error('API URL is required. Use --url option or set EMPLOYEE_MICROSERVICE_URL in .env');
            return Command::FAILURE;
        }
        
        if (!$apiToken) {
            $this->error('API token is required. Use --token option or set EMPLOYEE_MICROSERVICE_TOKEN in .env');
            return Command::FAILURE;
        }
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE: No changes will be applied');
        }
        
        try {
            // Fetch employees from external API
            $this->info('Fetching employee data from: ' . $apiUrl);
            
            $response = Http::timeout($timeout)
                ->withToken($apiToken)
                ->get($apiUrl . '/employees');
            
            if (!$response->successful()) {
                $this->error('Failed to fetch employees from external API: HTTP ' . $response->status());
                $this->error('Response: ' . $response->body());
                return Command::FAILURE;
            }
            
            $externalEmployees = $response->json('data', $response->json());
            
            if (empty($externalEmployees)) {
                $this->warn('No employee data received from external API');
                return Command::SUCCESS;
            }
            
            $this->info('Received ' . count($externalEmployees) . ' employees from external API');
            
            // Process each employee
            $successCount = 0;
            $failureCount = 0;
            $createCount = 0;
            $updateCount = 0;
            
            $progressBar = $this->output->createProgressBar(count($externalEmployees));
            $progressBar->start();
            
            DB::beginTransaction();
            
            foreach ($externalEmployees as $employeeData) {
                try {
                    if (!isset($employeeData['id']) || !isset($employeeData['email'])) {
                        $this->error('\nInvalid employee data: missing id or email');
                        $failureCount++;
                        continue;
                    }
                    
                    $externalId = (string) $employeeData['id'];
                    
                    // Find or create sync record
                    $syncRecord = EmployeeSync::byExternalId($externalId)->first();
                    $isNewEmployee = !$syncRecord;
                    
                    if (!$syncRecord) {
                        if (!$dryRun) {
                            $syncRecord = EmployeeSync::create([
                                'external_id' => $externalId,
                                'external_data' => $employeeData,
                                'sync_status' => 'pending',
                                'source_service' => $sourceService,
                                'api_version' => $response->header('API-Version')
                            ]);
                        }
                        $createCount++;
                    } else {
                        if (!$dryRun) {
                            $syncRecord->updateExternalData($employeeData);
                        }
                        $updateCount++;
                    }
                    
                    if (!$dryRun) {
                        $result = $this->processSyncEmployee($syncRecord, $employeeData, $isNewEmployee ? 'create' : 'update');
                        
                        if ($result['success']) {
                            $syncRecord->markAsSynced();
                            $successCount++;
                        } else {
                            $syncRecord->markAsFailed($result['error']);
                            $this->error('\nFailed to sync employee ' . $externalId . ': ' . $result['error']);
                            $failureCount++;
                        }
                    } else {
                        $successCount++;
                    }
                    
                } catch (\Exception $e) {
                    $this->error('\nException syncing employee: ' . $e->getMessage());
                    $failureCount++;
                }
                
                $progressBar->advance();
            }
            
            $progressBar->finish();
            
            if (!$dryRun) {
                DB::commit();
            } else {
                DB::rollback();
            }
            
            // Display results
            $this->newLine(2);
            $this->info('Sync completed!');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total processed', count($externalEmployees)],
                    ['New employees', $createCount],
                    ['Updated employees', $updateCount],
                    ['Successful syncs', $successCount],
                    ['Failed syncs', $failureCount]
                ]
            );
            
            if ($dryRun) {
                $this->warn('DRY RUN completed - no changes were made');
            }
            
            return $failureCount > 0 ? Command::FAILURE : Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Sync failed: ' . $e->getMessage());
            Log::error('Employee sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
    
    /**
     * Process sync for a single employee
     */
    private function processSyncEmployee(EmployeeSync $syncRecord, array $employeeData, string $action): array
    {
        try {
            switch ($action) {
                case 'create':
                    return $this->createEmployee($syncRecord, $employeeData);
                    
                case 'update':
                    return $this->updateEmployee($syncRecord, $employeeData);
                    
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
                $existingUser->update([
                    'name' => $employeeData['name'] ?? $employeeData['first_name'] . ' ' . $employeeData['last_name'],
                    'email' => $employeeData['email']
                ]);
                $user = $existingUser;
            } else {
                $user = User::create([
                    'name' => $employeeData['name'] ?? $employeeData['first_name'] . ' ' . $employeeData['last_name'],
                    'email' => $employeeData['email'],
                    'password' => bcrypt('temp_password_' . $employeeData['id']),
                    'email_verified_at' => now()
                ]);
            }
            
            // Create employee record
            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_id' => $employeeData['employee_number'] ?? 'EXT_' . $employeeData['id'],
                'external_id' => $employeeData['id'],
                'position' => $employeeData['position'] ?? $employeeData['job_title'] ?? null,
                'department' => $employeeData['department'] ?? null,
                'salary' => $employeeData['salary'] ?? null,
                'hire_date' => isset($employeeData['hire_date']) ? Carbon::parse($employeeData['hire_date']) : null,
                'status' => $employeeData['status'] ?? 'active'
            ]);
            
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
                // Try to find employee by external_id
                $employee = Employee::where('external_id', $employeeData['id'])->first();
                if (!$employee) {
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
                'name' => $employeeData['name'] ?? $employeeData['first_name'] . ' ' . $employeeData['last_name'],
                'email' => $employeeData['email']
            ]);
            
            // Update employee data
            $updateData = ['external_id' => $employeeData['id']];
            
            if (isset($employeeData['position']) || isset($employeeData['job_title'])) {
                $updateData['position'] = $employeeData['position'] ?? $employeeData['job_title'];
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
            
            if (isset($employeeData['employee_number'])) {
                $updateData['employee_id'] = $employeeData['employee_number'];
            }
            
            $employee->update($updateData);
            
            return ['success' => true, 'employee_id' => $employee->id];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Failed to update employee: ' . $e->getMessage()];
        }
    }
}
