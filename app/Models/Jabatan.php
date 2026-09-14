<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'jabatan';

    protected $fillable = [
        'kode_jabatan',
        'nama_jabatan',
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

    public function pegawai()
    {
        return $this->hasMany(Pegawai::class);
    }
}