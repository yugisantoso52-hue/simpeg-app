<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalisisJabatan extends Model
{
    use HasFactory;

    protected $table = 'analisis_jabatan';

    protected $fillable = [
        'jabatan_id',
        'unit_kerja_id',
        'kode_anjab',
        'ikhtisar_jabatan',
        'kualifikasi_pendidikan',
        'kualifikasi_pelatihan',
        'kualifikasi_pengalaman',
        'bahan_kerja',
        'perangkat_kerja',
        'tanggung_jawab',
        'wewenang',
        'korelasi_jabatan',
        'kondisi_lingkungan',
        'resiko_bahaya',
        'syarat_keterampilan',
        'syarat_bakat',
        'syarat_temperamen',
        'syarat_minat',
        'syarat_upaya_fisik',
        'kondisi_fisik',
        'prestasi_diharapkan',
        'kelas_jabatan',
        'status',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function uraianTugas()
    {
        return $this->hasMany(AbkUraianTugas::class, 'analisis_jabatan_id')->orderBy('urutan');
    }

    /**
     * Total Waktu Beban Kerja dalam Menit (Norma Waktu * Volume)
     */
    public function getTotalWaktuBebanMenitAttribute(): float
    {
        return (float) $this->uraianTugas->sum('waktu_beban_menit');
    }

    /**
     * Total Waktu Beban Kerja dalam Jam
     */
    public function getTotalJamBebanAttribute(): float
    {
        return round($this->total_waktu_beban_menit / 60, 2);
    }

    /**
     * Kebutuhan Pegawai berdasarkan Standar WKE 1.250 Jam (75.000 Menit/Tahun)
     */
    public function getKebutuhanPegawaiAttribute(): float
    {
        return round($this->total_waktu_beban_menit / 75000, 2);
    }

    /**
     * Pembulatan Formasi BKN
     */
    public function getFormasiPembulatanAttribute(): int
    {
        $kebutuhan = $this->kebutuhan_pegawai;
        return (int) max(1, round($kebutuhan));
    }

    /**
     * Jumlah Pegawai Riil Saat Ini (Bezetting)
     */
    public function getBezettingAttribute(): int
    {
        $query = Pegawai::where('jabatan_id', $this->jabatan_id)
            ->where('status_pegawai', 'Aktif');

        if ($this->unit_kerja_id) {
            $query->where(function($q) {
                $q->where('unit_kerja_id', $this->unit_kerja_id)
                  ->orWhereNull('unit_kerja_id');
            });
        }

        return $query->count();
    }

    /**
     * Selisih Formasi: Bezetting - Formasi Pembulatan
     */
    public function getSelisihFormasiAttribute(): int
    {
        return $this->bezetting - $this->formasi_pembulatan;
    }

    /**
     * Status Formasi Pegawai
     */
    public function getStatusFormasiAttribute(): string
    {
        $selisih = $this->selisih_formasi;

        if ($selisih < 0) {
            return 'Kurang Pegawai (' . abs($selisih) . ')';
        }

        if ($selisih > 0) {
            return 'Kelebihan Pegawai (+' . $selisih . ')';
        }

        return 'Ideal / Cukup';
    }

    /**
     * Warna Indikator Status Formasi
     */
    public function getStatusColorAttribute(): string
    {
        $selisih = $this->selisih_formasi;

        if ($selisih < 0) {
            return 'rose'; // Merah / Kurang
        }

        if ($selisih > 0) {
            return 'amber'; // Kuning / Lebih
        }

        return 'emerald'; // Hijau / Ideal
    }
}
