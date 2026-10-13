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
        'nomor_surat_usulan',
        'status',
        'catatan',
        'file_drh',
        'file_sk_cpns',
        'file_sk_pangkat_terakhir',
        'file_sk_jabatan_terakhir',
        'file_skp_2_tahun',
        'file_surat_pernyataan_tidak_hukdis',
        'diajukan_oleh',
        'diverifikasi_oleh',
        'diverifikasi_at',
    ];

    protected $casts = [
        'tahun_usulan'     => 'integer',
        'diverifikasi_at'  => 'datetime',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function diajukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
