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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action_type', [
                'login',
                'logout',
                'failed_login',
                'otp_verified',
                'otp_failed',
                'create',
                'update',
                'delete',
                'view',
                'export',
                'other'
            ])->index();
            $table->text('description');
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('affected_table')->nullable()->index();
            $table->unsignedBigInteger('affected_record_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->integer('login_attempt_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['action_type', 'created_at']);
            $table->index(['affected_table', 'affected_record_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
