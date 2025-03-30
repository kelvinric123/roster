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
        // For MySQL, we need to modify the column type
        Schema::table('staff', function (Blueprint $table) {
            // First drop the existing enum constraint
            $table->string('type')->change();
        });

        // Then update existing data if needed
        DB::statement("UPDATE staff SET type = 'admin' WHERE email = 'admin@qmed.asia'");

        // Then add the constraint back with the new enum values
        DB::statement("ALTER TABLE staff MODIFY COLUMN type ENUM('admin', 'specialist_doctor', 'medical_officer', 'houseman_officer', 'nurse')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First convert any admin users to a different type to avoid constraint violation
        DB::statement("UPDATE staff SET type = 'specialist_doctor' WHERE type = 'admin'");

        // For MySQL, we need to modify the column type
        Schema::table('staff', function (Blueprint $table) {
            // First drop the existing enum constraint
            $table->string('type')->change();
        });

        // Then add the constraint back with the original enum values
        DB::statement("ALTER TABLE staff MODIFY COLUMN type ENUM('specialist_doctor', 'medical_officer', 'houseman_officer', 'nurse')");
    }
};
