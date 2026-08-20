<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatJabatan extends Model
{
    // Mengunci nama tabel sesuai database
    protected $table = 'riwayat_jabatan';

    protected $fillable = [
        'pegawai_id',
        'jabatan_id',
        'unit_kerja_id',
        'nomor_sk',
        'tanggal_sk',
        'tmt_jabatan',
        'keterangan', // Ditambahkan sesuai temuan audit
        'status',
        'file_sk'
    ];

    protected $casts = [
        'tanggal_sk' => 'date',
        'tmt_jabatan' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    /**
     * Accessor URL File SK Digital
     */
    public function getFileSkUrlAttribute(): ?string
    {
        return $this->file_sk ? route('document.preview', ['path' => $this->file_sk]) : null;
    }
}