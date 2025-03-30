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
            $table->string('staff_type')->nullable()->after('department_id')
                  ->comment('The staff type this shift setting applies to (specialist_doctor, medical_officer, etc.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roster_shift_settings', function (Blueprint $table) {
            $table->dropColumn('staff_type');
        });
    }
};
