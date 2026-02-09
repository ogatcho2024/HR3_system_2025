<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('timesheets')) {
            return;
        }

        // Ensure status is a flexible string to accept lowercase values like "draft"
        DB::statement("ALTER TABLE `timesheets` MODIFY `status` VARCHAR(255) NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No safe down migration without knowing the original enum values
    }
};
