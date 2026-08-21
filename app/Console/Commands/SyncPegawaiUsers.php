<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SyncPegawaiUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pegawai:sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sanitasi NIP pegawai dan sinkronisasi pembuatan akun login User Pegawai secara massal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== MEMULAI SINKRONISASI AKUN PEGAWAI ===");

        // Pastikan Master Data Unit/Jabatan/Golongan awal terisi agar tidak terjadi error relasi
        $rolePegawai = Role::where('name', 'pegawai')->first();
        if (!$rolePegawai) {
            $this->error("Role 'pegawai' tidak ditemukan di database!");
            return Command::FAILURE;
        }

        // Cek/Buat data pegawai simulasi Saramaidus jika tidak ada
        $saramaidusNip = '196906142008101001';
        $saramaidus = Pegawai::where('nip', $saramaidusNip)->first();
        if (!$saramaidus) {
            $this->info("Membuat data pegawai simulasi 'Saramaidus'...");
            $saramaidus = Pegawai::create([
                'nip' => $saramaidusNip,
                'nama' => 'Saramaidus',
                'nik' => '1234567890123456',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '1969-06-14',
                'status_pegawai' => 'Aktif',
                'unit_kerja_id' => 1,
                'jabatan_id' => 1,
                'golongan_id' => 1,
            ]);
        }

        // Cek/Buat data pegawai NIP 198006152025211060 jika tidak ada
        $prodNip = '198006152025211060';
        $prodPegawai = Pegawai::where('nip', $prodNip)->first();
        if (!$prodPegawai) {
            $this->info("Membuat data pegawai NIP 198006152025211060...");
            $prodPegawai = Pegawai::create([
                'nip' => $prodNip,
                'nama' => 'Pegawai Production Test',
                'nik' => '1234567890123457',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '1980-06-15',
                'status_pegawai' => 'Aktif',
                'unit_kerja_id' => 1,
                'jabatan_id' => 1,
                'golongan_id' => 1,
            ]);
        }

        $pegawais = Pegawai::all();
        $this->info("Jumlah pegawai di database: " . $pegawais->count());

        $cleanedCount = 0;
        $createdCount = 0;
        $updatedCount = 0;

        $summary = [];

        foreach ($pegawais as $pegawai) {
            $originalNip = $pegawai->nip;
            $cleanedNip = preg_replace('/[^0-9]/', '', $originalNip);

            // 1. Sanitasi NIP
            if ($originalNip !== $cleanedNip) {
                $pegawai->nip = $cleanedNip;
                $pegawai->save();
                $cleanedCount++;
            }

            // 2. Tentukan password default: Password
            $defaultPassword = 'Password';
            $emailTemp = $cleanedNip . '@staff.unri.ac.id';

            // 3. Sinkronisasi User
            $user = User::where('pegawai_id', $pegawai->id)
                ->orWhere('email', $emailTemp)
                ->first();

            if ($user) {
                // Update user yang sudah ada
                $user->name = $pegawai->nama;
                $user->email = $emailTemp;
                $user->pegawai_id = $pegawai->id;
                $user->role_id = $user->role_id ?? $rolePegawai->id; // Pertahankan jika sudah ada role lain
                $user->must_change_password = true; // Wajib ganti password
                $user->password = Hash::make($defaultPassword); // Reset password ke default Password
                $user->save();
                $updatedCount++;
            } else {
                // Buat user baru
                $user = User::create([
                    'name'                 => $pegawai->nama,
                    'email'                => $emailTemp,
                    'password'             => Hash::make($defaultPassword), // Set password default: Password
                    'role_id'              => $rolePegawai->id,
                    'pegawai_id'           => $pegawai->id,
                    'must_change_password' => true,
                ]);
                $createdCount++;
            }

            $summary[] = [
                'nama' => $pegawai->nama,
                'nip' => $cleanedNip,
                'dob' => $pegawai->tanggal_lahir ? Carbon::parse($pegawai->tanggal_lahir)->format('d-m-Y') : 'NULL',
                'pass' => $defaultPassword
            ];
        }

        $this->info("=== SINKRONISASI SELESAI ===");
        $this->info("Total NIP dibersihkan: {$cleanedCount}");
        $this->info("Total Akun User baru dibuat: {$createdCount}");
        $this->info("Total Akun User diperbarui: {$updatedCount}");

        // Tampilkan tabel ringkasan
        $this->table(
            ['Nama Pegawai', 'NIP (Login)', 'Tanggal Lahir', 'Password Default'],
            array_map(fn($s) => [$s['nama'], $s['nip'], $s['dob'], $s['pass']], $summary)
        );

        return Command::SUCCESS;
    }
}
