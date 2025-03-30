<?php

namespace App\Http\Controllers;

use App\Models\RosterEntry;
use App\Models\Roster;
use App\Models\Staff;
use App\Models\RosterShiftSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RosterEntryController extends Controller
{
    /**
     * Display a listing of the roster entries for a specific roster.
     */
    public function index(Roster $roster)
    {
        $entries = RosterEntry::with('staff')
            ->where('roster_id', $roster->id)
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(20);
            
        return view('roster_entries.index', compact('roster', 'entries'));
    }

    /**
     * Show the form for creating a new roster entry.
     */
    public function create(Request $request)
    {
        $roster = Roster::findOrFail($request->roster_id);
        
        // Get staff that match the roster type (and department if specified)
        $staff = Staff::where('type', $roster->staff_type)
            ->when($roster->department_id, function ($query) use ($roster) {
                return $query->where('department_id', $roster->department_id);
            })
            ->get();
        
        // Determine available shift types based on roster type
        $shiftTypes = [];
        if ($roster->roster_type == 'shift') {
            $shiftTypes = [
                'morning' => 'Pagi',
                'evening' => 'Petang',
                'night' => 'Malam',
            ];
        } else {
            // oncall roster
            $shiftTypes = [
                'oncall' => 'On Call',
                'standby' => 'Standby',
            ];
        }
        
        return view('roster_entries.create', compact('roster', 'staff', 'shiftTypes'));
    }

    /**
     * Store a newly created roster entry in storage.
     */
    public function store(Request $request, Roster $roster)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'shift_type' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'is_confirmed' => 'boolean',
        ]);

        // Add the roster ID from the route parameter
        $validated['roster_id'] = $roster->id;

        // Check if there are configured shift settings for this department and shift type
        if ($roster->department_id && $roster->roster_type == 'shift') {
            $shiftSetting = RosterShiftSetting::where('department_id', $roster->department_id)
                ->where('shift_type', $validated['shift_type'])
                ->first();
                
            if ($shiftSetting) {
                // Use the configured times
                $validated['start_time'] = $shiftSetting->start_time->format('H:i');
                $validated['end_time'] = $shiftSetting->end_time->format('H:i');
            }
        }

        // Check for overlapping shifts for the same staff member
        $overlapping = RosterEntry::where('staff_id', $validated['staff_id'])
            ->where('date', $validated['date'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();
            
        if ($overlapping) {
            return back()->withInput()->with('error', 'This staff member already has an overlapping shift on this date.');
        }

        $entry = RosterEntry::create($validated);

        return redirect()->route('manage.rosters.show', $roster)
            ->with('success', 'Roster entry created successfully.');
    }

    /**
     * Display the specified roster entry.
     */
    public function show(RosterEntry $entry)
    {
        $entry->load('roster', 'staff');
        return view('roster_entries.show', compact('entry'));
    }

    /**
     * Show the form for editing the specified roster entry.
     */
    public function edit(RosterEntry $entry)
    {
        $entry->load('roster');
        
        // Get staff that match the roster type (and department if specified)
        $staff = Staff::where('type', $entry->roster->staff_type)
            ->when($entry->roster->department_id, function ($query) use ($entry) {
                return $query->where('department_id', $entry->roster->department_id);
            })
            ->get();
        
        // Determine available shift types based on roster type
        $shiftTypes = [];
        if ($entry->roster->roster_type == 'shift') {
            $shiftTypes = [
                'morning' => 'Pagi',
                'evening' => 'Petang',
                'night' => 'Malam',
            ];
        } else {
            // oncall roster
            $shiftTypes = [
                'oncall' => 'On Call',
                'standby' => 'Standby',
            ];
        }
        
        return view('roster_entries.edit', compact('entry', 'staff', 'shiftTypes'));
    }

    /**
     * Update the specified roster entry in storage.
     */
    public function update(Request $request, RosterEntry $entry)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'shift_type' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'is_confirmed' => 'boolean',
        ]);

        // Check for overlapping shifts for the same staff member (excluding this entry)
        $overlapping = RosterEntry::where('staff_id', $validated['staff_id'])
            ->where('date', $validated['date'])
            ->where('id', '!=', $entry->id)
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();
            
        if ($overlapping) {
            return back()->withInput()->with('error', 'This staff member already has an overlapping shift on this date.');
        }

        $entry->update($validated);

        return redirect()->route('manage.rosters.show', $entry->roster_id)
            ->with('success', 'Roster entry updated successfully.');
    }

    /**
     * Remove the specified roster entry from storage.
     */
    public function destroy(RosterEntry $entry)
    {
        $rosterId = $entry->roster_id;
        $entry->delete();

        return redirect()->route('manage.rosters.show', $rosterId)
            ->with('success', 'Roster entry deleted successfully.');
    }

    /**
     * Toggle confirmation status of a roster entry
     */
    public function toggleConfirmation(RosterEntry $entry)
    {
        $entry->update([
            'is_confirmed' => !$entry->is_confirmed
        ]);
        
        $status = $entry->is_confirmed ? 'confirmed' : 'unconfirmed';
        
        return back()->with('success', "Shift {$status} successfully.");
    }

    /**
     * Bulk create multiple roster entries
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'roster_id' => 'required|exists:rosters,id',
            'staff_id' => 'required|exists:staff,id',
            'dates' => 'required|array',
            'dates.*' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'shift_type' => 'required|string|max:50',
            'is_confirmed' => 'boolean',
        ]);

        $roster = Roster::findOrFail($validated['roster_id']);
        $successCount = 0;
        $errorCount = 0;

        // Check if there are configured shift settings for this department and shift type
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];
        
        if ($roster->department_id && $roster->roster_type == 'shift') {
            $shiftSetting = RosterShiftSetting::where('department_id', $roster->department_id)
                ->where('shift_type', $validated['shift_type'])
                ->first();
                
            if ($shiftSetting) {
                // Use the configured times
                $startTime = $shiftSetting->start_time->format('H:i');
                $endTime = $shiftSetting->end_time->format('H:i');
            }
        }

        DB::beginTransaction();
        try {
            foreach ($validated['dates'] as $date) {
                // Check for overlapping shifts
                $overlapping = RosterEntry::where('staff_id', $validated['staff_id'])
                    ->where('date', $date)
                    ->where(function($query) use ($startTime, $endTime) {
                        $query->whereBetween('start_time', [$startTime, $endTime])
                            ->orWhereBetween('end_time', [$startTime, $endTime])
                            ->orWhere(function($q) use ($startTime, $endTime) {
                                $q->where('start_time', '<=', $startTime)
                                  ->where('end_time', '>=', $endTime);
                            });
                    })
                    ->exists();
                
                if (!$overlapping) {
                    RosterEntry::create([
                        'roster_id' => $validated['roster_id'],
                        'staff_id' => $validated['staff_id'],
                        'date' => $date,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'shift_type' => $validated['shift_type'],
                        'is_confirmed' => $validated['is_confirmed'] ?? false,
                    ]);
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
            
            DB::commit();
            
            $message = "{$successCount} shifts created successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} shifts could not be created due to overlapping shifts.";
            }
            
            return redirect()->route('manage.rosters.show', $roster)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred while creating shifts: ' . $e->getMessage());
        }
    }

    /**
     * Process bulk updates for roster entries (add/delete) from interactive interface
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'roster_id' => 'required|exists:rosters,id',
            'changes' => 'required|array',
            'changes.*.action' => 'required|in:create,delete',
        ]);

        $rosterId = $validated['roster_id'];
        $roster = Roster::findOrFail($rosterId);
        $changes = $validated['changes'];

        // Track results
        $created = 0;
        $deleted = 0;
        $newEntries = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($changes as $change) {
                if ($change['action'] === 'create') {
                    // Get the entry data from the request
                    $entryData = [
                        'roster_id' => $rosterId,
                        'staff_id' => $change['entry']['staff_id'],
                        'date' => $change['entry']['date'],
                        'shift_type' => $change['entry']['shift_type'],
                        'start_time' => $change['entry']['start_time'],
                        'end_time' => $change['entry']['end_time'],
                        'is_confirmed' => $change['entry']['is_confirmed'] ?? false,
                    ];

                    // Check if there are configured shift settings for this department and shift type
                    if ($roster->department_id && $roster->roster_type == 'shift') {
                        $shiftSetting = RosterShiftSetting::where('department_id', $roster->department_id)
                            ->where('shift_type', $entryData['shift_type'])
                            ->first();
                            
                        if ($shiftSetting) {
                            // Use the configured times
                            $entryData['start_time'] = $shiftSetting->start_time->format('H:i');
                            $entryData['end_time'] = $shiftSetting->end_time->format('H:i');
                        }
                    }

                    // Check for overlapping shifts
                    $overlapping = RosterEntry::where('staff_id', $entryData['staff_id'])
                        ->where('date', $entryData['date'])
                        ->where(function($query) use ($entryData) {
                            $query->whereBetween('start_time', [$entryData['start_time'], $entryData['end_time']])
                                ->orWhereBetween('end_time', [$entryData['start_time'], $entryData['end_time']])
                                ->orWhere(function($q) use ($entryData) {
                                    $q->where('start_time', '<=', $entryData['start_time'])
                                      ->where('end_time', '>=', $entryData['end_time']);
                                });
                        })
                        ->exists();

                    if (!$overlapping) {
                        $entry = RosterEntry::create($entryData);
                        $created++;

                        // Store mapping between temporary ID and new DB ID
                        $newEntries[] = [
                            'temp_id' => $change['entry']['id'],
                            'id' => $entry->id
                        ];
                    } else {
                        $errors[] = "Could not create shift for date {$entryData['date']} due to overlap.";
                    }
                } elseif ($change['action'] === 'delete') {
                    $entryId = $change['entry_id'];
                    $entry = RosterEntry::where('id', $entryId)
                        ->where('roster_id', $rosterId)
                        ->first();

                    if ($entry) {
                        $entry->delete();
                        $deleted++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Changes saved successfully. Created: {$created}, Deleted: {$deleted}" . 
                            (count($errors) > 0 ? ", Errors: " . count($errors) : ""),
                'new_entries' => $newEntries,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing changes: ' . $e->getMessage()
            ], 500);
        }
    }
}
