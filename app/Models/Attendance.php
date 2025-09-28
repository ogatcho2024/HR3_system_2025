<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'break_start',
        'break_end',
        'hours_worked',
        'overtime_hours',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime:H:i',
        'clock_out_time' => 'datetime:H:i',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically calculate hours and overtime when saving
        static::saving(function ($attendance) {
            if ($attendance->clock_in_time && $attendance->clock_out_time) {
                $attendance->hours_worked = $attendance->calculateHours();
                $attendance->overtime_hours = $attendance->calculateOvertime();
            }
        });
    }

    /**
     * Get the user that owns the attendance record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created the attendance record.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
        
        $totalMinutes = $clockIn->diffInMinutes($clockOut);
        
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
     * Calculate overtime hours (hours worked beyond 8 hours).
     */
    public function calculateOvertime(): float
    {
        $hoursWorked = $this->calculateHours();
        $standardHours = 8.0;
        
        if ($hoursWorked > $standardHours) {
            return round($hoursWorked - $standardHours, 2);
        }
        
        return 0.0;
    }

    /**
     * Calculate and update both hours_worked and overtime_hours.
     */
    public function calculateAndUpdateHours(): void
    {
        $hoursWorked = $this->calculateHours();
        $overtimeHours = $this->calculateOvertime();
        
        $this->update([
            'hours_worked' => $hoursWorked,
            'overtime_hours' => $overtimeHours
        ]);
    }

    /**
     * Scope a query to only include present records.
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope a query to only include late records.
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope a query to only include absent records.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope a query to only include on break records.
     */
    public function scopeOnBreak($query)
    {
        return $query->where('status', 'on_break');
    }
}