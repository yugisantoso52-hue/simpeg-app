<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Perbaikan Relasi Master Data Tendik (Unit Kerja, Jabatan, Golongan)
     * Menggunakan Resolusi Dinamis Berbasis Nama Resmi (Bukan Hardcoded ID)
     * untuk Menjamin Konsistensi 100% antara Lingkungan Offline dan Online.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // Helper Resolusi ID Jabatan berdasarkan Nama
            $resolveJabatan = function ($namaJabatan, $unitKerjaId = null) {
                if (empty($namaJabatan)) return null;

                // 1. Cek exact match
                $j = DB::table('jabatan')->where('nama_jabatan', $namaJabatan)->first();
                if ($j) return $j->id;

                // 2. Cek like match spesifik
                $j = DB::table('jabatan')->where('nama_jabatan', 'like', $namaJabatan . '%')->first();
                if ($j) return $j->id;

                $j = DB::table('jabatan')->where('nama_jabatan', 'like', '%' . $namaJabatan . '%')->first();
                if ($j) return $j->id;

                // 3. Fallback insert jika di database online belum ada
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

                // 1. Cek exact match
                $u = DB::table('unit_kerja')->where('nama_unit', $namaUnit)->first();
                if ($u) return $u->id;

                // 2. Cek alias / like match
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

                // 3. Fallback insert
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

            $tendikUpdates = [
                // === 14 TENDIK PNS ===
                '197507122005011002' => [
                    'unit' => 'Keuangan dan Kepegawaian',
                    'jab'  => 'Kepala Bagian Umum',
                    'gol'  => 'IV/b',
                ],
                '197112232014092001' => [
                    'unit' => 'Akademik dan Kemahasiswaan',
                    'jab'  => 'Ketua Pokja Akademik dan Kemahasiswaan',
                    'gol'  => 'III/c',
                ],
                '196901141995032001' => [
                    'unit' => 'Umum dan Sarana Akademik',
                    'jab'  => 'Ketua Pokja Umum dan Sarana Akademik',
                    'gol'  => 'III/d',
                ],
                '197605212014092001' => [
                    'unit' => 'Keuangan dan Kepegawaian',
                    'jab'  => 'Ketua Pokja Keuangan dan Kepegawaian',
                    'gol'  => 'III/b',
                ],
                '198302172005012001' => [
                    'unit' => 'Laboratorium Keperawatan',
                    'jab'  => 'Pranata Laboratorium Pendidikan Ahli Muda',
                    'gol'  => 'III/c',
                ],
                '198706222010121003' => [
                    'unit' => 'Laboratorium Keperawatan',
                    'jab'  => 'Pranata Laboratorium Pendidikan Ahli Muda',
                    'gol'  => 'III/d',
                ],
                '198702132010122005' => [
                    'unit' => 'Keuangan dan Kepegawaian',
                    'jab'  => 'Pengolah Data dan Informasi',
                    'gol'  => 'III/d',
                ],
                '198704212010122007' => [
                    'unit' => 'Laboratorium Keperawatan',
                    'jab'  => 'Pranata Laboratorium Pendidikan Ahli Muda',
                    'gol'  => 'III/d',
                ],
                '197106142007011002' => [
                    'unit' => 'Akademik dan Kemahasiswaan',
                    'jab'  => 'Pengolah Data dan Informasi',
                    'gol'  => 'III/d',
                ],
                '197101022014091004' => [
                    'unit' => 'Akademik dan Kemahasiswaan',
                    'jab'  => 'Pengadministrasi Perkantoran',
                    'gol'  => 'III/c',
                ],
                '197001161999032001' => [
                    'unit' => 'Akademik dan Kemahasiswaan',
                    'jab'  => 'Pengolah Data dan Informasi',
                    'gol'  => 'III/c',
                ],
                '196906142008101001' => [
                    'unit' => 'Umum dan Sarana Akademik',
                    'jab'  => 'Pengadministrasi Perkantoran',
                    'gol'  => 'II/c',
                ],
                '199409022025062004' => [
                    'unit' => 'Akademik dan Kemahasiswaan',
                    'jab'  => 'Pranata Laboratorium Pendidikan Terampil',
                    'gol'  => 'II/c',
                ],
                '197401241995032001' => [
                    'unit' => 'Keuangan dan Kepegawaian',
                    'jab'  => 'Pengolah Data dan Informasi',
                    'gol'  => 'III/d',
                ],

                // === 14 TENDIK PPPK ===
                '197909112025211041' => [
                    'unit' => 'Keuangan dan Kepegawaian',
                    'jab'  => 'Penata Layanan Operasional',
                    'gol'  => null,
                ],
                '198308062025212035' => [
                    'unit' => 'Keuangan dan Kepegawaian',
                    'jab'  => 'Pengelola Layanan Operasional',
                    'gol'  => null,
                ],
                '199001022025212049' => [
                    'unit' => 'Laboratorium Keperawatan',
                    'jab'  => 'Pengelola Layanan Operasional',
                    'gol'  => null,
                ],
                '199101272025212049' => [
                    'unit' => 'Umum dan Sarana Akademik',
                    'jab'  => 'Pengelola Layanan Operasional',
                    'gol'  => null,
                ],
                '199209072025211055' => [
                    'unit' => 'Umum dan Sarana Akademik',
                    'jab'  => 'Pengadministrasi Perkantoran',
                    'gol'  => null,
                ],
                '199304042025212074' => [
                    'unit' => 'Laboratorium Keperawatan',
                    'jab'  => 'Penata Layanan Operasional',
                    'gol'  => null,
                ],
                '199303242025212051' => [
                    'unit' => 'Laboratorium Keperawatan',
                    'jab'  => 'Penata Layanan Operasional',
                    'gol'  => null,
                ],
                '199008212025211043' => [
                    'unit' => 'Umum dan Sarana Akademik',
                    'jab'  => 'Penata Layanan Operasional',
                    'gol'  => null,
                ],
                '198006152025211060' => [
                    'unit' => 'Keuangan dan Kepegawaian',
                    'jab'  => 'Penata Layanan Operasional',
                    'gol'  => null,
                ],
                '199404262025211057' => [
                    'unit' => 'Akademik dan Kemahasiswaan',
                    'jab'  => 'Pengelola Layanan Operasional',
                    'gol'  => null,
                ],
                '197712162025212008' => [
                    'unit' => 'Akademik dan Kemahasiswaan',
                    'jab'  => 'Penata Layanan Operasional',
                    'gol'  => null,
                ],
                '198401012025211112' => [
                    'unit' => 'Umum dan Sarana Akademik',
                    'jab'  => 'Operator Layanan Operasional',
                    'gol'  => null,
                ],
                '197307302025211017' => [
                    'unit' => 'Umum dan Sarana Akademik',
                    'jab'  => 'Pengadministrasi Perkantoran',
                    'gol'  => null,
                ],
                '198107212025211000' => [
                    'unit' => 'Umum dan Sarana Akademik',
                    'jab'  => 'Operator Layanan Operasional',
                    'gol'  => null,
                ],
            ];

            foreach ($tendikUpdates as $nip => $spec) {
                $pegawai = Pegawai::where('nip', $nip)->first();
                if (!$pegawai) continue;

                $unitKerjaId = $resolveUnitKerja($spec['unit']);
                $jabatanId   = $resolveJabatan($spec['jab'], $unitKerjaId);
                $golonganId  = $resolveGolongan($spec['gol']);

                $updateData = [
                    'unit_kerja_id' => $unitKerjaId,
                    'jabatan_id'    => $jabatanId,
                ];

                if ($golonganId !== null) {
                    $updateData['golongan_id'] = $golonganId;
                }

                $pegawai->update($updateData);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
