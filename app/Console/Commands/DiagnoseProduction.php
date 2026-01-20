<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class DiagnoseProduction extends Command
{
    protected $signature = 'diagnose:production {email?}';
    protected $description = 'Diagnose OTP issues in production environment';

    public function handle()
    {
        $this->info('=== PRODUCTION OTP DIAGNOSTIC ===');
        $this->newLine();

        // Environment
        $this->info('1. ENVIRONMENT');
        $this->line('   APP_ENV: ' . app()->environment());
        $this->line('   APP_DEBUG: ' . (config('app.debug') ? 'true' : 'false'));
        $this->newLine();

        // Configuration
        $this->info('2. OTP CONFIGURATION');
        $otpEnabled = config('auth.otp_enabled');
        $otpEnabledEnv = env('OTP_ENABLED');
        $this->line('   config(auth.otp_enabled): ' . ($otpEnabled ? 'TRUE ✓' : 'FALSE ✗'));
        $this->line('   env(OTP_ENABLED): ' . ($otpEnabledEnv ? 'TRUE ✓' : 'FALSE ✗'));
        $this->line('   OTP Expiry: ' . config('auth.otp_expiry_minutes', 'NOT SET') . ' minutes');
        $this->line('   OTP Length: ' . config('auth.otp_length', 'NOT SET'));
        
        if (!$otpEnabled) {
            $this->error('   ⚠️  OTP IS DISABLED IN CONFIG!');
            $this->warn('   Fix: Add OTP_ENABLED=true to .env and run: php artisan config:clear');
        }
        $this->newLine();

        // Mail configuration
        $this->info('3. MAIL CONFIGURATION');
        $this->line('   MAIL_MAILER: ' . config('mail.default'));
        $this->line('   MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->line('   MAIL_PORT: ' . config('mail.mailers.smtp.port'));
        $this->line('   MAIL_FROM: ' . config('mail.from.address'));
        $this->newLine();

        // Session configuration
        $this->info('4. SESSION CONFIGURATION');
        $sessionDriver = config('session.driver');
        $this->line('   Session Driver: ' . $sessionDriver);
        
        if ($sessionDriver === 'database') {
            try {
                $sessionCount = DB::table('sessions')->count();
                $this->line('   Sessions in DB: ' . $sessionCount . ' ✓');
            } catch (\Exception $e) {
                $this->error('   ⚠️  Sessions table error: ' . $e->getMessage());
                $this->warn('   Fix: Run: php artisan session:table && php artisan migrate');
            }
        }
        $this->newLine();

        // Database connection
        $this->info('5. DATABASE CONNECTION');
        try {
            DB::connection()->getPdo();
            $this->line('   Database: Connected ✓');
            $this->line('   Driver: ' . DB::connection()->getDriverName());
        } catch (\Exception $e) {
            $this->error('   ⚠️  Database error: ' . $e->getMessage());
        }
        $this->newLine();

        // Users table check
        $this->info('6. USERS TABLE CHECK');
        try {
            $totalUsers = User::count();
            $usersWithOtp = User::where('require_2fa', true)->count();
            $usersWithNull = User::whereNull('require_2fa')->count();
            $usersWithFalse = User::where('require_2fa', false)->count();

            $this->line('   Total Users: ' . $totalUsers);
            $this->line('   Users with require_2fa = TRUE: ' . $usersWithOtp . ' ✓');
            $this->line('   Users with require_2fa = NULL: ' . $usersWithNull . ($usersWithNull > 0 ? ' ⚠️' : ''));
            $this->line('   Users with require_2fa = FALSE: ' . $usersWithFalse . ($usersWithFalse > 0 ? ' ⚠️' : ''));

            if ($usersWithNull > 0 || $usersWithFalse > 0) {
                $this->newLine();
                $this->error('   ⚠️  FOUND USERS WITHOUT 2FA ENABLED!');
                $this->warn('   Fix: Run this in tinker:');
                $this->warn('   php artisan tinker');
                $this->warn('   >>> \\App\\Models\\User::whereNull(\'require_2fa\')->orWhere(\'require_2fa\', false)->update([\'require_2fa\' => true]);');
            }
        } catch (\Exception $e) {
            $this->error('   ⚠️  Error querying users: ' . $e->getMessage());
        }
        $this->newLine();

        // Specific user check
        $email = $this->argument('email');
        if ($email) {
            $this->info('7. SPECIFIC USER CHECK: ' . $email);
            try {
                $user = User::where('email', $email)->first();
                
                if ($user) {
                    $this->line('   User ID: ' . $user->id);
                    $this->line('   Email: ' . $user->email);
                    $this->line('   require_2fa: ' . ($user->require_2fa === null ? 'NULL ⚠️' : ($user->require_2fa ? 'TRUE ✓' : 'FALSE ⚠️')));
                    $this->line('   otp_verified: ' . ($user->otp_verified ? 'TRUE' : 'FALSE'));
                    $this->line('   otp_code: ' . ($user->otp_code ?? 'NULL'));
                    $this->line('   otp_expires_at: ' . ($user->otp_expires_at ?? 'NULL'));
                    
                    $this->newLine();
                    $this->info('   DECISION LOGIC FOR THIS USER:');
                    $otpEnabledConfig = config('auth.otp_enabled', true);
                    $userRequires2FA = $user->require_2fa ?? true;
                    $willTriggerOtp = $otpEnabledConfig && $userRequires2FA;
                    
                    $this->line('   → Config OTP Enabled: ' . ($otpEnabledConfig ? 'YES' : 'NO'));
                    $this->line('   → User Requires 2FA: ' . ($userRequires2FA ? 'YES' : 'NO'));
                    $this->line('   → Will Trigger OTP: ' . ($willTriggerOtp ? 'YES ✓' : 'NO ✗'));
                    
                    if (!$willTriggerOtp) {
                        $this->newLine();
                        $this->error('   ⚠️  THIS USER WILL BYPASS OTP!');
                        if (!$otpEnabledConfig) {
                            $this->warn('   Reason: OTP is disabled in config');
                            $this->warn('   Fix: Add OTP_ENABLED=true to .env');
                        }
                        if (!$userRequires2FA) {
                            $this->warn('   Reason: User does not require 2FA');
                            $this->warn('   Fix: Run in tinker: User::find(' . $user->id . ')->update([\'require_2fa\' => true]);');
                        }
                    }
                } else {
                    $this->error('   User not found with email: ' . $email);
                }
            } catch (\Exception $e) {
                $this->error('   ⚠️  Error checking user: ' . $e->getMessage());
            }
            $this->newLine();
        }

        // Check AuthController for updated code
        $this->info('8. CODE DEPLOYMENT CHECK');
        $authControllerPath = app_path('Http/Controllers/AuthController.php');
        if (file_exists($authControllerPath)) {
            $content = file_get_contents($authControllerPath);
            $hasNewCode = strpos($content, 'OTP DECISION POINT') !== false;
            
            if ($hasNewCode) {
                $this->line('   AuthController: Updated code detected ✓');
            } else {
                $this->error('   ⚠️  AuthController: OLD CODE DETECTED!');
                $this->warn('   Fix: Deploy latest code from git');
                $this->warn('   git pull origin main');
                $this->warn('   composer install --no-dev --optimize-autoloader');
                $this->warn('   php artisan config:clear');
            }
        }
        $this->newLine();

        // Middleware check
        $this->info('9. MIDDLEWARE CHECK');
        $middlewarePath = app_path('Http/Middleware/Ensure2FAVerified.php');
        if (file_exists($middlewarePath)) {
            $this->line('   Ensure2FAVerified middleware: EXISTS ✓');
        } else {
            $this->error('   ⚠️  Middleware file missing!');
        }
        $this->newLine();

        // Cache status
        $this->info('10. CACHE STATUS');
        $configCached = file_exists(base_path('bootstrap/cache/config.php'));
        $routesCached = file_exists(base_path('bootstrap/cache/routes-v7.php'));
        
        $this->line('   Config cached: ' . ($configCached ? 'YES' : 'NO'));
        $this->line('   Routes cached: ' . ($routesCached ? 'YES' : 'NO'));
        
        if ($configCached) {
            $this->warn('   ⚠️  Config is cached. If you changed .env, run: php artisan config:clear');
        }
        $this->newLine();

        // Summary
        $this->info('=== SUMMARY ===');
        
        $issues = [];
        if (!$otpEnabled) $issues[] = 'OTP is disabled in config';
        if (isset($usersWithNull) && $usersWithNull > 0) $issues[] = 'Users with NULL require_2fa';
        if (isset($usersWithFalse) && $usersWithFalse > 0) $issues[] = 'Users with FALSE require_2fa';
        if (isset($hasNewCode) && !$hasNewCode) $issues[] = 'Old code still deployed';
        
        if (empty($issues)) {
            $this->info('✓ No obvious issues detected!');
            $this->newLine();
            $this->line('If OTP is still bypassed:');
            $this->line('1. Check production logs: tail -f storage/logs/laravel.log | grep OTP');
            $this->line('2. Try login and watch for "OTP DECISION POINT" in logs');
            $this->line('3. Verify session is persisting');
        } else {
            $this->error('⚠️  ISSUES FOUND:');
            foreach ($issues as $issue) {
                $this->line('   • ' . $issue);
            }
            $this->newLine();
            $this->warn('Fix these issues and test again!');
        }

        return 0;
    }
}
