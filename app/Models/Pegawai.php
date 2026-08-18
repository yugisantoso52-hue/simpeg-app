<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';

    public const STATUS_AKTIF = 'Aktif';
    public const STATUS_PENSIUN = 'Pensiun';
    public const STATUS_MUTASI = 'Mutasi';
    public const STATUS_RESIGN = 'Resign';

    protected $fillable = [
        'nip', 'nik', 'nama', 'gelar_depan', 'gelar_belakang',
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama',
        'pendidikan', 'npwp', 'bpjs', 'email', 'no_hp', 'alamat',
        'status_pernikahan', 'nama_pasangan', 'jumlah_anak', 'gol_darah',
        'tinggi_badan', 'berat_badan', 'jenis_pegawai', 'status_asn',
        'unit_kerja_id', 'jabatan_id', 'golongan_id', 'tanggal_masuk',
        'tmt_sk_pertama', 'tmt_pangkat_terakhir', 'tmt_kgb_terakhir',
        'file_sk_pertama', 'file_sk_pangkat_terakhir', 'file_sk_kgb_terakhir',
        'satyalancana_terakhir', 'satyalancana_berikutnya', 'status_pegawai', 'foto',
        'kgb_berikutnya', 'kp_berikutnya'
    ];

    protected $casts = [
        'tanggal_lahir'           => 'date',
        'tanggal_masuk'           => 'date',
        'tmt_sk_pertama'          => 'date',
        'tmt_pangkat_terakhir'    => 'date',
        'tmt_kgb_terakhir'        => 'date',
        'satyalancana_berikutnya' => 'date',
        'kgb_berikutnya'          => 'date',
        'kp_berikutnya'           => 'date',
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

    /* --- ACCESSORS --- */

    protected function namaLengkap(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(
                ($this->gelar_depan ? $this->gelar_depan . ' ' : '') .
                $this->nama .
                ($this->gelar_belakang ? ', ' . $this->gelar_belakang : '')
            )
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
                if (!empty($this->pendidikan)) {
                    return $this->pendidikan;
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
            get: fn () => $this->foto ? asset('storage/' . $this->foto) : asset('images/default-user.png')
        );
    }

    /* --- ACCESSORS URL SK --- */

    protected function fileSkPertamaUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file_sk_pertama ? asset('storage/' . $this->file_sk_pertama) : null
        );
    }

    protected function fileSkPangkatTerakhirUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file_sk_pangkat_terakhir ? asset('storage/' . $this->file_sk_pangkat_terakhir) : null
        );
    }

    protected function fileSkKgbTerakhirUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file_sk_kgb_terakhir ? asset('storage/' . $this->file_sk_kgb_terakhir) : null
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
}