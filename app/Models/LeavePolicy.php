<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeavePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'leave_type',
        'annual_entitlement',
        'max_consecutive_days',
        'requires_approval',
        'min_notice_days',
        'allow_carry_forward',
        'max_carry_forward_days',
        'carry_forward_expiry_months',
        'description',
        'applicable_roles',
        'applicable_departments',
        'is_active',
    ];

    protected $casts = [
        'annual_entitlement' => 'decimal:2',
        'requires_approval' => 'boolean',
        'allow_carry_forward' => 'boolean',
        'is_active' => 'boolean',
        'applicable_roles' => 'array',
        'applicable_departments' => 'array',
        'max_consecutive_days' => 'integer',
        'min_notice_days' => 'integer',
        'max_carry_forward_days' => 'integer',
        'carry_forward_expiry_months' => 'integer',
    ];

    /**
     * Scope to get only active policies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get policies for a specific leave type.
     */
    public function scopeForLeaveType($query, string $leaveType)
    {
        return $query->where('leave_type', $leaveType);
    }

    /**
     * Check if policy applies to a specific role.
     */
    public function appliesToRole(string $role): bool
    {
        if (empty($this->applicable_roles)) {
            return true; // If no specific roles defined, applies to all
        }
        
        return in_array($role, $this->applicable_roles);
    }

    /**
     * Check if policy applies to a specific department.
     */
    public function appliesToDepartment(string $department): bool
    {
        if (empty($this->applicable_departments)) {
            return true; // If no specific departments defined, applies to all
        }
        
        return in_array($department, $this->applicable_departments);
    }

    /**
     * Get the formatted leave type name.
     */
    public function getFormattedLeaveTypeAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->leave_type));
    }

    /**
     * Get leave policies applicable to a user.
     */
    public static function getApplicablePolicies(User $user)
    {
        $employee = $user->employee;
        
        return static::active()
            ->get()
            ->filter(function ($policy) use ($employee) {
                $appliesToRole = $policy->appliesToRole($user->position ?? '');
                $appliesToDepartment = $policy->appliesToDepartment($employee->department ?? '');
                
                return $appliesToRole && $appliesToDepartment;
            });
    }
}
