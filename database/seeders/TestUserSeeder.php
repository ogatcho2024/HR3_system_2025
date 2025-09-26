<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user for mobile app
        User::create([
            'name' => 'Test',
            'lastname' => 'Employee', 
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'account_type' => 'employee',
            'phone' => '+1234567890',
            'position' => 'Mobile App Tester',
            'otp_status' => false,
        ]);

        echo "Test user created:\n";
        echo "Email: test@example.com\n";
        echo "Password: password123\n";
    }
}