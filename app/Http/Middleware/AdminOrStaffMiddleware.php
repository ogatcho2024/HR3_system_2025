<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminOrStaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this area.');
        }

        // Check if user has admin, staff, or super admin privileges
        $user = Auth::user();
        if (!($user->isSuperAdmin() || $user->isAdmin() || $user->isStaff())) {
            abort(403, 'Unauthorized access. Admin or Staff privileges required.');
        }

        return $next($request);
    }
}
