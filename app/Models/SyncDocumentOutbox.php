<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncDocumentOutbox extends Model
{
    use HasFactory;

    protected $table = 'sync_document_outboxes';

    protected $fillable = [
        'idempotency_key',
        'arsip_dokumen_uuid',
        'pegawai_sync_uuid',
        'local_file_path',
        'file_size',
        'checksum',
        'status',
        'attempts',
        'locked_at',
        'retry_at',
        'completed_at',
        'last_error',
    ];

    protected $casts = [
        'file_size'    => 'integer',
        'attempts'     => 'integer',
        'locked_at'    => 'datetime',
        'retry_at'     => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relasi ke metadata ArsipDokumen berdasarkan UUID
     */
    public function arsipDokumen(): BelongsTo
    {
        return $this->belongsTo(ArsipDokumen::class, 'arsip_dokumen_uuid', 'sync_uuid');
    }
}
