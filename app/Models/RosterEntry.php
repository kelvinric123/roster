<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RosterEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'roster_id',
        'staff_id',
        'date',
        'shift_date',
        'start_time',
        'end_time',
        'shift_type',
        'notes',
        'is_confirmed',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_confirmed' => 'boolean',
    ];

    /**
     * Get the roster that owns the entry
     */
    public function roster()
    {
        return $this->belongsTo(Roster::class);
    }

    /**
     * Get the staff assigned to this entry
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the department that this roster entry belongs to (via the roster)
     */
    public function department()
    {
        return $this->roster->department();
    }

    /**
     * Scope to filter entries by staff type
     */
    public function scopeForStaffType($query, $staffType)
    {
        return $query->whereHas('staff', function($q) use ($staffType) {
            $q->where('type', $staffType);
        });
    }

    /**
     * Scope to filter entries by department
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->whereHas('roster', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    /**
     * Scope to filter entries by roster type
     */
    public function scopeWithRosterType($query, $rosterType)
    {
        return $query->whereHas('roster', function($q) use ($rosterType) {
            $q->where('roster_type', $rosterType);
        });
    }

    /**
     * Set the shift_date attribute.
     * This is a mutator that ensures both date and shift_date fields are in sync.
     */
    public function setShiftDateAttribute($value)
    {
        $this->attributes['shift_date'] = $value;
        $this->attributes['date'] = $value;
    }

    /**
     * Get the shift_date attribute.
     * This is an accessor that returns the date field when shift_date is requested.
     */
    public function getShiftDateAttribute()
    {
        return $this->date;
    }

    /**
     * Get the formatted shift type label
     */
    public function getShiftTypeLabelAttribute()
    {
        switch ($this->shift_type) {
            case 'morning':
                return 'Morning Shift';
            case 'evening':
                return 'Evening Shift';
            case 'night':
                return 'Night Shift';
            case 'oncall':
                return 'First Oncall';
            case 'standby':
                return 'Second Oncall';
            default:
                return $this->shift_type;
        }
    }
}
