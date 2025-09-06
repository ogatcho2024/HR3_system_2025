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
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Policy name
            $table->string('leave_type'); // sick, vacation, personal, etc.
            $table->decimal('annual_entitlement', 5, 2); // Annual days entitled
            $table->integer('max_consecutive_days')->nullable(); // Max consecutive days allowed
            $table->boolean('requires_approval')->default(true);
            $table->integer('min_notice_days')->default(0); // Minimum notice required
            $table->boolean('allow_carry_forward')->default(false);
            $table->integer('max_carry_forward_days')->nullable(); // Max days that can be carried forward
            $table->integer('carry_forward_expiry_months')->nullable(); // Months after which carried forward expires
            $table->text('description')->nullable();
            $table->json('applicable_roles')->nullable(); // Roles this policy applies to
            $table->json('applicable_departments')->nullable(); // Departments this policy applies to
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
    }
};
