<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roster;
use App\Models\Department;
use App\Models\Staff;
use App\Models\DepartmentStaffTypeRoster;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SortableRosterController extends Controller
{
    /**
     * Show the form for creating a new sortable roster.
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
        
        return view('sortable_rosters.create', compact('departments', 'staffTypes', 'isDepartmentLeader', 'departmentId'));
    }

    /**
     * Store a newly created sortable roster in storage.
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

        // Add roster_type as 'oncall' instead of 'sortable'
        $validated['roster_type'] = 'oncall';
        $validated['is_published'] = false;

        // Create the roster
        $roster = Roster::create($validated);

        return redirect()->route('manage.sortable-rosters.show', $roster)
            ->with('success', 'Oncall roster created successfully.');
    }

    /**
     * Display the specified sortable roster.
     */
    public function show(Roster $sortableRoster)
    {
        // Load related data
        $sortableRoster->load('department');
        
        // Get department staff type roster configuration to get oncall settings
        $deptStaffTypeRoster = DepartmentStaffTypeRoster::where('department_id', $sortableRoster->department_id)
            ->where('staff_type', $sortableRoster->staff_type)
            ->where('roster_type', 'oncall')
            ->first();
            
        $oncallStaffCount = 2; // Default
        $oncallStaffTitles = []; // Default empty array for titles
        
        if ($deptStaffTypeRoster) {
            if (isset($deptStaffTypeRoster->settings['oncall_staff_count'])) {
                $oncallStaffCount = $deptStaffTypeRoster->settings['oncall_staff_count'];
            }
            
            if (isset($deptStaffTypeRoster->settings['oncall_staff_titles'])) {
                $oncallStaffTitles = $deptStaffTypeRoster->settings['oncall_staff_titles'];
            }
        }
        
        // Default titles if none are defined
        if (empty($oncallStaffTitles)) {
            for ($i = 1; $i <= $oncallStaffCount; $i++) {
                $oncallStaffTitles[] = "On Call " . $i;
            }
        }
        
        // Get relevant staff for this roster based on department and staff type
        $staff = Staff::where('department_id', $sortableRoster->department_id)
            ->where('type', $sortableRoster->staff_type)
            ->where('is_active', true)
            ->get();
        
        // Check if we have a saved order in the metadata
        $savedOrder = $sortableRoster->metadata['staff_order'] ?? null;
        if ($savedOrder) {
            // Reorder staff based on saved order
            $staffMap = $staff->keyBy('id');
            $orderedStaff = collect();
            
            foreach ($savedOrder as $staffId) {
                if (isset($staffMap[$staffId])) {
                    $orderedStaff->push($staffMap[$staffId]);
                    $staffMap->forget($staffId);
                }
            }
            
            // Add any staff not in the saved order at the end
            $orderedStaff = $orderedStaff->merge($staffMap->values());
            $staff = $orderedStaff;
        }
            
        return view('sortable_rosters.show', compact('sortableRoster', 'staff', 'oncallStaffCount', 'oncallStaffTitles'));
    }

    /**
     * Update the sortable roster order.
     */
    public function updateOrder(Request $request, Roster $sortableRoster)
    {
        $validator = Validator::make($request->all(), [
            'staff_order' => 'sometimes|required|array',
            'staff_order.*' => 'required|integer|exists:staff,id',
            'oncall_assignments' => 'sometimes|required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Initialize metadata if it doesn't exist
        $metadata = $sortableRoster->metadata ?? [];
        
        // Update with staff order if provided
        if ($request->has('staff_order')) {
            $metadata['staff_order'] = $request->staff_order;
        }
        
        // Update with oncall assignments if provided
        if ($request->has('oncall_assignments')) {
            $assignments = $request->oncall_assignments;
            
            // Process assignments to convert to indexed arrays and remove null values
            foreach ($assignments as $date => $roles) {
                $indexedArray = [];
                
                // Convert object/associative array to indexed array
                foreach ($roles as $roleIndex => $staffId) {
                    if ($staffId !== null) {
                        $indexedArray[(int)$roleIndex] = $staffId;
                    }
                }
                
                // Replace with processed array
                $assignments[$date] = $indexedArray;
            }
            
            $metadata['oncall_assignments'] = $assignments;
            
            // Log for debugging
            Log::info('Saved assignments', ['assignments' => $assignments]);
        }
        
        // Save the updated metadata
        $sortableRoster->metadata = $metadata;
        $sortableRoster->save();
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Generate oncall assignments based on all available staff
     */
    public function generateAssignments(Request $request, Roster $sortableRoster)
    {
        // Get department staff type roster configuration
        $deptStaffTypeRoster = DepartmentStaffTypeRoster::where('department_id', $sortableRoster->department_id)
            ->where('staff_type', $sortableRoster->staff_type)
            ->where('roster_type', 'oncall')
            ->first();
            
        $oncallStaffCount = 2; // Default
        $oncallStaffTitles = []; // Default empty array for titles
        
        if ($deptStaffTypeRoster) {
            if (isset($deptStaffTypeRoster->settings['oncall_staff_count'])) {
                $oncallStaffCount = $deptStaffTypeRoster->settings['oncall_staff_count'];
            }
            
            if (isset($deptStaffTypeRoster->settings['oncall_staff_titles'])) {
                $oncallStaffTitles = $deptStaffTypeRoster->settings['oncall_staff_titles'];
            }
        }
        
        // Default titles if none are defined
        if (empty($oncallStaffTitles)) {
            for ($i = 1; $i <= $oncallStaffCount; $i++) {
                $oncallStaffTitles[] = "On Call " . $i;
            }
        }
        
        // Get all active staff in this department and of this type
        $allStaff = Staff::where('department_id', $sortableRoster->department_id)
            ->where('type', $sortableRoster->staff_type)
            ->where('is_active', true)
            ->get();
            
        if ($allStaff->isEmpty()) {
            return redirect()->route('manage.sortable-rosters.show', $sortableRoster)
                ->with('error', 'No active staff found for this department and staff type.');
        }
        
        // Create array of staff IDs
        $staffIds = $allStaff->pluck('id')->toArray();
        
        // Get the date range
        $startDate = $sortableRoster->start_date;
        $endDate = $sortableRoster->end_date;
        $currentDate = clone $startDate;
        
        // Create roster entries
        $entries = [];
        $staffIndex = 0;
        
        while ($currentDate <= $endDate) {
            // Create entry for this date
            $dateStr = $currentDate->format('Y-m-d');
            
            // Assign oncall staff (take n staff from the array)
            $oncallStaffIds = [];
            for ($i = 0; $i < $oncallStaffCount; $i++) {
                if (!empty($staffIds)) {
                    $staffId = $staffIds[$staffIndex % count($staffIds)];
                    $oncallStaffIds[$i] = $staffId; // Use indexed array to maintain role positions
                    $staffIndex++;
                }
            }
            
            // Store in roster metadata
            $entries[$dateStr] = $oncallStaffIds;
            
            $currentDate->addDay();
        }
        
        // Save assignments to roster metadata
        $sortableRoster->metadata = array_merge($sortableRoster->metadata ?? [], [
            'oncall_assignments' => $entries,
            'oncall_staff_titles' => $oncallStaffTitles
        ]);
        
        $sortableRoster->save();
        
        return redirect()->route('manage.sortable-rosters.show', $sortableRoster)
            ->with('success', 'Oncall assignments generated successfully.');
    }

    /**
     * Publish the sortable roster.
     */
    public function publish(Request $request, Roster $sortableRoster)
    {
        // Mark roster as published
        $sortableRoster->is_published = true;
        $sortableRoster->save();
        
        return redirect()->route('manage.sortable-rosters.show', $sortableRoster)
            ->with('success', 'Oncall roster published successfully.');
    }

    /**
     * Save the on-call assignments for the roster.
     */
    public function saveAssignments(Request $request, Roster $sortableRoster)
    {
        $validator = Validator::make($request->all(), [
            'assignments' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Initialize metadata if it doesn't exist
        $metadata = $sortableRoster->metadata ?? [];
        
        // Update the assignments in the metadata
        $metadata['oncall_assignments'] = $request->assignments;
        
        // Save the updated metadata
        $sortableRoster->metadata = $metadata;
        $sortableRoster->save();
        
        // Log for debugging
        Log::info('Saved oncall assignments', ['assignments' => $request->assignments]);
        
        return response()->json([
            'success' => true,
            'message' => 'Assignments saved successfully'
        ]);
    }
} 