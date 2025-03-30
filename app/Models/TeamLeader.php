<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamLeader extends Model
{
    use SoftDeletes;
    
    // Define constants for leader types
    const TYPE_TEAM_LEADER = 'team_leader';
    const TYPE_MEDICAL_OFFICER_LEADER = 'medical_officer_leader';
    const TYPE_SPECIALIST_DOCTOR_LEADER = 'specialist_doctor_leader';
    const TYPE_HOUSEMAN_OFFICER_LEADER = 'houseman_officer_leader'; 
    const TYPE_NURSE_LEADER = 'nurse_leader';
    
    protected $fillable = [
        'staff_id',
        'department_id',
        'start_date',
        'end_date',
        'notes'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    
    /**
     * Get the staff member associated with this leader
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
    
    /**
     * Get the department this leader belongs to
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    /**
     * Get the formatted leader type label
     */
    public function getLeaderTypeLabelAttribute()
    {
        if (!$this->staff) {
            return 'Unknown';
        }
        
        return match($this->staff->type) {
            Staff::TYPE_SPECIALIST_DOCTOR => 'Specialist Doctor Leader',
            Staff::TYPE_MEDICAL_OFFICER => 'Medical Officer Leader',
            Staff::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer Leader',
            Staff::TYPE_NURSE => 'Nurse Leader',
            Staff::TYPE_ADMIN => 'Team Leader',
            default => ucfirst(str_replace('_', ' ', $this->staff->type)) . ' Leader',
        };
    }
    
    /**
     * Scope a query to only include active leaders
     */
    public function scopeActive($query)
    {
        return $query->whereNull('end_date')->orWhere('end_date', '>=', now());
    }
}
