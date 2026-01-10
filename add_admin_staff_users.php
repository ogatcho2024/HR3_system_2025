<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\Hash;
use App\Models\User;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating Super Admin and Staff users...\n\n";

// Create Super Admin
$superAdmin = User::create([
    'name' => 'Super',
    'lastname' => 'Admin',
    'email' => 'superadmin@company.com',
    'password' => Hash::make('password123'),
    'account_type' => 'Super admin',
    'phone' => '+1111111111',
    'position' => 'System Administrator',
    'otp_status' => false
]);

echo "✅ Super Admin created!\n";
echo "   Email: {$superAdmin->email}\n";
echo "   Password: password123\n";
echo "   Account Type: {$superAdmin->account_type}\n\n";

// Create Staff user
$staff = User::create([
    'name' => 'Staff',
    'lastname' => 'Member',
    'email' => 'staff@company.com',
    'password' => Hash::make('password123'),
    'account_type' => 'Staff',
    'phone' => '+2222222222',
    'position' => 'Staff Coordinator',
    'otp_status' => false
]);

echo "✅ Staff user created!\n";
echo "   Email: {$staff->email}\n";
echo "   Password: password123\n";
echo "   Account Type: {$staff->account_type}\n\n";

// Also create the user with asdasd@gmail.com
$regularUser = User::create([
    'name' => 'John',
    'lastname' => 'Smith',
    'email' => 'asdasd@gmail.com',
    'password' => Hash::make('123123'),
    'account_type' => 'Employee',
    'phone' => '+1234567890',
    'position' => 'Staff Member',
    'otp_status' => false
]);

echo "✅ Regular user created!\n";
echo "   Email: {$regularUser->email}\n";
echo "   Password: 123123\n";
echo "   Account Type: {$regularUser->account_type}\n\n";

echo "🎉 All users created successfully!\n";
