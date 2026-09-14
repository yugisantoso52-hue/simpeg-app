<?php

namespace database\seeders;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Models\Golongan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil semua ID yang tersedia dari tabel referensi
        $unitKerjaIds = UnitKerja::pluck('id')->toArray();
        $jabatanIds = Jabatan::pluck('id')->toArray();
        $golonganIds = Golongan::pluck('id')->toArray();

        // Antisipasi jika seeder utama belum dijalankan
        if (empty($unitKerjaIds) || empty($jabatanIds) || empty($golonganIds)) {
            $this->command->error('Gagal membuat seeder Pegawai: Pastikan UnitKerjaSeeder, JabatanSeeder, dan GolonganSeeder sudah dijalankan terlebih dahulu!');
            return;
        }

        // 2. Data dummy pegawai tiruan khusus SIMPEG
        $dataPegawai = [
            [
                'nip' => '198812102015031001',
                'nama' => 'Achmad Fauzi, S.Kom.',
                'nik' => '3201021012880002',
                'gelar_depan' => null,
                'gelar_belakang' => 'S.Kom.',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1988-12-10',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'pendidikan' => 'S1 Teknik Informatika',
                'email' => 'achmad.fauzi@simpeg.go.id',
                'no_hp' => '081234567890',
                'alamat' => 'Jl. Merdeka No. 45, Blok C, Jakarta Pusat',
                'npwp' => '12.345.678.9-012.000',
                'bpjs' => '0001234567890',
                'jenis_pegawai' => 'PNS',
                'status_asn' => 'ASN',
                'status_pernikahan' => 'Menikah',
                'nama_pasangan' => 'Siti Aminah',
                'jumlah_anak' => 2,
                'tanggal_masuk' => '2015-03-01',
                'tmt_sk_pertama' => '2015-03-01',
                'tmt_pangkat_terakhir' => '2022-04-01', // Terjadwal KP April 2026
                'tmt_kgb_terakhir' => '2024-03-01',     // Terjadwal KGB Maret 2026
                'status_pegawai' => 'Aktif',
                'gol_darah' => 'O',
                'tinggi_badan' => 170,
                'berat_badan' => 65,
            ],
            [
                'nip' => '199205172019082003',
                'nama' => 'drg. Riana Puspita',
                'nik' => '3201025705920005',
                'gelar_depan' => 'drg.',
                'gelar_belakang' => null,
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1992-05-17',
                'jenis_kelamin' => 'P',
                'agama' => 'Islam',
                'pendidikan' => 'S1 Profesi Dokter Gigi',
                'email' => 'riana.puspita@simpeg.go.id',
                'no_hp' => '085678901234',
                'alamat' => 'Perumahan Asri Garden No. B7, Bandung',
                'npwp' => '98.765.432.1-021.000',
                'bpjs' => '0009876543210',
                'jenis_pegawai' => 'PNS',
                'status_asn' => 'ASN',
                'status_pernikahan' => 'Belum Menikah',
                'nama_pasangan' => null,
                'jumlah_anak' => 0,
                'tanggal_masuk' => '2019-08-01',
                'tmt_sk_pertama' => '2019-08-01',
                'tmt_pangkat_terakhir' => '2023-10-01',
                'tmt_kgb_terakhir' => '2025-08-01',
                'status_pegawai' => 'Aktif',
                'gol_darah' => 'B',
                'tinggi_badan' => 160,
                'berat_badan' => 52,
            ],
            [
                'nip' => '199501012023121002',
                'nama' => 'Budi Setiawan, A.Md.',
                'nik' => '3201030101950001',
                'gelar_depan' => null,
                'gelar_belakang' => 'A.Md.',
                'tempat_lahir' => 'Surabaya',
                'tanggal_lahir' => '1995-01-01',
                'jenis_kelamin' => 'L',
                'agama' => 'Kristen',
                'pendidikan' => 'D3 Administrasi Perkantoran',
                'email' => 'budi.setiawan@simpeg.go.id',
                'no_hp' => '08991234567',
                'alamat' => 'Jl. Pemuda No. 12, Surabaya',
                'npwp' => null,
                'bpjs' => '0005556667771',
                'jenis_pegawai' => 'PPPK',
                'status_asn' => 'ASN',
                'status_pernikahan' => 'Menikah',
                'nama_pasangan' => 'Christina',
                'jumlah_anak' => 1,
                'tanggal_masuk' => '2023-12-01',
                'tmt_sk_pertama' => '2023-12-01',
                'tmt_pangkat_terakhir' => null,
                'tmt_kgb_terakhir' => null,
                'status_pegawai' => 'Aktif',
                'gol_darah' => 'AB',
                'tinggi_badan' => 175,
                'berat_badan' => 70,
            ]
        ];

        // 3. Masukkan data ke database menggunakan PegawaiService agar logika hitung otomatis KGB/KP tetap berjalan!
        // Ambil instance PegawaiService dari container aplikasi Laravel
        $pegawaiService = app(\App\Services\PegawaiService::class);

        foreach ($dataPegawai as $pegawaiData) {
            // Pasangkan ID relasi secara acak dari database yang sudah ada
            $pegawaiData['unit_kerja_id'] = $unitKerjaIds[array_rand($unitKerjaIds)];
            $pegawaiData['jabatan_id'] = $jabatanIds[array_rand($jabatanIds)];
            $pegawaiData['golongan_id'] = $golonganIds[array_rand($golonganIds)];

            // Eksekusi pembuatan data via service
            $pegawaiService->createPegawai($pegawaiData);
        }

        $this->command->info('PegawaiSeeder berhasil memasukkan ' . count($dataPegawai) . ' data pegawai dummy beserta riwayat awal jabatan dan pangkatnya.');
    }
}