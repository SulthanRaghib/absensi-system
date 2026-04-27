<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasUuids;

    public const REFERENCE_PERMISSION = 'permission';
    public const REFERENCE_ATTENDANCE_CORRECTION = 'attendance_correction';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ARCHIVED = 'archived';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'reference_type',
        'reference_id',
        'is_cleared',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_cleared' => 'boolean',
    ];

    /**
     * Get the permission that this notification references
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'reference_id')
            ->where('reference_type', self::REFERENCE_PERMISSION);
    }

    /**
     * Get the attendance correction that this notification references
     */
    public function attendanceCorrection(): BelongsTo
    {
        return $this->belongsTo(AttendanceCorrection::class, 'reference_id')
            ->where('reference_type', self::REFERENCE_ATTENDANCE_CORRECTION);
    }

    /**
     * Scope untuk notifikasi yang belum di-clear dan masih pending
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_cleared', false)
            ->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope untuk notifikasi yang sudah di-clear
     */
    public function scopeCleared(Builder $query): Builder
    {
        return $query->where('is_cleared', true);
    }

    /**
     * Scope untuk notifikasi berdasarkan user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $userId);
    }

    /**
     * Mark notification as cleared (soft-clear, tidak delete fisik)
     */
    public function markAsCleared(): bool
    {
        return $this->update(['is_cleared' => true]);
    }

    /**
     * Mark notification as archived when reference is approved/rejected
     */
    public function markAsArchived(string $referenceStatus): bool
    {
        return $this->update([
            'status' => $referenceStatus === self::STATUS_APPROVED ? self::STATUS_APPROVED : self::STATUS_REJECTED,
            'is_cleared' => true,
        ]);
    }

    public function isReference(string $referenceType): bool
    {
        return $this->reference_type === $referenceType;
    }
}
