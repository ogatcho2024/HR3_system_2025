<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

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
        'night_diff_minutes',
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
        'night_diff_minutes' => 'integer',
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
     * Get the employee record linked by user_id.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
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

        return self::calculateHoursFromTimes(
            $this->clock_in_time,
            $this->clock_out_time,
            $this->break_start,
            $this->break_end
        );
    }

    /**
     * Calculate hours worked from raw time strings (24h safe).
     */
    public static function calculateHoursFromTimes(
        ?string $clockIn,
        ?string $clockOut,
        ?string $breakStart = null,
        ?string $breakEnd = null
    ): float {
        if (empty($clockIn) || empty($clockOut)) {
            return 0.0;
        }

        $totalMinutes = self::diffMinutes($clockIn, $clockOut);

        if (!empty($breakStart) && !empty($breakEnd)) {
            $breakMinutes = self::diffMinutes($breakStart, $breakEnd);
            $totalMinutes -= $breakMinutes;
        }

        $totalMinutes = max(0, $totalMinutes);
        return round($totalMinutes / 60, 2);
    }

    private static function diffMinutes(string $start, string $end): int
    {
        $startTime = self::parseTimeValue($start);
        $endTime = self::parseTimeValue($end);

        if ($endTime->lessThan($startTime)) {
            $endTime->addDay();
        }

        $seconds = $endTime->getTimestamp() - $startTime->getTimestamp();
        return (int) round($seconds / 60);
    }

    private static function parseTimeValue(string $value): Carbon
    {
        $format = substr_count($value, ':') === 2 ? 'H:i:s' : 'H:i';
        return Carbon::createFromFormat($format, $value);
    }

    /**
     * Normalize time-only value to H:i or H:i:s string.
     */
    public static function normalizeTimeValue($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i:s');
        }

        $raw = trim((string) $value);

        // Extract time part from full datetime or ISO strings
        if (preg_match('/(\d{2}:\d{2}(?::\d{2})?)/', $raw, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function getStatusColumnNameStatic(): string
    {
        return Schema::hasColumn('timesheets', 'Status') ? 'Status' : 'status';
    }

    private function getStatusColumnName(): string
    {
        return self::getStatusColumnNameStatic();
    }

    public function getStatusAttribute($value): ?string
    {
        if ($value !== null) {
            return $value;
        }

        return $this->attributes['Status'] ?? null;
    }

    public function setStatusAttribute($value): void
    {
        $column = $this->getStatusColumnName();
        $this->attributes[$column] = $value;
    }

    /**
     * Scope a query to only include draft timesheets.
     */
    public function scopeDraft($query)
    {
        $column = (new self())->getStatusColumnName();
        return $query->where($column, 'draft');
    }

    /**
     * Scope a query to only include submitted timesheets.
     */
    public function scopeSubmitted($query)
    {
        $column = (new self())->getStatusColumnName();
        return $query->where($column, 'submitted');
    }

    /**
     * Scope a query to only include approved timesheets.
     */
    public function scopeApproved($query)
    {
        $column = (new self())->getStatusColumnName();
        return $query->where($column, 'approved');
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
