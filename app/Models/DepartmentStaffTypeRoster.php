<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentStaffTypeRoster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'staff_type',
        'roster_type',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the department that owns this staff type roster setting
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get rosters of this type for this department and staff type
     */
    public function rosters()
    {
        return $this->hasMany(Roster::class, 'department_id', 'department_id')
            ->where('staff_type', $this->staff_type)
            ->where('roster_type', $this->roster_type);
    }

    /**
     * Get staff of this type in this department
     */
    public function staff()
    {
        return $this->hasMany(Staff::class, 'department_id', 'department_id')
            ->where('type', $this->staff_type);
    }

    /**
     * Get the formatted staff type label
     */
    public function getStaffTypeLabelAttribute()
    {
        return match($this->staff_type) {
            'specialist_doctor' => 'Specialist Doctor',
            'medical_officer' => 'Medical Officer',
            'houseman_officer' => 'Houseman Officer',
            'nurse' => 'Nurse',
            default => ucfirst($this->staff_type),
        };
    }

    /**
     * Get the formatted roster type label
     */
    public function getRosterTypeLabelAttribute()
    {
        return match($this->roster_type) {
            'oncall' => 'On Call',
            'shift' => 'Shift',
            default => ucfirst($this->roster_type),
        };
    }
}
