<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roster;
use App\Models\Staff;
use App\Models\Department;
use App\Models\TeamLeader;
use App\Models\Holiday;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class StaffRosterViewController extends Controller
{
    /**
     * Display a listing of published rosters for the staff member
     */
    public function index()
    {
        $user = auth()->user();
        $staff = $user->staff;
        
        if (!$staff) {
            return redirect()->route('dashboard')->with('error', 'You do not have staff account.');
        }
        
        // Ensure staff has department relationship loaded
        if ($staff && !$staff->relationLoaded('department')) {
            $staff->load('department');
        }
        
        // Check if user is a department leader
        $isDepartmentLeader = false;
        $departmentName = null;
        $teamLeader = \App\Models\TeamLeader::where('staff_id', $staff->id)
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            })->first();
        
        if ($teamLeader) {
            $isDepartmentLeader = true;
            // Get department name from the department relationship
            if ($teamLeader->department) {
                $departmentName = $teamLeader->department->name;
            } else {
                // Fetch department if not loaded
                $department = \App\Models\Department::find($teamLeader->department_id);
                $departmentName = $department ? $department->name : null;
            }
        } else {
            // For regular staff, get department name from staff relationship
            if (is_object($staff->department)) {
                $departmentName = $staff->department->name;
            } elseif ($staff->department_id) {
                // If we only have department_id but not the relationship loaded
                $department = \App\Models\Department::find($staff->department_id);
                $departmentName = $department ? $department->name : 'Not Assigned';
            } else {
                $departmentName = 'Not Assigned';
            }
        }
        
        $query = Roster::query()
            ->where('is_published', true)
            ->where('department_id', $teamLeader->department_id ?? $staff->department_id);
        
        // Apply staff type filter based on leader type
        if ($isDepartmentLeader) {
            switch ($staff->type) {
                case 'specialist_doctor':
                    // Specialist doctor leader can view all staff types
                    break;
                case 'medical_officer':
                    // Medical officer leader can view medical officer, houseman officer, and nurse
                    $query->whereIn('staff_type', ['medical_officer', 'houseman_officer', 'nurse']);
                    break;
                case 'houseman_officer':
                    // Houseman officer leader can view houseman officer only
                    $query->where('staff_type', 'houseman_officer');
                    break;
                case 'nurse':
                    // Nurse leader can view nurse only
                    $query->where('staff_type', 'nurse');
                    break;
            }
        } else {
            // Regular staff can only view sortable-rosters for their staff type
            $query->where('staff_type', $staff->type);
        }
        
        $rosters = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('staff.rosters.index', compact('rosters', 'staff', 'isDepartmentLeader', 'departmentName'));
    }

    /**
     * Display the specified roster.
     */
    public function show(Roster $roster)
    {
        // Set no-cache headers for proper navigation
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        
        $user = auth()->user();
        $staff = $user->staff;
        
        if (!$staff) {
            return redirect()->route('dashboard')->with('error', 'You do not have staff account.');
        }
        
        // Ensure staff has department relationship loaded
        if ($staff && !$staff->relationLoaded('department')) {
            $staff->load('department');
        }
        
        // Check if user is a department leader
        $isDepartmentLeader = false;
        $departmentName = null;
        $teamLeader = \App\Models\TeamLeader::where('staff_id', $staff->id)
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now()->format('Y-m-d'));
            })->first();
        
        if ($teamLeader) {
            $isDepartmentLeader = true;
            // Get department name from the department relationship
            if ($teamLeader->department) {
                $departmentName = $teamLeader->department->name;
            } else {
                // Fetch department if not loaded
                $department = \App\Models\Department::find($teamLeader->department_id);
                $departmentName = $department ? $department->name : null;
            }
        } else {
            // For regular staff, get department name from staff relationship
            if (is_object($staff->department)) {
                $departmentName = $staff->department->name;
            } elseif ($staff->department_id) {
                // If we only have department_id but not the relationship loaded
                $department = \App\Models\Department::find($staff->department_id);
                $departmentName = $department ? $department->name : 'Not Assigned';
            } else {
                $departmentName = 'Not Assigned';
            }
        }
        
        // Check view permission
        $canView = false;
        
        // Check department
        if ($roster->department_id != $staff->department_id) {
            return redirect()->route('staff.rosters.index')
                ->with('error', 'You can only view sortable-rosters from your department.');
        }
        
        if (!$roster->is_published) {
            return redirect()->route('staff.rosters.index')
                ->with('error', 'You can only view published sortable-rosters.');
        }
        
        // Apply staff type restrictions based on leader type
        if ($isDepartmentLeader) {
            switch ($staff->type) {
                case 'specialist_doctor':
                    // Specialist doctor leader can view all staff types
                    $canView = true;
                    break;
                case 'medical_officer':
                    // Medical officer leader can view medical officer, houseman officer, and nurse
                    $canView = in_array($roster->staff_type, ['medical_officer', 'houseman_officer', 'nurse']);
                    break;
                case 'houseman_officer':
                    // Houseman officer leader can view houseman officer only
                    $canView = $roster->staff_type === 'houseman_officer';
                    break;
                case 'nurse':
                    // Nurse leader can view nurse only
                    $canView = $roster->staff_type === 'nurse';
                    break;
                default:
                    $canView = false;
            }
        } else {
            // Regular staff can only view sortable-rosters for their staff type
            $canView = $roster->staff_type === $staff->type;
        }
        
        if (!$canView) {
            return redirect()->route('staff.rosters.index')
                ->with('error', 'You do not have permission to view this sortable-roster.');
        }

        // Load slots with the staff
        $roster->load(['slots.staff']);
        
        // Get current week (from URL parameter or default to first week)
        $currentWeekStart = request()->query('week_start') 
            ? Carbon::parse(request()->query('week_start')) 
            : $roster->start_date->copy();
        
        // Ensure currentWeekStart is not before startDate
        if ($currentWeekStart->lt($roster->start_date)) {
            $currentWeekStart = $roster->start_date->copy();
        }
        
        // Calculate end of current week (6 days after start = 7 day week)
        $currentWeekEnd = $currentWeekStart->copy()->addDays(6);
        
        // If week would extend past the roster end date, cap it
        if ($currentWeekEnd->gt($roster->end_date)) {
            $currentWeekEnd = $roster->end_date->copy();
        }
        
        // Fetch active holidays that fall within the current week's date range
        $holidays = Holiday::where('status', 'active')
            ->where(function($query) use ($currentWeekStart, $currentWeekEnd) {
                // Get non-recurring holidays with exact date match
                $query->where('is_recurring', false)
                      ->whereBetween('date', [$currentWeekStart->format('Y-m-d'), $currentWeekEnd->format('Y-m-d')]);
                
                // Or get recurring holidays
                $query->orWhere(function($q) use ($currentWeekStart, $currentWeekEnd) {
                    $q->where('is_recurring', true);
                    
                    // Create a date range to check
                    $checkRange = [];
                    $checkDate = $currentWeekStart->copy();
                    while ($checkDate->lte($currentWeekEnd)) {
                        $checkRange[] = $checkDate->format('m-d');
                        $checkDate->addDay();
                    }
                    
                    // Use whereRaw to check month-day combinations
                    $q->whereRaw("DATE_FORMAT(date, '%m-%d') IN ('" . implode("','", $checkRange) . "')");
                });
            })
            ->get();
        
        // Create a date-indexed array of holidays
        $holidaysByDate = [];
        foreach ($holidays as $holiday) {
            if ($holiday->is_recurring) {
                // For recurring holidays, create date keys for all days in the range that match month-day
                $checkDate = $currentWeekStart->copy();
                while ($checkDate->lte($currentWeekEnd)) {
                    if (
                        $checkDate->format('m') == $holiday->date->format('m') && 
                        $checkDate->format('d') == $holiday->date->format('d')
                    ) {
                        $dateKey = $checkDate->format('Y-m-d');
                        if (!isset($holidaysByDate[$dateKey])) {
                            $holidaysByDate[$dateKey] = [];
                        }
                        $holidaysByDate[$dateKey][] = $holiday;
                    }
                    $checkDate->addDay();
                }
            } else {
                // For non-recurring holidays, just use the actual date
                $dateKey = $holiday->date->format('Y-m-d');
                if (!isset($holidaysByDate[$dateKey])) {
                    $holidaysByDate[$dateKey] = [];
                }
                $holidaysByDate[$dateKey][] = $holiday;
            }
        }
        
        return view('staff.rosters.show', compact('roster', 'staff', 'isDepartmentLeader', 'departmentName', 'holidaysByDate'));
    }
}
