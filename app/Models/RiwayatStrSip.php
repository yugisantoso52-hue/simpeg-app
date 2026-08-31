<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RiwayatStrSip extends Model
{
    use HasFactory, RecordsSyncOutbox;

    protected $table = 'riwayat_str_sip';

    protected $fillable = [
        'pegawai_id',
        'jenis_dokumen',
        'nomor_registrasi',
        'nama_dokumen',
        'instansi_penerbit',
        'tanggal_terbit',
        'tanggal_berakhir',
        'is_seumur_hidup',
        'status',
        'file_dokumen',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_terbit'   => 'date',
        'tanggal_berakhir' => 'date',
        'is_seumur_hidup'  => 'boolean',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    /**
     * URL Pratinjau Dokumen Digital
     */
    public function getFileDokumenUrlAttribute(): ?string
    {
        return (!empty($this->file_dokumen) && $this->file_dokumen !== '-') 
            ? route('document.preview', ['path' => $this->file_dokumen]) 
            : null;
    }

    /**
     * Sisa hari masa berlaku
     */
    public function getSisaHariAttribute(): ?int
    {
        if ($this->is_seumur_hidup) {
            return null;
        }

        if (!$this->tanggal_berakhir) {
            return null;
        }

        return (int) Carbon::now()->startOfDay()->diffInDays($this->tanggal_berakhir->startOfDay(), false);
    }

    /**
     * Status Keaktifan Aktual (Komputasi)
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->is_seumur_hidup) {
            return 'Seumur Hidup';
        }

        if ($this->status === 'Dalam Proses Perpanjangan') {
            return 'Dalam Perpanjangan';
        }

        $sisa = $this->sisa_hari;
        if ($sisa !== null && $sisa < 0) {
            return 'Kedaluwarsa';
        }

        if ($sisa !== null && $sisa <= 180) {
            return 'Segera Berakhir (' . $sisa . ' hari)';
        }

        return 'Aktif';
    }
}
