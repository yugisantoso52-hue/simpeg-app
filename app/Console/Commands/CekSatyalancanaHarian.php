<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Models\User;
use App\Notifications\SatyalancanaDueDateNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CekSatyalancanaHarian extends Command
{
    protected $signature = 'simpeg:cek-satyalancana';
    protected $description = 'Memeriksa pegawai yang memenuhi syarat Satyalancana (10, 20, 30 tahun) dalam radar 3 bulan ke depan';

    public function handle()
    {
        $hariIni = Carbon::today();
        $batasH3Bulan = Carbon::today()->addMonths(3);

        $this->info("Memulai pengecekan kelayakan Satyalancana Karya Satya per tanggal: " . $hariIni->toDateString());

        $paraPegawai = Pegawai::where('status_pegawai', 'Aktif')->get();
        $eligiblePegawai = collect();

        foreach ($paraPegawai as $pegawai) {
            $tmtAwal = $pegawai->tmt_sk_pertama ?? $pegawai->tanggal_masuk;
            if (!$tmtAwal) continue;

            $tmtStart = Carbon::parse($tmtAwal);
            
            // Hitung tanggal milestone 10, 20, dan 30 tahun dari TMT SK Pertama / Masuk
            foreach ([10, 20, 30] as $tahunMilestone) {
                $tglMilestone = $tmtStart->copy()->addYears($tahunMilestone);

                // Jika tanggal milestone jatuh antara HARI INI s/d 3 BULAN KE DEPAN
                if ($tglMilestone->between($hariIni, $batasH3Bulan)) {
                    $kategori = match($tahunMilestone) {
                        10 => 'Satyalancana Karya Satya 10 Tahun (Perunggu)',
                        20 => 'Satyalancana Karya Satya 20 Tahun (Perak)',
                        30 => 'Satyalancana Karya Satya 30 Tahun (Emas)',
                        default => null
                    };

                    if ($kategori) {
                        $pegawai->kategori_satyalancana = $kategori;
                        $pegawai->tgl_satyalancana = $tglMilestone->format('Y-m-d');
                        $eligiblePegawai->push($pegawai);

                        $nama = $pegawai->nama_lengkap ?? $pegawai->nama;
                        $this->line("🏅 [{$pegawai->nip}] {$nama} - Layak {$kategori} ({$tglMilestone->format('d-m-Y')})");
                    }
                }
            }
        }

        if ($eligiblePegawai->isNotEmpty()) {
            $admins = User::whereHas('role', function ($q) {
                $q->whereIn('name', ['admin', 'hrd', 'superadmin', 'pimpinan']);
            })->get();
            if ($admins->isEmpty()) $admins = User::all();

            foreach ($admins as $admin) {
                if (class_exists(SatyalancanaDueDateNotification::class)) {
                    $admin->notify(new SatyalancanaDueDateNotification($eligiblePegawai));
                }
            }
            $this->info("✅ Notifikasi kelayakan Satyalancana dikirim ke Admin.");
        } else {
            $this->info("Tidak ada pegawai yang masuk radar Satyalancana (10/20/30 tahun) dalam 3 bulan ke depan.");
        }

        return Command::SUCCESS;
    }
}