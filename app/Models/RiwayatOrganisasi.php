<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatOrganisasi extends Model
{
    use HasFactory, RecordsSyncOutbox;

    protected $table = 'riwayat_organisasi';

    protected $fillable = [
        'pegawai_id',
        'nama_organisasi',
        'jabatan_organisasi',
        'tahun_mulai',
        'tahun_selesai',
        'masih_aktif',
        'keterangan',
    ];

    protected $casts = [
        'masih_aktif' => 'boolean',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}
