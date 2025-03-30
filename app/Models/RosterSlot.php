<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class RosterSlot extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'roster_id',
        'staff_id',
        'date',
        'shift_type',
        'start_time',
        'end_time',
        'is_confirmed',
        'notes',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_confirmed' => 'boolean',
    ];
    
    /**
     * Get the roster that owns the slot.
     */
    public function roster()
    {
        return $this->belongsTo(Roster::class);
    }
    
    /**
     * Get the staff assigned to this slot.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
    
    /**
     * Get the department that this roster slot belongs to (via the roster)
     */
    public function department()
    {
        return $this->roster->department();
    }
    
    /**
     * Scope to filter slots by staff type
     */
    public function scopeForStaffType($query, $staffType)
    {
        return $query->whereHas('staff', function($q) use ($staffType) {
            $q->where('type', $staffType);
        });
    }

    /**
     * Scope to filter slots by department
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->whereHas('roster', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    /**
     * Scope to filter slots by roster type
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
        $this->attributes['date'] = $value;
    }

    /**
     * Get the shift_date attribute.
     * This is an accessor that returns the date field for compatibility with frontend code.
     */
    public function getShiftDateAttribute()
    {
        return $this->date;
    }
    
    /**
     * Get the formatted shift type label.
     *
     * @return string
     */
    public function getShiftTypeLabelAttribute()
    {
        $rosterType = $this->roster->roster_type ?? 'shift';
        
        if ($rosterType === 'shift') {
            return match($this->shift_type) {
                'morning' => 'Morning Shift',
                'evening' => 'Evening Shift',
                'night' => 'Night Shift',
                default => ucfirst($this->shift_type),
            };
        } else {
            return match($this->shift_type) {
                'oncall' => 'First Oncall',
                'standby' => 'Second Oncall',
                default => ucfirst($this->shift_type),
            };
        }
    }
}
