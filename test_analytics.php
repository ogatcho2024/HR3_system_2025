<?php

require __DIR__ . '/vendor/autoload.php';

// Boot Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

use App\Http\Controllers\AttendanceController;
use Illuminate\Http\Request;

echo "Testing Attendance Analytics Controller Methods\n";
echo "=============================================\n\n";

try {
    $controller = new AttendanceController();
    
    // Test the analytics data method directly
    echo "Testing daily analytics...\n";
    $request = new Request(['period' => 'daily']);
    $response = $controller->getAnalyticsData($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data && isset($data['success'])) {
        echo "✓ Daily analytics success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
        if (isset($data['data'])) {
            echo "✓ Data keys: " . implode(', ', array_keys($data['data'])) . "\n";
            echo "✓ Sample values: Present=" . ($data['data']['present'] ?? 'N/A') . 
                 ", Late=" . ($data['data']['late'] ?? 'N/A') . 
                 ", Absent=" . ($data['data']['absent'] ?? 'N/A') . "\n";
        }
    } else {
        echo "✗ Daily analytics failed\n";
    }
    
    echo "\n";
    
    // Test real-time data method
    echo "Testing real-time data...\n";
    $request = new Request();
    $response = $controller->getRealTimeData($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data && isset($data['success'])) {
        echo "✓ Real-time data success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
        if (isset($data['employees'])) {
            echo "✓ Employees returned: " . count($data['employees']) . "\n";
            if (count($data['employees']) > 0) {
                $firstEmployee = $data['employees'][0];
                echo "✓ Sample employee: " . ($firstEmployee['name'] ?? 'N/A') . " (" . ($firstEmployee['status'] ?? 'N/A') . ")\n";
            }
        }
        if (isset($data['stats'])) {
            echo "✓ Stats: " . json_encode($data['stats']) . "\n";
        }
    } else {
        echo "✗ Real-time data failed\n";
    }
    
    echo "\n";
    
    // Test weekly analytics
    echo "Testing weekly analytics...\n";
    $request = new Request(['period' => 'weekly']);
    $response = $controller->getAnalyticsData($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data && isset($data['success'])) {
        echo "✓ Weekly analytics success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
        if (isset($data['data'])) {
            echo "✓ Week data keys: " . implode(', ', array_keys($data['data'])) . "\n";
        }
    } else {
        echo "✗ Weekly analytics failed\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
