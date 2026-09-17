<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'attendance_type',
        'attendance_date',
        'check_in_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_distance_meters',
        'check_in_photo_path',
        'check_out_time',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_distance_meters',
        'check_out_photo_path',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_time' => 'datetime',
            'check_in_latitude' => 'float',
            'check_in_longitude' => 'float',
            'check_in_distance_meters' => 'float',
            'check_out_time' => 'datetime',
            'check_out_latitude' => 'float',
            'check_out_longitude' => 'float',
            'check_out_distance_meters' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Accessors for UI convenience
    public function getCheckInPhotoUrlAttribute(): ?string
    {
        return $this->check_in_photo_path ? route('presensi.photo', ['id' => $this->id, 'type' => 'in']) : null;
    }

    public function getCheckOutPhotoUrlAttribute(): ?string
    {
        return $this->check_out_photo_path ? route('presensi.photo', ['id' => $this->id, 'type' => 'out']) : null;
    }

    /**
     * Hitung Keterlambatan Masuk (Menit setelah 07:30 WIB)
     */
    public function getLateMinutesAttribute(): int
    {
        if (!$this->check_in_time) {
            return 0;
        }
        return \App\Services\AttendanceService::calculateLateMinutes($this->check_in_time);
    }

    /**
     * Hitung Pulang Sebelum Waktunya / PSW (Menit sebelum 16:00 atau 16:30 WIB)
     */
    public function getEarlyLeaveMinutesAttribute(): int
    {
        if (!$this->check_out_time) {
            return 0;
        }
        $date = $this->attendance_date ? \Carbon\Carbon::parse($this->attendance_date) : $this->check_out_time;
        return \App\Services\AttendanceService::calculateEarlyLeaveMinutes($this->check_out_time, $date);
    }

    /**
     * Hitung Total Detik Kerja Efektif (Sudah dikurangi jam istirahat resmi ASN UNRI)
     */
    public function getEffectiveWorkSecondsAttribute(): int
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return 0;
        }
        return \App\Services\AttendanceService::calculateEffectiveWorkSeconds($this->check_in_time, $this->check_out_time);
    }

    /**
     * Hitung Penjumlahan / Total Jam Kerja Efektif (Jam Masuk s/d Jam Pulang - Jam Istirahat)
     */
    public function getWorkDurationAttribute(): string
    {
        if (!$this->check_in_time) {
            return '-';
        }

        if (!$this->check_out_time) {
            return 'Sedang Bekerja (Belum Pulang)';
        }

        $effectiveSeconds = $this->effective_work_seconds;
        $hours = floor($effectiveSeconds / 3600);
        $minutes = floor(($effectiveSeconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours} Jam {$minutes} Menit";
        }

        if ($minutes > 0) {
            return "{$minutes} Menit";
        }

        return "{$effectiveSeconds} Detik";
    }

    /**
     * Format Ringkas Total Jam Kerja Efektif (e.g. untuk Export Excel & PDF)
     */
    public function getWorkDurationShortAttribute(): string
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return '-';
        }

        $effectiveSeconds = $this->effective_work_seconds;
        $hours = floor($effectiveSeconds / 3600);
        $minutes = floor(($effectiveSeconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function getFormattedCheckInTimeAttribute(): ?string
    {
        return $this->check_in_time ? $this->check_in_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-';
    }

    public function getFormattedCheckOutTimeAttribute(): ?string
    {
        return $this->check_out_time ? $this->check_out_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : 'Belum Check-Out';
    }

    public function getFormattedCheckInTimeSecAttribute(): ?string
    {
        return $this->check_in_time ? $this->check_in_time->timezone('Asia/Jakarta')->format('H:i:s') . ' WIB' : '-';
    }

    public function getFormattedCheckOutTimeSecAttribute(): ?string
    {
        return $this->check_out_time ? $this->check_out_time->timezone('Asia/Jakarta')->format('H:i:s') . ' WIB' : '-';
    }

    // Aliases matching prompt spec
    public function getPhotoPathAttribute(): ?string
    {
        return $this->check_in_photo_path;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->check_in_photo_url;
    }

    public function getLatitudeAttribute(): ?float
    {
        return $this->check_in_latitude;
    }

    public function getLongitudeAttribute(): ?float
    {
        return $this->check_in_longitude;
    }

    public function getDistanceMetersAttribute(): ?float
    {
        return $this->check_in_distance_meters;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'present' => ['label' => 'Hadir Tepat Waktu', 'class' => 'bg-green-100 text-green-800 border-green-200'],
            'late' => ['label' => 'Terlambat', 'class' => 'bg-amber-100 text-amber-800 border-amber-200'],
            'leave' => ['label' => 'Izin / Cuti', 'class' => 'bg-blue-100 text-blue-800 border-blue-200'],
            default => ['label' => ucfirst($this->status), 'class' => 'bg-gray-100 text-gray-800 border-gray-200'],
        };
    }
}
