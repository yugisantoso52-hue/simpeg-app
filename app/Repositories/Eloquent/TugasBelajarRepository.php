<?php

namespace App\Repositories\Eloquent;

use App\Models\TugasBelajar;
use App\Repositories\Contracts\TugasBelajarRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TugasBelajarRepository extends BaseRepository implements TugasBelajarRepositoryInterface
{
    public function __construct(TugasBelajar $model)
    {
        parent::__construct($model);
    }

    public function filter(?string $search, ?string $jenjang, ?string $status, ?int $pegawaiId = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->with(['pegawai.unitKerja', 'pegawai.jabatan'])
            ->when($pegawaiId, function ($query) use ($pegawaiId) {
                $query->where('pegawai_id', $pegawaiId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('program_studi', 'like', "%{$search}%")
                        ->orWhere('perguruan_tinggi', 'like', "%{$search}%")
                        ->orWhere('nomor_sk', 'like', "%{$search}%")
                        ->orWhere('sumber_pembiayaan', 'like', "%{$search}%")
                        ->orWhere('nama_sponsor', 'like', "%{$search}%")
                        ->orWhereHas('pegawai', function ($p) use ($search) {
                            $p->where('nama', 'like', "%{$search}%")
                                ->orWhere('nip', 'like', "%{$search}%");
                        });
                });
            })
            ->when($jenjang, function ($query) use ($jenjang) {
                $query->where('jenjang_studi', $jenjang);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status_studi', $status);
            })
            ->orderByRaw("CASE 
                WHEN status_studi = 'Sedang Studi' THEN 0 
                WHEN status_studi = 'Perpanjangan' THEN 1 
                WHEN status_studi = 'Lulus' THEN 2 
                ELSE 3 END ASC")
            ->latest('tanggal_mulai')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getStatistics(?int $pegawaiId = null): array
    {
        $query = $this->model->when($pegawaiId, function ($q) use ($pegawaiId) {
            $q->where('pegawai_id', $pegawaiId);
        });

        return [
            'total'        => (clone $query)->count(),
            'sedang_studi' => (clone $query)->where('status_studi', 'Sedang Studi')->count(),
            'perpanjangan' => (clone $query)->where('status_studi', 'Perpanjangan')->count(),
            'lulus'        => (clone $query)->where('status_studi', 'Lulus')->count(),
            'luar_negeri'  => (clone $query)->where('negara', '!=', 'Indonesia')->count(),
        ];
    }

    public function getByPegawai(int $pegawaiId): Collection
    {
        return $this->model->where('pegawai_id', $pegawaiId)
            ->latest('tanggal_mulai')
            ->get();
    }

    public function getActiveByPegawai(int $pegawaiId): ?TugasBelajar
    {
        return $this->model->where('pegawai_id', $pegawaiId)
            ->whereIn('status_studi', ['Sedang Studi', 'Perpanjangan'])
            ->latest('tanggal_mulai')
            ->first();
    }

    public function getExpiring(int $months = 6): Collection
    {
        $today = Carbon::today()->toDateString();
        $target = Carbon::today()->addMonths($months)->toDateString();

        return $this->model->with(['pegawai.unitKerja', 'pegawai.jabatan'])
            ->whereIn('status_studi', ['Sedang Studi', 'Perpanjangan'])
            ->whereBetween('tanggal_selesai', [$today, $target])
            ->orderBy('tanggal_selesai', 'asc')
            ->get();
    }
}
