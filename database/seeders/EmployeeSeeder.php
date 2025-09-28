<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user if one doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'john.doe@company.com'],
            [
                'name' => 'John',
                'lastname' => 'Doe',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0123',
                'account_type' => 'employee',
                'otp_status' => true,
                'position' => 'Software Developer',
            ]
        );

        // Create employee record
        Employee::firstOrCreate(
            ['user_id' => $user->id],
            [
                'employee_id' => 'EMP001',
                'department' => 'Information Technology',
                'position' => 'Senior Software Developer',
                'manager_name' => 'Jane Smith',
                'hire_date' => Carbon::parse('2023-01-15'),
                'salary' => 75000.00,
                'employment_type' => 'full-time',
                'work_location' => 'Main Office',
                'emergency_contact_name' => 'Mary Doe',
                'emergency_contact_phone' => '+1-555-0124',
                'address' => '123 Main Street, Cityville, ST 12345',
                'status' => 'active',
            ]
        );

        // Create another test user
        $user2 = User::firstOrCreate(
            ['email' => 'sarah.johnson@company.com'],
            [
                'name' => 'Sarah',
                'lastname' => 'Johnson',
                'password' => Hash::make('password'),
                'phone' => '+1-555-0125',
                'account_type' => 'employee',
                'otp_status' => true,
                'position' => 'Marketing Specialist',
            ]
        );

        // Create employee record for second user
        Employee::firstOrCreate(
            ['user_id' => $user2->id],
            [
                'employee_id' => 'EMP002',
                'department' => 'Marketing',
                'position' => 'Marketing Specialist',
                'manager_name' => 'Robert Wilson',
                'hire_date' => Carbon::parse('2023-03-20'),
                'salary' => 55000.00,
                'employment_type' => 'full-time',
                'work_location' => 'Main Office',
                'emergency_contact_name' => 'Michael Johnson',
                'emergency_contact_phone' => '+1-555-0126',
                'address' => '456 Oak Avenue, Cityville, ST 12346',
                'status' => 'active',
            ]
        );

        // Create new employees as requested
        $newEmployees = [
            [
                'name' => 'Roderick',
                'lastname' => 'Ogatcho',
                'email' => 'roderick.ogatcho@company.com',
                'department' => 'Engineering',
                'position' => 'Software Developer',
                'employee_id' => 'EMP003',
                'salary' => 65000.00,
            ],
            [
                'name' => 'Kurt',
                'lastname' => 'Bundalian',
                'email' => 'kurt.bundalian@company.com',
                'department' => 'Marketing',
                'position' => 'Marketing Specialist',
                'employee_id' => 'EMP004',
                'salary' => 58000.00,
            ],
            [
                'name' => 'Pablo',
                'lastname' => 'Tribiana',
                'email' => 'pablo.tribiana@company.com',
                'department' => 'Human Resources',
                'position' => 'HR Coordinator',
                'employee_id' => 'EMP005',
                'salary' => 62000.00,
            ],
            [
                'name' => 'Mark Adreane',
                'lastname' => 'Ducot',
                'email' => 'mark.ducot@company.com',
                'department' => 'Finance',
                'position' => 'Financial Analyst',
                'employee_id' => 'EMP006',
                'salary' => 70000.00,
            ],
        ];

        foreach ($newEmployees as $index => $employeeData) {
            // Create user record
            $user = User::firstOrCreate(
                ['email' => $employeeData['email']],
                [
                    'name' => $employeeData['name'],
                    'lastname' => $employeeData['lastname'],
                    'password' => Hash::make('password123'),
                    'phone' => '09' . str_pad(100000000 + $index, 9, '0', STR_PAD_LEFT),
                    'account_type' => 'employee',
                    'otp_status' => true,
                    'position' => $employeeData['position'],
                ]
            );

            // Create employee record
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => $employeeData['employee_id'],
                    'department' => $employeeData['department'],
                    'position' => $employeeData['position'],
                    'manager_name' => 'Department Manager',
                    'hire_date' => Carbon::now()->subMonths(rand(1, 12)),
                    'salary' => $employeeData['salary'],
                    'employment_type' => 'full-time',
                    'work_location' => 'Main Office',
                    'emergency_contact_name' => $employeeData['name'] . ' Emergency Contact',
                    'emergency_contact_phone' => '09' . str_pad(900000000 + $index, 9, '0', STR_PAD_LEFT),
                    'address' => ($index + 1) . '23 Sample Street, Sample City, Sample Province',
                    'status' => 'active',
                ]
            );
        }

        echo "Employee seeder completed successfully!\n";
        echo "Test users created:\n";
        echo "- john.doe@company.com (password: password)\n";
        echo "- sarah.johnson@company.com (password: password)\n";
        echo "\nNew employees added:\n";
        echo "- roderick.ogatcho@company.com (password: password123)\n";
        echo "- kurt.bundalian@company.com (password: password123)\n";
        echo "- pablo.tribiana@company.com (password: password123)\n";
        echo "- mark.ducot@company.com (password: password123)\n";
    }
}
