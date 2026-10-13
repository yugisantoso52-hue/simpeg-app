<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LayananSurat extends Model
{
    use HasFactory, RecordsSyncOutbox;

    protected $table = 'layanan_surat';

    protected $fillable = [
        'sync_uuid',
        'pegawai_id',
        'jenis_surat',
        'nomor_surat',
        'keperluan',
        'tanggal_surat',
        'status',
        'penandatangan_nama',
        'penandatangan_nip',
        'penandatangan_jabatan',
        'qr_token',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
