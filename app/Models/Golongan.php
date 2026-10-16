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
        'keterangan',
        'urutan',
    ];

    /**
     * Accessor: Nomor urutan kepangkatan (I/a s.d. IV/e)
     */
    public function getUrutanAttribute(): int
    {
        if (isset($this->attributes['urutan']) && is_numeric($this->attributes['urutan'])) {
            return (int) $this->attributes['urutan'];
        }

        $orderMap = [
            'I/A' => 1, 'I/B' => 2, 'I/C' => 3, 'I/D' => 4,
            'II/A' => 5, 'II/B' => 6, 'II/C' => 7, 'II/D' => 8,
            'III/A' => 9, 'III/B' => 10, 'III/C' => 11, 'III/D' => 12,
            'IV/A' => 13, 'IV/B' => 14, 'IV/C' => 15, 'IV/D' => 16, 'IV/E' => 17,
        ];

        $nama = strtoupper(trim((string) ($this->nama_golongan ?? '')));

        return $orderMap[$nama] ?? ((int) ($this->id ?? 0));
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->pegawai()->where('status_pegawai', 'Aktif')->exists()) {
                throw new \Exception('Golongan ini sedang digunakan oleh pegawai aktif.');
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
