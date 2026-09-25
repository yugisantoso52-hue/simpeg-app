<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Models\Pegawai;
use App\Models\RiwayatJabatan;

$ukPegawai = Pegawai::whereNotNull('unit_kerja_id')->pluck('unit_kerja_id')->unique()->values()->all();
$jbPegawai = Pegawai::whereNotNull('jabatan_id')->pluck('jabatan_id')->unique()->values()->all();
$jbRiwayat = RiwayatJabatan::whereNotNull('jabatan_id')->pluck('jabatan_id')->unique()->values()->all();

echo "Pegawai Unit Kerja IDs used: " . json_encode($ukPegawai) . "\n";
echo "Pegawai Jabatan IDs used: " . json_encode($jbPegawai) . "\n";
echo "Riwayat Jabatan IDs used: " . json_encode($jbRiwayat) . "\n";
