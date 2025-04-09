<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'staff_type',
        'department_id',
        'start_date',
        'end_date',
        'description',
        'is_published',
        'roster_type',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_published' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the department that owns the roster
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the department staff type roster configuration that this roster belongs to
     */
    public function departmentStaffTypeRoster()
    {
        return $this->belongsTo(DepartmentStaffTypeRoster::class, 'department_id', 'department_id')
            ->where('staff_type', $this->staff_type)
            ->where('roster_type', $this->roster_type);
    }

    /**
     * Get the slots for the roster
     */
    public function slots()
    {
        return $this->hasMany(RosterSlot::class);
    }

    /**
     * Get the entries for the roster
     */
    public function entries()
    {
        return $this->hasMany(RosterSlot::class)->withCasts([
            'date' => 'date:Y-m-d',
        ]);
    }

    /**
     * Get staff assigned to this roster via slots
     */
    public function staff()
    {
        return $this->hasManyThrough(
            Staff::class,
            RosterSlot::class,
            'roster_id', // Foreign key on roster_slots table
            'id', // Foreign key on staff table
            'id', // Local key on rosters table
            'staff_id' // Local key on roster_slots table
        );
    }

    /**
     * Scope a query to only include rosters of a specific staff type
     */
    public function scopeOfStaffType($query, $staffType)
    {
        return $query->where('staff_type', $staffType);
    }

    /**
     * Scope a query to only include rosters of a specific roster type
     */
    public function scopeOfRosterType($query, $rosterType)
    {
        return $query->where('roster_type', $rosterType);
    }

    /**
     * Get the formatted staff type
     */
    public function getStaffTypeLabelAttribute()
    {
        $types = [
            'specialist_doctor' => 'Specialist Doctor',
            'medical_officer' => 'Medical Officer',
            'houseman_officer' => 'Houseman Officer',
            'nurse' => 'Nurse',
        ];
        
        return $types[$this->staff_type] ?? ucfirst(str_replace('_', ' ', $this->staff_type));
    }

    /**
     * Get the formatted roster type
     */
    public function getRosterTypeLabelAttribute()
    {
        $types = [
            'oncall' => 'On Call',
            'shift' => 'Shift',
            'sortable' => 'Sortable',
        ];
        
        return $types[$this->roster_type] ?? ucfirst($this->roster_type);
    }
}
