<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Golongan extends Model
{
    use HasFactory;

    protected $table = 'golongan';

    protected $fillable = [
        'nama_golongan',
        'nama_pangkat',
        'keterangan'
    ];

    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->pegawai()->where('status_pegawai', 'Aktif')->exists()) {
                throw new \Exception("Golongan ini sedang digunakan oleh pegawai aktif.");
            }
        });
    }

    /**
     * Relasi ke model Pegawai (One-to-Many)
     */
    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }
}