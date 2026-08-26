<?php

namespace App\Repositories\Eloquent;

use App\Models\RiwayatSkp;
use App\Repositories\Contracts\RiwayatSkpRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RiwayatSkpRepository extends BaseRepository implements RiwayatSkpRepositoryInterface
{
    public function __construct(RiwayatSkp $model)
    {
        parent::__construct($model);
    }

    public function filter(?string $search, ?int $tahun, ?string $predikat, ?int $pegawaiId = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->with(['pegawai.unitKerja', 'pegawai.jabatan'])
            ->when($pegawaiId, function ($query) use ($pegawaiId) {
                $query->where('pegawai_id', $pegawaiId);
            })
            ->when($tahun, function ($query) use ($tahun) {
                $query->where('tahun', $tahun);
            })
            ->when($predikat, function ($query) use ($predikat) {
                $query->where('predikat_kinerja', $predikat);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('pejabat_penilai', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%")
                        ->orWhereHas('pegawai', function ($p) use ($search) {
                            $p->where('nama', 'like', "%{$search}%")
                                ->orWhere('nip', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('tahun', 'desc')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getStatistics(?int $pegawaiId = null): array
    {
        $query = $this->model->when($pegawaiId, function ($q) use ($pegawaiId) {
            $q->where('pegawai_id', $pegawaiId);
        });

        $currentYear = now()->year;
        $prevYear = $currentYear - 1;

        return [
            'total'           => (clone $query)->count(),
            'tahun_n'         => (clone $query)->where('tahun', $currentYear)->count(),
            'tahun_n1'        => (clone $query)->where('tahun', $prevYear)->count(),
            'sangat_baik'     => (clone $query)->where('predikat_kinerja', 'Sangat Baik')->count(),
            'baik'            => (clone $query)->where('predikat_kinerja', 'Baik')->count(),
            'berkas_lengkap'  => (clone $query)->whereNotNull('file_rencana_skp')->whereNotNull('file_evaluasi_skp')->count(),
        ];
    }

    public function getByPegawai(int $pegawaiId): Collection
    {
        return $this->model->where('pegawai_id', $pegawaiId)
            ->orderBy('tahun', 'desc')
            ->get();
    }

    public function findByPegawaiAndTahun(int $pegawaiId, int $tahun): ?RiwayatSkp
    {
        return $this->model->where('pegawai_id', $pegawaiId)
            ->where('tahun', $tahun)
            ->first();
    }
}
