<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all oncall department staff type rosters
        $oncallRosters = DB::table('department_staff_type_rosters')
            ->where('roster_type', 'oncall')
            ->get();
        
        // For each oncall roster, add default title values to settings
        foreach ($oncallRosters as $roster) {
            $settings = json_decode($roster->settings, true) ?: [];
            $oncallStaffCount = $settings['oncall_staff_count'] ?? 2;
            
            // Create default oncall staff titles array (oncall 1, oncall 2, etc.)
            $oncallStaffTitles = [];
            for ($i = 1; $i <= $oncallStaffCount; $i++) {
                $oncallStaffTitles[] = "oncall {$i}";
            }
            
            // Add oncall_staff_titles to settings
            $settings['oncall_staff_titles'] = $oncallStaffTitles;
            
            // Update the record
            DB::table('department_staff_type_rosters')
                ->where('id', $roster->id)
                ->update([
                    'settings' => json_encode($settings)
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove oncall_staff_titles from settings
        DB::table('department_staff_type_rosters')
            ->where('roster_type', 'oncall')
            ->update([
                'settings' => DB::raw("JSON_REMOVE(settings, '$.oncall_staff_titles')")
            ]);
    }
};
