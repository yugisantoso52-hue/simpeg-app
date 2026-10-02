<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\UnitKerja;
use App\Models\Jabatan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. CARI UNIT KERJA ACUAN
        $unitDekanat   = UnitKerja::where('kode_unit', 'UK-001')->orWhere('nama_unit', 'like', '%Dekan%')->first();
        $unitSenat     = UnitKerja::where('kode_unit', 'UK-002')->orWhere('nama_unit', 'like', '%Senat%')->first();
        $unitSpmf      = UnitKerja::where('kode_unit', 'UK-003')->orWhere('nama_unit', 'like', '%SPMF%')->first();
        $unitJurusan   = UnitKerja::where('kode_unit', 'UK-004')->orWhere('nama_unit', 'like', '%Jurusan Keperawatan%')->first();
        $unitPreklinik = UnitKerja::where('kode_unit', 'UK-005')->orWhere('nama_unit', 'like', '%Preklinik%')->first();
        $unitKlinik    = UnitKerja::where('kode_unit', 'UK-006')->orWhere('nama_unit', 'like', '%Klinik%')->first();
        $unitProdiS1   = UnitKerja::where('kode_unit', 'UK-009')->orWhere('nama_unit', 'like', '%Prodi S1%')->first();
        $unitProdiS2   = UnitKerja::where('kode_unit', 'UK-007')->orWhere('nama_unit', 'like', '%Prodi S2%')->first();
        $unitProdiS3   = UnitKerja::where('kode_unit', 'UK-016')->orWhere('nama_unit', 'like', '%Prodi S3%')->first();
        $unitProdiNers = UnitKerja::where('kode_unit', 'UK-008')->orWhere('nama_unit', 'like', '%Ners%')->first();
        $unitBagUmum   = UnitKerja::where('kode_unit', 'UK-010')->orWhere('nama_unit', 'like', '%Bagian Umum%')->first();
        $unitPokjaAkad = UnitKerja::where('kode_unit', 'UK-011')->orWhere('nama_unit', 'like', '%Akademik dan Kemahasiswaan%')->first();
        $unitPokjaKeu  = UnitKerja::where('kode_unit', 'UK-012')->orWhere('nama_unit', 'like', '%Keuangan dan Kepe%')->first();
        $unitPokjaUmum = UnitKerja::where('kode_unit', 'UK-013')->orWhere('nama_unit', 'like', '%Umum Sarana%')->first();
        $unitLab       = UnitKerja::where('kode_unit', 'UK-014')->orWhere('nama_unit', 'like', '%Laboratorium%')->first();
        $unitFungsional= UnitKerja::where('kode_unit', 'UK-015')->orWhere('nama_unit', 'like', '%Unit-Unit Fungsional%')->first();
        $unitMhsAlumni = UnitKerja::where('kode_unit', 'UK-017')->orWhere('nama_unit', 'like', '%Alumni%')->first();

        // 2. SINKRONISASI UNIT KERJA, GRADE, & KELOMPOK JABATAN EXISTING
        $updates = [
            // Pimpinan Fakultas
            1  => ['unit' => $unitDekanat?->id,   'grade' => 15, 'kel' => 'Pimpinan Fakultas', 'desc' => 'Pimpinan tertinggi penyelenggaraan Tridharma Perguruan Tinggi di Fakultas Keperawatan UNRI.'],
            2  => ['unit' => $unitDekanat?->id,   'grade' => 13, 'kel' => 'Pimpinan Fakultas', 'desc' => 'Membantu Dekan memimpin pelaksanaan bidang pendidikan, penelitian, pengabdian masyarakat, dan kurikulum OBE.'],
            3  => ['unit' => $unitDekanat?->id,   'grade' => 13, 'kel' => 'Pimpinan Fakultas', 'desc' => 'Membantu Dekan memimpin perencanaan keuangan, kepegawaian, ketatausahaan, dan sarana prasarana.'],
            4  => ['unit' => $unitDekanat?->id,   'grade' => 13, 'kel' => 'Pimpinan Fakultas', 'desc' => 'Membantu Dekan dalam pembinaan kemahasiswaan, penalaran, minat bakat, tracer study alumni, dan kerjasama.'],

            // Senat Fakultas
            5  => ['unit' => $unitSenat?->id,     'grade' => 12, 'kel' => 'Badan Pertimbangan (Senat)', 'desc' => 'Memimpin perumusan kebijakan akademik dan pengawasan mutu tridharma perguruan tinggi di tingkat fakultas.'],
            6  => ['unit' => $unitSenat?->id,     'grade' => 11, 'kel' => 'Badan Pertimbangan (Senat)', 'desc' => 'Mengkoordinasikan administrasi rapat, notulensi persidangan, dan dokumen rekomendasi Senat Fakultas.'],

            // SPMF
            7  => ['unit' => $unitSpmf?->id,      'grade' => 11, 'kel' => 'Penjaminan Mutu (SPMF)', 'desc' => 'Memimpin penjaminan mutu internal (SPMI), monev pembelajaran, dan persiapan akreditasi LAM-PTKes.'],

            // Pimpinan Jurusan
            8  => ['unit' => $unitJurusan?->id,   'grade' => 11, 'kel' => 'Pimpinan Jurusan', 'desc' => 'Memimpin pengelolaan sumber daya dosen, laboratorium, dan kurikulum keilmuan keperawatan.'],
            9  => ['unit' => $unitJurusan?->id,   'grade' => 10, 'kel' => 'Pimpinan Jurusan', 'desc' => 'Membantu Ketua Jurusan dalam administrasi perkuliahan dan operasional jurusan keperawatan.'],
            10 => ['unit' => $unitPreklinik?->id, 'grade' => 11, 'kel' => 'Pimpinan Jurusan', 'desc' => 'Memimpin penyelenggaraan tridharma pada rumpun keperawatan preklinik (S1, S2, S3).'],
            11 => ['unit' => $unitPreklinik?->id, 'grade' => 10, 'kel' => 'Pimpinan Jurusan', 'desc' => 'Mengkoordinasikan administrasi akademik pada Jurusan Preklinik Keperawatan.'],
            12 => ['unit' => $unitKlinik?->id,    'grade' => 11, 'kel' => 'Pimpinan Jurusan', 'desc' => 'Memimpin penyelenggaraan tridharma dan kepaniteraan klinik pada Jurusan Klinik dan Komunitas.'],
            13 => ['unit' => $unitKlinik?->id,    'grade' => 10, 'kel' => 'Pimpinan Jurusan', 'desc' => 'Mengkoordinasikan administrasi operasional pada Jurusan Klinik dan Komunitas.'],

            // Koordinator Program Studi
            14 => ['unit' => $unitProdiS2?->id,   'grade' => 10, 'kel' => 'Koordinator Program Studi', 'desc' => 'Mengkoordinasikan kurikulum magister S2, penelitian tesis, dan akreditasi Program Studi S2 Keperawatan.'],
            15 => ['unit' => $unitProdiS1?->id,   'grade' => 10, 'kel' => 'Koordinator Program Studi', 'desc' => 'Mengkoordinasikan kurikulum S1, pembelajaran OBE, plotting dosen, dan akreditasi Program Studi S1 Keperawatan.'],
            16 => ['unit' => $unitProdiNers?->id, 'grade' => 10, 'kel' => 'Koordinator Program Studi', 'desc' => 'Mengkoordinasikan stase kepaniteraan klinik mahasiswa ners di RS/Puskesmas dan persiapan Uji Kompetensi Ners (UKNI).'],
            41 => ['unit' => $unitProdiS3?->id,   'grade' => 10, 'kel' => 'Koordinator Program Studi', 'desc' => 'Mengkoordinasikan kurikulum doktor S3, riset lanjutan translasi keperawatan, dan publikasi internasional.'],

            // Kepala Unit & Pokja
            17 => ['unit' => $unitJurusan?->id,   'grade' => 10, 'kel' => 'Kelompok Jabatan Fungsional Dosen (KJFD)', 'desc' => 'Mengkoordinasikan pengembangan rumpun keahlian dosen dan penelitian bidang keperawatan.'],
            18 => ['unit' => $unitBagUmum?->id,   'grade' => 11, 'kel' => 'Tenaga Kependidikan & Tata Usaha', 'desc' => 'Memimpin urusan tata usaha, persuratan, keuangan, kepegawaian, perlengkapan, dan BMN fakultas.'],
            19 => ['unit' => $unitLab?->id,       'grade' => 10, 'kel' => 'Laboratorium Keperawatan', 'desc' => 'Memimpin pengelolaan 9 ruang laboratorium keperawatan, manikin medis, bahan praktikum dan K3.'],
            20 => ['unit' => $unitFungsional?->id,'grade' => 10, 'kel' => 'Unit-Unit Fungsional', 'desc' => 'Memimpin pelaksanaan tugas spesifik unit penunjang akademik dan pelayanan publik.'],
            21 => ['unit' => $unitPokjaAkad?->id, 'grade' => 9,  'kel' => 'Tenaga Kependidikan & Tata Usaha', 'desc' => 'Memimpin layanan registrasi mahasiswa, KRS, KHS, yudisium, dan administrasi akademik.'],
            22 => ['unit' => $unitPokjaKeu?->id,  'grade' => 9,  'kel' => 'Tenaga Kependidikan & Tata Usaha', 'desc' => 'Memimpin pengelolaan anggaran, SPJ, penggajian, presensi mobile, dan kenaikan pangkat pegawai.'],
            23 => ['unit' => $unitPokjaUmum?->id, 'grade' => 9,  'kel' => 'Tenaga Kependidikan & Tata Usaha', 'desc' => 'Memimpin inventarisasi BMN, perawatan sarana prasarana, kebersihan gedung, dan persuratan.'],

            // Dosen Fungsional
            24 => ['unit' => $unitJurusan?->id,   'grade' => 14, 'kel' => 'Jabatan Fungsional Dosen', 'desc' => 'Jabatan fungsional akademik tertinggi: memimpin riset unggulan dan pengembangan keilmuan keperawatan.'],
            25 => ['unit' => $unitJurusan?->id,   'grade' => 12, 'kel' => 'Jabatan Fungsional Dosen', 'desc' => 'Melaksanakan tridharma perguruan tinggi, pembimbingan tugas akhir, dan publikasi ilmiah terakreditasi.'],
            26 => ['unit' => $unitJurusan?->id,   'grade' => 10, 'kel' => 'Jabatan Fungsional Dosen', 'desc' => 'Melaksanakan pengajaran teori dan klinik, penelitian jurnal bereputasi, dan pengabdian masyarakat.'],
            27 => ['unit' => $unitJurusan?->id,   'grade' => 9,  'kel' => 'Jabatan Fungsional Dosen', 'desc' => 'Melaksanakan pengajaran dasar keperawatan, bimbingan praktikum, dan publikasi ilmiah.'],
            28 => ['unit' => $unitProdiS2?->id,   'grade' => 10, 'kel' => 'Jabatan Fungsional Dosen', 'desc' => 'Dosen pengampu perkuliahan dan pembimbing riset tesis mahasiswa Program Studi S2 Keperawatan.'],
            29 => ['unit' => $unitProdiS1?->id,   'grade' => 10, 'kel' => 'Jabatan Fungsional Dosen', 'desc' => 'Dosen pengampu perkuliahan dan pembimbing skripsi mahasiswa Program Studi S1 Keperawatan.'],
            30 => ['unit' => $unitProdiNers?->id, 'grade' => 10, 'kel' => 'Jabatan Fungsional Dosen', 'desc' => 'Dosen preseptor klinik pembimbing stase kepaniteraan mahasiswa Program Studi Profesi Ners.'],
            42 => ['unit' => $unitProdiS3?->id,   'grade' => 12, 'kel' => 'Jabatan Fungsional Dosen', 'desc' => 'Dosen promotor/ko-promotor disertasi mahasiswa Program Studi Doktor S3 Keperawatan.'],

            // Tenaga Kependidikan & Pelaksana
            31 => ['unit' => $unitLab?->id,       'grade' => 8,  'kel' => 'Laboratorium Keperawatan', 'desc' => 'Fungsional Pranata Laboratorium Pendidikan: menyiapkan manikin, alat medis, dan praktikum mahasiswa.'],
            32 => ['unit' => $unitBagUmum?->id,   'grade' => 8,  'kel' => 'Fungsional Tertentu & Pelaksana', 'desc' => 'Fungsional Pustakawan: mengelola repositori karya ilmiah keperawatan dan perpustakaan fakultas.'],
            33 => ['unit' => $unitPokjaAkad?->id, 'grade' => 6,  'kel' => 'Pelaksana & Administrasi', 'desc' => 'Melaksanakan verifikasi KRS, rekap nilai ujian, berkas yudisium, dan surat keterangan aktif kuliah.'],
            34 => ['unit' => $unitPokjaKeu?->id,  'grade' => 6,  'kel' => 'Pelaksana & Administrasi', 'desc' => 'Melaksanakan verifikasi SPJ anggaran, rekap logbook bulanan, usulan gaji berkala, dan cuti pegawai.'],
            35 => ['unit' => $unitPokjaUmum?->id, 'grade' => 6,  'kel' => 'Pelaksana & Administrasi', 'desc' => 'Melaksanakan pencatatan BMN SIMAK, pemeliharaan AC/gedung, perlengkapan kuliah, dan ekspedisi surat.'],
            36 => ['unit' => $unitBagUmum?->id,   'grade' => 7,  'kel' => 'Pelaksana & Administrasi', 'desc' => 'Mengelola sistem informasi kepegawaian, database fakultas, dan jaringan komunikasi data kampus.'],
            37 => ['unit' => $unitBagUmum?->id,   'grade' => 5,  'kel' => 'Pelaksana & Administrasi', 'desc' => 'Melaksanakan administrasi surat masuk/keluar, pengarsipan berkas dinas, dan ketatausahaan umum.'],
            38 => ['unit' => $unitBagUmum?->id,   'grade' => 7,  'kel' => 'Pelaksana & Administrasi', 'desc' => 'Melaksanakan penataan operasional layanan kepegawaian, administrasi publik, dan fasilitasi pimpinan.'],
            39 => ['unit' => $unitBagUmum?->id,   'grade' => 5,  'kel' => 'Pelaksana & Administrasi', 'desc' => 'Melaksanakan operasional layanan teknis harian, tata persuratan dinas, dan bantuan umum kantor.'],
            40 => ['unit' => $unitBagUmum?->id,   'grade' => 3,  'kel' => 'Pelaksana & Administrasi', 'desc' => 'Petugas pelaksana harian lepas: kebersihan lingkungan, keamanan gedung kuliah, dan pengemudi dinas.'],
        ];

        foreach ($updates as $jabId => $val) {
            Jabatan::where('id', $jabId)->update([
                'unit_kerja_id'    => $val['unit'],
                'kelas_jabatan'    => $val['grade'],
                'kelompok_jabatan' => $val['kel'],
                'ikhtisar_jabatan' => $val['desc'],
            ]);
        }

        // 3. DAFTARKAN JABATAN SPESIFIK STRUKTUR TERBARU (GPM, KJFD 9 BIDANG, UNIT FUNGSIONAL)
        // A. Gugus Penjamin Mutu (GPM)
        $gpmList = [
            ['nama' => 'Ketua GPM S1 Keperawatan',  'unit' => $unitProdiS1?->id ?? $unitSpmf?->id,   'grade' => 10, 'kel' => 'Penjaminan Mutu (SPMF)', 'desc' => 'Mengkoordinasikan evaluasi kurikulum OBE dan audit mutu internal pada Program Studi S1 Keperawatan.'],
            ['nama' => 'Ketua GPM S2 Keperawatan',  'unit' => $unitProdiS2?->id ?? $unitSpmf?->id,   'grade' => 10, 'kel' => 'Penjaminan Mutu (SPMF)', 'desc' => 'Mengkoordinasikan penjaminan mutu riset tesis dan kurikulum pascasarjana pada Program Studi S2 Keperawatan.'],
            ['nama' => 'Ketua GPM S3 Keperawatan',  'unit' => $unitProdiS3?->id ?? $unitSpmf?->id,   'grade' => 10, 'kel' => 'Penjaminan Mutu (SPMF)', 'desc' => 'Mengkoordinasikan standar mutu publikasi internasional dan disertasi pada Program Studi Doktor S3 Keperawatan.'],
            ['nama' => 'Ketua GPM Profesi Ners',    'unit' => $unitProdiNers?->id ?? $unitSpmf?->id, 'grade' => 10, 'kel' => 'Penjaminan Mutu (SPMF)', 'desc' => 'Mengkoordinasikan penjaminan mutu kepaniteraan klinik RS/Puskesmas dan kelulusan UKNI pada Program Studi Profesi Ners.'],
        ];
        foreach ($gpmList as $g) {
            Jabatan::firstOrCreate(
                ['nama_jabatan' => $g['nama']],
                [
                    'kode_jabatan'     => 'JAB-GPM-' . strtoupper(substr(md5($g['nama']), 0, 4)),
                    'unit_kerja_id'    => $g['unit'],
                    'kelas_jabatan'    => $g['grade'],
                    'kelompok_jabatan' => $g['kel'],
                    'ikhtisar_jabatan' => $g['desc'],
                ]
            );
        }

        // B. 9 Kelompok Jabatan Fungsional Dosen (KJFD)
        $kjfdList = [
            'Ketua KJFD Medikal Bedah',
            'Ketua KJFD Gawat Darurat',
            'Ketua KJFD Maternitas',
            'Ketua KJFD Anak',
            'Ketua KJFD Keluarga Komunitas',
            'Ketua KJFD Gerontik',
            'Ketua KJFD Jiwa',
            'Ketua KJFD Klinik',
            'Ketua KJFD Komunitas',
        ];
        foreach ($kjfdList as $idx => $kName) {
            Jabatan::firstOrCreate(
                ['nama_jabatan' => $kName],
                [
                    'kode_jabatan'     => 'JAB-KJFD-' . sprintf('%02d', $idx + 1),
                    'unit_kerja_id'    => $unitJurusan?->id,
                    'kelas_jabatan'    => 10,
                    'kelompok_jabatan' => 'Kelompok Jabatan Fungsional Dosen (KJFD)',
                    'ikhtisar_jabatan' => 'Memimpin pengembangan kurikulum keilmuan, penugasan dosen pengampu, dan riset pada rumpun ' . str_replace('Ketua KJFD ', '', $kName) . '.',
                ]
            );
        }

        // C. 9 Unit-Unit Fungsional
        $fungsionalList = [
            ['nama' => 'Pengelola Unit Etik Riset',                    'grade' => 10, 'desc' => 'Melaksanakan telaah kelayakan etik riset kesehatan pada penelitian sivitas akademika.'],
            ['nama' => 'Ketua Komite Etik',                            'grade' => 10, 'desc' => 'Memimpin sidang kelaikan etik riset kesehatan dan penerbitan surat lolos kaji etik (ethical clearance).'],
            ['nama' => 'Pengelola CBT (Computer Based Test)',          'grade' => 10, 'desc' => 'Mengelola laboratorium komputer untuk pelaksanaan ujian UKNI, tryout, dan ujian CBT berbasis online.'],
            ['nama' => 'Pengelola Kerjasama & Kemitraan',              'grade' => 10, 'desc' => 'Mengkoordinasikan inisiasi, pelaksanaan, dan monev naskah kerjasama MoU/MoA dengan RS dan mitra.'],
            ['nama' => 'Pengelola Penelitian & Pengabmasy',            'grade' => 10, 'desc' => 'Mengkoordinasikan roadmap penelitian dosen, review proposal hibah internal, dan kegiatan PkM.'],
            ['nama' => 'Koordinator NEDU (Nursing Education Dev. Unit)','grade' => 10, 'desc' => 'Mengkoordinasikan pembaruan kurikulum pendidikan keperawatan tingkat S1, Ners, dan S2.'],
            ['nama' => 'Pengelola Bimbingan Konseling',                'grade' => 9,  'desc' => 'Memberikan layanan pendampingan psikologis, konseling akademik, dan kesehatan mental mahasiswa.'],
            ['nama' => 'Pengelola Humas & Protokoler',                 'grade' => 9,  'desc' => 'Mengelola publikasi media massa, protokoler kegiatan fakultas, dan siaran berita resmi kampus.'],
            ['nama' => 'Pengelola PPID Fakultas',                      'grade' => 9,  'desc' => 'Melaksanakan pelayanan informasi publik dan dokumentasi resmi sesuai regulasi keterbukaan informasi.'],
        ];
        foreach ($fungsionalList as $idx => $f) {
            Jabatan::firstOrCreate(
                ['nama_jabatan' => $f['nama']],
                [
                    'kode_jabatan'     => 'JAB-FUNG-' . sprintf('%02d', $idx + 1),
                    'unit_kerja_id'    => $unitFungsional?->id,
                    'kelas_jabatan'    => $f['grade'],
                    'kelompok_jabatan' => 'Unit-Unit Fungsional',
                    'ikhtisar_jabatan' => $f['desc'],
                ]
            );
        }

        // D. Organisasi Mahasiswa & Alumni
        $mhsList = [
            ['nama' => 'Pengurus Badan Eksekutif Mahasiswa (BEM)',     'grade' => null, 'desc' => 'Pengurus organisasi eksekutif kemahasiswaan Fakultas Keperawatan UNRI.'],
            ['nama' => 'Pengurus Dewan Perwakilan Mahasiswa (DPM)',    'grade' => null, 'desc' => 'Pengurus lembaga legislatif dan perwakilan aspirasi mahasiswa Fakultas Keperawatan UNRI.'],
            ['nama' => 'Pengurus Ikatan Alumni (IKA FKp UNRI)',        'grade' => null, 'desc' => 'Pengurus jejaring alumni perawat dan kemitraan profesi keperawatan.'],
        ];
        foreach ($mhsList as $m) {
            Jabatan::firstOrCreate(
                ['nama_jabatan' => $m['nama']],
                [
                    'kode_jabatan'     => 'JAB-MHS-' . strtoupper(substr(md5($m['nama']), 0, 4)),
                    'unit_kerja_id'    => $unitMhsAlumni?->id ?? $unitDekanat?->id,
                    'kelas_jabatan'    => $m['grade'],
                    'kelompok_jabatan' => 'Organisasi Mahasiswa & Alumni',
                    'ikhtisar_jabatan' => $m['desc'],
                ]
            );
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
