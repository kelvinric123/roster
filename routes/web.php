<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\RosterDashboardController;
use App\Http\Controllers\DepartmentStaffTypeRosterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffRosterViewController;
use App\Http\Controllers\TeamLeaderController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\SortableRosterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Department Management Routes
    Route::resource('departments', DepartmentController::class);
    Route::patch('departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status');
    
    // Department Staff Type Roster Routes
    Route::get('departments/{department}/staff-type-rosters', [DepartmentStaffTypeRosterController::class, 'index'])->name('departments.staff-type-rosters.index');
    Route::post('departments/{department}/staff-type-rosters', [DepartmentStaffTypeRosterController::class, 'update'])->name('departments.staff-type-rosters.update');

    // Staff Management Routes
    Route::resource('staff', StaffController::class);
    Route::patch('staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
    
    // Team Leader Management Routes
    Route::resource('team-leaders', TeamLeaderController::class);
    
    // Staff Settings Routes
    Route::get('staff-settings', [StaffController::class, 'settings'])->name('staff.settings');
    Route::get('staff-settings/shift', [StaffController::class, 'shiftSettings'])->name('staff.shift-settings');
    Route::get('staff-settings/department-shifts', [StaffController::class, 'departmentShifts'])->name('staff.department-shifts');
    Route::get('staff-settings/oncall-schedules', [StaffController::class, 'oncallSchedules'])->name('staff.oncall-schedules');
    Route::post('staff-settings/oncall-schedules', [StaffController::class, 'storeOncallSchedule'])->name('staff.store-oncall-schedule');
    Route::get('staff-settings/oncall-assignments', [StaffController::class, 'oncallAssignments'])->name('staff.oncall-assignments');
    Route::get('staff-settings/type-settings', [StaffController::class, 'typeSettings'])->name('staff.type-settings');
    Route::put('staff-settings/type-settings/{type}', [StaffController::class, 'updateTypeSettings'])->name('staff.updateTypeSettings');

    // Calendar Management Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::resource('calendar', HolidayController::class);
        Route::patch('calendar/{holiday}/toggle-status', [HolidayController::class, 'toggleStatus'])->name('calendar.toggle-status');
    });

    // Rosters Management routes
    Route::prefix('manage')->name('manage.')->group(function () {
        Route::resource('rosters', RosterController::class);
        Route::get('/rosters/type/{type}', [RosterController::class, 'byType'])->name('rosters.by_type');
        Route::patch('/rosters/{roster}/toggle-publish', [RosterController::class, 'togglePublish'])->name('rosters.toggle_publish');
        Route::post('/rosters/{roster}/publish', [RosterController::class, 'publish'])->name('rosters.publish');
        Route::post('/rosters/{roster}/unpublish', [RosterController::class, 'unpublish'])->name('rosters.unpublish');
        Route::post('/rosters/{roster}/entries/bulk', [RosterController::class, 'bulkStoreEntries'])->name('rosters.entries.bulk-store');
        
        // Sortable Roster routes
        Route::get('/sortable-rosters/create', [SortableRosterController::class, 'create'])->name('sortable-rosters.create');
        Route::post('/sortable-rosters', [SortableRosterController::class, 'store'])->name('sortable-rosters.store');
        Route::get('/sortable-rosters/{sortableRoster}', [SortableRosterController::class, 'show'])->name('sortable-rosters.show');
        Route::post('/sortable-rosters/{sortableRoster}/update-order', [SortableRosterController::class, 'updateOrder'])->name('sortable-rosters.update-order');
        Route::post('/sortable-rosters/{sortableRoster}/generate-assignments', [SortableRosterController::class, 'generateAssignments'])->name('sortable-rosters.generate-assignments');
        Route::post('/sortable-rosters/{sortableRoster}/publish', [SortableRosterController::class, 'publish'])->name('sortable-rosters.publish');
        Route::post('/sortable-rosters/{sortableRoster}/save-assignments', [SortableRosterController::class, 'saveAssignments'])->name('sortable-rosters.save-assignments');
    });

    // Staff View Sortable-Rosters routes
    Route::prefix('staff-view')->name('staff.')->group(function () {
        Route::get('/rosters', [StaffRosterViewController::class, 'index'])->name('rosters.index');
        Route::get('/rosters/{roster}', [StaffRosterViewController::class, 'show'])->name('rosters.show');
    });

    // Roster Entry routes
    Route::post('/rosters/{roster}/entries', [RosterController::class, 'storeEntry'])->name('rosters.entries.store');
    Route::delete('/rosters/entries/{entry}', [RosterController::class, 'destroyEntry'])->name('rosters.entries.destroy');
    
    // Roster Slot routes
    Route::post('/rosters/{roster}/slots', [RosterController::class, 'storeSlot'])->name('rosters.slots.store');
    Route::delete('/rosters/slots/{slot}', [RosterController::class, 'destroySlot'])->name('rosters.slots.destroy');
    Route::post('/rosters/{roster}/bulk-slots', [RosterController::class, 'bulkStoreEntries'])->name('rosters.slots.bulk_store');

    // Roster Dashboard Routes
    Route::get('/roster-dashboard', [RosterDashboardController::class, 'index'])->name('roster.dashboard');
    Route::get('/roster-dashboard/staff-type/{type}', [RosterDashboardController::class, 'staffTypeStats'])->name('roster.staff_type_stats');
    Route::get('/roster-dashboard/department/{id}', [RosterDashboardController::class, 'departmentStats'])->name('roster.department_stats');
});

Route::get('/test-slots', function() {
    $slots = \App\Models\RosterSlot::all();
    return response()->json([
        'slots_count' => $slots->count(),
        'slots' => $slots
    ]);
});

// Catchall for different bulk slot storage URLs
Route::post('/rosters/{roster}/bulk-slots', [RosterController::class, 'bulkStoreEntries'])->name('rosters.catchall.bulk_slots');

require __DIR__.'/auth.php';
