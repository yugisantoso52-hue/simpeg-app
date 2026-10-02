<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Models\AnalisisJabatan;
use App\Models\AbkUraianTugas;
use App\Models\Pegawai;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengimplementasikan 26 Nomenklatur Jabatan Baku Sesuai Bagan Struktur Organisasi
     * Fakultas Keperawatan UNRI dan Standar PermenPAN-RB No. 1 Tahun 2020.
     */
    public function up(): void
    {
        // 1. Ambil Unit-Unit Kerja Utama
        $uDekanat   = UnitKerja::where('kode_unit', 'UK-001')->orWhere('nama_unit', 'like', '%Dekan%')->first();
        $uPreklinik = UnitKerja::where('kode_unit', 'UK-005')->orWhere('nama_unit', 'like', '%Preklinik%')->first();
        $uKlinik    = UnitKerja::where('kode_unit', 'UK-006')->orWhere('nama_unit', 'like', '%Klinik%')->first();
        $uProdiS1   = UnitKerja::where('kode_unit', 'UK-009')->orWhere('nama_unit', 'like', '%Prodi S1%')->first();
        $uProdiS2   = UnitKerja::where('kode_unit', 'UK-007')->orWhere('nama_unit', 'like', '%Prodi S2%')->first();
        $uProdiNers = UnitKerja::where('kode_unit', 'UK-008')->orWhere('nama_unit', 'like', '%Ners%')->first();
        $uProdiS3   = UnitKerja::where('kode_unit', 'UK-016')->orWhere('nama_unit', 'like', '%Prodi S3%')->first();
        $uBagUmum   = UnitKerja::where('kode_unit', 'UK-010')->orWhere('nama_unit', 'like', '%Bagian Umum%')->first();
        $uPokjaAkad = UnitKerja::where('kode_unit', 'UK-011')->orWhere('nama_unit', 'like', '%Akademik dan Kemahasiswaan%')->first();
        $uPokjaKeu  = UnitKerja::where('kode_unit', 'UK-012')->orWhere('nama_unit', 'like', '%Keuangan dan Kepe%')->first();
        $uPokjaUmum = UnitKerja::where('kode_unit', 'UK-013')->orWhere('nama_unit', 'like', '%Umum Sarana%')->first();
        $uLab       = UnitKerja::where('kode_unit', 'UK-014')->orWhere('nama_unit', 'like', '%Laboratorium%')->first();
        $uFungsional= UnitKerja::where('kode_unit', 'UK-015')->orWhere('nama_unit', 'like', '%Unit Fungsional%')->first();
        $uSPMF      = UnitKerja::where('kode_unit', 'UK-003')->orWhere('nama_unit', 'like', '%SPMF%')->first() ?? $uDekanat;

        // 2. Sinkronkan unit_kerja_id pejabat struktural yang ada di tabel Pegawai
        Pegawai::where('nama', 'like', '%Reni Zulfitri%')->update(['unit_kerja_id' => $uDekanat?->id]);
        Pegawai::where('nama', 'like', '%Sri Wahyuni%')->update(['unit_kerja_id' => $uDekanat?->id]);
        Pegawai::where('nama', 'like', '%Bakhtiar%')->update(['unit_kerja_id' => $uBagUmum?->id]);

        // 3. DAFTAR MASTER 26 NOMENKLATUR JABATAN BAKU
        $masterPositions = [
            // Kategori 1: Pimpinan Tinggi & Administrasi
            [
                'kode_anjab' => 'ANJAB-WD1-001',
                'nama_jabatan' => 'Wakil Dekan I (Bid. Akademik)',
                'unit_id' => $uDekanat?->id,
                'kelas' => 13,
                'kelompok' => 'Pimpinan',
                'ikhtisar' => 'Membantu Dekan dalam memimpin penyelenggaraan pendidikan, penelitian, pengabdian kepada masyarakat, penjaminan mutu akademik, dan kerjasama pendidikan.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyusun rencana strategis dan operasional bidang akademik, kurikulum OBE, dan kalender akademik fakultas.', 'satuan' => 'Dokumen Renja', 'menit' => 360, 'vol' => 40],
                    ['urutan' => 2, 'tugas' => 'Mengoordinasikan pelaksanaan perkuliahan, praktikum laboratorium, kepaniteraan klinik, dan ujian mahasiswa.', 'satuan' => 'Kegiatan', 'menit' => 180, 'vol' => 120],
                    ['urutan' => 3, 'tugas' => 'Memimpin rapat koordinasi bidang akademik bersama para Ketua Jurusan, Koorprodi, dan SPMF/GPM.', 'satuan' => 'Sesi Rapat', 'menit' => 120, 'vol' => 48],
                    ['urutan' => 4, 'tugas' => 'Menyusun laporan evaluasi mutu akademik dan akreditasi internasional/nasional fakultas.', 'satuan' => 'Laporan Monev', 'menit' => 480, 'vol' => 25],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-WD2-001',
                'nama_jabatan' => 'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'unit_id' => $uDekanat?->id,
                'kelas' => 13,
                'kelompok' => 'Pimpinan',
                'ikhtisar' => 'Membantu Dekan dalam pengelolaan perencanaan anggaran, perbendaharaan, pembinaan kepegawaian, ketatausahaan, dan sarana prasarana.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyusun Rencana Kerja dan Anggaran (RKA/DIPA) serta alokasi belanja operasional fakultas.', 'satuan' => 'Dokumen RKA', 'menit' => 480, 'vol' => 30],
                    ['urutan' => 2, 'tugas' => 'Melakukan supervisi pengelolaan keuangan, pertanggungjawaban SPJ, dan serapan anggaran berkala.', 'satuan' => 'Laporan Serapan', 'menit' => 180, 'vol' => 96],
                    ['urutan' => 3, 'tugas' => 'Membina disiplin, formasi beban kerja (ABK), kenaikan pangkat, dan pengembangan kompetensi SDM pegawai.', 'satuan' => 'Dokumen SDM', 'menit' => 120, 'vol' => 60],
                    ['urutan' => 4, 'tugas' => 'Mengawasi pemeliharaan fasilitas gedung, sarana laboratorium, dan inventarisasi Barang Milik Negara (BMN).', 'satuan' => 'Laporan BMN', 'menit' => 240, 'vol' => 45],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-WD3-001',
                'nama_jabatan' => 'Wakil Dekan III (Bid. Kemahasiswaan, Alumni dan Kerjasama)',
                'unit_id' => $uDekanat?->id,
                'kelas' => 13,
                'kelompok' => 'Pimpinan',
                'ikhtisar' => 'Membantu Dekan dalam pembinaan kegiatan kemahasiswaan, prestasi penalaran, kesejahteraan mahasiswa/beasiswa, hubungan alumni, dan jejaring kerjasama.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyusun program kerja pembinaan minat, bakat, penalaran, dan kompetisi mahasiswa tingkat nasional/internasional.', 'satuan' => 'Program Kerja', 'menit' => 360, 'vol' => 35],
                    ['urutan' => 2, 'tugas' => 'Memfasilitasi seleksi penerima beasiswa, verifikasi keringanan UKT, dan layanan konseling kemahasiswaan.', 'satuan' => 'Berkas Verifikasi', 'menit' => 120, 'vol' => 80],
                    ['urutan' => 3, 'tugas' => 'Mengembangkan jejaring kemitraan luar negeri, Rumah Sakit Pendidikan, Dinas Kesehatan, dan Ikatan Alumni (IKA).', 'satuan' => 'Naskah Kerjasama', 'menit' => 300, 'vol' => 40],
                    ['urutan' => 4, 'tugas' => 'Melaksanakan koordinasi tracer study alumni dan pendampingan kelulusan uji kompetensi (UKNI).', 'satuan' => 'Laporan Tracer', 'menit' => 240, 'vol' => 50],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-KBU-001',
                'nama_jabatan' => 'Kepala Bagian Umum',
                'unit_id' => $uBagUmum?->id,
                'kelas' => 11,
                'kelompok' => 'Tenaga Kependidikan',
                'ikhtisar' => 'Memimpin dan mengkoordinasikan seluruh urusan ketatausahaan, layanan akademik, kepegawaian, keuangan, kerumahtanggaan, dan perlengkapan fakultas.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Mengoordinasikan dan membagi tugas kepada para Ketua Pokja (Akademik, Keuangan/Kepegawaian, dan Umum/Sarana).', 'satuan' => 'Arahan Kerja', 'menit' => 120, 'vol' => 240],
                    ['urutan' => 2, 'tugas' => 'Meneliti dan memaraf persuratan dinas, dokumen usulan kepegawaian, dan berkas pencairan anggaran.', 'satuan' => 'Dokumen Naskah', 'menit' => 30, 'vol' => 900],
                    ['urutan' => 3, 'tugas' => 'Mengawasi ketertiban tata persuratan, kearsipan, kebersihan, keamanan lingkungan kampus, dan aset fakultas.', 'satuan' => 'Laporan Supervisi', 'menit' => 90, 'vol' => 150],
                    ['urutan' => 4, 'tugas' => 'Menyusun evaluasi kinerja tenaga kependidikan dan laporan tahunan Bagian Umum.', 'satuan' => 'Laporan Tahunan', 'menit' => 360, 'vol' => 20],
                ]
            ],

            // Kategori 2: Jabatan Pelaksana (Ketua Pokja)
            [
                'kode_anjab' => 'ANJAB-KPA-001',
                'nama_jabatan' => 'Ka Pokja Akademik',
                'unit_id' => $uPokjaAkad?->id,
                'kelas' => 9,
                'kelompok' => 'Tenaga Kependidikan',
                'ikhtisar' => 'Mengkoordinasikan dan melaksanakan layanan registrasi perkuliahan, administrasi nilai, ujian, yudisium, dan beasiswa kemahasiswaan.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Mengoordinasikan verifikasi pengisian KRS mahasiswa, perubahan mata kuliah, dan pembagian ruang kelas di SIAKAD.', 'satuan' => 'Layanan KRS', 'menit' => 45, 'vol' => 600],
                    ['urutan' => 2, 'tugas' => 'Memverifikasi kelayakan berkas pendaftaran yudisium, bebas pustaka, dan pencetakan ijazah/transkrip.', 'satuan' => 'Berkas Yudisium', 'menit' => 60, 'vol' => 350],
                    ['urutan' => 3, 'tugas' => 'Mengoordinasikan pengusulan dan verifikasi berkas mahasiswa penerima beasiswa (KIP-K, BI, Pemprov).', 'satuan' => 'Berkas Beasiswa', 'menit' => 60, 'vol' => 200],
                    ['urutan' => 4, 'tugas' => 'Menyusun laporan rekapitulasi data mahasiswa aktif, drop-out, cuti kuliah, dan kelulusan.', 'satuan' => 'Laporan Data', 'menit' => 180, 'vol' => 40],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-KPK-001',
                'nama_jabatan' => 'Ka Pokja Keu-Kepeg',
                'unit_id' => $uPokjaKeu?->id,
                'kelas' => 9,
                'kelompok' => 'Tenaga Kependidikan',
                'ikhtisar' => 'Mengkoordinasikan penyusunan pertanggungjawaban anggaran, verifikasi SPJ, layanan mutasi kepegawaian, presensi, SKP, dan kenaikan pangkat.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Memverifikasi bukti kuitansi belanja, SPJ kegiatan tridharma, dan dokumen pencairan dana operasional.', 'satuan' => 'Berkas SPJ', 'menit' => 60, 'vol' => 400],
                    ['urutan' => 2, 'tugas' => 'Memproses usulan kenaikan pangkat (KP), kenaikan gaji berkala (KGB), dan pensiun pegawai dosen/tendik.', 'satuan' => 'Usulan KP/KGB', 'menit' => 120, 'vol' => 80],
                    ['urutan' => 3, 'tugas' => 'Merekapitulasi dan memvalidasi presensi harian pegawai untuk dasar pembayaran tunjangan kinerja/uang makan.', 'satuan' => 'Rekap Presensi', 'menit' => 90, 'vol' => 240],
                    ['urutan' => 4, 'tugas' => 'Menyusun dokumen pemetaan bezetting dan evaluasi kebutuhan formasi ASN/CASN fakultas.', 'satuan' => 'Dokumen Bezetting', 'menit' => 360, 'vol' => 25],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-KPU-001',
                'nama_jabatan' => 'Ka Pokja Umum Sarana Akademik',
                'unit_id' => $uPokjaUmum?->id,
                'kelas' => 9,
                'kelompok' => 'Tenaga Kependidikan',
                'ikhtisar' => 'Mengkoordinasikan pengelolaan Barang Milik Negara (BMN), sarana prasarana perkuliahan, pemeliharaan gedung, kebersihan, dan keamanan kampus.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Melakukan inventarisasi, kodifikasi, dan pemutakhiran data aset Barang Milik Negara (BMN) di aplikasi SIMAK-BMN.', 'satuan' => 'Data Aset', 'menit' => 90, 'vol' => 250],
                    ['urutan' => 2, 'tugas' => 'Mengatur kesiapan sarana ruang kuliah, AC, LCD proyektor, dan sound system untuk kenyamanan belajar.', 'satuan' => 'Pemeriksaan Ruang', 'menit' => 45, 'vol' => 480],
                    ['urutan' => 3, 'tugas' => 'Mengoordinasikan jadwal tugas petugas kebersihan, keamanan (satpam), dan pengemudi dinas.', 'satuan' => 'Jadwal Kerja', 'menit' => 60, 'vol' => 150],
                    ['urutan' => 4, 'tugas' => 'Menyusun usulan rencana pemeliharaan berkala gedung, instalasi listrik, dan pengadaan sarana baru.', 'satuan' => 'Usulan Sarpras', 'menit' => 300, 'vol' => 30],
                ]
            ],

            // Kategori 3: Jabatan Fungsional Dosen Tugas Tambahan (Jurusan & Penjaminan Mutu)
            [
                'kode_anjab' => 'ANJAB-KJP-001',
                'nama_jabatan' => 'Ketua Jurusan Preklinik Keperawatan',
                'unit_id' => $uPreklinik?->id,
                'kelas' => 11,
                'kelompok' => 'Dosen Tugas Tambahan',
                'ikhtisar' => 'Memimpin jurusan preklinik dalam pengelolaan sumber daya pendidik/kependidikan, kurikulum dasar keperawatan, dan koordinasi program studi S1, S2, S3.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyusun rencana pengembangan akademik jurusan preklinik dan pembagian laboratorium penunjang teori.', 'satuan' => 'Rencana Jurusan', 'menit' => 360, 'vol' => 30],
                    ['urutan' => 2, 'tugas' => 'Mengoordinasikan dosen rumpun keilmuan biomedik, dasar keperawatan, dan pembagian tugas pembelajaran.', 'satuan' => 'Koordinasi Dosen', 'menit' => 120, 'vol' => 80],
                    ['urutan' => 3, 'tugas' => 'Melakukan monitoring pelaksanaan tridharma perguruan tinggi pada program studi di bawah jurusan preklinik.', 'satuan' => 'Laporan Monev', 'menit' => 180, 'vol' => 45],
                    ['urutan' => 4, 'tugas' => 'Mengevaluasi kinerja dosen dan usulan kenaikan jabatan fungsional akademik dosen preklinik.', 'satuan' => 'Dokumen Penilaian', 'menit' => 120, 'vol' => 60],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-SJP-001',
                'nama_jabatan' => 'Sekretaris Jurusan Preklinik Keperawatan',
                'unit_id' => $uPreklinik?->id,
                'kelas' => 10,
                'kelompok' => 'Dosen Tugas Tambahan',
                'ikhtisar' => 'Membantu Ketua Jurusan Preklinik dalam administrasi akademik, notulensi rapat jurusan, dokumentasi kurikulum, dan evaluasi beban kerja dosen.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyiapkan agenda, bahan, dan notulensi rapat koordinasi jurusan preklinik keperawatan.', 'satuan' => 'Notulen Rapat', 'menit' => 120, 'vol' => 48],
                    ['urutan' => 2, 'tugas' => 'Mengompilasi data Beban Kerja Dosen (BKD) dan laporan kinerja semesteran dosen preklinik.', 'satuan' => 'Rekap BKD', 'menit' => 90, 'vol' => 90],
                    ['urutan' => 3, 'tugas' => 'Mendokumentasikan arsip kurikulum, silabus, dan berita acara kegiatan akademik jurusan.', 'satuan' => 'Dokumen Arsip', 'menit' => 60, 'vol' => 150],
                    ['urutan' => 4, 'tugas' => 'Menyusun draf laporan pertanggungjawaban tahunan pelaksanaan program kerja jurusan preklinik.', 'satuan' => 'Laporan Tahunan', 'menit' => 300, 'vol' => 25],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-KJK-001',
                'nama_jabatan' => 'Ketua Jurusan Klinik dan Komunitas',
                'unit_id' => $uKlinik?->id,
                'kelas' => 11,
                'kelompok' => 'Dosen Tugas Tambahan',
                'ikhtisar' => 'Memimpin jurusan klinik dan komunitas dalam pengelolaan pembelajaran praktik klinik RS, wahana puskesmas, dan koordinasi Prodi Profesi Ners.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyusun kebijakan kurikulum kepaniteraan klinik keperawatan di RS Pendidikan dan fasilitas pelayanan kesehatan.', 'satuan' => 'Pedoman Klinik', 'menit' => 360, 'vol' => 30],
                    ['urutan' => 2, 'tugas' => 'Mengoordinasikan dosen pembimbing klinik (preseptor akademik) dan preseptor klinik Rumah Sakit.', 'satuan' => 'Sesi Koordinasi', 'menit' => 180, 'vol' => 60],
                    ['urutan' => 3, 'tugas' => 'Melakukan supervisi mutu pelaksanaan stase keperawatan medikal bedah, anak, maternitas, jiwa, dan komunitas.', 'satuan' => 'Laporan Supervisi', 'menit' => 240, 'vol' => 40],
                    ['urutan' => 4, 'tugas' => 'Mengevaluasi kesiapan mahasiswa dalam menghadapi Uji Kompetensi Ners Indonesia (UKNI).', 'satuan' => 'Evaluasi UKNI', 'menit' => 180, 'vol' => 35],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-SJK-001',
                'nama_jabatan' => 'Sekretaris Jurusan Klinik dan Komunitas',
                'unit_id' => $uKlinik?->id,
                'kelas' => 10,
                'kelompok' => 'Dosen Tugas Tambahan',
                'ikhtisar' => 'Membantu Ketua Jurusan Klinik dan Komunitas dalam administrasi wahana praktik klinik, jadwal rotasi dinas mahasiswa, dan surat pengantar RS.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyiapkan berkas administrasi izin praktik klinik, MoU wahana praktik, dan surat penugasan preseptor.', 'satuan' => 'Berkas Izin', 'menit' => 90, 'vol' => 120],
                    ['urutan' => 2, 'tugas' => 'Merekapitulasi nilai stase kepaniteraan klinik dan logbook asuhan keperawatan mahasiswa.', 'satuan' => 'Rekap Nilai', 'menit' => 60, 'vol' => 200],
                    ['urutan' => 3, 'tugas' => 'Menyusun notulen rapat berkala jurusan klinik bersama komite keperawatan RS jejaring.', 'satuan' => 'Notulen Rapat', 'menit' => 120, 'vol' => 40],
                    ['urutan' => 4, 'tugas' => 'Menyusun draf laporan kinerja tahunan jurusan klinik dan komunitas.', 'satuan' => 'Laporan Tahunan', 'menit' => 300, 'vol' => 25],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-SPM-001',
                'nama_jabatan' => 'Kepala SPMF / GPM',
                'unit_id' => $uSPMF?->id,
                'kelas' => 11,
                'kelompok' => 'Penjaminan Mutu',
                'ikhtisar' => 'Mengkoordinasikan perencanaan, pelaksanaan, evaluasi, pengendalian, dan peningkatan (PPEPP) Sistem Penjaminan Mutu Internal (SPMI) di tingkat fakultas.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyusun dan memutakhirkan dokumen Kebijakan, Manual, Standar, dan Formulir SPMI Fakultas Keperawatan.', 'satuan' => 'Dokumen SPMI', 'menit' => 480, 'vol' => 25],
                    ['urutan' => 2, 'tugas' => 'Melaksanakan Audit Mutu Internal (AMI) berkala terhadap seluruh program studi dan unit kerja fakultas.', 'satuan' => 'Sesi Audit AMI', 'menit' => 240, 'vol' => 50],
                    ['urutan' => 3, 'tugas' => 'Mengoordinasikan Rapat Tinjauan Manajemen (RTM) tindak lanjut temuan audit mutu bersama pimpinan.', 'satuan' => 'Dokumen RTM', 'menit' => 180, 'vol' => 30],
                    ['urutan' => 4, 'tugas' => 'Mendampingi persiapan visitasi akreditasi nasional LAM-PTKes dan internasional.', 'satuan' => 'Pendampingan', 'menit' => 360, 'vol' => 30],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-GPM-001',
                'nama_jabatan' => 'Ketua GPM S1 Keperawatan',
                'unit_id' => $uProdiS1?->id,
                'kelas' => 10,
                'kelompok' => 'Penjaminan Mutu',
                'ikhtisar' => 'Mengkoordinasikan gugus penjaminan mutu pada tingkat program studi untuk memastikan ketercapaian CPL dan kesesuaian perkuliahan dengan standar mutu.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Melakukan survei kepuasan mahasiswa, dosen, tenaga kependidikan, dan alumni tingkat prodi.', 'satuan' => 'Laporan Survei', 'menit' => 180, 'vol' => 40],
                    ['urutan' => 2, 'tugas' => 'Melakukan audit kesesuaian RPS, kontrak kuliah, dan soal ujian (UTS/UAS) dosen pengampu.', 'satuan' => 'Audit RPS', 'menit' => 60, 'vol' => 200],
                    ['urutan' => 3, 'tugas' => 'Menyusun laporan hasil monitoring dan evaluasi proses pembelajaran semesteran.', 'satuan' => 'Laporan Monev', 'menit' => 240, 'vol' => 30],
                    ['urutan' => 4, 'tugas' => 'Menyiapkan data kinerja mutu untuk Laporan Kinerja Program Studi (LKPS).', 'satuan' => 'Data LKPS', 'menit' => 300, 'vol' => 25],
                ]
            ],

            // Kategori 4: Jabatan Fungsional & Unit Teknis Khusus
            [
                'kode_anjab' => 'ANJAB-KPL-001',
                'nama_jabatan' => 'Kepala UPT Laboratorium Keperawatan',
                'unit_id' => $uLab?->id,
                'kelas' => 10,
                'kelompok' => 'Dosen Tugas Tambahan',
                'ikhtisar' => 'Mengkoordinasikan operasional, keselamatan kerja (K3), jadwal praktikum, dan pemeliharaan alat pada 9 ruang laboratorium keperawatan.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyusun jadwal penggunaan 9 laboratorium keperawatan untuk praktikum mahasiswa S1, S2, S3, dan Ners.', 'satuan' => 'Jadwal Lab', 'menit' => 180, 'vol' => 40],
                    ['urutan' => 2, 'tugas' => 'Mengoordinasikan tugas tenaga Pranata Laboratorium Pendidikan (PLP/Laboran) dan teknisi alat medik.', 'satuan' => 'Supervisi PLP', 'menit' => 60, 'vol' => 150],
                    ['urutan' => 3, 'tugas' => 'Menyusun usulan pengadaan manikin, alat simulasi canggih, dan bahan habis pakai medis.', 'satuan' => 'Proposal Pengadaan', 'menit' => 360, 'vol' => 20],
                    ['urutan' => 4, 'tugas' => 'Mengawasi penerapan standar keselamatan kerja biohazard dan pembuangan limbah medis medis infeksius.', 'satuan' => 'Laporan K3', 'menit' => 120, 'vol' => 50],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-ETK-001',
                'nama_jabatan' => 'Ketua Komite Etik',
                'unit_id' => $uFungsional?->id,
                'kelas' => 10,
                'kelompok' => 'Dosen Tugas Tambahan',
                'ikhtisar' => 'Memimpin penelaahan kelayakan etik penelitian kesehatan/keperawatan (ethical clearance) bagi peneliti dosen, mahasiswa, dan peneliti luar.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menetapkan reviewer penelaah protokol etik penelitian klinis dan komunitas.', 'satuan' => 'Penugasan Reviewer', 'menit' => 60, 'vol' => 150],
                    ['urutan' => 2, 'tugas' => 'Memimpin sidang pleno penelaahan etik penelitian kesehatan yang melibatkan subjek manusia.', 'satuan' => 'Sidang Pleno', 'menit' => 180, 'vol' => 48],
                    ['urutan' => 3, 'tugas' => 'Menandatangani sertifikat kelayakan etik (ethical approval) bagi proposal yang memenuhi syarat.', 'satuan' => 'Sertifikat Etik', 'menit' => 30, 'vol' => 300],
                    ['urutan' => 4, 'tugas' => 'Menyusun laporan berkala penelaahan etik ke Komite Etik Penelitian Kesehatan Nasional (KEPKN).', 'satuan' => 'Laporan KEPKN', 'menit' => 240, 'vol' => 25],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-CBT-001',
                'nama_jabatan' => 'Pengelola CBT (Computer Based Test)',
                'unit_id' => $uFungsional?->id,
                'kelas' => 9,
                'kelompok' => 'Unit Fungsional Khusus',
                'ikhtisar' => 'Mengelola kesiapan server, workstation, jaringan LAN, dan penyelenggaraan ujian berbasis komputer (CBT) fakultas maupun ujian nasional UKNI.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Melakukan uji fungsi (gladi resik) server CBT, sinkronisasi token ujian, dan jaringan lokal komputer.', 'satuan' => 'Sesi Uji Fungsi', 'menit' => 180, 'vol' => 45],
                    ['urutan' => 2, 'tugas' => 'Mengawasi teknisi IT dan kelancaran pelaksanaan ujian CBT per semester serta try-out UKNI.', 'satuan' => 'Sesi Ujian CBT', 'menit' => 240, 'vol' => 60],
                    ['urutan' => 3, 'tugas' => 'Melakukan pemeliharaan berkala perangkat lunak CBT, keamanan data soal, dan backup database ujian.', 'satuan' => 'Backup Sistem', 'menit' => 120, 'vol' => 50],
                    ['urutan' => 4, 'tugas' => 'Menyusun berita acara pelaksanaan ujian CBT dan laporan evaluasi gangguan teknis.', 'satuan' => 'Berita Acara', 'menit' => 60, 'vol' => 80],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-KSM-001',
                'nama_jabatan' => 'Pengelola Kerjasama & Kemitraan',
                'unit_id' => $uFungsional?->id,
                'kelas' => 9,
                'kelompok' => 'Unit Fungsional Khusus',
                'ikhtisar' => 'Mengelola inisiasi naskah kerjasama (MoU/MoA), kemitraan rumah sakit, pertukaran mahasiswa/dosen, serta pelayanan humas dan PPID fakultas.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyusun draf naskah nota kesepahaman (MoU) dan perjanjian kerjasama (MoA) dengan mitra institusi.', 'satuan' => 'Draf Naskah Kerjasama', 'menit' => 240, 'vol' => 40],
                    ['urutan' => 2, 'tugas' => 'Melakukan monitoring implementasi kegiatan kerjasama (Implementation Arrangement/IA).', 'satuan' => 'Laporan IA', 'menit' => 180, 'vol' => 45],
                    ['urutan' => 3, 'tugas' => 'Memfasilitasi peliputan rilis berita kegiatan fakultas, publikasi media sosial, dan portal PPID.', 'satuan' => 'Rilis Berita', 'menit' => 90, 'vol' => 120],
                    ['urutan' => 4, 'tugas' => 'Menyusun laporan capaian IKU-6 (kemitraan program studi dengan mitra bereputasi).', 'satuan' => 'Laporan IKU', 'menit' => 300, 'vol' => 25],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-P3M-001',
                'nama_jabatan' => 'Pengelola Penelitian & Pengabmasy',
                'unit_id' => $uFungsional?->id,
                'kelas' => 9,
                'kelompok' => 'Unit Fungsional Khusus',
                'ikhtisar' => 'Mengelola usulan proposal riset dan pengabdian masyarakat (P3M) dosen, verifikasi laporan kemajuan, seminar hasil, dan output jurnal ilmiah.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyelenggarakan penerimaan dan seleksi administrasi proposal penelitian/pengabdian dana DIPA/BOPTN.', 'satuan' => 'Berkas Proposal', 'menit' => 60, 'vol' => 180],
                    ['urutan' => 2, 'tugas' => 'Mengoordinasikan reviewer proposal dan seminar hasil penelitian dosen.', 'satuan' => 'Sesi Seminar', 'menit' => 180, 'vol' => 40],
                    ['urutan' => 3, 'tugas' => 'Merekapitulasi capaian publikasi artikel ilmiah dosen di jurnal Scopus, Sinta, dan HKI/paten.', 'satuan' => 'Rekap Luaran', 'menit' => 90, 'vol' => 80],
                    ['urutan' => 4, 'tugas' => 'Menyusun laporan tahunan kinerja penelitian dan pengabdian masyarakat Fakultas Keperawatan.', 'satuan' => 'Laporan P3M', 'menit' => 360, 'vol' => 20],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-NDU-001',
                'nama_jabatan' => 'Koordinator NEDU (Nursing Education Dev. Unit)',
                'unit_id' => $uFungsional?->id,
                'kelas' => 9,
                'kelompok' => 'Unit Fungsional Khusus',
                'ikhtisar' => 'Mengembangkan metode inovasi pembelajaran keperawatan, pelatihan preseptor/mentor klinik, bank soal CBT, dan kurikulum OBE.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Menyelenggarakan workshop penulisan dan telaah soal uji kompetensi ners (item development & item review).', 'satuan' => 'Sesi Workshop', 'menit' => 300, 'vol' => 25],
                    ['urutan' => 2, 'tugas' => 'Memfasilitasi pelatihan clinical preceptor bagi perawat Rumah Sakit Pendidikan.', 'satuan' => 'Kegiatan Pelatihan', 'menit' => 360, 'vol' => 20],
                    ['urutan' => 3, 'tugas' => 'Mengembangkan modul inovasi pembelajaran digital (e-learning/video asuhan keperawatan).', 'satuan' => 'Modul Digital', 'menit' => 240, 'vol' => 40],
                    ['urutan' => 4, 'tugas' => 'Menyusun evaluasi mutu pembelajaran klinik dan kesiapan kelulusan first-taker UKNI.', 'satuan' => 'Laporan NEDU', 'menit' => 180, 'vol' => 30],
                ]
            ],
            [
                'kode_anjab' => 'ANJAB-BKS-001',
                'nama_jabatan' => 'Pengelola Bimbingan Konseling',
                'unit_id' => $uFungsional?->id,
                'kelas' => 9,
                'kelompok' => 'Unit Fungsional Khusus',
                'ikhtisar' => 'Menyelenggarakan layanan pendampingan kesehatan mental, konseling akademik, konseling psikologis, dan penanganan hambatan studi mahasiswa.',
                'tugas' => [
                    ['urutan' => 1, 'tugas' => 'Melaksanakan sesi konseling tatap muka/daring bagi mahasiswa yang mengalami masalah akademik/pribadi.', 'satuan' => 'Sesi Konseling', 'menit' => 90, 'vol' => 150],
                    ['urutan' => 2, 'tugas' => 'Melakukan skrining awal kesehatan jiwa dan stres akademik mahasiswa baru dan mahasiswa stase klinik.', 'satuan' => 'Berkas Skrining', 'menit' => 45, 'vol' => 300],
                    ['urutan' => 3, 'tugas' => 'Memberikan rekomendasi akademik kepada Dekanat/Dosen PA terkait mahasiswa dengan perhatian khusus.', 'satuan' => 'Surat Rekomendasi', 'menit' => 60, 'vol' => 60],
                    ['urutan' => 4, 'tugas' => 'Menyusun laporan berkala layanan bimbingan konseling dan pencegahan kekerasan di kampus.', 'satuan' => 'Laporan Konseling', 'menit' => 180, 'vol' => 25],
                ]
            ],
        ];

        // 4. Proses Insert / Ensure AnalisisJabatan dan AbkUraianTugas
        foreach ($masterPositions as $item) {
            $jab = Jabatan::where('nama_jabatan', $item['nama_jabatan'])->first();
            if (!$jab) {
                $jab = Jabatan::create([
                    'nama_jabatan'     => $item['nama_jabatan'],
                    'kode_jabatan'     => str_replace('ANJAB-', 'JAB-', $item['kode_anjab']),
                    'unit_kerja_id'    => $item['unit_id'],
                    'kelas_jabatan'    => $item['kelas'],
                    'kelompok_jabatan' => $item['kelompok'],
                    'ikhtisar_jabatan' => $item['ikhtisar'],
                ]);
            } else {
                $jab->update([
                    'unit_kerja_id'    => $item['unit_id'],
                    'kelas_jabatan'    => $item['kelas'],
                    'kelompok_jabatan' => $item['kelompok'],
                ]);
            }

            $anjab = AnalisisJabatan::where('kode_anjab', $item['kode_anjab'])->orWhere('jabatan_id', $jab->id)->first();
            if (!$anjab) {
                $anjab = AnalisisJabatan::create([
                    'jabatan_id'              => $jab->id,
                    'unit_kerja_id'           => $item['unit_id'],
                    'kode_anjab'              => $item['kode_anjab'],
                    'ikhtisar_jabatan'        => $item['ikhtisar'],
                    'kualifikasi_pendidikan'  => 'S-1 / Profesi / S-2 / S-3 sesuai rumpun keahlian formasi',
                    'kualifikasi_pelatihan'   => 'Pekerti/AA, Kepemimpinan, Manajemen Mutu, Service Excellence',
                    'kualifikasi_pengalaman'  => 'Pengalaman di bidang tugas terkait minimal 2 tahun',
                    'bahan_kerja'             => "1. Dokumen Rencana Kerja Fakultas\n2. SOP & Pedoman Operasional Baku\n3. Data Pelayanan Akademik / Administrasi",
                    'perangkat_kerja'         => "1. Komputer / Laptop & Akses SIMPEG / SIAKAD\n2. Meja Kerja Representatif & Jaringan Internet",
                    'tanggung_jawab'          => "1. Ketercapaian target kinerja tugas pokok\n2. Kualitas layanan publik dan akuntabilitas kerja",
                    'wewenang'                => "1. Mengusulkan program kerja dan rekomendasi perbaikan\n2. Melakukan koordinasi teknis penugasan",
                    'korelasi_jabatan'        => "1. Dekan & Para Wakil Dekan: Pimpinan unit kerja\n2. Seluruh Sivitas Akademika FKP UNRI",
                    'kondisi_lingkungan'      => "Ruang kerja kantor ber-AC representatif",
                    'resiko_bahaya'           => "Kelelahan psikologis dan ketegangan kerja",
                    'syarat_keterampilan'     => "Kepemimpinan, komunikasi saintifik, manajemen perkantoran",
                    'syarat_bakat'            => "G, V, Q",
                    'syarat_temperamen'       => "D, M, T",
                    'syarat_minat'            => "Sosial, Enterprising, Investigatif",
                    'syarat_upaya_fisik'      => "Duduk, berbicara, melihat layar monitor",
                    'kondisi_fisik'           => "Sehat jasmani dan rohani",
                    'prestasi_diharapkan'     => "Target kinerja tercapai 100% tepat waktu",
                    'kelas_jabatan'           => $item['kelas'],
                    'status'                  => 'disetujui',
                ]);
            }

            if ($anjab && AbkUraianTugas::where('analisis_jabatan_id', $anjab->id)->count() === 0) {
                foreach ($item['tugas'] as $t) {
                    $waktuBeban = $t['menit'] * $t['vol'];
                    AbkUraianTugas::create([
                        'analisis_jabatan_id' => $anjab->id,
                        'urutan'              => $t['urutan'],
                        'uraian_tugas'        => $t['tugas'],
                        'satuan_hasil'        => $t['satuan'],
                        'norma_waktu_menit'   => $t['menit'],
                        'volume_1_tahun'      => $t['vol'],
                        'waktu_beban_menit'   => $waktuBeban,
                        'kebutuhan_pegawai'   => $waktuBeban / 75000,
                    ]);
                }
            }
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
