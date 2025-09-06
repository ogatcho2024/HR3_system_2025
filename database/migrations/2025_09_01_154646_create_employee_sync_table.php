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
        Schema::create('employee_sync', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique(); // ID from external microservice
            $table->unsignedBigInteger('employee_id')->nullable(); // Local employee ID
            $table->json('external_data'); // Raw data from external API
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'deleted'])->default('pending');
            $table->timestamp('last_sync_at')->nullable();
            $table->text('sync_error')->nullable(); // Error message if sync failed
            $table->integer('sync_attempts')->default(0);
            $table->string('source_service')->default('employee-microservice'); // Name of source microservice
            $table->string('api_version')->nullable(); // Version of external API
            $table->timestamps();
            
            // Indexes
            $table->index('external_id');
            $table->index('sync_status');
            $table->index('last_sync_at');
            
            // Foreign key constraint
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_sync');
    }
};
