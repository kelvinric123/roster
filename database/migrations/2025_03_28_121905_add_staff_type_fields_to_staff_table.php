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
        Schema::table('staff', function (Blueprint $table) {
            // Specialist Doctor fields
            $table->string('qualification')->nullable();
            
            // Medical Officer fields
            $table->string('department')->nullable();
            
            // Houseman Officer fields
            $table->string('current_rotation')->nullable();
            $table->integer('graduation_year')->nullable();
            
            // Nurse fields
            $table->string('nursing_unit')->nullable();
            $table->string('nurse_level')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn([
                'qualification',
                'department',
                'current_rotation',
                'graduation_year',
                'nursing_unit',
                'nurse_level'
            ]);
        });
    }
};
