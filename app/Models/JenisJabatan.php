<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisJabatan extends Model
{
    use HasFactory;

    protected $table = 'jenis_jabatan';

    protected $fillable = [
        'nama_jenis_jabatan',
        'keterangan',
    ];

    protected static function booted()
    {
        static::deleting(function ($model) {
            if (Pegawai::where('jenis_jabatan', $model->nama_jenis_jabatan)->where('status_pegawai', 'Aktif')->exists()) {
                throw new \Exception('Jenis Jabatan ini sedang digunakan oleh pegawai aktif.');
            }
        });
    }
}
