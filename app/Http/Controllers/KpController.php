<?php

namespace App\Http\Controllers;

use App\Models\Golongan;
use App\Models\Pegawai;
use App\Services\KpService;
use Illuminate\Http\Request;

class KpController extends Controller
{
    protected KpService $kpService;

    public function __construct(KpService $kpService)
    {
        $this->kpService = $kpService;
    }

    public function index(Request $request)
    {
        $allPegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan', 'riwayatSkp', 'riwayatPangkat'])
            ->where('status_pegawai', 'Aktif')
            ->get();

        $filter = $request->query('filter', 'all');

        $hariIni = \Carbon\Carbon::now()->startOfDay();
        $hariTarget = \Carbon\Carbon::now()->addMonths(3)->endOfDay();

        // 1. Pegawai Jatuh Tempo 3 Bulan ke Depan (Sesuai Dashboard Reminder)
        $reminderKp = $allPegawai->filter(function ($p) use ($hariIni, $hariTarget) {
            if (!$p->kp_berikutnya) return false;
            $tgl = \Carbon\Carbon::parse($p->kp_berikutnya);
            return $tgl->between($hariIni, $hariTarget);
        })->sortBy('kp_berikutnya')->values();

        // 2. Pegawai Lewat Waktu (Overdue / Belum Diproses dari Periode Sebelumnya)
        $overdueKp = $allPegawai->filter(function ($p) use ($hariIni) {
            if ($p->kp_berikutnya && \Carbon\Carbon::parse($p->kp_berikutnya)->lt($hariIni)) {
                return true;
            }
            $tmt = $p->tmt_pangkat_terakhir ?? $p->tanggal_masuk;
            if ($tmt && \Carbon\Carbon::parse($tmt)->diffInYears(\Carbon\Carbon::now()) >= 4) {
                if ($p->kp_berikutnya && \Carbon\Carbon::parse($p->kp_berikutnya)->gt($hariIni)) {
                    return false;
                }
                return true;
            }
            return false;
        })->sortBy('kp_berikutnya')->values();

        // 3. Semua Pegawai yang Layak Naik Pangkat
        $allLayakKp = $allPegawai->filter(function ($pegawai) {
            return $this->kpService->cekKelayakanKp($pegawai);
        })->sortBy(function ($pegawai) {
            return $pegawai->kp_berikutnya ?? '9999-12-31';
        })->values();

        if ($filter === 'reminder') {
            $layakKp = $reminderKp;
        } elseif ($filter === 'overdue') {
            $layakKp = $overdueKp;
        } else {
            $layakKp = $allLayakKp;
        }

        $golongans = Golongan::all();

        return view('kp.index', compact('layakKp', 'golongans', 'filter', 'reminderKp', 'overdueKp', 'allLayakKp'));
    }

    public function proses(Request $request, int $id)
    {
        $request->validate([
            'golongan_baru_id' => 'required|exists:golongan,id',
            'tmt_pangkat_baru' => 'required|date',
        ]);

        $pegawai = Pegawai::findOrFail($id);

        $this->kpService->prosesKp($pegawai, $request->only(['golongan_baru_id', 'tmt_pangkat_baru']));

        return back()->with('success', 'Kenaikan Pangkat untuk ' . ($pegawai->nama_lengkap ?? $pegawai->nama) . ' berhasil diproses.');
    }
}