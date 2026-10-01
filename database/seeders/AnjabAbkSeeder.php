<?php

namespace Database\Seeders;

use App\Models\AnalisisJabatan;
use App\Models\AbkUraianTugas;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnjabAbkSeeder extends Seeder
{
    public function run(): void
    {
        $unitDekanat   = UnitKerja::where('kode_unit', 'UK-001')->first();
        $unitPreklinik = UnitKerja::where('kode_unit', 'UK-005')->first();
        $unitKlinik    = UnitKerja::where('kode_unit', 'UK-006')->first();
        $unitProdiS1   = UnitKerja::where('kode_unit', 'UK-009')->first();
        $unitProdiS2   = UnitKerja::where('kode_unit', 'UK-007')->first();
        $unitProdiNers = UnitKerja::where('kode_unit', 'UK-008')->first();
        $unitBagUmum   = UnitKerja::where('kode_unit', 'UK-010')->first();
        $unitPokjaAkad = UnitKerja::where('kode_unit', 'UK-011')->first();
        $unitPokjaKeu  = UnitKerja::where('kode_unit', 'UK-012')->first();
        $unitPokjaUmum = UnitKerja::where('kode_unit', 'UK-013')->first();
        $unitLab       = UnitKerja::where('kode_unit', 'UK-014')->first();

        // 1. DEKAN
        $jabDekan = Jabatan::where('nama_jabatan', 'Dekan')->first();
        if ($jabDekan && !AnalisisJabatan::where('jabatan_id', $jabDekan->id)->exists()) {
            $anjab = AnalisisJabatan::create([
                'jabatan_id'              => $jabDekan->id,
                'unit_kerja_id'           => $unitDekanat?->id,
                'kode_anjab'              => 'ANJAB-DKN-001',
                'ikhtisar_jabatan'        => 'Memimpin penyelenggaraan tridharma perguruan tinggi, pembinaan sivitas akademika, pengelolaan keuangan, SDM, sarana prasarana, serta pengembangan mutu dan kerjasama Fakultas Keperawatan.',
                'kualifikasi_pendidikan'  => 'Doktor (S-3) Bidang Ilmu Keperawatan / Kesehatan dengan Jabatan Fungsional minimal Lektor Kepala',
                'kualifikasi_pelatihan'   => 'Pelatihan Kepemimpinan Nasional / Manajemen Pendidikan Tinggi / Good University Governance',
                'kualifikasi_pengalaman'  => 'Pernah menduduki jabatan struktural akademik minimal Ketua Jurusan / Wadek',
                'bahan_kerja'             => "1. Statuta Universitas Riau & Renstra Fakultas\n2. Rencana Kerja dan Anggaran (RKA/DIPA)\n3. Dokumen Akreditasi Nasional (LAM-PTKes) & Internasional\n4. Laporan Kinerja Fakultas",
                'perangkat_kerja'         => "1. SK Rektor & Aturan Perundang-undangan Pendidikan Tinggi\n2. Perangkat Komputer / Laptop & Akses SIMPEG / Sistem Informasi Kampus\n3. Ruang Rapat Pimpinan",
                'tanggung_jawab'          => "1. Ketercapaian Indikator Kinerja Utama (IKU) Fakultas Keperawatan\n2. Akuntabilitas penggunaan anggaran dan pembinaan kepegawaian\n3. Kelancaran suasana akademik yang kondusif dan bereputasi",
                'wewenang'                => "1. Menetapkan kebijakan operasional dan alokasi anggaran fakultas\n2. Menandatangani ijazah/transkrip dan SK Dekan\n3. Memberikan pembinaan dan penilaian kinerja bawahan",
                'korelasi_jabatan'        => "1. Rektor & Para Wakil Rektor UNRI: Pelaporan dan koordinasi kebijakan\n2. Para Wakil Dekan & Senat Fakultas: Koordinasi internal fakultas\n3. Mitra Stakeholder (RS, Dinas Kesehatan, Universitas Mitra)",
                'kondisi_lingkungan'      => "Ruang kerja pimpinan ber-AC, tenang, dengan fasilitas pertemuan representatif",
                'resiko_bahaya'           => "Stres kerja tingkat tinggi dalam pengambilan keputusan manajerial strategis",
                'syarat_keterampilan'     => "Kepemimpinan strategis, negosiasi, komunikasi publik, diplomasi institusional",
                'syarat_bakat'            => "G (Intelegensi), V (Verbal), N (Numerik)",
                'syarat_temperamen'       => "D (Memimpin), M (Menilai), P (Mempengaruhi)",
                'syarat_minat'            => "Sosial, Enterprising, Investigatif",
                'syarat_upaya_fisik'      => "Duduk, berbicara, memimpin rapat, menghadiri kegiatan institusional",
                'kondisi_fisik'           => "Sehat jasmani dan rohani",
                'prestasi_diharapkan'     => "Tercapainya 100% target IKU dan akreditasi Unggul pada seluruh program studi",
                'kelas_jabatan'           => 15,
                'status'                  => 'disetujui',
            ]);

            AbkUraianTugas::insert([
                [
                    'analisis_jabatan_id'    => $anjab->id,
                    'urutan'                 => 1,
                    'uraian_tugas'           => 'Menetapkan arah kebijakan strategis fakultas, rencana kerja tahunan, dan penganggaran berbasis IKU.',
                    'satuan_hasil'           => 'Dokumen Renja & IKU',
                    'norma_waktu_menit'      => 480,
                    'volume_1_tahun'         => 40,
                    'waktu_beban_menit'      => 19200,
                    'kebutuhan_pegawai'      => 19200 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjab->id,
                    'urutan'                 => 2,
                    'uraian_tugas'           => 'Memimpin rapat pimpinan berkala (Senat, Dekanat, Kajur, Koorprodi) dan evaluasi kinerja program.',
                    'satuan_hasil'           => 'Kegiatan Rapat',
                    'norma_waktu_menit'      => 180,
                    'volume_1_tahun'         => 96,
                    'waktu_beban_menit'      => 17280,
                    'kebutuhan_pegawai'      => 17280 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjab->id,
                    'urutan'                 => 3,
                    'uraian_tugas'           => 'Membina, mengawasi, dan menilai kinerja pejabat struktural serta dosen dan tenaga kependidikan.',
                    'satuan_hasil'           => 'Dokumen Penilaian SKP',
                    'norma_waktu_menit'      => 120,
                    'volume_1_tahun'         => 80,
                    'waktu_beban_menit'      => 9600,
                    'kebutuhan_pegawai'      => 9600 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjab->id,
                    'urutan'                 => 4,
                    'uraian_tugas'           => 'Menjalin dan memperluas kemitraan strategis tridharma dengan RS Pendidikan, Puskesmas, dan Universitas luar negeri.',
                    'satuan_hasil'           => 'Naskah Kerjasama / MoA',
                    'norma_waktu_menit'      => 300,
                    'volume_1_tahun'         => 50,
                    'waktu_beban_menit'      => 15000,
                    'kebutuhan_pegawai'      => 15000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjab->id,
                    'urutan'                 => 5,
                    'uraian_tugas'           => 'Menyusun Laporan Akuntabilitas Kinerja Instansi Pemerintah (LAKIP) dan laporan tahunan Dekan.',
                    'satuan_hasil'           => 'Laporan Tahunan',
                    'norma_waktu_menit'      => 600,
                    'volume_1_tahun'         => 24,
                    'waktu_beban_menit'      => 14400,
                    'kebutuhan_pegawai'      => 14400 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
            ]);
        }

        // 2. KOORDINATOR PROGRAM STUDI (S1 & PROFESI NERS)
        $jabKoorS1 = Jabatan::where('nama_jabatan', 'like', '%Koordinator Prodi S1%')->first();
        if ($jabKoorS1 && !AnalisisJabatan::where('jabatan_id', $jabKoorS1->id)->exists()) {
            $anjabKoor = AnalisisJabatan::create([
                'jabatan_id'              => $jabKoorS1->id,
                'unit_kerja_id'           => $unitProdiS1?->id,
                'kode_anjab'              => 'ANJAB-KPS-001',
                'ikhtisar_jabatan'        => 'Mengkoordinasikan perencanaan, pelaksanaan, monitoring, evaluasi kurikulum, perkuliahan, dan akreditasi pada Program Studi Sarjana Keperawatan.',
                'kualifikasi_pendidikan'  => 'Magister (S-2) / Doktor (S-3) Keperawatan dengan Jabatan Fungsional minimal Lektor',
                'kualifikasi_pelatihan'   => 'Pekerti/AA, Pelatihan Kurikulum OBE (Outcome-Based Education), Manajemen Program Studi',
                'kualifikasi_pengalaman'  => 'Dosen tetap minimal 3 tahun',
                'bahan_kerja'             => "1. Dokumen Kurikulum Program Studi\n2. Jadwal Perkuliahan & Praktikum\n3. Berkas Akreditasi LAM-PTKes\n4. Nilai Mahasiswa & RPS Mata Kuliah",
                'perangkat_kerja'         => "1. SIAKAD, SIMPEG, SISTER\n2. Komputer & Printer\n3. Rubrik Penilaian Capaian Pembelajaran Lulusan (CPL)",
                'tanggung_jawab'          => "1. Ketercapaian CPL dan kelulusan tepat waktu mahasiswa S1\n2. Penyelenggaraan evaluasi pembelajaran berbasis OBE\n3. Pertahankan peringkat akreditasi Unggul LAM-PTKes",
                'wewenang'                => "1. Menetapkan plotting dosen pengampu mata kuliah\n2. Menetapkan dosen pembimbing dan penguji skripsi\n3. Mengusulkan pembaruan RPS dan bahan kajian",
                'korelasi_jabatan'        => "1. Dekan & WD I: Laporan dan koordinasi kurikulum\n2. Dosen Prodi: Pengelolaan pengajaran dan RPS\n3. Mahasiswa S1: Pelayanan akademik dan pembimbingan",
                'kondisi_lingkungan'      => "Ruangan kerja dosen/prodi ber-AC",
                'resiko_bahaya'           => "Kelelahan psikologis dalam penanganan beban perkuliahan dan akreditasi",
                'syarat_keterampilan'     => "Desain kurikulum kesehatan, analisis CPL, kepemimpinan tim dosen",
                'syarat_bakat'            => "G, V, Q",
                'syarat_temperamen'       => "D, M, T",
                'syarat_minat'            => "Investigatif, Sosial",
                'syarat_upaya_fisik'      => "Duduk, mengajar, berbicara",
                'kondisi_fisik'           => "Sehat jasmani dan rohani",
                'prestasi_diharapkan'     => "Akreditasi Unggul dan masa tunggu kerja alumni < 3 bulan",
                'kelas_jabatan'           => 10,
                'status'                  => 'disetujui',
            ]);

            AbkUraianTugas::insert([
                [
                    'analisis_jabatan_id'    => $anjabKoor->id,
                    'urutan'                 => 1,
                    'uraian_tugas'           => 'Menyusun kalender akademik prodi, plotting dosen pengampu mata kuliah, dan pembagian dosen pembimbing akademik.',
                    'satuan_hasil'           => 'Dokumen Plotting',
                    'norma_waktu_menit'      => 360,
                    'volume_1_tahun'         => 50,
                    'waktu_beban_menit'      => 18000,
                    'kebutuhan_pegawai'      => 18000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabKoor->id,
                    'urutan'                 => 2,
                    'uraian_tugas'           => 'Melakukan audit mutu internal prodi, monitoring kesesuaian RPS, dan evaluasi hasil CPL mahasiswa.',
                    'satuan_hasil'           => 'Laporan Monev',
                    'norma_waktu_menit'      => 240,
                    'volume_1_tahun'         => 90,
                    'waktu_beban_menit'      => 21600,
                    'kebutuhan_pegawai'      => 21600 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabKoor->id,
                    'urutan'                 => 3,
                    'uraian_tugas'           => 'Menyelenggarakan ujian seminar proposal, seminar hasil, dan sidang sarjana keperawatan.',
                    'satuan_hasil'           => 'Sesi Ujian',
                    'norma_waktu_menit'      => 90,
                    'volume_1_tahun'         => 240,
                    'waktu_beban_menit'      => 21600,
                    'kebutuhan_pegawai'      => 21600 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabKoor->id,
                    'urutan'                 => 4,
                    'uraian_tugas'           => 'Menyusun Laporan Evaluasi Diri (LED) dan Laporan Kinerja Program Studi (LKPS) untuk akreditasi LAM-PTKes.',
                    'satuan_hasil'           => 'Dokumen LED & LKPS',
                    'norma_waktu_menit'      => 600,
                    'volume_1_tahun'         => 25,
                    'waktu_beban_menit'      => 15000,
                    'kebutuhan_pegawai'      => 15000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
            ]);
        }

        // 3. DOSEN FUNGSIONAL (LEKTOR / ASISTEN AHLI)
        $jabDosen = Jabatan::where('nama_jabatan', 'Lektor')->first() ?? Jabatan::where('nama_jabatan', 'like', '%Dosen%')->first();
        if ($jabDosen && !AnalisisJabatan::where('jabatan_id', $jabDosen->id)->exists()) {
            $anjabDosen = AnalisisJabatan::create([
                'jabatan_id'              => $jabDosen->id,
                'unit_kerja_id'           => $unitProdiS1?->id ?? $unitDekanat?->id,
                'kode_anjab'              => 'ANJAB-DSN-001',
                'ikhtisar_jabatan'        => 'Melaksanakan tridharma perguruan tinggi meliputi perkuliahan keperawatan, praktikum klinik, pembimbingan tugas akhir, penelitian jurnal terakreditasi, dan pengabdian masyarakat.',
                'kualifikasi_pendidikan'  => 'Magister (S-2) / Spesialis Keperawatan / Ners / Doktor (S-3)',
                'kualifikasi_pelatihan'   => 'Pekerti/AA, Pelatihan Metodologi Riset Kesehatan, Publikasi Internasional Bereputasi',
                'kualifikasi_pengalaman'  => 'Memiliki NIDN dan SK Fungsional Akademik',
                'bahan_kerja'             => "1. RPS & Bahan Ajar Perkuliahan\n2. Manikin & Alat Medis Laboratorium\n3. Proposal dan Data Riset Keperawatan\n4. Surat Tugas Tridharma & BKD",
                'perangkat_kerja'         => "1. LMS / SIAKAD / Google Scholar / Scopus\n2. Laptop, LCD Projector\n3. Instrumen Medis & Phantom Keperawatan",
                'tanggung_jawab'          => "1. Kelulusan pemahaman mahasiswa terhadap materi mata kuliah yang diampu\n2. Pemenuhan Beban Kerja Dosen (BKD) 12-16 SKS per semester\n3. Publikasi karya ilmiah dan etika akademik",
                'wewenang'                => "1. Memberikan penilaian nilai akhir mahasiswa (A/B/C/D/E)\n2. Menolak plagiarisme skripsi/tesis\n3. Mengajukan usulan dana hibah riset dan pengabdian",
                'korelasi_jabatan'        => "1. Koorprodi: Penugasan mata kuliah dan jadwal\n2. Mahasiswa: Pembimbingan dan perkuliahan\n3. LPPM UNRI: Pengelolaan hibah penelitian",
                'kondisi_lingkungan'      => "Ruang kelas kuliah, ruang laboratorium keperawatan, dan rumah sakit pendidikan",
                'resiko_bahaya'           => "Paparan kuman/cairan biologis pasien saat bimbingan klinik di RS",
                'syarat_keterampilan'     => "Keahlian klinis keperawatan, penulisan manuskrip ilmiah, pedagogik",
                'syarat_bakat'            => "G, V, Q",
                'syarat_temperamen'       => "I, P, T",
                'syarat_minat'            => "Investigatif, Sosial",
                'syarat_upaya_fisik'      => "Berdiri saat mengajar, melihat layar, berjalan di bangsal RS",
                'kondisi_fisik'           => "Sehat jasmani dan rohani",
                'prestasi_diharapkan'     => "Memenuhi BKD setiap semester dan mempublikasikan minimal 1 artikel ilmiah bereputasi per tahun",
                'kelas_jabatan'           => 10,
                'status'                  => 'disetujui',
            ]);

            AbkUraianTugas::insert([
                [
                    'analisis_jabatan_id'    => $anjabDosen->id,
                    'urutan'                 => 1,
                    'uraian_tugas'           => 'Menyiapkan materi ajar, RPS, dan melaksanakan perkuliahan teori tatap muka/daring serta evaluasi UTS/UAS.',
                    'satuan_hasil'           => 'Sesi Kuliah',
                    'norma_waktu_menit'      => 100,
                    'volume_1_tahun'         => 320,
                    'waktu_beban_menit'      => 32000,
                    'kebutuhan_pegawai'      => 32000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabDosen->id,
                    'urutan'                 => 2,
                    'uraian_tugas'           => 'Membimbing praktikum laboratorium klinik dan pembimbingan pre-post conference mahasiswa stase di RS.',
                    'satuan_hasil'           => 'Sesi Bimbingan',
                    'norma_waktu_menit'      => 120,
                    'volume_1_tahun'         => 180,
                    'waktu_beban_menit'      => 21600,
                    'kebutuhan_pegawai'      => 21600 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabDosen->id,
                    'urutan'                 => 3,
                    'uraian_tugas'           => 'Melakukan penelitian di bidang keperawatan kesehatan dan menyusun manuskrip jurnal terakreditasi/internasional.',
                    'satuan_hasil'           => 'Artikel Publikasi',
                    'norma_waktu_menit'      => 480,
                    'volume_1_tahun'         => 25,
                    'waktu_beban_menit'      => 12000,
                    'kebutuhan_pegawai'      => 12000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabDosen->id,
                    'urutan'                 => 4,
                    'uraian_tugas'           => 'Melaksanakan kegiatan pengabdian kepada masyarakat (edukasi kesehatan warga, posyandu lansia, dsb).',
                    'satuan_hasil'           => 'Kegiatan PkM',
                    'norma_waktu_menit'      => 360,
                    'volume_1_tahun'         => 28,
                    'waktu_beban_menit'      => 10080,
                    'kebutuhan_pegawai'      => 10080 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
            ]);
        }

        // 4. STAFF POKJA AKADEMIK DAN KEMAHASISWAAN
        $jabStafAkad = Jabatan::where('nama_jabatan', 'Staff Pokja Akademik dan Kemahasiswaan')->first();
        if ($jabStafAkad && !AnalisisJabatan::where('jabatan_id', $jabStafAkad->id)->exists()) {
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
}
