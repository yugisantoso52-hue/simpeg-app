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
        if (auth()->user()->role?->name === 'pimpinan') {
            abort(403, 'Akses terbatas untuk Administrator dan Pegawai Pengaju.');
        }

        if (!auth()->user()->canAccessExecutiveKepegawaianMenus()) {
            return redirect()->route('pengajuan-karir.index', ['tab' => 'kp', 'jenis' => 'KP']);
        }

        $filter = $request->query('filter', 'all');

        $hariIni = \Carbon\Carbon::now()->startOfDay();
        $hariTarget = \Carbon\Carbon::now()->addMonths(3)->endOfDay();
        $empatTahunLalu = \Carbon\Carbon::now()->subYears(4)->toDateString();
        $enamBulanKedepan = \Carbon\Carbon::now()->addMonths(6)->endOfDay()->toDateString();
        $tigaSetengahTahunLalu = \Carbon\Carbon::now()->subMonths(42)->toDateString();

        // 1. Query Pegawai Jatuh Tempo 3 Bulan ke Depan (Sesuai Dashboard Reminder)
        $reminderQuery = Pegawai::where('status_pegawai', 'Aktif')
            ->whereNotNull('kp_berikutnya')
            ->whereBetween('kp_berikutnya', [$hariIni->toDateString(), $hariTarget->toDateString()]);

        // 2. Query Pegawai Lewat Waktu (Overdue / Belum Diproses dari Periode Sebelumnya)
        $overdueQuery = Pegawai::where('status_pegawai', 'Aktif')
            ->where(function ($query) use ($hariIni, $empatTahunLalu) {
                $query->where(function ($q) use ($hariIni) {
                    $q->whereNotNull('kp_berikutnya')
                      ->where('kp_berikutnya', '<', $hariIni->toDateString());
                })->orWhere(function ($q) use ($hariIni, $empatTahunLalu) {
                    $q->where(function ($sq) use ($hariIni) {
                        $sq->whereNull('kp_berikutnya')
                           ->orWhere('kp_berikutnya', '<=', $hariIni->toDateString());
                    })->where(function ($sq) use ($empatTahunLalu) {
                        $sq->where('tmt_pangkat_terakhir', '<=', $empatTahunLalu)
                           ->orWhere(function ($ssq) use ($empatTahunLalu) {
                               $ssq->whereNull('tmt_pangkat_terakhir')
                                   ->where('tanggal_masuk', '<=', $empatTahunLalu);
                           });
                    });
                });
            });

        // 3. Query Semua Pegawai Layak Naik Pangkat (Radar 6 bulan / masa kerja >= 4 tahun)
        $allLayakQuery = Pegawai::where('status_pegawai', 'Aktif')
            ->where(function ($query) use ($enamBulanKedepan, $tigaSetengahTahunLalu) {
                $query->where(function ($q) use ($enamBulanKedepan) {
                    $q->whereNotNull('kp_berikutnya')
                      ->where('kp_berikutnya', '<=', $enamBulanKedepan);
                })->orWhere(function ($q) use ($tigaSetengahTahunLalu) {
                    $q->where('tmt_pangkat_terakhir', '<=', $tigaSetengahTahunLalu)
                      ->orWhere(function ($sq) use ($tigaSetengahTahunLalu) {
                          $sq->whereNull('tmt_pangkat_terakhir')
                             ->where('tanggal_masuk', '<=', $tigaSetengahTahunLalu);
                      });
                });
            });

        $relations = ['unitKerja', 'jabatan', 'golongan', 'riwayatSkp', 'riwayatPangkat', 'pengajuanKarir'];
        $makeCountable = fn(int $n) => $n > 0 ? array_fill(0, $n, null) : [];

        if ($filter === 'reminder') {
            $layakKp = (clone $reminderQuery)->with($relations)->orderBy('kp_berikutnya')->get();
            $reminderKp = $layakKp;
            $overdueKp = $makeCountable((clone $overdueQuery)->count());
            $allLayakKp = $makeCountable((clone $allLayakQuery)->count());
        } elseif ($filter === 'overdue') {
            $layakKp = (clone $overdueQuery)->with($relations)->orderBy('kp_berikutnya')->get();
            $overdueKp = $layakKp;
            $reminderKp = $makeCountable((clone $reminderQuery)->count());
            $allLayakKp = $makeCountable((clone $allLayakQuery)->count());
        } else {
            $layakKp = (clone $allLayakQuery)->with($relations)->orderByRaw('COALESCE(kp_berikutnya, "9999-12-31") ASC')->get();
            $allLayakKp = $layakKp;
            $reminderKp = $makeCountable((clone $reminderQuery)->count());
            $overdueKp = $makeCountable((clone $overdueQuery)->count());
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