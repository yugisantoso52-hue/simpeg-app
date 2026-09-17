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
        return $this->check_in_photo_path ? Storage::disk('public')->url($this->check_in_photo_path) : null;
    }

    public function getCheckOutPhotoUrlAttribute(): ?string
    {
        return $this->check_out_photo_path ? Storage::disk('public')->url($this->check_out_photo_path) : null;
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
