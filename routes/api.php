<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Department;
use App\Models\DepartmentStaffTypeRoster;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Get the roster type for a department and staff type
Route::get('/departments/{department}/staff-type-roster/{staffType}', function (Department $department, $staffType) {
    $staffTypeRoster = DepartmentStaffTypeRoster::where('department_id', $department->id)
        ->where('staff_type', $staffType)
        ->first();
        
    if ($staffTypeRoster) {
        return response()->json([
            'roster_type' => $staffTypeRoster->roster_type,
            'department_id' => $department->id,
            'staff_type' => $staffType,
        ]);
    }
    
    // Fallback to department default
    return response()->json([
        'roster_type' => $department->roster_type,
        'department_id' => $department->id,
        'staff_type' => $staffType,
    ]);
}); 