<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_time',
        'clock_out_time',
        'break_start',
        'break_end',
        'hours_worked',
        'overtime_hours',
        'project_name',
        'work_description',
        'status',
        'manager_comments',
        'submitted_at',
        'approved_at',
        'approved_by',
        'sent_to_payroll',
        'payroll_sent_at',
        'payroll_send_attempts',
        'payroll_last_error',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in_time' => 'datetime:H:i',
        'clock_out_time' => 'datetime:H:i',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'sent_to_payroll' => 'boolean',
        'payroll_sent_at' => 'datetime',
        'payroll_send_attempts' => 'integer',
    ];

    /**
     * Get the user that owns the timesheet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who approved the timesheet.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calculate hours worked based on clock in/out times.
     */
    public function calculateHours(): float
    {
        if (!$this->clock_in_time || !$this->clock_out_time) {
            return 0;
        }

        $clockIn = Carbon::createFromTimeString($this->clock_in_time);
        $clockOut = Carbon::createFromTimeString($this->clock_out_time);
        
        $totalMinutes = $clockOut->diffInMinutes($clockIn);
        
        // Subtract break time if provided
        if ($this->break_start && $this->break_end) {
            $breakStart = Carbon::createFromTimeString($this->break_start);
            $breakEnd = Carbon::createFromTimeString($this->break_end);
            $breakMinutes = $breakEnd->diffInMinutes($breakStart);
            $totalMinutes -= $breakMinutes;
        }
        
        return round($totalMinutes / 60, 2);
    }

    /**
     * Scope a query to only include draft timesheets.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include submitted timesheets.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope a query to only include approved timesheets.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'submitted' => 'bg-blue-100 text-blue-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
