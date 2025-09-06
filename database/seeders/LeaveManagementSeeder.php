<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeavePolicy;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class LeaveManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Leave Policies
        $policies = [
            [
                'name' => 'Standard Vacation Policy',
                'leave_type' => 'vacation',
                'annual_entitlement' => 20.00,
                'max_consecutive_days' => 10,
                'requires_approval' => true,
                'min_notice_days' => 7,
                'allow_carry_forward' => true,
                'max_carry_forward_days' => 5,
                'carry_forward_expiry_months' => 3,
                'description' => 'Standard vacation leave policy for all employees',
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave Policy',
                'leave_type' => 'sick',
                'annual_entitlement' => 10.00,
                'max_consecutive_days' => 30,
                'requires_approval' => false,
                'min_notice_days' => 0,
                'allow_carry_forward' => false,
                'description' => 'Sick leave policy with no advance notice required',
                'is_active' => true,
            ],
            [
                'name' => 'Personal Leave Policy',
                'leave_type' => 'personal',
                'annual_entitlement' => 5.00,
                'max_consecutive_days' => 3,
                'requires_approval' => true,
                'min_notice_days' => 2,
                'allow_carry_forward' => false,
                'description' => 'Personal leave for personal matters',
                'is_active' => true,
            ],
        ];
        
        foreach ($policies as $policy) {
            LeavePolicy::firstOrCreate(
                ['name' => $policy['name']],
                $policy
            );
        }
        
        // Create Leave Balances for existing users
        $users = User::with('employee')->get();
        $currentYear = date('Y');
        
        foreach ($users as $user) {
            if ($user->employee) {
                // Create vacation balance
                LeaveBalance::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'leave_type' => 'vacation',
                        'year' => $currentYear,
                    ],
                    [
                        'total_entitled' => 20.00,
                        'used' => rand(0, 8),
                        'pending' => rand(0, 3),
                        'available' => 0, // Will be calculated
                        'carried_forward' => rand(0, 3),
                    ]
                );
                
                // Create sick leave balance
                LeaveBalance::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'leave_type' => 'sick',
                        'year' => $currentYear,
                    ],
                    [
                        'total_entitled' => 10.00,
                        'used' => rand(0, 4),
                        'pending' => rand(0, 2),
                        'available' => 0, // Will be calculated
                        'carried_forward' => 0,
                    ]
                );
                
                // Create personal leave balance
                LeaveBalance::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'leave_type' => 'personal',
                        'year' => $currentYear,
                    ],
                    [
                        'total_entitled' => 5.00,
                        'used' => rand(0, 2),
                        'pending' => rand(0, 1),
                        'available' => 0, // Will be calculated
                        'carried_forward' => 0,
                    ]
                );
            }
        }
        
        // Recalculate available balances
        $balances = LeaveBalance::all();
        foreach ($balances as $balance) {
            $balance->calculateAvailable();
        }
        
        // Create some sample leave requests
        if ($users->count() > 0) {
            $sampleRequests = [
                [
                    'user_id' => $users->first()->id,
                    'leave_type' => 'vacation',
                    'start_date' => Carbon::now()->addDays(10),
                    'end_date' => Carbon::now()->addDays(14),
                    'days_requested' => 5,
                    'reason' => 'Family vacation to the beach',
                    'status' => 'pending',
                ],
                [
                    'user_id' => $users->first()->id,
                    'leave_type' => 'sick',
                    'start_date' => Carbon::now()->subDays(2),
                    'end_date' => Carbon::now()->subDays(2),
                    'days_requested' => 1,
                    'reason' => 'Doctor appointment',
                    'status' => 'approved',
                    'approved_at' => Carbon::now()->subDays(1),
                    'approved_by' => $users->count() > 1 ? $users->skip(1)->first()->id : $users->first()->id,
                ],
            ];
            
            if ($users->count() > 1) {
                $sampleRequests[] = [
                    'user_id' => $users->skip(1)->first()->id,
                    'leave_type' => 'personal',
                    'start_date' => Carbon::now()->addDays(5),
                    'end_date' => Carbon::now()->addDays(5),
                    'days_requested' => 1,
                    'reason' => 'Personal appointment',
                    'status' => 'pending',
                ];
            }
            
            foreach ($sampleRequests as $request) {
                LeaveRequest::firstOrCreate(
                    [
                        'user_id' => $request['user_id'],
                        'start_date' => $request['start_date'],
                        'end_date' => $request['end_date'],
                    ],
                    $request
                );
            }
        }
        
        echo "Leave Management seeder completed successfully!\n";
        echo "- Created leave policies\n";
        echo "- Created leave balances for all employees\n";
        echo "- Created sample leave requests\n";
    }
}
