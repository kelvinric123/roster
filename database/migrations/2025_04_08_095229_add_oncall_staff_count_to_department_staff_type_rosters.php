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
        // Update existing records to include oncall_staff_count in settings
        DB::table('department_staff_type_rosters')
            ->where('roster_type', 'oncall')
            ->update([
                'settings' => DB::raw("JSON_SET(COALESCE(settings, '{}'), '$.oncall_staff_count', 2)")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove oncall_staff_count from settings
        DB::table('department_staff_type_rosters')
            ->where('roster_type', 'oncall')
            ->update([
                'settings' => DB::raw("JSON_REMOVE(settings, '$.oncall_staff_count')")
            ]);
    }
};
