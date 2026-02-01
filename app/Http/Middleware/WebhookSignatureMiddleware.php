<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class WebhookSignatureMiddleware
{
    /**
     * Handle an incoming webhook request and verify HMAC signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user sync is enabled
        if (!config('user_sync.enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'User sync is currently disabled'
            ], 503);
        }

        $secret = config('user_sync.webhook_secret');
        
        // Ensure webhook secret is configured
        if (empty($secret)) {
            Log::error('User sync webhook secret not configured');
            return response()->json([
                'success' => false,
                'message' => 'Webhook authentication not configured'
            ], 500);
        }

        // Get the signature from the request header
        $receivedSignature = $request->header('X-Webhook-Signature');
        
        if (!$receivedSignature) {
            Log::warning('Webhook request received without signature', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Missing webhook signature'
            ], 401);
        }

        // Get the raw request body
        $payload = $request->getContent();
        
        // Calculate the expected signature using HMAC SHA-256
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        // Use timing-safe comparison to prevent timing attacks
        if (!hash_equals($expectedSignature, $receivedSignature)) {
            Log::warning('Webhook signature verification failed', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'received_signature' => substr($receivedSignature, 0, 10) . '...',
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature'
            ], 401);
        }

        // Signature is valid, proceed with the request
        Log::info('Webhook signature verified successfully', [
            'path' => $request->path(),
            'source_ip' => $request->ip(),
        ]);

        return $next($request);
    }
}
