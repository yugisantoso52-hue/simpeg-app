<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    protected $table = 'unit_kerja';

    protected $fillable = [
        'parent_id',
        'kode_unit',
        'nama_unit',
        'tipe_unit',
        'urutan',
        'keterangan'
    ];

    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->pegawai()->where('status_pegawai', 'Aktif')->exists()) {
                throw new \Exception("Unit Kerja ini sedang digunakan oleh pegawai aktif.");
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(UnitKerja::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(UnitKerja::class, 'parent_id')->orderBy('urutan');
    }

    public function jabatan()
    {
        return $this->hasMany(Jabatan::class, 'unit_kerja_id');
    }

    public function pegawai()
    {
        return $this->hasMany(Pegawai::class);
    }

    public function analisisJabatan()
    {
        return $this->hasMany(AnalisisJabatan::class, 'unit_kerja_id');
    }
}