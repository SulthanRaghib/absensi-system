<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationLink extends Model
{
    protected $fillable = [
        'token',
        'expires_at',
        'is_active',
        'jabatan_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }
}
