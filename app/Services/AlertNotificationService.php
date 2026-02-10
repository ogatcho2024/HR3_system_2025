<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class AlertNotificationService
{
    public function dispatchIfEligible(Alert $alert): void
    {
        if (!$this->isActiveNow($alert)) {
            return;
        }

        // Avoid duplicate notifications for the same alert.
        if (Notification::where('data->alert_id', $alert->id)->exists()) {
            return;
        }

        $targets = $this->resolveTargets($alert);
        if ($targets->isEmpty()) {
            return;
        }

        $now = now();
        $payload = $targets->map(function ($userId) use ($alert, $now) {
            return [
                'user_id' => $userId,
                'title' => $alert->title,
                'message' => $alert->message,
                'type' => $alert->type ?? 'info',
                'category' => 'system',
                'data' => json_encode(['alert_id' => $alert->id]),
                'read_at' => null,
                'is_important' => ($alert->priority ?? '') === 'urgent',
                'action_url' => route('notifications.index'),
                'action_text' => 'View Alerts',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });

        Notification::insert($payload->all());
    }

    private function isActiveNow(Alert $alert): bool
    {
        if (!$alert->is_active) {
            return false;
        }

        $now = now();
        if ($alert->start_date && Carbon::parse($alert->start_date)->gt($now)) {
            return false;
        }
        if ($alert->end_date && Carbon::parse($alert->end_date)->lt($now)) {
            return false;
        }

        return true;
    }

    private function resolveTargets(Alert $alert)
    {
        $roles = $alert->target_roles ?? [];

        $query = User::query();

        if (Schema::hasColumn('users', 'is_active')) {
            $query->where('is_active', true);
        }

        if (!empty($roles)) {
            $query->whereIn('account_type', $roles);
        }

        return $query->pluck('id');
    }
}
