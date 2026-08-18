<?php

namespace App\Repositories\Contracts;

interface PegawaiRepositoryInterface extends BaseRepositoryInterface
{
    public function getAktif(): int;

    public function getPensiun(): int;

    public function getKGBBulanIni(): int;

    public function getKPBulanIni(): int;
/**
 * Statistik Pegawai
 */
public function getStatistics(): array;
}