<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Model;

class PegawaiPenugasan extends Model
{
    use RecordsSyncOutbox;

    public const PERAN = ['Ketua', 'Sekretaris', 'Koordinator', 'Anggota', 'Staf'];

    protected $table = 'pegawai_penugasan';

    protected $fillable = ['pegawai_id', 'kelompok_penugasan_id', 'peran', 'keterangan'];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function kelompok()
    {
        return $this->belongsTo(KelompokPenugasan::class, 'kelompok_penugasan_id');
    }
}
