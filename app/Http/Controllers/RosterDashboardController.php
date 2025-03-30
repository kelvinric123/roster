<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roster;
use App\Models\RosterEntry;
use App\Models\RosterSlot;
use App\Models\Staff;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RosterDashboardController extends Controller
{
    /**
     * Display the roster dashboard.
     */
    public function index()
    {
        // Get summary stats
        $totalRosters = Roster::count();
        $publishedRosters = Roster::where('is_published', true)->count();
        
        // Count both entries and slots
        $totalEntries = RosterEntry::count() + RosterSlot::count();
        $confirmedEntries = RosterEntry::where('is_confirmed', true)->count() + 
                           RosterSlot::where('is_confirmed', true)->count();
        
        // Get current month's active rosters
        $currentMonth = Carbon::now()->format('Y-m');
        $activeRosters = Roster::where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->orderBy('name')
            ->get();
        
        // Get roster count by type
        $rostersByType = Roster::select('staff_type', DB::raw('count(*) as total'))
            ->groupBy('staff_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->staff_type => $item->total];
            });
        
        // Get roster count by department
        $rostersByDepartment = Roster::select('department_id', DB::raw('count(*) as total'))
            ->groupBy('department_id')
            ->get()
            ->map(function ($item) {
                $department = Department::find($item->department_id);
                return [
                    'department' => $department ? $department->name : 'Unknown',
                    'total' => $item->total
                ];
            });
        
        // Get recent rosters
        $recentRosters = Roster::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get upcoming end dates (rosters ending soon)
        $upcomingEndDates = Roster::where('end_date', '>=', Carbon::now())
            ->where('end_date', '<=', Carbon::now()->addDays(7))
            ->orderBy('end_date')
            ->get();
        
        return view('rosters.dashboard', compact(
            'totalRosters',
            'publishedRosters',
            'totalEntries',
            'confirmedEntries',
            'activeRosters',
            'rostersByType',
            'rostersByDepartment',
            'recentRosters',
            'upcomingEndDates'
        ));
    }

    /**
     * Show stats for a specific staff type
     */
    public function staffTypeStats($type)
    {
        // Validate type
        $validTypes = ['specialist_doctor', 'medical_officer', 'houseman_officer', 'nurse'];
        if (!in_array($type, $validTypes)) {
            return redirect()->route('roster.dashboard')->with('error', 'Invalid staff type.');
        }
        
        // Get rosters for this staff type
        $rosters = Roster::where('staff_type', $type)->get();
        
        // Get staff of this type
        $staff = Staff::where('type', $type)->get();
        
        // Get entries for this staff type's rosters
        $rosterIds = $rosters->pluck('id')->toArray();
        $entries = RosterEntry::whereIn('roster_id', $rosterIds)->get();
        $slots = RosterSlot::whereIn('roster_id', $rosterIds)->get();
        
        // Combine entries and slots count
        $totalItems = $entries->count() + $slots->count();
        
        // Get entry count by month (combining both entries and slots)
        $entriesByMonth = RosterEntry::whereIn('roster_id', $rosterIds)
            ->select(DB::raw('DATE_FORMAT(date, "%Y-%m") as month'), DB::raw('count(*) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->month => $item->total];
            });
            
        $slotsByMonth = RosterSlot::whereIn('roster_id', $rosterIds)
            ->select(DB::raw('DATE_FORMAT(date, "%Y-%m") as month'), DB::raw('count(*) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->month => $item->total];
            });
            
        // Merge the two collections
        $entriesByMonth = $entriesByMonth->map(function ($count, $month) use ($slotsByMonth) {
            return $count + ($slotsByMonth[$month] ?? 0);
        });
        
        // Add months that only exist in slots
        foreach ($slotsByMonth as $month => $count) {
            if (!isset($entriesByMonth[$month])) {
                $entriesByMonth[$month] = $count;
            }
        }
        
        // Staff with most entries (combining both entries and slots)
        $staffWithMostEntries = [];
        $staffCounts = [];
        
        // Count entries by staff
        foreach ($entries as $entry) {
            if (!isset($staffCounts[$entry->staff_id])) {
                $staffCounts[$entry->staff_id] = 0;
            }
            $staffCounts[$entry->staff_id]++;
        }
        
        // Count slots by staff
        foreach ($slots as $slot) {
            if (!isset($staffCounts[$slot->staff_id])) {
                $staffCounts[$slot->staff_id] = 0;
            }
            $staffCounts[$slot->staff_id]++;
        }
        
        // Sort by count
        arsort($staffCounts);
        
        // Take top 5
        $topStaffIds = array_slice(array_keys($staffCounts), 0, 5);
        
        // Format the result
        foreach ($topStaffIds as $staffId) {
            $staff = Staff::find($staffId);
            $staffWithMostEntries[] = [
                'staff_name' => $staff ? $staff->name : 'Unknown',
                'total_entries' => $staffCounts[$staffId]
            ];
        }
        
        // Get type label
        $staffTypeLabels = [
            'specialist_doctor' => 'Specialist Doctor',
            'medical_officer' => 'Medical Officer',
            'houseman_officer' => 'Houseman Officer',
            'nurse' => 'Nurse'
        ];
        
        return view('rosters.staff_type_stats', compact(
            'type',
            'staffTypeLabels',
            'rosters',
            'staff',
            'totalItems',
            'entriesByMonth',
            'staffWithMostEntries'
        ));
    }

    /**
     * Show the dashboard for a specific department
     */
    public function departmentStats($id)
    {
        $department = Department::findOrFail($id);
        
        // Get rosters for this department
        $rosters = Roster::where('department_id', $id)->get();
        
        // Get staff in this department
        $staff = Staff::where('department_id', $id)->get();
        
        // Get entries and slots for this department's rosters
        $rosterIds = $rosters->pluck('id')->toArray();
        $entries = RosterEntry::whereIn('roster_id', $rosterIds)->get();
        $slots = RosterSlot::whereIn('roster_id', $rosterIds)->get();
        
        // Combine entries and slots
        $totalItems = $entries->count() + $slots->count();
        
        // Get entry count by staff type for this department
        $entriesByStaffType = Roster::whereIn('id', $rosterIds)
            ->select('staff_type', DB::raw('count(*) as total'))
            ->groupBy('staff_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->staff_type => $item->total];
            });
        
        return view('rosters.department_stats', compact(
            'department',
            'rosters',
            'staff',
            'totalItems',
            'entriesByStaffType'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
