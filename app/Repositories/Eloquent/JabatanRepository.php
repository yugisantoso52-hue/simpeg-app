<?php

namespace App\Repositories\Eloquent;

use App\Models\Jabatan;
use App\Repositories\Contracts\JabatanRepositoryInterface;

class JabatanRepository extends BaseRepository implements JabatanRepositoryInterface
{
    public function __construct(Jabatan $model)
    {
        parent::__construct($model);
    }

    public function search(?string $search, int $perPage = 10)
    {
        return $this->model
            ->when($search, function ($query) use ($search) {
                $query->where('nama_jabatan', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}