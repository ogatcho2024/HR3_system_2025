<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckEmployeeOTP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:check-employees {--email= : Specific employee email to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check OTP/2FA settings for Employee account types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== OTP/2FA Employee Account Diagnostic ===');
        $this->newLine();
        
        // Environment info
        $this->info('Environment: ' . app()->environment());
        $this->info('OTP Config Enabled: ' . (config('auth.otp_enabled') ? 'YES' : 'NO'));
        $this->info('Session Driver: ' . config('session.driver'));
        $this->newLine();
        
        // Build query
        $query = User::where('account_type', 'Employee')
            ->orWhere('account_type', 'employee');
        
        if ($email = $this->option('email')) {
            $query->where('email', $email);
        }
        
        $employees = $query->get();
        
        if ($employees->isEmpty()) {
            $this->error('No Employee accounts found!');
            return 1;
        }
        
        $this->info("Found {$employees->count()} Employee account(s)");
        $this->newLine();
        
        // Table headers
        $headers = [
            'ID',
            'Email',
            'Account Type',
            'require_2fa (DB)',
            'require_2fa (Cast)',
            'otp_verified',
            'Has OTP Code',
            'OTP Expires At'
        ];
        
        $rows = [];
        foreach ($employees as $user) {
            // Get raw database value
            $rawUser = DB::table('users')->where('id', $user->id)->first();
            
            $rows[] = [
                $user->id,
                $user->email,
                $user->account_type,
                $this->formatBooleanValue($rawUser->require_2fa),
                $this->formatBooleanValue($user->require_2fa),
                $user->otp_verified ? 'YES' : 'NO',
                $user->otp_code ? 'YES' : 'NO',
                $user->otp_expires_at ? $user->otp_expires_at->format('Y-m-d H:i:s') : 'NULL'
            ];
        }
        
        $this->table($headers, $rows);
        $this->newLine();
        
        // Check for issues
        $issues = [];
        foreach ($employees as $user) {
            $rawUser = DB::table('users')->where('id', $user->id)->first();
            
            if ($rawUser->require_2fa === null) {
                $issues[] = "User {$user->email}: require_2fa is NULL in database";
            }
            
            if ($rawUser->require_2fa === 0 || $rawUser->require_2fa === false || $rawUser->require_2fa === '0') {
                $issues[] = "User {$user->email}: require_2fa is FALSE in database";
            }
            
            if ($user->require_2fa !== true) {
                $issues[] = "User {$user->email}: require_2fa casts to NON-TRUE value (type: " . gettype($user->require_2fa) . ", value: " . var_export($user->require_2fa, true) . ")";
            }
        }
        
        if (!empty($issues)) {
            $this->error('=== ISSUES FOUND ===');
            foreach ($issues as $issue) {
                $this->warn('⚠ ' . $issue);
            }
            $this->newLine();
        } else {
            $this->info('✓ No issues found - all employees have require_2fa = true');
        }
        
        return 0;
    }
    
    /**
     * Format boolean/null values for display
     */
    private function formatBooleanValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        
        if ($value === true || $value === 1 || $value === '1') {
            return 'TRUE (1)';
        }
        
        if ($value === false || $value === 0 || $value === '0') {
            return 'FALSE (0)';
        }
        
        return var_export($value, true);
    }
}
