<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating test users for Flutter app...\n";

// User 1: Network test screen credentials
$user1 = User::updateOrCreate(
    ['email' => 'test@example.com'], 
    [
        'name' => 'Test User',
        'password' => Hash::make('password123'),
        'account_type' => 'employee'
    ]
);
echo "✅ Created/Updated user: {$user1->email} (password: password123)\n";

// User 2: App test credentials
$user2 = User::updateOrCreate(
    ['email' => 'app@test.com'], 
    [
        'name' => 'App Test User',
        'password' => Hash::make('123qwe!@#QWE'),
        'account_type' => 'employee'
    ]
);
echo "✅ Created/Updated user: {$user2->email} (password: 123qwe!@#QWE)\n";

// User 3: Simple test user
$user3 = User::updateOrCreate(
    ['email' => 'admin@test.com'], 
    [
        'name' => 'Admin User',
        'password' => Hash::make('admin123'),
        'account_type' => 'admin'
    ]
);
echo "✅ Created/Updated user: {$user3->email} (password: admin123)\n";

echo "\n🎉 All test users created successfully!\n";
echo "\nYou can now login with any of these credentials:\n";
echo "1. test@example.com / password123\n";
echo "2. app@test.com / 123qwe!@#QWE\n";  
echo "3. admin@test.com / admin123\n";