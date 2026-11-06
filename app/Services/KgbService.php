<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Helpers\TanggalHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KgbService
{
    /**
     * Fitur Otomatisasi untuk Command Scheduler (simpeg:cek-kgb)
     */
    public function generateKgbOtomatis(): int
    {
        $jumlahDiproses = 0;
        
        $semuaPegawai = Pegawai::whereNotNull('tmt_kgb_terakhir')->get();

        foreach ($semuaPegawai as $pegawai) {
            if ($this->cekKelayakanKgb($pegawai)) {
                $tanggalAcuan = Carbon::parse($pegawai->tmt_kgb_terakhir);
                
                if ($tanggalAcuan->isSameDay(Carbon::today())) {
                    $dataSk = [
                        'tmt_baru' => Carbon::today()->format('Y-m-d'),
                    ];

                    $sukses = $this->prosesKgb($pegawai, $dataSk);
                    if ($sukses) {
                        $jumlahDiproses++;
                    }
                }
            }
        }

        return $jumlahDiproses;
    }

    /**
     * Memeriksa apakah seorang pegawai sudah berhak mendapatkan KGB baru atau jatuh tempo dalam radar 6 bulan ke depan.
     */
    public function cekKelayakanKgb(Pegawai $pegawai, int $radarBulan = 6): bool
    {
        // 1. Cek tanggal kgb_berikutnya
        if ($pegawai->kgb_berikutnya) {
            $tglKgb = Carbon::parse($pegawai->kgb_berikutnya);
            if ($tglKgb->lte(Carbon::now()->addMonths($radarBulan)->endOfDay())) {
                return true;
            }
        }

        // 2. Cek selisih waktu dari TMT KGB terakhir
        $tanggalAcuan = $pegawai->tmt_kgb_terakhir;
        if (!$tanggalAcuan) {
            return false;
        }

        $tglTmt = Carbon::parse($tanggalAcuan);
        return $tglTmt->diffInMonths(Carbon::now()) >= (24 - $radarBulan);
    }

    /**
     * Eksekusi proses Kenaikan Gaji Berkala (KGB)
     */
    public function prosesKgb(Pegawai $pegawai, array $datask): bool
    {
        $tmtBaru = $datask['tmt_kgb_baru'] ?? $datask['tmt_baru'] ?? null;
        if (!$tmtBaru) return false;

        return DB::transaction(function () use ($pegawai, $tmtBaru, $datask) {
            $updateData = [
                'tmt_kgb_terakhir' => $tmtBaru,
                'kgb_berikutnya'   => Carbon::parse($tmtBaru)->addYears(2)->toDateString(),
            ];

            if (!empty($datask['gaji_pokok_baru']) && \Illuminate\Support\Facades\Schema::hasColumn('pegawai', 'gaji_pokok')) {
                $updateData['gaji_pokok'] = $datask['gaji_pokok_baru'];
            }

            if (!empty($datask['nomor_sk']) && \Illuminate\Support\Facades\Schema::hasColumn('pegawai', 'nomor_sk_kgb_terakhir')) {
                $updateData['nomor_sk_kgb_terakhir'] = $datask['nomor_sk'];
            }

            if (!empty($datask['file_sk']) && \Illuminate\Support\Facades\Schema::hasColumn('pegawai', 'file_sk_kgb_terakhir')) {
                $updateData['file_sk_kgb_terakhir'] = $datask['file_sk'];
            }

            $pegawai->update($updateData);

            \Illuminate\Support\Facades\Cache::forget('dashboard_reminder');
            \Illuminate\Support\Facades\Cache::forget('dashboard_workflow_stats');

            return true;
        });
    }
}