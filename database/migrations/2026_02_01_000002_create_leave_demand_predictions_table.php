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
        Schema::create('leave_demand_predictions', function (Blueprint $table) {
            $table->id();
            $table->date('forecast_start_date');
            $table->date('forecast_end_date');
            $table->integer('predicted_count');
            $table->string('model_version', 50);
            $table->timestamp('predicted_at');
            $table->timestamps();

            $table->index(['forecast_start_date', 'forecast_end_date'], 'leave_demand_predictions_forecast_range_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_demand_predictions');
    }
};
