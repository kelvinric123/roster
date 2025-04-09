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
            'oncall_staff_counts' => 'array',
            'oncall_staff_counts.*' => 'nullable|integer|min:1|max:6',
            'oncall_staff_titles' => 'array',
            'oncall_staff_titles.*' => 'array',
            'oncall_staff_titles.*.*' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Update each staff type's roster type and settings
        foreach ($request->roster_types as $staffType => $rosterType) {
            $settings = [];
            
            // If this is an oncall roster type and we have a staff count setting
            if ($rosterType === 'oncall') {
                if (isset($request->oncall_staff_counts[$staffType])) {
                    $settings['oncall_staff_count'] = (int)$request->oncall_staff_counts[$staffType];
                }
                
                // Save oncall staff titles if provided
                if (isset($request->oncall_staff_titles[$staffType])) {
                    $titles = array_map(function($title) {
                        return trim($title) ?: null;
                    }, $request->oncall_staff_titles[$staffType]);
                    
                    // Filter out any null values and ensure we have proper titles
                    $titles = array_filter($titles, function($title) {
                        return $title !== null;
                    });
                    
                    // If we have no valid titles, create default ones based on the count
                    if (empty($titles)) {
                        $count = $settings['oncall_staff_count'] ?? 2;
                        for ($i = 1; $i <= $count; $i++) {
                            $titles[] = "oncall {$i}";
                        }
                    }
                    
                    $settings['oncall_staff_titles'] = array_values($titles);
                }
            }
            
            $department->staffTypeRosters()
                ->where('staff_type', $staffType)
                ->update([
                    'roster_type' => $rosterType,
                    'settings' => $settings
                ]);
        }
        
        return redirect()->route('departments.staff-type-rosters.index', $department)
            ->with('success', 'Staff type roster settings updated successfully.');
    }
}
