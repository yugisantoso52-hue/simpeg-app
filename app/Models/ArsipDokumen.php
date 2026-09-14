<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArsipDokumen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'arsip_dokumen';

    protected $fillable = [
        'sync_uuid',
        'pegawai_sync_uuid',
        'pegawai_id',
        'jenis_dokumen',
        'kategori',
        'documentable_type',
        'documentable_id',
        'documentable_sync_uuid',
        'nama_file_sistem',
        'nama_file_asli',
        'mime_type',
        'ukuran_file',
        'checksum',
        'storage_driver',
        'google_drive_file_id',
        'google_drive_folder_id',
        'status_sync',
        'sync_error',
        'tanggal_dokumen',
        'nomor_dokumen',
        'keterangan',
        'created_by',
        'created_by_sync_uuid',
        'is_active',
    ];

    protected $casts = [
        'ukuran_file'     => 'integer',
        'tanggal_dokumen' => 'date',
        'is_active'       => 'boolean',
        'deleted_at'      => 'datetime',
    ];

    /**
     * Relasi lokal ke Pegawai
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    /**
     * Relasi polimorfik ke entitas pemilik berkas (RiwayatPangkat, RiwayatPendidikan, dll.)
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relasi ke User pembuat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
