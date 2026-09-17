<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EmployeeAttendanceLocation;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
        $filters = [
            'date' => $request->get('date', Carbon::today()->toDateString()),
            'date_start' => $request->get('date_start'),
            'date_end' => $request->get('date_end'),
            'attendance_type' => $request->get('attendance_type'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        $attendances = $this->service->filterAttendances($filters, 20);
        $statistics = $this->service->todayStatistics();

        return view('attendance.admin.index', compact('attendances', 'filters', 'statistics'));
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
}
