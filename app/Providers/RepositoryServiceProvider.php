<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Import Contracts (Interfaces)
use App\Repositories\Contracts\PegawaiRepositoryInterface;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\Contracts\UnitKerjaRepositoryInterface;
use App\Repositories\Contracts\JabatanRepositoryInterface;
use App\Repositories\Contracts\GolonganRepositoryInterface;
use App\Repositories\Contracts\RiwayatPendidikanRepositoryInterface;
use App\Repositories\Contracts\RiwayatJabatanRepositoryInterface;
use App\Repositories\Contracts\RiwayatPangkatRepositoryInterface;
use App\Repositories\Contracts\RiwayatDiklatRepositoryInterface;
use App\Repositories\Contracts\RiwayatStrSipRepositoryInterface;
use App\Repositories\Contracts\PengajuanCutiRepositoryInterface;
use App\Repositories\Contracts\TugasBelajarRepositoryInterface;

// Import Eloquent Repositories
use App\Repositories\Eloquent\PegawaiRepository;
use App\Repositories\Eloquent\DashboardRepository;
use App\Repositories\Eloquent\UnitKerjaRepository;
use App\Repositories\Eloquent\JabatanRepository;
use App\Repositories\Eloquent\GolonganRepository;
use App\Repositories\Eloquent\RiwayatPendidikanRepository;
use App\Repositories\Eloquent\RiwayatJabatanRepository;
use App\Repositories\Eloquent\RiwayatPangkatRepository;
use App\Repositories\Eloquent\RiwayatDiklatRepository;
use App\Repositories\Eloquent\RiwayatStrSipRepository;
use App\Repositories\Eloquent\PengajuanCutiRepository;
use App\Repositories\Eloquent\TugasBelajarRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register Repository Bindings
     */
    public function register(): void
    {
        // Pegawai
        $this->app->bind(
            PegawaiRepositoryInterface::class,
            PegawaiRepository::class
        );

        // Dashboard
        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );

        // Unit Kerja
        $this->app->bind(
            UnitKerjaRepositoryInterface::class,
            UnitKerjaRepository::class
        );

        // Jabatan
        $this->app->bind(
            JabatanRepositoryInterface::class,
            JabatanRepository::class
        );

        // Golongan
        $this->app->bind(
            GolonganRepositoryInterface::class,
            GolonganRepository::class
        );

        // Riwayat Pendidikan
        $this->app->bind(
            RiwayatPendidikanRepositoryInterface::class,
            RiwayatPendidikanRepository::class
        );

        // Riwayat Jabatan
        $this->app->bind(
            RiwayatJabatanRepositoryInterface::class,
            RiwayatJabatanRepository::class
        );

        // Riwayat Pangkat
        $this->app->bind(
            RiwayatPangkatRepositoryInterface::class,
            RiwayatPangkatRepository::class
        );

        $this->app->bind(
            RiwayatDiklatRepositoryInterface::class,
            RiwayatDiklatRepository::class
        );

        // Riwayat STR & SIP (Legalitas Profesi)
        $this->app->bind(
            RiwayatStrSipRepositoryInterface::class,
            RiwayatStrSipRepository::class
        );

        // Pengajuan Cuti (E-Cuti)
        $this->app->bind(
            PengajuanCutiRepositoryInterface::class,
            PengajuanCutiRepository::class
        );

        // Tugas Belajar & Izin Belajar
        $this->app->bind(
            TugasBelajarRepositoryInterface::class,
            TugasBelajarRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}