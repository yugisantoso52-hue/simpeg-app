<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pk = \App\Models\PengajuanKarir::with('pegawai')->first();
if ($pk) {
    echo "ID: {$pk->id}\n";
    echo "Jenis: {$pk->jenis_pengajuan}\n";
    echo "Pegawai: " . ($pk->pegawai?->nama ?? 'N/A') . " (ID: {$pk->pegawai_id})\n";
    echo "file_sk_terakhir: [{$pk->file_sk_terakhir}]\n";
    echo "file_sk_pangkat_terakhir: [{$pk->file_sk_pangkat_terakhir}]\n";
    echo "file_sk_kgb_terakhir: [{$pk->file_sk_kgb_terakhir}]\n";
    echo "file_skp_1: [{$pk->file_skp_1}]\n";
    echo "file_skp_2: [{$pk->file_skp_2}]\n";
    echo "file_pendukung: [{$pk->file_pendukung}]\n";

    $pegawai = $pk->pegawai;
    if ($pegawai) {
        echo "\nPegawai Master Data:\n";
        echo "file_sk_pangkat_terakhir: [{$pegawai->file_sk_pangkat_terakhir}]\n";
        echo "file_sk_kgb_terakhir: [{$pegawai->file_sk_kgb_terakhir}]\n";
        echo "file_sk_pertama: [{$pegawai->file_sk_pertama}]\n";
        echo "Riwayat Pangkat Count: " . $pegawai->riwayatPangkat()->count() . "\n";
        echo "Riwayat SKP Count: " . $pegawai->riwayatSkp()->count() . "\n";
        foreach ($pegawai->riwayatSkp as $skp) {
            echo "  SKP Tahun: {$skp->tahun} | Evaluasi: [{$skp->file_evaluasi_skp}] | Rencana: [{$skp->file_rencana_skp}]\n";
        }
    }
}
