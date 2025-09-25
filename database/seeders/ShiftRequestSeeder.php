<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ShiftRequest;
use Carbon\Carbon;

class ShiftRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->count() === 0) {
            $this->command->error('No users found. Please create some users first.');
            return;
        }

        $requestTypes = ['schedule_change', 'swap', 'overtime', 'cover'];
        $reasons = [
            'Personal schedule conflict',
            'Medical appointment',
            'Family emergency',
            'Project deadline approaching',
            'Transportation issues',
            'Childcare needs',
        ];

        // Create 5 sample shift requests
        for ($i = 0; $i < 5; $i++) {
            $user = $users->random();
            $requestType = $requestTypes[array_rand($requestTypes)];
            $reason = $reasons[array_rand($reasons)];
            
            $requestData = [
                'user_id' => $user->id,
                'request_type' => $requestType,
                'requested_date' => Carbon::now()->addDays(rand(1, 30)),
                'reason' => $reason,
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(rand(0, 7)),
                'updated_at' => Carbon::now(),
            ];

            // Add type-specific fields
            switch ($requestType) {
                case 'schedule_change':
                    $requestData['current_start_time'] = '08:00:00';
                    $requestData['current_end_time'] = '16:00:00';
                    $requestData['requested_start_time'] = '14:00:00';
                    $requestData['requested_end_time'] = '22:00:00';
                    break;
                    
                case 'swap':
                    $requestData['current_start_time'] = '08:00:00';
                    $requestData['current_end_time'] = '16:00:00';
                    $requestData['requested_start_time'] = '16:00:00';
                    $requestData['requested_end_time'] = '00:00:00';
                    // Optionally set swap_with_user_id if there are multiple users
                    if ($users->count() > 1) {
                        $swapUser = $users->where('id', '!=', $user->id)->random();
                        $requestData['swap_with_user_id'] = $swapUser->id;
                    }
                    break;
                    
                case 'overtime':
                    $requestData['current_start_time'] = '08:00:00';
                    $requestData['current_end_time'] = '16:00:00';
                    $requestData['requested_start_time'] = '08:00:00';
                    $requestData['requested_end_time'] = '20:00:00';
                    break;
                    
                case 'cover':
                    $requestData['requested_start_time'] = '08:00:00';
                    $requestData['requested_end_time'] = '16:00:00';
                    break;
            }

            ShiftRequest::create($requestData);
        }

        $this->command->info('5 sample shift requests created successfully!');
    }
}