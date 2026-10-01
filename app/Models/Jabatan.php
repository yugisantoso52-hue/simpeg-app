<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'jabatan';

    protected $fillable = [
        'unit_kerja_id',
        'kode_jabatan',
        'nama_jabatan',
        'kelas_jabatan',
        'kelompok_jabatan',
        'ikhtisar_jabatan',
        'keterangan'
    ];

    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->pegawai()->where('status_pegawai', 'Aktif')->exists()) {
                throw new \Exception("Jabatan ini sedang digunakan oleh pegawai aktif.");
            }
        });
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function pegawai()
    {
        return $this->hasMany(Pegawai::class);
    }

    public function analisisJabatan()
    {
        return $this->hasOne(AnalisisJabatan::class, 'jabatan_id');
    }
}