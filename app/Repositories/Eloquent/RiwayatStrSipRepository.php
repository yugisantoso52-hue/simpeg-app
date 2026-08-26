<?php

namespace App\Repositories\Eloquent;

use App\Models\RiwayatStrSip;
use App\Repositories\Contracts\RiwayatStrSipRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RiwayatStrSipRepository extends BaseRepository implements RiwayatStrSipRepositoryInterface
{
    public function __construct(RiwayatStrSip $model)
    {
        parent::__construct($model);
    }

    public function search(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->with('pegawai')
            ->when($search, function ($query) use ($search) {
                $query->where('nomor_registrasi', 'like', "%{$search}%")
                    ->orWhere('nama_dokumen', 'like', "%{$search}%")
                    ->orWhere('instansi_penerbit', 'like', "%{$search}%")
                    ->orWhereHas('pegawai', function ($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%");
                    });
            })
            ->latest('tanggal_terbit')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function filter(?string $search, ?string $jenis, ?string $status, int $perPage = 10): LengthAwarePaginator
    {
        $today = Carbon::today()->toDateString();
        $target6Months = Carbon::today()->addMonths(6)->toDateString();

        return $this->model->with(['pegawai.unitKerja', 'pegawai.jabatan'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_registrasi', 'like', "%{$search}%")
                        ->orWhere('nama_dokumen', 'like', "%{$search}%")
                        ->orWhere('instansi_penerbit', 'like', "%{$search}%")
                        ->orWhereHas('pegawai', function ($p) use ($search) {
                            $p->where('nama', 'like', "%{$search}%")
                                ->orWhere('nip', 'like', "%{$search}%");
                        });
                });
            })
            ->when($jenis, function ($query) use ($jenis) {
                $query->where('jenis_dokumen', $jenis);
            })
            ->when($status, function ($query) use ($status, $today, $target6Months) {
                if ($status === 'seumur_hidup') {
                    $query->where('is_seumur_hidup', true);
                } elseif ($status === 'aktif') {
                    $query->where('is_seumur_hidup', true)
                        ->orWhere(function ($q) use ($today) {
                            $q->where('is_seumur_hidup', false)
                                ->where('tanggal_berakhir', '>=', $today);
                        });
                } elseif ($status === 'segera_berakhir') {
                    $query->where('is_seumur_hidup', false)
                        ->whereBetween('tanggal_berakhir', [$today, $target6Months]);
                } elseif ($status === 'kedaluwarsa') {
                    $query->where('is_seumur_hidup', false)
                        ->where('tanggal_berakhir', '<', $today);
                } else {
                    $query->where('status', $status);
                }
            })
            ->orderByRaw("CASE 
                WHEN is_seumur_hidup = 1 THEN 2 
                WHEN tanggal_berakhir < '{$today}' THEN 0 
                WHEN tanggal_berakhir <= '{$target6Months}' THEN 1 
                ELSE 3 END ASC")
            ->latest('tanggal_terbit')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getStatistics(): array
    {
        $today = Carbon::today()->toDateString();
        $target6Months = Carbon::today()->addMonths(6)->toDateString();

        return [
            'total'           => $this->model->count(),
            'str'             => $this->model->where('jenis_dokumen', 'STR')->count(),
            'sip'             => $this->model->whereIn('jenis_dokumen', ['SIP', 'SIKP'])->count(),
            'seumur_hidup'    => $this->model->where('is_seumur_hidup', true)->count(),
            'segera_berakhir' => $this->model->where('is_seumur_hidup', false)->whereBetween('tanggal_berakhir', [$today, $target6Months])->count(),
            'kedaluwarsa'     => $this->model->where('is_seumur_hidup', false)->where('tanggal_berakhir', '<', $today)->count(),
        ];
    }

    public function getByPegawai(int $pegawaiId): Collection
    {
        return $this->model->where('pegawai_id', $pegawaiId)
            ->orderBy('tanggal_terbit', 'desc')
            ->get();
    }

    public function getExpiring(int $months = 6): Collection
    {
        $today = Carbon::today()->toDateString();
        $target = Carbon::today()->addMonths($months)->toDateString();

        return $this->model->with(['pegawai.unitKerja', 'pegawai.jabatan'])
            ->where('is_seumur_hidup', false)
            ->whereBetween('tanggal_berakhir', [$today, $target])
            ->orderBy('tanggal_berakhir', 'asc')
            ->get();
    }
}
