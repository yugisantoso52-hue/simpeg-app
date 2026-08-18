<?php

namespace App\Repositories\Eloquent;

use App\Models\Golongan;
use App\Repositories\Contracts\GolonganRepositoryInterface;

class GolonganRepository extends BaseRepository implements GolonganRepositoryInterface
{
    public function __construct(Golongan $model)
    {
        parent::__construct($model);
    }

    public function search(?string $search, int $perPage = 10)
    {
        return $this->model
            ->when($search, function ($query) use ($search) {
                $query->where('nama_golongan', 'like', "%{$search}%")
                      ->orWhere('pangkat', 'like', "%{$search}%");
            })
            ->orderBy('nama_golongan', 'asc') // Urutan hierarki golongan biasanya dari kecil ke besar (misal: III/a ke IV/e)
            ->paginate($perPage)
            ->withQueryString();
    }
}