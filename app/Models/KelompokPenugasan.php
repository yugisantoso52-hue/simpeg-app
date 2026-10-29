<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Model;

class KelompokPenugasan extends Model
{
    use RecordsSyncOutbox;

    public const KATEGORI = [
        'spmf'            => 'Satuan Penjaminan Mutu (SPMF / GPM)',
        'senat'           => 'Senat Fakultas',
        'kjfd'            => 'Kelompok Jabatan Fungsional Dosen (KJFD)',
        'unit_fungsional' => 'Unit Fungsional',
        'laboratorium'    => 'Laboratorium / Ruang Keperawatan',
    ];

    protected $table = 'kelompok_penugasan';

    protected $fillable = ['kode', 'nama', 'kategori', 'kebutuhan', 'urutan'];

    public function penugasan()
    {
        return $this->hasMany(PegawaiPenugasan::class, 'kelompok_penugasan_id');
    }

    public function pegawai()
    {
        return $this->belongsToMany(Pegawai::class, 'pegawai_penugasan', 'kelompok_penugasan_id', 'pegawai_id')
            ->withPivot('peran')
            ->withTimestamps();
    }

    /** Label gabungan, contoh: "Laboratorium - Maternitas" */
    public function getLabelAttribute(): string
    {
        $prefix = match ($this->kategori) {
            'spmf'            => 'SPMF',
            'senat'           => 'Senat',
            'kjfd'            => 'KJFD',
            'unit_fungsional' => 'Unit',
            'laboratorium'    => 'Lab',
            default           => '',
        };

        return trim($prefix . ' - ' . $this->nama, ' -');
    }
}
