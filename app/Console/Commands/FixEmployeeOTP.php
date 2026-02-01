<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FixEmployeeOTP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:fix-employees {--dry-run : Show what would be changed without applying changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix 2FA settings for Employee accounts to ensure OTP is enabled';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('=== Fix Employee 2FA Settings ===');
        $this->newLine();
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }
        
        // Find all Employee accounts
        $employees = User::where('account_type', 'Employee')
            ->orWhere('account_type', 'employee')
            ->get();
        
        if ($employees->isEmpty()) {
            $this->error('No Employee accounts found!');
            return 1;
        }
        
        $this->info("Found {$employees->count()} Employee account(s)");
        $this->newLine();
        
        $needsFixCount = 0;
        $fixedCount = 0;
        
        foreach ($employees as $user) {
            // Get raw database value
            $rawUser = DB::table('users')->where('id', $user->id)->first();
            
            $needsFix = false;
            $issues = [];
            
            // Check if require_2fa is not true
            if ($rawUser->require_2fa !== 1 && $rawUser->require_2fa !== true) {
                $needsFix = true;
                $issues[] = "require_2fa is not TRUE (current: " . $this->formatValue($rawUser->require_2fa) . ")";
            }
            
            if ($needsFix) {
                $needsFixCount++;
                
                $this->warn("User {$user->id} ({$user->email}):");
                foreach ($issues as $issue) {
                    $this->line("  - {$issue}");
                }
                
                if (!$dryRun) {
                    // Fix the user
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'require_2fa' => true,
                            'otp_verified' => false, // Force OTP verification on next login
                            'otp_code' => null,
                            'otp_expires_at' => null,
                            'updated_at' => now(),
                        ]);
                    
                    $this->info("  ✓ FIXED");
                    $fixedCount++;
                } else {
                    $this->line("  → Would set require_2fa = true, reset OTP state");
                }
                
                $this->newLine();
            }
        }
        
        // Summary
        $this->newLine();
        $this->info('=== SUMMARY ===');
        $this->info("Total Employee accounts: {$employees->count()}");
        $this->info("Accounts needing fix: {$needsFixCount}");
        
        if (!$dryRun) {
            $this->info("Accounts fixed: {$fixedCount}");
            
            if ($fixedCount > 0) {
                $this->newLine();
                $this->comment('Note: Fixed users will need to verify OTP on their next login.');
            }
        } else {
            $this->newLine();
            $this->warn('Run without --dry-run to apply changes.');
        }
        
        return 0;
    }
    
    /**
     * Format value for display
     */
    private function formatValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        
        if ($value === true || $value === 1) {
            return 'TRUE (1)';
        }
        
        if ($value === false || $value === 0) {
            return 'FALSE (0)';
        }
        
        return var_export($value, true);
    }
}
