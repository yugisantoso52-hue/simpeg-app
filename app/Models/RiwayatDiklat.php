<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class RiwayatDiklat extends Model
{
    use HasFactory;

    protected $table = 'riwayat_diklat';

    protected $fillable = [
        'pegawai_id',
        'nama_diklat',
        'jenis_diklat',
        'penyelenggara',
        'tempat',
        'nomor_sertifikat',
        'tanggal_sertifikat',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_jam',
        'status',
        'file_sertifikat',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_sertifikat' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function getDurasiAttribute(): ?int
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return null;
        }

        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    public function getFileSertifikatUrlAttribute(): ?string
    {
        return $this->file_sertifikat ? route('document.preview', ['path' => $this->file_sertifikat]) : null;
    }
}