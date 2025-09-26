<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->account_type === 'admin' || $this->account_type === 'hr';
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
        'password' => 'hashed',
    ];
}