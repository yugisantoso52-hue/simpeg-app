<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Pegawai;
use App\Models\Jabatan;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Dapatkan referensi jabatan pimpinan
        $jbWadek1 = Jabatan::where('nama_jabatan', 'like', '%Wakil Dekan I%')->first();
        $jbWadek3 = Jabatan::where('nama_jabatan', 'like', '%Wakil Dekan III%')->first();
        $jbKajur  = Jabatan::where('nama_jabatan', 'like', '%Ketua Jurusan%')->first();

        // 2. Hubungkan Dr. Reni Zulfitri sebagai Wakil Dekan I (Bid. Akademik)
        $pWadek1 = Pegawai::where('nip', '197603092002122002')
            ->orWhere('nama', 'like', '%Reni Zulfitri%')
            ->first();

        if ($pWadek1 && $jbWadek1) {
            $pWadek1->update(['jabatan_id' => $jbWadek1->id]);

            // Buat / tautkan user account jika belum ada
            $uWadek1 = User::where('pegawai_id', $pWadek1->id)
                ->orWhere('email', '197603092002122002@staff.unri.ac.id')
                ->first();

            if (!$uWadek1) {
                User::create([
                    'name'       => $pWadek1->nama_lengkap ?? $pWadek1->nama,
                    'username'   => $pWadek1->nip,
                    'email'      => '197603092002122002@staff.unri.ac.id',
                    'password'   => Hash::make('password'),
                    'role_id'    => 3,
                    'pegawai_id' => $pWadek1->id,
                ]);
            } else {
                $uWadek1->update(['pegawai_id' => $pWadek1->id]);
            }
        }

        // 3. Hubungkan Ns. Sri Wahyuni sebagai Wakil Dekan III (Bid. Kemahasiswaan, Alumni dan Kerjasama)
        $pWadek3 = Pegawai::where('nip', '197706122003122002')
            ->orWhere('nama', 'like', '%Sri Wahyuni%')
            ->first();

        if ($pWadek3 && $jbWadek3) {
            $pWadek3->update(['jabatan_id' => $jbWadek3->id]);

            $uWadek3 = User::where('pegawai_id', $pWadek3->id)
                ->orWhere('email', '197706122003122002@staff.unri.ac.id')
                ->first();

            if (!$uWadek3) {
                User::create([
                    'name'       => $pWadek3->nama_lengkap ?? $pWadek3->nama,
                    'username'   => $pWadek3->nip,
                    'email'      => '197706122003122002@staff.unri.ac.id',
                    'password'   => Hash::make('password'),
                    'role_id'    => 3,
                    'pegawai_id' => $pWadek3->id,
                ]);
            } else {
                $uWadek3->update(['pegawai_id' => $pWadek3->id]);
            }
        }

        // 4. Pastikan hierarki atasan_id pada data pegawai terisi dengan tepat
        $pDekan  = Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Dekan%')->where('nama_jabatan', 'not like', '%Wakil%'))->first();
        $pWadek1 = Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan I%')->where('nama_jabatan', 'not like', '%Wakil Dekan II%')->where('nama_jabatan', 'not like', '%Wakil Dekan III%'))->first();
        $pWadek2 = Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan II%')->where('nama_jabatan', 'not like', '%Wakil Dekan III%'))->first();
        $pWadek3 = Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan III%'))->first();
        $pKabag  = Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Bagian Umum%'))->first();

        // Wadek II atasan = Dekan
        if ($pWadek2 && $pDekan) {
            $pWadek2->update(['atasan_id' => $pDekan->id]);
        }

        // Wadek I & Wadek III atasan = Wadek II
        if ($pWadek1 && $pWadek2) {
            $pWadek1->update(['atasan_id' => $pWadek2->id]);
        }
        if ($pWadek3 && $pWadek2) {
            $pWadek3->update(['atasan_id' => $pWadek2->id]);
        }

        // Kabag Umum atasan = Wadek II
        if ($pKabag && $pWadek2) {
            $pKabag->update(['atasan_id' => $pWadek2->id]);
        }

        // Tendik umum atasan = Kabag Umum
        if ($pKabag) {
            Pegawai::where(function ($q) {
                $q->whereNull('nidn_nuptk')->where('jenis_pegawai', '!=', 'Dosen');
            })->where('id', '!=', $pKabag->id)
              ->where('id', '!=', $pWadek2?->id)
              ->update(['atasan_id' => $pKabag->id]);
        }

        // Dosen atasan = Wadek 1 (Dr. Reni Zulfitri) jika belum ada Kajur
        if ($pWadek1) {
            Pegawai::where('jenis_pegawai', 'Dosen')
                ->whereNotIn('id', array_filter([$pDekan?->id, $pWadek1->id, $pWadek2?->id, $pWadek3?->id]))
                ->update(['atasan_id' => $pWadek1->id]);
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
