<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Models\User;
use App\Services\KpService;
use App\Notifications\KpDueDateNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CekKpHarian extends Command
{
    protected $signature = 'simpeg:cek-kp';
    protected $description = 'Memeriksa data pegawai yang memasuki TMT Kenaikan Pangkat (KP) otomatis (radar 3 bulan) & mengirim notifikasi';

    protected $kpService;

    public function __construct(KpService $kpService)
    {
        parent::__construct();
        $this->kpService = $kpService;
    }

    public function handle()
    {
        $hariIni = Carbon::today()->format('Y-m-d');
        $targetDate = Carbon::today()->addDays(90)->format('Y-m-d');
        
        $this->info("Memulai pengecekan Kenaikan Pangkat (KP) untuk tanggal: {$hariIni}");

        $paraPegawai = Pegawai::where('status_pegawai', 'Aktif')
            ->whereDate('kp_berikutnya', '<=', $targetDate)
            ->get();

        if ($paraPegawai->isEmpty()) {
            $this->info('Tidak ada pegawai yang terjadwal naik pangkat dalam radar 3 bulan ke depan.');
            return Command::SUCCESS;
        }

        $this->info('Ditemukan ' . $paraPegawai->count() . ' pegawai. Memproses...');

        $processedPegawai = collect();

        foreach ($paraPegawai as $pegawai) {
            try {
                if (Carbon::parse($pegawai->kp_berikutnya)->isPast() || Carbon::parse($pegawai->kp_berikutnya)->isToday()) {
                    $this->kpService->prosesKenaikanPangkatOtomatis($pegawai);
                }

                $processedPegawai->push($pegawai);

                $namaPegawai = $pegawai->nama_lengkap ?? $pegawai->nama ?? 'Pegawai';
                $this->line("✅ Pegawai [{$pegawai->nip}] {$namaPegawai} masuk radar KP ({$pegawai->kp_berikutnya}).");
                Log::info("SIMPEG: Pegawai NIP {$pegawai->nip} otomatis masuk radar Kenaikan Pangkat.");
                
            } catch (\Exception $e) {
                $this->error("❌ Gagal memproses pegawai NIP {$pegawai->nip}: " . $e->getMessage());
                Log::error("SIMPEG ERROR: Gagal memproses KP NIP {$pegawai->nip}. Pesan: " . $e->getMessage());
            }
        }

        if ($processedPegawai->isNotEmpty()) {
            // Ambil seluruh user agar aman tanpa dependensi kolom role
            $users = User::all();

            foreach ($users as $user) {
                if (class_exists(KpDueDateNotification::class)) {
                    $user->notify(new KpDueDateNotification($processedPegawai));
                }
            }

            $this->info("✅ Notifikasi dashboard KP berhasil dikirim.");
        }

        $this->info('Pengecekan Kenaikan Pangkat selesai.');
        return Command::SUCCESS;
    }
}