<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Services\KgbService;
use Illuminate\Http\Request;

class KgbController extends Controller
{
    protected KgbService $kgbService;

    // Suntikkan KgbService
    public function __construct(KgbService $kgbService)
    {
        $this->kgbService = $kgbService;
    }

    /**
     * Menampilkan daftar pegawai yang layak mendapatkan Kenaikan Gaji Berkala
     */
    public function index(Request $request)
    {
        $allPegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])
            ->where('status_pegawai', 'Aktif')
            ->get();

        $filter = $request->query('filter', 'all');

        $hariIni = \Carbon\Carbon::now()->startOfDay();
        $hariTarget = \Carbon\Carbon::now()->addMonths(3)->endOfDay();

        // 1. Pegawai Jatuh Tempo 3 Bulan ke Depan (Sesuai Dashboard Reminder)
        $reminderKgb = $allPegawai->filter(function ($p) use ($hariIni, $hariTarget) {
            if (!$p->kgb_berikutnya) return false;
            $tgl = \Carbon\Carbon::parse($p->kgb_berikutnya);
            return $tgl->between($hariIni, $hariTarget);
        })->sortBy('kgb_berikutnya')->values();

        // 2. Pegawai Lewat Waktu (Overdue / Belum Diproses dari Periode Sebelumnya)
        $overdueKgb = $allPegawai->filter(function ($p) use ($hariIni) {
            if ($p->kgb_berikutnya && \Carbon\Carbon::parse($p->kgb_berikutnya)->lt($hariIni)) {
                return true;
            }
            if ($p->tmt_kgb_terakhir && \Carbon\Carbon::parse($p->tmt_kgb_terakhir)->diffInYears(\Carbon\Carbon::now()) >= 2) {
                if ($p->kgb_berikutnya && \Carbon\Carbon::parse($p->kgb_berikutnya)->gt($hariIni)) {
                    return false;
                }
                return true;
            }
            return false;
        })->sortBy('kgb_berikutnya')->values();

        // 3. Semua Pegawai Layak KGB
        $allLayakKgb = $allPegawai->filter(function ($pegawai) {
            return $this->kgbService->cekKelayakanKgb($pegawai);
        })->sortBy(function ($pegawai) {
            return $pegawai->kgb_berikutnya ?? '9999-12-31';
        })->values();

        if ($filter === 'reminder') {
            $layakKgb = $reminderKgb;
        } elseif ($filter === 'overdue') {
            $layakKgb = $overdueKgb;
        } else {
            $layakKgb = $allLayakKgb;
        }

        return view('kgb.index', compact('layakKgb', 'filter', 'reminderKgb', 'overdueKgb', 'allLayakKgb'));
    }

    /**
     * Memproses aksi Kenaikan Gaji Berkala pegawai
     */
    public function proses(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        // Validasi input TMT KGB Baru dan Gaji Pokok Baru (jika ada)
        $request->validate([
            'tmt_kgb_baru' => 'required|date',
            'gaji_pokok_baru' => 'nullable|numeric|min:0'
        ]);

        // Eksekusi perubahan via KgbService
        $this->kgbService->prosesKgb($pegawai, [
            'tmt_kgb_baru' => $request->tmt_kgb_baru,
            'gaji_pokok_baru' => $request->gaji_pokok_baru
        ]);

        return redirect()->back()->with('success', 'Kenaikan Gaji Berkala (KGB) untuk ' . $pegawai->nama . ' berhasil diproses.');
    }
}