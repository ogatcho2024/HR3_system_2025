<?php

require_once __DIR__.'/vendor/autoload.php';

use App\Models\User;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Users in database:\n";
echo str_repeat("=", 80) . "\n\n";

$users = User::select('name', 'lastname', 'email', 'account_type')->get();

foreach ($users as $user) {
    echo "- {$user->name} {$user->lastname}\n";
    echo "  Email: {$user->email}\n";
    echo "  Account Type: {$user->account_type}\n\n";
}

echo "Total users: " . $users->count() . "\n";
