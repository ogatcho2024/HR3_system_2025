<?php

/**
 * Webhook Signature Tester
 * 
 * This script helps you generate valid webhook signatures for testing
 * the User Sync API locally or in development environments.
 * 
 * Usage: php tests/webhook_signature_tester.php
 */

require __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configuration
$webhookSecret = $_ENV['USER_SYNC_WEBHOOK_SECRET'] ?? 'your-secret-here';
$baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';

echo "================================================\n";
echo "User Sync Webhook Signature Tester\n";
echo "================================================\n\n";

// Example 1: Single User Sync
echo "Example 1: Single User Webhook\n";
echo "--------------------------------\n";

$singleUserPayload = [
    'user' => [
        'external_user_id' => 'TEST-USER-001',
        'email' => 'test.user@example.com',
        'name' => 'Test',
        'lastname' => 'User',
        'phone' => '+1234567890',
        'position' => 'Test Engineer',
        'account_type' => 'Employee',
        'role' => 'employee',
        'is_active' => true,
    ],
    'api_version' => '1.0',
    'source_service' => 'admin.cranecali-ms.com'
];

$jsonPayload1 = json_encode($singleUserPayload);
$signature1 = hash_hmac('sha256', $jsonPayload1, $webhookSecret);

echo "Payload:\n";
echo json_encode($singleUserPayload, JSON_PRETTY_PRINT) . "\n\n";

echo "Signature: {$signature1}\n\n";

echo "cURL command:\n";
echo "curl -X POST {$baseUrl}/api/user-sync/webhook \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -H \"X-Webhook-Signature: {$signature1}\" \\\n";
echo "  -d '{$jsonPayload1}'\n\n";

// Example 2: Batch User Sync
echo "================================================\n";
echo "Example 2: Batch User Sync\n";
echo "--------------------------------\n";

$batchPayload = [
    'users' => [
        [
            'external_user_id' => 'TEST-BATCH-001',
            'email' => 'batch1@example.com',
            'name' => 'Batch',
            'lastname' => 'User One',
            'account_type' => 'Employee',
        ],
        [
            'external_user_id' => 'TEST-BATCH-002',
            'email' => 'batch2@example.com',
            'name' => 'Batch',
            'lastname' => 'User Two',
            'account_type' => 'Staff',
        ]
    ],
    'api_version' => '1.0',
    'source_service' => 'admin.cranecali-ms.com'
];

$jsonPayload2 = json_encode($batchPayload);
$signature2 = hash_hmac('sha256', $jsonPayload2, $webhookSecret);

echo "Payload (2 users):\n";
echo json_encode($batchPayload, JSON_PRETTY_PRINT) . "\n\n";

echo "Signature: {$signature2}\n\n";

echo "cURL command:\n";
echo "curl -X POST {$baseUrl}/api/user-sync/batch \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -H \"X-Webhook-Signature: {$signature2}\" \\\n";
echo "  -d '{$jsonPayload2}'\n\n";

// Example 3: Update User (existing)
echo "================================================\n";
echo "Example 3: Update Existing User\n";
echo "--------------------------------\n";

$updatePayload = [
    'user' => [
        'external_user_id' => 'TEST-USER-001',
        'email' => 'test.user@example.com',
        'name' => 'Test',
        'lastname' => 'User Updated',
        'position' => 'Senior Test Engineer',
        'account_type' => 'Staff',
    ],
    'api_version' => '1.0'
];

$jsonPayload3 = json_encode($updatePayload);
$signature3 = hash_hmac('sha256', $jsonPayload3, $webhookSecret);

echo "Payload:\n";
echo json_encode($updatePayload, JSON_PRETTY_PRINT) . "\n\n";

echo "Signature: {$signature3}\n\n";

echo "cURL command:\n";
echo "curl -X POST {$baseUrl}/api/user-sync/webhook \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -H \"X-Webhook-Signature: {$signature3}\" \\\n";
echo "  -d '{$jsonPayload3}'\n\n";

// Example 4: Test Invalid Signature
echo "================================================\n";
echo "Example 4: Testing Invalid Signature (should fail)\n";
echo "--------------------------------\n";

$invalidSignature = 'invalid_signature_123456';

echo "This should return 401 Unauthorized:\n";
echo "curl -X POST {$baseUrl}/api/user-sync/webhook \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -H \"X-Webhook-Signature: {$invalidSignature}\" \\\n";
echo "  -d '{$jsonPayload1}'\n\n";

// Summary
echo "================================================\n";
echo "Configuration Summary\n";
echo "================================\n";
echo "Webhook Secret: " . substr($webhookSecret, 0, 10) . "...\n";
echo "Base URL: {$baseUrl}\n";
echo "API Endpoint: {$baseUrl}/api/user-sync/webhook\n";
echo "================================================\n\n";

echo "Note: Make sure USER_SYNC_ENABLED=true in your .env file\n";
echo "      and the webhook secret matches between systems.\n";
