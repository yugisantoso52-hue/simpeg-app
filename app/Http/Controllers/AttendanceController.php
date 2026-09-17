<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $service
    ) {}

    /**
     * Halaman Utama Presensi Karyawan
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $location = $this->service->getOrCreateLocation($user);

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $recentAttendances = Attendance::where('user_id', $user->id)
            ->latest('attendance_date')
            ->limit(10)
            ->get();

        $maxRadius = AttendanceService::MAX_ALLOWED_RADIUS_METERS;

        return view('attendance.index', compact(
            'user',
            'location',
            'todayAttendance',
            'recentAttendances',
            'maxRadius'
        ));
    }

    /**
     * Simpan Presensi Karyawan (Check-in atau Check-out)
     */
    public function store(StoreAttendanceRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        try {
            $attendance = $this->service->processAttendance($user, $request->validated());

            $action = $request->input('action', 'check_in');
            $actionLabel = $action === 'check_out' ? 'Check-Out (Pulang)' : 'Check-In (Masuk)';
            $message = "Presensi {$actionLabel} berhasil dicatat!";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'id' => $attendance->id,
                        'attendance_type' => $attendance->attendance_type,
                        'attendance_date' => $attendance->attendance_date->format('d/m/Y'),
                        'check_in_time' => $attendance->check_in_time ? $attendance->check_in_time->timezone('Asia/Jakarta')->format('H:i') : null,
                        'check_out_time' => $attendance->check_out_time ? $attendance->check_out_time->timezone('Asia/Jakarta')->format('H:i') : null,
                        'distance_meters' => $attendance->check_in_distance_meters,
                        'status' => $attendance->status,
                        'status_badge' => $attendance->status_badge,
                    ],
                ]);
            }

            return redirect()->route('presensi.index')->with('success', $message);

        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors($e->errors())->with('error', $firstError);
        } catch (\Throwable $e) {
            $msg = 'Terjadi kesalahan pada sistem: ' . $e->getMessage();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 500);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }
    }

    /**
     * Riwayat Presensi Saya (Pegawai)
     */
    public function history(Request $request): View
    {
        $user = $request->user();
        $query = Attendance::where('user_id', $user->id)
            ->latest('attendance_date');

        if ($request->filled('month')) {
            $query->whereMonth('attendance_date', $request->get('month'));
        }
        if ($request->filled('year')) {
            $query->whereYear('attendance_date', $request->get('year'));
        }

        $attendances = $query->paginate(20)->withQueryString();

        return view('attendance.history', compact('attendances', 'user'));
    }
}
