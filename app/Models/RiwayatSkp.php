<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatSkp extends Model
{
    use HasFactory;

    protected $table = 'riwayat_skp';

    protected $fillable = [
        'pegawai_id',
        'tahun',
        'predikat_kinerja',
        'file_rencana_skp',
        'file_evaluasi_skp',
        'pejabat_penilai',
        'keterangan',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function getFileRencanaSkpUrlAttribute(): ?string
    {
        return (!empty($this->file_rencana_skp) && $this->file_rencana_skp !== '-')
            ? route('document.preview', ['path' => $this->file_rencana_skp])
            : null;
    }

    public function getFileEvaluasiSkpUrlAttribute(): ?string
    {
        return (!empty($this->file_evaluasi_skp) && $this->file_evaluasi_skp !== '-')
            ? route('document.preview', ['path' => $this->file_evaluasi_skp])
            : null;
    }

    public function getIsLengkapAttribute(): bool
    {
        return !empty($this->file_rencana_skp) && !empty($this->file_evaluasi_skp);
    }

    public function getPredikatBadgeClassAttribute(): string
    {
        return match ($this->predikat_kinerja) {
            'Sangat Baik'      => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'Baik'             => 'bg-blue-100 text-blue-800 border-blue-300',
            'Butuh Perbaikan'  => 'bg-amber-100 text-amber-800 border-amber-300',
            'Kurang'           => 'bg-rose-100 text-rose-800 border-rose-300',
            'Sangat Kurang'    => 'bg-red-200 text-red-900 border-red-400',
            default            => 'bg-gray-100 text-gray-700 border-gray-300',
        };
    }
}
