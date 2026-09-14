<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Menampilkan Halaman Audit Log (Rekam Aktivitas User)
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $type   = strtoupper(trim((string) $request->get('type', '')));

        $query = ActivityLog::with('user')->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($type !== '' && $type !== 'ALL') {
            $query->where('activity_type', $type);
        }

        $logs = $query->paginate(20)->withQueryString();

        $stats = [
            'total'   => ActivityLog::count(),
            'login'   => ActivityLog::where('activity_type', 'LOGIN')->count(),
            'create'  => ActivityLog::where('activity_type', 'CREATE')->count(),
            'update'  => ActivityLog::where('activity_type', 'UPDATE')->count(),
            'delete'  => ActivityLog::where('activity_type', 'DELETE')->count(),
            'upload'  => ActivityLog::where('activity_type', 'UPLOAD_FILE')->count(),
        ];

        return view('audit-logs.index', compact('logs', 'stats', 'search', 'type'));
    }

    /**
     * Membersihkan Log Aktivitas Lama (> 90 Hari)
     */
    public function prune(Request $request)
    {
        $days = (int) $request->get('days', 90);
        $deleted = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()
            ->route('audit-logs.index')
            ->with('success', "Berhasil membersihkan {$deleted} baris log aktivitas lama (> {$days} hari).");
    }
}
