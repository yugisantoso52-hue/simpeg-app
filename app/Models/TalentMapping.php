<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalentMapping extends Model
{
    use HasFactory;

    protected $table = 'talent_mappings';

    protected $fillable = [
        'pegawai_id',
        'tahun',
        'periode',
        'jabatan_saat_ini_id',
        'unit_kerja_saat_ini_id',
        // Sumbu Kinerja
        'skor_skp_n',
        'predikat_skp_n',
        'skor_skp_n_minus_1',
        'predikat_skp_n_minus_1',
        'skor_kinerja_skp',
        'skor_disiplin_kehadiran',
        'skor_aktivitas_logbook',
        'sumbu_kinerja_nilai',
        'sumbu_kinerja_kategori',
        // Sumbu Potensi
        'skor_kualifikasi_pendidikan',
        'skor_pengembangan_kompetensi',
        'skor_rekam_jejak',
        'skor_asesmen_kompetensi',
        'sumbu_potensi_nilai',
        'sumbu_potensi_kategori',
        // Matriks
        'kuadran_box',
        'box_name',
        'status_talenta',
        'rekomendasi_kebijakan',
        'is_suksesi_eligible',
        // Validasi
        'status_validasi',
        'catatan_komite',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'tahun'                       => 'integer',
        'skor_skp_n'                  => 'decimal:2',
        'skor_skp_n_minus_1'          => 'decimal:2',
        'skor_kinerja_skp'            => 'decimal:2',
        'skor_disiplin_kehadiran'     => 'decimal:2',
        'skor_aktivitas_logbook'      => 'decimal:2',
        'sumbu_kinerja_nilai'         => 'decimal:2',
        'skor_kualifikasi_pendidikan' => 'decimal:2',
        'skor_pengembangan_kompetensi'=> 'decimal:2',
        'skor_rekam_jejak'            => 'decimal:2',
        'skor_asesmen_kompetensi'     => 'decimal:2',
        'sumbu_potensi_nilai'         => 'decimal:2',
        'kuadran_box'                 => 'integer',
        'is_suksesi_eligible'         => 'boolean',
        'validated_at'                => 'datetime',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_saat_ini_id');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_saat_ini_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function scopeTahun($query, int $year)
    {
        return $query->where('tahun', $year);
    }

    public function scopeKuadran($query, int $box)
    {
        return $query->where('kuadran_box', $box);
    }

    public function scopeEligibleSuksesi($query)
    {
        return $query->where('is_suksesi_eligible', true);
    }

    /**
     * Warna styling box UI berdasarkan kuadran PermenPAN-RB No. 3/2020
     */
    public function getBoxBadgeColorAttribute(): string
    {
        return match ($this->kuadran_box) {
            9 => 'bg-emerald-600 text-white border-emerald-700', // Kotak IX: Star / Top Talent
            8 => 'bg-teal-600 text-white border-teal-700',       // Kotak VIII: Kinerja Tinggi, Potensi Sedang
            7 => 'bg-cyan-600 text-white border-cyan-700',       // Kotak VII: Kinerja Tinggi, Potensi Rendah
            6 => 'bg-indigo-600 text-white border-indigo-700',   // Kotak VI: Kinerja Sedang, Potensi Tinggi
            5 => 'bg-blue-600 text-white border-blue-700',       // Kotak V: Kinerja Sedang, Potensi Sedang
            4 => 'bg-amber-500 text-white border-amber-600',     // Kotak IV: Kinerja Sedang, Potensi Rendah
            3 => 'bg-amber-600 text-white border-amber-700',     // Kotak III: Kinerja Rendah, Potensi Tinggi
            2 => 'bg-orange-600 text-white border-orange-700',   // Kotak II: Kinerja Rendah, Potensi Sedang
            1 => 'bg-rose-600 text-white border-rose-700',       // Kotak I: Kinerja Rendah, Potensi Rendah
            default => 'bg-gray-600 text-white border-gray-700',
        };
    }
}
