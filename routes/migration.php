<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/*
 * Manual Migration Route for Production
 * This is a temporary route to create the login_attempts table on production
 * Access: /run-migration?secret=your-secret-key
 */

Route::get('/run-migration', function () {
    // Basic security - you can change this secret
    $secret = request('secret');
    if ($secret !== 'create-login-table-2025') {
        abort(403, 'Unauthorized');
    }

    try {
        // Check if table already exists
        if (Schema::hasTable('login_attempts')) {
            return response()->json([
                'status' => 'success',
                'message' => 'login_attempts table already exists'
            ]);
        }

        // Create the table
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

        return response()->json([
            'status' => 'success',
            'message' => 'login_attempts table created successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Migration failed: ' . $e->getMessage()
        ], 500);
    }
});