<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PengajuanCuti extends Model
{
    use HasFactory, RecordsSyncOutbox;

    protected $table = 'pengajuan_cuti';

    public const STATUS_MENUNGGU_ATASAN = 'Menunggu Pertimbangan Atasan';
    public const STATUS_DISETUJUI_ATASAN = 'Disetujui Atasan (Menunggu PYBMC)';
    public const STATUS_DISETUJUI        = 'Disetujui';
    public const STATUS_DITOLAK          = 'Ditolak';
    public const STATUS_DIBATALKAN       = 'Dibatalkan';

    protected $fillable = [
        'pegawai_id',
        'jenis_cuti',
        'nomor_surat',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'alasan',
        'alamat_selama_cuti',
        'nomor_telepon',
        'file_lampiran',
        'status',
        'atasan_langsung_id',
        'pertimbangan_atasan',
        'catatan_atasan_langsung',
        'pertimbangan_atasan_at',
        'pybmc_id',
        'approved_by',
        'approved_at',
        'catatan_pimpinan',
    ];

    protected $casts = [
        'tanggal_mulai'          => 'date',
        'tanggal_selesai'        => 'date',
        'pertimbangan_atasan_at' => 'datetime',
        'approved_at'            => 'datetime',
        'jumlah_hari'            => 'integer',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function atasanLangsung()
    {
        return $this->belongsTo(User::class, 'atasan_langsung_id');
    }

    public function pybmc()
    {
        return $this->belongsTo(User::class, 'pybmc_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getFileLampiranUrlAttribute(): ?string
    {
        return (!empty($this->file_lampiran) && $this->file_lampiran !== '-') 
            ? route('document.preview', ['path' => $this->file_lampiran]) 
            : null;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Disetujui'                               => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'Disetujui Atasan (Menunggu PYBMC)'       => 'bg-blue-100 text-blue-800 border-blue-300 animate-pulse',
            'Ditolak'                                 => 'bg-rose-100 text-rose-800 border-rose-300',
            'Dibatalkan'                              => 'bg-gray-100 text-gray-700 border-gray-300',
            default                                   => 'bg-amber-100 text-amber-800 border-amber-300 animate-pulse',
        };
    }
}
