<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Models\User;
use App\Notifications\PensiunDueDateNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CekPensiunHarian extends Command
{
    protected $signature = 'simpeg:cek-pensiun';
    protected $description = 'Memeriksa pegawai memasuki Batas Usia Pensiun (BUP 58, 60, 65, 70) dalam radar 3 bulan ke depan';

    public function handle()
    {
        $hariIni = Carbon::today();
        $batasH3Bulan = Carbon::today()->addMonths(3);

        $this->info("Memulai evaluasi Batas Usia Pensiun (BUP) Pegawai per tanggal: " . $hariIni->toDateString());

        $paraPegawai = Pegawai::where('status_pegawai', 'Aktif')->get();
        $pensiunPegawai = collect();

        foreach ($paraPegawai as $pegawai) {
            if (!$pegawai->tanggal_lahir) continue;

            $tglLahir = Carbon::parse($pegawai->tanggal_lahir);
            $bup = $this->hitungBup($pegawai);

            // Hitung Tanggal Jatuh Tempo Pensiun Resmi
            $tglPensiun = $tglLahir->copy()->addYears($bup);

            // Cek jika TGL PENSIUN masuk dalam rentang HARI INI s/d 3 BULAN KE DEPAN
            if ($tglPensiun->between($hariIni, $batasH3Bulan)) {
                $pegawai->bup_tahun = $bup;
                $pegawai->tgl_pensiun = $tglPensiun->format('Y-m-d');
                $pensiunPegawai->push($pegawai);

                $nama = $pegawai->nama_lengkap ?? $pegawai->nama;
                $this->line("⚠️ [{$pegawai->nip}] {$nama} - Memasuki Masa Pensiun BUP {$bup} Tahun ({$tglPensiun->format('d-m-Y')})");
            }
        }

        if ($pensiunPegawai->isNotEmpty()) {
            $admins = User::whereHas('role', function ($q) {
                $q->whereIn('name', ['admin', 'hrd', 'superadmin', 'pimpinan']);
            })->get();
            if ($admins->isEmpty()) $admins = User::all();

            foreach ($admins as $admin) {
                if (class_exists(PensiunDueDateNotification::class)) {
                    $admin->notify(new PensiunDueDateNotification($pensiunPegawai));
                }
            }
            $this->info("✅ Notifikasi radar pensiun dikirim ke Admin/Pengelola.");
        } else {
            $this->info("Tidak ada pegawai yang masuk radar persiapan pensiun dalam 3 bulan ke depan.");
        }

        return Command::SUCCESS;
    }

    /**
     * Menentukan Batas Usia Pensiun (BUP) sesuai Ketentuan BKN
     */
    private function hitungBup($pegawai)
    {
        $jabatan = strtolower($pegawai->jabatan ?? '');
        $jenisJabatan = strtolower($pegawai->jenis_jabatan ?? '');

        // BUP 70 Tahun
        if (str_contains($jabatan, 'profesor') || str_contains($jabatan, 'guru besar') || 
            str_contains($jabatan, 'peneliti ahli utama') || str_contains($jabatan, 'perekayasa ahli utama')) {
            return 70;
        }

        // BUP 65 Tahun
        if (str_contains($jabatan, 'dosen') || str_contains($jabatan, 'ahli utama')) {
            return 65;
        }

        // BUP 60 Tahun
        if (str_contains($jabatan, 'guru') || str_contains($jabatan, 'ahli madya') || 
            str_contains($jenisJabatan, 'pimpinan tinggi') || str_contains($jenisJabatan, 'jpt')) {
            return 60;
        }

        // Default: BUP 58 Tahun (Administrasi, Ahli Pertama, Ahli Muda, Keterampilan)
        return 58;
    }
}