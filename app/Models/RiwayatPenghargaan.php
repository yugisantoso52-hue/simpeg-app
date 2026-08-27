<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPenghargaan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_penghargaan';

    protected $fillable = [
        'pegawai_id',
        'nama_penghargaan',
        'jenis_penghargaan',
        'instansi_pemberi',
        'tanggal_terima',
        'nomor_sk',
        'file_sk',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_terima' => 'date',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}
