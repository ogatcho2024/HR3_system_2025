<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmployeeSyncApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $provided = $request->header('X-API-KEY');
        $expected = config('hr_sync.api_key');

        if (!$expected || !$provided || !hash_equals($expected, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
