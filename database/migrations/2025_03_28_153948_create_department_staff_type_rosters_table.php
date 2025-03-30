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
        Schema::create('department_staff_type_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->enum('staff_type', ['specialist_doctor', 'medical_officer', 'houseman_officer', 'nurse'])
                  ->comment('Four standard staff types across all departments');
            $table->enum('roster_type', ['oncall', 'shift'])->default('shift')
                  ->comment('Each staff type can have its own roster type within a department');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable()->comment('Additional settings specific to this staff type roster');
            $table->timestamps();
            $table->softDeletes();
            
            // Create a unique constraint on department_id and staff_type
            $table->unique(['department_id', 'staff_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_staff_type_rosters');
    }
};
