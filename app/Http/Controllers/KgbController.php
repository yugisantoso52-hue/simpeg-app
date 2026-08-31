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
    public function index()
    {
        $allPegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])
            ->where('status_pegawai', 'Aktif')
            ->get();
            
        $layakKgb = $allPegawai->filter(function ($pegawai) {
            return $this->kgbService->cekKelayakanKgb($pegawai);
        })->sortBy(function ($pegawai) {
            return $pegawai->kgb_berikutnya ?? '9999-12-31';
        })->values();

        return view('kgb.index', compact('layakKgb'));
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