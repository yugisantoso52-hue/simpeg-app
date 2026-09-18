<?php

namespace App\Models;

use App\Traits\RecordsSyncOutbox;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Logbook extends Model
{
    use HasFactory, RecordsSyncOutbox;

    protected $table = 'logbooks';

    public const STATUS_DRAFT        = 'draft';
    public const STATUS_DIAJUKAN     = 'diajukan';
    public const STATUS_DISETUJUI    = 'disetujui';
    public const STATUS_PERLU_REVISI = 'perlu_revisi';
    public const STATUS_DITOLAK      = 'ditolak';

    public const KATEGORI_LIST = [
        'Tugas Pokok',
        'Tugas Tambahan',
        'Pelayanan / Administrasi',
        'Pembelajaran / Perkuliahan',
        'Penelitian / Publikasi',
        'Pengabdian Masyarakat',
        'Rapat / Koordinasi',
        'Lainnya',
    ];

    protected $fillable = [
        'sync_uuid',
        'pegawai_id',
        'user_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'durasi_menit',
        'kategori_kegiatan',
        'aktivitas',
        'deskripsi_kegiatan',
        'output_kegiatan',
        'jumlah_output',
        'satuan_output',
        'file_lampiran',
        'status',
        'catatan_atasan',
        'diverifikasi_oleh',
        'diverifikasi_pada',
    ];

    protected $casts = [
        'tanggal'           => 'date',
        'durasi_menit'      => 'integer',
        'jumlah_output'     => 'integer',
        'diverifikasi_pada' => 'datetime',
    ];

    /**
     * Relasi ke Data Pegawai
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    /**
     * Relasi ke Akun User pembuat logbook
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke User yang memverifikasi (Atasan / Pimpinan / Admin)
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    /**
     * Scope filter per Bulan dan Tahun
     */
    public function scopePeriode(Builder $query, ?int $month = null, ?int $year = null): Builder
    {
        $month = $month ?: Carbon::now()->month;
        $year = $year ?: Carbon::now()->year;

        return $query->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
    }

    /**
     * Scope filter berdasarkan status
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (!empty($status) && $status !== 'semua') {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope filter berdasarkan kategori
     */
    public function scopeKategori(Builder $query, ?string $kategori): Builder
    {
        if (!empty($kategori) && $kategori !== 'semua') {
            return $query->where('kategori_kegiatan', $kategori);
        }
        return $query;
    }

    /**
     * Format Durasi: misal 150 menit -> "2 Jam 30 Menit"
     */
    public function getDurasiFormattedAttribute(): string
    {
        $menit = (int) $this->durasi_menit;
        if ($menit <= 0) {
            return '0 Menit';
        }

        $jam = floor($menit / 60);
        $sisaMenit = $menit % 60;

        if ($jam > 0 && $sisaMenit > 0) {
            return "{$jam} Jam {$sisaMenit} Menit";
        } elseif ($jam > 0) {
            return "{$jam} Jam";
        }

        return "{$sisaMenit} Menit";
    }

    /**
     * Format Jam Kerja: "08:00 - 10:30"
     */
    public function getJamKerjaFormattedAttribute(): string
    {
        $mulai = $this->jam_mulai ? substr($this->jam_mulai, 0, 5) : '-';
        $selesai = $this->jam_selesai ? substr($this->jam_selesai, 0, 5) : '-';

        return "{$mulai} - {$selesai}";
    }

    /**
     * Format Tanggal Bahasa Indonesia: "Senin, 18 September 2026"
     */
    public function getTanggalFormattedAttribute(): string
    {
        if (!$this->tanggal) {
            return '-';
        }
        return Carbon::parse($this->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y');
    }

    /**
     * Label Status Bahasa Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT        => 'Draft',
            self::STATUS_DIAJUKAN     => 'Menunggu Verifikasi',
            self::STATUS_DISETUJUI    => 'Disetujui',
            self::STATUS_PERLU_REVISI => 'Perlu Revisi',
            self::STATUS_DITOLAK      => 'Ditolak',
            default                   => ucfirst((string) $this->status),
        };
    }

    /**
     * Kelas Warna Badge Status
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT        => 'bg-gray-100 text-gray-700 border-gray-300',
            self::STATUS_DIAJUKAN     => 'bg-amber-50 text-amber-700 border-amber-300',
            self::STATUS_DISETUJUI    => 'bg-emerald-50 text-emerald-700 border-emerald-300',
            self::STATUS_PERLU_REVISI => 'bg-orange-50 text-orange-700 border-orange-300',
            self::STATUS_DITOLAK      => 'bg-rose-50 text-rose-700 border-rose-300',
            default                   => 'bg-gray-100 text-gray-700 border-gray-300',
        };
    }

    /**
     * Cek apakah logbook dapat diubah oleh user tertentu
     */
    public function canEditBy(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Pegawai hanya bisa mengedit miliknya jika status draft atau perlu_revisi
        if ($user->id === $this->user_id) {
            return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PERLU_REVISI], true);
        }

        return false;
    }

    /**
     * Cek apakah logbook dapat dihapus oleh user tertentu
     */
    public function canDeleteBy(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Pegawai hanya bisa menghapus miliknya jika status draft
        if ($user->id === $this->user_id) {
            return $this->status === self::STATUS_DRAFT;
        }

        return false;
    }
}
