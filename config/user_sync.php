<?php

return [

    'enabled' => env('USER_SYNC_ENABLED', true),
    'webhook_secret' => env('USER_SYNC_WEBHOOK_SECRET', ''),
    'source_service' => env('USER_SYNC_SOURCE_SERVICE', 'admin.cranecali-ms.com'),
    'max_retry_attempts' => env('USER_SYNC_MAX_RETRY_ATTEMPTS', 3),
    'rate_limit' => [
        'max_attempts' => env('USER_SYNC_RATE_LIMIT_ATTEMPTS', 60),
        'decay_minutes' => env('USER_SYNC_RATE_LIMIT_DECAY', 1),
    ],

    'syncable_fields' => [
        'name',
        'lastname',
        'email',
        'photo',
        'position',
        'phone',
        'date_of_birth',
        'gender',
        'account_type',
        'role',
        'is_active',
    ],

    'protected_fields' => [
        'password',
        'otp_code',
        'otp_expires_at',
        'otp_verified',
        'otp_status',
        'require_2fa',
        'remember_token',
        'email_verified_at',
    ],

    'validation_rules' => [
        'external_user_id' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'name' => 'required|string|max:255',
        
        'lastname' => 'nullable|string|max:255',
        'photo' => 'nullable|string|max:500',
        'position' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'date_of_birth' => 'nullable|date',
        'gender' => 'nullable|in:male,female,other',
        'account_type' => 'nullable|in:Super admin,Admin,Staff,Employee',
        'role' => 'nullable|string|max:100',
        'is_active' => 'nullable|boolean',
    ],

    'new_user_defaults' => [
        'account_type' => 'Employee',
        'role' => 'employee',
        'is_active' => true,
        'email_verified_at' => null, 
        'otp_status' => false,
        'otp_verified' => false,
        'require_2fa' => false,
    ],

    'generate_random_password' => true,
    'password_prefix' => 'sync_',

    'logging' => [
        'enabled' => env('USER_SYNC_LOGGING_ENABLED', true),
        'log_payload' => env('USER_SYNC_LOG_PAYLOAD', false),
        'channel' => env('USER_SYNC_LOG_CHANNEL', 'stack'),
    ],

];
