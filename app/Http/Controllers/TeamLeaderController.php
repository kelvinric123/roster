<?php

namespace App\Http\Controllers;

use App\Models\TeamLeader;
use App\Models\Staff;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeamLeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TeamLeader::with(['staff', 'department']);
        
        // Filter by department
        if ($request->has('department_id') && !empty($request->department_id)) {
            $query->where('department_id', $request->department_id);
        }
        
        // Filter by active status
        if ($request->has('status') && $request->status === 'active') {
            $query->active();
        }
        
        $teamLeaders = $query->latest()->paginate(10)->withQueryString();
        
        $departments = Department::where('is_active', true)->get();
        
        return view('team_leaders.index', compact('teamLeaders', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $staffMembers = Staff::where('is_active', true)->get();
        
        $leaderTypes = [
            TeamLeader::TYPE_TEAM_LEADER => 'Team Leader',
            TeamLeader::TYPE_MEDICAL_OFFICER_LEADER => 'Medical Officer Leader',
            TeamLeader::TYPE_SPECIALIST_DOCTOR_LEADER => 'Specialist Doctor Leader',
            TeamLeader::TYPE_HOUSEMAN_OFFICER_LEADER => 'Houseman Officer Leader',
            TeamLeader::TYPE_NURSE_LEADER => 'Nurse Leader',
        ];
        
        $staffTypes = [
            Staff::TYPE_SPECIALIST_DOCTOR => 'Specialist Doctor',
            Staff::TYPE_MEDICAL_OFFICER => 'Medical Officer',
            Staff::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer',
            Staff::TYPE_NURSE => 'Nurse',
        ];
        
        $leaderTypeMapping = [
            Staff::TYPE_SPECIALIST_DOCTOR => TeamLeader::TYPE_SPECIALIST_DOCTOR_LEADER,
            Staff::TYPE_MEDICAL_OFFICER => TeamLeader::TYPE_MEDICAL_OFFICER_LEADER, 
            Staff::TYPE_HOUSEMAN_OFFICER => TeamLeader::TYPE_HOUSEMAN_OFFICER_LEADER,
            Staff::TYPE_NURSE => TeamLeader::TYPE_NURSE_LEADER,
        ];
        
        return view('team_leaders.create', compact('departments', 'staffMembers', 'leaderTypes', 'staffTypes', 'leaderTypeMapping'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:staff,id',
            'department_id' => 'required|exists:departments,id',
            'staff_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate that the staff member is of the appropriate type for the leader role
        $staff = Staff::find($request->staff_id);
        if ($staff->type !== $request->staff_type) {
            return back()->withErrors(['staff_id' => 'The selected staff member\'s type does not match the selected staff type.'])->withInput();
        }
        
        // Handle permanent position setting
        $data = $request->all();
        if ($request->has('is_permanent') && $request->is_permanent) {
            $data['end_date'] = null;
        }
        
        // Remove staff_type and leader_type as they're not fields in the team_leaders table
        unset($data['staff_type']);
        unset($data['leader_type']);
        
        // Create the team leader
        $teamLeader = TeamLeader::create($data);
        
        // Update user role
        $user = \App\Models\User::where('staff_id', $staff->id)->first();
        if ($user) {
            $user->update(['role' => $staff->type . '_leader']);
        }
        
        return redirect()->route('team-leaders.index')
            ->with('success', 'Team leader created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TeamLeader $teamLeader)
    {
        $teamLeader->load(['staff', 'department']);
        return view('team_leaders.show', compact('teamLeader'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TeamLeader $teamLeader)
    {
        $departments = Department::where('is_active', true)->get();
        $staffMembers = Staff::where('is_active', true)->get();
        
        $leaderTypes = [
            TeamLeader::TYPE_TEAM_LEADER => 'Team Leader',
            TeamLeader::TYPE_MEDICAL_OFFICER_LEADER => 'Medical Officer Leader',
            TeamLeader::TYPE_SPECIALIST_DOCTOR_LEADER => 'Specialist Doctor Leader',
            TeamLeader::TYPE_HOUSEMAN_OFFICER_LEADER => 'Houseman Officer Leader',
            TeamLeader::TYPE_NURSE_LEADER => 'Nurse Leader',
        ];
        
        $staffTypes = [
            Staff::TYPE_SPECIALIST_DOCTOR => 'Specialist Doctor',
            Staff::TYPE_MEDICAL_OFFICER => 'Medical Officer',
            Staff::TYPE_HOUSEMAN_OFFICER => 'Houseman Officer',
            Staff::TYPE_NURSE => 'Nurse',
        ];
        
        $leaderTypeMapping = [
            Staff::TYPE_SPECIALIST_DOCTOR => TeamLeader::TYPE_SPECIALIST_DOCTOR_LEADER,
            Staff::TYPE_MEDICAL_OFFICER => TeamLeader::TYPE_MEDICAL_OFFICER_LEADER, 
            Staff::TYPE_HOUSEMAN_OFFICER => TeamLeader::TYPE_HOUSEMAN_OFFICER_LEADER,
            Staff::TYPE_NURSE => TeamLeader::TYPE_NURSE_LEADER,
        ];
        
        return view('team_leaders.edit', compact('teamLeader', 'departments', 'staffMembers', 'leaderTypes', 'staffTypes', 'leaderTypeMapping'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeamLeader $teamLeader)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:staff,id',
            'department_id' => 'required|exists:departments,id',
            'staff_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate that the staff member is of the appropriate type for the leader role
        $staff = Staff::find($request->staff_id);
        if ($staff->type !== $request->staff_type) {
            return back()->withErrors(['staff_id' => 'The selected staff member\'s type does not match the selected staff type.'])->withInput();
        }
        
        // Handle permanent position setting
        $data = $request->all();
        if ($request->has('is_permanent') && $request->is_permanent) {
            $data['end_date'] = null;
        }
        
        // Remove staff_type and leader_type as they're not fields in the team_leaders table
        unset($data['staff_type']);
        unset($data['leader_type']);
        
        // Update the team leader
        $teamLeader->update($data);
        
        // Update user role
        $user = \App\Models\User::where('staff_id', $staff->id)->first();
        if ($user) {
            $user->update(['role' => $staff->type . '_leader']);
        }
        
        return redirect()->route('team-leaders.index')
            ->with('success', 'Team leader updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeamLeader $teamLeader)
    {
        // Reset user role back to staff type
        $staff = Staff::find($teamLeader->staff_id);
        $user = \App\Models\User::where('staff_id', $staff->id)->first();
        if ($user) {
            $user->update(['role' => $staff->type]);
        }
        
        $teamLeader->delete();
        
        return redirect()->route('team-leaders.index')
            ->with('success', 'Team leader deleted successfully.');
    }
    
    /**
     * Validate that the staff type is appropriate for the leader role
     */
    private function validateStaffTypeForLeaderRole($staffType, $leaderType)
    {
        $validCombinations = [
            Staff::TYPE_MEDICAL_OFFICER => [TeamLeader::TYPE_MEDICAL_OFFICER_LEADER, TeamLeader::TYPE_TEAM_LEADER],
            Staff::TYPE_SPECIALIST_DOCTOR => [TeamLeader::TYPE_SPECIALIST_DOCTOR_LEADER, TeamLeader::TYPE_TEAM_LEADER],
            Staff::TYPE_HOUSEMAN_OFFICER => [TeamLeader::TYPE_HOUSEMAN_OFFICER_LEADER, TeamLeader::TYPE_TEAM_LEADER],
            Staff::TYPE_NURSE => [TeamLeader::TYPE_NURSE_LEADER, TeamLeader::TYPE_TEAM_LEADER],
            Staff::TYPE_ADMIN => [TeamLeader::TYPE_TEAM_LEADER],
        ];
        
        if (isset($validCombinations[$staffType])) {
            return in_array($leaderType, $validCombinations[$staffType]);
        }
        
        return false;
    }
}
