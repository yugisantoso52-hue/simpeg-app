<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbkUraianTugas extends Model
{
    use HasFactory;

    protected $table = 'abk_uraian_tugas';

    protected $fillable = [
        'analisis_jabatan_id',
        'urutan',
        'uraian_tugas',
        'satuan_hasil',
        'norma_waktu_menit',
        'volume_1_tahun',
        'waktu_beban_menit',
        'kebutuhan_pegawai',
        'keterangan',
    ];

    protected $casts = [
        'norma_waktu_menit' => 'float',
        'volume_1_tahun'    => 'float',
        'waktu_beban_menit' => 'float',
        'kebutuhan_pegawai' => 'float',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->waktu_beban_menit = (float)$model->norma_waktu_menit * (float)$model->volume_1_tahun;
            // Standar WKE PermenPAN-RB & BKN: 1.250 Jam = 75.000 Menit
            $model->kebutuhan_pegawai = round($model->waktu_beban_menit / 75000, 4);
        });
    }

    public function analisisJabatan()
    {
        return $this->belongsTo(AnalisisJabatan::class, 'analisis_jabatan_id');
    }
}
