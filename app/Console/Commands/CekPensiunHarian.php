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
    protected $description = 'Memeriksa pegawai memasuki Batas Usia Pensiun (BUP 58, 60, 65, 70) dalam radar 1 tahun ke depan';

    public function handle()
    {
        $hariIni = Carbon::today();
        $batasH1Tahun = Carbon::today()->addYear();

        $this->info("Memulai evaluasi Batas Usia Pensiun (BUP) Pegawai per tanggal: " . $hariIni->toDateString());

        $paraPegawai = Pegawai::where('status_pegawai', 'Aktif')->get();
        $pensiunPegawai = collect();

        foreach ($paraPegawai as $pegawai) {
            if (!$pegawai->tanggal_lahir && !$pegawai->tanggal_pensiun) continue;

            $bup = $pegawai->bup;
            $tglPensiun = $pegawai->tanggal_pensiun_calculated;

            if (!$tglPensiun) continue;

            // Cek jika TGL PENSIUN masuk dalam rentang HARI INI s/d 1 TAHUN KE DEPAN
            if ($tglPensiun->between($hariIni, $batasH1Tahun)) {
                $pegawai->bup_tahun = $bup;
                $pegawai->tgl_pensiun = $tglPensiun->format('Y-m-d');
                $pensiunPegawai->push($pegawai);

                $nama = $pegawai->nama_lengkap ?? $pegawai->nama;
                $this->line("⚠️ [{$pegawai->nip}] {$nama} - Memasuki Masa Pensiun BUP {$bup} Tahun ({$tglPensiun->format('d-m-Y')})");
            }
        }

        if ($pensiunPegawai->isNotEmpty()) {
            $admins = User::whereHas('role', function ($q) {
                $q->whereIn('name', ['admin', 'hrd', 'superadmin', 'pimpinan', 'kepegawaian']);
            })->get();
            if ($admins->isEmpty()) {
                $admins = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['admin', 'hrd', 'superadmin', 'pimpinan', 'kepegawaian']);
                })->get();
            }

            foreach ($admins as $admin) {
                if (class_exists(PensiunDueDateNotification::class)) {
                    $admin->notify(new PensiunDueDateNotification($pensiunPegawai));
                }
            }
            $this->info("✅ Notifikasi radar pensiun dikirim ke Admin/Pengelola.");
        } else {
            $this->info("Tidak ada pegawai yang masuk radar persiapan pensiun dalam 1 tahun ke depan.");
        }

        return Command::SUCCESS;
    }
}