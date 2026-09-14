<?php

namespace App\Services;

use App\Repositories\Contracts\GolonganRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class GolonganService
{
    protected $golonganRepo;

    public function __construct(GolonganRepositoryInterface $golonganRepo)
    {
        $this->golonganRepo = $golonganRepo;
    }

    public function getPaginatedData(?string $search, int $perPage = 10)
    {
        return $this->golonganRepo->search($search, $perPage);
    }

    public function getAllData()
    {
        return $this->golonganRepo->all();
    }

    public function findById(int $id)
    {
        return $this->golonganRepo->find($id);
    }

    public function createData(array $data)
    {
        try {
            return $this->golonganRepo->create($data);
        } catch (Exception $e) {
            Log::error("Gagal menambahkan Golongan: " . $e->getMessage());
            throw new Exception("Terjadi kesalahan saat menyimpan data golongan.");
        }
    }

    public function updateData(int $id, array $data)
    {
        try {
            return $this->golonganRepo->update($id, $data);
        } catch (Exception $e) {
            Log::error("Gagal memperbarui Golongan ID {$id}: " . $e->getMessage());
            throw new Exception("Terjadi kesalahan saat memperbarui data golongan.");
        }
    }

    public function deleteData(int $id)
    {
        try {
            return $this->golonganRepo->delete($id);
        } catch (Exception $e) {
            Log::error("Gagal menghapus Golongan ID {$id}: " . $e->getMessage());
            throw new Exception("Data gagal dihapus. Pastikan data tidak berelasi dengan tabel pegawai.");
        }
    }
}