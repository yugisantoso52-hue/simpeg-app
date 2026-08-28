<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\RiwayatPenghargaan;
use App\Models\RiwayatOrganisasi;
use App\Models\RiwayatPublikasi;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';

    public const STATUS_AKTIF         = 'Aktif';
    public const STATUS_TUGAS_BELAJAR = 'Tugas Belajar';
    public const STATUS_NON_AKTIF     = 'Non Aktif';
    public const STATUS_PENSIUN       = 'Pensiun';
    public const STATUS_MUTASI        = 'Mutasi';
    public const STATUS_RESIGN        = 'Resign';

    protected $fillable = [
        // Identitas Utama
        'nip', 'karpeg_karis_karsu', 'nidn_nuptk', 'nama', 'gelar_depan', 'gelar_belakang',
        // Data Pribadi
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama',
        // Kontak
        'email', 'no_hp', 'alamat',
        // Status Keluarga
        'status_pernikahan', 'nama_pasangan', 'jumlah_anak',
        // Kepegawaian
        'jenis_pegawai', 'status_asn', 'pendidikan_terakhir',
        'unit_kerja_id', 'jabatan_id', 'golongan_id',
        // Masa Kerja Golongan (MKG)
        'mkg_tahun', 'mkg_bulan',
        // Tanggal Masuk & TMT
        'tanggal_masuk', 'tmt_sk_pertama', 'tmt_pangkat_terakhir', 'tmt_kgb_terakhir',
        // File SK
        'file_sk_pertama', 'file_sk_pangkat_terakhir', 'file_sk_kgb_terakhir',
        // Nomor & Tanggal SK
        'nomor_sk_pertama', 'tanggal_sk_pertama', 'nomor_sk_pangkat_terakhir', 'tanggal_sk_pangkat_terakhir',
        // Satyalancana & Status
        'satyalancana_terakhir', 'satyalancana_berikutnya', 'status_pegawai', 'foto',
        'kgb_berikutnya', 'kp_berikutnya',
        // Kontak Tambahan & Domisili
        'no_hp_darurat', 'nama_kontak_darurat', 'hubungan_kontak_darurat',
        'alamat_domisili', 'kode_pos', 'kota_domisili', 'provinsi',
        // Kepegawaian Teknis
        'jenis_jabatan', 'angka_kredit', 'batas_usia_pensiun', 'tanggal_pensiun',
        'no_sk_pensiun', 'tmt_pensiun', 'jenis_kontrak',
        'tanggal_kontrak_mulai', 'tanggal_kontrak_selesai',
    ];

    protected $casts = [
        'tanggal_lahir'           => 'date',
        'tanggal_masuk'           => 'date',
        'tmt_sk_pertama'          => 'date',
        'tmt_pangkat_terakhir'    => 'date',
        'tmt_kgb_terakhir'        => 'date',
        'tanggal_sk_pertama'      => 'date',
        'tanggal_sk_pangkat_terakhir' => 'date',
        'satyalancana_berikutnya' => 'date',
        'kgb_berikutnya'          => 'date',
        'kp_berikutnya'           => 'date',
        // Kepegawaian Teknis
        'tanggal_pensiun'         => 'date',
        'tmt_pensiun'             => 'date',
        'tanggal_kontrak_mulai'   => 'date',
        'tanggal_kontrak_selesai' => 'date',
        'angka_kredit'            => 'decimal:2',
    ];

    /**
     * Auto-calculate KGB (+2 Thn), KP (+4 Thn), & Satyalancana (10/20/30 Thn) saat simpan data
     */
    protected static function booted(): void
    {
        static::saving(function (Pegawai $pegawai) {
            // 1. Auto hitung KGB berikutnya (+2 Tahun)
            if (empty($pegawai->kgb_berikutnya) && !empty($pegawai->tmt_kgb_terakhir)) {
                $pegawai->kgb_berikutnya = Carbon::parse($pegawai->tmt_kgb_terakhir)->addYears(2)->toDateString();
            }

            // 2. Auto hitung KP berikutnya (+4 Tahun)
            if (empty($pegawai->kp_berikutnya) && !empty($pegawai->tmt_pangkat_terakhir)) {
                $pegawai->kp_berikutnya = Carbon::parse($pegawai->tmt_pangkat_terakhir)->addYears(4)->toDateString();
            }

            // 3. Auto hitung Satyalancana berikutnya (10, 20, 30 Tahun)
            $tmtMasuk = $pegawai->tanggal_masuk ?? $pegawai->tmt_sk_pertama;
            if (empty($pegawai->satyalancana_berikutnya) && !empty($tmtMasuk)) {
                $start = Carbon::parse($tmtMasuk);
                $years = $start->diffInYears(now());

                if ($years < 10) {
                    $pegawai->satyalancana_berikutnya = $start->copy()->addYears(10)->toDateString();
                } elseif ($years < 20) {
                    $pegawai->satyalancana_berikutnya = $start->copy()->addYears(20)->toDateString();
                } elseif ($years < 30) {
                    $pegawai->satyalancana_berikutnya = $start->copy()->addYears(30)->toDateString();
                }
            }

            // 4. Auto hitung tanggal pensiun dari BUP + tanggal_lahir
            if (empty($pegawai->tanggal_pensiun) && !empty($pegawai->tanggal_lahir) && !empty($pegawai->batas_usia_pensiun)) {
                $pegawai->tanggal_pensiun = Carbon::parse($pegawai->tanggal_lahir)
                    ->addYears($pegawai->batas_usia_pensiun)
                    ->toDateString();
            }
        });

        static::saved(function (Pegawai $pegawai) {
            \Illuminate\Support\Facades\Cache::forget('dashboard_statistics');
            \Illuminate\Support\Facades\Cache::forget('dashboard_grafik_golongan');
            \Illuminate\Support\Facades\Cache::forget('dashboard_grafik_pendidikan');
            \Illuminate\Support\Facades\Cache::forget('dashboard_grafik_unit');
            \Illuminate\Support\Facades\Cache::forget('pegawai_statistics');
        });

        static::deleted(function (Pegawai $pegawai) {
            \Illuminate\Support\Facades\Cache::forget('dashboard_statistics');
            \Illuminate\Support\Facades\Cache::forget('dashboard_grafik_golongan');
            \Illuminate\Support\Facades\Cache::forget('dashboard_grafik_pendidikan');
            \Illuminate\Support\Facades\Cache::forget('dashboard_grafik_unit');
            \Illuminate\Support\Facades\Cache::forget('pegawai_statistics');
        });
    }

    /* --- RELATIONSHIPS --- */

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function golongan(): BelongsTo
    {
        return $this->belongsTo(Golongan::class, 'golongan_id');
    }

    public function riwayatPendidikan(): HasMany
    {
        return $this->hasMany(RiwayatPendidikan::class, 'pegawai_id'); 
    }

    public function riwayatDiklat(): HasMany
    {
        return $this->hasMany(RiwayatDiklat::class, 'pegawai_id');
    }

    public function riwayatPangkat(): HasMany
    {
        return $this->hasMany(RiwayatPangkat::class, 'pegawai_id');
    }

    public function riwayatJabatan(): HasMany
    {
        return $this->hasMany(RiwayatJabatan::class, 'pegawai_id');
    }

    public function mutasi(): HasMany
    {
        return $this->hasMany(MutasiPegawai::class, 'pegawai_id');
    }

    public function riwayatStrSip(): HasMany
    {
        return $this->hasMany(RiwayatStrSip::class, 'pegawai_id');
    }

    public function pengajuanCuti(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'pegawai_id');
    }

    public function tugasBelajar(): HasMany
    {
        return $this->hasMany(TugasBelajar::class, 'pegawai_id');
    }

    public function riwayatSkp(): HasMany
    {
        return $this->hasMany(RiwayatSkp::class, 'pegawai_id')->orderBy('tahun', 'desc');
    }

    public function riwayatPenghargaan(): HasMany
    {
        return $this->hasMany(RiwayatPenghargaan::class, 'pegawai_id')->orderBy('tanggal_terima', 'desc');
    }

    public function riwayatOrganisasi(): HasMany
    {
        return $this->hasMany(RiwayatOrganisasi::class, 'pegawai_id');
    }

    public function riwayatPublikasi(): HasMany
    {
        return $this->hasMany(RiwayatPublikasi::class, 'pegawai_id')->orderBy('tahun_terbit', 'desc');
    }

    public function getSkpTahun(int $year): ?RiwayatSkp
    {
        return $this->riwayatSkp->firstWhere('tahun', $year);
    }

    /**
     * Hitung sisa kuota cuti tahunan pada tahun berjalan (Standar: 12 hari kerja)
     */
    public function getSisaCutiTahunanAttribute(): int
    {
        $currentYear = now()->year;
        $cutiTerpakai = $this->pengajuanCuti()
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->where('status', 'Disetujui')
            ->whereYear('tanggal_mulai', $currentYear)
            ->sum('jumlah_hari');

        return max(0, 12 - (int)$cutiTerpakai);
    }

    /* --- ACCESSORS --- */

    protected function nip(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => preg_replace('/[^0-9]/', '', (string)$value),
            set: fn ($value) => preg_replace('/[^0-9]/', '', (string)$value),
        );
    }

    protected function namaLengkap(): Attribute
    {
        return Attribute::make(
            get: function () {
                $gelarDepan = trim((string)($this->gelar_depan ?? ''));
                if ($gelarDepan !== '') {
                    // Pastikan gelar depan diakhiri tanda titik (.)
                    if (!str_ends_with($gelarDepan, '.')) {
                        $gelarDepan .= '.';
                    }
                    $gelarDepan .= ' ';
                }

                $namaUtama = trim((string)($this->nama ?? ''));

                // Jika nama utama sudah diawali dengan gelar depan, bersihkan agar tidak duplikat
                if ($gelarDepan !== '') {
                    $prefixWithoutDot = trim(rtrim($gelarDepan, '. '));
                    $prefixWithDot = trim($gelarDepan);
                    if (str_starts_with($namaUtama, $prefixWithDot)) {
                        $namaUtama = trim(substr($namaUtama, strlen($prefixWithDot)));
                    } elseif (str_starts_with($namaUtama, $prefixWithoutDot)) {
                        $namaUtama = trim(substr($namaUtama, strlen($prefixWithoutDot)));
                    }
                }

                $gelarBelakang = trim((string)($this->gelar_belakang ?? ''));
                if ($gelarBelakang !== '') {
                    if (str_contains($namaUtama, $gelarBelakang)) {
                        $namaUtama = str_replace([', ' . $gelarBelakang, ',' . $gelarBelakang, $gelarBelakang], '', $namaUtama);
                        $namaUtama = trim($namaUtama, " \t\n\r\0\x0B,");
                    }
                    $gelarBelakang = ', ' . $gelarBelakang;
                }

                return trim($gelarDepan . $namaUtama . $gelarBelakang);
            }
        );
    }

    protected function umur(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tanggal_lahir ? $this->tanggal_lahir->age : null
        );
    }

    protected function masaKerja(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tanggal_masuk ? $this->tanggal_masuk->diffInYears(now()) : null
        );
    }

    /**
     * Accessor: Menghitung Masa Kerja Otomatis Format (X Thn Y Bln)
     */
    protected function masaKerjaFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                $tmt = $this->tanggal_masuk ?? $this->tmt_sk_pertama;

                if (!$tmt) {
                    return '0 Thn 0 Bln';
                }

                $start = Carbon::parse($tmt);
                $diff = $start->diff(Carbon::now());

                return "{$diff->y} Thn {$diff->m} Bln";
            }
        );
    }

    /**
     * Accessor: Mengambil nama Pendidikan secara aman
     */
    protected function pendidikanTampil(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!empty($this->pendidikan_terakhir)) {
                    return $this->pendidikan_terakhir;
                }

                if ($this->relationLoaded('riwayatPendidikan')) {
                    $terakhir = $this->riwayatPendidikan->last();
                    return $terakhir->tingkat_pendidikan ?? $terakhir->jenjang ?? $terakhir->pendidikan ?? '-';
                }

                return '-';
            }
        );
    }

    protected function fotoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->foto && $this->id) {
                    return route('pegawai.foto', $this->id);
                }
                return 'https://ui-avatars.com/api/?name=' . urlencode($this->nama_lengkap ?? $this->nama ?? 'User') . '&color=7F9CF5&background=EBF4FF';
            }
        );
    }

    /* --- ACCESSORS URL SK --- */

    protected function fileSkPertamaUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file_sk_pertama ? route('document.preview', ['path' => $this->file_sk_pertama]) : null
        );
    }

    protected function fileSkPangkatTerakhirUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file_sk_pangkat_terakhir ? route('document.preview', ['path' => $this->file_sk_pangkat_terakhir]) : null
        );
    }

    protected function fileSkKgbTerakhirUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file_sk_kgb_terakhir ? route('document.preview', ['path' => $this->file_sk_kgb_terakhir]) : null
        );
    }

    /**
     * Accessor: TMT Pangkat Kedepan (+4 Tahun)
     */
    protected function kpBerikutnyaKalkulasi(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->kp_berikutnya) {
                    return $this->kp_berikutnya;
                }
                return $this->tmt_pangkat_terakhir ? $this->tmt_pangkat_terakhir->copy()->addYears(4) : null;
            }
        );
    }

    /**
     * Accessor: TMT KGB Kedepan (+2 Tahun)
     */
    protected function kgbBerikutnyaKalkulasi(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->kgb_berikutnya) {
                    return $this->kgb_berikutnya;
                }
                return $this->tmt_kgb_terakhir ? $this->tmt_kgb_terakhir->copy()->addYears(2) : null;
            }
        );
    }

    /**
     * Accessor: Satyalancana (Otomatis berdasarkan Masa Kerja jika data di DB kosong)
     */
    protected function satyalancanaTampil(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!empty($this->satyalancana_terakhir)) {
                    return $this->satyalancana_terakhir;
                }

                $tmt = $this->tanggal_masuk ?? $this->tmt_sk_pertama;
                if (!$tmt) return '-';

                $tahunMasaKerja = Carbon::parse($tmt)->diffInYears(now());

                if ($tahunMasaKerja >= 30) {
                    return 'Satyalancana XXX Thn';
                } elseif ($tahunMasaKerja >= 20) {
                    return 'Satyalancana XX Thn';
                } elseif ($tahunMasaKerja >= 10) {
                    return 'Satyalancana X Thn';
                }

                return '-';
            }
        );
    }

    /* --- QUERY SCOPES --- */

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_pegawai', self::STATUS_AKTIF);
    }

    public function scopeAsn(Builder $query): Builder
    {
        return $query->where('status_asn', 'ASN');
    }

    public function scopeNonAsn(Builder $query): Builder
    {
        return $query->where('status_asn', 'Non ASN');
    }

    /**
     * Scope: Filter data Dosen (Pendidik / Dosen Fungsional)
     */
    public function scopeDosen(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('jenis_pegawai', 'like', '%Dosen%')
              ->orWhere(function ($sq) {
                  $sq->whereNotNull('nidn_nuptk')
                     ->where('nidn_nuptk', '!=', '')
                     ->where('nidn_nuptk', '!=', '-');
              })
              ->orWhereHas('jabatan', function ($jq) {
                  $jq->where('nama_jabatan', 'like', '%Dosen%')
                    ->orWhere('nama_jabatan', 'like', '%Lektor%')
                    ->orWhere('nama_jabatan', 'like', '%Asisten Ahli%')
                    ->orWhere('nama_jabatan', 'like', '%Guru Besar%')
                    ->orWhere('nama_jabatan', 'like', '%Profesor%');
              });
        });
    }

    /**
     * Scope: Filter data Tenaga Kependidikan (Tendik / Staff Administrasi / Laboran / Teknisi)
     */
    public function scopeTendik(Builder $query): Builder
    {
        return $query->where(function ($q) {
            // Bukan Dosen dan bukan PHL
            $q->where(function ($sq) {
                $sq->whereIn('jenis_pegawai', ['Tendik', 'Tenaga Kependidikan', 'PNS', 'PPPK'])
                   ->orWhere('jenis_jabatan', 'Pelaksana')
                   ->orWhere('jenis_jabatan', 'Struktural')
                   ->orWhere('status_asn', 'ASN');
            })->where(function ($sq) {
                $sq->where('jenis_pegawai', 'not like', '%Dosen%')
                   ->orWhereNull('jenis_pegawai');
            })->where(function ($sq) {
                $sq->whereNotIn('jenis_pegawai', ['PHL', 'Honorer', 'Tenaga Kontrak', 'Pegawai Harian Lepas'])
                   ->orWhereNull('jenis_pegawai');
            })->where(function ($sq) {
                $sq->whereNull('nidn_nuptk')
                   ->orWhere('nidn_nuptk', '')
                   ->orWhere('nidn_nuptk', '-');
            });
        });
    }

    /**
     * Scope: Filter data Pegawai Harian Lepas (PHL / Honorer / Tenaga Kontrak Non-ASN)
     */
    public function scopePhl(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereIn('jenis_pegawai', ['PHL', 'Honorer', 'Tenaga Kontrak', 'Pegawai Harian Lepas'])
              ->orWhere('status_asn', 'Non ASN')
              ->orWhere('status_asn', 'PHL')
              ->orWhere('jenis_jabatan', 'Tenaga Kontrak')
              ->orWhereNotNull('jenis_kontrak');
        })->where(function ($sq) {
            $sq->where('jenis_pegawai', 'not like', '%Dosen%')
               ->orWhereNull('jenis_pegawai');
        });
    }

    /**
     * Accessor: Deteksi Kategori Kepegawaian (Dosen / Tendik / PHL)
     */
    protected function kategoriKepegawaian(): Attribute
    {
        return Attribute::make(
            get: function () {
                $jenis = strtoupper(trim((string)$this->jenis_pegawai));
                $nidn = trim((string)$this->nidn_nuptk);
                $statusAsn = strtoupper(trim((string)$this->status_asn));
                $jabatanNama = strtoupper(trim((string)($this->jabatan->nama_jabatan ?? '')));

                if (str_contains($jenis, 'DOSEN') || ($nidn !== '' && $nidn !== '-') || str_contains($jabatanNama, 'DOSEN') || str_contains($jabatanNama, 'LEKTOR') || str_contains($jabatanNama, 'GURU BESAR')) {
                    return 'Dosen';
                }

                if (in_array($jenis, ['PHL', 'HONORER', 'TENAGA KONTRAK', 'PEGAWAI HARIAN LEPAS']) || $statusAsn === 'NON ASN' || $statusAsn === 'PHL' || !empty($this->jenis_kontrak)) {
                    return 'PHL';
                }

                return 'Tendik';
            }
        );
    }
}