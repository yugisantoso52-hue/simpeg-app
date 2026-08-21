<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'nip',
        'password',
        'role_id',
        'pegawai_id', // Tambahkan field ini
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Relasi ke Model Pegawai
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function hasRole(array|string $roles): bool
    {
        if ((!$this->relationLoaded('role') || !$this->role) && $this->role_id) {
            $this->unsetRelation('role');
            $this->load('role');
        }

        if (!$this->role) {
            return false;
        }

        $allowedRoles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role->name, $allowedRoles, true);
    }
}