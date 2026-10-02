<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Models\AnalisisJabatan;
use App\Models\AbkUraianTugas;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Pastikan seluruh 9 Jabatan Baku Anjab & ABK tersinkronisasi 100% di server offline maupun online.
     */
    public function up(): void
    {
        // 1. Ambil / Pastikan Unit Kerja Terkait Ada
        $unitDekanat   = UnitKerja::where('kode_unit', 'UK-001')->orWhere('nama_unit', 'like', '%Dekan%')->first();
        $unitPreklinik = UnitKerja::where('kode_unit', 'UK-005')->orWhere('nama_unit', 'like', '%Preklinik%')->first();
        $unitKlinik    = UnitKerja::where('kode_unit', 'UK-006')->orWhere('nama_unit', 'like', '%Klinik%')->first();
        $unitProdiS1   = UnitKerja::where('kode_unit', 'UK-009')->orWhere('nama_unit', 'like', '%Prodi S1%')->first();
        $unitProdiS2   = UnitKerja::where('kode_unit', 'UK-007')->orWhere('nama_unit', 'like', '%Prodi S2%')->first();
        $unitProdiNers = UnitKerja::where('kode_unit', 'UK-008')->orWhere('nama_unit', 'like', '%Ners%')->first();
        $unitBagUmum   = UnitKerja::where('kode_unit', 'UK-010')->orWhere('nama_unit', 'like', '%Bagian Umum%')->first();
        $unitPokjaAkad = UnitKerja::where('kode_unit', 'UK-011')->orWhere('nama_unit', 'like', '%Akademik dan Kemahasiswaan%')->first();
        $unitPokjaKeu  = UnitKerja::where('kode_unit', 'UK-012')->orWhere('nama_unit', 'like', '%Keuangan dan Kepe%')->first();
        $unitPokjaUmum = UnitKerja::where('kode_unit', 'UK-013')->orWhere('nama_unit', 'like', '%Umum Sarana%')->first();
        $unitLab       = UnitKerja::where('kode_unit', 'UK-014')->orWhere('nama_unit', 'like', '%Laboratorium%')->first();

        // Pastikan Unit Prodi S3
        $unitProdiS3 = UnitKerja::firstOrCreate(
            ['nama_unit' => 'Program Studi S3 Keperawatan'],
            [
                'parent_id' => $unitPreklinik?->id,
                'kode_unit' => 'UK-016',
                'tipe_unit' => 'Program Studi',
                'urutan'    => 8,
            ]
        );

        // ==========================================
        // 1. KOORDINATOR PRODI S3 KEPERAWATAN (ANJAB-KPS-003)
        // ==========================================
        $jabKoorS3 = Jabatan::firstOrCreate(
            ['nama_jabatan' => 'Koordinator Prodi S3 Keperawatan'],
            [
                'kode_jabatan'     => 'JAB-KPS-003',
                'unit_kerja_id'    => $unitProdiS3->id,
                'kelas_jabatan'    => 10,
                'kelompok_jabatan' => 'Tugas Tambahan Dosen',
                'ikhtisar_jabatan' => 'Memimpin dan mengkoordinasikan penyelenggaraan program studi doktor keperawatan, kurikulum riset lanjutan, dan publikasi internasional bereputasi.',
            ]
        );

        $anjabS3 = AnalisisJabatan::where('kode_anjab', 'ANJAB-KPS-003')->orWhere('jabatan_id', $jabKoorS3->id)->first();
        if (!$anjabS3) {
            $anjabS3 = AnalisisJabatan::create([
                'jabatan_id'              => $jabKoorS3->id,
                'unit_kerja_id'           => $unitProdiS3->id,
                'kode_anjab'              => 'ANJAB-KPS-003',
                'ikhtisar_jabatan'        => 'Mengkoordinasikan perencanaan, pelaksanaan riset disertasi, kurikulum doktor keperawatan lanjutan, kolaborasi internasional, dan akreditasi Program Studi Doktor (S-3) Keperawatan.',
                'kualifikasi_pendidikan'  => 'Doktor (S-3) Keperawatan dengan Jabatan Fungsional minimal Lektor Kepala / Guru Besar',
                'kualifikasi_pelatihan'   => 'Manajemen Program Studi Doktor, Metodologi Riset Translasional, Bimbingan Disertasi Internasional',
                'kualifikasi_pengalaman'  => 'Dosen tetap S-3 dan memiliki publikasi internasional bereputasi (Scopus/WoS)',
                'bahan_kerja'             => "1. Dokumen Kurikulum S3 Keperawatan\n2. Naskah Disertasi Mahasiswa S3\n3. Proposal Kerjasama Riset Internasional\n4. Instrumen Akreditasi LAM-PTKes",
                'perangkat_kerja'         => "1. Sistem Informasi Riset & SIAKAD\n2. Komputer, Jurnal Database Internasional\n3. Ruang Ujian Sidang Tertutup/Terbuka Disertasi",
                'tanggung_jawab'          => "1. Kelulusan tepat waktu doktor keperawatan dengan publikasi Q1/Q2\n2. Penjaminan mutu kurikulum riset doktoral\n3. Akreditasi Unggul program doktor",
                'wewenang'                => "1. Menetapkan tim promotor dan ko-promotor disertasi\n2. Menetapkan jadwal sidang kualifikasi, proposal, dan promosi doktor\n3. Mengusulkan reviewer dan penguji eksternal internasional",
                'korelasi_jabatan'        => "1. Dekan & WD I: Laporan kurikulum dan capaian riset\n2. Mahasiswa Doktoral: Bimbingan dan ujian disertasi\n3. Sekolah Pascasarjana / LPPM UNRI: Koordinasi riset",
                'kondisi_lingkungan'      => "Ruang kerja pascasarjana representatif ber-AC",
                'resiko_bahaya'           => "Beban psikologis tinggi dalam menjaga reputasi akademik program doktor",
                'syarat_keterampilan'     => "Manajemen akademik lanjutan, riset kuantitatif/kualitatif tingkat tinggi, komunikasi saintifik internasional",
                'syarat_bakat'            => "G (Intelegensi), V (Verbal), Q (Klerikal)",
                'syarat_temperamen'       => "D (Memimpin), M (Menilai), T (Toleransi Tekanan)",
                'syarat_minat'            => "Investigatif, Ilmiah, Enterprising",
                'syarat_upaya_fisik'      => "Duduk, mengajar, menguji disertasi",
                'kondisi_fisik'           => "Sehat jasmani dan rohani",
                'prestasi_diharapkan'     => "100% lulusan menghasilkan minimal 1 publikasi terindeks Scopus dan akreditasi prodi Unggul",
                'kelas_jabatan'           => 10,
                'status'                  => 'disetujui',
            ]);
        }

        if ($anjabS3 && AbkUraianTugas::where('analisis_jabatan_id', $anjabS3->id)->count() === 0) {
            AbkUraianTugas::insert([
                [
                    'analisis_jabatan_id'    => $anjabS3->id,
                    'urutan'                 => 1,
                    'uraian_tugas'           => 'Menyusun kalender akademik program doktor, plotting promotor/ko-promotor, dan distribusi beban mengajar dosen S3.',
                    'satuan_hasil'           => 'Dokumen Plotting S3',
                    'norma_waktu_menit'      => 360,
                    'volume_1_tahun'         => 30,
                    'waktu_beban_menit'      => 10800,
                    'kebutuhan_pegawai'      => 10800 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabS3->id,
                    'urutan'                 => 2,
                    'uraian_tugas'           => 'Menyelenggarakan ujian kualifikasi doktoral, seminar proposal disertasi, dan ujian pra-promosi (sidang tertutup).',
                    'satuan_hasil'           => 'Sesi Ujian Disertasi',
                    'norma_waktu_menit'      => 180,
                    'volume_1_tahun'         => 60,
                    'waktu_beban_menit'      => 10800,
                    'kebutuhan_pegawai'      => 10800 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabS3->id,
                    'urutan'                 => 3,
                    'uraian_tugas'           => 'Memfasilitasi dan memverifikasi manuskrip publikasi mahasiswa S3 pada jurnal internasional bereputasi (Scopus/WoS).',
                    'satuan_hasil'           => 'Naskah Publikasi Q1/Q2',
                    'norma_waktu_menit'      => 300,
                    'volume_1_tahun'         => 45,
                    'waktu_beban_menit'      => 13500,
                    'kebutuhan_pegawai'      => 13500 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabS3->id,
                    'urutan'                 => 4,
                    'uraian_tugas'           => 'Menyusun Laporan Evaluasi Diri (LED) dan instrumen akreditasi program doktor LAM-PTKes.',
                    'satuan_hasil'           => 'Dokumen Akreditasi S3',
                    'norma_waktu_menit'      => 600,
                    'volume_1_tahun'         => 35,
                    'waktu_beban_menit'      => 21000,
                    'kebutuhan_pegawai'      => 21000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
            ]);
        }

        // ==========================================
        // 2. KOORDINATOR PRODI S2 KEPERAWATAN (ANJAB-KPS-002)
        // ==========================================
        $jabKoorS2 = Jabatan::where('nama_jabatan', 'like', '%Koordinator Prodi S2%')->first();
        if (!$jabKoorS2) {
            $jabKoorS2 = Jabatan::create([
                'nama_jabatan'     => 'Koordinator Prodi S2 Keperawatan',
                'kode_jabatan'     => 'JAB-KPS-002',
                'unit_kerja_id'    => $unitProdiS2?->id,
                'kelas_jabatan'    => 10,
                'kelompok_jabatan' => 'Tugas Tambahan Dosen',
                'ikhtisar_jabatan' => 'Memimpin dan mengkoordinasikan kurikulum magister keperawatan, perkuliahan berbasis riset, bimbingan tesis, dan akreditasi.',
            ]);
        }

        $anjabS2 = AnalisisJabatan::where('kode_anjab', 'ANJAB-KPS-002')->orWhere('jabatan_id', $jabKoorS2->id)->first();
        if (!$anjabS2) {
            $anjabS2 = AnalisisJabatan::create([
                'jabatan_id'              => $jabKoorS2->id,
                'unit_kerja_id'           => $unitProdiS2?->id,
                'kode_anjab'              => 'ANJAB-KPS-002',
                'ikhtisar_jabatan'        => 'Mengkoordinasikan kurikulum magister keperawatan, perkuliahan berbasis riset, bimbingan tesis, dan akreditasi Program Studi S-2 Keperawatan.',
                'kualifikasi_pendidikan'  => 'Doktor (S-3) Keperawatan dengan Jabatan Fungsional minimal Lektor Kepala',
                'kualifikasi_pelatihan'   => 'Manajemen Program Studi Magister, Pekerti/AA, Metodologi Riset Lanjutan',
                'kualifikasi_pengalaman'  => 'Dosen tetap minimal 3 tahun dan memiliki publikasi ilmiah nasional/internasional',
                'bahan_kerja'             => "1. Dokumen Kurikulum Magister S2\n2. Naskah Usulan & Hasil Tesis\n3. Dokumen Akreditasi LAM-PTKes\n4. Nilai Mahasiswa & Kalender Akademik",
                'perangkat_kerja'         => "1. SIAKAD, SIMPEG, SISTER\n2. Komputer, Jurnal Elektronik, Printer\n3. Ruang Seminar Tesis",
                'tanggung_jawab'          => "1. Ketercapaian CPL Magister dan kelulusan tepat waktu mahasiswa S2\n2. Kualitas publikasi tesis mahasiswa di jurnal terakreditasi Sinta/Scopus\n3. Akreditasi Unggul prodi S2",
                'wewenang'                => "1. Menetapkan plotting pembimbing dan penguji tesis S2\n2. Menyetujui pendaftaran seminar proposal dan ujian tutup tesis\n3. Mengusulkan pembaruan RPS berbasis riset",
                'korelasi_jabatan'        => "1. Dekan & WD I: Laporan capaian akademik magister\n2. Dosen Prodi: Pembagian beban mengajar dan bimbingan tesis\n3. Mahasiswa S2: Bimbingan dan ujian tesis",
                'kondisi_lingkungan'      => "Ruang kerja dosen pascasarjana ber-AC",
                'resiko_bahaya'           => "Kelelahan psikologis dalam supervisi riset tesis dan akreditasi",
                'syarat_keterampilan'     => "Desain kurikulum magister, analisis data statistik kesehatan, supervisi tesis",
                'syarat_bakat'            => "G, V, Q",
                'syarat_temperamen'       => "D, M, T",
                'syarat_minat'            => "Investigatif, Ilmiah, Sosial",
                'syarat_upaya_fisik'      => "Duduk, mengajar, menguji tesis",
                'kondisi_fisik'           => "Sehat jasmani dan rohani",
                'prestasi_diharapkan'     => "Akreditasi Unggul dan masa studi rata-rata 4 semester (2 tahun)",
                'kelas_jabatan'           => 10,
                'status'                  => 'disetujui',
            ]);
        }

        if ($anjabS2 && AbkUraianTugas::where('analisis_jabatan_id', $anjabS2->id)->count() === 0) {
            AbkUraianTugas::insert([
                [
                    'analisis_jabatan_id'    => $anjabS2->id,
                    'urutan'                 => 1,
                    'uraian_tugas'           => 'Menyusun kalender akademik magister, plotting pembimbing tesis, dan pembagian dosen pengampu mata kuliah S2.',
                    'satuan_hasil'           => 'Dokumen Plotting S2',
                    'norma_waktu_menit'      => 360,
                    'volume_1_tahun'         => 40,
                    'waktu_beban_menit'      => 14400,
                    'kebutuhan_pegawai'      => 14400 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabS2->id,
                    'urutan'                 => 2,
                    'uraian_tugas'           => 'Menyelenggarakan ujian seminar proposal, seminar hasil, dan sidang komprehensif/tutup tesis mahasiswa magister.',
                    'satuan_hasil'           => 'Sesi Sidang Tesis',
                    'norma_waktu_menit'      => 120,
                    'volume_1_tahun'         => 120,
                    'waktu_beban_menit'      => 14400,
                    'kebutuhan_pegawai'      => 14400 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabS2->id,
                    'urutan'                 => 3,
                    'uraian_tugas'           => 'Melakukan monitoring dan verifikasi manuskrip publikasi tesis mahasiswa S2 pada jurnal nasional Sinta / internasional.',
                    'satuan_hasil'           => 'Manuskrip Artikel S2',
                    'norma_waktu_menit'      => 240,
                    'volume_1_tahun'         => 60,
                    'waktu_beban_menit'      => 14400,
                    'kebutuhan_pegawai'      => 14400 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabS2->id,
                    'urutan'                 => 4,
                    'uraian_tugas'           => 'Menyusun Laporan Evaluasi Diri (LED) dan instrumen akreditasi program studi magister keperawatan.',
                    'satuan_hasil'           => 'Dokumen Akreditasi S2',
                    'norma_waktu_menit'      => 600,
                    'volume_1_tahun'         => 30,
                    'waktu_beban_menit'      => 18000,
                    'kebutuhan_pegawai'      => 18000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
            ]);
        }

        // ==========================================
        // 3. KOORDINATOR PRODI PROFESI NERS (ANJAB-KPS-004)
        // ==========================================
        $jabKoorNers = Jabatan::where('nama_jabatan', 'like', '%Koordinator Prodi Ners%')->first();
        if (!$jabKoorNers) {
            $jabKoorNers = Jabatan::create([
                'nama_jabatan'     => 'Koordinator Prodi Ners',
                'kode_jabatan'     => 'JAB-KPS-004',
                'unit_kerja_id'    => $unitProdiNers?->id,
                'kelas_jabatan'    => 10,
                'kelompok_jabatan' => 'Tugas Tambahan Dosen',
                'ikhtisar_jabatan' => 'Mengkoordinasikan stase kepaniteraan klinik mahasiswa ners di Rumah Sakit Pendidikan, Puskesmas, serta pelaksanaan UKNI.',
            ]);
        }

        $anjabNers = AnalisisJabatan::where('kode_anjab', 'ANJAB-KPS-004')->orWhere('jabatan_id', $jabKoorNers->id)->first();
        if (!$anjabNers) {
            $anjabNers = AnalisisJabatan::create([
                'jabatan_id'              => $jabKoorNers->id,
                'unit_kerja_id'           => $unitProdiNers?->id,
                'kode_anjab'              => 'ANJAB-KPS-004',
                'ikhtisar_jabatan'        => 'Mengkoordinasikan stase kepaniteraan klinik mahasiswa ners di Rumah Sakit Pendidikan, Puskesmas, panti wredha, dan komunitas, serta pelaksanaan Uji Kompetensi Ners Indonesia (UKNI).',
                'kualifikasi_pendidikan'  => 'Ners & Magister/Doktor Keperawatan dengan Jabatan Fungsional minimal Lektor',
                'kualifikasi_pelatihan'   => 'Preseptorship / Mentorship Klinik Keperawatan, Manajemen Kepaniteraan Klinik, UKOM Ners',
                'kualifikasi_pengalaman'  => 'Pengalaman membimbing klinik di Rumah Sakit minimal 3 tahun',
                'bahan_kerja'             => "1. Buku Panduan Stase Profesi Ners\n2. Jadwal Rotasi Mahasiswa di Wahana Praktik\n3. Logbook Asuhan Keperawatan Mahasiswa\n4. Data Ujian Kompetensi Ners (UKNI)",
                'perangkat_kerja'         => "1. MoU / MoA Rumah Sakit & Puskesmas\n2. Aplikasi Ujian CBT & SIAKAD\n3. Manikin & Alat Medis Simulasi",
                'tanggung_jawab'          => "1. Kelulusan 100% first-taker Uji Kompetensi Ners Indonesia (UKNI)\n2. Keselamatan pasien dan mahasiswa selama stase kepaniteraan di RS\n3. Pemenuhan kompetensi 9 stase keperawatan",
                'wewenang'                => "1. Menetapkan wahana praktik dan rotasi dinas mahasiswa ners\n2. Menunjuk preseptor akademik dan preseptor klinik RS\n3. Menentukan kelulusan evaluasi stase klinik",
                'korelasi_jabatan'        => "1. Dekan & WD I: Laporan stase klinik dan UKOM\n2. Komite Keperawatan RS Pendidikan: Koordinasi lahan praktik\n3. Mahasiswa Profesi Ners: Pembimbingan dan evaluasi",
                'kondisi_lingkungan'      => "Ruang prodi kampus dan lingkungan Rumah Sakit Pendidikan / Puskesmas",
                'resiko_bahaya'           => "Paparan risiko infeksi nosokomial di rumah sakit",
                'syarat_keterampilan'     => "Keterampilan asuhan keperawatan klinik lanjut, koordinasi antar-institusi kesehatan, supervisi klinis",
                'syarat_bakat'            => "G, V, Q, K",
                'syarat_temperamen'       => "D, M, S (Stabil), T",
                'syarat_minat'            => "Sosial, Medis, Investigatif",
                'syarat_upaya_fisik'      => "Berdiri, berjalan di bangsal, supervisi mahasiswa",
                'kondisi_fisik'           => "Sehat jasmani dan rohani",
                'prestasi_diharapkan'     => "Tingkat kelulusan UKNI > 95% dan masa tunggu kerja perawat < 2 bulan",
                'kelas_jabatan'           => 10,
                'status'                  => 'disetujui',
            ]);
        }

        if ($anjabNers && AbkUraianTugas::where('analisis_jabatan_id', $anjabNers->id)->count() === 0) {
            AbkUraianTugas::insert([
                [
                    'analisis_jabatan_id'    => $anjabNers->id,
                    'urutan'                 => 1,
                    'uraian_tugas'           => 'Menyusun jadwal rotasi stase kepaniteraan klinik, penempatan kelompok mahasiswa di RS/Puskesmas, dan surat pengantar dinas.',
                    'satuan_hasil'           => 'Dokumen Rotasi Klinik',
                    'norma_waktu_menit'      => 360,
                    'volume_1_tahun'         => 45,
                    'waktu_beban_menit'      => 16200,
                    'kebutuhan_pegawai'      => 16200 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabNers->id,
                    'urutan'                 => 2,
                    'uraian_tugas'           => 'Melakukan koordinasi dan monitoring rutin (ronde klinis) ke Rumah Sakit Pendidikan dan Puskesmas bersama preseptor klinik.',
                    'satuan_hasil'           => 'Sesi Ronde Monitoring',
                    'norma_waktu_menit'      => 180,
                    'volume_1_tahun'         => 80,
                    'waktu_beban_menit'      => 14400,
                    'kebutuhan_pegawai'      => 14400 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabNers->id,
                    'urutan'                 => 3,
                    'uraian_tugas'           => 'Menyelenggarakan bimbingan intensif dan try-out persiapan Uji Kompetensi Ners Indonesia (UKNI) berbasis CBT.',
                    'satuan_hasil'           => 'Sesi Try Out & Bimbingan',
                    'norma_waktu_menit'      => 240,
                    'volume_1_tahun'         => 60,
                    'waktu_beban_menit'      => 14400,
                    'kebutuhan_pegawai'      => 14400 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabNers->id,
                    'urutan'                 => 4,
                    'uraian_tugas'           => 'Menyusun laporan evaluasi mutu kepaniteraan klinik dan dokumen akreditasi profesi ners LAM-PTKes.',
                    'satuan_hasil'           => 'Dokumen Akreditasi Ners',
                    'norma_waktu_menit'      => 600,
                    'volume_1_tahun'         => 30,
                    'waktu_beban_menit'      => 18000,
                    'kebutuhan_pegawai'      => 18000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
            ]);
        }

        // ==========================================
        // 4. STAFF POKJA AKADEMIK DAN KEMAHASISWAAN (ANJAB-AKAD-001)
        // ==========================================
        $jabStafAkad = Jabatan::where('nama_jabatan', 'Staff Pokja Akademik dan Kemahasiswaan')->first();
        if (!$jabStafAkad) {
            $jabStafAkad = Jabatan::create([
                'nama_jabatan'     => 'Staff Pokja Akademik dan Kemahasiswaan',
                'kode_jabatan'     => 'JAB-STAF-AKAD',
                'unit_kerja_id'    => $unitPokjaAkad?->id,
                'kelas_jabatan'    => 6,
                'kelompok_jabatan' => 'Pelaksana',
                'ikhtisar_jabatan' => 'Melaksanakan pelayanan administrasi registrasi mahasiswa, penginputan kartu rencana studi (KRS), penerbitan transkrip nilai, administrasi yudisium, dan beasiswa.',
            ]);
        }

        $anjabStafAkad = AnalisisJabatan::where('kode_anjab', 'ANJAB-AKAD-001')->orWhere('jabatan_id', $jabStafAkad->id)->first();
        if (!$anjabStafAkad) {
            $anjabStafAkad = AnalisisJabatan::create([
                'jabatan_id'              => $jabStafAkad->id,
                'unit_kerja_id'           => $unitPokjaAkad?->id,
                'kode_anjab'              => 'ANJAB-AKAD-001',
                'ikhtisar_jabatan'        => 'Melaksanakan pelayanan administrasi registrasi mahasiswa, penginputan kartu rencana studi (KRS), penerbitan transkrip nilai, administrasi yudisium, dan beasiswa kemahasiswaan.',
                'kualifikasi_pendidikan'  => 'D-III / D-IV / S-1 Administrasi Pendidikan / Komputer / Manajemen',
                'kualifikasi_pelatihan'   => 'Pengelolaan SIAKAD, Pelayanan Prima (Service Excellence), Kearsipan Digital',
                'kualifikasi_pengalaman'  => 'Minimal 1 tahun di bidang administrasi akademik',
                'bahan_kerja'             => "1. Berkas KRS & KHS Mahasiswa\n2. Formulir Pengajuan Cuti Kuliah\n3. Berkas Pendaftaran Yudisium & Ijazah\n4. Surat Permohonan Beasiswa",
                'perangkat_kerja'         => "1. Komputer & Printer Cetak KHS/Ijazah\n2. Aplikasi Portal Akademik (SIAKAD)\n3. ATK & Map Berkas Mahasiswa",
                'tanggung_jawab'          => "1. Keakuratan pencatatan nilai dan transkrip mahasiswa\n2. Ketertiban arsip berkas yudisium dan ijazah\n3. Kecepatan pelayanan surat keterangan aktif kuliah",
                'wewenang'                => "1. Meneliti keabsahan persyaratan yudisium mahasiswa\n2. Memverifikasi pengajuan cuti kuliah mahasiswa\n3. Mencetak draf transkrip akademik",
                'korelasi_jabatan'        => "1. Ka Pokja Akademik: Laporan tugas dan verifikasi\n2. Mahasiswa: Pelayanan administrasi akademik\n3. BAAK Rektorat: Koordinasi wisuda dan PIN (Penomoran Ijazah Nasional)",
                'kondisi_lingkungan'      => "Ruang loket pelayanan administrasi kantor ber-AC",
                'resiko_bahaya'           => "Kelelahan mata akibat monitor komputer",
                'syarat_keterampilan'     => "Aplikasi perkantoran, SIAKAD, komunikasi pelayanan",
                'syarat_bakat'            => "Q (Klerikal), V (Verbal), N (Numerik)",
                'syarat_temperamen'       => "R, T",
                'syarat_minat'            => "Konvensional, Sosial",
                'syarat_upaya_fisik'      => "Duduk, melihat, melayani mahasiswa di loket",
                'kondisi_fisik'           => "Sehat jasmani dan rohani",
                'prestasi_diharapkan'     => "Pelayanan berkas akademik mahasiswa selesai maksimal 1 hari kerja (zero delay)",
                'kelas_jabatan'           => 6,
                'status'                  => 'disetujui',
            ]);
        }

        if ($anjabStafAkad && AbkUraianTugas::where('analisis_jabatan_id', $anjabStafAkad->id)->count() === 0) {
            AbkUraianTugas::insert([
                [
                    'analisis_jabatan_id'    => $anjabStafAkad->id,
                    'urutan'                 => 1,
                    'uraian_tugas'           => 'Melayani verifikasi KRS online, perubahan kartu rencana studi, dan pengecekan kuota kelas mahasiswa per semester.',
                    'satuan_hasil'           => 'Berkas KRS Mahasiswa',
                    'norma_waktu_menit'      => 20,
                    'volume_1_tahun'         => 1200,
                    'waktu_beban_menit'      => 24000,
                    'kebutuhan_pegawai'      => 24000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabStafAkad->id,
                    'urutan'                 => 2,
                    'uraian_tugas'           => 'Memeriksa berkas persyaratan, menghitung IPK, dan menyiapkan kelengkapan berkas yudisium serta cetak transkrip nilai kelulusan.',
                    'satuan_hasil'           => 'Berkas Yudisium',
                    'norma_waktu_menit'      => 60,
                    'volume_1_tahun'         => 400,
                    'waktu_beban_menit'      => 24000,
                    'kebutuhan_pegawai'      => 24000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabStafAkad->id,
                    'urutan'                 => 3,
                    'uraian_tugas'           => 'Menerbitkan surat keterangan aktif kuliah, surat izin penelitian skripsi, dan rekomendasi beasiswa mahasiswa.',
                    'satuan_hasil'           => 'Surat Keterangan',
                    'norma_waktu_menit'      => 25,
                    'volume_1_tahun'         => 600,
                    'waktu_beban_menit'      => 15000,
                    'kebutuhan_pegawai'      => 15000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabStafAkad->id,
                    'urutan'                 => 4,
                    'uraian_tugas'           => 'Menata dan mendigitalisasi arsip berkas nilai, berita acara ujian skripsi/sidang, dan transkrip alumni.',
                    'satuan_hasil'           => 'Arsip Digital',
                    'norma_waktu_menit'      => 30,
                    'volume_1_tahun'         => 450,
                    'waktu_beban_menit'      => 13500,
                    'kebutuhan_pegawai'      => 13500 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe rollback
    }
};
