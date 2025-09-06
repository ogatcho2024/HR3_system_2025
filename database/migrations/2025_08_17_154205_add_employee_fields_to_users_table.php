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
            $table->string('lastname')->nullable()->after('name');
            $table->string('photo')->nullable()->after('email');
            $table->string('account_type')->default('employee')->after('photo');
            $table->boolean('otp_status')->default(false)->after('account_type');
            $table->string('phone')->nullable()->after('otp_status');
            $table->string('position')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'lastname',
                'photo', 
                'account_type',
                'otp_status',
                'phone',
                'position'
            ]);
        });
    }
};
