<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'type',
        'correction_time_in',
        'correction_time_out',
        'reason',
        'proof_image',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'date' => 'date',
        'correction_time_in' => 'datetime',
        'correction_time_out' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
