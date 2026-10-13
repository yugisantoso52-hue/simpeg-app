<?php

namespace App\Console\Commands;

use App\Models\RiwayatStrSip;
use App\Models\User;
use App\Notifications\StrSipExpiringNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CekStrSipKadaluarsa extends Command
{
    protected $signature = 'simpeg:cek-str-sip';
    protected $description = 'Memeriksa masa berlaku STR & SIP Dosen/Ners (peringatan dini 6 bulan, 3 bulan, dan kedaluwarsa)';

    public function handle()
    {
        $today = Carbon::today();
        $warningThreshold = Carbon::today()->addDays(180); // 6 bulan ke depan

        $this->info("Memulai evaluasi masa berlaku STR & SIP Perawat/Dosen per: " . $today->toDateString());

        // Cari STR/SIP bukan seumur hidup yang tanggal berakhirnya sudah lewat atau <= 180 hari
        $docs = RiwayatStrSip::with(['pegawai.user'])
            ->where('is_seumur_hidup', false)
            ->whereNotNull('tanggal_berakhir')
            ->where('tanggal_berakhir', '<=', $warningThreshold->toDateString())
            ->get();

        if ($docs->isEmpty()) {
            $this->info("✅ Semua STR dan SIP tenaga dosen/klinis dalam keadaan aman (aktif dan tidak ada yang mendekati jatuh tempo).");
            return Command::SUCCESS;
        }

        $this->warn("Ditemukan {$docs->count()} dokumen STR / SIP yang membutuhkan perhatian khusus.");

        $expiringCollection = collect();

        foreach ($docs as $doc) {
            $pegawai = $doc->pegawai;
            $nama = $pegawai ? ($pegawai->nama_lengkap ?? $pegawai->nama) : 'Pegawai Tidak Dikenal';
            $tglBerakhir = Carbon::parse($doc->tanggal_berakhir);
            $sisaHari = (int) $today->diffInDays($tglBerakhir, false);

            if ($sisaHari < 0) {
                $statusKet = "KEDALUWARSA (" . abs($sisaHari) . " hari lalu)";
                // Update status model jika belum kedaluwarsa
                if ($doc->status !== 'Kedaluwarsa' && $doc->status !== 'Dalam Proses Perpanjangan') {
                    $doc->update(['status' => 'Kedaluwarsa']);
                }
            } elseif ($sisaHari <= 90) {
                $statusKet = "KRITIS (Sisa {$sisaHari} hari / < 3 bulan)";
            } else {
                $statusKet = "PERINGATAN (Sisa {$sisaHari} hari / < 6 bulan)";
            }

            $this->line("⚠️ [{$doc->jenis_dokumen}] {$doc->nomor_registrasi} - {$nama}: {$statusKet}");
            Log::warning("SIMPEG STR/SIP ALERT: [{$doc->jenis_dokumen}] {$doc->nomor_registrasi} milik {$nama} {$statusKet}. Tanggal berakhir: {$doc->tanggal_berakhir->format('d-m-Y')}");

            $expiringCollection->push($doc);

            // Beritahu pegawai yang bersangkutan secara personal jika memiliki akun user
            if ($pegawai && $pegawai->user) {
                $pegawai->user->notify(new StrSipExpiringNotification(collect([$doc])));
            }
        }

        // Kirim notifikasi rekap ke jajaran Admin & Pengelola Kepegawaian
        $admins = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['admin', 'hrd', 'superadmin', 'pimpinan', 'executive_kepegawaian']);
        })->get();

        if ($admins->isEmpty()) {
            $admins = User::all();
        }

        foreach ($admins as $admin) {
            $admin->notify(new StrSipExpiringNotification($expiringCollection));
        }

        $this->info("✅ Peringatan dini STR & SIP berhasil dikirimkan ke pengelola dan dosen terkait.");

        return Command::SUCCESS;
    }
}
