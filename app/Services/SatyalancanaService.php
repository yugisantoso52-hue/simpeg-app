<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Helpers\TanggalHelper;
use Carbon\Carbon;

class SatyalancanaService
{
    /**
     * Mengecek dan mengategorikan kelayakan Satyalancana Pegawai secara presisi.
     * @return string|null '10 Tahun', '20 Tahun', '30 Tahun', atau null jika belum layak/sudah dapat
     */
    public function cekKelayakanSatyalancana(Pegawai $pegawai): ?string
    {
        // 1. Ubah ke tmt_sk_pertama sesuai kolom di Model Pegawai Anda
        $tanggalAcuan = $pegawai->tmt_sk_pertama ?? $pegawai->tanggal_masuk;

        if (!$tanggalAcuan) {
            return null;
        }

        // Hitung masa kerja riil dalam satuan tahun
        $masaKerjaTahun = TanggalHelper::hitungSelisihTahun($tanggalAcuan->format('Y-m-d'));
        
        // Ambil riwayat penghargaan terakhir (normalisasi nama lengkap/warna ke '10 Tahun', '20 Tahun', '30 Tahun')
        $rawTerakhir = (string) ($pegawai->satyalancana_terakhir ?? '');
        if (str_contains($rawTerakhir, '30') || strcasecmp($rawTerakhir, 'Emas') === 0) {
            $penghargaanTerakhir = '30 Tahun';
        } elseif (str_contains($rawTerakhir, '20') || strcasecmp($rawTerakhir, 'Perak') === 0) {
            $penghargaanTerakhir = '20 Tahun';
        } elseif (str_contains($rawTerakhir, '10') || strcasecmp($rawTerakhir, 'Perunggu') === 0) {
            $penghargaanTerakhir = '10 Tahun';
        } else {
            $penghargaanTerakhir = $rawTerakhir;
        } 

        // 2. Logika Filter Berlapis (Hanya muncul jika jatuh tempo tepat di kelipatan tahunnya & belum pernah dapat kelas tersebut)
        
        // Kategori 30 Tahun
        if ($masaKerjaTahun >= 30 && $penghargaanTerakhir !== '30 Tahun') {
            return '30 Tahun';
        } 
        
        // Kategori 20 Tahun
        if ($masaKerjaTahun >= 20 && $masaKerjaTahun < 30 && !in_array($penghargaanTerakhir, ['20 Tahun', '30 Tahun'])) {
            return '20 Tahun';
        } 
        
        // Kategori 10 Tahun
        if ($masaKerjaTahun >= 10 && $masaKerjaTahun < 20 && !in_array($penghargaanTerakhir, ['10 Tahun', '20 Tahun', '30 Tahun'])) {
            return '10 Tahun';
        }

        return null;
    }
}