<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payroll System API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for integrating with external payroll system
    |
    */

    'endpoint' => env('PAYROLL_API_ENDPOINT', 'https://hr4.cranecali-ms.com/api/payroll/timesheets/import.php'),

    'api_key' => env('PAYROLL_API_KEY', ''),

    'api_secret' => env('PAYROLL_API_SECRET', ''),

    'timeout' => env('PAYROLL_TIMEOUT', 30),

    'retries' => env('PAYROLL_RETRIES', 3),

    'retry_delay' => env('PAYROLL_RETRY_DELAY', 100),

    'source_system' => env('PAYROLL_SOURCE_SYSTEM', 'HumanResources3'),

    'enabled' => env('PAYROLL_SYNC_ENABLED', true),

];
