<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Services\SatyalancanaService;

class SatyalancanaController extends Controller
{
    protected SatyalancanaService $satyalancanaService;

    public function __construct(SatyalancanaService $satyalancanaService)
    {
        $this->satyalancanaService = $satyalancanaService;
    }

    public function index()
    {
        $allPegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])->get();
        $layakSatyalancana = [];

        foreach ($allPegawai as $pegawai) {
            $kategori = $this->satyalancanaService->cekKelayakanSatyalancana($pegawai);
            if ($kategori) {
                // Tempelkan informasi kategori penghargaan ke objek pegawai sementara
                $pegawai->kategori_satyalancana = $kategori;
                $layakSatyalancana[] = $pegawai;
            }
        }

        return view('satyalancana.index', compact('layakSatyalancana'));
    }
}