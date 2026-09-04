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
        return $this->searchFiltered($search, null, $perPage);
    }

    /**
     * Pencarian pegawai dengan filter kategori (Dosen, PNS, PPPK, PHL, Aktif)
     */
    public function searchFiltered(?string $search, ?string $filter = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(['unitKerja', 'jabatan', 'golongan'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nip', 'like', "%{$search}%")
                      ->orWhere('nama', 'like', "%{$search}%")
                      ->orWhere('nidn_nuptk', 'like', "%{$search}%");
                });
            })
            ->when($filter, function ($query) use ($filter) {
                switch (strtolower($filter)) {
                    case 'dosen':
                        $query->dosen();
                        break;
                    case 'dosen_pns':
                        $query->dosen()->where(function ($q) {
                            $q->where('jenis_pegawai', 'not like', '%PPPK%')
                              ->where(function ($sq) {
                                  $sq->where('status_asn', 'ASN')
                                     ->orWhereRaw('CHAR_LENGTH(nip) = 18');
                              });
                        });
                        break;
                    case 'dosen_pppk':
                        $query->dosen()->where(function ($q) {
                            $q->where('jenis_pegawai', 'like', '%PPPK%')
                              ->orWhere('status_asn', 'PPPK')
                              ->orWhereRaw('CHAR_LENGTH(nip) = 21');
                        });
                        break;
                    case 'tendik':
                        $query->tendik();
                        break;
                    case 'tendik_pns':
                        $query->tendik()->where(function ($q) {
                            $q->where('jenis_pegawai', 'PNS')
                              ->orWhere(function ($sq) {
                                  $sq->where('status_asn', 'ASN')
                                     ->where('jenis_pegawai', 'not like', '%PPPK%');
                              });
                        });
                        break;
                    case 'tendik_pppk':
                        $query->tendik()->where(function ($q) {
                            $q->where('jenis_pegawai', 'PPPK')
                              ->orWhere('status_asn', 'PPPK');
                        });
                        break;
                    case 'pns':
                        $query->where(function ($q) {
                            $q->where('jenis_pegawai', 'PNS')
                              ->orWhere(function ($sq) {
                                  $sq->where('status_asn', 'ASN')
                                     ->where('jenis_pegawai', 'not like', '%PPPK%');
                              });
                        });
                        break;
                    case 'pppk':
                        $query->where(function ($q) {
                            $q->where('jenis_pegawai', 'PPPK')
                              ->orWhere('status_asn', 'PPPK')
                              ->orWhereRaw('CHAR_LENGTH(nip) = 21');
                        });
                        break;
                    case 'phl':
                    case 'honorer':
                        $query->phl();
                        break;
                    case 'aktif':
                        $query->aktif();
                        break;
                    case 'pensiun':
                        $query->where('status_pegawai', 'Pensiun');
                        break;
                }
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
        $stats = $this->model->selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN status_pegawai = 'Aktif' THEN 1 ELSE 0 END), 0) as aktif,
            COALESCE(SUM(CASE WHEN status_pegawai = 'Pensiun' THEN 1 ELSE 0 END), 0) as pensiun,
            COALESCE(SUM(CASE WHEN status_asn = 'ASN' THEN 1 ELSE 0 END), 0) as asn,
            COALESCE(SUM(CASE WHEN status_asn = 'Non ASN' THEN 1 ELSE 0 END), 0) as non_asn,
            COALESCE(SUM(CASE WHEN jenis_pegawai = 'PNS' THEN 1 ELSE 0 END), 0) as pns,
            COALESCE(SUM(CASE WHEN jenis_pegawai = 'PPPK' THEN 1 ELSE 0 END), 0) as pppk,
            COALESCE(SUM(CASE WHEN (jenis_pegawai LIKE '%Dosen%' OR (nidn_nuptk IS NOT NULL AND nidn_nuptk != '' AND nidn_nuptk != '-')) THEN 1 ELSE 0 END), 0) as dosen,
            COALESCE(SUM(CASE WHEN jenis_pegawai IN ('PHL', 'Honorer', 'Tenaga Kontrak') OR status_asn = 'Non ASN' THEN 1 ELSE 0 END), 0) as phl
        ")->first();

        return [
            'total'   => (int)($stats->total ?? 0),
            'aktif'   => (int)($stats->aktif ?? 0),
            'pensiun' => (int)($stats->pensiun ?? 0),
            'asn'     => (int)($stats->asn ?? 0),
            'non_asn' => (int)($stats->non_asn ?? 0),
            'pns'     => (int)($stats->pns ?? 0),
            'pppk'    => (int)($stats->pppk ?? 0),
            'dosen'   => (int)($stats->dosen ?? 0),
            'phl'     => (int)($stats->phl ?? 0),
            'honorer' => (int)($stats->phl ?? 0),
        ];
    }
}