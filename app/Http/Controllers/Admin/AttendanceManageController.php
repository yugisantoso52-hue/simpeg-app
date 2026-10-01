<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceExport;
use App\Exports\AttendanceMonthlyExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EmployeeAttendanceLocation;
use App\Models\User;
use App\Services\ApprovalHierarchyService;
use App\Services\AttendanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceManageController extends Controller
{
    public function __construct(
        protected AttendanceService $service,
        protected ApprovalHierarchyService $hierarchy
    ) {}

    /**
     * Rekap Presensi Karyawan (Admin & Pimpinan Panel)
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $bawahanIds = $this->hierarchy->getScopedPresensiPegawaiIds($user);
        $scopeLabel = $this->hierarchy->getPresensiScopeLabel($user);

        $mode = $request->get('mode', 'daily');
        $statistics = $this->service->todayStatistics($bawahanIds);

        if ($mode === 'monthly') {
            $month = (int) $request->get('month', Carbon::now('Asia/Jakarta')->month);
            $year = (int) $request->get('year', Carbon::now('Asia/Jakarta')->year);
            $search = $request->get('search');

            $matrixData = $this->service->getMonthlyMatrix($month, $year, $search, 50, $bawahanIds);

            return view('attendance.admin.index', [
                'mode' => 'monthly',
                'month' => $month,
                'year' => $year,
                'search' => $search,
                'matrixData' => $matrixData,
                'statistics' => $statistics,
                'scopeLabel' => $scopeLabel,
                'filters' => [
                    'mode' => 'monthly',
                    'month' => $month,
                    'year' => $year,
                    'search' => $search,
                ],
            ]);
        }

        // MODE DAILY: Model A (Accordion Lipatan Pegawai - Log Harian Terstruktur 1 Bulan Kerja)
        $month = (int) $request->get('month', Carbon::now('Asia/Jakarta')->month);
        $year = (int) $request->get('year', Carbon::now('Asia/Jakarta')->year);
        $kategori = $request->get('kategori', 'all');
        $search = $request->get('search');

        $accordionData = $this->service->getDailyAccordionData(
            $month,
            $year,
            $kategori,
            $search,
            $bawahanIds,
            20
        );

        return view('attendance.admin.index', [
            'mode' => 'daily',
            'month' => $month,
            'year' => $year,
            'kategori' => $kategori,
            'search' => $search,
            'accordionData' => $accordionData,
            'statistics' => $statistics,
            'scopeLabel' => $scopeLabel,
            'filters' => [
                'mode' => 'daily',
                'month' => $month,
                'year' => $year,
                'kategori' => $kategori,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Helper untuk normalisasi filter pencarian
     */
    protected function extractFilters(Request $request): array
    {
        $date = $request->get('date');
        $dateStart = $request->get('date_start');
        $dateEnd = $request->get('date_end');

        if (!$date && !$dateStart && !$dateEnd && ($request->has('month') || $request->has('year'))) {
            $month = (int) $request->get('month', Carbon::now('Asia/Jakarta')->month);
            $year = (int) $request->get('year', Carbon::now('Asia/Jakarta')->year);
            $dateStart = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->startOfMonth()->toDateString();
            $dateEnd = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->endOfMonth()->toDateString();
        }

        // Default ke hari ini jika tanpa query params sama sekali
        if (!$date && !$dateStart && !$dateEnd && !$request->has('search') && !$request->has('status') && !$request->has('attendance_type')) {
            $date = Carbon::today()->toDateString();
        }

        return [
            'date' => $date,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'attendance_type' => $request->get('attendance_type'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
            'kategori' => $request->get('kategori', 'all'),
        ];
    }

    /**
     * Manajemen & Monitoring Titik Lokasi Acuan Pegawai
     */
    public function locations(Request $request): View
    {
        $user = $request->user();
        $bawahanIds = $this->hierarchy->getScopedPresensiPegawaiIds($user);

        $search = $request->get('search');

        $query = User::with(['pegawai.unitKerja', 'attendanceLocation'])
            ->where(function ($q) {
                $q->whereNotNull('pegawai_id')
                  ->orWhereHas('role', function ($qr) {
                      $qr->whereIn('name', ['pegawai', 'admin', 'pimpinan']);
                  });
            });

        if ($bawahanIds !== null) {
            $query->whereIn('pegawai_id', $bawahanIds);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('pegawai', function ($qp) use ($search) {
                      $qp->where('nama', 'like', "%{$search}%")
                         ->orWhere('nip', 'like', "%{$search}%");
                  });
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('attendance.admin.locations', compact('users', 'search'));
    }

    /**
     * Reset Titik Acuan Lokasi Karyawan (WFO / WFH / ALL)
     */
    public function resetLocation(Request $request, int $userId): RedirectResponse
    {
        $authUser = $request->user();
        $bawahanIds = $this->hierarchy->getScopedPresensiPegawaiIds($authUser);

        $user = User::findOrFail($userId);

        if ($bawahanIds !== null && (! $user->pegawai_id || ! in_array($user->pegawai_id, $bawahanIds, true))) {
            abort(403, 'Anda tidak memiliki hak akses untuk mereset lokasi pegawai di luar lingkup kerja Anda.');
        }

        $type = $request->input('type', 'all');
        $this->service->resetLocation($userId, $type);

        $typeLabel = match (strtolower($type)) {
            'wfo' => 'Titik WFO',
            'wfh' => 'Titik WFH',
            default => 'Semua Titik Acuan (WFO & WFH)',
        };

        return redirect()->back()->with('success', "Koordinat {$typeLabel} untuk pegawai '{$user->name}' berhasil direset. Pegawai dapat mengunci titik baru saat presensi berikutnya.");
    }

    /**
     * Hapus Presensi (Koreksi Admin)
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $attendance = Attendance::findOrFail($id);
        $authUser = $request->user();
        $bawahanIds = $this->hierarchy->getScopedPresensiPegawaiIds($authUser);

        if ($bawahanIds !== null) {
            $targetPegawaiId = $attendance->user?->pegawai_id;
            if (! $targetPegawaiId || ! in_array($targetPegawaiId, $bawahanIds, true)) {
                abort(403, 'Anda tidak memiliki otoritas menghapus catatan presensi pegawai ini.');
            }
        }

        $attendance->delete();

        return redirect()->back()->with('success', 'Data presensi berhasil dihapus.');
    }

    /**
     * Export Rekap Presensi Pegawai ke Excel (.xlsx)
     */
    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $bawahanIds = $this->hierarchy->getScopedPresensiPegawaiIds($user);

        $mode = $request->get('mode', 'daily');

        if ($mode === 'monthly') {
            $month = (int) $request->get('month', Carbon::now('Asia/Jakarta')->month);
            $year = (int) $request->get('year', Carbon::now('Asia/Jakarta')->year);
            $search = $request->get('search');

            $filename = 'Matriks_Presensi_' . $year . '_' . sprintf('%02d', $month) . '.xlsx';
            return Excel::download(new AttendanceMonthlyExport($month, $year, $search, $bawahanIds), $filename);
        }

        $filters = $this->extractFilters($request);
        if ($bawahanIds !== null) {
            $filters['bawahan_ids'] = $bawahanIds;
        }
        $filename = 'Rekap_Presensi_' . Carbon::now('Asia/Jakarta')->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new AttendanceExport($filters), $filename);
    }

    /**
     * Cetak Rekap Presensi Pegawai ke PDF
     */
    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $bawahanIds = $this->hierarchy->getScopedPresensiPegawaiIds($user);

        $mode = $request->get('mode', 'daily');

        if ($mode === 'monthly') {
            $month = (int) $request->get('month', Carbon::now('Asia/Jakarta')->month);
            $year = (int) $request->get('year', Carbon::now('Asia/Jakarta')->year);
            $search = $request->get('search');

            $matrixData = $this->service->getMonthlyMatrix($month, $year, $search, 5000, $bawahanIds);
            $pdf = Pdf::loadView('exports.pdf.attendance_monthly', compact('matrixData'))->setPaper('a4', 'landscape');

            $filename = 'Matriks_Presensi_' . $year . '_' . sprintf('%02d', $month) . '.pdf';
            return $pdf->download($filename);
        }

        $filters = $this->extractFilters($request);
        if ($bawahanIds !== null) {
            $filters['bawahan_ids'] = $bawahanIds;
        }

        $paginator = $this->service->filterAttendances($filters, 5000);
        $attendances = collect($paginator->items());

        if (!empty($filters['date'])) {
            $periodText = Carbon::parse($filters['date'])->translatedFormat('d F Y');
        } elseif (!empty($filters['date_start']) && !empty($filters['date_end'])) {
            $periodText = Carbon::parse($filters['date_start'])->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($filters['date_end'])->translatedFormat('d F Y');
        } else {
            $periodText = 'Semua Periode';
        }

        $pdf = Pdf::loadView('exports.pdf.attendance', [
            'attendances' => $attendances,
            'periodText' => $periodText,
        ])->setPaper('a4', 'landscape');

        $filename = 'Rekap_Presensi_' . Carbon::now('Asia/Jakarta')->format('Y-m-d_His') . '.pdf';
        return $pdf->download($filename);
    }
}
