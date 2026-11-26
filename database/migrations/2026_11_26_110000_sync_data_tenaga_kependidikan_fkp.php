<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;
use App\Models\KuotaCutiPegawai;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sinkronisasi & Pelengkapan Data 28 Tenaga Kependidikan (Tendik) FKp UNRI
     * Berdasarkan Data Resmi Kepegawaian (14 PNS & 14 PPPK)
     */
    public function up(): void
    {
        DB::transaction(function () {
            // Helper Resolusi ID Jabatan berdasarkan Nama
            $resolveJabatan = function ($namaJabatan, $unitKerjaId = null) {
                if (empty($namaJabatan)) return null;

                $j = DB::table('jabatan')->where('nama_jabatan', $namaJabatan)->first()
                   ?? DB::table('jabatan')->where('nama_jabatan', 'like', $namaJabatan . '%')->first()
                   ?? DB::table('jabatan')->where('nama_jabatan', 'like', '%' . $namaJabatan . '%')->first();

                if ($j) return $j->id;

                $kode = 'JB-' . strtoupper(substr(md5($namaJabatan), 0, 6));
                return DB::table('jabatan')->insertGetId([
                    'kode_jabatan'  => $kode,
                    'nama_jabatan'  => $namaJabatan,
                    'keterangan'    => $namaJabatan,
                    'unit_kerja_id' => $unitKerjaId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            };

            // Helper Resolusi ID Unit Kerja berdasarkan Nama
            $resolveUnitKerja = function ($namaUnit) {
                if (empty($namaUnit)) return null;

                $u = DB::table('unit_kerja')->where('nama_unit', $namaUnit)->first();
                if ($u) return $u->id;

                if (str_contains(strtolower($namaUnit), 'keuangan') || str_contains(strtolower($namaUnit), 'kepegawaian')) {
                    $u = DB::table('unit_kerja')->where('nama_unit', 'like', '%Keuangan%')->first();
                    if ($u) return $u->id;
                }

                if (str_contains(strtolower($namaUnit), 'akademik') && str_contains(strtolower($namaUnit), 'kemahasiswaan')) {
                    $u = DB::table('unit_kerja')->where('nama_unit', 'like', '%Akademik dan Kemahasiswaan%')->first();
                    if ($u) return $u->id;
                }

                if (str_contains(strtolower($namaUnit), 'umum') && str_contains(strtolower($namaUnit), 'sarana')) {
                    $u = DB::table('unit_kerja')->where('nama_unit', 'like', '%Umum%')->where('nama_unit', 'like', '%Sarana%')->first();
                    if ($u) return $u->id;
                }

                if (str_contains(strtolower($namaUnit), 'laboratorium')) {
                    $u = DB::table('unit_kerja')->where('nama_unit', 'like', '%Laboratorium%')->first();
                    if ($u) return $u->id;
                }

                $u = DB::table('unit_kerja')->where('nama_unit', 'like', '%' . $namaUnit . '%')->first();
                if ($u) return $u->id;

                $kode = 'UK-' . strtoupper(substr(md5($namaUnit), 0, 6));
                return DB::table('unit_kerja')->insertGetId([
                    'kode_unit'  => $kode,
                    'nama_unit'  => $namaUnit,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            };

            // Helper Resolusi ID Golongan berdasarkan Nama
            $resolveGolongan = function ($namaGol) {
                if (empty($namaGol)) return null;
                $g = DB::table('golongan')->where('nama_golongan', $namaGol)->first()
                   ?? DB::table('golongan')->where('nama_golongan', 'like', '%' . $namaGol . '%')->first();
                return $g?->id;
            };

            $tendikData = [
                // === 14 TENDIK PNS ===
                [
                    'nip'                  => '197507122005011002',
                    'nama'                 => 'Bakhtiar',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'S.Sos., M.Si',
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1975-07-12',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'IV/b',
                    'jabatan_nama'         => 'Kepala Bagian Umum',
                    'unit_kerja_nama'      => 'Keuangan dan Kepegawaian',
                    'pendidikan_terakhir'  => 'S2',
                    'tmt_sk_pertama'       => '2005-11-01',
                    'tanggal_masuk'        => '2005-11-01',
                    'tmt_pangkat_terakhir' => '2025-11-17',
                    'tmt_kgb_terakhir'     => '2025-04-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '197112232014092001',
                    'nama'                 => 'Zurriati Yusni',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'SE',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1971-12-23',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'III/c',
                    'jabatan_nama'         => 'Ketua Pokja Akademik dan Kemahasiswaan',
                    'unit_kerja_nama'      => 'Akademik dan Kemahasiswaan',
                    'pendidikan_terakhir'  => 'S1',
                    'tmt_sk_pertama'       => '2014-09-01',
                    'tanggal_masuk'        => '2014-09-01',
                    'tmt_pangkat_terakhir' => '2022-10-01',
                    'tmt_kgb_terakhir'     => '2026-01-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 6, 'sisa' => 6, 'catatan' => 'Pengajuan 10-Sep-2026'],
                    ],
                ],
                [
                    'nip'                  => '196901141995032001',
                    'nama'                 => 'Yandrilita Sanra',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'S.Si',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1969-01-14',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'III/d',
                    'jabatan_nama'         => 'Ketua Pokja Umum dan Sarana Akademik',
                    'unit_kerja_nama'      => 'Umum dan Sarana Akademik',
                    'pendidikan_terakhir'  => 'S1',
                    'tmt_sk_pertama'       => '1995-03-01',
                    'tanggal_masuk'        => '1995-03-01',
                    'tmt_pangkat_terakhir' => '2026-01-01',
                    'tmt_kgb_terakhir'     => '2026-01-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '197605212014092001',
                    'nama'                 => 'Dolli Vita Zenitha Harning Arivina',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'SE',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1976-05-21',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'III/b',
                    'jabatan_nama'         => 'Ketua Pokja Keuangan dan Kepegawaian',
                    'unit_kerja_nama'      => 'Keuangan dan Kepegawaian',
                    'pendidikan_terakhir'  => 'S1',
                    'tmt_sk_pertama'       => '2014-09-01',
                    'tanggal_masuk'        => '2014-09-01',
                    'tmt_pangkat_terakhir' => '2022-10-01',
                    'tmt_kgb_terakhir'     => '2024-07-01',
                    'cuti'                 => [
                        2025 => ['awal' => 12, 'terpakai' => 6, 'sisa' => 6, 'catatan' => 'Sisa Cuti N-1'],
                        2026 => ['awal' => 12, 'terpakai' => 12, 'sisa' => 0, 'catatan' => 'Pengajuan 4 Mei 2026'],
                    ],
                ],
                [
                    'nip'                  => '198302172005012001',
                    'nama'                 => 'Eka Febriyanti',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'Am.AK, SKM, M.Si',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1983-02-17',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'III/c',
                    'jabatan_nama'         => 'Pranata Laboratorium Pendidikan Ahli Muda',
                    'unit_kerja_nama'      => 'Laboratorium Keperawatan',
                    'pendidikan_terakhir'  => 'S2',
                    'tmt_sk_pertama'       => '2005-01-01',
                    'tanggal_masuk'        => '2005-01-01',
                    'tmt_pangkat_terakhir' => '2025-02-01',
                    'tmt_kgb_terakhir'     => '2026-01-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 7, 'sisa' => 5, 'catatan' => 'Pengajuan 1 Sept, 13 Nov 2026'],
                    ],
                ],
                [
                    'nip'                  => '198706222010121003',
                    'nama'                 => 'Putra Mulia',
                    'gelar_depan'          => 'Ns.',
                    'gelar_belakang'       => 'S.Kep., M.Si',
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1987-06-22',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'III/d',
                    'jabatan_nama'         => 'Pranata Laboratorium Pendidikan Ahli Muda',
                    'unit_kerja_nama'      => 'Laboratorium Keperawatan',
                    'pendidikan_terakhir'  => 'S2',
                    'tmt_sk_pertama'       => '2010-12-01',
                    'tanggal_masuk'        => '2010-12-01',
                    'tmt_pangkat_terakhir' => '2024-12-01',
                    'tmt_kgb_terakhir'     => '2024-12-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 2, 'sisa' => 10, 'catatan' => 'Pengajuan 15 Juni 2026'],
                    ],
                ],
                [
                    'nip'                  => '198702132010122005',
                    'nama'                 => 'Wandha Paramitha',
                    'gelar_depan'          => 'Ns.',
                    'gelar_belakang'       => 'S.Kep',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1987-02-13',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'III/d',
                    'jabatan_nama'         => 'Pengolah Data dan Informasi',
                    'unit_kerja_nama'      => 'Keuangan dan Kepegawaian',
                    'pendidikan_terakhir'  => 'S1',
                    'tmt_sk_pertama'       => '2010-12-01',
                    'tanggal_masuk'        => '2010-12-01',
                    'tmt_pangkat_terakhir' => '2024-12-01',
                    'tmt_kgb_terakhir'     => '2024-12-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 7, 'sisa' => 5, 'catatan' => 'Pengajuan 13 Mei, 25 Juni 2026'],
                    ],
                ],
                [
                    'nip'                  => '198704212010122007',
                    'nama'                 => 'Trivini Valencia',
                    'gelar_depan'          => 'Ns.',
                    'gelar_belakang'       => 'M.Kep',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1987-04-21',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'III/d',
                    'jabatan_nama'         => 'Pranata Laboratorium Pendidikan Ahli Muda',
                    'unit_kerja_nama'      => 'Laboratorium Keperawatan',
                    'pendidikan_terakhir'  => 'S2',
                    'tmt_sk_pertama'       => '2010-12-01',
                    'tanggal_masuk'        => '2010-12-01',
                    'tmt_pangkat_terakhir' => '2024-12-01',
                    'tmt_kgb_terakhir'     => '2024-12-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 5, 'sisa' => 7, 'catatan' => 'Pengajuan 19 Juli, 14 Okt 2026'],
                    ],
                ],
                [
                    'nip'                  => '197106142007011002',
                    'nama'                 => 'Djunizar',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'S.Sos',
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1971-06-14',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'III/d',
                    'jabatan_nama'         => 'Pengolah Data dan Informasi',
                    'unit_kerja_nama'      => 'Akademik dan Kemahasiswaan',
                    'pendidikan_terakhir'  => 'S1',
                    'tmt_sk_pertama'       => '2007-01-01',
                    'tanggal_masuk'        => '2007-01-01',
                    'tmt_pangkat_terakhir' => '2024-01-01',
                    'tmt_kgb_terakhir'     => '2025-02-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 6, 'sisa' => 6, 'catatan' => 'Pengajuan 25-Sep-2026'],
                    ],
                ],
                [
                    'nip'                  => '197101022014091004',
                    'nama'                 => 'Deni Suryani',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'S.Sos',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1971-01-02',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'III/c',
                    'jabatan_nama'         => 'Pengadministrasi Perkantoran',
                    'unit_kerja_nama'      => 'Akademik dan Kemahasiswaan',
                    'pendidikan_terakhir'  => 'S1',
                    'tmt_sk_pertama'       => '2014-09-01',
                    'tanggal_masuk'        => '2014-09-01',
                    'tmt_pangkat_terakhir' => '2024-12-01',
                    'tmt_kgb_terakhir'     => '2024-08-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '197001161999032001',
                    'nama'                 => 'T. Nurul Aini',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'S.Si',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1970-01-16',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'III/c',
                    'jabatan_nama'         => 'Pengolah Data dan Informasi',
                    'unit_kerja_nama'      => 'Akademik dan Kemahasiswaan',
                    'pendidikan_terakhir'  => 'S1',
                    'tmt_sk_pertama'       => '1999-03-01',
                    'tanggal_masuk'        => '1999-03-01',
                    'tmt_pangkat_terakhir' => '2022-10-01',
                    'tmt_kgb_terakhir'     => '2025-03-01',
                    'cuti'                 => [
                        2025 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => 'Sisa Cuti N-1'],
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '196906142008101001',
                    'nama'                 => 'Saramaidus',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => null,
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1969-06-14',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'II/c',
                    'jabatan_nama'         => 'Pengadministrasi Perkantoran',
                    'unit_kerja_nama'      => 'Umum dan Sarana Akademik',
                    'pendidikan_terakhir'  => 'SMA',
                    'tmt_sk_pertama'       => '2008-10-01',
                    'tanggal_masuk'        => '2008-10-01',
                    'tmt_pangkat_terakhir' => '2024-08-01',
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '199409022025062004',
                    'nama'                 => 'Yusrina',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'A.Md',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1994-09-02',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'II/c',
                    'jabatan_nama'         => 'Pranata Laboratorium Pendidikan Terampil',
                    'unit_kerja_nama'      => 'Akademik dan Kemahasiswaan',
                    'pendidikan_terakhir'  => 'D3 Teknik Informatika',
                    'tmt_sk_pertama'       => '2025-06-01',
                    'tanggal_masuk'        => '2025-06-01',
                    'tmt_pangkat_terakhir' => '2025-06-01',
                    'tmt_kgb_terakhir'     => '2025-06-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '197401241995032001',
                    'nama'                 => 'Yandriwita',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'S.Pd',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1974-01-24',
                    'jenis_pegawai'        => 'PNS',
                    'status_asn'           => 'ASN',
                    'golongan_nama'        => 'III/d',
                    'jabatan_nama'         => 'Pengolah Data dan Informasi',
                    'unit_kerja_nama'      => 'Keuangan dan Kepegawaian',
                    'pendidikan_terakhir'  => 'S1',
                    'tmt_sk_pertama'       => '1995-03-01',
                    'tanggal_masuk'        => '1995-03-01',
                    'tmt_pangkat_terakhir' => '2019-10-01',
                    'tmt_kgb_terakhir'     => '2026-03-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 12, 'sisa' => 0, 'catatan' => 'Pengajuan 4 Agus, 12 Okt 2026'],
                    ],
                ],

                // === 14 TENDIK PPPK ===
                [
                    'nip'                  => '197909112025211041',
                    'nama'                 => 'Zelfi Ardi',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'SE',
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1979-09-11',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Penata Layanan Operasional',
                    'unit_kerja_nama'      => 'Keuangan dan Kepegawaian',
                    'pendidikan_terakhir'  => 'S1 Ekonomi Manajemen',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '198308062025212035',
                    'nama'                 => 'Sisriani',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'A.Md',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1983-08-06',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Pengelola Layanan Operasional',
                    'unit_kerja_nama'      => 'Keuangan dan Kepegawaian',
                    'pendidikan_terakhir'  => 'D3 Keuangan',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '199001022025212049',
                    'nama'                 => 'Atika Ayuningtyas Adhe',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'A.Md.Kep',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1990-01-02',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Pengelola Layanan Operasional',
                    'unit_kerja_nama'      => 'Laboratorium Keperawatan',
                    'pendidikan_terakhir'  => 'D3 Keperawatan',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '199101272025212049',
                    'nama'                 => 'Rahayu Utami',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'A.Md',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1991-01-27',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Pengelola Layanan Operasional',
                    'unit_kerja_nama'      => 'Umum dan Sarana Akademik',
                    'pendidikan_terakhir'  => 'D3 Manajemen',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 7, 'sisa' => 5, 'catatan' => 'Cuti Melahirkan 3 bln, Pengajuan 23 Des 2025, 2 Nov 2026'],
                    ],
                ],
                [
                    'nip'                  => '199209072025211055',
                    'nama'                 => 'Reski Utama',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => null,
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1992-09-07',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Pengadministrasi Perkantoran',
                    'unit_kerja_nama'      => 'Umum dan Sarana Akademik',
                    'pendidikan_terakhir'  => 'SMU',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '199304042025212074',
                    'nama'                 => 'Rosnia Safitri',
                    'gelar_depan'          => 'Ns.',
                    'gelar_belakang'       => 'S.Kep',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1993-04-04',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Penata Layanan Operasional',
                    'unit_kerja_nama'      => 'Laboratorium Keperawatan',
                    'pendidikan_terakhir'  => 'S1 Keperawatan',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 3, 'sisa' => 9, 'catatan' => 'Pengajuan 6 Okt 2026'],
                    ],
                ],
                [
                    'nip'                  => '199303242025212051',
                    'nama'                 => 'Cia Novita',
                    'gelar_depan'          => 'Ns.',
                    'gelar_belakang'       => 'S.Kep',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1993-03-24',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Penata Layanan Operasional',
                    'unit_kerja_nama'      => 'Laboratorium Keperawatan',
                    'pendidikan_terakhir'  => 'S1 Keperawatan',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '199008212025211043',
                    'nama'                 => 'Wendi Kurniawan',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'S.I.Kom',
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1990-08-21',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Penata Layanan Operasional',
                    'unit_kerja_nama'      => 'Umum dan Sarana Akademik',
                    'pendidikan_terakhir'  => 'S1 Ilmu Komunikasi',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '198006152025211060',
                    'nama'                 => 'Rahmad Hidayat',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'ST',
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1980-06-15',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Penata Layanan Operasional',
                    'unit_kerja_nama'      => 'Keuangan dan Kepegawaian',
                    'pendidikan_terakhir'  => 'S1 Teknik Sipil',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '199404262025211057',
                    'nama'                 => 'Yogi Ariangga Dewa',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'A.Md',
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1994-04-26',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Pengelola Layanan Operasional',
                    'unit_kerja_nama'      => 'Akademik dan Kemahasiswaan',
                    'pendidikan_terakhir'  => 'D3 Komputer',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '197712162025212008',
                    'nama'                 => 'Yennawati',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'SM',
                    'jenis_kelamin'        => 'P',
                    'tanggal_lahir'        => '1977-12-16',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Penata Layanan Operasional',
                    'unit_kerja_nama'      => 'Akademik dan Kemahasiswaan',
                    'pendidikan_terakhir'  => 'S1 Manajemen',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '198401012025211112',
                    'nama'                 => 'Syafei',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => null,
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1984-01-01',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Operator Layanan Operasional',
                    'unit_kerja_nama'      => 'Umum dan Sarana Akademik',
                    'pendidikan_terakhir'  => 'SMA',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '197307302025211017',
                    'nama'                 => 'Dandi Aznan',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => null,
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1973-07-30',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Pengadministrasi Perkantoran',
                    'unit_kerja_nama'      => 'Umum dan Sarana Akademik',
                    'pendidikan_terakhir'  => 'SMA',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
                [
                    'nip'                  => '198107212025211000',
                    'nama'                 => 'Pramuwasono Bela Negara',
                    'gelar_depan'          => null,
                    'gelar_belakang'       => 'SH',
                    'jenis_kelamin'        => 'L',
                    'tanggal_lahir'        => '1981-07-21',
                    'jenis_pegawai'        => 'PPPK',
                    'status_asn'           => 'PPPK',
                    'golongan_nama'        => null,
                    'jabatan_nama'         => 'Operator Layanan Operasional',
                    'unit_kerja_nama'      => 'Umum dan Sarana Akademik',
                    'pendidikan_terakhir'  => 'S1 Hukum',
                    'tmt_sk_pertama'       => '2025-10-01',
                    'tanggal_masuk'        => '2025-10-01',
                    'tmt_pangkat_terakhir' => null,
                    'tmt_kgb_terakhir'     => '2025-10-01',
                    'cuti'                 => [
                        2026 => ['awal' => 12, 'terpakai' => 0, 'sisa' => 12, 'catatan' => null],
                    ],
                ],
            ];

            foreach ($tendikData as $item) {
                $cutiRecords = $item['cuti'] ?? [];
                unset($item['cuti']);

                $unitKerjaId = $resolveUnitKerja($item['unit_kerja_nama']);
                $jabatanId   = $resolveJabatan($item['jabatan_nama'], $unitKerjaId);
                $golonganId  = $resolveGolongan($item['golongan_nama']);

                unset($item['unit_kerja_nama'], $item['jabatan_nama'], $item['golongan_nama']);

                $item['unit_kerja_id'] = $unitKerjaId;
                $item['jabatan_id']    = $jabatanId;
                if ($golonganId !== null) {
                    $item['golongan_id'] = $golonganId;
                }

                $pegawai = Pegawai::where('nip', $item['nip'])->first();
                if ($pegawai) {
                    $pegawai->update($item);

                    foreach ($cutiRecords as $tahun => $cuti) {
                        KuotaCutiPegawai::updateOrCreate(
                            [
                                'pegawai_id' => $pegawai->id,
                                'tahun'      => $tahun,
                            ],
                            [
                                'kuota_awal'          => $cuti['awal'],
                                'kuota_terpakai'      => $cuti['terpakai'],
                                'sisa_kuota'          => $cuti['sisa'],
                                'catatan_penyesuaian' => $cuti['catatan'],
                            ]
                        );
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
