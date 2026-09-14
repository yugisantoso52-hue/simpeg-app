<?php

namespace App\Services;

use App\Models\UnitKerja;
use App\Repositories\Contracts\UnitKerjaRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class UnitKerjaService
{
    public function __construct(
        protected UnitKerjaRepositoryInterface $repository
    ) {}

    public function paginate(?string $search = null, int $perPage = 10)
    {
        return $this->repository->paginate($search, $perPage);
    }

    public function all()
    {
        return $this->repository->all();
    }

    public function find(int|string $id): UnitKerja
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): UnitKerja
    {
        try {
            return $this->repository->create($data);
        } catch (Exception $e) {
            Log::error("Gagal menambahkan Unit Kerja: " . $e->getMessage());
            throw new Exception("Terjadi kesalahan saat menyimpan data unit kerja.");
        }
    }

    public function update(int|string $id, array $data): UnitKerja
    {
        try {
            return $this->repository->update($id, $data);
        } catch (Exception $e) {
            Log::error("Gagal memperbarui Unit Kerja ID {$id}: " . $e->getMessage());
            throw new Exception("Terjadi kesalahan saat memperbarui data unit kerja.");
        }
    }

    public function delete(int|string $id): bool
    {
        try {
            return $this->repository->delete($id);
        } catch (Exception $e) {
            Log::error("Gagal menghapus Unit Kerja ID {$id}: " . $e->getMessage());
            throw new Exception("Data gagal dihapus. Pastikan unit kerja ini tidak sedang digunakan oleh pegawai.");
        }
    }
}