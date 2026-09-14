<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pegawai;
use App\Models\User;
use App\Notifications\KgbDueDateNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CekKgbHarian extends Command
{
    protected $signature = 'simpeg:cek-kgb';
    protected $description = 'Mengecek jatuh tempo KGB harian pegawai (radar 3 bulan ke depan) dan mengirimkan notifikasi ke dashboard';

    public function handle()
    {
        $hariIni = Carbon::today()->format('Y-m-d');
        $targetDate = Carbon::today()->addDays(90)->format('Y-m-d');

        $this->info("Memulai pengecekan Kenaikan Gaji Berkala (KGB) per tanggal: {$hariIni}");

        try {
            $paraPegawai = Pegawai::where('status_pegawai', 'Aktif')
                ->whereDate('kgb_berikutnya', '<=', $targetDate)
                ->get();

            if ($paraPegawai->isEmpty()) {
                $this->info('Tidak ada pegawai yang memasuki periode KGB saat ini.');
                return Command::SUCCESS;
            }

            $count = $paraPegawai->count();
            $this->info("Ditemukan {$count} pegawai yang masuk periode KGB. Mengirim notifikasi...");

            // Kirim notifikasi ke semua user terdaftar di tabel users secara aman
            $users = User::all();

            foreach ($users as $user) {
                if (class_exists(KgbDueDateNotification::class)) {
                    $user->notify(new KgbDueDateNotification($paraPegawai));
                }
            }

            $this->info("✅ Notifikasi KGB berhasil dikirimkan ke {$users->count()} pengguna.");
            Log::info("SIMPEG: Notifikasi KGB harian berhasil diproses untuk {$count} pegawai.");

        } catch (\Exception $e) {
            $this->error("❌ Terjadi kesalahan pada CekKgbHarian: " . $e->getMessage());
            Log::error("SIMPEG ERROR [CekKgbHarian]: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}