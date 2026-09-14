<?php

namespace App\Repositories\Contracts;

interface GolonganRepositoryInterface extends BaseRepositoryInterface
{
    public function search(?string $search, int $perPage = 10);
}