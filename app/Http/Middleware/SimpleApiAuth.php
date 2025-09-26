<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleApiAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not provided'
            ], 401);
        }

        $apiToken = ApiToken::where('token', $token)
            ->with('user')
            ->first();

        if (!$apiToken || $apiToken->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 401);
        }

        // Mark token as used
        $apiToken->markAsUsed();

        // Add user to request for controllers to use
        $request->attributes->add(['authenticated_user' => $apiToken->user]);

        return $next($request);
    }

    /**
     * Extract token from request
     */
    private function getTokenFromRequest(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }
}
