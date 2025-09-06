<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notificationService = app(NotificationService::class);
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found. Please create users first.');
            return;
        }

        foreach ($users as $user) {
            // Create welcome notification
            $notificationService->createWelcomeNotification($user);

            // Create some sample notifications
            $notificationService->create(
                $user,
                'System Maintenance Scheduled',
                'The system will undergo maintenance on Saturday from 2:00 AM to 4:00 AM.',
                'warning',
                'system',
                ['maintenance_date' => '2025-09-07', 'downtime' => '2 hours'],
                true
            );

            $notificationService->create(
                $user,
                'New Company Policy Update',
                'Please review the updated remote work policy in the employee handbook.',
                'info',
                'general',
                ['policy_section' => 'Remote Work'],
                false,
                route('employee.profile'),
                'View Policy'
            );

            $notificationService->createLeaveRequestNotification(
                $user,
                'approved',
                [
                    'leave_type' => 'annual',
                    'start_date' => '2025-09-15',
                    'end_date' => '2025-09-17',
                    'days_requested' => 3,
                ]
            );

            $notificationService->createTimesheetNotification(
                $user,
                'submitted',
                [
                    'work_date' => '2025-09-01',
                    'hours_worked' => 8,
                    'project_name' => 'Website Development',
                ]
            );
        }

        $this->command->info('Sample notifications created for all users.');
    }
}
