<?php

namespace App\Repositories\Eloquent;

use App\Models\Pegawai;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardRepository implements DashboardRepositoryInterface
{
    /**
     * Mengambil Statistik Utama Pegawai
     */
    public function getStatistics(): array
    {
        return [
            'total'   => Pegawai::count(),
            'asn'     => Pegawai::where('status_asn', 'ASN')->count(),
            'non_asn' => Pegawai::where('status_asn', 'Non ASN')->count(),
            'aktif'   => Pegawai::where('status_pegawai', 'Aktif')->count(),
            'pensiun' => Pegawai::where('status_pegawai', 'Pensiun')->count(),
            'pns'     => Pegawai::where('jenis_pegawai', 'PNS')->count(),
            'pppk'    => Pegawai::where('jenis_pegawai', 'PPPK')->count(),
            'honorer' => Pegawai::where('jenis_pegawai', 'Honorer')->count(),
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
        return Pegawai::select('pendidikan', DB::raw('count(*) as total'))
            ->whereNotNull('pendidikan')
            ->where('pendidikan', '!=', '')
            ->groupBy('pendidikan')
            ->orderBy('pendidikan')
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
     * Mengambil Data Pengingat (Reminder) Jatuh Tempo H-3 Bulan
     */
    /**
     * Mengambil Data Pengingat (Reminder) Jatuh Tempo H-3 Bulan
     */
    public function getReminder(): array
    {
        $hariIni    = Carbon::now()->startOfDay()->toDateTimeString();
        $hariTarget = Carbon::now()->addMonths(3)->endOfDay()->toDateTimeString();

        $tahunLahirTargetMin  = Carbon::now()->subYears(58)->startOfDay()->toDateTimeString(); 
        $tahunLahirTargetMaks = Carbon::now()->subYears(58)->addMonths(3)->endOfDay()->toDateTimeString();

        // 1. KGB (Filter SQL Direct)
        $kgb = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])
            ->where('status_pegawai', 'Aktif')
            ->whereBetween('kgb_berikutnya', [$hariIni, $hariTarget])
            ->orderBy('kgb_berikutnya', 'asc')
            ->take(20)
            ->get()
            ->map(function ($pegawai) {
                $pegawai->tanggal_kegiatan = $pegawai->kgb_berikutnya ? Carbon::parse($pegawai->kgb_berikutnya)->format('d-m-Y') : '-';
                return $pegawai;
            });

        // 2. Kenaikan Pangkat (KP - Filter SQL Direct)
        $kp = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])
            ->where('status_pegawai', 'Aktif')
            ->whereBetween('kp_berikutnya', [$hariIni, $hariTarget])
            ->orderBy('kp_berikutnya', 'asc')
            ->take(20)
            ->get()
            ->map(function ($pegawai) {
                $pegawai->tanggal_kegiatan = $pegawai->kp_berikutnya ? Carbon::parse($pegawai->kp_berikutnya)->format('d-m-Y') : '-';
                return $pegawai;
            });

        // 3. Pensiun (BUP 58 Tahun - Filter SQL Direct)
        $pensiun = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])
            ->where('status_pegawai', 'Aktif')
            ->whereNotNull('tanggal_lahir')
            ->whereBetween('tanggal_lahir', [$tahunLahirTargetMin, $tahunLahirTargetMaks])
            ->orderBy('tanggal_lahir', 'asc')
            ->take(20)
            ->get()
            ->map(function ($pegawai) {
                $pegawai->tanggal_kegiatan = Carbon::parse($pegawai->tanggal_lahir)->addYears(58)->format('d-m-Y');
                return $pegawai;
            });

        // 4. Satyalancana (Filter SQL Direct)
        $satyalancana = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])
            ->where('status_pegawai', 'Aktif')
            ->whereBetween('satyalancana_berikutnya', [$hariIni, $hariTarget])
            ->orderBy('satyalancana_berikutnya', 'asc')
            ->take(20)
            ->get()
            ->map(function ($pegawai) {
                $pegawai->tanggal_kegiatan = $pegawai->satyalancana_berikutnya ? Carbon::parse($pegawai->satyalancana_berikutnya)->format('d-m-Y') : '-';
                return $pegawai;
            });

        return [
            'kgb'          => $kgb,
            'kp'           => $kp,
            'pensiun'      => $pensiun,
            'satyalancana' => $satyalancana,
        ];
    }
}