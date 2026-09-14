<?php

namespace App\Repositories\Eloquent;

use App\Models\PengajuanCuti;
use App\Repositories\Contracts\PengajuanCutiRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PengajuanCutiRepository extends BaseRepository implements PengajuanCutiRepositoryInterface
{
    public function __construct(PengajuanCuti $model)
    {
        parent::__construct($model);
    }

    public function filter(?string $search, ?string $jenis, ?string $status, ?int $pegawaiId = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->with(['pegawai.unitKerja', 'pegawai.jabatan', 'approver'])
            ->when($pegawaiId, function ($query) use ($pegawaiId) {
                $query->where('pegawai_id', $pegawaiId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('alasan', 'like', "%{$search}%")
                        ->orWhere('jenis_cuti', 'like', "%{$search}%")
                        ->orWhereHas('pegawai', function ($p) use ($search) {
                            $p->where('nama', 'like', "%{$search}%")
                                ->orWhere('nip', 'like', "%{$search}%");
                        });
                });
            })
            ->when($jenis, function ($query) use ($jenis) {
                $query->where('jenis_cuti', $jenis);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByRaw("CASE WHEN status = 'Menunggu Persetujuan' THEN 0 ELSE 1 END")
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getStatistics(?int $pegawaiId = null): array
    {
        $today = Carbon::today()->toDateString();
        $query = $this->model->when($pegawaiId, function ($q) use ($pegawaiId) {
            $q->where('pegawai_id', $pegawaiId);
        });

        return [
            'total'     => (clone $query)->count(),
            'menunggu'  => (clone $query)->where('status', 'Menunggu Persetujuan')->count(),
            'disetujui' => (clone $query)->where('status', 'Disetujui')->count(),
            'ditolak'   => (clone $query)->where('status', 'Ditolak')->count(),
            'hari_ini'  => (clone $query)->where('status', 'Disetujui')
                ->where('tanggal_mulai', '<=', $today)
                ->where('tanggal_selesai', '>=', $today)
                ->count(),
        ];
    }

    public function getByPegawai(int $pegawaiId): Collection
    {
        return $this->model->with(['approver'])
            ->where('pegawai_id', $pegawaiId)
            ->latest('created_at')
            ->get();
    }

    public function getPendingCount(): int
    {
        return $this->model->where('status', 'Menunggu Persetujuan')->count();
    }
}
