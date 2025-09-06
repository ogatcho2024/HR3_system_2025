<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CreateEmployeeRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:create-records {--force : Force create even if employee record exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create employee records for existing users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating employee records for existing users...');
        
        $users = User::all();
        $created = 0;
        $skipped = 0;
        
        foreach ($users as $user) {
            $existingEmployee = Employee::where('user_id', $user->id)->first();
            
            if ($existingEmployee && !$this->option('force')) {
                $this->warn("Skipping user {$user->name} - employee record already exists");
                $skipped++;
                continue;
            }
            
            if ($existingEmployee && $this->option('force')) {
                $existingEmployee->delete();
                $this->warn("Replaced existing employee record for {$user->name}");
            }
            
            // Create employee record
            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_id' => 'EMP' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                'department' => $this->getRandomDepartment(),
                'position' => $this->getRandomPosition(),
                'hire_date' => Carbon::now()->subDays(rand(30, 365)),
                'salary' => rand(50000, 120000),
                'employment_type' => 'full-time',
                'status' => 'active'
            ]);
            
            $this->info("Created employee record for {$user->name} (ID: {$employee->employee_id})");
            $created++;
        }
        
        $this->newLine();
        $this->info("Summary:");
        $this->info("Created: {$created}");
        $this->info("Skipped: {$skipped}");
        $this->info("Total active employees now: " . Employee::active()->count());
        
        return Command::SUCCESS;
    }
    
    private function getRandomDepartment()
    {
        $departments = [
            'Engineering',
            'Human Resources',
            'Marketing',
            'Sales',
            'Finance',
            'Operations',
            'Customer Service',
            'IT Support'
        ];
        
        return $departments[array_rand($departments)];
    }
    
    private function getRandomPosition()
    {
        $positions = [
            'Software Engineer',
            'Senior Developer',
            'Project Manager',
            'Business Analyst',
            'HR Specialist',
            'Marketing Coordinator',
            'Sales Representative',
            'Accountant',
            'Operations Manager',
            'Customer Support'
        ];
        
        return $positions[array_rand($positions)];
    }
}
