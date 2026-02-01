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
        Schema::create('user_syncs', function (Blueprint $table) {
            $table->id();
            $table->string('external_user_id')->unique(); // ID from admin.cranecali-ms.com
            $table->unsignedBigInteger('user_id')->nullable(); // Local user ID
            $table->json('external_data'); // Full payload from external system
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'deleted'])->default('pending');
            $table->integer('sync_attempts')->default(0);
            $table->timestamp('last_sync_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('source_service')->default('admin.cranecali-ms.com');
            $table->string('api_version')->nullable();
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['external_user_id']);
            $table->index(['user_id']);
            $table->index(['sync_status']);
            $table->index(['source_service']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_syncs');
    }
};
