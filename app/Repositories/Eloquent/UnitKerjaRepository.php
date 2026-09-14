<?php

namespace App\Repositories\Eloquent;

use App\Models\UnitKerja;
use App\Repositories\Contracts\UnitKerjaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UnitKerjaRepository implements UnitKerjaRepositoryInterface
{
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return UnitKerja::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama_unit', 'like', "%{$search}%")
                      ->orWhere('keterangan', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString(); // Memastikan query string pencarian tetap menempel saat pindah halaman
    }

    public function all(): Collection
    {
        return UnitKerja::orderBy('nama_unit', 'asc')->get();
    }

    public function findOrFail(int|string $id): UnitKerja
    {
        return UnitKerja::findOrFail($id);
    }

    public function create(array $data): UnitKerja
    {
        return UnitKerja::create($data);
    }

    public function update(int|string $id, array $data): UnitKerja
    {
        $unit = $this->findOrFail($id);
        if (array_key_exists('kode_unit', $data) && is_null($data['kode_unit'])) {
            unset($data['kode_unit']);
        }
        $unit->update($data);
        return $unit->fresh();
    }

    public function delete(int|string $id): bool
    {
        return $this->findOrFail($id)->delete();
    }
}