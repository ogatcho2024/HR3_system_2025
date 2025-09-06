<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AttendanceController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class TestAttendanceAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:attendance-api {--create-sample-data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the attendance API and optionally create sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('create-sample-data')) {
            $this->createSampleData();
        }
        
        $this->testAPI();
        return Command::SUCCESS;
    }
    
    private function createSampleData()
    {
        $this->info('Creating sample attendance data...');
        
        // Get existing users and create employees if they don't exist
        $users = User::all();
        if ($users->isEmpty()) {
            $this->error('No users found. Please create some users first.');
            return;
        }
        
        $departments = ['IT', 'Marketing', 'Finance', 'HR'];
        $positions = ['Developer', 'Manager', 'Analyst', 'Coordinator'];
        
        foreach ($users as $index => $user) {
            // Create employee record if it doesn't exist
            $employee = Employee::where('user_id', $user->id)->first();
            if (!$employee) {
                Employee::create([
                    'user_id' => $user->id,
                    'employee_id' => 'EMP' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                    'department' => $departments[$index % count($departments)],
                    'position' => $positions[$index % count($positions)],
                    'hire_date' => Carbon::now()->subDays(rand(30, 365)),
                    'status' => 'active',
                ]);
            }
            
            // Create today's attendance if it doesn't exist
            $today = Carbon::now()->toDateString();
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->first();
                
            if (!$existingAttendance) {
                $statuses = ['present', 'late', 'absent', 'on_break'];
                $clockInTimes = ['08:00', '08:15', '08:30', '08:45', '09:00'];
                
                // 80% chance of having attendance record
                if (rand(1, 10) <= 8) {
                    $status = $statuses[array_rand($statuses)];
                    
                    // Skip if absent
                    if ($status !== 'absent') {
                        $clockInTime = $clockInTimes[array_rand($clockInTimes)];
                        
                        Attendance::create([
                            'user_id' => $user->id,
                            'date' => $today,
                            'clock_in_time' => $clockInTime,
                            'status' => $status,
                            'created_by' => $user->id,
                        ]);
                    }
                }
            }
        }
        
        $this->info('Sample data created successfully.');
    }
    
    private function testAPI()
    {
        $this->info('Testing Attendance API...');
        
        // Test the real-time data endpoint
        $controller = new AttendanceController();
        $request = new Request();
        
        $response = $controller->getRealTimeData($request);
        $data = json_decode($response->getContent(), true);
        
        $this->info('API Response:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Employees', $data['stats']['total'] ?? 0],
                ['Present', $data['stats']['present'] ?? 0],
                ['Late', $data['stats']['late'] ?? 0],
                ['Absent', $data['stats']['absent'] ?? 0],
                ['On Break', $data['stats']['break'] ?? 0],
                ['Employee Records Returned', count($data['employees'] ?? [])],
            ]
        );
        
        // Show sample employees
        if (!empty($data['employees'])) {
            $this->info('Sample Employees:');
            $employees = array_slice($data['employees'], 0, 5);
            $this->table(
                ['Name', 'Department', 'Position', 'Status', 'Check In'],
                array_map(function($emp) {
                    return [
                        $emp['name'],
                        $emp['department'],
                        $emp['position'],
                        $emp['status'],
                        $emp['checkIn'] ?? 'N/A'
                    ];
                }, $employees)
            );
        } else {
            $this->warn('No employee data returned. Try running with --create-sample-data');
        }
        
        // Test different status filters
        $this->info('Testing status filters...');
        foreach (['all', 'present', 'late', 'absent', 'break'] as $status) {
            $request = new Request(['status' => $status]);
            $response = $controller->getRealTimeData($request);
            $data = json_decode($response->getContent(), true);
            $count = count($data['employees'] ?? []);
            $this->line("Filter '{$status}': {$count} employees");
        }
        
        $this->info('API test completed successfully!');
    }
}
