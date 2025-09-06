<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'year',
        'total_entitled',
        'used',
        'pending',
        'available',
        'carried_forward',
        'expires_at',
    ];

    protected $casts = [
        'total_entitled' => 'decimal:2',
        'used' => 'decimal:2',
        'pending' => 'decimal:2',
        'available' => 'decimal:2',
        'carried_forward' => 'decimal:2',
        'expires_at' => 'date',
        'year' => 'integer',
    ];

    /**
     * Get the user that owns the leave balance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate and update the available balance.
     */
    public function calculateAvailable(): void
    {
        $this->available = $this->total_entitled + $this->carried_forward - $this->used - $this->pending;
        $this->save();
    }

    /**
     * Get the utilization percentage.
     */
    public function getUtilizationPercentageAttribute(): float
    {
        $total = $this->total_entitled + $this->carried_forward;
        return $total > 0 ? round(($this->used / $total) * 100, 1) : 0;
    }

    /**
     * Check if balance is sufficient for a request.
     */
    public function hasSufficientBalance(float $days): bool
    {
        return $this->available >= $days;
    }

    /**
     * Scope to get balances for a specific year.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to get balances for a specific leave type.
     */
    public function scopeForLeaveType($query, string $leaveType)
    {
        return $query->where('leave_type', $leaveType);
    }
}
