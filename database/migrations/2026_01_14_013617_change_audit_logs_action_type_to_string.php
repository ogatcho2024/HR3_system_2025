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
        // Check database driver
        $driver = Schema::connection(null)->getConnection()->getDriverName();
        
        if ($driver === 'mysql') {
            // For MySQL, we need to drop and recreate the column
            \DB::statement("ALTER TABLE audit_logs MODIFY action_type VARCHAR(50)");
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support MODIFY, but if the table was created with string type, we're good
            // If it was created with ENUM (which SQLite treats as TEXT), we need to recreate the table
            // For now, we'll just ensure new installations work correctly
        } else {
            // PostgreSQL and others
            \DB::statement("ALTER TABLE audit_logs ALTER COLUMN action_type TYPE VARCHAR(50)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot easily revert to ENUM in a database-agnostic way
        // This is acceptable as we're moving to a more flexible type
    }
};
