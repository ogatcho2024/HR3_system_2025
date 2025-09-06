<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reimbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'type',
        'amount',
        'description',
        'status',
        'receipt_path',
        'submitted_date',
        'approved_date',
        'approved_by',
        'rejection_reason',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'submitted_date' => 'date',
        'approved_date' => 'date',
    ];

    /**
     * Get the user that owns the reimbursement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee that owns the reimbursement.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the approver of the reimbursement.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for pending reimbursements.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved reimbursements.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected reimbursements.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
