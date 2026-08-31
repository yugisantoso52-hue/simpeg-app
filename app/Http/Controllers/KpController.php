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

    public function index()
    {
        $allPegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan', 'riwayatSkp', 'riwayatPangkat'])
            ->where('status_pegawai', 'Aktif')
            ->get();

        $layakKp = $allPegawai->filter(function ($pegawai) {
            return $this->kpService->cekKelayakanKp($pegawai);
        });

        $golongans = Golongan::all();

        return view('kp.index', compact('layakKp', 'golongans'));
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