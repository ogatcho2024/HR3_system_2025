<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoginAttempt;

class VerifyBlockDuration extends Command
{
    protected $signature = 'test:block-duration';
    protected $description = 'Verify the 5-minute block duration';

    public function handle()
    {
        $this->info('Testing 5-minute block duration...');
        
        // Clear any existing attempts
        LoginAttempt::query()->delete();
        
        $email = 'test@example.com';
        $ip = '127.0.0.1';
        
        // Record 3 failed attempts to trigger block
        LoginAttempt::recordFailedAttempt($email, $ip);
        LoginAttempt::recordFailedAttempt($email, $ip);
        LoginAttempt::recordFailedAttempt($email, $ip);
        
        $attempt = LoginAttempt::where('email', $email)->first();
        
        if ($attempt && $attempt->blocked_until) {
            $currentTime = now();
            $blockedUntil = $attempt->blocked_until;
            $durationMinutes = $currentTime->diffInMinutes($blockedUntil);
            $durationSeconds = $currentTime->diffInSeconds($blockedUntil);
            
            $this->line("Current time: " . $currentTime->format('H:i:s'));
            $this->line("Blocked until: " . $blockedUntil->format('H:i:s'));
            $this->line("Duration: {$durationMinutes} minutes and " . ($durationSeconds % 60) . " seconds");
            
            if ($durationMinutes >= 4 && $durationMinutes <= 5) {
                $this->info("✓ Block duration is correct (~5 minutes)");
            } else {
                $this->error("✗ Block duration is incorrect: {$durationMinutes} minutes");
            }
        } else {
            $this->error("✗ No block was created");
        }
        
        // Clean up
        LoginAttempt::query()->delete();
        
        return 0;
    }
}