<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLoggerService
{
    /**
     * Mencatat Log Aktivitas secara Asinkron / Non-blocking
     */
    public static function log(
        string $activityType,
        string $description,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $changes = null,
        ?User $user = null
    ): void {
        $request = request();
        $currentUser = $user ?? Auth::user();

        $userId    = $currentUser?->id;
        $userName  = $currentUser?->name ?? 'Sistem / Pengguna';
        $userEmail = $currentUser?->email;
        $roleName  = $currentUser?->role?->name ?? 'Guest';
        $ip        = $request?->ip() ?? '127.0.0.1';
        $userAgent = substr((string) ($request?->userAgent() ?? '-'), 0, 500);

        // Menggunakan callback terminating Laravel (berjalan di background setelah HTTP response dikirim)
        app()->terminating(function () use (
            $userId,
            $userName,
            $userEmail,
            $roleName,
            $activityType,
            $subjectType,
            $subjectId,
            $description,
            $changes,
            $ip,
            $userAgent
        ) {
            try {
                ActivityLog::create([
                    'user_id'       => $userId,
                    'user_name'     => $userName,
                    'user_email'    => $userEmail,
                    'role_name'     => $roleName,
                    'activity_type' => strtoupper($activityType),
                    'subject_type'  => $subjectType,
                    'subject_id'    => $subjectId,
                    'description'   => $description,
                    'changes'       => $changes,
                    'ip_address'    => $ip,
                    'user_agent'    => $userAgent,
                ]);
            } catch (\Throwable $e) {
                Log::warning("Gagal mencatat ActivityLog: " . $e->getMessage());
            }
        });
    }

    /**
     * Catat Aktivitas Login User
     */
    public static function logLogin(User $user): void
    {
        try {
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("Gagal update last_login_at pada user ID {$user->id}: " . $e->getMessage());
        }

        self::log(
            activityType: 'LOGIN',
            description: "Pengguna {$user->name} ({$user->email}) berhasil masuk ke sistem.",
            subjectType: 'User',
            subjectId: $user->id,
            user: $user
        );
    }

    /**
     * Catat Aktivitas Logout User
     */
    public static function logLogout(User $user): void
    {
        self::log(
            activityType: 'LOGOUT',
            description: "Pengguna {$user->name} ({$user->email}) keluar dari sistem.",
            subjectType: 'User',
            subjectId: $user->id,
            user: $user
        );
    }

    /**
     * Catat Aktivitas Perubahan Data (Update)
     */
    public static function logUpdate(string $subjectType, int $subjectId, string $description, ?array $oldData = null, ?array $newData = null): void
    {
        $changes = null;
        if ($oldData || $newData) {
            $changes = [
                'before' => $oldData,
                'after'  => $newData,
            ];
        }

        self::log(
            activityType: 'UPDATE',
            description: $description,
            subjectType: $subjectType,
            subjectId: $subjectId,
            changes: $changes
        );
    }

    /**
     * Catat Aktivitas Pembuatan Data (Create)
     */
    public static function logCreate(string $subjectType, int $subjectId, string $description, ?array $data = null): void
    {
        self::log(
            activityType: 'CREATE',
            description: $description,
            subjectType: $subjectType,
            subjectId: $subjectId,
            changes: $data ? ['created' => $data] : null
        );
    }

    /**
     * Catat Aktivitas Penghapusan Data (Delete)
     */
    public static function logDelete(string $subjectType, int $subjectId, string $description): void
    {
        self::log(
            activityType: 'DELETE',
            description: $description,
            subjectType: $subjectType,
            subjectId: $subjectId
        );
    }

    /**
     * Catat Aktivitas Upload Berkas PDF/Gambar
     */
    public static function logUpload(string $subjectType, int $subjectId, string $fieldName, string $fileName): void
    {
        self::log(
            activityType: 'UPLOAD_FILE',
            description: "Mengunggah berkas [{$fieldName}]: {$fileName}",
            subjectType: $subjectType,
            subjectId: $subjectId
        );
    }
}
