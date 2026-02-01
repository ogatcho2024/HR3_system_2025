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
        Schema::create('leave_approval_predictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_request_id');
            $table->string('predicted_label', 20);
            $table->decimal('predicted_probability', 6, 5);
            $table->string('model_version', 50);
            $table->timestamp('predicted_at');
            $table->timestamps();

            $table->index(['leave_request_id', 'predicted_at'], 'leave_approval_predictions_request_predicted_at_idx');
            $table->foreign('leave_request_id')
                ->references('id')
                ->on('leave_requests')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_approval_predictions');
    }
};
