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
        'verifikasi_admin_at',
        'verifikasi_admin_by',
        'paraf_kabag_at',
        'paraf_kabag_by',
        'paraf_kapokja_at',
        'paraf_kapokja_by',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_surat'       => 'date',
        'verifikasi_admin_at' => 'datetime',
        'paraf_kabag_at'      => 'datetime',
        'paraf_kapokja_at'    => 'datetime',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function kabag(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'paraf_kabag_by');
    }

    public function kaPokja(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'paraf_kapokja_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
