<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StaffLoginRequest;
use App\Models\Staff;
use App\Models\User;
use App\Models\TeamLeader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffAuthController extends Controller
{
    /**
     * Display the staff login view.
     */
    public function create(): View
    {
        return view('auth.staff-login');
    }

    /**
     * Handle an incoming staff authentication request.
     */
    public function store(StaffLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user is a team leader
        if ($user->staff_id) {
            $teamLeader = TeamLeader::where('staff_id', $user->staff_id)
                ->where(function($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })->first();
                
            if ($teamLeader) {
                // Store team leader info in session
                $request->session()->put('is_team_leader', true);
                $request->session()->put('team_leader_type', $user->staff->type ?? null);
                $request->session()->put('team_leader_department_id', $teamLeader->department_id);
            }
        }
        
        // Redirect based on staff type/role
        if ($user->role === Staff::TYPE_ADMIN) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        } elseif ($user->role === Staff::TYPE_SPECIALIST_DOCTOR) {
            return redirect()->intended(route('specialist.dashboard', absolute: false));
        } elseif ($user->role === Staff::TYPE_MEDICAL_OFFICER) {
            return redirect()->intended(route('medical.dashboard', absolute: false));
        } elseif ($user->role === Staff::TYPE_HOUSEMAN_OFFICER) {
            return redirect()->intended(route('houseman.dashboard', absolute: false));
        } elseif ($user->role === Staff::TYPE_NURSE) {
            return redirect()->intended(route('nurse.dashboard', absolute: false));
        }

        // Default fallback
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Create a staff user account if it doesn't exist
     */
    public function createStaffAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $staff = Staff::findOrFail($request->staff_id);
        
        // Check if user account already exists
        if (User::where('staff_id', $staff->id)->exists()) {
            return back()->withErrors(['staff_id' => 'This staff member already has an account.']);
        }
        
        // Create new user account linked to staff
        User::create([
            'name' => $staff->name,
            'email' => $staff->email,
            'password' => Hash::make($request->password),
            'staff_id' => $staff->id,
            'role' => $staff->type,
        ]);
        
        return redirect()->route('staff.login')
            ->with('status', 'Account created successfully. You can now log in.');
    }
    
    /**
     * Show the form for creating a new staff account
     */
    public function showCreateAccountForm(): View
    {
        $staffWithoutAccounts = Staff::whereDoesntHave('user')->get();
        return view('auth.staff-register', compact('staffWithoutAccounts'));
    }
}
