<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserSync extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_user_id',
        'user_id',
        'external_data',
        'sync_status',
        'sync_attempts',
        'last_sync_at',
        'error_message',
        'source_service',
        'api_version',
    ];

    protected $casts = [
        'external_data' => 'array',
        'last_sync_at' => 'datetime',
        'sync_attempts' => 'integer',
    ];

    /**
     * Get the user associated with this sync record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for pending syncs.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('sync_status', 'pending');
    }

    /**
     * Scope for synced records.
     */
    public function scopeSynced(Builder $query): Builder
    {
        return $query->where('sync_status', 'synced');
    }

    /**
     * Scope for failed syncs.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('sync_status', 'failed');
    }

    /**
     * Scope to find by external user ID.
     */
    public function scopeByExternalId(Builder $query, string $externalId): Builder
    {
        return $query->where('external_user_id', $externalId);
    }

    /**
     * Mark the sync as successfully completed.
     */
    public function markAsSynced(): void
    {
        $this->update([
            'sync_status' => 'synced',
            'last_sync_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark the sync as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'sync_status' => 'failed',
            'last_sync_at' => now(),
            'error_message' => $errorMessage,
            'sync_attempts' => $this->sync_attempts + 1,
        ]);
    }

    /**
     * Update external data payload.
     */
    public function updateExternalData(array $data): void
    {
        $this->update([
            'external_data' => $data,
        ]);
    }

    /**
     * Increment sync attempts.
     */
    public function incrementSyncAttempts(): void
    {
        $this->increment('sync_attempts');
    }
}
