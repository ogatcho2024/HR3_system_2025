<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class DebugOTPStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:otp {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug OTP configuration and user status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== OTP System Debug Information ===');
        $this->info('');
        
        // Configuration
        $this->info('Configuration:');
        $this->line('  Environment: ' . app()->environment());
        $this->line('  OTP Enabled (config): ' . (config('auth.otp_enabled') ? 'true' : 'false'));
        $this->line('  OTP Enabled (env): ' . (env('OTP_ENABLED', 'not set') === true ? 'true' : (env('OTP_ENABLED', 'not set') === false ? 'false' : env('OTP_ENABLED', 'not set'))));
        $this->line('  OTP Expiry: ' . config('auth.otp_expiry_minutes', 'not set') . ' minutes');
        $this->line('  Session Driver: ' . config('session.driver'));
        $this->line('  Mail Driver: ' . config('mail.default'));
        $this->line('  Mail Host: ' . config('mail.mailers.smtp.host'));
        $this->info('');
        
        // User-specific debug
        $email = $this->argument('email');
        
        if ($email) {
            $user = User::where('email', $email)->first();
            
            if ($user) {
                $this->info('User Information:');
                $this->line('  ID: ' . $user->id);
                $this->line('  Name: ' . $user->name . ' ' . $user->lastname);
                $this->line('  Email: ' . $user->email);
                $this->line('  Account Type: ' . $user->account_type);
                $this->info('');
                
                $this->info('2FA Status:');
                $this->line('  require_2fa: ' . ($user->require_2fa === null ? 'NULL' : ($user->require_2fa ? 'true' : 'false')));
                $this->line('  otp_verified: ' . ($user->otp_verified === null ? 'NULL' : ($user->otp_verified ? 'true' : 'false')));
                $this->line('  otp_code: ' . ($user->otp_code ?: 'not set'));
                $this->line('  otp_expires_at: ' . ($user->otp_expires_at ? $user->otp_expires_at->format('Y-m-d H:i:s') : 'not set'));
                $this->info('');
                
                // Decision logic
                $otpEnabled = config('auth.otp_enabled', true);
                $userRequires2FA = $user->require_2fa ?? true;
                $willTriggerOTP = $otpEnabled && $userRequires2FA === true;
                
                $this->info('Decision Logic:');
                $this->line('  Config OTP Enabled: ' . ($otpEnabled ? 'YES' : 'NO'));
                $this->line('  User Requires 2FA: ' . ($userRequires2FA ? 'YES' : 'NO'));
                $this->line('  Will Trigger OTP: ' . ($willTriggerOTP ? '<fg=green>YES</>' : '<fg=red>NO</>'));
                
                if (!$willTriggerOTP) {
                    $this->warn('');
                    $this->warn('⚠ OTP WILL BE BYPASSED FOR THIS USER');
                    $this->warn('');
                    
                    if ($user->require_2fa === null) {
                        $this->warn('Reason: require_2fa is NULL in database');
                        $this->warn('Solution: Run: UPDATE users SET require_2fa = 1 WHERE email = "' . $email . '";');
                    } elseif ($user->require_2fa === false || $user->require_2fa === 0) {
                        $this->warn('Reason: require_2fa is explicitly set to FALSE');
                        $this->warn('Solution: Run: UPDATE users SET require_2fa = 1 WHERE email = "' . $email . '";');
                    } elseif (!$otpEnabled) {
                        $this->warn('Reason: OTP is disabled in configuration');
                        $this->warn('Solution: Set OTP_ENABLED=true in .env file');
                    }
                }
            } else {
                $this->error('User not found: ' . $email);
            }
        } else {
            // Show all users
            $this->info('All Users 2FA Status:');
            $users = User::all();
            
            if ($users->count() === 0) {
                $this->warn('No users found in database');
            } else {
                $this->table(
                    ['ID', 'Email', 'require_2fa', 'otp_verified', 'Will Trigger OTP'],
                    $users->map(function ($user) {
                        $otpEnabled = config('auth.otp_enabled', true);
                        $userRequires2FA = $user->require_2fa ?? true;
                        $willTrigger = $otpEnabled && $userRequires2FA === true;
                        
                        return [
                            $user->id,
                            $user->email,
                            $user->require_2fa === null ? 'NULL' : ($user->require_2fa ? 'true' : 'false'),
                            $user->otp_verified === null ? 'NULL' : ($user->otp_verified ? 'true' : 'false'),
                            $willTrigger ? 'YES' : 'NO',
                        ];
                    })
                );
            }
            
            $this->info('');
            $this->info('To debug a specific user: php artisan debug:otp user@example.com');
        }
        
        return Command::SUCCESS;
    }
}
