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
        Schema::create('roster_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roster_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('shift_type'); // morning, evening, night, oncall, standby
            $table->time('start_time')->default('00:00:00');
            $table->time('end_time')->default('23:59:59');
            $table->boolean('is_confirmed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add unique constraint to prevent duplicate slots
            $table->unique(['roster_id', 'staff_id', 'date', 'shift_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_slots');
    }
};
