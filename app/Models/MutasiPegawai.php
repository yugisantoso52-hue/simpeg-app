<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Model;

class MutasiPegawai extends Model
{
    use RecordsSyncOutbox;
    protected $table = 'mutasi_pegawai';

    protected $fillable = [
        'pegawai_id',
        'unit_lama_id',
        'unit_baru_id',
        'jabatan_lama_id',
        'jabatan_baru_id',
        'tmt',
        'nomor_sk',
        'file_sk',
        'keterangan'
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function unitLama()
    {
        return $this->belongsTo(UnitKerja::class,'unit_lama_id');
    }

    public function unitBaru()
    {
        return $this->belongsTo(UnitKerja::class,'unit_baru_id');
    }

    public function jabatanLama()
    {
        return $this->belongsTo(Jabatan::class,'jabatan_lama_id');
    }

    public function jabatanBaru()
    {
        return $this->belongsTo(Jabatan::class,'jabatan_baru_id');
    }
}