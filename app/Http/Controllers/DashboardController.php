<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Staff;
use App\Models\Roster;
use App\Models\RosterEntry;
use App\Models\RosterSlot;
use App\Models\TeamLeader;
use App\Models\Holiday;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics
     */
    public function index()
    {
        $user = Auth::user();
        
        // Load staff and department for the user
        if ($user && $user->role !== 'admin') {
            $user->load('staff.department');
        }
        
        // Check if user is a department leader
        $isDepartmentLeader = false;
        $departmentId = null;
        $teamLeaderData = null;
        $departmentName = null;
        $staffTypeLabel = null;
        $teamStats = null;
        
        if ($user && $user->staff_id && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            // Find the team leader entry for this user
            $teamLeader = TeamLeader::where('staff_id', $user->staff_id)
                ->where(function($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->with('department')  // Eager load department
                ->first();
                
            if ($teamLeader) {
                $isDepartmentLeader = true;
                $departmentId = $teamLeader->department_id;
                $teamLeaderData = $teamLeader;
                $departmentName = $teamLeader->department->name ?? 'Department';
                
                // Get staff type label
                $staffTypeLabels = [
                    'specialist_doctor' => 'Specialist Doctor',
                    'medical_officer' => 'Medical Officer',
                    'houseman_officer' => 'Houseman Officer',
                    'nurse' => 'Nurse'
                ];
                
                $staffType = $user->staff->type ?? null;
                $staffTypeLabel = $staffTypeLabels[$staffType] ?? ucfirst(str_replace('_', ' ', $staffType));
                
                // Calculate team statistics for team leader
                $teamStats = $this->calculateTeamStats($departmentId, $staffType);
            }
        }
        
        // Calculate staff shift statistics
        $staffShiftStats = null;
        
        if ($user && $user->staff_id) {
            $staffId = $user->staff_id;
            
            // Get all roster entries for this staff
            $rosterEntries = RosterEntry::where('staff_id', $staffId)->get();
            
            // Get all roster slots for this staff
            $rosterSlots = RosterSlot::where('staff_id', $staffId)->get();
            
            // Calculate total shifts
            $totalShifts = $rosterEntries->count() + $rosterSlots->count();
            
            // Calculate on-call shifts
            $oncallEntries = $rosterEntries->filter(function($entry) {
                return $entry->shift_type === 'oncall' || $entry->shift_type === 'standby';
            })->count();
            
            $oncallSlots = $rosterSlots->filter(function($slot) {
                return $slot->shift_type === 'oncall' || $slot->shift_type === 'standby';
            })->count();
            
            $oncallShifts = $oncallEntries + $oncallSlots;
            
            // Get all holidays
            $holidays = Holiday::pluck('date')->map(function($date) {
                return $date->format('Y-m-d');
            })->toArray();
            
            // Calculate weekend shifts
            $weekendEntries = $rosterEntries->filter(function($entry) {
                $date = Carbon::parse($entry->date);
                return $date->isWeekend();
            })->count();
            
            $weekendSlots = $rosterSlots->filter(function($slot) {
                $date = Carbon::parse($slot->date);
                return $date->isWeekend();
            })->count();
            
            $weekendShifts = $weekendEntries + $weekendSlots;
            
            // Calculate holiday shifts
            $holidayEntries = $rosterEntries->filter(function($entry) use ($holidays) {
                return in_array($entry->date, $holidays);
            })->count();
            
            $holidaySlots = $rosterSlots->filter(function($slot) use ($holidays) {
                return in_array($slot->date, $holidays);
            })->count();
            
            $holidayShifts = $holidayEntries + $holidaySlots;
            
            // Calculate weekday shifts (excluding holidays)
            $weekdayEntries = $rosterEntries->filter(function($entry) use ($holidays) {
                $date = Carbon::parse($entry->date);
                return !$date->isWeekend() && !in_array($entry->date, $holidays);
            })->count();
            
            $weekdaySlots = $rosterSlots->filter(function($slot) use ($holidays) {
                $date = Carbon::parse($slot->date);
                return !$date->isWeekend() && !in_array($slot->date, $holidays);
            })->count();
            
            $weekdayShifts = $weekdayEntries + $weekdaySlots;
            
            $staffShiftStats = [
                'totalShifts' => $totalShifts,
                'oncallShifts' => $oncallShifts,
                'weekendShifts' => $weekendShifts,
                'weekdayShifts' => $weekdayShifts,
                'holidayShifts' => $holidayShifts,
            ];
        }
        
        // Get department stats - filtered for department leaders
        $departments = Department::with('staff')->get();
        
        // Mock announcements for the notice board
        $announcements = [
            [
                'title' => 'New Roster System Launched',
                'content' => 'The new roster system is now live. Please check your schedules and report any issues.',
                'date' => '2023-03-15',
                'type' => 'success'
            ],
            [
                'title' => 'Upcoming Holiday Schedule',
                'content' => 'Please submit your holiday preferences by the end of the month.',
                'date' => '2023-03-10',
                'type' => 'important'
            ],
            [
                'title' => 'Department Meeting',
                'content' => 'There will be a department meeting on Friday at 2 PM.',
                'date' => '2023-03-05',
                'type' => 'event'
            ]
        ];
        
        return view('dashboard', compact(
            'departments', 
            'announcements', 
            'isDepartmentLeader',
            'staffShiftStats',
            'departmentName',
            'staffTypeLabel',
            'teamStats'
        ));
    }
    
    /**
     * Calculate team statistics for a department leader
     */
    private function calculateTeamStats($departmentId, $staffType)
    {
        // Get all staff of this type in this department
        $teamMembers = Staff::where('department_id', $departmentId);
        
        // Filter by staff type based on leader type
        if ($staffType === 'specialist_doctor') {
            // Specialist doctor leader can view all staff types
            $teamMembers = $teamMembers->get();
        } elseif ($staffType === 'medical_officer') {
            // Medical officer leader can view medical officer, houseman officer, and nurse
            $teamMembers = $teamMembers->whereIn('type', ['medical_officer', 'houseman_officer', 'nurse'])->get();
        } elseif ($staffType === 'houseman_officer') {
            // Houseman officer leader can view houseman officer only
            $teamMembers = $teamMembers->where('type', 'houseman_officer')->get();
        } elseif ($staffType === 'nurse') {
            // Nurse leader can view nurse only
            $teamMembers = $teamMembers->where('type', 'nurse')->get();
        } else {
            // Default fallback
            $teamMembers = $teamMembers->get();
        }
        
        $teamCount = $teamMembers->count();
        $staffIds = $teamMembers->pluck('id')->toArray();
        
        // Get all entries and slots for the team
        $entries = RosterEntry::whereIn('staff_id', $staffIds)->get();
        $slots = RosterSlot::whereIn('staff_id', $staffIds)->get();
        
        // Get all holidays
        $holidays = Holiday::pluck('date')->map(function($date) {
            return $date->format('Y-m-d');
        })->toArray();
        
        // Calculate total shifts
        $totalShifts = $entries->count() + $slots->count();
        
        // Calculate on-call shifts
        $oncallEntries = $entries->filter(function($entry) {
            return $entry->shift_type === 'oncall' || $entry->shift_type === 'standby';
        })->count();
        
        $oncallSlots = $slots->filter(function($slot) {
            return $slot->shift_type === 'oncall' || $slot->shift_type === 'standby';
        })->count();
        
        $oncallShifts = $oncallEntries + $oncallSlots;
        
        // Calculate weekend shifts
        $weekendEntries = $entries->filter(function($entry) {
            $date = Carbon::parse($entry->date);
            return $date->isWeekend();
        })->count();
        
        $weekendSlots = $slots->filter(function($slot) {
            $date = Carbon::parse($slot->date);
            return $date->isWeekend();
        })->count();
        
        $weekendShifts = $weekendEntries + $weekendSlots;
        
        // Calculate holiday shifts
        $holidayEntries = $entries->filter(function($entry) use ($holidays) {
            return in_array($entry->date, $holidays);
        })->count();
        
        $holidaySlots = $slots->filter(function($slot) use ($holidays) {
            return in_array($slot->date, $holidays);
        })->count();
        
        $holidayShifts = $holidayEntries + $holidaySlots;
        
        // Calculate weekday shifts (excluding holidays)
        $weekdayEntries = $entries->filter(function($entry) use ($holidays) {
            $date = Carbon::parse($entry->date);
            return !$date->isWeekend() && !in_array($entry->date, $holidays);
        })->count();
        
        $weekdaySlots = $slots->filter(function($slot) use ($holidays) {
            $date = Carbon::parse($slot->date);
            return !$date->isWeekend() && !in_array($slot->date, $holidays);
        })->count();
        
        $weekdayShifts = $weekdayEntries + $weekdaySlots;
        
        // Calculate weekend/holiday shifts combined
        $weekendHolidayShifts = $weekendShifts + $holidayShifts;
        
        // Get top staff with most shifts
        $staffShiftCounts = [];
        
        foreach ($entries as $entry) {
            if (!isset($staffShiftCounts[$entry->staff_id])) {
                $staffShiftCounts[$entry->staff_id] = 0;
            }
            $staffShiftCounts[$entry->staff_id]++;
        }
        
        foreach ($slots as $slot) {
            if (!isset($staffShiftCounts[$slot->staff_id])) {
                $staffShiftCounts[$slot->staff_id] = 0;
            }
            $staffShiftCounts[$slot->staff_id]++;
        }
        
        // Sort by count (descending)
        arsort($staffShiftCounts);
        
        // Take top 5
        $topStaffIds = array_slice(array_keys($staffShiftCounts), 0, 5);
        $topStaff = [];
        $maxShifts = !empty($staffShiftCounts) ? max($staffShiftCounts) : 0;
        
        foreach ($topStaffIds as $staffId) {
            $staffMember = $teamMembers->firstWhere('id', $staffId);
            if ($staffMember) {
                $topStaff[] = [
                    'name' => $staffMember->name,
                    'shifts' => $staffShiftCounts[$staffId]
                ];
            }
        }
        
        return [
            'teamCount' => $teamCount,
            'totalShifts' => $totalShifts,
            'oncallShifts' => $oncallShifts,
            'weekendShifts' => $weekendShifts,
            'holidayShifts' => $holidayShifts,
            'weekdayShifts' => $weekdayShifts,
            'weekendHolidayShifts' => $weekendHolidayShifts,
            'topStaff' => $topStaff,
            'maxShifts' => $maxShifts
        ];
    }
}
