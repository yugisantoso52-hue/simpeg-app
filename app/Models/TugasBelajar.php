<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TugasBelajar extends Model
{
    use HasFactory, RecordsSyncOutbox;

    protected $table = 'tugas_belajar';

    protected $fillable = [
        'pegawai_id',
        'jenis_pengembangan',
        'jenjang_studi',
        'program_studi',
        'perguruan_tinggi',
        'negara',
        'sumber_pembiayaan',
        'nama_sponsor',
        'nomor_sk',
        'tanggal_sk',
        'tanggal_mulai',
        'tanggal_selesai',
        'semester_berjalan',
        'status_studi',
        'file_sk',
        'file_laporan_progress',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_sk'        => 'date',
        'tanggal_mulai'     => 'date',
        'tanggal_selesai'   => 'date',
        'semester_berjalan' => 'integer',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function getFileSkUrlAttribute(): ?string
    {
        return (!empty($this->file_sk) && $this->file_sk !== '-') 
            ? route('document.preview', ['path' => $this->file_sk]) 
            : null;
    }

    public function getFileLaporanProgressUrlAttribute(): ?string
    {
        return (!empty($this->file_laporan_progress) && $this->file_laporan_progress !== '-') 
            ? route('document.preview', ['path' => $this->file_laporan_progress]) 
            : null;
    }

    public function getSisaHariAttribute(): ?int
    {
        if ($this->status_studi === 'Lulus' || $this->status_studi === 'Dibatalkan / DO') {
            return null;
        }

        if (!$this->tanggal_selesai) {
            return null;
        }

        return (int) Carbon::now()->startOfDay()->diffInDays($this->tanggal_selesai->startOfDay(), false);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status_studi) {
            'Sedang Studi'     => 'bg-blue-100 text-blue-800 border-blue-300',
            'Perpanjangan'     => 'bg-amber-100 text-amber-800 border-amber-300 animate-pulse',
            'Lulus'            => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'Dibatalkan / DO'  => 'bg-rose-100 text-rose-800 border-rose-300',
            default            => 'bg-gray-100 text-gray-700 border-gray-300',
        };
    }
}
