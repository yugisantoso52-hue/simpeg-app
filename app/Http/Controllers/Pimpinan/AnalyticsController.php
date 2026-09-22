<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Services\ExecutiveAnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    protected ExecutiveAnalyticsService $analyticsService;

    public function __construct(ExecutiveAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Halaman Utama Executive Analytics Dashboard (Dekanat & Pimpinan)
     */
    public function index(Request $request): View
    {
        $now = Carbon::now('Asia/Jakarta');
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);

        $kpis = $this->analyticsService->getExecutiveKpis($month, $year);
        $logbookWorkload = $this->analyticsService->getLogbookWorkloadByUnit($month, $year);
        $attendanceTrends = $this->analyticsService->getAttendanceTrends($month, $year);
        $retirementRadar = $this->analyticsService->getRetirementProjections();
        $staffComposition = $this->analyticsService->getStaffComposition();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('pimpinan.analytics', compact(
            'kpis',
            'logbookWorkload',
            'attendanceTrends',
            'retirementRadar',
            'staffComposition',
            'month',
            'year',
            'namaBulan'
        ));
    }

    /**
     * Cetak Laporan Ringkasan Eksekutif PDF
     */
    public function exportPdf(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);

        $kpis = $this->analyticsService->getExecutiveKpis($month, $year);
        $logbookWorkload = $this->analyticsService->getLogbookWorkloadByUnit($month, $year);
        $retirementRadar = $this->analyticsService->getRetirementProjections();
        $staffComposition = $this->analyticsService->getStaffComposition();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $pdf = Pdf::loadView('exports.pdf.executive_summary', compact(
            'kpis',
            'logbookWorkload',
            'retirementRadar',
            'staffComposition',
            'month',
            'year',
            'namaBulan'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream("Laporan_Eksekutif_SIKAP_FKP_{$month}_{$year}.pdf");
    }
}
