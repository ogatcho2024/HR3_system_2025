<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveDemandPrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'forecast_start_date',
        'forecast_end_date',
        'predicted_count',
        'model_version',
        'predicted_at',
    ];

    protected $casts = [
        'forecast_start_date' => 'date',
        'forecast_end_date' => 'date',
        'predicted_at' => 'datetime',
    ];
}
