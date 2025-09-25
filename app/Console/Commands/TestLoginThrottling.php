<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoginAttempt;
use Illuminate\Support\Facades\DB;

class TestLoginThrottling extends Command
{
    protected $signature = 'test:login-throttling';
    protected $description = 'Test the login throttling functionality';

    public function handle()
    {
        $this->info('Testing Login Rate Limiting System');
        $this->info('==================================');
        $this->newLine();

        // Test 1: Record failed attempts
        $this->info('Test 1: Recording failed attempts');
        $email = 'test@example.com';
        $ip = '192.168.1.100';

        // Clear any existing attempts
        LoginAttempt::clearAttempts($email, $ip);

        for ($i = 1; $i <= 5; $i++) {
            LoginAttempt::recordFailedAttempt($email, $ip);
            
            $attempt = LoginAttempt::where('email', $email)->first();
            $this->line("Attempt {$i}: {$attempt->attempts} attempts recorded");
            
            if (LoginAttempt::isBlocked($email, $ip)) {
                $remaining = LoginAttempt::getBlockTimeRemaining($email, $ip);
                $this->warn("Account is now BLOCKED for {$remaining} minutes");
                break;
            }
        }

        $this->newLine();

        // Test 2: Check if blocking works
        $this->info('Test 2: Checking block status');
        if (LoginAttempt::isBlocked($email, $ip)) {
            $remaining = LoginAttempt::getBlockTimeRemaining($email, $ip);
            $this->line("✓ Account is blocked for {$remaining} minutes");
        } else {
            $this->error("✗ Account is not blocked (unexpected)");
        }

        $this->newLine();

        // Test 3: Clear attempts (simulate successful login)
        $this->info('Test 3: Clearing attempts after successful login');
        LoginAttempt::clearAttempts($email, $ip);

        if (!LoginAttempt::isBlocked($email, $ip)) {
            $this->line("✓ Account is no longer blocked after clearing attempts");
        } else {
            $this->error("✗ Account is still blocked (unexpected)");
        }

        $this->newLine();

        // Test 4: Show current database state
        $this->info('Test 4: Database state');
        $attempts = DB::table('login_attempts')->get();
        $this->line("Total login attempt records: " . $attempts->count());

        foreach ($attempts as $attempt) {
            $this->line("Email: {$attempt->email}, IP: {$attempt->ip_address}, Attempts: {$attempt->attempts}");
        }

        $this->newLine();
        $this->info('Rate limiting tests completed!');
        $this->comment('You can now test manually by trying to login with wrong credentials 3+ times.');

        return 0;
    }
}