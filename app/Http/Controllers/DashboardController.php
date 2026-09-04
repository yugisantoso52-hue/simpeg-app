<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Services\DashboardService;
use App\Services\PegawaiCompletenessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    // Dependency Injection DashboardService
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Menampilkan Halaman Utama Dashboard
     */
    public function index(): View
    {
        $user = Auth::user();

        $data = [
            'statistik'        => $this->dashboardService->statistics(),
            'grafikGolongan'   => $this->dashboardService->grafikGolongan(),
            'grafikPendidikan' => $this->dashboardService->grafikPendidikan(),
            'grafikUnit'       => $this->dashboardService->grafikUnit(),
            'pegawaiBaru'      => $this->dashboardService->pegawaiBaru(),
            'reminder'         => $this->dashboardService->reminder(),
        ];

        // Jika user yang login adalah Pegawai Perorangan (Bukan Admin/Pimpinan)
        if ($user->hasRole('pegawai')) {
            $pegawaiId = $user->pegawai_id;

            if (!$pegawaiId) {
                $p = Pegawai::where('email', $user->email)->orWhere('nip', $user->name)->first();
                $pegawaiId = $p?->id;
            }

            if ($pegawaiId) {
                $myPegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan', 'riwayatStrSip', 'riwayatPendidikan', 'riwayatDiklat', 'riwayatSkp'])->find($pegawaiId);
                $myCuti    = PengajuanCuti::where('pegawai_id', $pegawaiId)->latest()->take(5)->get();

                $data['myPegawai']        = $myPegawai;
                $data['myCuti']           = $myCuti;
                $data['completenessData'] = PegawaiCompletenessService::calculate($myPegawai);
            }
        }

        // Jika user yang login adalah Admin / Pimpinan
        if ($user->hasRole(['admin', 'pimpinan'])) {
            $data['facultyCompleteness'] = PegawaiCompletenessService::getFacultyCompleteness();
        }

        return view('dashboard', $data);
    }
}