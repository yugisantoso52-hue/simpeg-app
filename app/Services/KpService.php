<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Golongan;
use App\Helpers\TanggalHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KpService
{
    /**
     * Cek apakah pegawai sudah layak naik pangkat (>= 4 tahun atau jatuh tempo dalam radar 6 bulan ke depan)
     */
    public function cekKelayakanKp(Pegawai $pegawai, int $radarBulan = 6): bool
    {
        // 1. Cek jika tanggal kp_berikutnya sudah ada dan jatuh tempo dalam radar bulan ke depan atau sudah lewat
        if ($pegawai->kp_berikutnya) {
            $tglKp = Carbon::parse($pegawai->kp_berikutnya);
            if ($tglKp->lte(Carbon::now()->addMonths($radarBulan)->endOfDay())) {
                return true;
            }
        }

        // 2. Cek selisih tahun dari TMT pangkat terakhir / tanggal masuk
        $tanggalAcuan = $pegawai->tmt_pangkat_terakhir ?? $pegawai->tanggal_masuk;

        if (!$tanggalAcuan) {
            return false;
        }

        $tanggalStr = is_string($tanggalAcuan) ? $tanggalAcuan : $tanggalAcuan->format('Y-m-d');
        $tglTmt = Carbon::parse($tanggalStr);
        return $tglTmt->diffInMonths(Carbon::now()) >= (48 - $radarBulan);
    }

    /**
     * Eksekusi Kenaikan Pangkat dan otomatis perbarui golongan pegawai serta riwayat pangkatnya
     */
    public function prosesKp(Pegawai $pegawai, array $data): bool
    {
        return DB::transaction(function () use ($pegawai, $data) {
            // 1. Nonaktifkan riwayat pangkat lama jika ada
            if (method_exists($pegawai, 'riwayatPangkat')) {
                $pegawai->riwayatPangkat()->where('status', 'aktif')->update(['status' => 'nonaktif']);

                // 2. Buat riwayat pangkat baru
                $pegawai->riwayatPangkat()->create([
                    'golongan_id' => $data['golongan_baru_id'],
                    'tmt'         => $data['tmt_pangkat_baru'],
                    'status'      => 'aktif'
                ]);
            }

            // 3. Update data induk pegawai
            $pegawai->update([
                'golongan_id'          => $data['golongan_baru_id'],
                'tmt_pangkat_terakhir' => $data['tmt_pangkat_baru'],
                'kp_berikutnya'        => Carbon::parse($data['tmt_pangkat_baru'])->addYears(4)->toDateString(),
            ]);

            return true;
        });
    }

    /**
     * Proses otomatisasi Kenaikan Pangkat untuk Command Scheduler (simpeg:cek-kp)
     */
    public function prosesKenaikanPangkatOtomatis(Pegawai $pegawai): bool
    {
        return DB::transaction(function () use ($pegawai) {
            $tmtBaru = $pegawai->kp_berikutnya 
                ? (is_string($pegawai->kp_berikutnya) ? $pegawai->kp_berikutnya : $pegawai->kp_berikutnya->format('Y-m-d'))
                : ($pegawai->tmt_pangkat_terakhir ? Carbon::parse($pegawai->tmt_pangkat_terakhir)->addYears(4)->format('Y-m-d') : date('Y-m-d'));

            $golonganSaatIniId = $pegawai->golongan_id;
            $golonganBerikutnyaId = $golonganSaatIniId ? Golongan::where('id', '>', $golonganSaatIniId)->min('id') : null;
            $targetGolonganId = $golonganBerikutnyaId ?? $golonganSaatIniId;

            if ($targetGolonganId) {
                return $this->prosesKp($pegawai, [
                    'golongan_baru_id' => $targetGolonganId,
                    'tmt_pangkat_baru' => $tmtBaru,
                ]);
            }

            return false;
        });
    }
}