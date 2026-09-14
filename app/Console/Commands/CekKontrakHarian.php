<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CekKontrakHarian extends Command
{
    protected $signature = 'simpeg:cek-kontrak';
    protected $description = 'Memeriksa masa kontrak pegawai PHL / Honorer yang akan berakhir dalam 30 hari ke depan';

    public function handle()
    {
        $hariIni = Carbon::today();
        $batasH30Hari = Carbon::today()->addDays(30);

        $this->info("Memulai pengecekan masa kontrak pegawai PHL per tanggal: " . $hariIni->toDateString());

        $paraPhl = Pegawai::where('status_pegawai', 'Aktif')
            ->whereIn('jenis_pegawai', ['PHL', 'Honorer'])
            ->whereNotNull('tanggal_kontrak_selesai')
            ->get();

        $kontrakMendekati = collect();

        foreach ($paraPhl as $pegawai) {
            $tglSelesai = Carbon::parse($pegawai->tanggal_kontrak_selesai);

            if ($tglSelesai->between($hariIni, $batasH30Hari)) {
                $sisaHari = $hariIni->diffInDays($tglSelesai, false);
                $pegawai->sisa_hari_kontrak = $sisaHari;
                $kontrakMendekati->push($pegawai);

                $nama = $pegawai->nama_lengkap ?? $pegawai->nama;
                $this->line("⚠️ [{$pegawai->nip}] {$nama} - Kontrak berakhir pada {$tglSelesai->format('d-m-Y')} (Sisa {$sisaHari} hari)");
            }
        }

        if ($kontrakMendekati->isNotEmpty()) {
            $this->info("✅ Ditemukan " . $kontrakMendekati->count() . " pegawai PHL dengan kontrak mendekati masa berakhir.");
            Log::info("SIMPEG: Pengecekan kontrak PHL menemukan {$kontrakMendekati->count()} pegawai mendekati masa berakhir.");
        } else {
            $this->info("Tidak ada pegawai PHL yang kontraknya berakhir dalam 30 hari ke depan.");
        }

        return Command::SUCCESS;
    }
}
