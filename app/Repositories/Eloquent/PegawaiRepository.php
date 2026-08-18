<?php

namespace App\Repositories\Eloquent;

use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\PegawaiRepositoryInterface;

class PegawaiRepository extends BaseRepository implements PegawaiRepositoryInterface
{
    public function __construct(Pegawai $model)
    {
        parent::__construct($model);
    }

    /**
     * Pencarian pegawai
     */
    public function search(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(['unitKerja', 'jabatan', 'golongan'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nip', 'like', "%{$search}%")
                      ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Jumlah Pegawai Aktif
     */
    public function getAktif(): int
    {
        return $this->model->aktif()->count();
    }

    /**
     * Jumlah Pegawai Pensiun
     */
    public function getPensiun(): int
    {
        return $this->model
            ->where('status_pegawai', Pegawai::STATUS_PENSIUN)
            ->count();
    }

    /**
     * Jumlah Pegawai yang KGB bulan ini
     */
    public function getKGBBulanIni(): int
    {
        return $this->model
            ->whereMonth('kgb_berikutnya', Carbon::now()->month)
            ->whereYear('kgb_berikutnya', Carbon::now()->year)
            ->count();
    }

    /**
     * Jumlah Pegawai yang KP bulan ini
     */
    public function getKPBulanIni(): int
    {
        return $this->model
            ->whereMonth('kp_berikutnya', Carbon::now()->month)
            ->whereYear('kp_berikutnya', Carbon::now()->year)
            ->count();
    }
/**
 * Statistik Pegawai
 */
public function getStatistics(): array
{
    return [

        'total' => $this->model->count(),

        'aktif' => $this->model
            ->where('status_pegawai', 'Aktif')
            ->count(),

        'pensiun' => $this->model
            ->where('status_pegawai', 'Pensiun')
            ->count(),

        'asn' => $this->model
            ->where('status_asn', 'ASN')
            ->count(),

        'non_asn' => $this->model
            ->where('status_asn', 'Non ASN')
            ->count(),

        'pns' => $this->model
            ->where('jenis_pegawai', 'PNS')
            ->count(),

        'pppk' => $this->model
            ->where('jenis_pegawai', 'PPPK')
            ->count(),

        'honorer' => $this->model
            ->where('jenis_pegawai', 'Honorer')
            ->count(),

    ];
}
}