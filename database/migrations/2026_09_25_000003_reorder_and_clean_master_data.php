<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;
use App\Models\RiwayatJabatan;

return new class extends Migration
{
    /**
     * Run the migrations to clean duplicates & re-order Master Data from highest to lowest hierarchy.
     */
    public function up(): void
    {
        // 1. Ambil pemetaan nama lama ke ID baru yang akan dipasang
        // -------------------------------------------------------------------------
        
        // 1A. DATA UNIT KERJA RAPI (URUT HIERARKI TERTINGGI KE TERENDAH)
        $unitKerjas = [
            [
                'kode_unit'  => 'UK-001',
                'nama_unit'  => 'Dekanat / Pimpinan Fakultas',
                'keterangan' => 'Unsur Pimpinan Fakultas Keperawatan UNRI (Dekan & para Wadek)',
            ],
            [
                'kode_unit'  => 'UK-002',
                'nama_unit'  => 'Senat Fakultas',
                'keterangan' => 'Unsur Pertimbangan Normatif dan Perwakilan Fakultas',
            ],
            [
                'kode_unit'  => 'UK-003',
                'nama_unit'  => 'Satuan Penjamin Mutu (SPMF) & GPM',
                'keterangan' => 'Unsur Penjaminan Mutu Fakultas & Prodi (GPM S1, S2, Ners)',
            ],
            [
                'kode_unit'  => 'UK-004',
                'nama_unit'  => 'Jurusan Preklinik Keperawatan',
                'keterangan' => 'Jurusan Preklinik Keperawatan Fakultas Keperawatan',
            ],
            [
                'kode_unit'  => 'UK-005',
                'nama_unit'  => 'Program Studi S1 Keperawatan',
                'keterangan' => 'Program Studi Sarjana Keperawatan (S1)',
            ],
            [
                'kode_unit'  => 'UK-006',
                'nama_unit'  => 'Program Studi S2 Keperawatan',
                'keterangan' => 'Program Studi Magister Keperawatan (S2)',
            ],
            [
                'kode_unit'  => 'UK-007',
                'nama_unit'  => 'Jurusan Klinik dan Komunitas',
                'keterangan' => 'Jurusan Klinik dan Komunitas Fakultas Keperawatan',
            ],
            [
                'kode_unit'  => 'UK-008',
                'nama_unit'  => 'Program Studi Profesi Ners',
                'keterangan' => 'Program Studi Pendidikan Profesi Ners',
            ],
            [
                'kode_unit'  => 'UK-009',
                'nama_unit'  => 'Bagian Umum',
                'keterangan' => 'Bagian Tata Usaha & Administrasi Umum Fakultas Keperawatan',
            ],
            [
                'kode_unit'  => 'UK-010',
                'nama_unit'  => 'Pokja Akademik dan Kemahasiswaan',
                'keterangan' => 'Kelompok Kerja Bidang Akademik dan Kemahasiswaan',
            ],
            [
                'kode_unit'  => 'UK-011',
                'nama_unit'  => 'Pokja Keuangan dan Kepepegawaian',
                'keterangan' => 'Kelompok Kerja Keuangan dan Kepepegawaian',
            ],
            [
                'kode_unit'  => 'UK-012',
                'nama_unit'  => 'Pokja Umum Sarana Akademik',
                'keterangan' => 'Kelompok Kerja Umum Sarana Akademik',
            ],
            [
                'kode_unit'  => 'UK-013',
                'nama_unit'  => 'Laboratorium Keperawatan',
                'keterangan' => 'Unit Pelaksana Teknis Laboratorium Keperawatan (9 Ruang Lab)',
            ],
            [
                'kode_unit'  => 'UK-014',
                'nama_unit'  => 'Unit-Unit Fungsional',
                'keterangan' => 'Unit Etik, CBT, NEDU, P2M, Kerjasama, Konseling, Humas, PPID',
            ],
        ];

        // Backup relasi unit_kerja_id di pegawai
        $pegawaiUkBackup = DB::table('pegawai')->whereNotNull('unit_kerja_id')->get(['id', 'unit_kerja_id']);
        $oldUkMap = DB::table('unit_kerja')->pluck('nama_unit', 'id')->toArray();

        // 1B. DATA JABATAN RAPI (URUT HIERARKI MANAGEMENT TERATAS SAMPAI STAFF PELAKSANA)
        $jabatans = [
            // --- 1. UNSUR PIMPINAN FAKULTAS (DEKANAT) ---
            [
                'kode_jabatan' => 'JB-001',
                'nama_jabatan' => 'Dekan',
                'keterangan'   => 'Pimpinan Tertinggi Fakultas Keperawatan UNRI',
            ],
            [
                'kode_jabatan' => 'JB-002',
                'nama_jabatan' => 'Wakil Dekan I (Bid. Akademik)',
                'keterangan'   => 'Wakil Dekan Bidang Akademik (WD I)',
            ],
            [
                'kode_jabatan' => 'JB-003',
                'nama_jabatan' => 'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'keterangan'   => 'Wakil Dekan Bidang Keuangan dan Umum (WD II)',
            ],
            [
                'kode_jabatan' => 'JB-004',
                'nama_jabatan' => 'Wakil Dekan III (Bid. Kemahasiswaan, Alumni dan Kerjasama)',
                'keterangan'   => 'Wakil Dekan Bidang Kemahasiswaan, Alumni dan Kerjasama (WD III)',
            ],

            // --- 2. UNSUR SENAT & PENJAMINAN MUTU ---
            [
                'kode_jabatan' => 'JB-005',
                'nama_jabatan' => 'Ketua Senat Fakultas',
                'keterangan'   => 'Ketua Senat Fakultas Keperawatan UNRI',
            ],
            [
                'kode_jabatan' => 'JB-006',
                'nama_jabatan' => 'Sekretaris Senat Fakultas',
                'keterangan'   => 'Sekretaris Senat Fakultas Keperawatan UNRI',
            ],
            [
                'kode_jabatan' => 'JB-007',
                'nama_jabatan' => 'Kepala SPMF / GPM',
                'keterangan'   => 'Penanggung Jawab Penjaminan Mutu Fakultas (SPMF) & GPM',
            ],

            // --- 3. UNSUR JURUSAN & KOORDINATOR PROGRAM STUDI ---
            [
                'kode_jabatan' => 'JB-008',
                'nama_jabatan' => 'Ketua Jurusan Preklinik Keperawatan',
                'keterangan'   => 'Ketua Jurusan Preklinik Keperawatan',
            ],
            [
                'kode_jabatan' => 'JB-009',
                'nama_jabatan' => 'Sekretaris Jurusan Preklinik Keperawatan',
                'keterangan'   => 'Sekretaris Jurusan Preklinik Keperawatan',
            ],
            [
                'kode_jabatan' => 'JB-010',
                'nama_jabatan' => 'Ketua Jurusan Klinik dan Komunitas',
                'keterangan'   => 'Ketua Jurusan Klinik dan Komunitas',
            ],
            [
                'kode_jabatan' => 'JB-011',
                'nama_jabatan' => 'Sekretaris Jurusan Klinik dan Komunitas',
                'keterangan'   => 'Sekretaris Jurusan Klinik dan Komunitas',
            ],
            [
                'kode_jabatan' => 'JB-012',
                'nama_jabatan' => 'Koordinator Prodi S1 Keperawatan',
                'keterangan'   => 'Koordinator Program Studi Sarjana (S1) Keperawatan',
            ],
            [
                'kode_jabatan' => 'JB-013',
                'nama_jabatan' => 'Koordinator Prodi S2 Keperawatan',
                'keterangan'   => 'Koordinator Program Studi Magister (S2) Keperawatan',
            ],
            [
                'kode_jabatan' => 'JB-014',
                'nama_jabatan' => 'Koordinator Prodi Ners',
                'keterangan'   => 'Koordinator Program Studi Pendidikan Profesi Ners',
            ],
            [
                'kode_jabatan' => 'JB-015',
                'nama_jabatan' => 'Ketua Kelompok Jabatan Fungsional Dosen (KJFD)',
                'keterangan'   => 'Ketua Kelompok Jabatan Fungsional Dosen',
            ],

            // --- 4. UNSUR TATA USAHA & KELOMPOK KERJA (POKJA) ---
            [
                'kode_jabatan' => 'JB-016',
                'nama_jabatan' => 'Kepala Bagian Umum',
                'keterangan'   => 'Kepala Bagian Umum / Kabag TU Fakultas Keperawatan',
            ],
            [
                'kode_jabatan' => 'JB-017',
                'nama_jabatan' => 'Ka Pokja Akademik',
                'keterangan'   => 'Kepala Pokja Akademik dan Kemahasiswaan',
            ],
            [
                'kode_jabatan' => 'JB-018',
                'nama_jabatan' => 'Ka Pokja Keu-Kepeg',
                'keterangan'   => 'Kepala Pokja Keuangan dan Kepepegawaian',
            ],
            [
                'kode_jabatan' => 'JB-019',
                'nama_jabatan' => 'Ka Pokja Umum Sarana Akademik',
                'keterangan'   => 'Kepala Pokja Umum Sarana Akademik',
            ],

            // --- 5. UNSUR UPT LABORATORIUM & UNIT FUNGSIONAL ---
            [
                'kode_jabatan' => 'JB-020',
                'nama_jabatan' => 'Kepala UPT Laboratorium Keperawatan',
                'keterangan'   => 'Kepala Laboratorium Keperawatan Fakultas',
            ],
            [
                'kode_jabatan' => 'JB-021',
                'nama_jabatan' => 'Kepala Unit Fungsional',
                'keterangan'   => 'Kepala Unit Etik / CBT / NEDU / P2M / Konseling / Humas / PPID',
            ],

            // --- 6. UNSUR JABATAN FUNGSIONAL DOSEN ---
            [
                'kode_jabatan' => 'JB-022',
                'nama_jabatan' => 'Guru Besar',
                'keterangan'   => 'Jabatan Fungsional Dosen — Guru Besar / Profesor',
            ],
            [
                'kode_jabatan' => 'JB-023',
                'nama_jabatan' => 'Lektor Kepala',
                'keterangan'   => 'Jabatan Fungsional Dosen — Lektor Kepala',
            ],
            [
                'kode_jabatan' => 'JB-024',
                'nama_jabatan' => 'Lektor',
                'keterangan'   => 'Jabatan Fungsional Dosen — Lektor',
            ],
            [
                'kode_jabatan' => 'JB-025',
                'nama_jabatan' => 'Asisten Ahli',
                'keterangan'   => 'Jabatan Fungsional Dosen — Asisten Ahli',
            ],
            [
                'kode_jabatan' => 'JB-026',
                'nama_jabatan' => 'Dosen S1/S2 Keperawatan',
                'keterangan'   => 'Dosen Fungsional Prodi S1 / S2 Keperawatan',
            ],
            [
                'kode_jabatan' => 'JB-027',
                'nama_jabatan' => 'Dosen Profesi Ners',
                'keterangan'   => 'Dosen Fungsional Prodi Profesi Ners',
            ],

            // --- 7. UNSUR FUNGSIONAL TENDIK, PLP, & STAFF PELAKSANA ---
            [
                'kode_jabatan' => 'JB-028',
                'nama_jabatan' => 'Pranata Laboratorium Pendidikan (PLP / Laboran)',
                'keterangan'   => 'Jabatan Fungsional PLP / Laboran Lab Keperawatan',
            ],
            [
                'kode_jabatan' => 'JB-029',
                'nama_jabatan' => 'Pustakawan',
                'keterangan'   => 'Jabatan Fungsional Pustakawan Perpustakaan Fakultas',
            ],
            [
                'kode_jabatan' => 'JB-030',
                'nama_jabatan' => 'Staff Pokja Akademik dan Kemahasiswaan',
                'keterangan'   => 'Staf Pelaksana Pokja Akademik dan Kemahasiswaan',
            ],
            [
                'kode_jabatan' => 'JB-031',
                'nama_jabatan' => 'Staff Pokja Keu & Kepeg',
                'keterangan'   => 'Staf Pelaksana Pokja Keuangan dan Kepepegawaian',
            ],
            [
                'kode_jabatan' => 'JB-032',
                'nama_jabatan' => 'Staff Pokja Umum Sarana Akademik',
                'keterangan'   => 'Staf Pelaksana Pokja Umum Sarana Akademik',
            ],
            [
                'kode_jabatan' => 'JB-033',
                'nama_jabatan' => 'Pengolah Data dan Informasi',
                'keterangan'   => 'Pelaksana Pengolah Data & Sistem Informasi',
            ],
            [
                'kode_jabatan' => 'JB-034',
                'nama_jabatan' => 'Pengadministrasi Perkantoran',
                'keterangan'   => 'Pelaksana Layanan Administrasi Perkantoran',
            ],
            [
                'kode_jabatan' => 'JB-035',
                'nama_jabatan' => 'Penata Layanan Operasional',
                'keterangan'   => 'Pelaksana Penata Layanan Operasional',
            ],
            [
                'kode_jabatan' => 'JB-036',
                'nama_jabatan' => 'Operator Layanan Operasional',
                'keterangan'   => 'Pelaksana Operator Layanan Operasional',
            ],
            [
                'kode_jabatan' => 'JB-037',
                'nama_jabatan' => 'PHL / PPNPN',
                'keterangan'   => 'Pegawai Harian Lepas / Tenaga Kontrak Non-ASN',
            ],
        ];

        // Backup relasi jabatan_id di pegawai & riwayat_jabatan
        $pegawaiJbBackup = DB::table('pegawai')->whereNotNull('jabatan_id')->get(['id', 'jabatan_id']);
        $riwayatJbBackup = DB::table('riwayat_jabatan')->whereNotNull('jabatan_id')->get(['id', 'jabatan_id']);
        $oldJbMap = DB::table('jabatan')->pluck('nama_jabatan', 'id')->toArray();

        // 1C. DATA JENIS JABATAN RAPI PER KATEGORI
        $jenisJabatans = [
            ['nama_jenis_jabatan' => 'KJFD - MEDIKAL BEDAH', 'keterangan' => 'Departemen Keperawatan Medikal Bedah (KMB)'],
            ['nama_jenis_jabatan' => 'KJFD - GAWAT DARURAT', 'keterangan' => 'Departemen Keperawatan Gawat Darurat & Kritis (KGD)'],
            ['nama_jenis_jabatan' => 'KJFD - MATERNITAS', 'keterangan' => 'Departemen Keperawatan Maternitas'],
            ['nama_jenis_jabatan' => 'KJFD - ANAK', 'keterangan' => 'Departemen Keperawatan Anak'],
            ['nama_jenis_jabatan' => 'KJFD - KELUARGA KOMUNITAS', 'keterangan' => 'Departemen Keperawatan Keluarga & Komunitas'],
            ['nama_jenis_jabatan' => 'KJFD - GERONTIK', 'keterangan' => 'Departemen Keperawatan Gerontik (Lansia)'],
            ['nama_jenis_jabatan' => 'KJFD - JIWA', 'keterangan' => 'Departemen Keperawatan Jiwa'],
            ['nama_jenis_jabatan' => 'KJFD - KLINIK', 'keterangan' => 'Kelompok Jabatan Fungsional Dosen Klinik'],
            ['nama_jenis_jabatan' => 'KJFD - KOMUNITAS', 'keterangan' => 'Kelompok Jabatan Fungsional Dosen Komunitas'],
            ['nama_jenis_jabatan' => 'LAB - BIOMEDIK', 'keterangan' => 'Ruang Keperawatan Laboratorium Biomedik'],
            ['nama_jenis_jabatan' => 'LAB - MEDIKAL BEDAH', 'keterangan' => 'Ruang Keperawatan Laboratorium Medikal Bedah'],
            ['nama_jenis_jabatan' => 'LAB - GAWAT DARURAT', 'keterangan' => 'Ruang Keperawatan Laboratorium Gawat Darurat'],
            ['nama_jenis_jabatan' => 'LAB - JIWA', 'keterangan' => 'Ruang Keperawatan Laboratorium Jiwa'],
            ['nama_jenis_jabatan' => 'LAB - KELUARGA KOMUNITAS', 'keterangan' => 'Ruang Keperawatan Laboratorium Keluarga Komunitas'],
            ['nama_jenis_jabatan' => 'LAB - GERONTIK', 'keterangan' => 'Ruang Keperawatan Laboratorium Gerontik'],
            ['nama_jenis_jabatan' => 'LAB - MATERNITAS', 'keterangan' => 'Ruang Keperawatan Laboratorium Maternitas'],
            ['nama_jenis_jabatan' => 'LAB - ANAK', 'keterangan' => 'Ruang Keperawatan Laboratorium Anak'],
            ['nama_jenis_jabatan' => 'LAB - TUMBUH KEMBANG ANAK', 'keterangan' => 'Ruang Keperawatan Laboratorium Tumbuh Kembang Anak'],
        ];

        // Disable Foreign Key Checks saat pembersihan & penyusunan ulang
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. KOSONGKAN TABEL MASTER TERLEBIH DAHULU
        DB::table('unit_kerja')->truncate();
        DB::table('jabatan')->truncate();
        DB::table('jenis_jabatan')->truncate();

        // 3. RE-INSERT UNIT KERJA TERURUT
        $newUkNameToId = [];
        foreach ($unitKerjas as $uk) {
            $id = DB::table('unit_kerja')->insertGetId([
                'kode_unit'  => $uk['kode_unit'],
                'nama_unit'  => $uk['nama_unit'],
                'keterangan' => $uk['keterangan'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $newUkNameToId[$uk['nama_unit']] = $id;
        }

        // 4. RE-INSERT JABATAN TERURUT
        $newJbNameToId = [];
        foreach ($jabatans as $jb) {
            $id = DB::table('jabatan')->insertGetId([
                'kode_jabatan' => $jb['kode_jabatan'],
                'nama_jabatan' => $jb['nama_jabatan'],
                'keterangan'   => $jb['keterangan'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            $newJbNameToId[$jb['nama_jabatan']] = $id;
        }

        // 5. RE-INSERT JENIS JABATAN TERURUT
        foreach ($jenisJabatans as $jj) {
            DB::table('jenis_jabatan')->insert([
                'nama_jenis_jabatan' => $jj['nama_jenis_jabatan'],
                'keterangan'         => $jj['keterangan'],
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        // 6. MAP DAN KEMBALIKAN RELASI PEGAWAI DENGAN ID MASTER BARU
        foreach ($pegawaiUkBackup as $p) {
            $oldName = $oldUkMap[$p->unit_kerja_id] ?? null;
            if ($oldName && isset($newUkNameToId[$oldName])) {
                DB::table('pegawai')->where('id', $p->id)->update(['unit_kerja_id' => $newUkNameToId[$oldName]]);
            } else {
                // Fallback ke Dekanat jika nama lama tidak ditemukan
                DB::table('pegawai')->where('id', $p->id)->update(['unit_kerja_id' => $newUkNameToId['Dekanat / Pimpinan Fakultas'] ?? 1]);
            }
        }

        foreach ($pegawaiJbBackup as $p) {
            $oldName = $oldJbMap[$p->jabatan_id] ?? null;
            if ($oldName && isset($newJbNameToId[$oldName])) {
                DB::table('pegawai')->where('id', $p->id)->update(['jabatan_id' => $newJbNameToId[$oldName]]);
            } elseif ($oldName && str_contains($oldName, 'Wakil Dekan')) {
                DB::table('pegawai')->where('id', $p->id)->update(['jabatan_id' => $newJbNameToId['Wakil Dekan I (Bid. Akademik)'] ?? 2]);
            } else {
                DB::table('pegawai')->where('id', $p->id)->update(['jabatan_id' => $newJbNameToId['Kepala Bagian Umum'] ?? 16]);
            }
        }

        foreach ($riwayatJbBackup as $r) {
            $oldName = $oldJbMap[$r->jabatan_id] ?? null;
            if ($oldName && isset($newJbNameToId[$oldName])) {
                DB::table('riwayat_jabatan')->where('id', $r->id)->update(['jabatan_id' => $newJbNameToId[$oldName]]);
            }
        }

        // Enable Kembali Foreign Key Checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};
