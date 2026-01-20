<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable 2FA for all users who have it disabled or NULL
        DB::table('users')
            ->whereNull('require_2fa')
            ->orWhere('require_2fa', false)
            ->orWhere('require_2fa', 0)
            ->update([
                'require_2fa' => true,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't reverse this - we want 2FA enabled for security
        // If needed, manually disable per user
    }
};
