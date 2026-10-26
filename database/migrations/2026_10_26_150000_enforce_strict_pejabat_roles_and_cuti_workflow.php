<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pastikan pemisahan wewenang (Segregation of Duties) pada tabel users & pegawai:
     * - Pejabat berjenjang (Ka Pokja Keu-Kepeg, Kepala Bagian Umum, Wakil Dekan II, Dekan)
     *   memiliki role 'pimpinan' (BUKAN 'admin'), sehingga tidak terjadi tumpang tindih
     *   wewenang antara Admin Screening, Paraf Ka Pokja, Pertimbangan/Paraf Kabag, dan TTE Pimpinan.
     */
    public function up(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('users') || !Schema::hasTable('pegawai')) {
            return;
        }

        // 1. Pastikan role 'pimpinan' dan 'admin' tersedia
        $roleAdminId = DB::table('roles')->where('name', 'admin')->value('id');
        $rolePimpinanId = DB::table('roles')->where('name', 'pimpinan')->value('id');

        if (!$rolePimpinanId) {
            $rolePimpinanId = DB::table('roles')->insertGetId([
                'name'         => 'pimpinan',
                'display_name' => 'Pimpinan',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // 2. Pastikan jabatan dan role_id untuk 4 pejabat berjenjang utama sesuai wewenangnya (bukan role admin)
        $pejabatMappings = [
            // Tahap 2: Ka Pokja Keu-Kepeg
            '197605212014092001' => [
                'jabatan_likes' => ['%Pokja Keuangan%', '%Ka Pokja Keu%'],
                'default_jab'   => 'Ketua Pokja Keuangan dan Kepegawaian',
            ],
            // Tahap 3: Kepala Bagian Umum
            '197507122005011002' => [
                'jabatan_likes' => ['%Kepala Bagian Umum%', '%Kabag Umum%'],
                'default_jab'   => 'Kepala Bagian Umum',
            ],
            // Tahap 4: Wakil Dekan II
            '198509092014041001' => [
                'jabatan_likes' => ['%Wakil Dekan II%', '%Wakil Dekan Keuangan%'],
                'default_jab'   => 'Wakil Dekan II (Bid. Keuangan dan Umum)',
            ],
            // Tahap 4: Dekan
            '197508222001122001' => [
                'jabatan_likes' => ['Dekan'],
                'default_jab'   => 'Dekan',
            ],
        ];

        foreach ($pejabatMappings as $nipKey => $cfg) {
            $nip = (string) $nipKey;
            $pegawai = DB::table('pegawai')->where('nip', $nip)->first();
            if (!$pegawai) {
                continue;
            }

            if (Schema::hasTable('jabatan')) {
                $jabatanId = null;
                foreach ($cfg['jabatan_likes'] as $pattern) {
                    $q = DB::table('jabatan');
                    if ($pattern === 'Dekan') {
                        $q->where('nama_jabatan', 'Dekan');
                    } else {
                        $q->where('nama_jabatan', 'like', $pattern);
                    }
                    $jabatanId = $q->value('id');
                    if ($jabatanId) {
                        break;
                    }
                }

                if ($jabatanId && (int) $pegawai->jabatan_id !== (int) $jabatanId) {
                    DB::table('pegawai')->where('id', $pegawai->id)->update(['jabatan_id' => $jabatanId]);
                }
            }

            // Pastikan akun user pejabat ini bertaut ke pegawai_id dan menggunakan role 'pimpinan' (bukan 'admin')
            DB::table('users')
                ->where(function ($q) use ($pegawai, $nip) {
                    $q->where('pegawai_id', $pegawai->id)
                      ->orWhere('email', $nip . '@simpeg.test')
                      ->orWhere('name', $nip);
                })
                ->update([
                    'pegawai_id' => $pegawai->id,
                    'role_id'    => $rolePimpinanId,
                ]);
        }

        // 3. Pastikan akun murni Administrator (admin@simpeg.test) tidak tertaut ke pegawai pejabat struktural
        if ($roleAdminId) {
            DB::table('users')
                ->where('email', 'admin@simpeg.test')
                ->update([
                    'role_id'    => $roleAdminId,
                    'pegawai_id' => null,
                ]);
        }
    }

    public function down(): void
    {
        // Non-destructive migration
    }
};
