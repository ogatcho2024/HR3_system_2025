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
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('ip_address');
            $table->integer('attempts')->default(1);
            $table->timestamp('last_attempt');
            $table->timestamp('blocked_until')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['email', 'ip_address']);
            $table->index('blocked_until');
            $table->index('last_attempt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};