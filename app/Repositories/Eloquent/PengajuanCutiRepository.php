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
        $stats = $this->model->when($pegawaiId, function ($q) use ($pegawaiId) {
            $q->where('pegawai_id', $pegawaiId);
        })->selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN status = 'Menunggu Persetujuan' THEN 1 ELSE 0 END), 0) as menunggu,
            COALESCE(SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END), 0) as disetujui,
            COALESCE(SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END), 0) as ditolak,
            COALESCE(SUM(CASE WHEN status = 'Disetujui' AND tanggal_mulai <= ? AND tanggal_selesai >= ? THEN 1 ELSE 0 END), 0) as hari_ini
        ", [$today, $today])->first();

        return [
            'total'     => (int)($stats->total ?? 0),
            'menunggu'  => (int)($stats->menunggu ?? 0),
            'disetujui' => (int)($stats->disetujui ?? 0),
            'ditolak'   => (int)($stats->ditolak ?? 0),
            'hari_ini'  => (int)($stats->hari_ini ?? 0),
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
