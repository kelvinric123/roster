<?php

namespace App\Http\Controllers;

use App\Models\RosterShiftSetting;
use App\Models\Department;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RosterShiftSettingController extends Controller
{
    /**
     * Display a listing of the shift settings.
     */
    public function index(Request $request)
    {
        $filterType = $request->input('filter_type', 'department');
        $staffTypes = [
            Staff::TYPE_SPECIALIST_DOCTOR => 'Specialist Doctor',
            Staff::TYPE_MEDICAL_OFFICER => 'Medical Officer',
            Staff::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer',
            Staff::TYPE_NURSE => 'Nurse',
        ];
        
        $departments = Department::with(['shiftSettings' => function($query) use ($request) {
            if ($request->has('staff_type')) {
                $query->where('staff_type', $request->staff_type);
            }
            if ($request->has('roster_type')) {
                $query->where('roster_type', $request->roster_type);
            }
        }])->where('is_active', true)->get();
        
        return view('roster_shift_settings.index', compact('departments', 'staffTypes', 'filterType'));
    }

    /**
     * Show the form for creating a new shift setting.
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $shiftTypes = [
            'morning' => 'Morning Shift',
            'evening' => 'Evening Shift',
            'night' => 'Night Shift',
            'oncall' => 'On Call',
            'standby' => 'Standby',
        ];
        
        $staffTypes = [
            Staff::TYPE_SPECIALIST_DOCTOR => 'Specialist Doctor',
            Staff::TYPE_MEDICAL_OFFICER => 'Medical Officer',
            Staff::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer',
            Staff::TYPE_NURSE => 'Nurse',
        ];
        
        $rosterTypes = [
            'weekly' => 'Weekly Roster',
            'monthly' => 'Monthly Roster',
            'oncall' => 'On Call Roster',
        ];
        
        // Define which roster types are shift-based
        $shiftBasedRosterTypes = ['weekly', 'monthly'];
        
        return view('roster_shift_settings.create', compact('departments', 'shiftTypes', 'staffTypes', 'rosterTypes', 'shiftBasedRosterTypes'));
    }

    /**
     * Store a newly created shift setting in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'staff_type' => 'required|string',
            'roster_type' => 'required|string',
            'shift_type' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Only allow shift settings for shift-based roster types
        $shiftBasedRosterTypes = ['weekly', 'monthly'];
        if (!in_array($validated['roster_type'], $shiftBasedRosterTypes) && !in_array($validated['shift_type'], ['oncall', 'standby'])) {
            return redirect()->back()->with('error', 'Shift settings can only be created for shift-based roster types (weekly, monthly) or for on-call shift types.');
        }

        // Check if setting already exists for this department, staff type, roster type and shift type
        $existingSetting = RosterShiftSetting::where('department_id', $validated['department_id'])
            ->where('staff_type', $validated['staff_type'])
            ->where('roster_type', $validated['roster_type'])
            ->where('shift_type', $validated['shift_type'])
            ->first();

        if ($existingSetting) {
            // Update existing setting
            $existingSetting->update($validated);
            $message = 'Shift setting updated successfully.';
        } else {
            // Create new setting
            RosterShiftSetting::create($validated);
            $message = 'Shift setting created successfully.';
        }

        return redirect()->route('roster-shift-settings.index')
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified shift setting.
     */
    public function edit(RosterShiftSetting $rosterShiftSetting)
    {
        // Only allow editing shift settings for shift-based roster types or on-call shift types
        $shiftBasedRosterTypes = ['weekly', 'monthly'];
        $oncallShiftTypes = ['oncall', 'standby'];
        
        if (!in_array($rosterShiftSetting->roster_type, $shiftBasedRosterTypes) && 
            !in_array($rosterShiftSetting->shift_type, $oncallShiftTypes)) {
            return redirect()->route('roster-shift-settings.index')
                ->with('error', 'Only shift settings for shift-based roster types can be edited.');
        }
        
        $departments = Department::where('is_active', true)->get();
        $shiftTypes = [
            'morning' => 'Morning Shift',
            'evening' => 'Evening Shift',
            'night' => 'Night Shift',
            'oncall' => 'On Call',
            'standby' => 'Standby',
        ];
        
        $staffTypes = [
            Staff::TYPE_SPECIALIST_DOCTOR => 'Specialist Doctor',
            Staff::TYPE_MEDICAL_OFFICER => 'Medical Officer',
            Staff::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer',
            Staff::TYPE_NURSE => 'Nurse',
        ];
        
        $rosterTypes = [
            'weekly' => 'Weekly Roster',
            'monthly' => 'Monthly Roster',
            'oncall' => 'On Call Roster',
        ];
        
        // Define which roster types are shift-based
        $shiftBasedRosterTypes = ['weekly', 'monthly'];
        
        return view('roster_shift_settings.edit', compact(
            'rosterShiftSetting', 
            'departments', 
            'shiftTypes', 
            'staffTypes', 
            'rosterTypes',
            'shiftBasedRosterTypes'
        ));
    }

    /**
     * Update the specified shift setting in storage.
     */
    public function update(Request $request, RosterShiftSetting $rosterShiftSetting)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'staff_type' => 'required|string',
            'roster_type' => 'required|string',
            'shift_type' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Only allow updating shift settings for shift-based roster types
        $shiftBasedRosterTypes = ['weekly', 'monthly'];
        if (!in_array($validated['roster_type'], $shiftBasedRosterTypes) && !in_array($validated['shift_type'], ['oncall', 'standby'])) {
            return redirect()->back()->with('error', 'Shift settings can only be updated for shift-based roster types (weekly, monthly) or for on-call shift types.');
        }

        $rosterShiftSetting->update($validated);

        return redirect()->route('roster-shift-settings.index')
            ->with('success', 'Shift setting updated successfully.');
    }

    /**
     * Remove the specified shift setting from storage.
     */
    public function destroy(RosterShiftSetting $rosterShiftSetting)
    {
        $rosterShiftSetting->delete();

        return redirect()->route('roster-shift-settings.index')
            ->with('success', 'Shift setting deleted successfully.');
    }
}
