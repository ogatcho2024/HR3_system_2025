<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AttendanceTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating test users and employees...');
        
        // Get the highest existing employee ID to avoid conflicts
        $lastEmployeeId = Employee::max('employee_id');
        $startingId = 1;
        if ($lastEmployeeId) {
            // Extract number from employee ID (assuming format like EMP001)
            $lastNumber = (int) substr($lastEmployeeId, 3);
            $startingId = $lastNumber + 1;
        }
        
        // Sample departments and positions
        $departments = ['IT', 'Marketing', 'Finance', 'HR', 'Operations', 'Sales', 'Design', 'Logistics'];
        $positions = [
            'IT' => ['Software Developer', 'System Admin', 'DevOps Engineer', 'Tech Lead'],
            'Marketing' => ['Marketing Manager', 'Content Creator', 'SEO Specialist', 'Social Media Manager'],
            'Finance' => ['Financial Analyst', 'Accountant', 'Finance Manager', 'Auditor'],
            'HR' => ['HR Manager', 'Recruiter', 'HR Generalist', 'Training Coordinator'],
            'Operations' => ['Operations Manager', 'Process Analyst', 'Operations Coordinator', 'Quality Assurance'],
            'Sales' => ['Sales Manager', 'Sales Representative', 'Account Manager', 'Business Development'],
            'Design' => ['UI/UX Designer', 'Graphic Designer', 'Product Designer', 'Creative Director'],
            'Logistics' => ['Logistics Coordinator', 'Supply Chain Manager', 'Warehouse Manager', 'Shipping Specialist']
        ];
        
        $employeeNames = [
            'John Smith', 'Sarah Johnson', 'Mike Davis', 'Emily Brown', 'David Wilson',
            'Lisa Chen', 'Robert Taylor', 'Jennifer Garcia', 'Michael Rodriguez', 'Amanda Martinez',
            'Christopher Lee', 'Jessica Anderson', 'Matthew Thomas', 'Ashley Jackson', 'Daniel White',
            'Stephanie Harris', 'James Martin', 'Nicole Thompson', 'Andrew Garcia', 'Rachel Miller',
            'Kevin Davis', 'Lauren Wilson', 'Ryan Moore', 'Melissa Taylor', 'Brandon Anderson',
            'Samantha Thomas', 'Justin Jackson', 'Kimberly White', 'Timothy Brown', 'Heather Jones',
            'Jonathan Miller', 'Rebecca Davis', 'Nicholas Wilson', 'Michelle Garcia', 'Alexander Johnson',
            'Tiffany Smith', 'Jacob Rodriguez', 'Crystal Martinez', 'William Thompson', 'Vanessa Lopez'
        ];
        
        $users = [];
        $employees = [];
        
        foreach ($employeeNames as $index => $fullName) {
            $nameParts = explode(' ', $fullName);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1];
            $email = strtolower($firstName . '.' . $lastName . '@company.com');
            $department = $departments[$index % count($departments)];
            $position = $positions[$department][array_rand($positions[$department])];
            
            // Create user
            $user = User::create([
                'name' => $firstName,
                'lastname' => $lastName,
                'email' => $email,
                'password' => Hash::make('password123'),
                'phone' => '+1-555-' . str_pad($index + 1000, 4, '0', STR_PAD_LEFT),
                'position' => $position,
                'account_type' => 'employee',
            ]);
            
            // Create employee record
            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_id' => 'EMP' . str_pad($startingId + $index, 3, '0', STR_PAD_LEFT),
                'department' => $department,
                'position' => $position,
                'manager_name' => 'Manager ' . $department,
                'hire_date' => Carbon::now()->subDays(rand(30, 365)),
                'salary' => rand(40000, 120000),
                'employment_type' => ['full_time', 'part_time', 'contract'][array_rand(['full_time', 'part_time', 'contract'])],
                'work_location' => ['office', 'remote', 'hybrid'][array_rand(['office', 'remote', 'hybrid'])],
                'emergency_contact_name' => 'Emergency Contact ' . $index,
                'emergency_contact_phone' => '+1-555-' . str_pad($index + 2000, 4, '0', STR_PAD_LEFT),
                'address' => $index + 1 . ' Main St, City, State 12345',
                'status' => 'active',
            ]);
            
            $users[] = $user;
            $employees[] = $employee;
        }
        
        $this->command->info('Created ' . count($users) . ' users and employees.');
        
        // Create attendance records for today with various statuses
        $this->command->info('Creating today\'s attendance records...');
        
        $today = Carbon::now()->toDateString();
        $attendanceStatuses = ['present', 'late', 'absent', 'on_break'];
        $clockInTimes = ['08:00', '08:15', '08:30', '08:45', '09:00', '09:15', '09:30'];
        
        foreach ($employees as $index => $employee) {
            // Randomly decide if employee has attendance record (some might be absent)
            if (rand(1, 10) <= 8) { // 80% chance of having an attendance record
                $status = $attendanceStatuses[array_rand($attendanceStatuses)];
                
                // Skip creating record if absent
                if ($status === 'absent') {
                    continue;
                }
                
                $clockInTime = $clockInTimes[array_rand($clockInTimes)];
                $clockInCarbon = Carbon::createFromTimeString($clockInTime);
                
                // Determine if late (after 8:15 AM)
                if ($clockInCarbon->gt(Carbon::createFromTimeString('08:15'))) {
                    $status = 'late';
                }
                
                $attendanceData = [
                    'user_id' => $employee->user_id,
                    'date' => $today,
                    'clock_in_time' => $clockInTime,
                    'status' => $status,
                'created_by' => User::first()->id ?? 1, // Use first user or fallback to 1
                ];
                
                // Add break times if on break
                if ($status === 'on_break') {
                    $attendanceData['break_start'] = Carbon::createFromTimeString($clockInTime)
                        ->addHours(rand(2, 4))
                        ->format('H:i');
                }
                
                // Add clock out time for some employees (those who finished work)
                if (rand(1, 10) <= 3 && $status !== 'on_break') { // 30% chance of being clocked out
                    $clockOutTime = Carbon::createFromTimeString($clockInTime)
                        ->addHours(rand(8, 9))
                        ->addMinutes(rand(0, 59))
                        ->format('H:i');
                    $attendanceData['clock_out_time'] = $clockOutTime;
                    
                    // Calculate hours worked
                    $clockIn = Carbon::createFromTimeString($clockInTime);
                    $clockOut = Carbon::createFromTimeString($clockOutTime);
                    $hoursWorked = $clockOut->diffInMinutes($clockIn) / 60;
                    $attendanceData['hours_worked'] = round($hoursWorked, 2);
                }
                
                Attendance::create($attendanceData);
            }
        }
        
        $this->command->info('Created today\'s attendance records.');
        
        // Create some historical attendance data for the past week
        $this->command->info('Creating historical attendance data...');
        
        for ($i = 1; $i <= 7; $i++) {
            $date = Carbon::now()->subDays($i)->toDateString();
            
            // Skip weekends
            if (Carbon::parse($date)->isWeekend()) {
                continue;
            }
            
            foreach ($employees as $employee) {
                // 85% chance of attendance on historical days
                if (rand(1, 100) <= 85) {
                    $clockInTime = $clockInTimes[array_rand($clockInTimes)];
                    $clockInCarbon = Carbon::createFromTimeString($clockInTime);
                    $status = $clockInCarbon->gt(Carbon::createFromTimeString('08:15')) ? 'late' : 'present';
                    
                    $clockOutTime = Carbon::createFromTimeString($clockInTime)
                        ->addHours(8)
                        ->addMinutes(rand(-30, 60))
                        ->format('H:i');
                    
                    $clockIn = Carbon::createFromTimeString($clockInTime);
                    $clockOut = Carbon::createFromTimeString($clockOutTime);
                    $hoursWorked = $clockOut->diffInMinutes($clockIn) / 60;
                    
                    Attendance::create([
                        'user_id' => $employee->user_id,
                        'date' => $date,
                        'clock_in_time' => $clockInTime,
                        'clock_out_time' => $clockOutTime,
                        'status' => $status,
                        'hours_worked' => round($hoursWorked, 2),
                        'created_by' => User::first()->id ?? 1,
                    ]);
                }
            }
        }
        
        $this->command->info('Created historical attendance data.');
        
        // Summary
        $totalUsers = User::count();
        $totalEmployees = Employee::count();
        $todayAttendance = Attendance::where('date', $today)->count();
        
        $this->command->info("\n=== Test Data Created Successfully ===");
        $this->command->info("Total Users: {$totalUsers}");
        $this->command->info("Total Employees: {$totalEmployees}");
        $this->command->info("Today's Attendance Records: {$todayAttendance}");
        
        // Show breakdown by status for today
        $present = Attendance::where('date', $today)->where('status', 'present')->count();
        $late = Attendance::where('date', $today)->where('status', 'late')->count();
        $onBreak = Attendance::where('date', $today)->where('status', 'on_break')->count();
        $absent = $totalEmployees - $todayAttendance;
        
        $this->command->info("\n=== Today's Status Breakdown ===");
        $this->command->info("Present: {$present}");
        $this->command->info("Late: {$late}");
        $this->command->info("On Break: {$onBreak}");
        $this->command->info("Absent: {$absent}");
        
        $this->command->info("\nYou can now test the Live Attendance Monitor!");
    }
}
