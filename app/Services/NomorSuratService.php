<?php

namespace App\Services;

class NomorSuratService
{
    /**
     * Dapatkan prefix standar institusi (e.g. UN19.5.1.1.10)
     */
    public function getPrefixUniv(): string
    {
        return (string) config('simpeg.nomor_surat.prefix_univ', 'UN19.5.1.1.10');
    }

    /**
     * Format nomor surat dinas standar secara terpusat
     *
     * @param string|null $nomorInput Nomor urut / nomor surat mentah
     * @param string|null $kode Kode klasifikasi surat (default: KP)
     * @param int|null $tahun Tahun surat (default: tahun berjalan)
     * @param bool $prefixB Apakah ditambahkan prefix "B/" (nota dinas/naskah dinas keluar)
     * @return string
     */
    public function format(?string $nomorInput, ?string $kode = null, ?int $tahun = null, bool $prefixB = false): string
    {
        $prefix = $this->getPrefixUniv();
        $kode = $kode ?: config('simpeg.nomor_surat.default_kode', 'KP');
        $tahun = $tahun ?: (int) date('Y');

        $input = trim((string) $nomorInput);

        // Jika nomor sudah terformat lengkap dengan prefix universitas, kembalikan apa adanya
        if (str_contains($input, '/UN19')) {
            return $input;
        }

        // Jika nomor input kosong, buat format placeholder titik-titik untuk cetak formulir
        if ($input === '') {
            $base = "............/{$prefix}/{$kode}/{$tahun}";
            return $prefixB ? "B/{$base}" : $base;
        }

        $inputClean = rtrim($input, '/');
        if ($prefixB) {
            $nomorFormatted = 'B/' . ltrim($inputClean, 'B/');
        } else {
            $nomorFormatted = $inputClean;
        }

        return "{$nomorFormatted}/{$prefix}/{$kode}/{$tahun}";
    }

    /**
     * Format nomor surat untuk pengajuan cuti
     */
    public function formatCuti(?string $nomorInput, ?int $tahun = null): string
    {
        $kode = config('simpeg.nomor_surat.kode_cuti', 'KP');
        return $this->format($nomorInput, $kode, $tahun, false);
    }

    /**
     * Format nomor surat untuk usulan karir (KP / KGB)
     */
    public function formatKarir(?string $nomorInput, ?int $tahun = null, bool $prefixB = true): string
    {
        $kode = config('simpeg.nomor_surat.kode_karir', 'KP');
        return $this->format($nomorInput, $kode, $tahun, $prefixB);
    }

    /**
     * Format nomor surat untuk usulan Satyalancana Karya Satya
     */
    public function formatSatyalancana(?string $nomorInput, ?int $tahun = null): string
    {
        $kode = config('simpeg.nomor_surat.kode_satyalancana', 'KP');
        return $this->format($nomorInput, $kode, $tahun, false);
    }

    /**
     * Format nomor surat dinas aktif bekerja
     */
    public function formatSuratAktif(?string $nomorInput, ?int $tahun = null): string
    {
        $kode = config('simpeg.nomor_surat.kode_aktif', 'KP');
        return $this->format($nomorInput, $kode, $tahun, false);
    }

    /**
     * Format draf nomor otomatis berbasis urutan
     */
    public function formatDraft(int $urutan, ?string $kode = null, ?int $tahun = null, bool $prefixB = true): string
    {
        $prefix = $this->getPrefixUniv();
        $kode = $kode ?: config('simpeg.nomor_surat.default_kode', 'KP');
        $tahun = $tahun ?: (int) date('Y');

        $nomorUrut = str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
        $nomorPart = $prefixB ? "B/{$nomorUrut}" : $nomorUrut;

        return "{$nomorPart}/{$prefix}/{$kode}/{$tahun}";
    }
}
