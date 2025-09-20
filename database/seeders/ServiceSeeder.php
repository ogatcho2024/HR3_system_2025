<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('services')->insert([
            [
                'id' => 1,
                'service_id' => 'hr-001',
                'service_name' => 'HR Management',
                'service_description' => 'Comprehensive HR management services including employee onboarding, performance tracking, and compliance management.',
                'service_price' => '$99 - $299/month',
                'service_image' => 'hr-management.jpg',
                'service_featured' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'service_id' => 'ess-002',
                'service_name' => 'Employee Self-Service',
                'service_description' => 'Self-service portal for employees to manage their personal information, leave requests, and view pay stubs.',
                'service_price' => '$49 - $149/month',
                'service_image' => 'employee-self-service.jpg',
                'service_featured' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'service_id' => 'lm-003',
                'service_name' => 'Leave Management',
                'service_description' => 'Complete leave request and approval system with calendar integration and automated notifications.',
                'service_price' => '$79 - $199/month',
                'service_image' => 'leave-management.jpg',
                'service_featured' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}