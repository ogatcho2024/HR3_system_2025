<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EmployeeSync extends Model
{
    protected $table = 'employee_sync';
    
    protected $fillable = [
        'external_id',
        'employee_id',
        'external_data',
        'sync_status',
        'last_sync_at',
        'sync_error',
        'sync_attempts',
        'source_service',
        'api_version'
    ];
    
    protected $casts = [
        'external_data' => 'array',
        'last_sync_at' => 'datetime',
        'sync_attempts' => 'integer'
    ];
    
    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }
    
    public function scopeFailed($query)
    {
        return $query->where('sync_status', 'failed');
    }
    
    public function scopeSynced($query)
    {
        return $query->where('sync_status', 'synced');
    }
    
    public function scopeByExternalId($query, $externalId)
    {
        return $query->where('external_id', $externalId);
    }
    
    // Methods
    public function markAsSynced()
    {
        $this->update([
            'sync_status' => 'synced',
            'last_sync_at' => now(),
            'sync_error' => null
        ]);
    }
    
    public function markAsFailed($errorMessage)
    {
        $this->update([
            'sync_status' => 'failed',
            'sync_error' => $errorMessage,
            'sync_attempts' => $this->sync_attempts + 1
        ]);
    }
    
    public function markAsDeleted()
    {
        $this->update([
            'sync_status' => 'deleted',
            'last_sync_at' => now()
        ]);
    }
    
    public function updateExternalData(array $data)
    {
        $this->update([
            'external_data' => $data,
            'sync_status' => 'pending',
            'last_sync_at' => now()
        ]);
    }
    
    public function shouldRetrySync(): bool
    {
        return $this->sync_status === 'failed' && $this->sync_attempts < 3;
    }
    
    public function isStale(): bool
    {
        return $this->last_sync_at && $this->last_sync_at->diffInMinutes(now()) > 60;
    }
}
