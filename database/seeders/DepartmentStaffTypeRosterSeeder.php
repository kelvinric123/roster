<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\DepartmentStaffTypeRoster;
use App\Models\Staff;

class DepartmentStaffTypeRosterSeeder extends Seeder
{
    /**
     * Run the database seeds to initialize department staff type roster settings.
     * This creates settings for each of the 4 staff types in all departments,
     * with each staff type having its own roster type configuration.
     */
    public function run(): void
    {
        $departments = Department::all();
        $staffTypes = [
            'specialist_doctor',
            'medical_officer',
            'houseman_officer',
            'nurse',
        ];
        
        // Default roster type assignments (can be customized)
        $defaultRosterTypes = [
            'specialist_doctor' => 'oncall', // Specialists typically use on-call rosters
            'medical_officer' => 'oncall',   // Medical officers may use on-call rosters
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
        
        $count = 0;
        
        foreach ($departments as $department) {
            $this->command->info("Processing department: {$department->name}");
            
            foreach ($staffTypes as $staffType) {
                // Check if setting already exists
                $exists = DepartmentStaffTypeRoster::where('department_id', $department->id)
                    ->where('staff_type', $staffType)
                    ->exists();
                    
                if (!$exists) {
                    // Each staff type gets its own roster type configuration
                    DepartmentStaffTypeRoster::create([
                        'department_id' => $department->id,
                        'staff_type' => $staffType,
                        'roster_type' => $defaultRosterTypes[$staffType],
                        'settings' => $defaultSettings[$staffType],
                        'is_active' => true,
                    ]);
                    $count++;
                    $this->command->info("  Created {$staffType} roster type setting.");
                } else {
                    $this->command->info("  {$staffType} roster type setting already exists.");
                }
            }
        }
        
        $this->command->info("Created {$count} staff type roster settings across departments.");
        $this->command->info("Each department now has 4 distinct staff types with their own roster types.");
    }
}
