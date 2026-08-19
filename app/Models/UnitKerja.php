<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    protected $table = 'unit_kerja';

    protected $fillable = [
        'kode_unit',
        'nama_unit',
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

    public function pegawai()
    {
        return $this->hasMany(Pegawai::class);
    }
}