<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an audit entry.
     */
    public function log(
        string $actionType,
        string $description,
        ?int $userId = null,
        ?string $affectedTable = null,
        ?int $affectedRecordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        int $loginAttemptCount = 0
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action_type' => $actionType,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'affected_table' => $affectedTable,
            'affected_record_id' => $affectedRecordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'login_attempt_count' => $loginAttemptCount,
        ]);
    }

    /**
     * Log successful login.
     */
    public function logLogin(?User $user = null): AuditLog
    {
        $user = $user ?? Auth::user();
        
        return $this->log(
            'login',
            "User {$user->name} {$user->lastname} logged in successfully",
            $user->id
        );
    }

    /**
     * Log logout.
     */
    public function logLogout(?User $user = null): AuditLog
    {
        $user = $user ?? Auth::user();
        
        return $this->log(
            'logout',
            "User {$user->name} {$user->lastname} logged out",
            $user->id
        );
    }

    /**
     * Log failed login attempt.
     */
    public function logFailedLogin(string $email, int $attemptCount = 1): AuditLog
    {
        return $this->log(
            'failed_login',
            "Failed login attempt for email: {$email}",
            null,
            null,
            null,
            null,
            null,
            $attemptCount
        );
    }

    /**
     * Log OTP verification success.
     */
    public function logOtpVerified(?User $user = null): AuditLog
    {
        $user = $user ?? Auth::user();
        
        return $this->log(
            'otp_verified',
            "OTP verified successfully for user {$user->name} {$user->lastname}",
            $user->id
        );
    }

    /**
     * Log OTP verification failure.
     */
    public function logOtpFailed(?User $user = null, string $reason = 'Invalid OTP'): AuditLog
    {
        $user = $user ?? Auth::user();
        
        return $this->log(
            'otp_failed',
            "OTP verification failed for user {$user->name} {$user->lastname}: {$reason}",
            $user->id
        );
    }

    /**
     * Log record creation.
     */
    public function logCreate(string $table, int $recordId, array $newValues, string $description = null): AuditLog
    {
        $description = $description ?? "Created new record in {$table}";
        
        return $this->log(
            'create',
            $description,
            Auth::id(),
            $table,
            $recordId,
            null,
            $newValues
        );
    }

    /**
     * Log record update.
     */
    public function logUpdate(string $table, int $recordId, array $oldValues, array $newValues, string $description = null): AuditLog
    {
        $description = $description ?? "Updated record in {$table}";
        
        // Only include changed values
        $changes = [];
        foreach ($newValues as $key => $value) {
            if (isset($oldValues[$key]) && $oldValues[$key] != $value) {
                $changes[$key] = [
                    'old' => $oldValues[$key],
                    'new' => $value
                ];
            }
        }
        
        return $this->log(
            'update',
            $description,
            Auth::id(),
            $table,
            $recordId,
            $oldValues,
            $newValues
        );
    }

    /**
     * Log record deletion.
     */
    public function logDelete(string $table, int $recordId, array $oldValues, string $description = null): AuditLog
    {
        $description = $description ?? "Deleted record from {$table}";
        
        return $this->log(
            'delete',
            $description,
            Auth::id(),
            $table,
            $recordId,
            $oldValues,
            null
        );
    }

    /**
     * Log record view.
     */
    public function logView(string $table, int $recordId, string $description = null): AuditLog
    {
        $description = $description ?? "Viewed record from {$table}";
        
        return $this->log(
            'view',
            $description,
            Auth::id(),
            $table,
            $recordId
        );
    }

    /**
     * Log data export.
     */
    public function logExport(string $description, ?string $table = null): AuditLog
    {
        return $this->log(
            'export',
            $description,
            Auth::id(),
            $table
        );
    }

    /**
     * Log generic action.
     */
    public function logOther(string $description, ?array $newValues = null): AuditLog
    {
        return $this->log(
            'other',
            $description,
            Auth::id(),
            null,
            null,
            null,
            $newValues
        );
    }

    /**
     * Log account creation.
     */
    public function logAccountCreated(int $accountUserId, array $accountData, string $description = null): AuditLog
    {
        $description = $description ?? "Created new user account: {$accountData['name']} {$accountData['lastname']}";
        
        return $this->log(
            'account_created',
            $description,
            Auth::id(),
            'users',
            $accountUserId,
            null,
            $accountData
        );
    }

    /**
     * Log account update.
     */
    public function logAccountUpdated(int $accountUserId, array $oldData, array $newData, string $description = null): AuditLog
    {
        $description = $description ?? "Updated user account #{$accountUserId}";
        
        return $this->log(
            'account_updated',
            $description,
            Auth::id(),
            'users',
            $accountUserId,
            $oldData,
            $newData
        );
    }

    /**
     * Log account deletion.
     */
    public function logAccountDeleted(int $accountUserId, array $accountData, string $description = null): AuditLog
    {
        $description = $description ?? "Deleted user account: {$accountData['name']} {$accountData['lastname']}";
        
        return $this->log(
            'account_deleted',
            $description,
            Auth::id(),
            'users',
            $accountUserId,
            $accountData,
            null
        );
    }

    /**
     * Log password change.
     */
    public function logPasswordChanged(?User $user = null, string $description = null): AuditLog
    {
        $user = $user ?? Auth::user();
        $description = $description ?? "Password changed for user: {$user->name} {$user->lastname}";
        
        return $this->log(
            'password_changed',
            $description,
            $user->id,
            'users',
            $user->id
        );
    }

    /**
     * Log email change.
     */
    public function logEmailChanged(?User $user = null, string $oldEmail, string $newEmail): AuditLog
    {
        $user = $user ?? Auth::user();
        
        return $this->log(
            'email_changed',
            "Email changed from {$oldEmail} to {$newEmail} for user: {$user->name} {$user->lastname}",
            $user->id,
            'users',
            $user->id,
            ['email' => $oldEmail],
            ['email' => $newEmail]
        );
    }

    /**
     * Log role/account type change.
     */
    public function logRoleChanged(int $accountUserId, string $oldRole, string $newRole, string $userName): AuditLog
    {
        return $this->log(
            'role_changed',
            "Role changed from '{$oldRole}' to '{$newRole}' for user: {$userName}",
            Auth::id(),
            'users',
            $accountUserId,
            ['account_type' => $oldRole],
            ['account_type' => $newRole]
        );
    }

    /**
     * Get recent activity for a user.
     */
    public function getUserActivity(int $userId, int $limit = 10)
    {
        return AuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent failed login attempts for an IP or email.
     */
    public function getRecentFailedLogins(string $identifier, int $minutes = 30)
    {
        return AuditLog::where('action_type', 'failed_login')
            ->where(function ($query) use ($identifier) {
                $query->where('ip_address', $identifier)
                    ->orWhere('description', 'like', "%{$identifier}%");
            })
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Get audit statistics.
     */
    public function getStatistics(array $filters = [])
    {
        $query = AuditLog::query();

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return [
            'total_actions' => (clone $query)->count(),
            'by_type' => (clone $query)->selectRaw('action_type, COUNT(*) as count')
                ->groupBy('action_type')
                ->pluck('count', 'action_type'),
            'unique_users' => (clone $query)->distinct('user_id')->count('user_id'),
            'failed_logins' => (clone $query)->where('action_type', 'failed_login')->count(),
        ];
    }
}
