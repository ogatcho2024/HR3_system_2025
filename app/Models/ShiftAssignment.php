<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_template_id',
        'assignment_date',
        'status',
        'actual_start_time',
        'actual_end_time',
        'notes',
        'assigned_by'
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'actual_start_time' => 'datetime:H:i',
        'actual_end_time' => 'datetime:H:i',
        'assigned_at' => 'datetime'
    ];

    /**
     * Get the employee that owns the shift assignment.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the shift template that owns the assignment.
     */
    public function shiftTemplate(): BelongsTo
    {
        return $this->belongsTo(ShiftTemplate::class);
    }

    /**
     * Get the user who assigned this shift.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope for assignments within a date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('assignment_date', [$startDate, $endDate]);
    }

    /**
     * Scope for assignments by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for assignments by department (through employee).
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->whereHas('employee', function ($q) use ($department) {
            $q->where('department', $department);
        });
    }

    /**
     * Scope for assignments by shift template.
     */
    public function scopeByShiftTemplate($query, $shiftTemplateId)
    {
        return $query->where('shift_template_id', $shiftTemplateId);
    }

    /**
     * Get the day of week for the assignment date.
     */
    public function getDayOfWeekAttribute(): string
    {
        return $this->assignment_date->format('l'); // Full textual representation of a day
    }

    /**
     * Check if the assignment is for today.
     */
    public function isTodayAttribute(): bool
    {
        return $this->assignment_date->isToday();
    }

    /**
     * Get formatted assignment date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->assignment_date->format('M j, Y');
    }
}
