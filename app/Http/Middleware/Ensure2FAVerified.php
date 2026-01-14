<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Ensure2FAVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Allow access if no user is authenticated (will be handled by auth middleware)
        if (!$user) {
            return $next($request);
        }

        // Check if user requires 2FA and hasn't verified OTP yet
        if ($user->require_2fa && !$user->otp_verified) {
            // Allow access to OTP-related routes
            if ($request->routeIs('otp.*') || $request->routeIs('logout')) {
                return $next($request);
            }

            // Redirect to OTP verification page
            return redirect()->route('otp.show')->with('info', 'Please verify your OTP to continue.');
        }

        return $next($request);
    }
}
