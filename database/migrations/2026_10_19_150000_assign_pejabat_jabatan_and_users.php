<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\Role;
use App\Models\Jabatan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Dapatkan role pimpinan & pegawai
        $rolePimpinan = DB::table('roles')->where('name', 'pimpinan')->value('id') 
            ?? DB::table('roles')->where('name', 'admin')->value('id') 
            ?? 1;
        $rolePegawai = DB::table('roles')->where('name', 'pegawai')->value('id') ?? 2;

        // 2. Hubungkan Dolli Vita Zenitha Harning Arivina (Ka Pokja Keu-Kepeg) ke jabatan ID 2
        $jabatanPokjaId = DB::table('jabatan')
            ->where('nama_jabatan', 'like', '%Pokja Keuangan%')
            ->value('id') ?? 2;

        DB::table('pegawai')
            ->where('nip', '197605212014092001')
            ->update(['jabatan_id' => $jabatanPokjaId]);

        // 3. Hubungkan Prof. Wan Nishfa Dewi (Dekan) ke jabatan ID 36 dan buat/hubungkan akun User
        $jabatanDekanId = DB::table('jabatan')
            ->where('nama_jabatan', 'Dekan')
            ->value('id') ?? 36;

        $pegawaiDekan = DB::table('pegawai')->where('nip', '197508222001122001')->first();
        if ($pegawaiDekan) {
            DB::table('pegawai')->where('id', $pegawaiDekan->id)->update(['jabatan_id' => $jabatanDekanId]);

            $userDekan = DB::table('users')->where('pegawai_id', $pegawaiDekan->id)->first();
            if (!$userDekan) {
                DB::table('users')->insert([
                    'name'                 => $pegawaiDekan->nama,
                    'email'                => '197508222001122001@simpeg.test',
                    'password'             => Hash::make('password'),
                    'role_id'              => $rolePimpinan,
                    'pegawai_id'           => $pegawaiDekan->id,
                    'must_change_password' => false,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            } else {
                DB::table('users')->where('id', $userDekan->id)->update(['role_id' => $rolePimpinan]);
            }
        }

        // 4. Hubungkan Dr. Safri (Wakil Dekan II) ke jabatan ID 38 dan buat/hubungkan akun User
        $jabatanWd2Id = DB::table('jabatan')
            ->where('nama_jabatan', 'like', '%Wakil Dekan Keuangan%')
            ->value('id') ?? 38;

        $pegawaiWd2 = DB::table('pegawai')->where('nip', '198509092014041001')->first();
        if ($pegawaiWd2) {
            DB::table('pegawai')->where('id', $pegawaiWd2->id)->update(['jabatan_id' => $jabatanWd2Id]);

            $userWd2 = DB::table('users')->where('pegawai_id', $pegawaiWd2->id)->first();
            if (!$userWd2) {
                DB::table('users')->insert([
                    'name'                 => $pegawaiWd2->nama,
                    'email'                => '198509092014041001@simpeg.test',
                    'password'             => Hash::make('password'),
                    'role_id'              => $rolePimpinan,
                    'pegawai_id'           => $pegawaiWd2->id,
                    'must_change_password' => false,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            } else {
                DB::table('users')->where('id', $userWd2->id)->update(['role_id' => $rolePimpinan]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive rollback needed
    }
};
