<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Repositories\Contracts\RiwayatPendidikanRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RiwayatPendidikanService
{
    public function __construct(
        protected RiwayatPendidikanRepositoryInterface $repository
    ) {}

    public function paginate($search = null)
    {
        return $this->repository->paginate($search);
    }

    public function pegawai()
    {
        return Pegawai::orderByRaw("CASE WHEN status_pegawai = 'Aktif' THEN 0 ELSE 1 END")
            ->orderBy('nama')
            ->get();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function create(array $data, ?UploadedFile $ijazah)
    {
        return DB::transaction(function () use ($data, $ijazah) {

            if ($ijazah) {
                $data['ijazah'] = PegawaiStorageService::store($ijazah, $data['pegawai_id'] ?? null, 'ijazah', $data['jenjang'] ?? 'ijazah');
            }

            $created = $this->repository->create($data);
            $pegawai = Pegawai::find($data['pegawai_id'] ?? null);
            if ($pegawai) {
                app(PegawaiService::class)->syncPegawaiFromHistories($pegawai);
            }

            return $created;

        });
    }

    public function update($id, array $data, ?UploadedFile $ijazah)
    {
        return DB::transaction(function () use ($id, $data, $ijazah) {

            $model = $this->repository->find($id);

            if ($ijazah) {

                if ($model->ijazah) {
                    PegawaiStorageService::delete($model->ijazah);
                }

                $pegawaiTarget = $data['pegawai_id'] ?? $model->pegawai_id;
                $jenjangTarget = $data['jenjang'] ?? $model->jenjang ?? 'ijazah';
                $data['ijazah'] = PegawaiStorageService::store($ijazah, $pegawaiTarget, 'ijazah', $jenjangTarget);
            }

            $updated = $this->repository->update($id, $data);
            $pegawai = Pegawai::find($data['pegawai_id'] ?? $model->pegawai_id);
            if ($pegawai) {
                app(PegawaiService::class)->syncPegawaiFromHistories($pegawai);
            }

            return $updated;

        });
    }

    public function delete($id)
    {
        return DB::transaction(function () use ($id) {

            $model = $this->repository->find($id);

            if ($model->ijazah) {
                PegawaiStorageService::delete($model->ijazah);
            }

            $pegawaiId = $model->pegawai_id;
            $deleted = $this->repository->delete($id);
            $pegawai = Pegawai::find($pegawaiId);
            if ($pegawai) {
                app(PegawaiService::class)->syncPegawaiFromHistories($pegawai);
            }

            return $deleted;

        });
    }
}