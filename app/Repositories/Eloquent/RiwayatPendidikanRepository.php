<?php

namespace App\Repositories\Eloquent;

use App\Models\RiwayatPendidikan;
use App\Repositories\Contracts\RiwayatPendidikanRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiwayatPendidikanRepository implements RiwayatPendidikanRepositoryInterface
{
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return RiwayatPendidikan::with('pegawai')
            ->when($search, function ($q) use ($search) {
                $q->where('jenjang', 'like', "%{$search}%")
                    ->orWhere('institusi', 'like', "%{$search}%")
                    ->orWhere('jurusan', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);
    }

    public function find(int $id): ?RiwayatPendidikan
    {
        return RiwayatPendidikan::findOrFail($id);
    }

    public function create(array $data): RiwayatPendidikan
    {
        return RiwayatPendidikan::create($data);
    }

    public function update(int $id, array $data): RiwayatPendidikan
    {
        $model = $this->find($id);

        $model->update($data);

        return $model;
    }

    public function delete(int $id): bool
    {
        return $this->find($id)->delete();
    }
}