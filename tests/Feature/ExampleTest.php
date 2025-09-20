<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the application returns a successful response', function () {
    // Seed basic data for the test
    \DB::table('services')->insert([
        [
            'service_id' => 'test-001',
            'service_name' => 'Test Service',
            'service_description' => 'Test Description',
            'service_price' => '$100',
            'service_image' => 'test.jpg',
            'service_featured' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);
    
    $response = $this->get('/');
    
    $response->assertStatus(200);
});
