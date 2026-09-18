<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LogbookExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Logbook\VerifyLogbookRequest;
use App\Models\Logbook;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Services\LogbookService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LogbookManageController extends Controller
{
    public function __construct(
        protected LogbookService $service
    ) {}

    /**
     * Dashboard Verifikasi & Rekap Logbook Kinerja Pegawai (Admin & Pimpinan)
     */
    public function index(Request $request)
    {
        $month = (int) $request->get('bulan', Carbon::now()->month);
        $year = (int) $request->get('tahun', Carbon::now()->year);
        $unitKerjaId = $request->filled('unit_kerja_id') ? (int) $request->get('unit_kerja_id') : null;
        $kategoriPegawai = $request->get('kategori_pegawai', 'semua');
        $status = $request->get('status', 'semua');
        $search = $request->get('search');

        $statistics = $this->service->getAdminStatistics($month, $year, $unitKerjaId);

        $logbooks = $this->service->getAdminQuery($month, $year, $unitKerjaId, $kategoriPegawai, $status, $search)
            ->paginate(20)
            ->withQueryString();

        $unitKerjaList = UnitKerja::orderBy('nama_unit')->get();

        return view('admin.logbook.index', compact(
            'logbooks',
            'statistics',
            'unitKerjaList',
            'month',
            'year',
            'unitKerjaId',
            'kategoriPegawai',
            'status',
            'search'
        ));
    }

    /**
     * Verifikasi Logbook Pegawai (Setujui, Minta Revisi, Tolak)
     */
    public function verify(int $id, VerifyLogbookRequest $request)
    {
        $logbook = Logbook::findOrFail($id);
        $verifierId = (int) $request->user()->id;

        $this->service->verify(
            $logbook,
            $request->get('status'),
            $request->get('catatan_atasan'),
            $verifierId
        );

        $label = match ($request->get('status')) {
            Logbook::STATUS_DISETUJUI    => 'disetujui',
            Logbook::STATUS_PERLU_REVISI => 'diminta untuk direvisi oleh pegawai',
            Logbook::STATUS_DITOLAK      => 'ditolak',
            default                      => 'diperbarui',
        };

        return redirect()->back()->with('success', "Aktivitas logbook pegawai berhasil {$label}.");
    }

    /**
     * Persetujuan Massal (Bulk Approval)
     */
    public function bulkVerify(Request $request)
    {
        $request->validate([
            'logbook_ids'   => ['required', 'array'],
            'logbook_ids.*' => ['exists:logbooks,id'],
            'status'        => ['required', 'in:disetujui,ditolak'],
        ]);

        $ids = $request->get('logbook_ids');
        $status = $request->get('status');
        $catatan = $request->get('catatan_atasan');
        $verifierId = (int) $request->user()->id;

        $count = $this->service->verifyBulk($ids, $status, $catatan, $verifierId);

        return redirect()->back()->with('success', "Sebanyak {$count} aktivitas logbook berhasil disetujui sekaligus.");
    }

    /**
     * Cetak PDF Rekapitulasi Kinerja Logbook per Unit Kerja / Fakultas
     */
    public function exportRekapPdf(Request $request)
    {
        $month = (int) $request->get('bulan', Carbon::now()->month);
        $year = (int) $request->get('tahun', Carbon::now()->year);
        $unitKerjaId = $request->filled('unit_kerja_id') ? (int) $request->get('unit_kerja_id') : null;
        $kategoriPegawai = $request->get('kategori_pegawai', 'semua');
        $status = $request->get('status', 'semua');

        $logbooks = $this->service->getAdminQuery($month, $year, $unitKerjaId, $kategoriPegawai, $status)
            ->get();

        $unitKerja = $unitKerjaId ? UnitKerja::find($unitKerjaId) : null;
        $namaBulan = Carbon::createFromDate($year, $month, 1)->locale('id')->isoFormat('MMMM Y');

        $pdf = Pdf::loadView('admin.logbook.rekap-pdf', compact('logbooks', 'unitKerja', 'month', 'year', 'namaBulan', 'status'))
            ->setPaper('a4', 'landscape');

        $unitName = $unitKerja ? str_replace(' ', '_', $unitKerja->nama_unit) : 'Semua_Unit';
        $filename = "Rekap_Logbook_{$unitName}_{$namaBulan}.pdf";

        return $pdf->stream($filename);
    }

    /**
     * Ekspor Rekapitulasi Logbook ke Excel
     */
    public function exportRekapExcel(Request $request)
    {
        $month = (int) $request->get('bulan', Carbon::now()->month);
        $year = (int) $request->get('tahun', Carbon::now()->year);
        $unitKerjaId = $request->filled('unit_kerja_id') ? (int) $request->get('unit_kerja_id') : null;
        $status = $request->get('status');

        $unitKerja = $unitKerjaId ? UnitKerja::find($unitKerjaId) : null;
        $unitName = $unitKerja ? str_replace(' ', '_', $unitKerja->nama_unit) : 'Semua_Unit';
        $filename = "Rekap_Logbook_{$unitName}_{$year}_{$month}.xlsx";

        return Excel::download(new LogbookExport(null, $month, $year, $unitKerjaId, $status), $filename);
    }
}
