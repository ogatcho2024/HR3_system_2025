<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'ip_address',
        'attempts',
        'last_attempt',
        'blocked_until'
    ];

    protected $casts = [
        'last_attempt' => 'datetime',
        'blocked_until' => 'datetime'
    ];

    /**
     * Check if IP or email is currently blocked
     */
    public static function isBlocked($email, $ipAddress)
    {
        $attempt = self::where(function ($query) use ($email, $ipAddress) {
            $query->where('email', $email)
                  ->orWhere('ip_address', $ipAddress);
        })
        ->where('blocked_until', '>', now())
        ->first();

        return $attempt !== null;
    }

    /**
     * Get remaining block time in minutes
     */
    public static function getBlockTimeRemaining($email, $ipAddress)
    {
        $attempt = self::where(function ($query) use ($email, $ipAddress) {
            $query->where('email', $email)
                  ->orWhere('ip_address', $ipAddress);
        })
        ->where('blocked_until', '>', now())
        ->first();

        if ($attempt) {
            $seconds = now()->diffInSeconds($attempt->blocked_until);
            $minutes = ceil($seconds / 60);
            return max(1, $minutes); // Always show at least 1 minute if blocked
        }

        return 0;
    }

    /**
     * Get remaining block time in seconds for more precise countdown
     */
    public static function getBlockTimeRemainingSeconds($email, $ipAddress)
    {
        $attempt = self::where(function ($query) use ($email, $ipAddress) {
            $query->where('email', $email)
                  ->orWhere('ip_address', $ipAddress);
        })
        ->where('blocked_until', '>', now())
        ->first();

        if ($attempt) {
            return max(0, now()->diffInSeconds($attempt->blocked_until));
        }

        return 0;
    }

    /**
     * Record a failed login attempt
     */
    public static function recordFailedAttempt($email, $ipAddress)
    {
        $maxAttempts = config('auth.login_attempts.max_attempts', 3);
        $blockDuration = config('auth.login_attempts.block_duration', 5); // minutes

        // Find existing attempt record
        $attempt = self::where('email', $email)
                      ->orWhere('ip_address', $ipAddress)
                      ->first();

        if ($attempt) {
            // If more than 15 minutes have passed since last attempt, reset counter
            if ($attempt->last_attempt->diffInMinutes(now()) > 15) {
                $attempt->attempts = 1;
            } else {
                $attempt->attempts++;
            }
            
            $attempt->last_attempt = now();
            
            // Block if exceeded max attempts
            if ($attempt->attempts >= $maxAttempts) {
                $attempt->blocked_until = now()->addMinutes($blockDuration);
            }
            
            $attempt->save();
        } else {
            // Create new attempt record
            self::create([
                'email' => $email,
                'ip_address' => $ipAddress,
                'attempts' => 1,
                'last_attempt' => now()
            ]);
        }
    }

    /**
     * Clear login attempts for successful login
     */
    public static function clearAttempts($email, $ipAddress)
    {
        self::where('email', $email)
            ->orWhere('ip_address', $ipAddress)
            ->delete();
    }

    /**
     * Clean up old records (run this periodically)
     */
    public static function cleanup()
    {
        return self::where('last_attempt', '<', now()->subHours(24))
            ->where(function ($query) {
                $query->whereNull('blocked_until')
                      ->orWhere('blocked_until', '<', now());
            })
            ->delete();
    }
}