<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Illuminate\Database\Eloquent\Model;

class PengajuanJabatan extends Model
{
    use RecordsSyncOutbox;

    public const STATUS_MENUNGGU  = 'menunggu';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK   = 'ditolak';

    protected $table = 'pengajuan_jabatan';

    protected $fillable = [
        'pegawai_id', 'jabatan_id', 'unit_kerja_id', 'nomor_sk', 'tanggal_sk', 'tmt_jabatan',
        'file_sk', 'status', 'diajukan_oleh', 'diverifikasi_oleh', 'diverifikasi_at', 'catatan',
    ];

    protected $casts = [
        'tanggal_sk'      => 'date',
        'tmt_jabatan'     => 'date',
        'diverifikasi_at' => 'datetime',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', self::STATUS_MENUNGGU);
    }
}
