<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EmployeeAttendanceLocation;
use App\Models\User;
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
        protected AttendanceService $service
    ) {}

    /**
     * Rekap Presensi Karyawan (Admin Panel)
     */
    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);
        $attendances = $this->service->filterAttendances($filters, 20);
        $statistics = $this->service->todayStatistics();

        return view('attendance.admin.index', compact('attendances', 'filters', 'statistics'));
    }

    /**
     * Helper untuk normalisasi filter pencarian
     */
    protected function extractFilters(Request $request): array
    {
        $date = $request->get('date');
        // Default ke hari ini jika tanpa query params sama sekali
        if (!$request->has('date') && !$request->has('date_start') && !$request->has('search') && !$request->has('status') && !$request->has('attendance_type')) {
            $date = Carbon::today()->toDateString();
        }

        return [
            'date' => $date,
            'date_start' => $request->get('date_start'),
            'date_end' => $request->get('date_end'),
            'attendance_type' => $request->get('attendance_type'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];
    }

    /**
     * Manajemen & Monitoring Titik Lokasi Acuan Pegawai
     */
    public function locations(Request $request): View
    {
        $search = $request->get('search');

        $query = User::with(['pegawai.unitKerja', 'attendanceLocation'])
            ->whereNotNull('pegawai_id')
            ->orWhereHas('role', function ($q) {
                $q->whereIn('name', ['pegawai', 'admin', 'pimpinan']);
            });

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
        $type = $request->input('type', 'all');

        $user = User::findOrFail($userId);
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
    public function destroy(int $id): RedirectResponse
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return redirect()->back()->with('success', 'Data presensi berhasil dihapus.');
    }

    /**
     * Export Rekap Presensi Pegawai ke Excel (.xlsx)
     */
    public function exportExcel(Request $request)
    {
        $filters = $this->extractFilters($request);

        $filename = 'Rekap_Presensi_' . Carbon::now('Asia/Jakarta')->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new AttendanceExport($filters), $filename);
    }

    /**
     * Cetak Rekap Presensi Pegawai ke PDF
     */
    public function exportPdf(Request $request)
    {
        $filters = $this->extractFilters($request);

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
