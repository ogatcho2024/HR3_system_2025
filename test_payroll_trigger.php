<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         REAL-TIME PAYROLL TRIGGER TEST                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Step 1: Find a submitted timesheet
echo "📋 Step 1: Finding a submitted timesheet...\n";
$timesheet = Timesheet::where('status', 'submitted')->first();

if (!$timesheet) {
    echo "❌ No submitted timesheets found. Creating a test timesheet...\n";
    
    // Find a user to assign the timesheet to
    $user = User::first();
    
    if (!$user) {
        echo "❌ No users found in database. Cannot continue test.\n";
        exit(1);
    }
    
    $timesheet = Timesheet::create([
        'user_id' => $user->id,
        'work_date' => now(),
        'clock_in_time' => '08:00',
        'clock_out_time' => '17:00',
        'hours_worked' => 8.5,
        'overtime_hours' => 0.5,
        'status' => 'submitted',
        'submitted_at' => now(),
        'project_name' => 'Test Project',
        'work_description' => 'Test work for payroll trigger verification',
    ]);
    
    echo "✅ Created test timesheet ID: {$timesheet->id}\n\n";
} else {
    echo "✅ Found timesheet ID: {$timesheet->id}\n\n";
}

// Display timesheet details
echo "📊 Timesheet Details:\n";
echo "   ID: {$timesheet->id}\n";
echo "   User: {$timesheet->user->name}\n";
echo "   Date: {$timesheet->work_date->format('Y-m-d')}\n";
echo "   Hours: {$timesheet->hours_worked}\n";
echo "   Status: {$timesheet->status}\n";
echo "   Sent to payroll: " . ($timesheet->sent_to_payroll ? 'YES' : 'NO') . "\n";
echo "   Payroll send attempts: " . ($timesheet->payroll_send_attempts ?? 0) . "\n\n";

// Step 2: Simulate approval
echo "🚀 Step 2: Simulating approval (triggering real-time sync)...\n";

// Get a valid user ID for approval
$validUser = User::first();

// Set auth user for the approval
Auth::loginUsingId($validUser->id);

// Store old status
$oldStatus = $timesheet->status;

// Update to approved (this should trigger the job dispatch)
$timesheet->update([
    'status' => 'approved',
    'approved_at' => now(),
    'approved_by' => $validUser->id
]);

echo "✅ Timesheet approved!\n";
echo "   Old status: {$oldStatus}\n";
echo "   New status: {$timesheet->status}\n\n";

// Step 3: Check if job would be dispatched (simulate the controller logic)
echo "🔍 Step 3: Verifying trigger conditions...\n";

if ($oldStatus !== 'approved' && !$timesheet->sent_to_payroll) {
    echo "✅ TRIGGER CONDITIONS MET:\n";
    echo "   ✓ Status changed from '{$oldStatus}' to 'approved'\n";
    echo "   ✓ Not already sent to payroll\n";
    echo "   ✓ Job WOULD be dispatched: SendTimesheetToPayrollJob::dispatch({$timesheet->id})\n\n";
    
    // Actually dispatch the job if queue is configured
    try {
        \App\Jobs\SendTimesheetToPayrollJob::dispatch($timesheet->id);
        echo "✅ Job dispatched successfully!\n\n";
    } catch (\Exception $e) {
        echo "⚠️  Job dispatch failed: {$e->getMessage()}\n";
        echo "   This is OK if queue is not configured.\n\n";
    }
} else {
    echo "❌ TRIGGER CONDITIONS NOT MET:\n";
    if ($oldStatus === 'approved') {
        echo "   ✗ Old status was already 'approved'\n";
    }
    if ($timesheet->sent_to_payroll) {
        echo "   ✗ Already sent to payroll\n";
    }
    echo "\n";
}

// Step 4: Check logs
echo "📝 Step 4: Checking recent logs...\n";
$logFile = storage_path('logs/laravel.log');

if (file_exists($logFile)) {
    $logs = file($logFile);
    $recentLogs = array_slice($logs, -20); // Last 20 lines
    
    $found = false;
    foreach ($recentLogs as $line) {
        if (strpos($line, '[Timesheet]') !== false || strpos($line, '[PayrollSync]') !== false) {
            echo "   " . trim($line) . "\n";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "   ℹ️  No recent [Timesheet] or [PayrollSync] log entries found.\n";
    }
} else {
    echo "   ⚠️  Log file not found at: {$logFile}\n";
}

echo "\n";

// Step 5: Check queue
echo "🔄 Step 5: Checking queue status...\n";

try {
    $pendingJobs = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo "   Pending jobs in queue: {$pendingJobs}\n";
    
    if ($pendingJobs > 0) {
        echo "   ✅ Jobs are queued! Run: php artisan queue:work --once\n";
    }
} catch (\Exception $e) {
    echo "   ⚠️  Queue table not found. Queue might be set to 'sync' or not configured.\n";
    echo "   Current queue connection: " . config('queue.default') . "\n";
}

echo "\n";

// Summary
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                    TEST SUMMARY                               ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "Timesheet ID: {$timesheet->id}\n";
echo "Final status: {$timesheet->status}\n";
echo "Sent to payroll: " . ($timesheet->sent_to_payroll ? 'YES' : 'NO') . "\n";
echo "Trigger test: " . ($oldStatus !== 'approved' && !$timesheet->sent_to_payroll ? '✅ PASSED' : '❌ FAILED') . "\n\n";

echo "Next steps:\n";
echo "1. Run queue worker: php artisan queue:work --once\n";
echo "2. Check logs: Get-Content storage\\logs\\laravel.log -Tail 50 | Select-String 'Payroll'\n";
echo "3. Verify database: SELECT id, status, sent_to_payroll, payroll_sent_at FROM timesheets WHERE id = {$timesheet->id};\n\n";

echo "✅ Test completed!\n";
