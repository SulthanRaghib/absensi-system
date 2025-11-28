<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'jabatan_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relationship with absences
     */
    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user can access Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        if ($panel->getId() === 'user') {
            return $this->role === 'user' || $this->isAdmin();
        }

        return false;
    }

    /**
     * Get today's absence
     */
    public function getTodayAbsence(): ?Absence
    {
        return $this->absences()
            ->whereDate('tanggal', today())
            ->first();
    }

    /**
     * Check if user has checked in today
     */
    public function hasCheckedInToday(): bool
    {
        return $this->absences()
            ->whereDate('tanggal', today())
            ->whereNotNull('jam_masuk')
            ->exists();
    }

    /**
     * Check if user has checked out today
     */
    public function hasCheckedOutToday(): bool
    {
        return $this->absences()
            ->whereDate('tanggal', today())
            ->whereNotNull('jam_pulang')
            ->exists();
    }
}
