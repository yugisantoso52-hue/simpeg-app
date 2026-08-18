<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GeneratePegawaiUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cari atau pastikan Role pegawai (misal nama role = 'pegawai')
        $rolePegawai = Role::where('name', 'pegawai')->first();

        if (!$rolePegawai) {
            $this->command->error("Role 'pegawai' tidak ditemukan di tabel roles!");
            return;
        }

        // 2. Ambil semua pegawai yang belum memiliki akun user
        $pegawais = Pegawai::whereNotIn('id', function ($query) {
            $query->select('pegawai_id')->from('users')->whereNotNull('pegawai_id');
        })->get();

        $count = 0;
        foreach ($pegawais as $pegawai) {
            $email = $pegawai->email;
            
            // Jika email kosong, generate dari NIP yang sudah dibersihkan dari spasi
            if (empty($email)) {
                $nipClean = str_replace(' ', '', trim($pegawai->nip ?? ''));
                $identifier = !empty($nipClean) ? $nipClean : 'pegawai_' . $pegawai->id;
                $email = $identifier . '@simpeg.test';
            }

            // Buat akun User baru
            User::create([
                'name'       => $pegawai->nama,
                'email'      => $email,
                'password'   => Hash::make('password123'), // Password default untuk seluruh pegawai
                'role_id'    => $rolePegawai->id,
                'pegawai_id' => $pegawai->id,
            ]);

            $count++;
        }

        $this->command->info("Berhasil membuat {$count} akun login pegawai! Password default: password123");
    }
}