<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftTemplate extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'days',
        'schedule_type',
        'selected_dates',
        'department',
        'description',
        'status',
        'assigned_employees_count'
    ];

    protected $casts = [
        'days' => 'array',
        'selected_dates' => 'array'
    ];

    /**
     * Get the formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return $this->start_time . ' - ' . $this->end_time;
    }

    /**
     * Get formatted working days
     */
    public function getFormattedDaysAttribute()
    {
        // Handle date-based schedules
        if ($this->schedule_type === 'dates' && !empty($this->selected_dates)) {
            $dateCount = count($this->selected_dates);
            return $dateCount . ' specific date' . ($dateCount > 1 ? 's' : '') . ' selected';
        }
        
        // Handle weekly schedules
        if (empty($this->days)) {
            return '';
        }
        
        $dayMap = [
            'Monday' => 'Mon',
            'Tuesday' => 'Tue',
            'Wednesday' => 'Wed',
            'Thursday' => 'Thu',
            'Friday' => 'Fri',
            'Saturday' => 'Sat',
            'Sunday' => 'Sun'
        ];
        
        $shortDays = array_map(fn($day) => $dayMap[$day] ?? $day, $this->days);
        
        // Check for common patterns
        if (count($this->days) === 5 && 
            array_diff($this->days, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']) === []) {
            return 'Mon - Fri';
        }
        
        if (count($this->days) === 7) {
            return 'All Days';
        }
        
        return implode(', ', $shortDays);
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for specific department
     */
    public function scopeForDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Get the shift assignments for this template.
     */
    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }
}
