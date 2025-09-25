<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;

class TimesheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->count() === 0) {
            $this->command->error('No users found. Please create some users first.');
            return;
        }

        $statuses = ['draft', 'submitted', 'approved', 'rejected'];
        $projects = ['Web Development', 'Mobile App', 'Database Migration', 'UI/UX Design', 'Testing', 'Documentation'];
        $descriptions = [
            'Working on frontend components',
            'Database optimization tasks', 
            'Bug fixes and testing',
            'Client meeting and requirements gathering',
            'Code review and documentation',
            'Project planning and analysis'
        ];

        // Create sample timesheets - ensure unique user_id + work_date combinations
        $createdCount = 0;
        $attempts = 0;
        $maxAttempts = 100;
        
        while ($createdCount < 15 && $attempts < $maxAttempts) {
            $user = $users->random();
            $workDate = Carbon::now()->subDays(rand(0, 30));
            
            // Check if this combination already exists
            $exists = Timesheet::where('user_id', $user->id)
                ->where('work_date', $workDate->toDateString())
                ->exists();
                
            if (!$exists) {
                $clockIn = $workDate->copy()->setTime(8 + rand(0, 2), rand(0, 59));
                $hoursWorked = 7 + rand(0, 4) + (rand(0, 1) * 0.5); // 7-11 hours
                $clockOut = $clockIn->copy()->addHours(floor($hoursWorked))->addMinutes(($hoursWorked - floor($hoursWorked)) * 60);
                $overtimeHours = max(0, $hoursWorked - 8);
                $status = $statuses[array_rand($statuses)];
                
                Timesheet::create([
                    'user_id' => $user->id,
                    'work_date' => $workDate->toDateString(),
                    'clock_in_time' => $clockIn->format('H:i'),
                    'clock_out_time' => $clockOut->format('H:i'),
                    'break_start' => '12:00',
                    'break_end' => '13:00',
                    'hours_worked' => $hoursWorked,
                    'overtime_hours' => $overtimeHours,
                    'project_name' => $projects[array_rand($projects)],
                    'work_description' => $descriptions[array_rand($descriptions)],
                    'status' => $status,
                    'submitted_at' => in_array($status, ['submitted', 'approved', 'rejected']) 
                        ? $workDate->copy()->addHours(rand(1, 8)) 
                        : null,
                    'approved_at' => $status === 'approved' 
                        ? $workDate->copy()->addDays(rand(1, 3)) 
                        : null,
                    'approved_by' => $status === 'approved' 
                        ? $users->random()->id 
                        : null,
                ]);
                
                $createdCount++;
            }
            
            $attempts++;
        }

        $this->command->info("{$createdCount} sample timesheets created successfully!");
    }
}
