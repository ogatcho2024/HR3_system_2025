<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$allowedTypes
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$allowedTypes): Response
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized access. Authentication required.');
        }

        $user = Auth::user();

        // Check if user's account type is in the allowed types
        if (!in_array($user->account_type, $allowedTypes)) {
            abort(403, 'Unauthorized access. Insufficient privileges.');
        }

        return $next($request);
    }
}
