<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roster;
use App\Models\Department;
use App\Models\Staff;
use App\Models\Holiday;
use Illuminate\Support\Facades\DB;
use App\Models\DepartmentStaffTypeRoster;
use App\Models\RosterEntry;
use App\Models\RosterShiftSetting;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\RosterSlot;

class RosterController extends Controller
{
    /**
     * Display a listing of the rosters.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isDepartmentLeader = false;
        $departmentName = null;
        $departmentId = null;
        
        // Check if user is a department leader
        $teamLeader = \App\Models\TeamLeader::where('staff_id', $user->staff_id)
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now()->format('Y-m-d'));
            })->first();
            
        if ($teamLeader) {
            $isDepartmentLeader = true;
            $departmentId = $teamLeader->department_id;
            
            // Get department name from the department relationship
            if ($teamLeader->department) {
                $departmentName = $teamLeader->department->name;
            } else {
                // Fetch department if not loaded
                $department = \App\Models\Department::find($teamLeader->department_id);
                $departmentName = $department ? $department->name : null;
            }
        }
        
        // Build the roster query
        $query = Roster::with('department');
        
        // Filter by staff type if requested
        if ($request->has('type') && !empty($request->type)) {
            $query->where('staff_type', $request->type);
        }
        
        // Department leader can only see rosters for their department
        if ($isDepartmentLeader && $departmentId) {
            $query->where('department_id', $departmentId);
            
            // Apply staff type restrictions based on leader type
            if ($user->staff) {
                $staffType = $user->staff->type;
                
                switch ($staffType) {
                    case 'specialist_doctor':
                        // Specialist doctor leader can see all staff types
                        break;
                    case 'medical_officer':
                        // Medical officer leader can see medical officer, houseman officer, and nurse
                        $query->whereIn('staff_type', ['medical_officer', 'houseman_officer', 'nurse']);
                        break;
                    case 'houseman_officer':
                        // Houseman officer leader can see houseman officer only
                        $query->where('staff_type', 'houseman_officer');
                        break;
                    case 'nurse':
                        // Nurse leader can see nurse only
                        $query->where('staff_type', 'nurse');
                        break;
                }
            }
        }
        
        // Fetch the rosters
        $rosters = $query->latest()->paginate(10);
        
        return view('rosters.index', compact('rosters', 'isDepartmentLeader', 'departmentName'));
    }

    /**
     * Show the form for creating a new roster.
     */
    public function create()
    {
        $user = auth()->user();
        $isDepartmentLeader = false;
        $departmentId = null;
        
        // Check if user is a department leader
        if ($user && $user->staff_id && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            // Find the team leader entry for this user
            $teamLeader = \App\Models\TeamLeader::where('staff_id', $user->staff_id)
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', now())
                ->first();
                
            if ($teamLeader) {
                $isDepartmentLeader = true;
                $departmentId = $teamLeader->department_id;
                
                // Department leaders can only select their own department
                $departments = Department::where('id', $departmentId)->where('is_active', true)->get();
            } else {
                $departments = Department::where('is_active', true)->get();
            }
        } else {
            // Admin and regular staff can select any department
            $departments = Department::where('is_active', true)->get();
        }
        
        $staffTypes = [
            Staff::TYPE_SPECIALIST_DOCTOR => 'Specialist Doctor',
            Staff::TYPE_MEDICAL_OFFICER => 'Medical Officer',
            Staff::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer',
            Staff::TYPE_NURSE => 'Nurse',
        ];
        
        // Filter staff types based on leader role
        if ($isDepartmentLeader && $user->staff_id) {
            $staffType = $user->staff->type;
            
            switch ($staffType) {
                case Staff::TYPE_SPECIALIST_DOCTOR:
                    // Specialist leader can create roster for all staff types
                    // No filtering needed
                    break;
                    
                case Staff::TYPE_MEDICAL_OFFICER:
                    // Medical officer leader can create for medical officer, house officer, and nurse
                    // Remove specialist doctor from options
                    unset($staffTypes[Staff::TYPE_SPECIALIST_DOCTOR]);
                    break;
                    
                case Staff::TYPE_HOUSEMAN_OFFICER:
                    // House officer leader can create for house officer only
                    $staffTypes = [
                        Staff::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer'
                    ];
                    break;
                    
                case Staff::TYPE_NURSE:
                    // Nurse leader can create for nurse only
                    $staffTypes = [
                        Staff::TYPE_NURSE => 'Nurse'
                    ];
                    break;
            }
        }
        
        $rosterTypes = [
            'oncall' => 'On Call',
            'shift' => 'Shift',
        ];
        
        // Get staff type roster configurations for all departments - ensure it's fully loaded
        if ($isDepartmentLeader && $departmentId) {
            // Only get the leader's department
            $departmentsWithRosterTypes = Department::where('id', $departmentId)
                ->with(['staffTypeRosters' => function($query) {
                    $query->where('is_active', true);
                }])->get()
                ->map(function($department) {
                    // Convert to array of staff type rosters instead of keying by staff_type
                    $staffTypeRosters = $department->staffTypeRosters->toArray();
                    
                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                        'staff_type_rosters' => $staffTypeRosters,
                    ];
                });
        } else {
            // Get all departments for admin and regular staff
            $departmentsWithRosterTypes = Department::with(['staffTypeRosters' => function($query) {
                $query->where('is_active', true);
            }])->get()
                ->map(function($department) {
                    // Convert to array of staff type rosters instead of keying by staff_type
                    $staffTypeRosters = $department->staffTypeRosters->toArray();
                    
                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                        'staff_type_rosters' => $staffTypeRosters,
                    ];
                });
        }
        
        return view('rosters.create', compact('departments', 'staffTypes', 'rosterTypes', 'departmentsWithRosterTypes', 'isDepartmentLeader', 'departmentId'));
    }

    /**
     * Store a newly created roster in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'staff_type' => 'required|string|in:specialist_doctor,medical_officer,houseman_officer,nurse',
        ]);

        // Check if user is a department leader
        $user = auth()->user();
        if ($user && $user->staff_id && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            // Find the team leader entry for this user
            $teamLeader = \App\Models\TeamLeader::where('staff_id', $user->staff_id)
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', now())
                ->first();
                
            // If team leader is found, apply restrictions
            if ($teamLeader) {
                // Ensure department leader can only create rosters for their own department
                if ($teamLeader->department_id != $validated['department_id']) {
                    return redirect()->route('manage.rosters.index')
                        ->with('error', 'You can only create rosters for your own department.');
                }
                
                // Apply staff type restrictions based on leader role
                // Get the associated staff's type
                $staffType = $user->staff->type;
                
                // Check if leader can create roster for the selected staff type
                $canCreate = false;
                
                switch ($staffType) {
                    case Staff::TYPE_SPECIALIST_DOCTOR:
                        // Specialist leader can create roster for all staff types
                        $canCreate = true;
                        break;
                        
                    case Staff::TYPE_MEDICAL_OFFICER:
                        // Medical officer leader can create for medical officer, house officer, and nurse
                        $canCreate = in_array($validated['staff_type'], [
                            Staff::TYPE_MEDICAL_OFFICER,
                            Staff::TYPE_HOUSEMAN_OFFICER,
                            Staff::TYPE_NURSE
                        ]);
                        break;
                        
                    case Staff::TYPE_HOUSEMAN_OFFICER:
                        // House officer leader can create for house officer only
                        $canCreate = $validated['staff_type'] === Staff::TYPE_HOUSEMAN_OFFICER;
                        break;
                        
                    case Staff::TYPE_NURSE:
                        // Nurse leader can create for nurse only
                        $canCreate = $validated['staff_type'] === Staff::TYPE_NURSE;
                        break;
                        
                    default:
                        $canCreate = false;
                }
                
                if (!$canCreate) {
                    return redirect()->route('manage.rosters.index')
                        ->with('error', 'You do not have permission to create rosters for this staff type.');
                }
            }
        }

        // Get the department
        $department = Department::findOrFail($validated['department_id']);
        $staffType = $validated['staff_type'];
        
        // Find the roster type setting for this staff type in the department
        $staffTypeRoster = $department->staffTypeRosters()
            ->where('staff_type', $staffType)
            ->first();
            
        if (!$staffTypeRoster) {
            // Initialize roster settings for this department if they don't exist
            $department->initializeStaffTypeRosters();
            
            // Get the newly created staff type roster
            $staffTypeRoster = $department->staffTypeRosters()
                ->where('staff_type', $staffType)
                ->first();
        }
        
        if ($staffTypeRoster) {
            // Use the specific roster type for this staff type in this department
            $validated['roster_type'] = $staffTypeRoster->roster_type;
        } else {
            // This shouldn't happen with the new structure, but keeping as fallback
            // Use default roster types based on staff type
            $validated['roster_type'] = match($staffType) {
                'specialist_doctor', 'medical_officer' => 'oncall',
                'houseman_officer', 'nurse' => 'shift',
                default => 'shift',
            };
        }

        $validated['is_published'] = false;
        $validated['created_by'] = auth()->id();

        $roster = Roster::create($validated);

        // Redirect to the rosters index page instead of showing the newly created roster
        return redirect()->route('manage.rosters.index')
            ->with('success', 'Roster created successfully.');
    }

    /**
     * Display the specified roster.
     */
    public function show(Roster $roster)
    {
        $user = auth()->user();
        $isDepartmentLeader = false;
        $departmentName = null;
        
        // Add debugging info
        \Log::info('Showing roster', [
            'roster_id' => $roster->id,
            'roster_name' => $roster->name,
            'user_id' => $user->id,
            'start_date' => $roster->start_date->format('Y-m-d'),
            'end_date' => $roster->end_date->format('Y-m-d'),
        ]);
        
        // Check if user is a department leader
        $teamLeader = \App\Models\TeamLeader::where('staff_id', $user->staff_id)
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now()->format('Y-m-d'));
            })->first();
            
        if ($teamLeader) {
            $isDepartmentLeader = true;
            $departmentId = $teamLeader->department_id;
            
            // Get department name from the department relationship
            if ($teamLeader->department) {
                $departmentName = $teamLeader->department->name;
            } else {
                // Fetch department if not loaded
                $department = \App\Models\Department::find($teamLeader->department_id);
                $departmentName = $department ? $department->name : null;
            }
            
            // Check if the roster belongs to the leader's department
            if ($roster->department_id != $departmentId) {
                return redirect()->route('manage.rosters.index')
                    ->with('error', 'You can only view rosters from your department.');
            }
            
            // Check staff type restrictions
            $canView = false;
            $staffType = $user->staff->type;
            
            switch ($staffType) {
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
            
            if (!$canView) {
                return redirect()->route('manage.rosters.index')
                    ->with('error', 'You do not have permission to view this roster.');
            }
        }
        
        // Load roster with department and related data
        $roster->load(['department', 'entries.staff', 'slots.staff']);
        
        // Debug loaded entries
        \Log::info('Roster entries and slots loading complete', [
            'entries_count' => $roster->entries->count(),
            'slots_count' => $roster->slots->count(),
        ]);
        
        // Debug detailed information about slots
        if ($roster->slots->count() > 0) {
            $slotsDebug = $roster->slots->take(5)->map(function($slot) {
                return [
                    'id' => $slot->id,
                    'staff_id' => $slot->staff_id,
                    'staff_name' => $slot->staff ? $slot->staff->name : 'Unknown',
                    'date' => $slot->date ? $slot->date->format('Y-m-d') : null,
                    'shift_type' => $slot->shift_type,
                    'roster_id' => $slot->roster_id,
                ];
            })->toArray();
            \Log::info('Sample slots:', $slotsDebug);
            
            // Check specifically for April 8, 2025
            $april8slots = $roster->slots->filter(function($slot) {
                return $slot->date && $slot->date->format('Y-m-d') === '2025-04-08';
            })->values()->map(function($slot) {
                return [
                    'id' => $slot->id,
                    'staff_id' => $slot->staff_id,
                    'staff_name' => $slot->staff ? $slot->staff->name : 'Unknown',
                    'date' => $slot->date ? $slot->date->format('Y-m-d') : null,
                    'shift_type' => $slot->shift_type,
                ];
            })->toArray();
            
            \Log::info('April 8, 2025 slots:', [
                'count' => count($april8slots),
                'slots' => $april8slots
            ]);
        }
        
        // Get date range for the roster
        $startDate = $roster->start_date->copy();
        $endDate = $roster->end_date->copy();

        // Fetch active holidays that fall within the roster's date range
        $holidays = Holiday::where('status', 'active')
            ->where(function($query) use ($startDate, $endDate) {
                // Get non-recurring holidays with exact date match
                $query->where('is_recurring', false)
                      ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                
                // Or get recurring holidays
                $query->orWhere(function($q) use ($startDate, $endDate) {
                    $q->where('is_recurring', true);
                    
                    // Create a date range to check
                    $checkRange = [];
                    $checkDate = $startDate->copy();
                    while ($checkDate->lte($endDate)) {
                        $checkRange[] = $checkDate->format('m-d');
                        $checkDate->addDay();
                    }
                    
                    // Use whereRaw to check month-day combinations
                    if (!empty($checkRange)) {
                        $q->whereRaw("DATE_FORMAT(date, '%m-%d') IN ('" . implode("','", $checkRange) . "')");
                    }
                });
            })
            ->get();

        // Create a date-indexed array of holidays
        $holidaysByDate = [];
        foreach ($holidays as $holiday) {
            if ($holiday->is_recurring) {
                // For recurring holidays, create date keys for all days in the range that match month-day
                $checkDate = $startDate->copy();
                while ($checkDate->lte($endDate)) {
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
        
        // Log loaded data
        \Log::info('Roster data loaded', [
            'roster_id' => $roster->id,
            'entries_count' => $roster->entries->count(),
            'slots_count' => $roster->slots->count()
        ]);
        
        // Get the first few entries and slots for debugging
        if ($roster->entries->count() > 0) {
            \Log::info('Sample entries:', $roster->entries->take(3)->toArray());
        }
        
        if ($roster->slots->count() > 0) {
            \Log::info('Sample slots:', $roster->slots->take(3)->toArray());
        }
        
        // Get available staff for roster
        $availableStaff = Staff::where('department_id', $roster->department_id)
            ->where('type', $roster->staff_type)
            ->where('is_active', true)
            ->get();
        
        // Combine both entries and slots
        $combinedEntries = collect();
        
        // Add slots first (newer data)
        if ($roster->slots->count() > 0) {
            $combinedEntries = $combinedEntries->concat($roster->slots);
        }
        
        // Add entries that don't have corresponding slots
        if ($roster->entries->count() > 0) {
            $existingSlots = $combinedEntries->map(function($slot) {
                return $slot->date->format('Y-m-d') . '_' . $slot->staff_id . '_' . $slot->shift_type;
            })->toArray();
            
            $uniqueEntries = $roster->entries->filter(function($entry) use ($existingSlots) {
                $entryKey = $entry->date->format('Y-m-d') . '_' . $entry->staff_id . '_' . $entry->shift_type;
                return !in_array($entryKey, $existingSlots);
            });
            
            if ($uniqueEntries->count() > 0) {
                $combinedEntries = $combinedEntries->concat($uniqueEntries);
            }
        }
        
        // Log combined entries count
        \Log::info('Combined entries for display', [
            'combined_count' => $combinedEntries->count(),
            'first_few' => $combinedEntries->take(3)->toArray()
        ]);
        
        // For slots-only approach
        // $entries = $roster->slots;
        // For combined approach  
        $entries = $combinedEntries;
        
        // Organize entries by date for the calendar view
        $entriesByDate = [];
        foreach ($entries as $entry) {
            $dateKey = $entry->date->format('Y-m-d');
            if (!isset($entriesByDate[$dateKey])) {
                $entriesByDate[$dateKey] = [];
            }
            $entriesByDate[$dateKey][] = $entry;
        }
        
        \Log::info('Roster show - data count', [
            'roster_id' => $roster->id,
            'slots_count' => $roster->slots->count(),
            'entries_count' => $roster->entries->count(),
            'available_staff' => $availableStaff->count()
        ]);
        
        return view('rosters.show', compact('roster', 'isDepartmentLeader', 'departmentName', 'availableStaff', 'entries', 'entriesByDate', 'holidaysByDate'));
    }

    /**
     * Show the form for editing the specified roster.
     */
    public function edit(Roster $roster)
    {
        $user = auth()->user();
        $isDepartmentLeader = false;
        $departmentName = null;
        $departmentId = null;
        
        // Check if user is a department leader
        $teamLeader = \App\Models\TeamLeader::where('staff_id', $user->staff_id)
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now()->format('Y-m-d'));
            })->first();
            
        if ($teamLeader) {
            $isDepartmentLeader = true;
            $departmentId = $teamLeader->department_id;
            
            // Get department name from the department relationship
            if ($teamLeader->department) {
                $departmentName = $teamLeader->department->name;
            } else {
                // Fetch department if not loaded
                $department = \App\Models\Department::find($teamLeader->department_id);
                $departmentName = $department ? $department->name : null;
            }
            
            // Check if the roster belongs to the leader's department
            if ($roster->department_id != $departmentId) {
                return redirect()->route('manage.rosters.index')
                    ->with('error', 'You can only edit rosters from your department.');
            }
            
            // Check staff type restrictions
            $canEdit = false;
            $staffType = $user->staff->type;
            
            switch ($staffType) {
                case 'specialist_doctor':
                    // Specialist doctor leader can edit all staff types
                    $canEdit = true;
                    break;
                case 'medical_officer':
                    // Medical officer leader can edit medical officer, houseman officer, and nurse
                    $canEdit = in_array($roster->staff_type, ['medical_officer', 'houseman_officer', 'nurse']);
                    break;
                case 'houseman_officer':
                    // Houseman officer leader can edit houseman officer only
                    $canEdit = $roster->staff_type === 'houseman_officer';
                    break;
                case 'nurse':
                    // Nurse leader can edit nurse only
                    $canEdit = $roster->staff_type === 'nurse';
                    break;
                default:
                    $canEdit = false;
            }
            
            if (!$canEdit) {
                return redirect()->route('manage.rosters.index')
                    ->with('error', 'You do not have permission to edit this roster.');
            }
            
            // Department leaders can only select their own department
            $departments = Department::where('id', $departmentId)->where('is_active', true)->get();
        } else {
            // Admin and regular staff can select any department
            $departments = Department::where('is_active', true)->get();
        }
        
        $staffTypes = [
            Staff::TYPE_SPECIALIST_DOCTOR => 'Specialist Doctor',
            Staff::TYPE_MEDICAL_OFFICER => 'Medical Officer',
            Staff::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer',
            Staff::TYPE_NURSE => 'Nurse',
        ];
        
        // Filter staff types based on leader role
        if ($isDepartmentLeader && $user->staff_id) {
            $staffType = $user->staff->type;
            
            switch ($staffType) {
                case 'specialist_doctor':
                    // Specialist leader can create roster for all staff types
                    // No filtering needed
                    break;
                    
                case 'medical_officer':
                    // Medical officer leader can create for medical officer, house officer, and nurse
                    // Remove specialist doctor from options
                    unset($staffTypes[Staff::TYPE_SPECIALIST_DOCTOR]);
                    break;
                    
                case 'houseman_officer':
                    // House officer leader can create for house officer only
                    $staffTypes = [
                        Staff::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer'
                    ];
                    break;
                    
                case 'nurse':
                    // Nurse leader can create for nurse only
                    $staffTypes = [
                        Staff::TYPE_NURSE => 'Nurse'
                    ];
                    break;
            }
        }
        
        $rosterTypes = [
            'oncall' => 'On Call',
            'shift' => 'Shift',
        ];
        
        // Get staff type roster configurations for all departments
        if ($isDepartmentLeader && $departmentId) {
            // Only get the leader's department
            $departmentsWithRosterTypes = Department::where('id', $departmentId)
                ->with(['staffTypeRosters' => function($query) {
                    $query->where('is_active', true);
                }])->get()
                ->map(function($department) {
                    // Convert to array of staff type rosters instead of keying by staff_type
                    $staffTypeRosters = $department->staffTypeRosters->toArray();
                    
                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                        'staff_type_rosters' => $staffTypeRosters,
                    ];
                });
        } else {
            // Get all departments for admin and regular staff
            $departmentsWithRosterTypes = Department::with(['staffTypeRosters' => function($query) {
                $query->where('is_active', true);
            }])->get()
                ->map(function($department) {
                    // Convert to array of staff type rosters instead of keying by staff_type
                    $staffTypeRosters = $department->staffTypeRosters->toArray();
                    
                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                        'staff_type_rosters' => $staffTypeRosters,
                    ];
                });
        }
        
        return view('rosters.edit', compact('roster', 'departments', 'staffTypes', 'rosterTypes', 'departmentsWithRosterTypes', 'isDepartmentLeader', 'departmentName', 'departmentId'));
    }

    /**
     * Update the specified roster in storage.
     */
    public function update(Request $request, Roster $roster)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'staff_type' => 'required|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        // Check if user is a department leader
        $user = auth()->user();
        if ($user && $user->staff_id && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            // Find the team leader entry for this user
            $teamLeader = \App\Models\TeamLeader::where('staff_id', $user->staff_id)
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', now())
                ->first();
                
            // Ensure department leader can only update rosters for their own department
            if ($teamLeader) {
                // Check if they're trying to change department
                if ($validated['department_id'] != $teamLeader->department_id) {
                    return redirect()->route('manage.rosters.index')
                        ->with('error', 'You cannot change the department to one you do not lead.');
                }
                
                // Check if this roster belongs to their department
                if ($roster->department_id != $teamLeader->department_id) {
                    return redirect()->route('manage.rosters.index')
                        ->with('error', 'You can only update rosters for your own department.');
                }
                
                // Apply staff type restrictions based on leader role
                // Get the associated staff's type
                $staffType = $user->staff->type;
                
                // Check if they're trying to change staff type and if they have permission
                if ($roster->staff_type != $validated['staff_type']) {
                    $canUpdate = false;
                    
                    switch ($staffType) {
                        case Staff::TYPE_SPECIALIST_DOCTOR:
                            // Specialist leader can update roster for all staff types
                            $canUpdate = true;
                            break;
                            
                        case Staff::TYPE_MEDICAL_OFFICER:
                            // Medical officer leader can update for medical officer, house officer, and nurse
                            $canUpdate = in_array($validated['staff_type'], [
                                Staff::TYPE_MEDICAL_OFFICER,
                                Staff::TYPE_HOUSEMAN_OFFICER,
                                Staff::TYPE_NURSE
                            ]);
                            break;
                            
                        case Staff::TYPE_HOUSEMAN_OFFICER:
                            // House officer leader can update for house officer only
                            $canUpdate = $validated['staff_type'] === Staff::TYPE_HOUSEMAN_OFFICER;
                            break;
                            
                        case Staff::TYPE_NURSE:
                            // Nurse leader can update for nurse only
                            $canUpdate = $validated['staff_type'] === Staff::TYPE_NURSE;
                            break;
                            
                        default:
                            $canUpdate = false;
                    }
                    
                    if (!$canUpdate) {
                        return redirect()->route('manage.rosters.index')
                            ->with('error', 'You do not have permission to change the staff type for this roster.');
                    }
                }
            }
        }

        // Get the department
        $department = Department::findOrFail($validated['department_id']);
        $staffType = $validated['staff_type'];
        
        // Find the roster type setting for this staff type in the department
        $staffTypeRoster = $department->staffTypeRosters()
            ->where('staff_type', $staffType)
            ->first();
        
        if (!$staffTypeRoster) {
            // Initialize roster settings for this department if they don't exist
            $department->initializeStaffTypeRosters();
            
            // Get the newly created staff type roster
            $staffTypeRoster = $department->staffTypeRosters()
                ->where('staff_type', $staffType)
                ->first();
        }
            
        if ($staffTypeRoster) {
            // Use the specific roster type for this staff type in this department
            $validated['roster_type'] = $staffTypeRoster->roster_type;
        } else {
            // This shouldn't happen with the new structure, but keeping as fallback
            // Use default roster types based on staff type
            $validated['roster_type'] = match($staffType) {
                'specialist_doctor', 'medical_officer' => 'oncall',
                'houseman_officer', 'nurse' => 'shift',
                default => 'shift',
            };
        }

        // Update the roster
        $roster->update($validated);
        
        return redirect()->route('manage.rosters.index')
            ->with('success', 'Roster updated successfully.');
    }

    /**
     * Remove the specified roster from storage.
     */
    public function destroy(Roster $roster)
    {
        // Check if user is a department leader
        $user = auth()->user();
        $canDelete = true;
        
        if ($user && $user->staff_id && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            // Find the team leader entry for this user
            $teamLeader = \App\Models\TeamLeader::where('staff_id', $user->staff_id)
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', now())
                ->first();
                
            // Department leaders can only delete rosters for their own department
            if ($teamLeader && $teamLeader->department_id != $roster->department_id) {
                $canDelete = false;
            }
        }
        
        if (!$canDelete) {
            return redirect()->route('manage.rosters.index')
                ->with('error', 'You can only delete rosters for your own department.');
        }
        
        // Delete related entries first to avoid foreign key constraints
        RosterEntry::where('roster_id', $roster->id)->delete();
        
        $roster->delete();
        return redirect()->route('manage.rosters.index')
            ->with('success', 'Roster deleted successfully.');
    }

    /**
     * Display all rosters for a specific staff type.
     */
    public function byType($type)
    {
        $validTypes = ['specialist_doctor', 'medical_officer', 'houseman_officer', 'nurse'];
        
        if (!in_array($type, $validTypes)) {
            abort(404);
        }
        
        $rosters = Roster::with('department')
            ->where('staff_type', $type)
            ->latest()
            ->paginate(10);
            
        $staffTypeLabel = (new Roster(['staff_type' => $type]))->staff_type_label;
        
        return view('rosters.by_type', compact('rosters', 'type', 'staffTypeLabel'));
    }

    /**
     * Publish or unpublish a roster.
     */
    public function togglePublish(Roster $roster)
    {
        $roster->update([
            'is_published' => !$roster->is_published
        ]);
        
        $status = $roster->is_published ? 'published' : 'unpublished';
        
        return back()->with('success', "Roster {$status} successfully.");
    }

    /**
     * Store a new entry in the specified roster.
     */
    public function storeEntry(Request $request, Roster $roster)
    {
        // Load the roster's department to get shift settings
        $roster->load('department');
        
        $rules = [
            'staff_id' => 'required|exists:staff,id',
            'shift_date' => 'required|date',
            'notes' => 'nullable|string',
            'is_confirmed' => 'sometimes|boolean',
        ];
        
        // Different validation based on roster type
        if ($roster->roster_type === 'shift') {
            // For regular shift rosters
            $rules['shift_type'] = 'required|in:morning,evening,night';
            
            if ($roster->department) {
                $shiftSettings = RosterShiftSetting::where('department_id', $roster->department->id)
                    ->pluck('shift_type')
                    ->toArray();
                
                if (count($shiftSettings) > 0) {
                    $rules['shift_type'] = 'required|in:' . implode(',', $shiftSettings);
                }
            }
            
            // Validate and set times based on shift type
            if ($request->filled('shift_type') && $roster->department) {
                $shiftSetting = RosterShiftSetting::where('department_id', $roster->department->id)
                    ->where('shift_type', $request->shift_type)
                    ->first();
                
                if ($shiftSetting) {
                    // Get times from settings
                    $startTime = $shiftSetting->start_time;
                    $endTime = $shiftSetting->end_time;
                    
                    // Apply times automatically
                    $request->merge([
                        'start_time' => $startTime, 
                        'end_time' => $endTime
                    ]);
                }
            }
        } else {
            // For oncall rosters
            $rules['shift_type'] = 'required|in:oncall,standby';
        }
        
        // Add time validation
        $rules['start_time'] = 'required';
        $rules['end_time'] = 'required';
        
        $validatedData = $request->validate($rules);
        
        // Format the shift_date as Y-m-d
        if (isset($validatedData['shift_date'])) {
            $validatedData['shift_date'] = Carbon::parse($validatedData['shift_date'])->format('Y-m-d');
        }
        
        // Add roster_id to the data
        $validatedData['roster_id'] = $roster->id;
        
        // Set is_confirmed to false if not provided
        $validatedData['is_confirmed'] = $request->has('is_confirmed') ? (bool)$request->is_confirmed : false;
        
        RosterSlot::create($validatedData);
        
        return redirect()->route('manage.rosters.show', $roster)
            ->with('success', 'Shift added successfully.');
    }
    
    /**
     * Store a new slot in the specified roster.
     */
    public function storeSlot(Request $request, Roster $roster)
    {
        // Load the roster's department to get shift settings
        $roster->load('department');
        
        $rules = [
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'is_confirmed' => 'sometimes|boolean',
        ];
        
        // Different validation based on roster type
        if ($roster->roster_type === 'shift') {
            // For regular shift rosters
            $rules['shift_type'] = 'required|in:morning,evening,night';
            
            if ($roster->department) {
                $shiftSettings = RosterShiftSetting::where('department_id', $roster->department->id)
                    ->pluck('shift_type')
                    ->toArray();
                
                if (count($shiftSettings) > 0) {
                    $rules['shift_type'] = 'required|in:' . implode(',', $shiftSettings);
                }
            }
            
            // Validate and set times based on shift type
            if ($request->filled('shift_type') && $roster->department) {
                $shiftSetting = RosterShiftSetting::where('department_id', $roster->department->id)
                    ->where('shift_type', $request->shift_type)
                    ->first();
                
                if ($shiftSetting) {
                    // Get times from settings
                    $startTime = $shiftSetting->start_time;
                    $endTime = $shiftSetting->end_time;
                    
                    // Apply times automatically
                    $request->merge([
                        'start_time' => $startTime, 
                        'end_time' => $endTime
                    ]);
                }
            }
        } else {
            // For oncall rosters
            $rules['shift_type'] = 'required|in:oncall,standby';
        }
        
        // Add time validation
        $rules['start_time'] = 'required';
        $rules['end_time'] = 'required';
        
        $validatedData = $request->validate($rules);
        
        // Format the date as Y-m-d
        if (isset($validatedData['date'])) {
            $validatedData['date'] = Carbon::parse($validatedData['date'])->format('Y-m-d');
        }
        
        // Add roster_id to the data
        $validatedData['roster_id'] = $roster->id;
        
        // Set is_confirmed to false if not provided
        $validatedData['is_confirmed'] = $request->has('is_confirmed') ? (bool)$request->is_confirmed : false;
        
        RosterSlot::create($validatedData);
        
        return redirect()->route('manage.rosters.show', $roster)
            ->with('success', 'Shift added successfully.');
    }

    /**
     * Bulk store entries for a roster.
     */
    public function bulkStoreEntries(Request $request, Roster $roster)
    {
        $roster->load('department');
        
        // Log the incoming request
        \Log::info('bulkStoreEntries called', [
            'roster_id' => $roster->id,
            'request_type' => $request->isJson() ? 'JSON' : 'Form',
            'request_data' => $request->all()
        ]);
        
        // Validate the request data as JSON
        if ($request->isJson()) {
            // JSON request - for drag-and-drop interface
            try {
                $data = $request->json()->all();
                
                if (!isset($data['entries']) || !is_array($data['entries'])) {
                    return response()->json(['success' => false, 'message' => 'Invalid request format.'], 400);
                }
                
                $newEntries = [];
                $createdEntries = [];
                
                foreach ($data['entries'] as $entry) {
                    // Validate this entry
                    $validatedEntry = $this->validateRosterEntry($entry, $roster);
                    
                    if (!$validatedEntry) {
                        continue;
                    }
                    
                    // Handle date field (could be shift_date or date)
                    $shiftDate = $entry['shift_date'] ?? $entry['date'] ?? null;
                    
                    if (!$shiftDate) {
                        \Log::error('Invalid date in entry', $entry);
                        continue;
                    }
                    
                    \Log::info('Creating roster slot', [
                        'staff_id' => $validatedEntry['staff_id'],
                        'date' => $shiftDate,
                        'shift_type' => $validatedEntry['shift_type']
                    ]);
                    
                    // Create the roster slot
                    $newEntry = RosterSlot::updateOrCreate(
                        [
                            'roster_id' => $roster->id,
                            'staff_id' => $validatedEntry['staff_id'],
                            'date' => $shiftDate,
                            'shift_type' => $validatedEntry['shift_type'],
                        ],
                        [
                            'is_confirmed' => $validatedEntry['is_confirmed'] ?? false,
                            'start_time' => $entry['start_time'] ?? '00:00:00',
                            'end_time' => $entry['end_time'] ?? '23:59:59',
                            'notes' => $entry['notes'] ?? null,
                        ]
                    );
                    
                    \Log::info('Created slot', [
                        'id' => $newEntry->id,
                        'staff_id' => $newEntry->staff_id,
                        'date' => $newEntry->date,
                        'shift_type' => $newEntry->shift_type
                    ]);
                    
                    // Add to responses
                    $newEntries[] = [
                        'id' => $newEntry->id,
                        'temp_id' => $entry['id'] ?? null
                    ];
                    
                    // Load the staff relation for the response
                    $newEntry->load('staff');
                    $createdEntries[] = $newEntry->toArray();  // Convert to array to ensure full serialization
                }
                
                // Return success with new entry IDs mapped to temp IDs
                return response()->json([
                    'success' => true, 
                    'new_entries' => $newEntries,
                    'created_entries' => $createdEntries,
                    'message' => 'Entries saved successfully.'
                ]);
            } catch (\Exception $e) {
                \Log::error('Error processing entries: ' . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false, 
                    'message' => 'Error processing request: ' . $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        } else {
            // Form-based request - for bulk add form
            try {
                $rules = [
                    'staff_id' => 'required|exists:staff,id',
                    'dates' => 'required|array',
                    'dates.*' => 'required|date|between:' . $roster->start_date->format('Y-m-d') . ',' . $roster->end_date->format('Y-m-d'),
                    'start_time' => 'required',
                    'end_time' => 'required',
                    'notes' => 'nullable|string',
                    'is_confirmed' => 'sometimes|boolean',
                ];
                
                // Different validation based on roster type
                if ($roster->roster_type === 'shift') {
                    // For regular shift rosters
                    $rules['shift_type'] = 'required|in:morning,evening,night';
                    
                    if ($roster->department) {
                        $shiftSettings = RosterShiftSetting::where('department_id', $roster->department->id)
                            ->pluck('shift_type')
                            ->toArray();
                        
                        if (count($shiftSettings) > 0) {
                            $rules['shift_type'] = 'required|in:' . implode(',', $shiftSettings);
                        }
                    }
                } else {
                    // For oncall rosters
                    $rules['shift_type'] = 'required|in:oncall,standby';
                }
                
                $validatedData = $request->validate($rules);
                
                // Create an entry for each selected date
                foreach ($validatedData['dates'] as $date) {
                    RosterSlot::updateOrCreate(
                        [
                            'roster_id' => $roster->id,
                            'staff_id' => $validatedData['staff_id'],
                            'date' => $date,
                            'shift_type' => $validatedData['shift_type'],
                        ],
                        [
                            'start_time' => $validatedData['start_time'],
                            'end_time' => $validatedData['end_time'],
                            'notes' => $validatedData['notes'] ?? null,
                            'is_confirmed' => $request->has('is_confirmed') ? (bool)$request->is_confirmed : false,
                        ]
                    );
                }
                
                return redirect()->route('manage.rosters.show', $roster)
                    ->with('success', count($validatedData['dates']) . ' shifts added successfully.');
            } catch (\Exception $e) {
                return redirect()->route('manage.rosters.show', $roster)
                    ->with('error', 'Error processing request: ' . $e->getMessage());
            }
        }
    }

    /**
     * Remove the specified entry from storage.
     */
    public function destroyEntry(RosterEntry $entry)
    {
        $roster = $entry->roster;
        $entry->delete();
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Entry deleted successfully']);
        }
        
        return redirect()->route('manage.rosters.show', $roster)
            ->with('success', 'Shift deleted successfully.');
    }

    /**
     * Remove the specified slot from storage.
     */
    public function destroySlot(RosterSlot $slot)
    {
        $roster = $slot->roster;
        $slot->delete();
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Slot deleted successfully']);
        }
        
        return redirect()->route('manage.rosters.show', $roster)
            ->with('success', 'Shift deleted successfully.');
    }

    private function validateRosterEntry($entry, $roster)
    {
        $rules = [
            'staff_id' => 'required|exists:staff,id',
            'shift_date' => 'required_without:date|date',
            'date' => 'required_without:shift_date|date',
            'shift_type' => $roster->roster_type === 'shift' 
                ? 'required|in:morning,evening,night' 
                : 'required|in:oncall,standby',
            'is_confirmed' => 'sometimes|boolean',
        ];
        
        $validator = Validator::make($entry, $rules);
        
        if ($validator->fails()) {
            Log::error('Validation failed for entry', [
                'errors' => $validator->errors()->all(),
                'entry' => $entry
            ]);
            return false;
        }
        
        $validatedEntry = $validator->validated();
        
        // Check date range
        $startDate = $roster->start_date->format('Y-m-d');
        $endDate = $roster->end_date->format('Y-m-d');
        
        if (isset($validatedEntry['shift_date'])) {
            $shiftDate = $validatedEntry['shift_date'];
        } else {
            $shiftDate = $validatedEntry['date'];
        }
        
        if ($shiftDate < $startDate || $shiftDate > $endDate) {
            Log::error('Date out of range', [
                'roster_id' => $roster->id,
                'date' => $shiftDate,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return false;
        }
        
        return $validatedEntry;
    }
}
