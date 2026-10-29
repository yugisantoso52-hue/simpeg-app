<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsulanSatyalancana extends Model
{
    use HasFactory, RecordsSyncOutbox;

    protected $table = 'usulan_satyalancana';

    protected $fillable = [
        'sync_uuid',
        'pegawai_id',
        'kategori',
        'tahun_usulan',
        'periode_usulan',
        'nomor_surat_usulan',
        'status', // draft, diajukan, diverifikasi_admin, diparaf_kabag, diparaf_kapokja, disetujui_wd2, selesai, ditolak
        'catatan',
        'file_drh',
        'file_sk_cpns',
        'file_sk_pangkat_terakhir',
        'file_sk_jabatan_terakhir',
        'file_skp_2_tahun',
        'file_surat_pernyataan_tidak_hukdis',
        'file_surat_pengantar',
        'diajukan_oleh',
        'diverifikasi_oleh',
        'diverifikasi_at',
        'verifikasi_admin_at',
        'verifikasi_admin_by',
        'paraf_kabag_at',
        'paraf_kabag_by',
        'paraf_kapokja_at',
        'paraf_kapokja_by',
        'ttd_wd2_at',
        'ttd_wd2_by',
    ];

    protected $casts = [
        'tahun_usulan'        => 'integer',
        'diverifikasi_at'     => 'datetime',
        'verifikasi_admin_at' => 'datetime',
        'paraf_kabag_at'      => 'datetime',
        'paraf_kapokja_at'    => 'datetime',
        'ttd_wd2_at'          => 'datetime',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function diajukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function verifikasiAdminBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikasi_admin_by');
    }

    public function parafKabagBy(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'paraf_kabag_by');
    }

    public function parafKaPokjaBy(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'paraf_kapokja_by');
    }

    public function ttdWd2By(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'ttd_wd2_by');
    }

    public function verifikatorKaPokja(): BelongsTo
    {
        return $this->parafKaPokjaBy();
    }

    public function verifikatorKabag(): BelongsTo
    {
        return $this->parafKabagBy();
    }

    public function verifikatorWd2(): BelongsTo
    {
        return $this->ttdWd2By();
    }

    public function penandatanganDekan(): BelongsTo
    {
        return $this->ttdWd2By();
    }

    public function getJenisPengajuanAttribute(): string
    {
        return 'SATYALANCANA';
    }

    public function getNomorSuratAttribute(): ?string
    {
        return $this->nomor_surat_usulan;
    }

    public function getParafWd2AtAttribute(): ?\Carbon\Carbon
    {
        return $this->ttd_wd2_at;
    }

    public function getParafWd2ByAttribute(): ?int
    {
        return $this->ttd_wd2_by;
    }

    public function getTtdDekanAtAttribute(): ?\Carbon\Carbon
    {
        return $this->ttd_wd2_at;
    }

    public function getTtdDekanByAttribute(): ?int
    {
        return $this->ttd_wd2_by;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'diajukan' => ['label' => 'Diajukan', 'color' => 'blue', 'icon' => 'clock'],
            'diverifikasi_admin' => ['label' => 'Screening Admin', 'color' => 'amber', 'icon' => 'check'],
            'diparaf_kapokja' => ['label' => 'Paraf Ka Pokja', 'color' => 'teal', 'icon' => 'check'],
            'diparaf_kabag' => ['label' => 'Paraf Kabag Umum', 'color' => 'indigo', 'icon' => 'check'],
            'disetujui_wd2' => ['label' => 'Disahkan TTE WD II', 'color' => 'emerald', 'icon' => 'check-circle'],
            'ditolak' => ['label' => 'Ditolak / Revisi', 'color' => 'rose', 'icon' => 'x-circle'],
            'selesai' => ['label' => 'Selesai Keppres RI', 'color' => 'teal', 'icon' => 'award'],
            default => ['label' => ucfirst(str_replace('_', ' ', $this->status)), 'color' => 'gray', 'icon' => 'info'],
        };
    }
}
