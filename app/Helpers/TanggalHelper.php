<?php

namespace App\Helpers;

use Carbon\Carbon;
use Log;

class TanggalHelper
{
    public static function hitungSelisihTahun(string $tanggalAwal, ?string $tanggalAkhir = null): int
    {
        try {
            $awal = Carbon::parse($tanggalAwal);
            $akhir = $tanggalAkhir ? Carbon::parse($tanggalAkhir) : Carbon::now();
            return (int) $awal->diffInYears($akhir);
        } catch (\Exception $e) {
            Log::error("Gagal menghitung selisih tahun: " . $e->getMessage());
            return 0; // Kembalikan nilai aman
        }
    }

    public static function formatIndonesia(?string $tanggal): string
    {
        if (!$tanggal) return '-';
        return Carbon::parse($tanggal)->translatedFormat('d F Y');
    }
}