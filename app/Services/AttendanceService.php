<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EmployeeAttendanceLocation;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public const MAX_ALLOWED_RADIUS_METERS = 75.0;
    public const ATTENDANCE_TOKEN_TTL_SECONDS = 60; // Token kedaluwarsa dalam 60 detik
    
    // Ketentuan Jam Kerja ASN Universitas Riau (37,5 Jam/Minggu, 7,5 Jam/Hari Efektif)
    public const WORK_START_TIME = '07:30:00';
    public const WORK_END_MON_THU = '16:00:00';
    public const WORK_END_FRI = '16:30:00';
    public const BREAK_START_MON_THU = '12:00:00';
    public const BREAK_END_MON_THU = '13:00:00';
    public const BREAK_START_FRI = '11:45:00';
    public const BREAK_END_FRI = '13:15:00';
    public const STANDARD_WORK_MINUTES = 450; // 7,5 jam = 450 menit kerja efektif
    public const DEFAULT_LATE_TIME = '07:30:00';

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
     * Generate single-use anti-replay attendance token with 60s TTL.
     */
    public function generateAttendanceToken(User $user): string
    {
        $token = Str::random(40);
        Cache::put("att_token_{$user->id}_{$token}", true, self::ATTENDANCE_TOKEN_TTL_SECONDS);
        return $token;
    }

    /**
     * Validate and immediately burn the anti-replay token.
     */
    public function validateAndBurnToken(User $user, ?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        $key = "att_token_{$user->id}_{$token}";
        if (Cache::has($key)) {
            Cache::forget($key);
            return true;
        }
        return false;
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
     *     notes?: string|null,
     *     accuracy?: float|int|null,
     *     altitude?: float|int|null,
     *     speed?: float|int|null,
     *     is_mock?: bool|null,
     *     liveness_verified?: bool|null,
     *     liveness_challenge?: string|null,
     *     face_similarity_score?: float|null,
     *     device_fingerprint?: string|null,
     *     device_platform?: string|null,
     *     token?: string|null
     * } $payload
     * @return Attendance
     * @throws ValidationException
     */
    public function processAttendance(User $user, array $payload): Attendance
    {
        // 1. Anti-Replay Token (Single Use, 60s TTL)
        $token = $payload['token'] ?? null;
        if (!$this->validateAndBurnToken($user, $token)) {
            throw ValidationException::withMessages([
                'token' => 'Sesi presensi telah kedaluwarsa atau token tidak valid (Anti-Replay Protection). Silakan refresh halaman dan lakukan presensi kembali.',
            ]);
        }

        $type = strtolower($payload['attendance_type'] ?? 'wfo');
        if (!in_array($type, ['wfo', 'wfh'])) {
            throw ValidationException::withMessages([
                'attendance_type' => 'Tipe presensi tidak valid. Pilih WFO atau WFH.',
            ]);
        }

        // 2. Pilar 1: Sensor Geolocation & Mock Check
        $accuracy = isset($payload['accuracy']) ? (float) $payload['accuracy'] : null;
        if ($accuracy === null || $accuracy <= 0) {
            throw ValidationException::withMessages([
                'accuracy' => 'Presensi ditolak: Sinyal sensor GPS tidak valid atau terdeteksi penggunaan Mock/Fake GPS emulator (akurasi <= 0 meter).',
            ]);
        }
        if ($accuracy > self::MAX_ALLOWED_RADIUS_METERS) {
            throw ValidationException::withMessages([
                'accuracy' => "Presensi ditolak: Akurasi sinyal GPS Anda terlalu lemah ({$accuracy} meter > batas toleransi " . self::MAX_ALLOWED_RADIUS_METERS . " meter). Harap berada di area terbuka dan tunggu GPS mengunci posisi presisi.",
            ]);
        }

        $isMock = !empty($payload['is_mock']);
        if ($isMock) {
            throw ValidationException::withMessages([
                'is_mock' => 'Presensi ditolak: Terdeteksi manipulasi peramban / otomasi emulator (Headless/Mock DevTools).',
            ]);
        }

        // 3. Pilar 2: Uji Keaktifan Wajah (Liveness Detection)
        $livenessVerified = !empty($payload['liveness_verified']);
        $livenessChallenge = $payload['liveness_challenge'] ?? 'blink';
        $faceSimilarityScore = isset($payload['face_similarity_score']) ? (float) $payload['face_similarity_score'] : null;

        if (!$livenessVerified) {
            throw ValidationException::withMessages([
                'liveness_verified' => 'Presensi ditolak: Uji keaktifan wajah (Liveness Detection) belum berhasil diselesaikan.',
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

        // 4. Pilar 3: Integritas Perangkat (Anti-Titip Absen & Impossible Travel)
        $deviceFingerprint = $payload['device_fingerprint'] ?? null;
        $devicePlatform = $payload['device_platform'] ?? null;
        $altitude = isset($payload['altitude']) ? (float) $payload['altitude'] : null;
        $speed = isset($payload['speed']) ? (float) $payload['speed'] : null;
        $ipAddress = request()->ip();
        $userAgent = request()->userAgent();

        $isSuspicious = false;
        $suspiciousReasons = [];

        // Deteksi Multi-Akun pada 1 Perangkat di Hari yang Sama
        if ($deviceFingerprint) {
            $deviceCollisions = Attendance::whereDate('attendance_date', $today)
                ->where('device_fingerprint', $deviceFingerprint)
                ->where('user_id', '!=', $user->id)
                ->with('user')
                ->get();

            if ($deviceCollisions->isNotEmpty()) {
                $collidingNames = $deviceCollisions->pluck('user.name')->filter()->unique()->implode(', ');
                $isSuspicious = true;
                $suspiciousReasons[] = "Indikasi Multi-Akun / Titip Absen: Perangkat ini digunakan oleh pegawai lain ({$collidingNames}) pada hari yang sama.";
            }
        }

        // Deteksi Impossible Travel saat Check-out
        if ($action === 'check_out' && $existingAttendance && $existingAttendance->check_in_time && $existingAttendance->check_in_latitude && $existingAttendance->check_in_longitude) {
            $distanceFromCheckIn = $this->calculateDistance(
                (float) $existingAttendance->check_in_latitude,
                (float) $existingAttendance->check_in_longitude,
                $latitude,
                $longitude
            );
            $secondsElapsed = Carbon::parse($existingAttendance->check_in_time)->diffInSeconds($now);
            if ($secondsElapsed > 0) {
                $speedKmh = ($distanceFromCheckIn / 1000) / ($secondsElapsed / 3600);
                // Jarak > 2 km dengan rata-rata kecepatan > 120 km/jam
                if ($distanceFromCheckIn > 2000 && $speedKmh > 120) {
                    $isSuspicious = true;
                    $minutesElapsed = max(1, round($secondsElapsed / 60));
                    $suspiciousReasons[] = "Impossible Travel: Terdeteksi perpindahan jarak " . round($distanceFromCheckIn) . " m dalam {$minutesElapsed} menit (kecepatan " . round($speedKmh, 1) . " km/jam).";
                }
            }
        }

        $suspiciousReasonText = !empty($suspiciousReasons) ? implode("\n", $suspiciousReasons) : null;

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

        return DB::transaction(function () use (
            $user, $type, $today, $action, $latitude, $longitude, $distanceMeters,
            $photoPath, $notes, $existingAttendance, $now,
            $ipAddress, $userAgent, $accuracy, $altitude, $speed, $isMock,
            $livenessVerified, $livenessChallenge, $faceSimilarityScore,
            $deviceFingerprint, $devicePlatform, $isSuspicious, $suspiciousReasonText
        ) {
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
                        'ip_address' => $ipAddress,
                        'user_agent' => $userAgent,
                        'gps_accuracy' => $accuracy,
                        'gps_altitude' => $altitude,
                        'gps_speed' => $speed,
                        'is_mock_location' => $isMock,
                        'liveness_verified' => $livenessVerified,
                        'liveness_challenge' => $livenessChallenge,
                        'face_similarity_score' => $faceSimilarityScore,
                        'device_fingerprint' => $deviceFingerprint,
                        'device_platform' => $devicePlatform,
                        'is_suspicious' => $existingAttendance->is_suspicious || $isSuspicious,
                        'suspicious_reason' => trim(($existingAttendance->suspicious_reason ? $existingAttendance->suspicious_reason . "\n" : '') . ($suspiciousReasonText ?? '')),
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
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'gps_accuracy' => $accuracy,
                    'gps_altitude' => $altitude,
                    'gps_speed' => $speed,
                    'is_mock_location' => $isMock,
                    'liveness_verified' => $livenessVerified,
                    'liveness_challenge' => $livenessChallenge,
                    'face_similarity_score' => $faceSimilarityScore,
                    'device_fingerprint' => $deviceFingerprint,
                    'device_platform' => $devicePlatform,
                    'is_suspicious' => $isSuspicious,
                    'suspicious_reason' => $suspiciousReasonText,
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
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'gps_accuracy' => $accuracy,
                'gps_altitude' => $altitude,
                'gps_speed' => $speed,
                'is_mock_location' => $isMock,
                'liveness_verified' => $livenessVerified,
                'liveness_challenge' => $livenessChallenge,
                'face_similarity_score' => $faceSimilarityScore,
                'device_fingerprint' => $deviceFingerprint,
                'device_platform' => $devicePlatform,
                'is_suspicious' => $existingAttendance->is_suspicious || $isSuspicious,
                'suspicious_reason' => trim(($existingAttendance->suspicious_reason ? $existingAttendance->suspicious_reason . "\n" : '') . ($suspiciousReasonText ?? '')),
            ]);

            return $existingAttendance;
        });
    }

    /**
     * Dapatkan rincian jadwal kerja resmi ASN UNRI berdasarkan tanggal tertentu.
     * - Senin - Kamis: 07:30 - 16:00 (Istirahat 12:00 - 13:00)
     * - Jumat: 07:30 - 16:30 (Istirahat 11:45 - 13:15)
     */
    public static function getScheduleForDate(Carbon $date): array
    {
        $dateStr = $date->toDateString();
        $isFriday = ($date->dayOfWeek === Carbon::FRIDAY);

        $start = Carbon::parse($dateStr . ' ' . self::WORK_START_TIME, 'Asia/Jakarta');
        $end = Carbon::parse($dateStr . ' ' . ($isFriday ? self::WORK_END_FRI : self::WORK_END_MON_THU), 'Asia/Jakarta');
        $breakStart = Carbon::parse($dateStr . ' ' . ($isFriday ? self::BREAK_START_FRI : self::BREAK_START_MON_THU), 'Asia/Jakarta');
        $breakEnd = Carbon::parse($dateStr . ' ' . ($isFriday ? self::BREAK_END_FRI : self::BREAK_END_MON_THU), 'Asia/Jakarta');

        return [
            'is_friday' => $isFriday,
            'start' => $start,
            'end' => $end,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
            'break_duration_minutes' => (int) $breakStart->diffInMinutes($breakEnd),
            'target_work_minutes' => self::STANDARD_WORK_MINUTES,
        ];
    }

    /**
     * Hitung durasi keterlambatan masuk (menit lewat dari 07:30 WIB)
     */
    public static function calculateLateMinutes(Carbon $checkInTime): int
    {
        $in = $checkInTime->copy()->timezone('Asia/Jakarta');
        $schedule = self::getScheduleForDate($in);
        if ($in->greaterThan($schedule['start'])) {
            return (int) $schedule['start']->diffInMinutes($in);
        }
        return 0;
    }

    /**
     * Hitung durasi pulang sebelum waktunya / PSW (menit sebelum 16:00 atau 16:30 WIB)
     */
    public static function calculateEarlyLeaveMinutes(Carbon $checkOutTime, Carbon $date): int
    {
        $out = $checkOutTime->copy()->timezone('Asia/Jakarta');
        $schedule = self::getScheduleForDate($date);
        if ($out->lessThan($schedule['end'])) {
            return (int) $out->diffInMinutes($schedule['end']);
        }
        return 0;
    }

    /**
     * Hitung durasi jam kerja efektif dalam detik.
     * Mengurangkan potongan irisan jam istirahat resmi jika rentang presensi melintasinya.
     */
    public static function calculateEffectiveWorkSeconds(Carbon $in, Carbon $out): int
    {
        $inJkt = $in->copy()->timezone('Asia/Jakarta');
        $outJkt = $out->copy()->timezone('Asia/Jakarta');

        if ($outJkt->lessThanOrEqualTo($inJkt)) {
            return 0;
        }

        $grossSeconds = $inJkt->diffInSeconds($outJkt);
        $schedule = self::getScheduleForDate($inJkt);

        $breakStart = $schedule['break_start'];
        $breakEnd = $schedule['break_end'];

        // Overlap antara rentang kerja [$inJkt, $outJkt] dan rentang istirahat [$breakStart, $breakEnd]
        $overlapStart = $inJkt->greaterThan($breakStart) ? $inJkt : $breakStart;
        $overlapEnd = $outJkt->lessThan($breakEnd) ? $outJkt : $breakEnd;

        $breakSeconds = 0;
        if ($overlapEnd->greaterThan($overlapStart)) {
            $breakSeconds = $overlapStart->diffInSeconds($overlapEnd);
        }

        return max(0, $grossSeconds - $breakSeconds);
    }

    /**
     * Tentukan status kehadiran (Hadir Tepat Waktu vs Terlambat).
     * Terlambat jika check-in melewati jam 07:30:00 WIB.
     */
    public function determineStatus(Carbon $time): string
    {
        $timeInJakarta = $time->copy()->timezone('Asia/Jakarta');
        $schedule = self::getScheduleForDate($timeInJakarta);
        return $timeInJakarta->greaterThan($schedule['start']) ? 'late' : 'present';
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
     * Filter presensi untuk panel rekap Admin & Pimpinan
     */
    public function filterAttendances(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Attendance::with(['user.pegawai.unitKerja', 'user.pegawai.jabatan'])
            ->latest('attendance_date')
            ->latest('check_in_time');

        if (!empty($filters['bawahan_ids'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->whereIn('pegawai_id', $filters['bawahan_ids']);
            });
        }

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
    public function todayStatistics(?array $bawahanIds = null): array
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $pegawaiQuery = Pegawai::query();
        $attQuery = Attendance::whereDate('attendance_date', $today);

        if ($bawahanIds !== null) {
            $pegawaiQuery->whereIn('id', $bawahanIds);
            $attQuery->whereHas('user', fn($q) => $q->whereIn('pegawai_id', $bawahanIds));
        }

        $totalPegawai = $pegawaiQuery->count();
        if ($totalPegawai === 0) {
            $totalPegawai = User::count();
        }

        $totalUsers = User::count();
        $presentCount = (clone $attQuery)->count();
        $wfoCount = (clone $attQuery)->where('attendance_type', 'wfo')->count();
        $wfhCount = (clone $attQuery)->where('attendance_type', 'wfh')->count();
        $lateCount = (clone $attQuery)->where('status', 'late')->count();
        $suspiciousCount = Schema::hasColumn('attendances', 'is_suspicious')
            ? (clone $attQuery)->where('is_suspicious', true)->count()
            : 0;

        return [
            'total_users' => $totalPegawai,
            'total_pegawai' => $totalPegawai,
            'total_user_accounts' => $totalUsers,
            'total_present' => $presentCount,
            'total_wfo' => $wfoCount,
            'total_wfh' => $wfhCount,
            'total_late' => $lateCount,
            'total_suspicious' => $suspiciousCount,
        ];
    }

    /**
     * Dapatkan data Matriks Presensi Bulanan (Kalender 1 - 31) untuk semua pegawai / bawahan
     */
    public function getMonthlyMatrix(int $month, int $year, ?string $search = null, int $perPage = 50, ?array $bawahanIds = null): array
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

        if ($bawahanIds !== null) {
            $query->whereIn('id', $bawahanIds);
        }

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
            $totalLateCount = 0;
            $totalEarlyCount = 0;
            $totalLateMinutes = 0;
            $totalEarlyMinutes = 0;
            $totalWfo = 0;
            $totalWfh = 0;
            $totalEffectiveSeconds = 0;

            $dayRecords = [];
            foreach ($days as $d => $dayInfo) {
                $att = $attendancesByDay[$d] ?? null;
                if ($att) {
                    $totalHadir++;
                    if ($att->attendance_type === 'wfh') {
                        $totalWfh++;
                    } else {
                        $totalWfo++;
                    }

                    $lateMinutes = 0;
                    $earlyMinutes = 0;

                    if ($att->check_in_time) {
                        $lateMinutes = self::calculateLateMinutes($att->check_in_time);
                        if ($lateMinutes > 0) {
                            $totalLateCount++;
                            $totalLateMinutes += $lateMinutes;
                        }
                    }

                    $attDate = $att->attendance_date ? Carbon::parse($att->attendance_date) : ($att->check_in_time ?? $now);
                    if ($att->check_out_time) {
                        $earlyMinutes = self::calculateEarlyLeaveMinutes($att->check_out_time, $attDate);
                        if ($earlyMinutes > 0) {
                            $totalEarlyCount++;
                            $totalEarlyMinutes += $earlyMinutes;
                        }
                    }

                    if ($att->check_in_time && $att->check_out_time) {
                        $effectiveSecs = self::calculateEffectiveWorkSeconds($att->check_in_time, $att->check_out_time);
                        $totalEffectiveSeconds += $effectiveSecs;
                    }

                    $dayRecords[$d] = [
                        'status' => $att->status,
                        'type' => $att->attendance_type,
                        'in' => $att->formatted_check_in_time,
                        'out' => $att->formatted_check_out_time,
                        'duration' => $att->work_duration,
                        'late_minutes' => $lateMinutes,
                        'early_leave_minutes' => $earlyMinutes,
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

            $hours = floor($totalEffectiveSeconds / 3600);
            $minutes = floor(($totalEffectiveSeconds % 3600) / 60);
            $formattedTotalDuration = $hours > 0 ? "{$hours} Jam {$minutes} Menit" : ($minutes > 0 ? "{$minutes} Menit" : "-");

            // Total Pelanggaran Disiplin Waktu ASN UNRI (Keterlambatan + Pulang Cepat)
            $totalViolationMinutes = $totalLateMinutes + $totalEarlyMinutes;
            $sanksiHari = intdiv($totalViolationMinutes, self::STANDARD_WORK_MINUTES); // Setiap 450 menit = 1 hari sanksi
            $sisaMenitSanksi = $totalViolationMinutes % self::STANDARD_WORK_MINUTES;

            $vHours = floor($totalViolationMinutes / 60);
            $vMins = $totalViolationMinutes % 60;
            $formattedViolationTime = $vHours > 0 ? "{$vHours}j {$vMins}m" : "{$vMins}m";

            $formattedSanksi = $sanksiHari > 0
                ? "{$sanksiHari} Hari (Sisa {$sisaMenitSanksi}m)"
                : ($totalViolationMinutes > 0 ? "0 Hari ({$formattedViolationTime})" : "-");

            $matrixRows[] = [
                'pegawai' => $pegawai,
                'days' => $dayRecords,
                'total_hadir' => $totalHadir,
                'total_late' => $totalLateCount,
                'total_late_minutes' => $totalLateMinutes,
                'total_early_count' => $totalEarlyCount,
                'total_early_minutes' => $totalEarlyMinutes,
                'total_violation_minutes' => $totalViolationMinutes,
                'formatted_violation_time' => $formattedViolationTime,
                'sanksi_hari' => $sanksiHari,
                'sisa_menit_sanksi' => $sisaMenitSanksi,
                'formatted_sanksi' => $formattedSanksi,
                'total_wfo' => $totalWfo,
                'total_wfh' => $totalWfh,
                'total_duration' => $formattedTotalDuration,
                'total_seconds' => $totalEffectiveSeconds,
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
