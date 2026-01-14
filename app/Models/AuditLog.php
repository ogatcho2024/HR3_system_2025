<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    const UPDATED_AT = null; // Audit logs are immutable, no updated_at

    // Valid action types
    const ACTION_TYPES = [
        'login',
        'logout',
        'failed_login',
        'otp_verified',
        'otp_failed',
        'create',
        'update',
        'delete',
        'view',
        'export',
        'account_created',
        'account_updated',
        'account_deleted',
        'password_changed',
        'email_changed',
        'role_changed',
        'other',
    ];

    protected $fillable = [
        'user_id',
        'action_type',
        'description',
        'ip_address',
        'user_agent',
        'affected_table',
        'affected_record_id',
        'old_values',
        'new_values',
        'login_attempt_count',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'login_attempt_count' => 'integer',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by action type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('action_type', $type);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by IP address.
     */
    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by affected table.
     */
    public function scopeAffecting($query, $table, $recordId = null)
    {
        $query->where('affected_table', $table);
        
        if ($recordId) {
            $query->where('affected_record_id', $recordId);
        }
        
        return $query;
    }

    /**
     * Scope for authentication-related actions.
     */
    public function scopeAuthActions($query)
    {
        return $query->whereIn('action_type', ['login', 'logout', 'failed_login', 'otp_verified', 'otp_failed']);
    }

    /**
     * Scope for data modification actions.
     */
    public function scopeDataActions($query)
    {
        return $query->whereIn('action_type', ['create', 'update', 'delete']);
    }

    /**
     * Scope for account change actions.
     */
    public function scopeAccountActions($query)
    {
        return $query->whereIn('action_type', ['account_created', 'account_updated', 'account_deleted', 'password_changed', 'email_changed', 'role_changed']);
    }

    /**
     * Scope for failed login attempts.
     */
    public function scopeFailedLoginAttempts($query)
    {
        return $query->where('action_type', 'failed_login');
    }

    /**
     * Get a human-readable action type label.
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            'login' => 'Login',
            'logout' => 'Logout',
            'failed_login' => 'Failed Login',
            'otp_verified' => 'OTP Verified',
            'otp_failed' => 'OTP Failed',
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'view' => 'Viewed',
            'export' => 'Exported',
            'account_created' => 'Account Created',
            'account_updated' => 'Account Updated',
            'account_deleted' => 'Account Deleted',
            'password_changed' => 'Password Changed',
            'email_changed' => 'Email Changed',
            'role_changed' => 'Role Changed',
            'other' => 'Other',
        ];

        return $labels[$this->action_type] ?? $this->action_type;
    }

    /**
     * Get formatted user name or 'Guest' if unauthenticated.
     */
    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->name . ' ' . $this->user->lastname : 'Guest';
    }

    /**
     * Prevent modification of audit logs (except by super admin).
     */
    protected static function boot()
    {
        parent::boot();

        // Validate action_type before creating
        static::creating(function ($model) {
            if (!in_array($model->action_type, self::ACTION_TYPES)) {
                throw new \InvalidArgumentException("Invalid action type: {$model->action_type}");
            }
        });

        // Prevent updates to audit logs
        static::updating(function ($model) {
            // Allow updates only if explicitly allowed (will be checked in policy)
            if (!$model->canBeModified()) {
                return false;
            }
        });
    }

    /**
     * Check if audit log can be modified.
     */
    public function canBeModified()
    {
        // Can add logic here if needed, but generally audit logs should be immutable
        return false;
    }
}
