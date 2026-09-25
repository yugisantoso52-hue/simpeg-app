<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\UnitKerja;
use App\Models\Jabatan;

echo "======================================================================\n";
echo "1. MASTER DATA: UNIT KERJA (" . UnitKerja::count() . " Total)\n";
echo "======================================================================\n";
foreach (UnitKerja::orderBy("id")->get() as $u) {
    echo sprintf("ID: %-3d | KODE: %-12s | NAMA: %-40s | KET: %s\n", $u->id, $u->kode_unit, $u->nama_unit, $u->keterangan);
}

echo "\n======================================================================\n";
echo "2. MASTER DATA: JABATAN (" . Jabatan::count() . " Total)\n";
echo "======================================================================\n";
foreach (Jabatan::orderBy("id")->get() as $j) {
    echo sprintf("ID: %-3d | KODE: %-12s | NAMA: %-45s | KET: %s\n", $j->id, $j->kode_jabatan, $j->nama_jabatan, $j->keterangan);
}

echo "\n======================================================================\n";
echo "3. MASTER DATA: JENIS JABATAN (" . DB::table("jenis_jabatan")->count() . " Total)\n";
echo "======================================================================\n";
foreach (DB::table("jenis_jabatan")->orderBy("id")->get() as $jj) {
    $nama = $jj->nama_jenis_jabatan ?? $jj->nama ?? "-";
    $ket = $jj->keterangan ?? "-";
    echo sprintf("ID: %-3d | NAMA: %-35s | KET: %s\n", $jj->id, $nama, $ket);
}
