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
                $processedPegawai->push($pegawai);

                $namaPegawai = $pegawai->nama_lengkap ?? $pegawai->nama ?? 'Pegawai';
                $this->line("📌 Pegawai [{$pegawai->nip}] {$namaPegawai} memasuki radar Kenaikan Pangkat ({$pegawai->kp_berikutnya}). Menunggu pengajuan & SK resmi BKN.");
                Log::info("SIMPEG: Pegawai NIP {$pegawai->nip} ({$namaPegawai}) memasuki radar periode Kenaikan Pangkat (TMT: {$pegawai->kp_berikutnya}).");
                
            } catch (\Exception $e) {
                $this->error("❌ Gagal memproses pegawai NIP {$pegawai->nip}: " . $e->getMessage());
                Log::error("SIMPEG ERROR: Gagal memproses KP NIP {$pegawai->nip}. Pesan: " . $e->getMessage());
            }
        }

        if ($processedPegawai->isNotEmpty()) {
            // Notifikasi dikirimkan terarah ke Admin & Pimpinan (bukan spam ke seluruh pengguna)
            $users = User::whereHas('role', function ($q) {
                $q->whereIn('name', ['admin', 'pimpinan', 'executive_kepegawaian']);
            })->get();
            if ($users->isEmpty()) {
                $users = User::all();
            }

            foreach ($users as $user) {
                if (class_exists(KpDueDateNotification::class)) {
                    $user->notify(new KpDueDateNotification($processedPegawai));
                }
            }

            $this->info("✅ Notifikasi radar KP berhasil dikirim ke Admin & Pimpinan.");
        }

        $this->info('Pengecekan Kenaikan Pangkat selesai.');
        return Command::SUCCESS;
    }
}