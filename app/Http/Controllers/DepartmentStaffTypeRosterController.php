<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\DepartmentStaffTypeRoster;
use Illuminate\Support\Facades\Validator;

class DepartmentStaffTypeRosterController extends Controller
{
    /**
     * Display roster type settings for a department
     */
    public function index(Department $department)
    {
        $department->load('staffTypeRosters');
        
        // Define staff types
        $staffTypes = [
            'specialist_doctor' => 'Specialist Doctor',
            'medical_officer' => 'Medical Officer',
            'houseman_officer' => 'Houseman Officer',
            'nurse' => 'Nurse',
        ];
        
        // Create settings for staff types that don't have settings yet
        $existingTypes = $department->staffTypeRosters->pluck('staff_type')->toArray();
        
        foreach ($staffTypes as $type => $label) {
            if (!in_array($type, $existingTypes)) {
                $department->staffTypeRosters()->create([
                    'staff_type' => $type,
                    'roster_type' => $department->roster_type, // Use department default
                ]);
            }
        }
        
        // Reload to get the new settings
        $department->load('staffTypeRosters');
        
        return view('departments.staff_type_rosters', compact('department', 'staffTypes'));
    }
    
    /**
     * Update roster type settings for a department
     */
    public function update(Request $request, Department $department)
    {
        $validator = Validator::make($request->all(), [
            'roster_types' => 'required|array',
            'roster_types.*' => 'required|in:oncall,shift',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Update each staff type's roster type
        foreach ($request->roster_types as $staffType => $rosterType) {
            $department->staffTypeRosters()
                ->where('staff_type', $staffType)
                ->update(['roster_type' => $rosterType]);
        }
        
        return redirect()->route('departments.staff-type-rosters.index', $department)
            ->with('success', 'Staff type roster settings updated successfully.');
    }
}
