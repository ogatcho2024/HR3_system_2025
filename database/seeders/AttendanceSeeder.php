<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing attendance records
        DB::table('attendances')->delete();
        
        // Get all active employees with users
        $employees = Employee::with('user')
            ->where('status', 'active')
            ->whereHas('user')
            ->get();

        if ($employees->isEmpty()) {
            $this->command->info('No active employees found. Please seed employees first.');
            return;
        }

        // Generate attendance records for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        
        $attendanceRecords = [];
        $adminUser = User::where('email', 'admin@example.com')->first();
        $createdBy = $adminUser ? $adminUser->id : 1;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip weekends for most attendance records
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($employees as $employee) {
                // 85% chance of attendance on weekdays
                $attendanceChance = rand(1, 100);
                
                if ($attendanceChance <= 85) {
                    // Determine status based on probability
                    $statusChance = rand(1, 100);
                    if ($statusChance <= 80) {
                        $status = 'present';
                    } elseif ($statusChance <= 95) {
                        $status = 'late';
                    } else {
                        $status = 'on_break';
                    }
                    
                    // Generate realistic clock-in times
                    $baseClockIn = Carbon::parse($date->format('Y-m-d') . ' 08:00:00');
                    
                    if ($status === 'late') {
                        // Late arrivals: 8:15 AM to 9:30 AM
                        $clockInMinutes = rand(15, 90);
                    } else {
                        // Normal arrivals: 7:45 AM to 8:15 AM
                        $clockInMinutes = rand(-15, 15);
                    }
                    
                    $clockIn = $baseClockIn->copy()->addMinutes($clockInMinutes);
                    
                    // Generate clock-out times (8-9 hours later)
                    $workHours = rand(8, 9);
                    $workMinutes = rand(0, 59);
                    $clockOut = $clockIn->copy()->addHours($workHours)->addMinutes($workMinutes);
                    
                    // Generate break times (usually 1 hour lunch)
                    $breakStart = $clockIn->copy()->addHours(rand(4, 5)); // 4-5 hours after start
                    $breakEnd = $breakStart->copy()->addHour(); // 1 hour break
                    
                    // Calculate hours worked (excluding break)
                    $totalMinutes = $clockOut->diffInMinutes($clockIn);
                    $breakMinutes = $breakEnd->diffInMinutes($breakStart);
                    $workingMinutes = max(0, $totalMinutes - $breakMinutes); // Ensure positive
                    $hoursWorked = round($workingMinutes / 60, 2);
                    
                    // Calculate overtime (anything over 8 hours)
                    $overtimeHours = $hoursWorked > 8 ? round($hoursWorked - 8, 2) : 0;
                    
                    $attendanceRecords[] = [
                        'user_id' => $employee->user->id,
                        'date' => $date->format('Y-m-d'),
                        'clock_in_time' => $clockIn->format('H:i:s'),
                        'clock_out_time' => $clockOut->format('H:i:s'),
                        'break_start' => $breakStart->format('H:i:s'),
                        'break_end' => $breakEnd->format('H:i:s'),
                        'hours_worked' => $hoursWorked,
                        'overtime_hours' => $overtimeHours,
                        'status' => $status,
                        'notes' => $this->generateNotes($status),
                        'created_by' => $createdBy,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    // Create absent record
                    $attendanceRecords[] = [
                        'user_id' => $employee->user->id,
                        'date' => $date->format('Y-m-d'),
                        'clock_in_time' => null,
                        'clock_out_time' => null,
                        'break_start' => null,
                        'break_end' => null,
                        'hours_worked' => 0,
                        'overtime_hours' => 0,
                        'status' => 'absent',
                        'notes' => 'No attendance recorded',
                        'created_by' => $createdBy,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        // Insert all records in chunks for better performance
        $chunks = array_chunk($attendanceRecords, 100);
        foreach ($chunks as $chunk) {
            DB::table('attendances')->insert($chunk);
        }
        
        $this->command->info('Successfully seeded ' . count($attendanceRecords) . ' attendance records.');
    }
    
    /**
     * Generate realistic notes based on status
     */
    private function generateNotes(string $status): ?string
    {
        $notes = [
            'present' => [
                'Regular attendance',
                'On time',
                'Full day worked',
                null,
            ],
            'late' => [
                'Traffic delay',
                'Personal appointment',
                'Transportation issue',
                'Late arrival - approved',
            ],
            'on_break' => [
                'Extended lunch break',
                'Medical appointment',
                'Personal emergency',
            ],
            'absent' => [
                'Sick leave',
                'Personal leave',
                'Unplanned absence',
                'No show',
            ],
        ];
        
        $statusNotes = $notes[$status] ?? [null];
        return $statusNotes[array_rand($statusNotes)];
    }
}
