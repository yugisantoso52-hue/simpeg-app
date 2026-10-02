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
     */
    public function up(): void
    {
        // 1. UPDATE / ENSURE UNIT KERJA TERBARU SESUAI STRUKTUR RESMI
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

        // Tambah / Pastikan Unit Kerja S3 Keperawatan ada di bawah Jurusan Preklinik
        $unitProdiS3 = UnitKerja::firstOrCreate(
            ['nama_unit' => 'Program Studi S3 Keperawatan'],
            [
                'parent_id' => $unitPreklinik?->id,
                'kode_unit' => 'UK-016',
                'tipe_unit' => 'Program Studi',
                'urutan'    => 8,
            ]
        );

        // Tambah / Pastikan Unit Kerja BEM, DPM, Alumni ada di bawah Dekanat
        $unitMhsAlumni = UnitKerja::firstOrCreate(
            ['nama_unit' => 'Organisasi Kemahasiswaan (BEM, DPM) & Alumni'],
            [
                'parent_id' => $unitDekanat?->id,
                'kode_unit' => 'UK-017',
                'tipe_unit' => 'Kemahasiswaan',
                'urutan'    => 17,
            ]
        );

        // 2. UPDATE / ENSURE JABATAN TERBARU
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

        Jabatan::firstOrCreate(
            ['nama_jabatan' => 'Dosen S3 Keperawatan'],
            [
                'kode_jabatan'     => 'JAB-DSN-004',
                'unit_kerja_id'    => $unitProdiS3->id,
                'kelas_jabatan'    => 12,
                'kelompok_jabatan' => 'Fungsional Dosen',
                'ikhtisar_jabatan' => 'Melaksanakan pengajaran tingkat doktoral, pembimbingan disertasi, riset translasional keperawatan, dan pengabdian masyarakat.',
            ]
        );

        // Sinkronisasi kelas jabatan pimpinan & pokja
        Jabatan::where('nama_jabatan', 'like', '%Wakil Dekan I%')->update(['kelas_jabatan' => 13]);
        Jabatan::where('nama_jabatan', 'like', '%Wakil Dekan II%')->update(['kelas_jabatan' => 13]);
        Jabatan::where('nama_jabatan', 'like', '%Wakil Dekan III%')->update(['kelas_jabatan' => 13]);
        Jabatan::where('nama_jabatan', 'like', '%Koordinator Prodi S2%')->update(['kelas_jabatan' => 10]);
        Jabatan::where('nama_jabatan', 'like', '%Koordinator Prodi Ners%')->update(['kelas_jabatan' => 10]);
        Jabatan::where('nama_jabatan', 'like', '%Kepala Bagian Umum%')->update(['kelas_jabatan' => 11]);
        Jabatan::where('nama_jabatan', 'like', '%Ka Pokja Keu%')->update(['kelas_jabatan' => 9]);
        Jabatan::where('nama_jabatan', 'like', '%Ka Pokja Umum%')->update(['kelas_jabatan' => 9]);

        // 3. ANJAB & ABK UNTUK KOORDINATOR PRODI S3 KEPERAWATAN
        if (!AnalisisJabatan::where('jabatan_id', $jabKoorS3->id)->exists()) {
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

        // 4. ANJAB & ABK UNTUK KOORDINATOR PRODI S2 KEPERAWATAN
        $jabKoorS2 = Jabatan::where('nama_jabatan', 'like', '%Koordinator Prodi S2%')->first();
        if ($jabKoorS2 && !AnalisisJabatan::where('jabatan_id', $jabKoorS2->id)->exists()) {
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

        // 5. ANJAB & ABK UNTUK KOORDINATOR PRODI PROFESI NERS
        $jabKoorNers = Jabatan::where('nama_jabatan', 'like', '%Koordinator Prodi Ners%')->first();
        if ($jabKoorNers && !AnalisisJabatan::where('jabatan_id', $jabKoorNers->id)->exists()) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe rollback tanpa merusak data
    }
};
