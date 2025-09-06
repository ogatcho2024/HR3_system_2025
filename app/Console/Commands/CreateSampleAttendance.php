<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CreateSampleAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:create-sample {--days=7 : Number of days to create attendance for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample attendance data for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $this->info("Creating sample attendance data for {$days} days...");
        
        $users = User::whereHas('employee')->get();
        $created = 0;
        
        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($i)->toDateString();
            
            foreach ($users as $user) {
                // Skip if attendance already exists for this date
                if (Attendance::where('user_id', $user->id)->where('date', $date)->exists()) {
                    continue;
                }
                
                // Randomly decide if employee attended (90% attendance rate)
                if (rand(1, 100) <= 90) {
                    // Generate random clock in time (8:00-10:00 AM)
                    $clockInHour = rand(8, 9);
                    $clockInMinute = rand(0, 59);
                    $clockInTime = sprintf('%02d:%02d', $clockInHour, $clockInMinute);
                    
                    // Generate clock out time (4:00-6:00 PM)
                    $clockOutHour = rand(16, 18);
                    $clockOutMinute = rand(0, 59);
                    $clockOutTime = sprintf('%02d:%02d', $clockOutHour, $clockOutMinute);
                    
                    // Calculate hours worked
                    $clockIn = Carbon::createFromTimeString($clockInTime);
                    $clockOut = Carbon::createFromTimeString($clockOutTime);
                    $hoursWorked = $clockOut->diffInHours($clockIn, true);
                    
                    // Determine status
                    $status = ($clockInHour >= 9 && $clockInMinute > 0) ? 'late' : 'present';
                    
                    // Random break times
                    $breakStart = null;
                    $breakEnd = null;
                    if (rand(1, 100) <= 70) { // 70% take breaks
                        $breakStartHour = rand(12, 14);
                        $breakStartMinute = rand(0, 59);
                        $breakStart = sprintf('%02d:%02d', $breakStartHour, $breakStartMinute);
                        
                        $breakEndHour = $breakStartHour;
                        $breakEndMinute = $breakStartMinute + rand(30, 60);
                        if ($breakEndMinute >= 60) {
                            $breakEndHour++;
                            $breakEndMinute -= 60;
                        }
                        $breakEnd = sprintf('%02d:%02d', $breakEndHour, $breakEndMinute);
                    }
                    
                    Attendance::create([
                        'user_id' => $user->id,
                        'date' => $date,
                        'clock_in_time' => $clockInTime,
                        'clock_out_time' => $clockOutTime,
                        'break_start' => $breakStart,
                        'break_end' => $breakEnd,
                        'hours_worked' => round($hoursWorked, 2),
                        'status' => $status
                    ]);
                    
                    $created++;
                }
            }
        }
        
        $this->newLine();
        $this->info("Created {$created} attendance records");
        $this->info("Today's attendance count: " . Attendance::where('date', Carbon::now()->toDateString())->count());
        
        return Command::SUCCESS;
    }
}
