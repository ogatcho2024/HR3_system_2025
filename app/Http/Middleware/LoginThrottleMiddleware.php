<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LoginAttempt;
use Illuminate\Support\Facades\RateLimiter;

class LoginThrottleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to POST requests (login attempts)
        if ($request->method() !== 'POST') {
            return $next($request);
        }

        $email = $request->input('email');
        $ipAddress = $request->ip();
        
        // Check if IP or email is blocked - but don't block, just mark for the controller to handle
        if (LoginAttempt::isBlocked($email, $ipAddress)) {
            $remainingTime = LoginAttempt::getBlockTimeRemaining($email, $ipAddress);
            
            // Add throttle flag to request for controller to check
            $request->merge(['_is_throttled' => true, '_throttle_time' => $remainingTime]);
        }

        // Create a rate limiter key combining IP and email  
        $rateLimitKey = 'login-attempts:' . $ipAddress . ':' . $email;
        
        // Check Laravel's built-in rate limiter (backup protection)
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) { // 5 attempts per minute as backup
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = ceil($seconds / 60);
            
            $request->merge(['_is_throttled' => true, '_throttle_time' => $minutes]);
        }

        // Allow the request to continue to the controller
        return $next($request);
    }
}