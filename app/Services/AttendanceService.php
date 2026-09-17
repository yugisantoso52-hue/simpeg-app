<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EmployeeAttendanceLocation;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public const MAX_ALLOWED_RADIUS_METERS = 75.0;
    public const DEFAULT_LATE_TIME = '08:00:00';

    /**
     * Haversine Formula untuk menghitung jarak antara 2 titik koordinat (meter).
     * Menggunakan jari-jari bumi R = 6.371.000 meter.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Ambil atau buat record titik lokasi acuan untuk karyawan/user.
     */
    public function getOrCreateLocation(User $user): EmployeeAttendanceLocation
    {
        return EmployeeAttendanceLocation::firstOrCreate(
            ['user_id' => $user->id],
            [
                'wfo_latitude' => null,
                'wfo_longitude' => null,
                'wfo_locked_at' => null,
                'wfh_latitude' => null,
                'wfh_longitude' => null,
                'wfh_locked_at' => null,
            ]
        );
    }

    /**
     * Proses Presensi (Check-in atau Check-out)
     *
     * @param User $user
     * @param array{
     *     attendance_type: string,
     *     action?: string,
     *     latitude: float,
     *     longitude: float,
     *     photo: string,
     *     notes?: string|null
     * } $payload
     * @return Attendance
     * @throws ValidationException
     */
    public function processAttendance(User $user, array $payload): Attendance
    {
        $type = strtolower($payload['attendance_type'] ?? 'wfo');
        if (!in_array($type, ['wfo', 'wfh'])) {
            throw ValidationException::withMessages([
                'attendance_type' => 'Tipe presensi tidak valid. Pilih WFO atau WFH.',
            ]);
        }

        $latitude = (float) $payload['latitude'];
        $longitude = (float) $payload['longitude'];
        $photoBase64 = $payload['photo'] ?? null;
        $notes = $payload['notes'] ?? null;

        if (empty($photoBase64)) {
            throw ValidationException::withMessages([
                'photo' => 'Foto selfie wajib diambil langsung melalui kamera.',
            ]);
        }

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        // Tentukan aksi: check_in atau check_out
        $action = $payload['action'] ?? null;
        if (!$action) {
            $action = (!$existingAttendance || !$existingAttendance->check_in_time) ? 'check_in' : 'check_out';
        }

        if ($action === 'check_out') {
            if (!$existingAttendance) {
                throw ValidationException::withMessages([
                    'action' => 'Anda belum melakukan check-in hari ini.',
                ]);
            }
            if ($existingAttendance->check_out_time) {
                throw ValidationException::withMessages([
                    'action' => 'Anda sudah melakukan check-out hari ini pada ' . Carbon::parse($existingAttendance->check_out_time)->timezone('Asia/Jakarta')->translatedFormat('H:i') . ' WIB.',
                ]);
            }
        }

        // Ambil data titik acuan user
        $location = $this->getOrCreateLocation($user);
        $isLocked = $location->isLocked($type);
        $distanceMeters = 0.0;
        $justLocked = false;

        // Mekanisme One-Time Lock
        if (!$isLocked) {
            // Belum terkunci: koordinat saat ini otomatis dikunci sebagai titik acuan tetap
            if ($type === 'wfh') {
                $location->wfh_latitude = $latitude;
                $location->wfh_longitude = $longitude;
                $location->wfh_locked_at = $now;
            } else {
                $location->wfo_latitude = $latitude;
                $location->wfo_longitude = $longitude;
                $location->wfo_locked_at = $now;
            }
            $location->save();

            $distanceMeters = 0.0;
            $justLocked = true;
        } else {
            // Sudah terkunci: validasi radius toleransi maksimal 75 meter
            $refCoords = $location->getCoordinates($type);
            $distanceMeters = $this->calculateDistance(
                $latitude,
                $longitude,
                $refCoords['latitude'],
                $refCoords['longitude']
            );

            if ($distanceMeters > self::MAX_ALLOWED_RADIUS_METERS) {
                $unitTypeLabel = $type === 'wfh' ? 'Titik WFH' : 'Titik Kantor (WFO)';
                throw ValidationException::withMessages([
                    'radius' => "Anda berada di luar radius presensi {$unitTypeLabel} (Jarak: {$distanceMeters} meter. Maksimal: " . self::MAX_ALLOWED_RADIUS_METERS . " meter). Silakan mendekat ke lokasi acuan.",
                ]);
            }
        }

        // Simpan foto selfie Base64 ke disk storage
        $photoPath = $this->saveBase64Photo($photoBase64, $user->id, $action);

        return DB::transaction(function () use ($user, $type, $today, $action, $latitude, $longitude, $distanceMeters, $photoPath, $notes, $existingAttendance, $now) {
            if ($action === 'check_in') {
                if ($existingAttendance) {
                    // Update check-in jika belum check out
                    $existingAttendance->update([
                        'attendance_type' => $type,
                        'check_in_time' => $now,
                        'check_in_latitude' => $latitude,
                        'check_in_longitude' => $longitude,
                        'check_in_distance_meters' => $distanceMeters,
                        'check_in_photo_path' => $photoPath,
                        'status' => $this->determineStatus($now),
                        'notes' => $notes ?: $existingAttendance->notes,
                    ]);
                    return $existingAttendance;
                }

                return Attendance::create([
                    'user_id' => $user->id,
                    'attendance_type' => $type,
                    'attendance_date' => $today,
                    'check_in_time' => $now,
                    'check_in_latitude' => $latitude,
                    'check_in_longitude' => $longitude,
                    'check_in_distance_meters' => $distanceMeters,
                    'check_in_photo_path' => $photoPath,
                    'status' => $this->determineStatus($now),
                    'notes' => $notes,
                ]);
            }

            // Check-out
            $existingAttendance->update([
                'check_out_time' => $now,
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'check_out_distance_meters' => $distanceMeters,
                'check_out_photo_path' => $photoPath,
                'notes' => $notes ? ($existingAttendance->notes ? $existingAttendance->notes . ' | ' . $notes : $notes) : $existingAttendance->notes,
            ]);

            return $existingAttendance;
        });
    }

    /**
     * Tentukan status kehadiran (Hadir Tepat Waktu vs Terlambat).
     */
    protected function determineStatus(Carbon $time): string
    {
        $timeInJakarta = $time->copy()->timezone('Asia/Jakarta');
        $cutoff = Carbon::parse($timeInJakarta->toDateString() . ' ' . self::DEFAULT_LATE_TIME, 'Asia/Jakarta');
        return $timeInJakarta->greaterThan($cutoff) ? 'late' : 'present';
    }

    /**
     * Decode dan simpan gambar base64 ke disk public storage.
     * Path format: attendances/YYYY-MM-DD/user_{id}_{action}_{timestamp}.jpg
     */
    public function saveBase64Photo(string $base64String, int $userId, string $action): string
    {
        // Bersihkan header Data URL jika ada (e.g. data:image/jpeg;base64,...)
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $base64String = substr($base64String, strpos($base64String, ',') + 1);
            $extension = strtolower($type[1]);
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                $extension = 'jpg';
            }
        } else {
            $extension = 'jpg';
        }

        $imageData = base64_decode($base64String);
        if ($imageData === false) {
            throw ValidationException::withMessages([
                'photo' => 'Format foto selfie tidak valid atau berkas rusak.',
            ]);
        }

        $dateFolder = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $fileName = "attendances/{$dateFolder}/user_{$userId}_{$action}_" . time() . '_' . Str::random(6) . ".{$extension}";

        Storage::disk('public')->put($fileName, $imageData);

        return $fileName;
    }

    /**
     * Reset titik koordinat acuan (WFO, WFH, atau keduanya) oleh Admin/HR.
     */
    public function resetLocation(int $userId, string $type = 'all'): EmployeeAttendanceLocation
    {
        $user = User::findOrFail($userId);
        $location = $this->getOrCreateLocation($user);

        $type = strtolower($type);
        if ($type === 'wfo') {
            $location->wfo_latitude = null;
            $location->wfo_longitude = null;
            $location->wfo_locked_at = null;
        } elseif ($type === 'wfh') {
            $location->wfh_latitude = null;
            $location->wfh_longitude = null;
            $location->wfh_locked_at = null;
        } else {
            $location->wfo_latitude = null;
            $location->wfo_longitude = null;
            $location->wfo_locked_at = null;
            $location->wfh_latitude = null;
            $location->wfh_longitude = null;
            $location->wfh_locked_at = null;
        }

        $location->save();
        return $location;
    }

    /**
     * Filter presensi untuk panel rekap Admin
     */
    public function filterAttendances(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Attendance::with(['user.pegawai.unitKerja', 'user.pegawai.jabatan'])
            ->latest('attendance_date')
            ->latest('check_in_time');

        if (!empty($filters['date'])) {
            $query->whereDate('attendance_date', $filters['date']);
        }

        if (!empty($filters['date_start']) && !empty($filters['date_end'])) {
            $query->whereBetween('attendance_date', [$filters['date_start'], $filters['date_end']]);
        }

        if (!empty($filters['attendance_type'])) {
            $query->where('attendance_type', $filters['attendance_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
                   ->orWhereHas('pegawai', function ($qp) use ($search) {
                       $qp->where('nama', 'like', "%{$search}%")
                           ->orWhere('nip', 'like', "%{$search}%");
                   });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Statistik Presensi Hari Ini
     */
    public function todayStatistics(): array
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $totalPegawai = Pegawai::count();
        if ($totalPegawai === 0) {
            $totalPegawai = User::count();
        }

        $totalUsers = User::count();
        $presentCount = Attendance::whereDate('attendance_date', $today)->count();
        $wfoCount = Attendance::whereDate('attendance_date', $today)->where('attendance_type', 'wfo')->count();
        $wfhCount = Attendance::whereDate('attendance_date', $today)->where('attendance_type', 'wfh')->count();
        $lateCount = Attendance::whereDate('attendance_date', $today)->where('status', 'late')->count();

        return [
            'total_users' => $totalPegawai,
            'total_pegawai' => $totalPegawai,
            'total_user_accounts' => $totalUsers,
            'total_present' => $presentCount,
            'total_wfo' => $wfoCount,
            'total_wfh' => $wfhCount,
            'total_late' => $lateCount,
        ];
    }

    /**
     * Dapatkan data Matriks Presensi Bulanan (Kalender 1 - 31) untuk semua pegawai
     */
    public function getMonthlyMatrix(int $month, int $year, ?string $search = null, int $perPage = 50): array
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;
        $now = Carbon::now('Asia/Jakarta');

        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::createFromDate($year, $month, $d, 'Asia/Jakarta');
            $days[$d] = [
                'day' => $d,
                'date' => $date->toDateString(),
                'day_name' => $date->translatedFormat('D'), // Sen, Sel, Rab, Kam, Jum, Sab, Min
                'day_short' => $date->format('D'),
                'is_weekend' => $date->isWeekend(),
                'is_future' => $date->isAfter($now->endOfDay()),
                'is_today' => $date->isToday(),
            ];
        }

        $query = Pegawai::with([
            'unitKerja',
            'jabatan',
            'user.attendances' => function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('attendance_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            }
        ])->where('status_pegawai', 'Aktif');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $pegawaiList = $query->orderBy('nama', 'asc')->paginate($perPage)->withQueryString();

        $matrixRows = [];
        foreach ($pegawaiList as $pegawai) {
            $attendancesByDay = [];
            if ($pegawai->user && $pegawai->user->attendances) {
                foreach ($pegawai->user->attendances as $att) {
                    $dayNum = (int) Carbon::parse($att->attendance_date)->format('j');
                    $attendancesByDay[$dayNum] = $att;
                }
            }

            $totalHadir = 0;
            $totalLate = 0;
            $totalWfo = 0;
            $totalWfh = 0;
            $totalSeconds = 0;

            $dayRecords = [];
            foreach ($days as $d => $dayInfo) {
                $att = $attendancesByDay[$d] ?? null;
                if ($att) {
                    $totalHadir++;
                    if ($att->status === 'late') {
                        $totalLate++;
                    }
                    if ($att->attendance_type === 'wfh') {
                        $totalWfh++;
                    } else {
                        $totalWfo++;
                    }

                    if ($att->check_in_time && $att->check_out_time) {
                        $diffSeconds = $att->check_in_time->diffInSeconds($att->check_out_time);
                        $totalSeconds += $diffSeconds;
                    }

                    $dayRecords[$d] = [
                        'status' => $att->status,
                        'type' => $att->attendance_type,
                        'in' => $att->formatted_check_in_time,
                        'out' => $att->formatted_check_out_time,
                        'duration' => $att->work_duration,
                        'distance' => $att->check_in_distance_meters,
                        'photo_in' => $att->check_in_photo_url,
                        'photo_out' => $att->check_out_photo_url,
                        'notes' => $att->notes,
                        'badge' => $att->status === 'late' ? 'T' : ($att->status === 'leave' ? 'I' : 'H'),
                        'badge_color' => $att->status === 'late' ? 'yellow' : ($att->status === 'leave' ? 'blue' : 'green'),
                    ];
                } else {
                    if ($dayInfo['is_weekend']) {
                        $dayRecords[$d] = [
                            'status' => 'weekend',
                            'badge' => '—',
                            'badge_color' => 'gray',
                        ];
                    } elseif ($dayInfo['is_future']) {
                        $dayRecords[$d] = [
                            'status' => 'future',
                            'badge' => '·',
                            'badge_color' => 'lightgray',
                        ];
                    } else {
                        $dayRecords[$d] = [
                            'status' => 'absent',
                            'badge' => 'A',
                            'badge_color' => 'red',
                        ];
                    }
                }
            }

            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $formattedTotalDuration = $hours > 0 ? "{$hours} Jam {$minutes} Menit" : ($minutes > 0 ? "{$minutes} Menit" : "-");

            $matrixRows[] = [
                'pegawai' => $pegawai,
                'days' => $dayRecords,
                'total_hadir' => $totalHadir,
                'total_late' => $totalLate,
                'total_wfo' => $totalWfo,
                'total_wfh' => $totalWfh,
                'total_duration' => $formattedTotalDuration,
                'total_seconds' => $totalSeconds,
            ];
        }

        return [
            'days' => $days,
            'days_in_month' => $daysInMonth,
            'month' => $month,
            'year' => $year,
            'month_name' => Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y'),
            'paginator' => $pegawaiList,
            'rows' => $matrixRows,
        ];
    }
}
