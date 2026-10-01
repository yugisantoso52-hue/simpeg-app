<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modifikasi tabel unit_kerja untuk hierarki struktur organisasi (Parent-Child)
        Schema::table('unit_kerja', function (Blueprint $table) {
            if (!Schema::hasColumn('unit_kerja', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('unit_kerja')->nullOnDelete();
            }
            if (!Schema::hasColumn('unit_kerja', 'tipe_unit')) {
                $table->string('tipe_unit', 50)->nullable()->after('nama_unit');
            }
            if (!Schema::hasColumn('unit_kerja', 'urutan')) {
                $table->integer('urutan')->default(0)->after('tipe_unit');
            }
        });

        // 2. Modifikasi tabel jabatan untuk penunjang Anjab & Evaluasi Jabatan (Kelas Jabatan)
        Schema::table('jabatan', function (Blueprint $table) {
            if (!Schema::hasColumn('jabatan', 'unit_kerja_id')) {
                $table->foreignId('unit_kerja_id')->nullable()->after('id')->constrained('unit_kerja')->nullOnDelete();
            }
            if (!Schema::hasColumn('jabatan', 'kelas_jabatan')) {
                $table->unsignedTinyInteger('kelas_jabatan')->nullable()->after('nama_jabatan');
            }
            if (!Schema::hasColumn('jabatan', 'kelompok_jabatan')) {
                $table->string('kelompok_jabatan', 50)->nullable()->after('kelas_jabatan');
            }
            if (!Schema::hasColumn('jabatan', 'ikhtisar_jabatan')) {
                $table->text('ikhtisar_jabatan')->nullable()->after('kelompok_jabatan');
            }
        });

        // 3. Buat tabel analisis_jabatan (Standar PermenPAN-RB No. 1 Tahun 2020)
        if (!Schema::hasTable('analisis_jabatan')) {
            Schema::create('analisis_jabatan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jabatan_id')->constrained('jabatan')->cascadeOnDelete();
                $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
                $table->string('kode_anjab', 50)->nullable();
                $table->text('ikhtisar_jabatan')->nullable();

                // Kualifikasi Jabatan
                $table->text('kualifikasi_pendidikan')->nullable();
                $table->text('kualifikasi_pelatihan')->nullable();
                $table->text('kualifikasi_pengalaman')->nullable();

                // Bahan & Perangkat Kerja (JSON / List Text)
                $table->longText('bahan_kerja')->nullable();
                $table->longText('perangkat_kerja')->nullable();

                // Tanggung Jawab & Wewenang
                $table->longText('tanggung_jawab')->nullable();
                $table->longText('wewenang')->nullable();

                // Korelasi Jabatan & Lingkungan
                $table->longText('korelasi_jabatan')->nullable();
                $table->longText('kondisi_lingkungan')->nullable();
                $table->longText('resiko_bahaya')->nullable();

                // Syarat Jabatan (Bakat, Temperamen, Minat, Upaya Fisik)
                $table->text('syarat_keterampilan')->nullable();
                $table->text('syarat_bakat')->nullable();
                $table->text('syarat_temperamen')->nullable();
                $table->text('syarat_minat')->nullable();
                $table->text('syarat_upaya_fisik')->nullable();
                $table->text('kondisi_fisik')->nullable();

                // Prestasi Kerja & Evaluasi Jabatan
                $table->text('prestasi_diharapkan')->nullable();
                $table->unsignedTinyInteger('kelas_jabatan')->nullable();
                $table->enum('status', ['draft', 'disetujui'])->default('draft');
                $table->timestamps();
            });
        }

        // 4. Buat tabel abk_uraian_tugas (Perhitungan Beban Kerja Per Butir Tugas)
        if (!Schema::hasTable('abk_uraian_tugas')) {
            Schema::create('abk_uraian_tugas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('analisis_jabatan_id')->constrained('analisis_jabatan')->cascadeOnDelete();
                $table->integer('urutan')->default(1);
                $table->text('uraian_tugas');
                $table->string('satuan_hasil', 100)->default('Kegiatan');
                $table->decimal('norma_waktu_menit', 10, 2)->default(60.00); // Waktu penyelesaian 1 output
                $table->decimal('volume_1_tahun', 10, 2)->default(1.00);      // Target beban tahunan
                $table->decimal('waktu_beban_menit', 14, 2)->default(60.00);  // norma_waktu * volume
                $table->decimal('kebutuhan_pegawai', 8, 4)->default(0.0008);  // waktu_beban / 75.000 menit
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 5. Seed Relasi Parent-Child Unit Kerja sesuai Struktur Organisasi FKP UNRI
        $this->seedUnitKerjaHierarchy();

        // 6. Seed Klasifikasi dan Data Awal Anjab & ABK Standar
        $this->seedInitialAnjabAbk();
    }

    /**
     * Memetakan struktur bagan organisasi induk-anak (Parent-Child)
     * berdasarkan gambar resmi Struktur Organisasi Fakultas Keperawatan UNRI
     */
    private function seedUnitKerjaHierarchy(): void
    {
        $uDekanat   = DB::table('unit_kerja')->where('kode_unit', 'UK-001')->first();
        $uSenat     = DB::table('unit_kerja')->where('kode_unit', 'UK-002')->first();
        $uSpmf      = DB::table('unit_kerja')->where('kode_unit', 'UK-003')->first();
        $uJurusan   = DB::table('unit_kerja')->where('kode_unit', 'UK-004')->first();
        $uPreklinik = DB::table('unit_kerja')->where('kode_unit', 'UK-005')->first();
        $uKlinik    = DB::table('unit_kerja')->where('kode_unit', 'UK-006')->first();
        $uProdiS2   = DB::table('unit_kerja')->where('kode_unit', 'UK-007')->first();
        $uProdiNers = DB::table('unit_kerja')->where('kode_unit', 'UK-008')->first();
        $uProdiS1   = DB::table('unit_kerja')->where('kode_unit', 'UK-009')->first();
        $uBagUmum   = DB::table('unit_kerja')->where('kode_unit', 'UK-010')->first();
        $uPokjaAkad = DB::table('unit_kerja')->where('kode_unit', 'UK-011')->first();
        $uPokjaKeu  = DB::table('unit_kerja')->where('kode_unit', 'UK-012')->first();
        $uPokjaUmum = DB::table('unit_kerja')->where('kode_unit', 'UK-013')->first();
        $uLab       = DB::table('unit_kerja')->where('kode_unit', 'UK-014')->first();
        $uFungsional= DB::table('unit_kerja')->where('kode_unit', 'UK-015')->first();

        if ($uDekanat) {
            DB::table('unit_kerja')->where('id', $uDekanat->id)->update([
                'parent_id' => null,
                'tipe_unit' => 'Pimpinan Fakultas',
                'urutan'    => 1,
            ]);

            // Senat & SPMF (Garis Penunjang & Pertimbangan Dekanat)
            if ($uSenat) {
                DB::table('unit_kerja')->where('id', $uSenat->id)->update([
                    'parent_id' => $uDekanat->id,
                    'tipe_unit' => 'Badan Pertimbangan Normatif',
                    'urutan'    => 2,
                ]);
            }
            if ($uSpmf) {
                DB::table('unit_kerja')->where('id', $uSpmf->id)->update([
                    'parent_id' => $uDekanat->id,
                    'tipe_unit' => 'Penjaminan Mutu',
                    'urutan'    => 3,
                ]);
            }

            // Jurusan Induk
            if ($uJurusan) {
                DB::table('unit_kerja')->where('id', $uJurusan->id)->update([
                    'parent_id' => $uDekanat->id,
                    'tipe_unit' => 'Jurusan Induk',
                    'urutan'    => 4,
                ]);

                // Preklinik & Klinik Komunitas di bawah Jurusan
                if ($uPreklinik) {
                    DB::table('unit_kerja')->where('id', $uPreklinik->id)->update([
                        'parent_id' => $uJurusan->id,
                        'tipe_unit' => 'Jurusan Bidang',
                        'urutan'    => 5,
                    ]);

                    // Prodi S1 & S2 di bawah Jurusan Preklinik Keperawatan
                    if ($uProdiS1) {
                        DB::table('unit_kerja')->where('id', $uProdiS1->id)->update([
                            'parent_id' => $uPreklinik->id,
                            'tipe_unit' => 'Program Studi',
                            'urutan'    => 6,
                        ]);
                    }
                    if ($uProdiS2) {
                        DB::table('unit_kerja')->where('id', $uProdiS2->id)->update([
                            'parent_id' => $uPreklinik->id,
                            'tipe_unit' => 'Program Studi',
                            'urutan'    => 7,
                        ]);
                    }
                }

                if ($uKlinik) {
                    DB::table('unit_kerja')->where('id', $uKlinik->id)->update([
                        'parent_id' => $uJurusan->id,
                        'tipe_unit' => 'Jurusan Bidang',
                        'urutan'    => 8,
                    ]);

                    // Prodi Profesi Ners di bawah Jurusan Klinik dan Komunitas
                    if ($uProdiNers) {
                        DB::table('unit_kerja')->where('id', $uProdiNers->id)->update([
                            'parent_id' => $uKlinik->id,
                            'tipe_unit' => 'Program Studi',
                            'urutan'    => 9,
                        ]);
                    }
                }
            }

            // Bagian Umum & Pokja-Pokja
            if ($uBagUmum) {
                DB::table('unit_kerja')->where('id', $uBagUmum->id)->update([
                    'parent_id' => $uDekanat->id,
                    'tipe_unit' => 'Tata Usaha / Umum',
                    'urutan'    => 10,
                ]);

                if ($uPokjaAkad) {
                    DB::table('unit_kerja')->where('id', $uPokjaAkad->id)->update([
                        'parent_id' => $uBagUmum->id,
                        'tipe_unit' => 'Kelompok Kerja (Pokja)',
                        'urutan'    => 11,
                    ]);
                }
                if ($uPokjaKeu) {
                    DB::table('unit_kerja')->where('id', $uPokjaKeu->id)->update([
                        'parent_id' => $uBagUmum->id,
                        'tipe_unit' => 'Kelompok Kerja (Pokja)',
                        'urutan'    => 12,
                    ]);
                }
                if ($uPokjaUmum) {
                    DB::table('unit_kerja')->where('id', $uPokjaUmum->id)->update([
                        'parent_id' => $uBagUmum->id,
                        'tipe_unit' => 'Kelompok Kerja (Pokja)',
                        'urutan'    => 13,
                    ]);
                }
            }

            // Laboratorium & Unit Fungsional
            if ($uLab) {
                DB::table('unit_kerja')->where('id', $uLab->id)->update([
                    'parent_id' => $uDekanat->id,
                    'tipe_unit' => 'Unit Pelaksana Teknis Lab',
                    'urutan'    => 14,
                ]);
            }
            if ($uFungsional) {
                DB::table('unit_kerja')->where('id', $uFungsional->id)->update([
                    'parent_id' => $uDekanat->id,
                    'tipe_unit' => 'Unit Fungsional Khusus',
                    'urutan'    => 15,
                ]);
            }
        }
    }

    /**
     * Menanamkan data Anjab & ABK awal untuk jabatan kunci di FKP UNRI
     */
    private function seedInitialAnjabAbk(): void
    {
        // Update kelas jabatan pada master jabatan
        $gradeMap = [
            'Dekan'                                     => [15, 'Pimpinan', 'Memimpin penyelenggaraan tridharma perguruan tinggi, pembinaan sivitas akademika, dan tata kelola fakultas.'],
            'Wakil Dekan I (Bid. Akademik)'             => [13, 'Pimpinan', 'Membantu Dekan dalam memimpin pelaksanaan pendidikan, penelitian, pengabdian masyarakat, dan penjaminan mutu.'],
            'Wakil Dekan II (Bid. Keuangan dan Umum)'   => [13, 'Pimpinan', 'Membantu Dekan dalam perencanaan anggaran, perbendaharaan, kepegawaian, ketatausahaan, dan sarana prasarana.'],
            'Wakil Dekan III (Bid. Kemahasiswaan, Alumni dan Kerjasama)' => [13, 'Pimpinan', 'Membantu Dekan dalam pembinaan kegiatan kemahasiswaan, alumni, kerjasama institusional, dan tracer study.'],
            'Ketua Senat Fakultas'                      => [12, 'Senat', 'Memimpin senat fakultas dalam penetapan kebijakan akademik, pertimbangan, dan pengawasan mutu akademik.'],
            'Kepala SPMF / GPM'                         => [11, 'Penjaminan Mutu', 'Mengkoordinasikan sistem penjaminan mutu internal fakultas (SPMF) dan gugus penjamin mutu program studi.'],
            'Ketua Jurusan (Kajur)'                     => [11, 'Dosen Tugas Tambahan', 'Memimpin jurusan dalam pengelolaan sumber daya pendidik dan kependidikan serta koordinasi program studi.'],
            'Koordinator Prodi S1 Keperawatan'          => [10, 'Dosen Tugas Tambahan', 'Mengkoordinasikan penyelenggaraan kurikulum, pembelajaran, dan akreditasi pada Program Studi Sarjana Keperawatan.'],
            'Koordinator Prodi S2 Keperawatan'          => [10, 'Dosen Tugas Tambahan', 'Mengkoordinasikan penyelenggaraan kurikulum, riset tesis, dan akreditasi pada Program Studi Magister Keperawatan.'],
            'Koordinator Prodi Ners'                    => [10, 'Dosen Tugas Tambahan', 'Mengkoordinasikan pembelajaran klinik, stase profesi di RS/Puskesmas, dan akreditasi Program Studi Profesi Ners.'],
            'Kepala Bagian Umum'                        => [11, 'Tenaga Kependidikan', 'Memimpin dan mengkoordinasikan seluruh urusan ketatausahaan, akademik, kepegawaian, keuangan, dan perlengkapan fakultas.'],
            'Ka Pokja Akademik'                         => [9, 'Tenaga Kependidikan', 'Mengkoordinasikan layanan administrasi perkuliahan, registrasi mahasiswa, ujian, yudisium, dan wisuda.'],
            'Ka Pokja Keu-Kepeg'                        => [9, 'Tenaga Kependidikan', 'Mengkoordinasikan penyusunan anggaran, pertanggungjawaban keuangan, presensi, usulan kenaikan pangkat, gaji berkala, dan cuti pegawai.'],
            'Ka Pokja Umum Sarana Akademik'             => [9, 'Tenaga Kependidikan', 'Mengkoordinasikan pengelolaan barang milik negara (BMN), sarana perkuliahan, kebersihan, keamanan, dan pemeliharaan gedung.'],
            'Kepala UPT Laboratorium Keperawatan'       => [10, 'Dosen Tugas Tambahan', 'Mengkoordinasikan operasional 9 ruang laboratorium keperawatan, keselamatan kerja (K3), dan praktikum mahasiswa.'],
            'Pranata Laboratorium Pendidikan (PLP / Laboran)' => [8, 'Tenaga Kependidikan', 'Melakukan pengelolaan alat praktikum, bahan medis habis pakai, inventarisasi, dan pendampingan praktikum keperawatan.'],
            'Guru Besar'                                => [14, 'Dosen Fungsional', 'Melaksanakan tridharma perguruan tinggi di bidang keperawatan pada jenjang tertinggi akademik dan pembinaan calon doktor.'],
            'Lektor Kepala'                             => [12, 'Dosen Fungsional', 'Melaksanakan pendidikan perkuliahan, pembimbingan skripsi/tesis, penelitian terpublikasi, dan pengabdian masyarakat.'],
            'Lektor'                                    => [10, 'Dosen Fungsional', 'Melaksanakan pengajaran, pengujian, penelitian jurnal nasional/internasional, dan pengabdian masyarakat.'],
            'Asisten Ahli'                              => [9, 'Dosen Fungsional', 'Melaksanakan pengajaran dasar keperawatan, asistensi praktikum, penelitian terpublikasi, dan pembimbingan akademik.'],
            'Staff Pokja Akademik dan Kemahasiswaan'    => [6, 'Pelaksana', 'Melaksanakan pelayanan administrasi nilai, KRS mahasiswa, surat keterangan aktif kuliah, dan kelengkapan yudisium.'],
            'Staff Pokja Keu & Kepeg'                   => [6, 'Pelaksana', 'Melaksanakan pengelolaan berkas kepegawaian, rekap presensi, verifikasi logbook harian, dan berkas pencairan anggaran.'],
            'Staff Pokja Umum & Perlengkapan'           => [6, 'Pelaksana', 'Melaksanakan pencatatan BMN, pemeliharaan ruangan kelas, inventarisasi sarana, dan kebersihan lingkungan.'],
        ];

        foreach ($gradeMap as $namaJabatan => $info) {
            DB::table('jabatan')->where('nama_jabatan', $namaJabatan)->update([
                'kelas_jabatan'    => $info[0],
                'kelompok_jabatan' => $info[1],
                'ikhtisar_jabatan' => $info[2],
            ]);
        }

        // Tautkan Jabatan PLP dan Laboran ke Unit Kerja Laboratorium Keperawatan
        $labUnit = DB::table('unit_kerja')->where('kode_unit', 'UK-014')->first();
        if ($labUnit) {
            DB::table('jabatan')->where('nama_jabatan', 'like', '%Laboratorium%')->orWhere('nama_jabatan', 'like', '%PLP%')->update([
                'unit_kerja_id' => $labUnit->id,
            ]);
        }

        // Tautkan Jabatan Pokja ke Unit Kerja Masing-Masing
        $pokjaAkadUnit = DB::table('unit_kerja')->where('kode_unit', 'UK-011')->first();
        if ($pokjaAkadUnit) {
            DB::table('jabatan')->where('nama_jabatan', 'like', '%Pokja Akademik%')->update(['unit_kerja_id' => $pokjaAkadUnit->id]);
        }
        $pokjaKeuUnit = DB::table('unit_kerja')->where('kode_unit', 'UK-012')->first();
        if ($pokjaKeuUnit) {
            DB::table('jabatan')->where('nama_jabatan', 'like', '%Pokja Keu%')->update(['unit_kerja_id' => $pokjaKeuUnit->id]);
        }
        $pokjaUmumUnit = DB::table('unit_kerja')->where('kode_unit', 'UK-013')->first();
        if ($pokjaUmumUnit) {
            DB::table('jabatan')->where('nama_jabatan', 'like', '%Pokja Umum%')->update(['unit_kerja_id' => $pokjaUmumUnit->id]);
        }

        // Contoh Data Anjab & ABK Standar untuk: Pranata Laboratorium Pendidikan (PLP)
        $plpJabatan = DB::table('jabatan')->where('nama_jabatan', 'like', '%Pranata Laboratorium%')->first();
        if ($plpJabatan) {
            $anjabId = DB::table('analisis_jabatan')->insertGetId([
                'jabatan_id'              => $plpJabatan->id,
                'unit_kerja_id'           => $labUnit?->id,
                'kode_anjab'              => 'ANJAB-PLP-001',
                'ikhtisar_jabatan'        => 'Mengelola laboratorium keperawatan, menyiapkan peralatan dan bahan medis praktikum, mendampingi instruktur/dosen praktikum klinik, serta melakukan kalibrasi dan pemeliharaan alat.',
                'kualifikasi_pendidikan'  => 'D-III / D-IV / S-1 Terapan Keperawatan, Analis Medis, atau Teknik Elektromedik',
                'kualifikasi_pelatihan'   => 'Pelatihan K3 Laboratorium Medis, Manajemen Pengelolaan Laboratorium, Kalibrasi Alat Kesehatan',
                'kualifikasi_pengalaman'  => 'Minimal 1 tahun bekerja di fasilitas laboratorium kesehatan/keperawatan',
                'bahan_kerja'             => "1. Panduan Praktikum Mahasiswa\n2. Jadwal Praktikum Semester\n3. SOP Penggunaan Ruang & Alat Lab\n4. Formulir Peminjaman & Pemakaian Alat",
                'perangkat_kerja'         => "1. Phantom Pasien & Manikin Keperawatan (Resusitasi, Injeksi, Kateter, dsb)\n2. Alat Medis (Tensimeter, EKG, Suction, Nebulizer, Bed Pasien)\n3. Komputer & Sistem Inventaris Lab\n4. APD (Handscoon, Masker, Jas Lab)",
                'tanggung_jawab'          => "1. Ketersediaan alat dan bahan praktikum tepat waktu\n2. Keamanan, sterilisasi, dan kebersihan 9 ruang laboratorium keperawatan\n3. Akurasi pencatatan inventaris dan kondisi alat laboratorium\n4. Penerapan standar keselamatan kerja K3 laboratorium",
                'wewenang'                => "1. Memeriksa kesiapan dan kelayakan alat sebelum digunakan praktikum\n2. Menolak peminjaman alat yang tidak sesuai SOP\n3. Mengusulkan pemusnahan bahan medis kedaluwarsa atau penggantian alat rusak",
                'korelasi_jabatan'        => "1. Kepala UPT Laboratorium: Pelaporan dan pertanggungjawaban rutin\n2. Dosen Pengampu Praktikum: Koordinasi kesiapan alat per topik perkuliahan\n3. Mahasiswa: Pendampingan teknis penggunaan alat lab",
                'kondisi_lingkungan'      => "Tempat kerja di dalam ruangan ber-AC, penerangan cukup, potensi paparan cairan/desinfektan dan limbah medis praktikum",
                'resiko_bahaya'           => "Tertusuk jarum/benda tajam medis, terkena percikan cairan disinfektan kimia",
                'syarat_keterampilan'     => "Mampu mengoperasikan manikin/phantom medis, sterilisasi alat, dan pengelolaan inventaris BMN",
                'syarat_bakat'            => "G (Intelegensi Umum), V (Bakat Verbal), N (Numerik), M (Kecekatan Tangan/Manual Dexterity)",
                'syarat_temperamen'       => "R (Kemampuan menyesuaikan diri dengan kegiatan berulang secara teliti), T (Kemampuan situasi yang menghendaki ketepatan)",
                'syarat_minat'            => "Realistik, Investigatif, Konvensional",
                'syarat_upaya_fisik'      => "Berdiri, berjalan, mengangkat manikin/peralatan, melihat detail",
                'kondisi_fisik'           => "Sehat jasmani dan rohani, tidak buta warna",
                'prestasi_diharapkan'     => "Kelancaran 100% sesi praktikum mahasiswa per semester tanpa insiden kecelakaan kerja",
                'kelas_jabatan'           => 8,
                'status'                  => 'disetujui',
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);

            // Butir-butir Tugas ABK untuk PLP
            $uraianTugasPLP = [
                [
                    'analisis_jabatan_id'    => $anjabId,
                    'urutan'                 => 1,
                    'uraian_tugas'           => 'Menyiapkan alat dan bahan praktikum keperawatan (manikin, bed, infus set, spuit, perban) sebelum jadwal praktikum dimulai.',
                    'satuan_hasil'           => 'Sesi Praktikum',
                    'norma_waktu_menit'      => 60,   // 1 jam per sesi praktikum
                    'volume_1_tahun'         => 350,  // ~350 sesi praktikum lab per tahun untuk 9 ruang lab
                    'waktu_beban_menit'      => 21000,
                    'kebutuhan_pegawai'      => 21000 / 75000, // 0.28
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabId,
                    'urutan'                 => 2,
                    'uraian_tugas'           => 'Melakukan pendampingan teknis dan pengawasan keselamatan kerja mahasiswa selama praktikum berlangsung di 9 ruang lab.',
                    'satuan_hasil'           => 'Sesi Praktikum',
                    'norma_waktu_menit'      => 90,   // 1.5 jam per sesi
                    'volume_1_tahun'         => 350,
                    'waktu_beban_menit'      => 31500,
                    'kebutuhan_pegawai'      => 31500 / 75000, // 0.42
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabId,
                    'urutan'                 => 3,
                    'uraian_tugas'           => 'Membersihkan, mensterilkan instrumen medis, dan merapikan kembali alat lab setelah praktikum selesai.',
                    'satuan_hasil'           => 'Kegiatan',
                    'norma_waktu_menit'      => 45,
                    'volume_1_tahun'         => 350,
                    'waktu_beban_menit'      => 15750,
                    'kebutuhan_pegawai'      => 15750 / 75000, // 0.21
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabId,
                    'urutan'                 => 4,
                    'uraian_tugas'           => 'Melakukan inventarisasi alat, pencatatan kartu stok bahan medis, kalibrasi alat, dan uji fungsi manikin berkala.',
                    'satuan_hasil'           => 'Laporan',
                    'norma_waktu_menit'      => 120,
                    'volume_1_tahun'         => 96, // 8 kali per bulan
                    'waktu_beban_menit'      => 11520,
                    'kebutuhan_pegawai'      => 11520 / 75000, // 0.1536
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabId,
                    'urutan'                 => 5,
                    'uraian_tugas'           => 'Menyusun laporan pemeliharaan, usulan pengadaan bahan medis habis pakai, dan berita acara kerusakan alat.',
                    'satuan_hasil'           => 'Dokumen Laporan',
                    'norma_waktu_menit'      => 180,
                    'volume_1_tahun'         => 24, // 2 kali per bulan
                    'waktu_beban_menit'      => 4320,
                    'kebutuhan_pegawai'      => 4320 / 75000, // 0.0576
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
            ];

            DB::table('abk_uraian_tugas')->insert($uraianTugasPLP);
        }

        // Contoh Data Anjab & ABK Standar untuk: Staff Pokja Keu & Kepeg
        $stafKepegJabatan = DB::table('jabatan')->where('nama_jabatan', 'Staff Pokja Keu & Kepeg')->first();
        if ($stafKepegJabatan) {
            $anjabKepegId = DB::table('analisis_jabatan')->insertGetId([
                'jabatan_id'              => $stafKepegJabatan->id,
                'unit_kerja_id'           => $pokjaKeuUnit?->id,
                'kode_anjab'              => 'ANJAB-KEPEG-001',
                'ikhtisar_jabatan'        => 'Melaksanakan pelayanan administrasi kepegawaian, verifikasi presensi harian, monitoring logbook kinerja, pemrosesan usulan kenaikan pangkat/gaji berkala, serta permohonan cuti pegawai.',
                'kualifikasi_pendidikan'  => 'D-III / D-IV / S-1 Manajemen, Administrasi Negara, Ilmu Pemerintahan, atau Komputer',
                'kualifikasi_pelatihan'   => 'Pelatihan Pengelolaan SIMPEG, Manajemen ASN, Teknis Pelayanan Kepegawaian',
                'kualifikasi_pengalaman'  => 'Minimal 1 tahun di bidang administrasi kepegawaian',
                'bahan_kerja'             => "1. Dokumen Permohonan Cuti Pegawai\n2. Rekap Data Presensi & Logbook Harian\n3. Berkas Usulan KP dan KGB\n4. Surat Keputusan (SK) dan Arsip Kepegawaian",
                'perangkat_kerja'         => "1. Komputer / Laptop & Printer\n2. Aplikasi SIMPEG & Portal BKN (SIASN)\n3. ATK & Filing Cabinet Arsip",
                'tanggung_jawab'          => "1. Keakuratan verifikasi data kehadiran dan logbook kinerja pegawai\n2. Ketepatan waktu pemrosesan usulan kenaikan pangkat & gaji berkala\n3. Keamanan dokumen dan kerahasiaan arsip kepegawaian",
                'wewenang'                => "1. Memeriksa kelengkapan berkas permohonan layanan kepegawaian\n2. Memberikan paraf pada draf surat pengantar kepegawaian\n3. Mengingatkan pegawai mengenai keterlambatan presensi atau pelaporan",
                'korelasi_jabatan'        => "1. Ka Pokja Keu-Kepeg: Atasan langsung untuk arahan dan verifikasi\n2. Seluruh Pegawai Dosen & Tendik: Pelayanan administrasi kepegawaian\n3. Biro Kepegawaian Rektorat: Koordinasi berkas usulan institusional",
                'kondisi_lingkungan'      => "Tempat kerja di dalam ruangan kantor tertutup ber-AC dengan meja kerja dan komputer",
                'resiko_bahaya'           => "Kelelahan mata akibat monitor komputer (ergonomi)",
                'syarat_keterampilan'     => "Pengoperasian aplikasi SIMPEG, Microsoft Office, kearsipan digital",
                'syarat_bakat'            => "G (Intelegensi), Q (Ketelitian Klerikal), V (Verbal)",
                'syarat_temperamen'       => "R (Rutin/Teratur), T (Tepat/Teliti)",
                'syarat_minat'            => "Konvensional, Sosial",
                'syarat_upaya_fisik'      => "Duduk dalam waktu lama, mengetik, melihat layar",
                'kondisi_fisik'           => "Sehat jasmani dan rohani",
                'prestasi_diharapkan'     => "Layanan administrasi kepegawaian tuntas 100% tanpa komplain dari pegawai",
                'kelas_jabatan'           => 6,
                'status'                  => 'disetujui',
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);

            $uraianTugasKepeg = [
                [
                    'analisis_jabatan_id'    => $anjabKepegId,
                    'urutan'                 => 1,
                    'uraian_tugas'           => 'Memeriksa dan merekap kehadiran pegawai (presensi mobile/GPS) serta memvalidasi rekap bulanan untuk pembayaran uang makan/tukin.',
                    'satuan_hasil'           => 'Laporan Bulanan',
                    'norma_waktu_menit'      => 120,
                    'volume_1_tahun'         => 240, // setiap hari kerja diperiksa
                    'waktu_beban_menit'      => 28800,
                    'kebutuhan_pegawai'      => 28800 / 75000, // 0.384
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabKepegId,
                    'urutan'                 => 2,
                    'uraian_tugas'           => 'Memverifikasi dan memproses pengajuan cuti pegawai (cuti tahunan, sakit, melahirkan, dll.) di sistem e-cuti.',
                    'satuan_hasil'           => 'Berkas Cuti',
                    'norma_waktu_menit'      => 30,
                    'volume_1_tahun'         => 300,
                    'waktu_beban_menit'      => 9000,
                    'kebutuhan_pegawai'      => 9000 / 75000, // 0.12
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabKepegId,
                    'urutan'                 => 3,
                    'uraian_tugas'           => 'Menginventarisir dan memproses berkas usulan Kenaikan Pangkat (KP) dan Kenaikan Gaji Berkala (KGB) dosen dan tendik.',
                    'satuan_hasil'           => 'Berkas Usulan',
                    'norma_waktu_menit'      => 180,
                    'volume_1_tahun'         => 80,
                    'waktu_beban_menit'      => 14400,
                    'kebutuhan_pegawai'      => 14400 / 75000, // 0.192
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabKepegId,
                    'urutan'                 => 4,
                    'uraian_tugas'           => 'Mengelola arsip kepegawaian (SK, ijazah, dokumen kepangkatan, STR/SIP, PAK) baik dalam bentuk fisik maupun digital di SIMPEG.',
                    'satuan_hasil'           => 'Dokumen Arsip',
                    'norma_waktu_menit'      => 45,
                    'volume_1_tahun'         => 400,
                    'waktu_beban_menit'      => 18000,
                    'kebutuhan_pegawai'      => 18000 / 75000, // 0.24
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
                [
                    'analisis_jabatan_id'    => $anjabKepegId,
                    'urutan'                 => 5,
                    'uraian_tugas'           => 'Menyusun laporan berkala keadaan kepegawaian (DUK, rekapitulasi pensiun, tugas belajar, dan formasi SDM).',
                    'satuan_hasil'           => 'Laporan',
                    'norma_waktu_menit'      => 240,
                    'volume_1_tahun'         => 24,
                    'waktu_beban_menit'      => 5760,
                    'kebutuhan_pegawai'      => 5760 / 75000, // 0.0768
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ],
            ];

            DB::table('abk_uraian_tugas')->insert($uraianTugasKepeg);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abk_uraian_tugas');
        Schema::dropIfExists('analisis_jabatan');

        Schema::table('jabatan', function (Blueprint $table) {
            $table->dropForeign(['unit_kerja_id']);
            $table->dropColumn(['unit_kerja_id', 'kelas_jabatan', 'kelompok_jabatan', 'ikhtisar_jabatan']);
        });

        Schema::table('unit_kerja', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'tipe_unit', 'urutan']);
        });
    }
};
