<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuotaCutiPegawai extends Model
{
    use HasFactory;

    protected $table = 'kuota_cuti_pegawai';

    protected $fillable = [
        'pegawai_id',
        'tahun',
        'kuota_awal',
        'kuota_terpakai',
        'sisa_kuota',
        'catatan_penyesuaian',
        'updated_by',
    ];

    protected $casts = [
        'tahun'          => 'integer',
        'kuota_awal'     => 'integer',
        'kuota_terpakai' => 'integer',
        'sisa_kuota'     => 'integer',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
