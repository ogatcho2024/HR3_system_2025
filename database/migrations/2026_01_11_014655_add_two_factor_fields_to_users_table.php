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
        Schema::table('users', function (Blueprint $table) {
            $table->string('otp_code', 6)->nullable()->after('otp_status');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            $table->boolean('otp_verified')->default(false)->after('otp_expires_at');
            $table->boolean('require_2fa')->default(true)->after('otp_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp_code', 'otp_expires_at', 'otp_verified', 'require_2fa']);
        });
    }
};
