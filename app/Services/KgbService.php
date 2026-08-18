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
     * Memeriksa apakah seorang pegawai sudah berhak mendapatkan KGB baru.
     */
    public function cekKelayakanKgb(Pegawai $pegawai): bool
    {
        $tanggalAcuan = $pegawai->tmt_kgb_terakhir;
        
        if (!$tanggalAcuan) {
            return false;
        }

        return TanggalHelper::hitungSelisihTahun($tanggalAcuan) >= 2;
    }

    /**
     * Eksekusi proses Kenaikan Gaji Berkala (KGB)
     */
    public function prosesKgb(Pegawai $pegawai, array $datask): bool
    {
        $tmtBaru = $datask['tmt_kgb_baru'] ?? $datask['tmt_baru'] ?? null;
        if (!$tmtBaru) return false;

        return DB::transaction(function () use ($pegawai, $tmtBaru) {
            $pegawai->update([
                'tmt_kgb_terakhir' => $tmtBaru,
                'kgb_berikutnya'   => Carbon::parse($tmtBaru)->addYears(2)->toDateString(),
            ]);

            return true;
        });
    }
}