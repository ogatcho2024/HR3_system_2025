<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create a notification for a specific user
     */
    public function create(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        string $category = 'general',
        array $data = [],
        bool $isImportant = false,
        ?string $actionUrl = null,
        ?string $actionText = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'category' => $category,
            'data' => $data,
            'is_important' => $isImportant,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
        ]);
    }

    /**
     * Create notifications for multiple users
     */
    public function createForUsers(
        Collection|array $users,
        string $title,
        string $message,
        string $type = 'info',
        string $category = 'general',
        array $data = [],
        bool $isImportant = false,
        ?string $actionUrl = null,
        ?string $actionText = null
    ): Collection {
        $notifications = collect();

        foreach ($users as $user) {
            $notifications->push($this->create(
                $user,
                $title,
                $message,
                $type,
                $category,
                $data,
                $isImportant,
                $actionUrl,
                $actionText
            ));
        }

        return $notifications;
    }

    /**
     * Create notification for all users
     */
    public function createForAllUsers(
        string $title,
        string $message,
        string $type = 'info',
        string $category = 'system',
        array $data = [],
        bool $isImportant = false,
        ?string $actionUrl = null,
        ?string $actionText = null
    ): Collection {
        $users = User::all();
        return $this->createForUsers($users, $title, $message, $type, $category, $data, $isImportant, $actionUrl, $actionText);
    }

    /**
     * Create leave request notification
     */
    public function createLeaveRequestNotification(User $user, string $status, array $leaveData = []): Notification
    {
        $messages = [
            'submitted' => 'Your leave request has been submitted and is pending approval.',
            'approved' => 'Your leave request has been approved.',
            'rejected' => 'Your leave request has been rejected.',
        ];

        $types = [
            'submitted' => 'info',
            'approved' => 'success',
            'rejected' => 'error',
        ];

        return $this->create(
            $user,
            'Leave Request ' . ucfirst($status),
            $messages[$status] ?? 'Leave request status updated.',
            $types[$status] ?? 'info',
            'leave',
            $leaveData,
            in_array($status, ['approved', 'rejected']),
            route('employee.leave-requests'),
            'View Leave Requests'
        );
    }

    /**
     * Create timesheet notification
     */
    public function createTimesheetNotification(User $user, string $status, array $timesheetData = []): Notification
    {
        $messages = [
            'submitted' => 'Your timesheet has been submitted successfully.',
            'approved' => 'Your timesheet has been approved.',
            'rejected' => 'Your timesheet has been rejected and requires revision.',
        ];

        $types = [
            'submitted' => 'success',
            'approved' => 'success',
            'rejected' => 'warning',
        ];

        return $this->create(
            $user,
            'Timesheet ' . ucfirst($status),
            $messages[$status] ?? 'Timesheet status updated.',
            $types[$status] ?? 'info',
            'timesheet',
            $timesheetData,
            $status === 'rejected',
            route('employee.timesheets'),
            'View Timesheets'
        );
    }

    /**
     * Create shift notification
     */
    public function createShiftNotification(User $user, string $status, array $shiftData = []): Notification
    {
        $messages = [
            'assigned' => 'You have been assigned a new shift.',
            'changed' => 'Your shift schedule has been updated.',
            'requested' => 'Your shift swap request has been submitted.',
            'approved' => 'Your shift swap request has been approved.',
            'rejected' => 'Your shift swap request has been rejected.',
        ];

        $types = [
            'assigned' => 'info',
            'changed' => 'warning',
            'requested' => 'info',
            'approved' => 'success',
            'rejected' => 'error',
        ];

        return $this->create(
            $user,
            'Shift ' . ucfirst($status),
            $messages[$status] ?? 'Shift status updated.',
            $types[$status] ?? 'info',
            'shift',
            $shiftData,
            in_array($status, ['assigned', 'changed']),
            null,
            null
        );
    }

    /**
     * Create system notification
     */
    public function createSystemNotification(
        User $user,
        string $title,
        string $message,
        bool $isImportant = false,
        ?string $actionUrl = null,
        ?string $actionText = null
    ): Notification {
        return $this->create(
            $user,
            $title,
            $message,
            'info',
            'system',
            [],
            $isImportant,
            $actionUrl,
            $actionText
        );
    }

    /**
     * Create welcome notification for new users
     */
    public function createWelcomeNotification(User $user): Notification
    {
        return $this->create(
            $user,
            'Welcome to HR Management System',
            'Welcome! Please complete your profile setup to get started.',
            'info',
            'system',
            [],
            true,
            route('employee.profile'),
            'Complete Profile'
        );
    }

    /**
     * Get unread notification count for a user
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()->unread()->count();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): int
    {
        return $user->notifications()
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Delete old read notifications (older than specified days)
     */
    public function cleanupOldNotifications(int $daysOld = 30): int
    {
        return Notification::where('read_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}
