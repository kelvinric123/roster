<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Roster;
use App\Models\Department;
use App\Models\RosterEntry;
use App\Models\RosterSlot;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RosterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample rosters
        $this->createSampleRosters();
    }
    
    private function createSampleRosters(): void
    {
        $departments = Department::all();
        $staffTypes = ['specialist_doctor', 'medical_officer', 'houseman_officer', 'nurse'];
        $rosterTypes = ['shift', 'oncall'];
        
        // Create at least 1 roster for each department and staff type
        foreach ($departments as $department) {
            foreach ($staffTypes as $staffType) {
                // Determine if this department supports this staff type
                $departmentStaffTypeRoster = DB::table('department_staff_type_rosters')
                    ->where('department_id', $department->id)
                    ->where('staff_type', $staffType)
                    ->where('is_active', true)
                    ->first();
                    
                if (!$departmentStaffTypeRoster) {
                    continue; // Skip if this department doesn't support this staff type
                }
                
                // Choose a roster type based on department_staff_type_roster
                $rosterType = $departmentStaffTypeRoster->roster_type ?? ($staffType === 'specialist_doctor' ? 'oncall' : 'shift');
                
                // Create the roster
                $startDate = Carbon::now()->subDays(rand(0, 10));
                $endDate = $startDate->copy()->addDays(rand(20, 30));
                
                $roster = Roster::create([
                    'name' => $department->name . ' ' . ucwords(str_replace('_', ' ', $staffType)) . ' Roster',
                    'department_id' => $department->id,
                    'staff_type' => $staffType,
                    'roster_type' => $rosterType,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_published' => true,
                ]);
                
                // Create sample entries for this roster
                $this->createSampleEntries($roster);
            }
        }
    }
    
    private function createSampleEntries(Roster $roster): void
    {
        // Get staff of the appropriate type in this department
        $staff = Staff::where('type', $roster->staff_type)
                     ->where('department_id', $roster->department_id)
                     ->get();
        
        // If no staff found, use any staff of this type (for testing)
        if ($staff->isEmpty()) {
            $staff = Staff::where('type', $roster->staff_type)->get();
        }
        
        // If still no staff, skip this roster
        if ($staff->isEmpty()) {
            return;
        }
        
        // Define shift types and times
        $shiftTypes = [
            'morning' => ['08:00:00', '16:00:00'],
            'evening' => ['16:00:00', '00:00:00'],
            'night' => ['00:00:00', '08:00:00'],
            'oncall' => ['17:00:00', '08:00:00'],
            'standby' => ['17:00:00', '08:00:00'],
        ];
        
        // Add entries for each day in the roster
        $currentDate = $roster->start_date->copy();
        $endDate = $roster->end_date->copy();
        
        while ($currentDate <= $endDate) {
            // Determine which shift types to create based on roster type
            if ($roster->roster_type === 'shift') {
                $shiftTypesToCreate = ['morning', 'evening', 'night'];
            } else {
                $shiftTypesToCreate = ['oncall', 'standby'];
            }
            
            // For newer rosters, create RosterSlots
            if ($currentDate->isAfter(Carbon::now()->subDays(7))) {
                foreach ($shiftTypesToCreate as $shiftType) {
                    // Assign a random staff member to this shift
                    $assignedStaff = $staff->random();
                    
                    RosterSlot::create([
                        'roster_id' => $roster->id,
                        'staff_id' => $assignedStaff->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'start_time' => $shiftTypes[$shiftType][0],
                        'end_time' => $shiftTypes[$shiftType][1],
                        'shift_type' => $shiftType,
                        'notes' => $shiftType === 'night' || $shiftType === 'oncall' ? 'On-call duty' : null,
                        'is_confirmed' => rand(0, 1) === 1,
                    ]);
                }
            } else {
                // For older rosters, use RosterEntry (legacy)
                foreach ($shiftTypesToCreate as $shiftType) {
                    // Assign a random staff member to this shift
                    $assignedStaff = $staff->random();
                    
                    RosterEntry::create([
                        'roster_id' => $roster->id,
                        'staff_id' => $assignedStaff->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'start_time' => $shiftTypes[$shiftType][0],
                        'end_time' => $shiftTypes[$shiftType][1],
                        'shift_type' => $shiftType,
                        'notes' => $shiftType === 'night' || $shiftType === 'oncall' ? 'On-call duty' : null,
                        'is_confirmed' => rand(0, 1) === 1,
                    ]);
                }
            }
            
            $currentDate->addDay();
        }
    }
}
