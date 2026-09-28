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

        $allowedRoles = is_array($roles) ? $roles : [$roles];

        // 1. Cek role langsung di tabel roles
        if ($this->role && in_array($this->role->name, $allowedRoles, true)) {
            return true;
        }

        // 2. Hak akses pimpinan untuk 15 Jabatan Pimpinan FKP UNRI
        if (in_array('pimpinan', $allowedRoles, true) && $this->isPimpinan()) {
            return true;
        }

        // 3. Hak akses atasan untuk pimpinan / pejabat yang memiliki bawahan
        if (in_array('atasan', $allowedRoles, true) && $this->isAtasan()) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah user memegang salah satu dari 15 Jabatan Pimpinan FKP UNRI
     */
    public function isPimpinan(): bool
    {
        if ($this->role && in_array($this->role->name, ['admin', 'pimpinan'], true)) {
            return true;
        }

        if (!$this->pegawai_id) {
            return false;
        }

        $pegawai = $this->pegawai ?? Pegawai::with('jabatan')->find($this->pegawai_id);
        if (!$pegawai) {
            return false;
        }

        return $pegawai->isPimpinan();
    }

    /**
     * Cek apakah user memiliki pegawai bawahan langsung atau bawahan hirarki
     */
    public function isAtasan(): bool
    {
        if ($this->role && in_array($this->role->name, ['admin', 'pimpinan'], true)) {
            return true;
        }

        if ($this->isPimpinan()) {
            return true;
        }

        if (!$this->pegawai_id) {
            return false;
        }

        return !empty($this->getBawahanIds());
    }

    /**
     * Ambil array ID pegawai bawahan langsung & hirarki
     */
    public function getBawahanIds(): array
    {
        if (!$this->pegawai_id) {
            return [];
        }

        $pegawai = $this->pegawai ?? Pegawai::find($this->pegawai_id);
        if (!$pegawai) {
            return [];
        }

        return app(\App\Services\ApprovalHierarchyService::class)->getBawahanIdsForPegawai($pegawai);
    }
}