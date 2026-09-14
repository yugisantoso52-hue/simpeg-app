<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RiwayatPendidikan extends Model
{
    use HasFactory, RecordsSyncOutbox;

    protected $table = 'riwayat_pendidikan';

    protected $fillable = [
        'pegawai_id',
        'jenjang',
        'institusi',
        'fakultas',
        'jurusan',
        'tahun_lulus',
        'ijazah'
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    /**
     * Accessor URL Ijazah Digital
     */
    public function getIjazahUrlAttribute(): ?string
    {
        return (!empty($this->ijazah) && $this->ijazah !== '-') 
            ? route('document.preview', ['path' => $this->ijazah]) 
            : null;
    }
}