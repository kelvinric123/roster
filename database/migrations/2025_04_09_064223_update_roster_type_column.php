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
        // Directly update the column to support the new value
        DB::statement("ALTER TABLE rosters MODIFY roster_type ENUM('oncall', 'shift', 'sortable')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original values
        DB::statement("ALTER TABLE rosters MODIFY roster_type ENUM('oncall', 'shift')");
    }
};
