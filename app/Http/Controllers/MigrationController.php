<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class MigrationController extends Controller
{
    /**
     * Show the migration status page
     */
    public function showStatus()
    {
        $hasTable = Schema::hasTable('login_attempts');
        
        return view('admin.migration-status', compact('hasTable'));
    }
    
    /**
     * Create the login_attempts table
     */
    public function createTable(Request $request)
    {
        // Basic validation
        $request->validate([
            'confirm' => 'required|accepted'
        ]);

        try {
            // Check if table already exists
            if (Schema::hasTable('login_attempts')) {
                return back()->with('info', 'login_attempts table already exists');
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

            return back()->with('success', 'login_attempts table created successfully! Rate limiting is now active.');

        } catch (\Exception $e) {
            return back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }
}