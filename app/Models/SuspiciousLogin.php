<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuspiciousLogin extends Model
{
    protected $fillable = [
        'attempted_email',
        'ip_address',
        'previous_user_id',
        'blocked_at',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
    ];

    public function previousUser()
    {
        return $this->belongsTo(User::class, 'previous_user_id');
    }
}
