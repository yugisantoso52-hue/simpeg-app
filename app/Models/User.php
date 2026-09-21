<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, RecordsSyncOutbox;

    protected $fillable = [
        'name',
        'email',
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

    public function attendanceLocation(): HasOne
    {
        return $this->hasOne(EmployeeAttendanceLocation::class, 'user_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class, 'user_id');
    }

    public function todayAttendance(): HasOne
    {
        return $this->hasOne(Attendance::class, 'user_id')
            ->whereDate('attendance_date', \Carbon\Carbon::now('Asia/Jakarta')->toDateString());
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

    /**
     * Cek apakah user memiliki pegawai bawahan langsung
     */
    public function isAtasan(): bool
    {
        if (!$this->pegawai_id) {
            return false;
        }

        return Pegawai::where('atasan_id', $this->pegawai_id)->exists();
    }

    /**
     * Ambil array ID pegawai bawahan langsung
     */
    public function getBawahanIds(): array
    {
        if (!$this->pegawai_id) {
            return [];
        }

        return Pegawai::where('atasan_id', $this->pegawai_id)->pluck('id')->toArray();
    }
}