<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'roster_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     * Register model events to automatically initialize staff type roster settings.
     */
    protected static function boot()
    {
        parent::boot();
        
        // When a new department is created, initialize staff type roster settings
        static::created(function ($department) {
            $department->initializeStaffTypeRosters();
        });
    }
    
    /**
     * Initialize default staff type roster settings for this department
     */
    public function initializeStaffTypeRosters()
    {
        // Default staff types
        $staffTypes = [
            'specialist_doctor',
            'medical_officer',
            'houseman_officer',
            'nurse',
        ];
        
        // Default roster type assignments as per requirements
        $defaultRosterTypes = [
            'specialist_doctor' => 'oncall', // Specialists typically use on-call rosters
            'medical_officer' => 'oncall',   // Medical officers use on-call rosters
            'houseman_officer' => 'shift',   // Housemen typically use shift-based rosters
            'nurse' => 'shift',              // Nurses typically use shift-based rosters
        ];
        
        // Default settings for each staff type
        $defaultSettings = [
            'specialist_doctor' => [
                'allow_leave_requests' => true,
                'require_approval' => true,
                'notification_days' => 7,
            ],
            'medical_officer' => [
                'allow_leave_requests' => true,
                'require_approval' => true,
                'notification_days' => 7,
            ],
            'houseman_officer' => [
                'allow_leave_requests' => true,
                'require_approval' => true,
                'notification_days' => 14,
            ],
            'nurse' => [
                'allow_leave_requests' => true,
                'require_approval' => true,
                'notification_days' => 14,
            ],
        ];
        
        foreach ($staffTypes as $staffType) {
            // Create staff type roster setting if it doesn't exist
            DepartmentStaffTypeRoster::firstOrCreate(
                [
                    'department_id' => $this->id,
                    'staff_type' => $staffType,
                ],
                [
                    'roster_type' => $defaultRosterTypes[$staffType],
                    'settings' => $defaultSettings[$staffType],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Get a formatted label for the roster type
     */
    public function getRosterTypeLabelAttribute()
    {
        return match($this->roster_type) {
            'oncall' => 'On Call',
            'shift' => 'Shift',
            default => ucfirst($this->roster_type),
        };
    }

    /**
     * Get all staff in the department
     */
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Get staff of a specific type in this department
     */
    public function staffOfType($type)
    {
        return $this->staff()->where('type', $type);
    }

    /**
     * Get specialist doctors in this department
     */
    public function specialistDoctors()
    {
        return $this->staffOfType('specialist_doctor');
    }

    /**
     * Get medical officers in this department
     */
    public function medicalOfficers()
    {
        return $this->staffOfType('medical_officer');
    }

    /**
     * Get houseman officers in this department
     */
    public function housemanOfficers()
    {
        return $this->staffOfType('houseman_officer');
    }

    /**
     * Get nurses in this department
     */
    public function nurses()
    {
        return $this->staffOfType('nurse');
    }

    /**
     * Get the shift settings for the department
     */
    public function shiftSettings()
    {
        return $this->hasMany(RosterShiftSetting::class);
    }

    /**
     * Get the staff type roster settings for the department
     */
    public function staffTypeRosters()
    {
        return $this->hasMany(DepartmentStaffTypeRoster::class);
    }

    /**
     * Get rosters for a specific staff type in this department
     */
    public function rostersForStaffType($staffType, $rosterType = null)
    {
        $query = $this->hasMany(Roster::class)->where('staff_type', $staffType);
        
        if ($rosterType !== null) {
            $query->where('roster_type', $rosterType);
        }
        
        return $query;
    }
}
