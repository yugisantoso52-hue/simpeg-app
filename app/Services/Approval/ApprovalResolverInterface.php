<?php

namespace App\Services\Approval;

use App\Models\Pegawai;

interface ApprovalResolverInterface
{
    /**
     * Memeriksa apakah resolver ini mendukung / menangani pegawai atau jabatan yang diberikan.
     */
    public function supports(Pegawai $pegawai): bool;

    /**
     * Menyelesaikan informasi aturan persetujuan logbook / kinerja (Rule 1 s/d 20).
     */
    public function resolveApprovalRule(Pegawai $pegawai): ?array;

    /**
     * Mendapatkan daftar ID pegawai bawahan jika pegawai bertindak sebagai pimpinan/atasan.
     *
     * @return int[]|null Mengembalikan array ID bawahan, atau null jika tidak memiliki bawahan.
     */
    public function getBawahanIds(Pegawai $pimpinan): ?array;

    /**
     * Mendapatkan atasan langsung pertimbangan cuti tahap 1.
     */
    public function resolveAtasanLangsungCuti(Pegawai $pegawai): ?Pegawai;

    /**
     * Mendapatkan Pejabat Yang Berwenang Memberikan Cuti (PYBMC) tahap 2.
     */
    public function resolvePybmcCuti(Pegawai $pegawai, ?string $jenisCuti = null): ?Pegawai;
}
