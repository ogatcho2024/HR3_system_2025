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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('leave_type'); // sick, vacation, personal, etc.
            $table->integer('year');
            $table->decimal('total_entitled', 5, 2)->default(0); // Total days entitled for the year
            $table->decimal('used', 5, 2)->default(0); // Days used
            $table->decimal('pending', 5, 2)->default(0); // Days in pending requests
            $table->decimal('available', 5, 2)->default(0); // Available days (calculated field)
            $table->decimal('carried_forward', 5, 2)->default(0); // Days carried from previous year
            $table->date('expires_at')->nullable(); // When carried forward days expire
            $table->timestamps();
            
            $table->unique(['user_id', 'leave_type', 'year']);
            $table->index(['user_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
