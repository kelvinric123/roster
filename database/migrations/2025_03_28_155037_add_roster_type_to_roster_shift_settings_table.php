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
        Schema::table('roster_shift_settings', function (Blueprint $table) {
            $table->enum('roster_type', ['oncall', 'shift'])->default('shift')->after('shift_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roster_shift_settings', function (Blueprint $table) {
            $table->dropColumn('roster_type');
        });
    }
};
