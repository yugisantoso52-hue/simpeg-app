<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PengajuanCuti extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_cuti';

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
        'approved_by',
        'approved_at',
        'catatan_pimpinan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'approved_at'     => 'datetime',
        'jumlah_hari'     => 'integer',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
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
            'Disetujui'            => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'Ditolak'              => 'bg-rose-100 text-rose-800 border-rose-300',
            'Dibatalkan'           => 'bg-gray-100 text-gray-700 border-gray-300',
            default                => 'bg-amber-100 text-amber-800 border-amber-300 animate-pulse',
        };
    }
}
