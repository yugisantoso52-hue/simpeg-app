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
}
