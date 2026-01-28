<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'external_id',
        'qr_secret',
        'department',
        'position',
        'manager_name',
        'hire_date',
        'salary',
        'employment_type',
        'work_location',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
    ];

    /**
     * Get the user that owns the employee record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the leave requests for the employee.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'user_id', 'user_id');
    }

    /**
     * Get the timesheets for the employee.
     */
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'user_id', 'user_id');
    }

    /**
     * Get the attendance records for the employee through user.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'user_id');
    }

    /**
     * Get the shift assignments for the employee.
     */
    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }
    
    /**
     * Get the sync record for this employee.
     */
    public function syncRecord()
    {
        return $this->hasOne(EmployeeSync::class);
    }

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user->name . ' ' . $this->user->lastname;
    }

    /**
     * Scope a query to only include active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    /**
     * Scope a query to find employees by external ID.
     */
    public function scopeByExternalId($query, $externalId)
    {
        return $query->where('external_id', $externalId);
    }
    
    /**
     * Check if employee is synced from external source.
     */
    public function isExternallyManaged(): bool
    {
        return !empty($this->external_id);
    }
    
    /**
     * Get the last sync timestamp for this employee.
     */
    public function getLastSyncAttribute()
    {
        return $this->syncRecord ? $this->syncRecord->last_sync_at : null;
    }
    
    /**
     * Get the QR attendance logs for this employee.
     */
    public function qrAttendanceLogs(): HasMany
    {
        return $this->hasMany(QrAttendanceLog::class);
    }
    
    /**
     * Boot the model and add event listeners.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Auto-generate qr_secret when creating a new employee
        static::creating(function ($employee) {
            if (empty($employee->qr_secret)) {
                $employee->qr_secret = hash('sha256', Str::random(32));
            }
        });
    }
    
    /**
     * Generate today's daily QR token.
     * Uses HMAC-SHA256 with the employee's secret and current date.
     * 
     * @return string
     */
    public function generateDailyQrToken(): string
    {
        if (empty($this->qr_secret)) {
            $this->qr_secret = hash('sha256', Str::random(32));
            $this->save();
        }
        
        $currentDate = date('Y-m-d');
        return hash_hmac('sha256', $currentDate, $this->qr_secret);
    }
    
    /**
     * Verify if a given token matches today's expected token.
     * 
     * @param string $token
     * @return bool
     */
    public function verifyDailyToken(string $token): bool
    {
        $expectedToken = $this->generateDailyQrToken();
        return hash_equals($expectedToken, $token);
    }
}
