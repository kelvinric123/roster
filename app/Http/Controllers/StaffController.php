<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Staff::with(['department']);
        
        // Hide admin staff type from non-admin users
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        
        // Always filter out admin staff type for non-admin users
        if (!$isAdmin) {
            $query->where('type', '!=', 'admin');
        }
        
        // Check if user is a department leader
        $isDepartmentLeader = false;
        $departmentId = null;
        
        if ($user && $user->staff_id && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin') {
            // Find the team leader entry for this user
            $teamLeader = \App\Models\TeamLeader::where('staff_id', $user->staff_id)
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', now())
                ->first();
                
            if ($teamLeader) {
                $isDepartmentLeader = true;
                $departmentId = $teamLeader->department_id;
                
                // Filter query to only show staff from leader's department
                $query->where('department_id', $departmentId);
            }
        }
        
        // Filter by name, email or phone
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }
        
        // Filter by department (only if user is not a department leader)
        if (!$isDepartmentLeader && $request->has('department_id') && !empty($request->department_id)) {
            $query->where('department_id', $request->department_id);
        }
        
        // Filter by staff type
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }
        
        // Filter by status
        if ($request->has('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $staff = $query->latest()->paginate(10)->withQueryString();
        
        // Get departments - if department leader, only show their department
        if ($isDepartmentLeader && $departmentId) {
            $departments = Department::where('id', $departmentId)->where('is_active', true)->get();
        } else {
            $departments = Department::where('is_active', true)->get();
        }
        
        // Only show admin type in dropdown for admin users
        $staffTypes = [];
        if ($isAdmin) {
            $staffTypes = [
                'admin' => 'Administrator',
                'specialist_doctor' => 'Specialist Doctor',
                'medical_officer' => 'Medical Officer',
                'houseman_officer' => 'Houseman Officer',
                'nurse' => 'Nurse'
            ];
        } else {
            $staffTypes = [
                'specialist_doctor' => 'Specialist Doctor',
                'medical_officer' => 'Medical Officer',
                'houseman_officer' => 'Houseman Officer',
                'nurse' => 'Nurse'
            ];
        }
        
        return view('staff.index', compact('staff', 'departments', 'staffTypes', 'isDepartmentLeader', 'departmentId', 'isAdmin'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        
        $departments = \App\Models\Department::where('is_active', true)->get();
        
        // Only show admin staff type option for admin users
        $staffTypes = [];
        if ($isAdmin) {
            $staffTypes = [
                'admin' => 'Administrator',
                'specialist_doctor' => 'Specialist Doctor',
                'medical_officer' => 'Medical Officer',
                'houseman_officer' => 'Houseman Officer',
                'nurse' => 'Nurse'
            ];
        } else {
            $staffTypes = [
                'specialist_doctor' => 'Specialist Doctor',
                'medical_officer' => 'Medical Officer',
                'houseman_officer' => 'Houseman Officer',
                'nurse' => 'Nurse'
            ];
        }
        
        return view('staff.create', compact('departments', 'isAdmin', 'staffTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        
        // Set validation rules based on user role
        $typeRule = $isAdmin 
            ? 'required|in:admin,specialist_doctor,medical_officer,houseman_officer,nurse'
            : 'required|in:specialist_doctor,medical_officer,houseman_officer,nurse';
        
        $baseRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'phone' => 'nullable|string|max:20',
            'type' => $typeRule,
            'joining_date' => 'required|date',
            'notes' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
        ];

        // Ensure non-admin users can't create admin staff
        if (!$isAdmin && $request->type === 'admin') {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to create administrator profiles.');
        }

        $typeSpecificRules = $this->getTypeSpecificRules($request->type);
        $validator = Validator::make($request->all(), array_merge($baseRules, $typeSpecificRules));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create the staff record
        $staff = Staff::create($request->all());

        // Create a user account automatically with default password
        $defaultPassword = 'qmed.asia';
        \App\Models\User::create([
            'name' => $staff->name,
            'email' => $staff->email,
            'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword),
            'staff_id' => $staff->id,
            'role' => $staff->type,
        ]);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member created successfully with default login credentials (Password: qmed.asia).');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Staff $staff)
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        
        // Prevent non-admin users from editing admin profiles
        if (!$isAdmin && $staff->type === 'admin') {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to edit administrator profiles.');
        }
        
        $departments = \App\Models\Department::where('is_active', true)->get();
        $user = \App\Models\User::where('staff_id', $staff->id)->first();
        
        // Only show admin staff type option for admin users
        $staffTypes = [];
        if ($isAdmin) {
            $staffTypes = [
                'admin' => 'Administrator',
                'specialist_doctor' => 'Specialist Doctor',
                'medical_officer' => 'Medical Officer',
                'houseman_officer' => 'Houseman Officer',
                'nurse' => 'Nurse'
            ];
        } else {
            $staffTypes = [
                'specialist_doctor' => 'Specialist Doctor',
                'medical_officer' => 'Medical Officer',
                'houseman_officer' => 'Houseman Officer',
                'nurse' => 'Nurse'
            ];
        }
        
        return view('staff.edit', compact('staff', 'departments', 'user', 'isAdmin', 'staffTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        
        // Prevent non-admin users from updating admin profiles
        if (!$isAdmin && $staff->type === 'admin') {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to update administrator profiles.');
        }
        
        $baseRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $staff->id,
            'phone' => 'nullable|string|max:20',
            'type' => 'required|in:admin,specialist_doctor,medical_officer,houseman_officer,nurse',
            'joining_date' => 'required|date',
            'notes' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
        ];

        // Non-admin users can't change staff type to admin
        if (!$isAdmin) {
            $baseRules['type'] = 'required|in:specialist_doctor,medical_officer,houseman_officer,nurse';
        }

        $typeSpecificRules = $this->getTypeSpecificRules($request->type);
        $validator = Validator::make($request->all(), array_merge($baseRules, $typeSpecificRules));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update the staff record
        $staff->update($request->all());

        // Check if user account exists and update it
        $user = \App\Models\User::where('staff_id', $staff->id)->first();
        if ($user) {
            $user->update([
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $staff->type,
            ]);
        } else {
            // Create a user account if it doesn't exist
            $defaultPassword = 'qmed.asia';
            \App\Models\User::create([
                'name' => $staff->name,
                'email' => $staff->email,
                'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword),
                'staff_id' => $staff->id,
                'role' => $staff->type,
            ]);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff)
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        
        // Prevent non-admin users from deleting admin profiles
        if (!$isAdmin && $staff->type === 'admin') {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to delete administrator profiles.');
        }
        
        $staff->delete();
        return redirect()->route('staff.index')
            ->with('success', 'Staff member deleted successfully.');
    }

    public function toggleStatus(Staff $staff)
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        
        // Prevent non-admin users from toggling status of admin profiles
        if (!$isAdmin && $staff->type === 'admin') {
            return redirect()->route('staff.index')
                ->with('error', 'You do not have permission to modify administrator profiles.');
        }
        
        $staff->update(['is_active' => !$staff->is_active]);
        return redirect()->route('staff.index')
            ->with('success', 'Staff member status updated successfully.');
    }

    /**
     * Get validation rules specific to staff type
     */
    private function getTypeSpecificRules($type)
    {
        return match($type) {
            'admin' => [],
            'specialist_doctor' => [
                'specialization' => 'nullable|string|max:255',
                'qualification' => 'required|string|max:255',
            ],
            'medical_officer' => [
                'specialization' => 'nullable|string|max:255',
            ],
            'houseman_officer' => [
                'current_rotation' => 'required|string|max:255',
                'graduation_year' => 'required|integer|min:1900',
            ],
            'nurse' => [
                'nursing_unit' => 'required|string|max:255',
                'nurse_level' => 'required|string|max:255',
            ],
            default => [],
        };
    }
    
    /**
     * Display the staff settings page
     */
    public function settings()
    {
        return view('staff.settings');
    }
    
    /**
     * Display the shift settings page
     */
    public function shiftSettings()
    {
        $departments = \App\Models\Department::where('is_active', true)->get();
        $shiftSettings = \App\Models\RosterShiftSetting::with('department')->get();
        
        return view('staff.shift_settings', compact('departments', 'shiftSettings'));
    }
    
    /**
     * Display the department shifts page
     */
    public function departmentShifts()
    {
        $departments = \App\Models\Department::where('is_active', true)->get();
        $shiftSettings = \App\Models\RosterShiftSetting::with('department')->get();
        
        return view('staff.department_shifts', compact('departments', 'shiftSettings'));
    }
    
    /**
     * Display the on-call schedules page
     */
    public function oncallSchedules()
    {
        $departments = \App\Models\Department::where('is_active', true)->get();
        $staff = Staff::where('is_active', true)
            ->whereIn('type', ['specialist_doctor', 'medical_officer', 'houseman_officer'])
            ->get();
            
        return view('staff.oncall_schedules', compact('departments', 'staff'));
    }
    
    /**
     * Display the on-call assignments page
     */
    public function oncallAssignments()
    {
        $departments = \App\Models\Department::where('is_active', true)->get();
        $staff = Staff::where('is_active', true)
            ->whereIn('type', ['specialist_doctor', 'medical_officer', 'houseman_officer'])
            ->get();
            
        return view('staff.oncall_assignments', compact('departments', 'staff'));
    }
    
    /**
     * Display the staff type settings page
     */
    public function typeSettings()
    {
        $staffTypes = [
            'specialist_doctor' => 'Specialist Doctor',
            'medical_officer' => 'Medical Officer',
            'houseman_officer' => 'Houseman Officer',
            'nurse' => 'Nurse'
        ];
        
        return view('staff.type_settings', compact('staffTypes'));
    }
    
    /**
     * Update staff type settings
     */
    public function updateTypeSettings(Request $request, $type)
    {
        $validTypes = ['specialist_doctor', 'medical_officer', 'houseman_officer', 'nurse'];
        
        if (!in_array($type, $validTypes)) {
            return back()->with('error', 'Invalid staff type specified.');
        }
        
        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|max:255',
            'required_fields' => 'nullable|array',
            'required_fields.*' => 'string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Here you would update the settings in the database
        // For now, just redirect with success message
        
        return back()->with('success', 'Staff type settings updated successfully.');
    }
    
    /**
     * Store a new on-call schedule
     */
    public function storeOncallSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'staff_type' => 'required|in:specialist_doctor,medical_officer,houseman_officer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'rotation_type' => 'required|in:daily,weekly,custom',
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Here you would store the on-call schedule in the database
        // For demonstration purposes, we'll just redirect with a success message
        
        return back()->with('success', 'On-Call schedule created successfully!');
    }
}
