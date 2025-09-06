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

        echo "Employee seeder completed successfully!\n";
        echo "Test users created:\n";
        echo "- john.doe@company.com (password: password)\n";
        echo "- sarah.johnson@company.com (password: password)\n";
    }
}
