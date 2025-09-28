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
        Schema::create('departments', function (Blueprint $table) {
            $table->id('department_id');
            $table->string('department_name', 100);
            $table->string('department_code', 10)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
            
            // Foreign key constraint (assuming employees table exists)
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
            
            // Indexes for better performance
            $table->index(['status']);
            $table->index(['department_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
