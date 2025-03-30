<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RosterShiftSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'staff_type',
        'shift_type',
        'roster_type',
        'start_time',
        'end_time',
        'name',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the department that owns this shift setting
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the department staff type roster configuration for this shift setting
     */
    public function departmentStaffTypeRoster()
    {
        return $this->belongsTo(DepartmentStaffTypeRoster::class, 'department_id', 'department_id')
            ->where('staff_type', $this->staff_type)
            ->where('roster_type', $this->roster_type);
    }

    /**
     * Scope to get shift settings for a specific staff type
     */
    public function scopeForStaffType($query, $staffType)
    {
        return $query->where('staff_type', $staffType);
    }

    /**
     * Scope to get shift settings for a specific roster type
     */
    public function scopeForRosterType($query, $rosterType)
    {
        return $query->where('roster_type', $rosterType);
    }

    /**
     * Get the formatted shift type
     */
    public function getShiftTypeLabelAttribute()
    {
        return match($this->shift_type) {
            'morning' => 'Morning Shift',
            'evening' => 'Evening Shift',
            'night' => 'Night Shift',
            'oncall' => 'On Call',
            'standby' => 'Standby',
            default => ucfirst($this->shift_type),
        };
    }

    /**
     * Get the formatted staff type label
     */
    public function getStaffTypeLabelAttribute()
    {
        return match($this->staff_type) {
            Staff::TYPE_SPECIALIST_DOCTOR => 'Specialist Doctor',
            Staff::TYPE_MEDICAL_OFFICER => 'Medical Officer',
            Staff::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer',
            Staff::TYPE_NURSE => 'Nurse',
            default => ucfirst($this->staff_type ?? ''),
        };
    }
}
