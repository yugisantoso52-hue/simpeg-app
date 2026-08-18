<?php

namespace App\Repositories\Contracts;

interface JabatanRepositoryInterface extends BaseRepositoryInterface
{
    public function search(?string $search, int $perPage = 10);
}