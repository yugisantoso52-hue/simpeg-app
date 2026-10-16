<?php

namespace App\Services\Approval\Resolvers;

use App\Models\Pegawai;
use App\Services\Approval\ApprovalResolverInterface;

abstract class AbstractApprovalResolver implements ApprovalResolverInterface
{
    /**
     * Cache in-memory per siklus request untuk pencarian pegawai berdasarkan jabatan
     */
    protected static array $memoizedJabatanPegawai = [];

    /**
     * Helper cari Pegawai berdasarkan list nama jabatan (dengan in-memory memoization)
     */
    public function findPegawaiByJabatan(array $jabatanNames): ?Pegawai
    {
        $cacheKey = implode('|', $jabatanNames);
        if (array_key_exists($cacheKey, self::$memoizedJabatanPegawai)) {
            return self::$memoizedJabatanPegawai[$cacheKey];
        }

        foreach ($jabatanNames as $name) {
            $pegawai = Pegawai::with(['jabatan', 'unitKerja'])
                ->whereHas('jabatan', function ($q) use ($name) {
                    $q->where('nama_jabatan', 'like', "%{$name}%");
                })->first();

            if ($pegawai) {
                return self::$memoizedJabatanPegawai[$cacheKey] = $pegawai;
            }
        }

        return self::$memoizedJabatanPegawai[$cacheKey] = null;
    }

    /**
     * Helper cari Pegawai berdasarkan kata kunci unit kerja
     */
    public function findPegawaiByUnit(string $unitKeyword): ?Pegawai
    {
        return Pegawai::with(['jabatan', 'unitKerja'])
            ->whereHas('unitKerja', function ($q) use ($unitKeyword) {
                $q->where('nama_unit', 'like', "%{$unitKeyword}%")
                  ->orWhere('nama_unit_kerja', 'like', "%{$unitKeyword}%");
            })->first();
    }

    /**
     * Reset memoized in-memory cache jika dibutuhkan (misal saat testing)
     */
    public static function clearMemoizedCache(): void
    {
        self::$memoizedJabatanPegawai = [];
    }

    /**
     * Default implementation untuk resolver yang tidak menangani cuti tahap 1 secara spesifik
     */
    public function resolveAtasanLangsungCuti(Pegawai $pegawai): ?Pegawai
    {
        return null;
    }

    /**
     * Default implementation untuk resolver yang tidak menangani PYBMC cuti secara spesifik
     */
    public function resolvePybmcCuti(Pegawai $pegawai, ?string $jenisCuti = null): ?Pegawai
    {
        return null;
    }
}
