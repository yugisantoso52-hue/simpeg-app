<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RiwayatPangkat extends Model
{
    use HasFactory;

    protected $table = 'riwayat_pangkat';

    protected $fillable = [
        'pegawai_id',
        'golongan_id',
        'tmt',
        'nomor_sk',
        'file_sk',
        'status',
        'tanggal_berakhir',
        'keterangan',
    ];

    protected $casts = [
        'tmt' => 'date',
        'tanggal_berakhir' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function golongan()
    {
        return $this->belongsTo(Golongan::class, 'golongan_id');
    }

    /**
     * Accessor URL File SK Digital
     */
    public function getFileSkUrlAttribute(): ?string
    {
        return $this->file_sk ? route('document.preview', ['path' => $this->file_sk]) : null;
    }
}