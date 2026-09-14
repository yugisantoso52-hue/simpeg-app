<?php

namespace App\Services;

use App\Repositories\Contracts\JabatanRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class JabatanService
{
    protected $jabatanRepo;

    public function __construct(JabatanRepositoryInterface $jabatanRepo)
    {
        $this->jabatanRepo = $jabatanRepo;
    }

    public function getPaginatedData(?string $search, int $perPage = 10)
    {
        return $this->jabatanRepo->search($search, $perPage);
    }

    public function getAllData()
    {
        return $this->jabatanRepo->all();
    }

    public function findById(int $id)
    {
        return $this->jabatanRepo->find($id);
    }

    public function createData(array $data)
    {
        try {
            return $this->jabatanRepo->create($data);
        } catch (Exception $e) {
            Log::error("Gagal menambahkan Jabatan: " . $e->getMessage());
            throw new Exception("Terjadi kesalahan saat menyimpan data jabatan.");
        }
    }

    public function updateData(int $id, array $data)
    {
        try {
            return $this->jabatanRepo->update($id, $data);
        } catch (Exception $e) {
            Log::error("Gagal memperbarui Jabatan ID {$id}: " . $e->getMessage());
            throw new Exception("Terjadi kesalahan saat memperbarui data jabatan.");
        }
    }

    public function deleteData(int $id)
    {
        try {
            return $this->jabatanRepo->delete($id);
        } catch (Exception $e) {
            Log::error("Gagal menghapus Jabatan ID {$id}: " . $e->getMessage());
            throw new Exception("Data gagal dihapus. Pastikan data tidak berelasi dengan tabel pegawai.");
        }
    }
}