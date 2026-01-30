<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add payroll tracking fields to timesheets table for real-time integration.
     */
    public function up(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            // Track if timesheet has been sent to payroll
            $table->boolean('sent_to_payroll')->default(false)->after('status');
            
            // Track when it was sent
            $table->timestamp('payroll_sent_at')->nullable()->after('sent_to_payroll');
            
            // Track number of send attempts
            $table->unsignedInteger('payroll_send_attempts')->default(0)->after('payroll_sent_at');
            
            // Store last error message
            $table->text('payroll_last_error')->nullable()->after('payroll_send_attempts');
            
            // Add index for querying unsent approved timesheets
            $table->index(['status', 'sent_to_payroll'], 'idx_payroll_sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropIndex('idx_payroll_sync_status');
            $table->dropColumn([
                'sent_to_payroll',
                'payroll_sent_at',
                'payroll_send_attempts',
                'payroll_last_error'
            ]);
        });
    }
};
