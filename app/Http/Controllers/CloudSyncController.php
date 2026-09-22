<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class CloudSyncController extends Controller
{
    /**
     * Web Trigger: Admin clicks a button to sync live data from Railway to Localhost
     */
    public function pullFromWeb(Request $request)
    {
        return abort(403, 'SYSTEM HALTED: Fitur sinkronisasi lama dinonaktifkan demi keamanan data. Sistem sinkronisasi baru sedang dikembangkan.');

        try {
            $exitCode = Artisan::call('simpeg:pull-cloud');
            $output = Artisan::output();

            if ($exitCode === 0) {
                return redirect()->route('dashboard')->with('success', 'Alhamdulillah! Sinkronisasi Data dari Cloud Railway (29 Pegawai) berhasil diperbarui ke Localhost.');
            } else {
                return redirect()->back()->with('error', 'Gagal menyinkronkan data: ' . $output);
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    /**
     * Secret token validation for secure data synchronization
     */
    protected function validateToken(Request $request): bool
    {
        $token = $request->query('key') ?? $request->header('X-Sync-Key');
        $configuredKey = env('SYNC_TOKEN') ?: config('app.key');

        if (empty($configuredKey) || empty($token)) {
            return false;
        }

        return hash_equals((string)$configuredKey, (string)$token);
    }

    /**
     * Export all active database tables and uploaded files list
     */
    public function export(Request $request)
    {
        if (!$this->validateToken($request)) {
            return response()->json(['error' => 'Akses ditolak. Kunci sinkronisasi tidak valid.'], Response::HTTP_UNAUTHORIZED);
        }

        // List of tables to sync in order of dependency
        $tables = [
            'roles',
            'permissions',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'unit_kerja',
            'jabatan',
            'golongan',
            'jenis_jabatan',
            'users',
            'pegawai',
            'riwayat_pendidikan',
            'riwayat_jabatan',
            'riwayat_pangkat',
            'riwayat_diklat',
            'riwayat_str_sip',
            'riwayat_skp',
            'riwayat_penghargaan',
            'riwayat_organisasi',
            'riwayat_publikasi',
            'tugas_belajar',
            'mutasi_pegawai',
            'pengajuan_cuti',
            'notifications',
        ];

        $data = [];
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $data[$table] = DB::table($table)->get()->toArray();
            }
        }

        // Get list of uploaded public storage files
        $publicFiles = [];
        if (File::exists(storage_path('app/public'))) {
            $allFiles = File::allFiles(storage_path('app/public'));
            foreach ($allFiles as $file) {
                $publicFiles[] = str_replace('\\', '/', $file->getRelativePathname());
            }
        }

        return response()->json([
            'status' => 'success',
            'app_name' => config('app.name'),
            'environment' => config('app.env'),
            'exported_at' => now()->toIso8601String(),
            'tables' => $data,
            'files' => $publicFiles,
        ]);
    }

    /**
     * Download a specific storage file during cloud sync (dengan proteksi Path Traversal)
     */
    public function downloadFile(Request $request, $path)
    {
        if (!$this->validateToken($request)) {
            return response()->json(['error' => 'Akses ditolak.'], Response::HTTP_UNAUTHORIZED);
        }

        $decodedPath = rawurldecode($path);
        if (str_contains($decodedPath, '..') || str_contains($decodedPath, "\0")) {
            return response()->json(['error' => 'Jalur berkas tidak valid.'], Response::HTTP_BAD_REQUEST);
        }

        $cleanPath = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $decodedPath), DIRECTORY_SEPARATOR);

        $publicStorageRoot = realpath(storage_path('app/public'));
        $privateStorageRoot = realpath(storage_path('app/private'));

        $targetPath = storage_path('app/public' . DIRECTORY_SEPARATOR . $cleanPath);
        if (!file_exists($targetPath)) {
            $targetPath = storage_path('app/private' . DIRECTORY_SEPARATOR . $cleanPath);
        }

        if (file_exists($targetPath)) {
            $realTarget = realpath($targetPath);
            if ($realTarget !== false) {
                $isInsidePublic = $publicStorageRoot && str_starts_with($realTarget, $publicStorageRoot . DIRECTORY_SEPARATOR);
                $isInsidePrivate = $privateStorageRoot && str_starts_with($realTarget, $privateStorageRoot . DIRECTORY_SEPARATOR);

                if ($isInsidePublic || $isInsidePrivate) {
                    return response()->download($realTarget);
                }
            }
        }

        return response()->json(['error' => 'Berkas tidak ditemukan'], Response::HTTP_NOT_FOUND);
    }
}
