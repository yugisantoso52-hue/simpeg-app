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
        'nomor_surat',
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
        'verifikasi_admin_at',
        'verifikasi_admin_by',
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
        'tmt_lama'              => 'date',
        'tmt_baru'              => 'date',
        'verifikasi_admin_at'   => 'datetime',
        'paraf_kapokja_at'      => 'datetime',
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

    /**
     * Resolusi path file dokumen (memprioritaskan file upload pada pengajuan, lalu fallback otomatis ke master data pegawai)
     */
    public function resolveDocPath(string $type): ?string
    {
        $type = strtolower($type);
        $pegawai = $this->pegawai;

        return match ($type) {
            'sk_pangkat', 'sk_pangkat_terakhir', 'pangkat' => 
                $this->file_sk_pangkat_terakhir 
                ?: ($this->file_sk_terakhir 
                ?: ($pegawai?->file_sk_pangkat_terakhir 
                ?: $pegawai?->riwayatPangkat()->latest('tmt')->first()?->file_sk)),

            'sk_kgb', 'sk_kgb_terakhir', 'kgb' => 
                $this->file_sk_kgb_terakhir 
                ?: $pegawai?->file_sk_kgb_terakhir,

            'skp_1', 'skp_n1' => 
                $this->file_skp_1 
                ?: $this->resolveMasterSkp(1),

            'skp_2', 'skp_n2' => 
                $this->file_skp_2 
                ?: $this->resolveMasterSkp(2),

            'skp_2_tahun' =>
                $this->file_skp_2_tahun
                ?: ($this->file_skp_1 ?: $this->resolveMasterSkp(1)),

            'sk_cpns', 'cpns' => 
                $this->file_sk_cpns 
                ?: $pegawai?->file_sk_pertama,

            'sk_jabatan', 'jabatan' => 
                $this->file_sk_jabatan_terakhir 
                ?: ($this->file_sk_terakhir 
                ?: $pegawai?->riwayatJabatan()->latest('tmt_jabatan')->first()?->file_sk),

            'karpeg' => 
                $this->file_karpeg 
                ?: $pegawai?->file_karpeg,

            'pak' => 
                $this->file_pak 
                ?: $pegawai?->file_pak,

            'pendukung', 'file_pendukung' => 
                $this->file_pendukung,

            'hukdis', 'surat_pernyataan_tidak_hukdis', 'file_surat_pernyataan_tidak_hukdis' => 
                $this->file_surat_pernyataan_tidak_hukdis,

            default => null,
        };
    }

    /**
     * Helper untuk mengambil SKP dari master data (1 = N-1, 2 = N-2)
     */
    protected function resolveMasterSkp(int $step = 1): ?string
    {
        if (!$this->pegawai) return null;

        $baseYear = $this->created_at ? (int)$this->created_at->format('Y') : (int)date('Y');
        $targetYear = $baseYear - $step;

        $skp = $this->pegawai->riwayatSkp()->where('tahun', $targetYear)->first();
        if ($skp && (!empty($skp->file_evaluasi_skp) || !empty($skp->file_rencana_skp))) {
            return $skp->file_evaluasi_skp ?: $skp->file_rencana_skp;
        }

        // Fallback: Ambil berdasarkan urutan tahun terbaru di master data
        $skpList = $this->pegawai->riwayatSkp()->orderByDesc('tahun')->get();
        $targetIndex = $step - 1;
        if ($skpList->count() > $targetIndex) {
            $item = $skpList[$targetIndex];
            return $item->file_evaluasi_skp ?: $item->file_rencana_skp;
        }

        return null;
    }

    /**
     * Periksa apakah dokumen tersedia (baik dari pengajuan maupun master data)
     */
    public function hasDoc(string $type): bool
    {
        $path = $this->resolveDocPath($type);
        return !empty($path) && $path !== '-';
    }

    /**
     * Cek apakah dokumen diambil dari master data (karena tidak diunggah langsung pada pengajuan)
     */
    public function isDocFromMaster(string $type): bool
    {
        $type = strtolower($type);
        return match ($type) {
            'sk_pangkat', 'sk_pangkat_terakhir', 'pangkat' => empty($this->file_sk_pangkat_terakhir) && empty($this->file_sk_terakhir) && $this->hasDoc($type),
            'sk_kgb', 'sk_kgb_terakhir', 'kgb' => empty($this->file_sk_kgb_terakhir) && $this->hasDoc($type),
            'skp_1', 'skp_n1' => empty($this->file_skp_1) && $this->hasDoc($type),
            'skp_2', 'skp_n2' => empty($this->file_skp_2) && $this->hasDoc($type),
            'skp_2_tahun' => empty($this->file_skp_2_tahun) && $this->hasDoc($type),
            'sk_cpns', 'cpns' => empty($this->file_sk_cpns) && $this->hasDoc($type),
            'sk_jabatan', 'jabatan' => empty($this->file_sk_jabatan_terakhir) && empty($this->file_sk_terakhir) && $this->hasDoc($type),
            'karpeg' => empty($this->file_karpeg) && $this->hasDoc($type),
            'pak' => empty($this->file_pak) && $this->hasDoc($type),
            default => false,
        };
    }

    /**
     * URL untuk melihat dokumen aman via dedicated streaming controller
     */
    public function getDocPreviewUrl(string $type): string
    {
        return route('pengajuan-karir.dokumen', [
            'id'    => $this->id,
            'jenis' => $type,
        ]);
    }
}
