<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPublikasi extends Model
{
    use HasFactory, RecordsSyncOutbox;

    protected $table = 'riwayat_publikasi';

    protected $fillable = [
        'pegawai_id',
        'judul_publikasi',
        'jenis_publikasi',
        'nama_jurnal',
        'penerbit',
        'tahun_terbit',
        'volume_nomor',
        'url_doi',
        'indeksasi',
        'file_publikasi',
        'keterangan',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}
