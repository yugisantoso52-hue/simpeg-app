<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendanceLocation extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance_locations';

    protected $fillable = [
        'user_id',
        'wfo_latitude',
        'wfo_longitude',
        'wfo_locked_at',
        'wfh_latitude',
        'wfh_longitude',
        'wfh_locked_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'wfo_latitude' => 'float',
            'wfo_longitude' => 'float',
            'wfo_locked_at' => 'datetime',
            'wfh_latitude' => 'float',
            'wfh_longitude' => 'float',
            'wfh_locked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isWfoLocked(): bool
    {
        return !is_null($this->wfo_locked_at) && !is_null($this->wfo_latitude) && !is_null($this->wfo_longitude);
    }

    public function isWfhLocked(): bool
    {
        return !is_null($this->wfh_locked_at) && !is_null($this->wfh_latitude) && !is_null($this->wfh_longitude);
    }

    public function isLocked(string $type): bool
    {
        return strtolower($type) === 'wfh' ? $this->isWfhLocked() : $this->isWfoLocked();
    }

    public function getCoordinates(string $type): ?array
    {
        $type = strtolower($type);
        if ($type === 'wfh') {
            if (!$this->isWfhLocked()) {
                return null;
            }
            return [
                'latitude' => (float) $this->wfh_latitude,
                'longitude' => (float) $this->wfh_longitude,
                'locked_at' => $this->wfh_locked_at,
            ];
        }

        if (!$this->isWfoLocked()) {
            return null;
        }

        return [
            'latitude' => (float) $this->wfo_latitude,
            'longitude' => (float) $this->wfo_longitude,
            'locked_at' => $this->wfo_locked_at,
        ];
    }
}
