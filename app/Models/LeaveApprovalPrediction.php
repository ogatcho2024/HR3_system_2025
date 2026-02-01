<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveApprovalPrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'predicted_label',
        'predicted_probability',
        'model_version',
        'predicted_at',
    ];

    protected $casts = [
        'predicted_at' => 'datetime',
        'predicted_probability' => 'float',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}
