<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin.or.staff' => \App\Http\Middleware\AdminOrStaffMiddleware::class,
            'login.throttle' => \App\Http\Middleware\LoginThrottleMiddleware::class,
            'cors' => \App\Http\Middleware\CorsMiddleware::class,
            'simple.api.auth' => \App\Http\Middleware\SimpleApiAuth::class,
            '2fa.verified' => \App\Http\Middleware\Ensure2FAVerified::class,
            'account.type' => \App\Http\Middleware\EnsureAccountType::class,
            'webhook.signature' => \App\Http\Middleware\WebhookSignatureMiddleware::class,
            'employee.sync.key' => \App\Http\Middleware\VerifyEmployeeSyncApiKey::class,
        ]);
        
        // Add CORS middleware to API routes
        $middleware->group('api', [
            \App\Http\Middleware\CorsMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
