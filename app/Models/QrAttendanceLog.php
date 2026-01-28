<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class QrAttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'log_type',
        'scanned_at',
        'daily_token',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the log.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope a query to only include logs from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scanned_at', Carbon::today());
    }

    /**
     * Scope a query to only include logs from a specific date.
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('scanned_at', $date);
    }

    /**
     * Scope a query to only include IN logs.
     */
    public function scopeTimeIn($query)
    {
        return $query->where('log_type', 'IN');
    }

    /**
     * Scope a query to only include OUT logs.
     */
    public function scopeTimeOut($query)
    {
        return $query->where('log_type', 'OUT');
    }

    /**
     * Scope to get logs for a specific employee.
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}
