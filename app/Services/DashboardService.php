<?php

namespace App\Services;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    protected DashboardRepositoryInterface $dashboardRepository;

    /**
     * Dependency Injection DashboardRepository
     */
    public function __construct(DashboardRepositoryInterface $dashboardRepository) 
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    /**
     * Statistik Dashboard (Cache 10 Menit)
     */
    public function statistics(): array
    {
        return Cache::remember('dashboard_statistics', now()->addMinutes(10), function () {
            return $this->dashboardRepository->getStatistics();
        });
    }

    /**
     * Grafik Golongan (Cache 10 Menit)
     */
    public function grafikGolongan()
    {
        return Cache::remember('dashboard_grafik_golongan', now()->addMinutes(10), function () {
            return $this->dashboardRepository->getPegawaiPerGolongan();
        });
    }

    /**
     * Grafik Pendidikan (Cache 10 Menit)
     */
    public function grafikPendidikan()
    {
        return Cache::remember('dashboard_grafik_pendidikan', now()->addMinutes(10), function () {
            return $this->dashboardRepository->getPegawaiPerPendidikan();
        });
    }

    /**
     * Grafik Unit Kerja (Cache 10 Menit)
     */
    public function grafikUnit()
    {
        return Cache::remember('dashboard_grafik_unit', now()->addMinutes(10), function () {
            return $this->dashboardRepository->getPegawaiPerUnit();
        });
    }

    /**
     * Pegawai Baru (Real-time)
     */
    public function pegawaiBaru(int $limit = 5)
    {
        return $this->dashboardRepository->getPegawaiBaru($limit);
    }

    /**
     * Reminder KGB, KP, Satyalancana, dan Pensiun (Cache Ringan 3 Menit)
     */
    public function reminder(): array
    {
        try {
            $data = Cache::remember('dashboard_reminder', now()->addMinutes(3), function () {
                return $this->dashboardRepository->getReminder();
            });
        } catch (\Throwable $e) {
            Cache::forget('dashboard_reminder');
            $data = $this->dashboardRepository->getReminder();
        }

        // Fail-safe sanitization against incomplete class or corrupted cache
        $keys = ['kgb', 'kp', 'pensiun', 'satyalancana', 'str_sip'];
        $clean = [];
        foreach ($keys as $k) {
            $val = $data[$k] ?? [];
            if (!is_countable($val) || (is_object($val) && get_class($val) === '__PHP_Incomplete_Class')) {
                Cache::forget('dashboard_reminder');
                $clean[$k] = collect();
            } else {
                $clean[$k] = $val;
            }
        }

        return $clean;
    }
}