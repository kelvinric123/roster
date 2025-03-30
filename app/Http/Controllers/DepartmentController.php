<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\TeamLeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $isDepartmentLeader = false;
        $departmentId = null;
        
        // Check if user is a department leader
        if ($user && $user->staff_id && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            // Find the team leader entry for this user
            $teamLeader = TeamLeader::where('staff_id', $user->staff_id)
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', now())
                ->first();
                
            if ($teamLeader) {
                $isDepartmentLeader = true;
                $departmentId = $teamLeader->department_id;
                
                // Department leaders only see their own department
                $departments = Department::where('id', $departmentId)->latest()->paginate(10);
            } else {
                $departments = Department::latest()->paginate(10);
            }
        } else {
            // Admin and regular staff see all departments
            $departments = Department::latest()->paginate(10);
        }
        
        return view('departments.index', compact('departments', 'isDepartmentLeader'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if user is a department leader - they shouldn't create departments
        $user = Auth::user();
        if ($user && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            return redirect()->route('departments.index')
                ->with('error', 'Department leaders cannot create new departments.');
        }
        
        return view('departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user is a department leader - they shouldn't create departments
        $user = Auth::user();
        if ($user && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            return redirect()->route('departments.index')
                ->with('error', 'Department leaders cannot create new departments.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departments,code',
            'description' => 'nullable|string',
            // roster_type is now managed through DepartmentStaffTypeRoster
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Department::create($request->all());

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        // Check if user is a department leader - they can only view their own department
        $user = Auth::user();
        $canView = true;
        
        if ($user && $user->staff_id && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            $teamLeader = TeamLeader::where('staff_id', $user->staff_id)
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', now())
                ->first();
                
            if ($teamLeader && $teamLeader->department_id != $department->id) {
                $canView = false;
            }
        }
        
        if (!$canView) {
            return redirect()->route('departments.index')
                ->with('error', 'You do not have permission to view this department.');
        }
        
        $department->load('staff');
        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        // Check if user is a department leader - they can only edit their own department
        $user = Auth::user();
        $canEdit = true;
        
        if ($user && $user->staff_id && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            $teamLeader = TeamLeader::where('staff_id', $user->staff_id)
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', now())
                ->first();
                
            if ($teamLeader && $teamLeader->department_id != $department->id) {
                $canEdit = false;
            }
        }
        
        if (!$canEdit) {
            return redirect()->route('departments.index')
                ->with('error', 'You do not have permission to edit this department.');
        }
        
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        // Check if user is a department leader - they can only update their own department
        $user = Auth::user();
        $canUpdate = true;
        
        if ($user && $user->staff_id && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            $teamLeader = TeamLeader::where('staff_id', $user->staff_id)
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', now())
                ->first();
                
            if ($teamLeader && $teamLeader->department_id != $department->id) {
                $canUpdate = false;
            }
        }
        
        if (!$canUpdate) {
            return redirect()->route('departments.index')
                ->with('error', 'You do not have permission to update this department.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            // roster_type is now managed through DepartmentStaffTypeRoster
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $department->update($request->all());

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        // Department leaders cannot delete departments
        $user = Auth::user();
        if ($user && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            return redirect()->route('departments.index')
                ->with('error', 'Department leaders cannot delete departments.');
        }
        
        // Check if department has staff
        if ($department->staff()->count() > 0) {
            return redirect()->route('departments.index')
                ->with('error', 'Department cannot be deleted because it has staff assigned to it.');
        }

        $department->delete();
        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    /**
     * Toggle the status of the specified department.
     */
    public function toggleStatus(Department $department)
    {
        // Department leaders cannot change department status
        $user = Auth::user();
        if ($user && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            return redirect()->route('departments.index')
                ->with('error', 'Department leaders cannot change department status.');
        }
        
        $department->update(['is_active' => !$department->is_active]);
        return redirect()->route('departments.index')
            ->with('success', 'Department status updated successfully.');
    }
}
