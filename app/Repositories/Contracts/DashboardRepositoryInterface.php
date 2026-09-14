<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    public function getStatistics(): array;

    public function getPegawaiPerGolongan();

    public function getPegawaiPerPendidikan();

    public function getPegawaiPerUnit();

    public function getPegawaiBaru(int $limit = 10);

    public function getReminder(): array;
}