<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Account type constants
    public const ACCOUNT_TYPE_SUPER_ADMIN = 'Super admin';
    public const ACCOUNT_TYPE_ADMIN = 'Admin';
    public const ACCOUNT_TYPE_STAFF = 'Staff';
    public const ACCOUNT_TYPE_EMPLOYEE = 'Employee';

    /**
     * Get the employee record associated with the user.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get the leave requests for the user.
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the leave balances for the user.
     */
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get the timesheets for the user.
     */
    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    /**
     * Get the shift requests for the user.
     */
    public function shiftRequests()
    {
        return $this->hasMany(ShiftRequest::class);
    }

    /**
     * Get the attendance records for the user.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the alerts created by the user.
     */
    public function createdAlerts()
    {
        return $this->hasMany(Alert::class, 'created_by');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the reimbursements for the user.
     */
    public function reimbursements()
    {
        return $this->hasMany(Reimbursement::class);
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->account_type === self::ACCOUNT_TYPE_SUPER_ADMIN;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->account_type === self::ACCOUNT_TYPE_ADMIN || $this->isSuperAdmin();
    }

    /**
     * Check if user is staff.
     */
    public function isStaff(): bool
    {
        return $this->account_type === self::ACCOUNT_TYPE_STAFF;
    }

    /**
     * Check if user is employee.
     */
    public function isEmployee(): bool
    {
        return $this->account_type === self::ACCOUNT_TYPE_EMPLOYEE;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'photo',
        'account_type',
        'otp_status',
        'otp_code',
        'otp_expires_at',
        'otp_verified',
        'require_2fa',
        'phone',
        'position',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_status' => 'boolean',
        'otp_verified' => 'boolean',
        'require_2fa' => 'boolean',
        'otp_expires_at' => 'datetime',
        'password' => 'hashed',
    ];
}
