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
        Schema::create('leave_balance_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_balance_id')->constrained('leave_balances')->onDelete('cascade');
            $table->foreignId('adjusted_by')->constrained('users')->onDelete('cascade');
            $table->string('adjustment_type'); // total_entitled, used, carried_forward
            $table->integer('old_value');
            $table->integer('new_value');
            $table->text('reason');
            $table->timestamps();
            
            $table->index(['leave_balance_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balance_adjustments');
    }
};
