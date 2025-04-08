<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('roster_entries');
        Schema::dropIfExists('roster_shift_settings');
    }

    /**
     * Reverse the migrations.
     * Note: This is intentionally left empty as we don't want to recreate these tables.
     */
    public function down(): void
    {
        // We're not recreating these tables in case of rollback
    }
};
