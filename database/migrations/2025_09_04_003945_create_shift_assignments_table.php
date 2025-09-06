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
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_template_id')->constrained()->onDelete('cascade');
            $table->date('assignment_date');
            $table->enum('status', ['scheduled', 'confirmed', 'absent', 'completed', 'cancelled'])->default('scheduled');
            $table->time('actual_start_time')->nullable();
            $table->time('actual_end_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Ensure no duplicate assignments for the same employee, shift, and date
            $table->unique(['employee_id', 'shift_template_id', 'assignment_date'], 'unique_employee_shift_date');
            
            // Indexes for better performance
            $table->index(['assignment_date', 'status']);
            $table->index(['shift_template_id', 'assignment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
    }
};
