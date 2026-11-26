<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\KuotaCutiPegawai;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Seed Data Transaksi Riil & Sinkronisasi Kuota Cuti ASN
     * Mengacu ke Peraturan BKN Nomor 7 Tahun 2022
     */
    public function up(): void
    {
        DB::transaction(function () {
            // 1. Bersihkan transaksi cuti dummy/uji coba pada Bakhtiar
            $bakhtiar = Pegawai::where('nip', '197507122005011002')->first();
            if ($bakhtiar) {
                PengajuanCuti::where('pegawai_id', $bakhtiar->id)
                    ->where('alasan', 'like', '%Uji Coba%')
                    ->delete();

                // Pastikan Kuota Cuti Bakhtiar utuh 12 hari
                KuotaCutiPegawai::updateOrCreate(
                    ['pegawai_id' => $bakhtiar->id, 'tahun' => 2026],
                    ['kuota_awal' => 12, 'kuota_terpakai' => 0, 'sisa_kuota' => 12, 'catatan_penyesuaian' => null]
                );
            }

            // 2. Daftar Transaksi Cuti Resmi Sesuai Rekapitulasi Kepegawaian (Peraturan BKN No. 7 Tahun 2022)
            $transaksiList = [
                // Zurriati Yusni
                [
                    'nip'             => '197112232014092001',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-09-10',
                    'tanggal_selesai' => '2026-09-17',
                    'jumlah_hari'     => 6,
                    'alasan'          => 'Keperluan Pribadi dan Keluarga Sesuai Rekap BKN No. 7/2022',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-09-10 08:30:00',
                ],
                // Dolli Vita Zenitha
                [
                    'nip'             => '197605212014092001',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-05-04',
                    'tanggal_selesai' => '2026-05-20',
                    'jumlah_hari'     => 12,
                    'alasan'          => 'Keperluan Pribadi dan Keluarga Sesuai Rekap BKN No. 7/2022',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-05-04 09:00:00',
                ],
                // Eka Febriyanti (1 Sept & 13 Nov)
                [
                    'nip'             => '198302172005012001',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-09-01',
                    'tanggal_selesai' => '2026-09-04',
                    'jumlah_hari'     => 4,
                    'alasan'          => 'Cuti Tahunan Tahap 1 Sesuai Rekap BKN No. 7/2022',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-09-01 08:15:00',
                ],
                [
                    'nip'             => '198302172005012001',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-11-13',
                    'tanggal_selesai' => '2026-11-17',
                    'jumlah_hari'     => 3,
                    'alasan'          => 'Cuti Tahunan Tahap 2 Sesuai Rekap BKN No. 7/2022',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-11-13 10:00:00',
                ],
                // Ns. Putra Mulia (15 Juni)
                [
                    'nip'             => '198706222010121003',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-06-15',
                    'tanggal_selesai' => '2026-06-16',
                    'jumlah_hari'     => 2,
                    'alasan'          => 'Keperluan Keluarga Sesuai Rekap BKN No. 7/2022',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-06-15 08:45:00',
                ],
                // Ns. Wandha Paramitha (13 Mei 2 hk, 25 Juni 5 hk)
                [
                    'nip'             => '198702132010122005',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-05-13',
                    'tanggal_selesai' => '2026-05-14',
                    'jumlah_hari'     => 2,
                    'alasan'          => 'Cuti Tahunan Tahap 1 (2 Hari Kerja)',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-05-13 09:10:00',
                ],
                [
                    'nip'             => '198702132010122005',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-06-25',
                    'tanggal_selesai' => '2026-07-01',
                    'jumlah_hari'     => 5,
                    'alasan'          => 'Cuti Tahunan Tahap 2 (5 Hari Kerja)',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-06-25 08:30:00',
                ],
                // Ns. Trivini Valencia (19 Juli, 14 Okt)
                [
                    'nip'             => '198704212010122007',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-07-19',
                    'tanggal_selesai' => '2026-07-21',
                    'jumlah_hari'     => 2,
                    'alasan'          => 'Cuti Tahunan Tahap 1',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-07-19 09:00:00',
                ],
                [
                    'nip'             => '198704212010122007',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-10-14',
                    'tanggal_selesai' => '2026-10-16',
                    'jumlah_hari'     => 3,
                    'alasan'          => 'Cuti Tahunan Tahap 2',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-10-14 08:20:00',
                ],
                // Djunizar (25-Sep)
                [
                    'nip'             => '197106142007011002',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-09-25',
                    'tanggal_selesai' => '2026-10-02',
                    'jumlah_hari'     => 6,
                    'alasan'          => 'Keperluan Pribadi Sesuai Rekap BKN No. 7/2022',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-09-25 09:15:00',
                ],
                // Yandriwita (4 Agus, 12 Okt)
                [
                    'nip'             => '197401241995032001',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-08-04',
                    'tanggal_selesai' => '2026-08-11',
                    'jumlah_hari'     => 6,
                    'alasan'          => 'Cuti Tahunan Tahap 1',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-08-04 08:30:00',
                ],
                [
                    'nip'             => '197401241995032001',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-10-12',
                    'tanggal_selesai' => '2026-10-19',
                    'jumlah_hari'     => 6,
                    'alasan'          => 'Cuti Tahunan Tahap 2',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-10-12 09:40:00',
                ],
                // Rahayu Utami (Cuti Melahirkan 3 Bulan & Cuti Tahunan 7 hari)
                [
                    'nip'             => '199101272025212049',
                    'jenis_cuti'      => 'Cuti Melahirkan',
                    'nomor_surat'     => '800.1.11.3/UN19.5.1.1.8/KP/2025',
                    'tanggal_mulai'   => '2025-12-23',
                    'tanggal_selesai' => '2026-03-23',
                    'jumlah_hari'     => 90,
                    'alasan'          => 'Cuti Melahirkan (3 Bulan) Sesuai Peraturan BKN No. 7 Tahun 2022 Pasal 18',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2025-12-23 09:00:00',
                ],
                [
                    'nip'             => '199101272025212049',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-11-02',
                    'tanggal_selesai' => '2026-11-10',
                    'jumlah_hari'     => 7,
                    'alasan'          => 'Cuti Tahunan Sesuai Rekap BKN No. 7/2022',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-11-02 08:30:00',
                ],
                // Ns. Rosnia Safitri (6 Okt)
                [
                    'nip'             => '199304042025212074',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'nomor_surat'     => '800.1.11.1/UN19.5.1.1.8/KP/2026',
                    'tanggal_mulai'   => '2026-10-06',
                    'tanggal_selesai' => '2026-10-08',
                    'jumlah_hari'     => 3,
                    'alasan'          => 'Keperluan Pribadi Sesuai Rekap BKN No. 7/2022',
                    'status'          => 'Disetujui',
                    'tgl_pengajuan'   => '2026-10-06 09:00:00',
                ],
            ];

            foreach ($transaksiList as $t) {
                $pegawai = Pegawai::where('nip', $t['nip'])->first();
                if (!$pegawai) continue;

                // Cek apakah transaksi sudah ada
                $exists = PengajuanCuti::where('pegawai_id', $pegawai->id)
                    ->where('jenis_cuti', $t['jenis_cuti'])
                    ->where('tanggal_mulai', $t['tanggal_mulai'])
                    ->first();

                if (!$exists) {
                    PengajuanCuti::create([
                        'pegawai_id'         => $pegawai->id,
                        'jenis_cuti'         => $t['jenis_cuti'],
                        'nomor_surat'        => $t['nomor_surat'],
                        'tanggal_mulai'      => $t['tanggal_mulai'],
                        'tanggal_selesai'    => $t['tanggal_selesai'],
                        'jumlah_hari'        => $t['jumlah_hari'],
                        'alasan'             => $t['alasan'],
                        'status'             => $t['status'],
                        'metode_ttd'         => 'elektronik',
                        'pertimbangan_atasan'=> 'Disetujui',
                        'created_at'         => $t['tgl_pengajuan'],
                        'updated_at'         => $t['tgl_pengajuan'],
                    ]);
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
