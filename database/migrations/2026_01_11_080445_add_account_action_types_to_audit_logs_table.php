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
        // Modify the action_type ENUM to include new account-related actions
        \DB::statement("ALTER TABLE audit_logs MODIFY action_type ENUM(
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
            'account_created',
            'account_updated',
            'account_deleted',
            'password_changed',
            'email_changed',
            'role_changed',
            'other'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM values
        \DB::statement("ALTER TABLE audit_logs MODIFY action_type ENUM(
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
        )");
    }
};
