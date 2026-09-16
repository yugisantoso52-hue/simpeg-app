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
        $currentYear = now()->year;
        $prevYear = $currentYear - 1;

        $stats = $this->model->when($pegawaiId, function ($q) use ($pegawaiId) {
            $q->where('pegawai_id', $pegawaiId);
        })->selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN tahun = ? THEN 1 ELSE 0 END), 0) as tahun_n,
            COALESCE(SUM(CASE WHEN tahun = ? THEN 1 ELSE 0 END), 0) as tahun_n1,
            COALESCE(SUM(CASE WHEN predikat_kinerja = 'Sangat Baik' THEN 1 ELSE 0 END), 0) as sangat_baik,
            COALESCE(SUM(CASE WHEN predikat_kinerja = 'Baik' THEN 1 ELSE 0 END), 0) as baik,
            COALESCE(SUM(CASE WHEN file_rencana_skp IS NOT NULL AND file_evaluasi_skp IS NOT NULL THEN 1 ELSE 0 END), 0) as berkas_lengkap
        ", [$currentYear, $prevYear])->first();

        return [
            'total'           => (int)($stats->total ?? 0),
            'tahun_n'         => (int)($stats->tahun_n ?? 0),
            'tahun_n1'        => (int)($stats->tahun_n1 ?? 0),
            'sangat_baik'     => (int)($stats->sangat_baik ?? 0),
            'baik'            => (int)($stats->baik ?? 0),
            'berkas_lengkap'  => (int)($stats->berkas_lengkap ?? 0),
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
