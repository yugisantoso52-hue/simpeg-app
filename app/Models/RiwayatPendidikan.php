<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RiwayatPendidikan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_pendidikan';

    protected $fillable = [
        'pegawai_id',
        'jenjang',
        'institusi',
        'fakultas',
        'jurusan',
        'tahun_lulus',
        'ijazah'
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    /**
     * Accessor URL Ijazah Digital
     */
    public function getIjazahUrlAttribute(): ?string
    {
        return $this->ijazah ? Storage::url($this->ijazah) : null;
    }
}