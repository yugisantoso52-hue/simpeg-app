<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PengajuanKarir extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_karir';

    protected $fillable = [
        'sync_uuid',
        'pegawai_id',
        'jenis_pengajuan',
        'periode_kp',
        'tahun_periode',
        'golongan_lama_id',
        'golongan_tujuan_id',
        'gaji_pokok_lama',
        'gaji_pokok_baru',
        'tmt_lama',
        'tmt_baru',
        'mkg_tahun',
        'mkg_bulan',
        'file_sk_terakhir',
        'file_sk_pangkat_terakhir',
        'file_sk_kgb_terakhir',
        'file_skp_1',
        'file_skp_2',
        'file_karpeg',
        'file_pak',
        'file_pendukung',
        'status',
        'catatan_pegawai',
        'catatan_verifikator',
        'paraf_kapokja_at',
        'paraf_kapokja_by',
        'paraf_kabag_at',
        'paraf_kabag_by',
        'paraf_wd2_at',
        'paraf_wd2_by',
        'ttd_dekan_at',
        'ttd_dekan_by',
    ];

    protected $casts = [
        'tmt_lama'          => 'date',
        'tmt_baru'          => 'date',
        'paraf_kapokja_at'  => 'datetime',
        'paraf_kabag_at'    => 'datetime',
        'paraf_wd2_at'      => 'datetime',
        'ttd_dekan_at'      => 'datetime',
        'gaji_pokok_lama'   => 'decimal:2',
        'gaji_pokok_baru'   => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->sync_uuid)) {
                $model->sync_uuid = (string) Str::uuid();
            }
        });
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function golonganLama(): BelongsTo
    {
        return $this->belongsTo(Golongan::class, 'golongan_lama_id');
    }

    public function golonganTujuan(): BelongsTo
    {
        return $this->belongsTo(Golongan::class, 'golongan_tujuan_id');
    }

    public function verifikatorKaPokja(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'paraf_kapokja_by');
    }

    public function verifikatorKabag(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'paraf_kabag_by');
    }

    public function verifikatorWd2(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'paraf_wd2_by');
    }

    public function penandatanganDekan(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'ttd_dekan_by');
    }

    /**
     * Label status berwarna untuk badge
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'diajukan' => ['label' => 'Diajukan', 'color' => 'blue', 'icon' => 'clock'],
            'diverifikasi_kapokja' => ['label' => 'Paraf Ka Pokja', 'color' => 'sky', 'icon' => 'check'],
            'diverifikasi_kabag' => ['label' => 'Paraf Kabag Umum', 'color' => 'indigo', 'icon' => 'check'],
            'diverifikasi_wd2', 'disetujui_wd2' => ['label' => 'Disetujui WD II (Sah)', 'color' => 'emerald', 'icon' => 'check-circle'],
            'disetujui_dekan' => ['label' => 'Disetujui Dekan', 'color' => 'emerald', 'icon' => 'award'],
            'ditolak' => ['label' => 'Ditolak / Perlu Revisi', 'color' => 'rose', 'icon' => 'x-circle'],
            'selesai' => ['label' => 'Selesai / Terbit SK', 'color' => 'teal', 'icon' => 'check-check'],
            default => ['label' => ucfirst($this->status), 'color' => 'gray', 'icon' => 'info'],
        };
    }
}
