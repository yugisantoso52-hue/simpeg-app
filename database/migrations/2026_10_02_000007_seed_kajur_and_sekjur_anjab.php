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
     * Menanamkan Dokumen Baku Anjab & ABK untuk:
     * 1. Ketua Jurusan (Kajur) - Berada di bawah Wakil Dekan I (Bid. Akademik)
     * 2. Sekretaris Jurusan (Sekjur) - Berada di bawah Ketua Jurusan
     * Membawahi Jurusan Preklinik Keperawatan dan Jurusan Klinik & Komunitas.
     */
    public function up(): void
    {
        // 1. Pastikan Hirarki Unit Kerja Jurusan Induk dan Cabang
        $unitDekanat   = UnitKerja::where('kode_unit', 'UK-001')->orWhere('nama_unit', 'like', '%Dekan%')->first();
        $unitJurusan   = UnitKerja::where('kode_unit', 'UK-004')->orWhere('nama_unit', 'like', '%Jurusan Keperawatan%')->first();
        $unitPreklinik = UnitKerja::where('kode_unit', 'UK-005')->orWhere('nama_unit', 'like', '%Preklinik%')->first();
        $unitKlinik    = UnitKerja::where('kode_unit', 'UK-006')->orWhere('nama_unit', 'like', '%Klinik%')->first();

        if ($unitJurusan) {
            $unitJurusan->update([
                'parent_id' => $unitDekanat?->id,
                'tipe_unit' => 'Jurusan Induk',
            ]);
        }

        if ($unitPreklinik && $unitJurusan) {
            $unitPreklinik->update([
                'parent_id' => $unitJurusan->id,
                'tipe_unit' => 'Jurusan Bidang',
            ]);
        }

        if ($unitKlinik && $unitJurusan) {
            $unitKlinik->update([
                'parent_id' => $unitJurusan->id,
                'tipe_unit' => 'Jurusan Bidang',
            ]);
        }

        // ==========================================
        // 2. KETUA JURUSAN (KAJUR) - ANJAB-KJR-001
        // ==========================================
        $jabKajur = Jabatan::where('nama_jabatan', 'like', '%Ketua Jurusan (Kajur)%')
            ->orWhere('nama_jabatan', 'Ketua Jurusan')
            ->first();

        if (!$jabKajur) {
            $jabKajur = Jabatan::create([
                'nama_jabatan'     => 'Ketua Jurusan (Kajur)',
                'kode_jabatan'     => 'JAB-KJR-001',
                'unit_kerja_id'    => $unitJurusan?->id,
                'kelas_jabatan'    => 11,
                'kelompok_jabatan' => 'Dosen Tugas Tambahan',
                'ikhtisar_jabatan' => 'Memimpin jurusan dalam pengelolaan tridharma, pembagian beban kerja dosen, dan membawahi Jurusan Preklinik serta Jurusan Klinik & Komunitas.',
            ]);
        } else {
            $jabKajur->update([
                'unit_kerja_id'    => $unitJurusan?->id,
                'kelas_jabatan'    => 11,
                'kelompok_jabatan' => 'Dosen Tugas Tambahan',
            ]);
        }

        $anjabKajur = AnalisisJabatan::where('kode_anjab', 'ANJAB-KJR-001')->orWhere('jabatan_id', $jabKajur->id)->first();
        if (!$anjabKajur) {
            $anjabKajur = AnalisisJabatan::create([
                'jabatan_id'              => $jabKajur->id,
                'unit_kerja_id'           => $unitJurusan?->id,
                'kode_anjab'              => 'ANJAB-KJR-001',
                'ikhtisar_jabatan'        => 'Memimpin, merencanakan, mengkoordinasikan, dan mengawasi seluruh penyelenggaraan pendidikan akademik dan profesi, pengelolaan sumber daya dosen dan tenaga kependidikan pada Jurusan Preklinik Keperawatan dan Jurusan Klinik dan Komunitas di bawah koordinasi Wakil Dekan Bidang Akademik.',
                'kualifikasi_pendidikan'  => 'Doktor (S-3) / Magister (S-2) Keperawatan dengan Jabatan Fungsional minimal Lektor Kepala',
                'kualifikasi_pelatihan'   => 'Kepemimpinan Manajemen Jurusan, Pekerti/AA, Audit Mutu Akademik Internal, Kurikulum OBE',
                'kualifikasi_pengalaman'  => 'Dosen tetap minimal 4 tahun dan berpengalaman dalam pengelolaan program studi / laboratorium',
                'bahan_kerja'             => "1. Statuta UNRI & Rencana Strategis Fakultas\n2. Dokumen Kurikulum S1, S2, S3, dan Profesi Ners\n3. Data Beban Kerja Dosen (BKD) dan SKP\n4. Laporan Kinerja Jurusan Preklinik dan Klinik",
                'perangkat_kerja'         => "1. SIAKAD, SIMPEG, SISTER, dan Google Workspace\n2. Laptop / Komputer & Akses Internet\n3. Ruang Rapat Pimpinan Jurusan",
                'tanggung_jawab'          => "1. Kelancaran dan mutu perkuliahan teori, laboratorium, dan praktik klinik\n2. Pembagian beban tridharma dosen yang adil dan berkeadilan (BKD)\n3. Ketercapaian IKU jurusan dan akreditasi Unggul seluruh prodi",
                'wewenang'                => "1. Menetapkan plotting beban mengajar dan bimbingan tugas akhir dosen\n2. Memberikan rekomendasi kenaikan pangkat dan jabatan fungsional dosen\n3. Memimpin rapat evaluasi berkala pimpinan jurusan dan koorprodi",
                'korelasi_jabatan'        => "1. Dekan & WD I: Pelaporan dan pertanggungjawaban operasional akademik\n2. Koorprodi (S1, S2, S3, Ners): Arahan dan koordinasi kurikulum\n3. Dosen & Laboran: Pembinaan dan supervisi tugas harian",
                'kondisi_lingkungan'      => "Ruang kerja kantor ber-AC representatif dan lingkungan kampus yang kondusif",
                'resiko_bahaya'           => "Kelelahan psikologis dalam penanganan dinamika akademik dan akreditasi",
                'syarat_keterampilan'     => "Kepemimpinan akademik, manajemen konflik, komunikasi saintifik, desain kurikulum kesehatan",
                'syarat_bakat'            => "G (Intelegensi Umum), V (Verbal), Q (Klerikal)",
                'syarat_temperamen'       => "D (Memimpin), M (Menilai), T (Toleransi Tekanan)",
                'syarat_minat'            => "Enterprising, Sosial, Investigatif",
                'syarat_upaya_fisik'      => "Duduk, berbicara, memimpin rapat, meninjau laboratorium dan wahana klinik",
                'kondisi_fisik'           => "Sehat jasmani dan rohani",
                'prestasi_diharapkan'     => "Tercapainya 100% pemenuhan BKD dosen dan seluruh program studi terakreditasi Unggul",
                'kelas_jabatan'           => 11,
                'status'                  => 'disetujui',
            ]);
        }

        if ($anjabKajur && AbkUraianTugas::where('analisis_jabatan_id', $anjabKajur->id)->count() === 0) {
            AbkUraianTugas::insert([
                [
                    'analisis_jabatan_id'    => $anjabKajur->id,
                    'urutan'                 => 1,
                    'uraian_tugas'           => 'Menyusun rencana kerja tahunan jurusan, pembagian beban mengajar dosen (BKD), dan kalender kegiatan akademik jurusan.',
                    'satuan_hasil'           => 'Dokumen Renja Jurusan',
                    'norma_waktu_menit'      => 360,
                    'volume_1_tahun'         => 45,
                    'waktu_beban_menit'      => 16200,
                    'kebutuhan_pegawai'      => 16200 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabKajur->id,
                    'urutan'                 => 2,
                    'uraian_tugas'           => 'Mengkoordinasikan dan mengawasi pelaksanaan perkuliahan, praktikum laboratorium, dan kepaniteraan klinik pada Jurusan Preklinik serta Jurusan Klinik & Komunitas.',
                    'satuan_hasil'           => 'Sesi Supervisi',
                    'norma_waktu_menit'      => 180,
                    'volume_1_tahun'         => 100,
                    'waktu_beban_menit'      => 18000,
                    'kebutuhan_pegawai'      => 18000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabKajur->id,
                    'urutan'                 => 3,
                    'uraian_tugas'           => 'Memimpin rapat koordinasi rutin jurusan bersama Sekretaris Jurusan, para Koorprodi (S1, S2, S3, Ners), dan Kepala Laboratorium.',
                    'satuan_hasil'           => 'Sesi Rapat',
                    'norma_waktu_menit'      => 120,
                    'volume_1_tahun'         => 48,
                    'waktu_beban_menit'      => 5760,
                    'kebutuhan_pegawai'      => 5760 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabKajur->id,
                    'urutan'                 => 4,
                    'uraian_tugas'           => 'Melakukan pembinaan karier, penilaian kinerja (SKP), dan rekomendasi kenaikan jabatan fungsional dosen pada kelompok keahlian (KJFD).',
                    'satuan_hasil'           => 'Berkas Penilaian',
                    'norma_waktu_menit'      => 120,
                    'volume_1_tahun'         => 85,
                    'waktu_beban_menit'      => 10200,
                    'kebutuhan_pegawai'      => 10200 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabKajur->id,
                    'urutan'                 => 5,
                    'uraian_tugas'           => 'Menyusun laporan evaluasi kinerja tahunan jurusan dan pertanggungjawaban program kerja kepada Dekan melalui Wakil Dekan Bidang Akademik.',
                    'satuan_hasil'           => 'Laporan Tahunan',
                    'norma_waktu_menit'      => 480,
                    'volume_1_tahun'         => 25,
                    'waktu_beban_menit'      => 12000,
                    'kebutuhan_pegawai'      => 12000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
            ]);
        }

        // ==========================================
        // 3. SEKRETARIS JURUSAN - ANJAB-SJR-001
        // ==========================================
        $jabSekjur = Jabatan::where('nama_jabatan', 'Sekretaris Jurusan')->first();
        if (!$jabSekjur) {
            $jabSekjur = Jabatan::create([
                'nama_jabatan'     => 'Sekretaris Jurusan',
                'kode_jabatan'     => 'JAB-SJR-001',
                'unit_kerja_id'    => $unitJurusan?->id,
                'kelas_jabatan'    => 10,
                'kelompok_jabatan' => 'Dosen Tugas Tambahan',
                'ikhtisar_jabatan' => 'Membantu Ketua Jurusan dalam administrasi akademik, notulensi rapat, rekapitulasi BKD/SKP dosen, dan sinkronisasi jadwal.',
            ]);
        } else {
            $jabSekjur->update([
                'unit_kerja_id'    => $unitJurusan?->id,
                'kelas_jabatan'    => 10,
                'kelompok_jabatan' => 'Dosen Tugas Tambahan',
            ]);
        }

        $anjabSekjur = AnalisisJabatan::where('kode_anjab', 'ANJAB-SJR-001')->orWhere('jabatan_id', $jabSekjur->id)->first();
        if (!$anjabSekjur) {
            $anjabSekjur = AnalisisJabatan::create([
                'jabatan_id'              => $jabSekjur->id,
                'unit_kerja_id'           => $unitJurusan?->id,
                'kode_anjab'              => 'ANJAB-SJR-001',
                'ikhtisar_jabatan'        => 'Membantu Ketua Jurusan dalam pengelolaan administrasi akademik, ketatausahaan, dokumentasi kurikulum, rekapitulasi BKD/SKP dosen, serta pengkoordinasian jadwal perkuliahan antara Jurusan Preklinik dan Jurusan Klinik & Komunitas.',
                'kualifikasi_pendidikan'  => 'Magister (S-2) / Doktor (S-3) Keperawatan dengan Jabatan Fungsional minimal Lektor',
                'kualifikasi_pelatihan'   => 'Pekerti/AA, Manajemen Administrasi Perguruan Tinggi, Kearsipan Digital',
                'kualifikasi_pengalaman'  => 'Dosen tetap minimal 3 tahun',
                'bahan_kerja'             => "1. Dokumen Agenda & Undangan Rapat Jurusan\n2. Berkas Beban Kerja Dosen (BKD) & Nilai Mahasiswa\n3. Draf Jadwal Perkuliahan & Praktikum\n4. Notulensi Rapat & Arsip Jurusan",
                'perangkat_kerja'         => "1. Komputer / Laptop & SIAKAD / SIMPEG / SISTER\n2. ATK & Buku Register Persuratan\n3. Printer & Scanner Berkas",
                'tanggung_jawab'          => "1. Ketepatan dan kelengkapan dokumentasi administrasi jurusan\n2. Kerapian arsip notulensi rapat dan berita acara akademik\n3. Ketepatan waktu kompilasi berkas evaluasi BKD dosen",
                'wewenang'                => "1. Memeriksa kelengkapan berkas usulan dan laporan BKD dosen\n2. Mengatur jadwal rapat dan menyusun draf notulen\n3. Mengusulkan perbaikan alur administrasi jurusan",
                'korelasi_jabatan'        => "1. Ketua Jurusan: Atasan langsung untuk arahan dan laporan\n2. Para Koorprodi: Koordinasi jadwal dan ruang perkuliahan\n3. Dosen Jurusan: Pengumpulan berkas BKD dan SKP",
                'kondisi_lingkungan'      => "Ruang kerja kantor jurusan ber-AC dan tenang",
                'resiko_bahaya'           => "Kelelahan mata akibat monitor komputer",
                'syarat_keterampilan'     => "Administrasi akademik, notulensi cepat, Microsoft Office, kearsipan elektronik",
                'syarat_bakat'            => "Q (Klerikal), V (Verbal), G (Intelegensi)",
                'syarat_temperamen'       => "R (Rutin/Teratur), T (Tepat/Teliti)",
                'syarat_minat'            => "Konvensional, Sosial",
                'syarat_upaya_fisik'      => "Duduk, mengetik, memeriksa dokumen berkas",
                'kondisi_fisik'           => "Sehat jasmani dan rohani",
                'prestasi_diharapkan'     => "Administrasi dan notulensi rapat selesai maksimal 1 hari setelah kegiatan",
                'kelas_jabatan'           => 10,
                'status'                  => 'disetujui',
            ]);
        }

        if ($anjabSekjur && AbkUraianTugas::where('analisis_jabatan_id', $anjabSekjur->id)->count() === 0) {
            AbkUraianTugas::insert([
                [
                    'analisis_jabatan_id'    => $anjabSekjur->id,
                    'urutan'                 => 1,
                    'uraian_tugas'           => 'Menyiapkan agenda, bahan rapat, dan notulensi rapat pleno serta koordinasi berkala pimpinan jurusan.',
                    'satuan_hasil'           => 'Notulen Rapat',
                    'norma_waktu_menit'      => 120,
                    'volume_1_tahun'         => 50,
                    'waktu_beban_menit'      => 6000,
                    'kebutuhan_pegawai'      => 6000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabSekjur->id,
                    'urutan'                 => 2,
                    'uraian_tugas'           => 'Mengompilasi dan memverifikasi berkas Beban Kerja Dosen (BKD) serta SKP seluruh dosen tetap jurusan setiap semester.',
                    'satuan_hasil'           => 'Berkas BKD',
                    'norma_waktu_menit'      => 90,
                    'volume_1_tahun'         => 170,
                    'waktu_beban_menit'      => 15300,
                    'kebutuhan_pegawai'      => 15300 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabSekjur->id,
                    'urutan'                 => 3,
                    'uraian_tugas'           => 'Mengkoordinasikan sinkronisasi jadwal perkuliahan, pemakaian ruang kelas, dan jadwal ujian bersama para Koorprodi.',
                    'satuan_hasil'           => 'Jadwal Perkuliahan',
                    'norma_waktu_menit'      => 180,
                    'volume_1_tahun'         => 60,
                    'waktu_beban_menit'      => 10800,
                    'kebutuhan_pegawai'      => 10800 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabSekjur->id,
                    'urutan'                 => 4,
                    'uraian_tugas'           => 'Mendokumentasikan dan memelihara arsip kurikulum, silabus RPS, dan berita acara yudisium/ujian sidang skripsi/tesis.',
                    'satuan_hasil'           => 'Dokumen Arsip',
                    'norma_waktu_menit'      => 60,
                    'volume_1_tahun'         => 200,
                    'waktu_beban_menit'      => 12000,
                    'kebutuhan_pegawai'      => 12000 / 75000,
                    'created_at'             => now(), 'updated_at' => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabSekjur->id,
                    'urutan'                 => 5,
                    'uraian_tugas'           => 'Menyusun draf laporan kinerja berkala dan laporan tahunan jurusan bersama Ketua Jurusan.',
                    'satuan_hasil'           => 'Laporan Kinerja',
                    'norma_waktu_menit'      => 360,
                    'volume_1_tahun'         => 25,
                    'waktu_beban_menit'      => 9000,
                    'kebutuhan_pegawai'      => 9000 / 75000,
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
