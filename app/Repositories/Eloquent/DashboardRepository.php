<?php

namespace App\Repositories\Eloquent;

use App\Models\Pegawai;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardRepository implements DashboardRepositoryInterface
{
    /**
     * Mengambil Statistik Utama Pegawai (Dioptimasi 1 Query Cepat)
     */
    public function getStatistics(): array
    {
        $stats = Pegawai::selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN status_asn = 'ASN' THEN 1 ELSE 0 END), 0) as asn,
            COALESCE(SUM(CASE WHEN status_asn = 'Non ASN' THEN 1 ELSE 0 END), 0) as non_asn,
            COALESCE(SUM(CASE WHEN status_pegawai = 'Aktif' THEN 1 ELSE 0 END), 0) as aktif,
            COALESCE(SUM(CASE WHEN status_pegawai = 'Pensiun' THEN 1 ELSE 0 END), 0) as pensiun,
            COALESCE(SUM(CASE WHEN jenis_pegawai = 'Dosen' THEN 1 ELSE 0 END), 0) as dosen,
            COALESCE(SUM(CASE WHEN jenis_pegawai = 'PNS' THEN 1 ELSE 0 END), 0) as pns,
            COALESCE(SUM(CASE WHEN jenis_pegawai = 'PPPK' THEN 1 ELSE 0 END), 0) as pppk,
            COALESCE(SUM(CASE WHEN jenis_pegawai IN ('PHL', 'Honorer') THEN 1 ELSE 0 END), 0) as phl
        ")->first();

        return [
            'total'   => (int)($stats->total ?? 0),
            'asn'     => (int)($stats->asn ?? 0),
            'non_asn' => (int)($stats->non_asn ?? 0),
            'aktif'   => (int)($stats->aktif ?? 0),
            'pensiun' => (int)($stats->pensiun ?? 0),
            'dosen'   => (int)($stats->dosen ?? 0),
            'pns'     => (int)($stats->pns ?? 0),
            'pppk'    => (int)($stats->pppk ?? 0),
            'phl'     => (int)($stats->phl ?? 0),
        ];
    }

    /**
     * Mengambil Jumlah Pegawai Berdasarkan Golongan
     */
    public function getPegawaiPerGolongan()
    {
        return Pegawai::select('golongan_id', DB::raw('count(*) as total'))
            ->whereNotNull('golongan_id')
            ->groupBy('golongan_id')
            ->with('golongan')
            ->get()
            ->map(function ($item) {
                return [
                    'golongan' => $item->golongan->nama_golongan ?? $item->golongan->nama ?? 'Tanpa Golongan',
                    'total'    => $item->total
                ];
            });
    }

    /**
     * Mengambil Jumlah Pegawai Berdasarkan Tingkat Pendidikan
     */
    public function getPegawaiPerPendidikan()
    {
        return Pegawai::select('pendidikan_terakhir', DB::raw('count(*) as total'))
            ->whereNotNull('pendidikan_terakhir')
            ->where('pendidikan_terakhir', '!=', '')
            ->groupBy('pendidikan_terakhir')
            ->orderBy('pendidikan_terakhir')
            ->get();
    }

    /**
     * Mengambil Jumlah Pegawai per Unit Kerja
     */
    public function getPegawaiPerUnit()
    {
        return Pegawai::select('unit_kerja_id', DB::raw('count(*) as total'))
            ->whereNotNull('unit_kerja_id')
            ->groupBy('unit_kerja_id')
            ->with('unitKerja')
            ->get()
            ->map(function ($item) {
                return [
                    'unit'  => $item->unitKerja->nama_unit ?? $item->unitKerja->nama_unit_kerja ?? $item->unitKerja->nama ?? 'Tanpa Unit Kerja',
                    'total' => $item->total
                ];
            });
    }

    /**
     * Mengambil Daftar Pegawai yang Baru Bergabung
     */
    public function getPegawaiBaru(int $limit = 10)
    {
        return Pegawai::with(['unitKerja', 'jabatan'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Mengambil Data Pengingat (Reminder) Jatuh Tempo (KGB, KP, Satyalancana: H-3 Bulan; Pensiun: H-1 Tahun)
     */
    public function getReminder(): array
    {
        $hariIni    = Carbon::now()->startOfDay()->toDateTimeString();
        $hariTarget = Carbon::now()->addMonths(3)->endOfDay()->toDateTimeString();

        // Khusus Masa Pensiun (BUP 58 Tahun): Radar 1 Tahun ke Depan
        $tahunLahirPensiunMin  = Carbon::now()->subYears(58)->startOfDay()->toDateTimeString(); 
        $tahunLahirPensiunMaks = Carbon::now()->subYears(58)->addYear()->endOfDay()->toDateTimeString();

        // 1. KGB (Filter SQL Direct - 3 Bulan ke Depan)
        $kgb = Pegawai::where('status_pegawai', 'Aktif')
            ->whereBetween('kgb_berikutnya', [$hariIni, $hariTarget])
            ->orderBy('kgb_berikutnya', 'asc')
            ->take(20)
            ->get()
            ->map(function ($pegawai) {
                return (object) [
                    'id'               => $pegawai->id,
                    'nama'             => $pegawai->nama,
                    'nama_lengkap'     => $pegawai->nama_lengkap ?? $pegawai->nama,
                    'nip'              => $pegawai->nip,
                    'tanggal_kegiatan' => $pegawai->kgb_berikutnya ? Carbon::parse($pegawai->kgb_berikutnya)->format('d-m-Y') : '-',
                ];
            });

        // 2. Kenaikan Pangkat (KP - Filter SQL Direct - 3 Bulan ke Depan)
        $kp = Pegawai::where('status_pegawai', 'Aktif')
            ->whereBetween('kp_berikutnya', [$hariIni, $hariTarget])
            ->orderBy('kp_berikutnya', 'asc')
            ->take(20)
            ->get()
            ->map(function ($pegawai) {
                return (object) [
                    'id'               => $pegawai->id,
                    'nama'             => $pegawai->nama,
                    'nama_lengkap'     => $pegawai->nama_lengkap ?? $pegawai->nama,
                    'nip'              => $pegawai->nip,
                    'tanggal_kegiatan' => $pegawai->kp_berikutnya ? Carbon::parse($pegawai->kp_berikutnya)->format('d-m-Y') : '-',
                ];
            });

        // 3. Pensiun (BUP 58 Tahun - Filter SQL Direct - 1 Tahun ke Depan)
        $pensiun = Pegawai::where('status_pegawai', 'Aktif')
            ->whereNotNull('tanggal_lahir')
            ->whereBetween('tanggal_lahir', [$tahunLahirPensiunMin, $tahunLahirPensiunMaks])
            ->orderBy('tanggal_lahir', 'asc')
            ->take(20)
            ->get()
            ->map(function ($pegawai) {
                return (object) [
                    'id'               => $pegawai->id,
                    'nama'             => $pegawai->nama,
                    'nama_lengkap'     => $pegawai->nama_lengkap ?? $pegawai->nama,
                    'nip'              => $pegawai->nip,
                    'tanggal_kegiatan' => Carbon::parse($pegawai->tanggal_lahir)->addYears(58)->format('d-m-Y'),
                ];
            });

        // 4. Satyalancana (Filter SQL Direct)
        $satyalancana = Pegawai::where('status_pegawai', 'Aktif')
            ->whereBetween('satyalancana_berikutnya', [$hariIni, $hariTarget])
            ->orderBy('satyalancana_berikutnya', 'asc')
            ->take(20)
            ->get()
            ->map(function ($pegawai) {
                return (object) [
                    'id'               => $pegawai->id,
                    'nama'             => $pegawai->nama,
                    'nama_lengkap'     => $pegawai->nama_lengkap ?? $pegawai->nama,
                    'nip'              => $pegawai->nip,
                    'tanggal_kegiatan' => $pegawai->satyalancana_berikutnya ? Carbon::parse($pegawai->satyalancana_berikutnya)->format('d-m-Y') : '-',
                ];
            });

        // 5. Legalitas Profesi (STR & SIP - Radar 6 Bulan ke Depan)
        $strSip = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('riwayat_str_sip')) {
            $hariTarget6Bulan = Carbon::now()->addMonths(6)->endOfDay()->toDateTimeString();
            $strSip = \App\Models\RiwayatStrSip::with(['pegawai'])
                ->where('is_seumur_hidup', false)
                ->whereBetween('tanggal_berakhir', [$hariIni, $hariTarget6Bulan])
                ->orderBy('tanggal_berakhir', 'asc')
                ->take(20)
                ->get()
                ->map(function ($item) {
                    $nama = $item->pegawai->nama_lengkap ?? $item->pegawai->nama ?? '-';
                    return (object) [
                        'id'               => $item->id,
                        'nama'             => $nama,
                        'nama_lengkap'     => $nama,
                        'jenis_dokumen'    => $item->jenis_dokumen ?? 'STR/SIP',
                        'tanggal_kegiatan' => $item->tanggal_berakhir ? Carbon::parse($item->tanggal_berakhir)->format('d-m-Y') : '-',
                    ];
                });
        }

        return [
            'kgb'          => $kgb,
            'kp'           => $kp,
            'pensiun'      => $pensiun,
            'satyalancana' => $satyalancana,
            'str_sip'      => $strSip,
        ];
    }
}