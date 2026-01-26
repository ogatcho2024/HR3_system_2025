<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_type',
        'requested_date',
        'current_start_time',
        'current_end_time',
        'requested_start_time',
        'requested_end_time',
        'swap_with_user_id',
        'reason',
        'status',
        'manager_comments',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the shift request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user to swap with.
     */
    public function swapWithUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'swap_with_user_id');
    }

    /**
     * Get the user who approved the request.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
    
    /**
     * Accessor for request_date (alias for requested_date)
     */
    public function getRequestDateAttribute()
    {
        return $this->requested_date;
    }
    
    /**
     * Accessor for rejection_reason (alias for manager_comments)
     */
    public function getRejectionReasonAttribute()
    {
        return $this->manager_comments;
    }
}
