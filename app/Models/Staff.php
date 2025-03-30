<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    // Define constants for staff types to ensure consistency
    const TYPE_ADMIN = 'admin';
    const TYPE_SPECIALIST_DOCTOR = 'specialist_doctor';
    const TYPE_MEDICAL_OFFICER = 'medical_officer';
    const TYPE_HOUSEMAN_OFFICER = 'houseman_officer';
    const TYPE_NURSE = 'nurse';

    protected $fillable = [
        'name',
        'email',
        'type',
        'department_id',
        'joining_date',
        'notes',
        'phone',
        'is_active',
        'specialization',
        // Specialist Doctor fields
        'qualification',
        // Houseman Officer fields
        'current_rotation',
        'graduation_year',
        // Nurse fields
        'nursing_unit',
        'nurse_level',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'is_active' => 'boolean',
        'graduation_year' => 'integer',
    ];

    /**
     * Get the user record associated with the staff
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            self::TYPE_ADMIN => 'Administrator',
            self::TYPE_SPECIALIST_DOCTOR => 'Specialist Doctor',
            self::TYPE_MEDICAL_OFFICER => 'Medical Officer',
            self::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer',
            self::TYPE_NURSE => 'Nurse',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the department that the staff belongs to
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the department staff type roster configuration for this staff
     */
    public function departmentStaffTypeRoster()
    {
        return $this->hasOneThrough(
            DepartmentStaffTypeRoster::class,
            Department::class,
            'id', // Foreign key on departments table
            'department_id', // Foreign key on department_staff_type_rosters table
            'department_id', // Local key on staff table
            'id' // Local key on departments table
        )->where('staff_type', $this->type);
    }

    /**
     * Get the roster entries for the staff member
     */
    public function rosterEntries()
    {
        return $this->hasMany(RosterEntry::class);
    }

    /**
     * Get the roster slots for the staff member
     */
    public function rosterSlots()
    {
        return $this->hasMany(RosterSlot::class);
    }

    /**
     * Get the rosters this staff member is assigned to
     */
    public function rosters()
    {
        return $this->belongsToMany(Roster::class, 'roster_entries')
            ->withPivot(['date', 'start_time', 'end_time', 'shift_type', 'notes', 'is_confirmed'])
            ->withTimestamps();
    }

    /**
     * Scope a query to only include staff of a specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Is this staff an administrator?
     */
    public function isAdmin()
    {
        return $this->type === self::TYPE_ADMIN;
    }

    /**
     * Is this staff a specialist doctor?
     */
    public function isSpecialistDoctor()
    {
        return $this->type === self::TYPE_SPECIALIST_DOCTOR;
    }

    /**
     * Is this staff a medical officer?
     */
    public function isMedicalOfficer()
    {
        return $this->type === self::TYPE_MEDICAL_OFFICER;
    }

    /**
     * Is this staff a houseman officer?
     */
    public function isHousemanOfficer()
    {
        return $this->type === self::TYPE_HOUSEMAN_OFFICER;
    }

    /**
     * Is this staff a nurse?
     */
    public function isNurse()
    {
        return $this->type === self::TYPE_NURSE;
    }

    /**
     * Get the formatted staff type
     */
    public function getStaffTypeLabelAttribute()
    {
        return match($this->type) {
            self::TYPE_ADMIN => 'Administrator',
            self::TYPE_SPECIALIST_DOCTOR => 'Specialist Doctor',
            self::TYPE_MEDICAL_OFFICER => 'Medical Officer',
            self::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer',
            self::TYPE_NURSE => 'Nurse',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the formatted specialization display
     */
    public function getSpecializationDisplayAttribute()
    {
        return match($this->type) {
            self::TYPE_SPECIALIST_DOCTOR => $this->specialization,
            self::TYPE_MEDICAL_OFFICER => $this->specialization,
            self::TYPE_HOUSEMAN_OFFICER => $this->current_rotation,
            self::TYPE_NURSE => $this->nursing_unit,
            default => $this->specialization,
        };
    }

    /**
     * Get the secondary info display for specialization
     */
    public function getSpecializationSecondaryAttribute()
    {
        return match($this->type) {
            self::TYPE_SPECIALIST_DOCTOR => $this->qualification,
            self::TYPE_MEDICAL_OFFICER => null,
            self::TYPE_HOUSEMAN_OFFICER => "Grad: " . $this->graduation_year,
            self::TYPE_NURSE => "Level: " . $this->nurse_level,
            default => null,
        };
    }
}
