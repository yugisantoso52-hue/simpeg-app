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
        $attendanceToken = $this->service->generateAttendanceToken($user);
        $tokenTtl = AttendanceService::ATTENDANCE_TOKEN_TTL_SECONDS;

        return view('attendance.index', compact(
            'user',
            'location',
            'todayAttendance',
            'recentAttendances',
            'maxRadius',
            'attendanceToken',
            'tokenTtl'
        ));
    }

    /**
     * Dapatkan Token Anti-Replay Baru (AJAX Refresh jika expired)
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $token = $this->service->generateAttendanceToken($request->user());
        return response()->json([
            'success' => true,
            'token' => $token,
            'expires_in' => AttendanceService::ATTENDANCE_TOKEN_TTL_SECONDS,
        ]);
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

    /**
     * Stream / Tampilkan Berkas Foto Selfie Presensi
     */
    public function streamPhoto(int $id, string $type)
    {
        $attendance = Attendance::findOrFail($id);
        $user = auth()->user();

        if (!$user) {
            abort(401);
        }

        // Otorisasi IDOR: Admin, Pimpinan, pemilik presensi, atau atasan langsungnya
        $isOwn = (int)$attendance->user_id === (int)$user->id;
        $isPrivileged = $user->hasRole(['admin', 'pimpinan']);
        $isAtasan = $user->isAtasan() && in_array($attendance->user?->pegawai_id, $user->getBawahanIds());

        if (!$isOwn && !$isPrivileged && !$isAtasan) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat foto selfie presensi pegawai ini.');
        }

        $path = strtolower($type) === 'out'
            ? $attendance->check_out_photo_path
            : $attendance->check_in_photo_path;

        if (!$path) {
            abort(404, 'Foto selfie tidak ditemukan.');
        }

        // 1. Cek via disk public
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->response($path);
        }

        // 2. Cek via disk local
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('local')->response($path);
        }

        // 3. Cek direct storage_path
        $fullPath = storage_path('app/public/' . $path);
        if (file_exists($fullPath)) {
            return response()->file($fullPath);
        }

        abort(404, 'Berkas foto selfie tidak ditemukan di penyimpanan server.');
    }
}
