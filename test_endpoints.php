<?php

// Simple test script to verify our attendance analytics endpoints
echo "Testing Attendance Analytics Endpoints\n";
echo "=====================================\n\n";

// Test different analytics periods
$periods = ['daily', 'weekly', 'monthly', 'yearly'];
$baseUrl = 'http://localhost/dashboard/HumanResources3/public';

foreach ($periods as $period) {
    echo "Testing {$period} analytics...\n";
    
    $url = "{$baseUrl}/attendance/analytics-data?period={$period}";
    echo "URL: {$url}\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'method' => 'GET',
        ],
    ]);
    
    try {
        $response = file_get_contents($url, false, $context);
        if ($response !== false) {
            echo "Raw response length: " . strlen($response) . "\n";
            echo "Raw response (first 200 chars): " . substr($response, 0, 200) . "...\n";
            
            $data = json_decode($response, true);
            if ($data && isset($data['success'])) {
                echo "✓ Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
                if (isset($data['data'])) {
                    echo "✓ Data returned: " . count($data['data']) . " keys\n";
                }
            } else {
                echo "✗ Invalid response format or JSON decode failed\n";
                echo "JSON error: " . json_last_error_msg() . "\n";
            }
        } else {
            echo "✗ Failed to fetch data\n";
        }
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test real-time data endpoint
echo "Testing real-time data endpoint...\n";
$url = "{$baseUrl}/attendance/real-time-data";
echo "URL: {$url}\n";

try {
    $response = file_get_contents($url, false, $context);
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && isset($data['success'])) {
            echo "✓ Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
            if (isset($data['employees'])) {
                echo "✓ Employees returned: " . count($data['employees']) . "\n";
            }
            if (isset($data['stats'])) {
                echo "✓ Stats returned: " . count($data['stats']) . " metrics\n";
            }
        } else {
            echo "✗ Invalid response format\n";
        }
    } else {
        echo "✗ Failed to fetch data\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
