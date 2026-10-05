<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalentAssessment extends Model
{
    use HasFactory;

    protected $table = 'talent_assessments';

    protected $fillable = [
        'pegawai_id',
        'tanggal_asesmen',
        'nomor_surat',
        'lembaga_penyelenggara',
        'metode_asesmen',
        'skor_manajerial',
        'skor_sosio_kultural',
        'skor_teknis',
        'skor_potensi',
        'skor_total',
        'kategori_kelayakan',
        'ringkasan_kompetensi',
        'rekomendasi_pengembangan',
        'file_laporan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_asesmen'     => 'date',
        'skor_manajerial'     => 'decimal:2',
        'skor_sosio_kultural' => 'decimal:2',
        'skor_teknis'         => 'decimal:2',
        'skor_potensi'        => 'decimal:2',
        'skor_total'          => 'decimal:2',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFileLaporanUrlAttribute(): ?string
    {
        return (!empty($this->file_laporan) && $this->file_laporan !== '-')
            ? route('document.preview', ['path' => $this->file_laporan])
            : null;
    }
}
