<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'asc')->get();
        return view('settings.calendar.index', compact('holidays'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings.calendar.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'is_recurring' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Holiday::create([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
            'is_recurring' => $request->has('is_recurring') ? 1 : 0,
            'status' => 'active',
        ]);

        return redirect()->route('settings.calendar.index')
            ->with('success', 'Holiday added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $holiday)
    {
        return view('settings.calendar.show', compact('holiday'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $holiday)
    {
        return view('settings.calendar.edit', compact('holiday'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'is_recurring' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $holiday->update([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
            'is_recurring' => $request->has('is_recurring') ? 1 : 0,
        ]);

        return redirect()->route('settings.calendar.index')
            ->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->route('settings.calendar.index')
            ->with('success', 'Holiday deleted successfully.');
    }

    /**
     * Toggle the status of the specified holiday.
     */
    public function toggleStatus(Holiday $holiday)
    {
        $holiday->update([
            'status' => $holiday->status === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()->route('settings.calendar.index')
            ->with('success', 'Holiday status updated successfully.');
    }
}
