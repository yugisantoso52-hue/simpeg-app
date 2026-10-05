<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuccessionPlan extends Model
{
    use HasFactory;

    protected $table = 'succession_plans';

    protected $fillable = [
        'jabatan_target_id',
        'pegawai_id',
        'tahun',
        'peringkat_prioritas',
        'match_score',
        'gap_analysis',
        'status_kesiapan',
        'status_nominasi',
        'catatan_komite',
        'created_by',
    ];

    protected $casts = [
        'tahun'               => 'integer',
        'peringkat_prioritas' => 'integer',
        'match_score'         => 'decimal:2',
        'gap_analysis'        => 'array',
    ];

    public function jabatanTarget(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_target_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeTahun($query, int $year)
    {
        return $query->where('tahun', $year);
    }

    public function scopeJabatan($query, int $jabatanId)
    {
        return $query->where('jabatan_target_id', $jabatanId);
    }

    public function scopeReadyNow($query)
    {
        return $query->where('status_kesiapan', 'Siap Sekarang');
    }

    /**
     * Badge warna status kesiapan suksesi
     */
    public function getKesiapanBadgeClassAttribute(): string
    {
        return match ($this->status_kesiapan) {
            'Siap Sekarang'           => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'Siap 1-2 Tahun'          => 'bg-blue-100 text-blue-800 border-blue-300',
            'Potensial Jangka Panjang'=> 'bg-amber-100 text-amber-800 border-amber-300',
            default                   => 'bg-gray-100 text-gray-700 border-gray-300',
        };
    }
}
