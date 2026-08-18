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

    public function pegawai()
    {
        return $this->hasMany(Pegawai::class);
    }
}