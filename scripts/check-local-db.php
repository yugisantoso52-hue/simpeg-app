<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DATA DATABASE LOKAL SIKAP ===\n";
echo "Total Pegawai: " . \App\Models\Pegawai::count() . "\n";
echo "Total Users: " . \App\Models\User::count() . "\n";
echo "Total Unit Kerja: " . \App\Models\UnitKerja::count() . "\n";
echo "Total Jabatan: " . \App\Models\Jabatan::count() . "\n";
echo "Total Golongan: " . \App\Models\Golongan::count() . "\n";

echo "\n--- DAFTAR PENGGUNA (USERS) ---\n";
foreach (\App\Models\User::with('role')->get() as $u) {
    echo "- ID: {$u->id} | Nama: {$u->name} | Email: {$u->email} | Role: " . ($u->role->name ?? '-') . " | PegawaiID: " . ($u->pegawai_id ?? '-') . "\n";
}

$rahmad = \App\Models\Pegawai::where('nip', 'like', '%198006152025211060%')->first();
if ($rahmad) {
    echo "\n--- DATA PEGAWAI RAHMAD HIDAYAT DITEMUKAN DI LOKAL ---\n";
    echo "ID: {$rahmad->id} | NIP: {$rahmad->nip} | Nama: {$rahmad->nama}\n";
} else {
    echo "\n--- NIP 198006152025211060 TIDAK DITEMUKAN DI TABEL PEGAWAI LOKAL ---\n";
}
