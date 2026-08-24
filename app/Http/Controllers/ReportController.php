<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Exports\DukExport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Export Daftar Urut Kepangkatan (DUK) ke PDF
     */
    public function exportDukPdf(Request $request)
    {
        $query = Pegawai::with(['unitKerja', 'jabatan', 'golongan']);

        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        // Fetch dan urutkan data pegawai berdasarkan hierarki golongan
        $pegawais = $query->get()->sortByDesc(function ($p) {
            return $p->golongan->nama_golongan ?? '';
        });

        $pdf = Pdf::loadView('exports.pdf.duk', compact('pegawais'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('DUK_Pegawai_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export Daftar Urut Kepangkatan (DUK) ke Excel
     */
    public function exportDukExcel(Request $request)
    {
        $unitKerjaId = $request->get('unit_kerja_id');
        return Excel::download(new DukExport($unitKerjaId), 'DUK_Pegawai_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Surat Keputusan / Pemberitahuan KGB ke PDF
     */
    public function exportKgbPdf($id)
    {
        $pegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])->findOrFail($id);

        $tmt = $pegawai->tanggal_masuk ?? $pegawai->tmt_sk_pertama;
        $diff = $tmt ? \Carbon\Carbon::parse($tmt)->diff(\Carbon\Carbon::now()) : null;

        $kgb = (object) [
            'id'                  => $pegawai->id,
            'pegawai'             => $pegawai,
            'gaji_lama'           => 0,
            'gaji_baru'           => 0,
            'terbilang_gaji_baru' => 'nol',
            'masa_kerja_tahun'    => $diff ? $diff->y : 0,
            'masa_kerja_bulan'    => $diff ? $diff->m : 0,
            'tmt_kgb_baru'        => $pegawai->kgb_berikutnya ? $pegawai->kgb_berikutnya->format('Y-m-d') : date('Y-m-d'),
        ];

        $pdf = Pdf::loadView('exports.pdf.sk-kgb', compact('kgb'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('SK_KGB_' . $pegawai->nip . '.pdf');
    }

    /**
     * Export Daftar Pengingat Kepegawaian (KGB, KP, Pensiun, Satyalancana) ke PDF
     */
    public function exportReminderPdf(Request $request)
    {
        $type = $request->get('type', 'all');
        $repository = app(\App\Repositories\Contracts\DashboardRepositoryInterface::class);
        $reminder = $repository->getReminder();

        $pdf = Pdf::loadView('exports.pdf.reminder', compact('reminder', 'type'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Pengingat_Kepegawaian_' . $type . '_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export Daftar Pengingat Kepegawaian (KGB, KP, Pensiun, Satyalancana) ke Excel
     */
    public function exportReminderExcel(Request $request)
    {
        $type = $request->get('type', 'all');
        return Excel::download(new \App\Exports\ReminderExport($type), 'Pengingat_Kepegawaian_' . $type . '_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Layanan Stream / Preview Berkas Privat Terproteksi Autentikasi & Autorisasi (SEC-NEW-01 Fix)
     */
    public function streamPrivateFile(Request $request, string $path)
    {
        // 1. Otorisasi Dasar Autentikasi
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // 2. Proteksi Path Traversal & Normalisasi Input
        if (str_contains($path, "\0")) {
            abort(404);
        }

        $decodedPath = rawurldecode($path);

        // Tolak secara eksplisit jika mengandung komponen '..' atau karakter absolut
        if (str_contains($decodedPath, '..') || str_starts_with($decodedPath, '/') || str_starts_with($decodedPath, '\\') || preg_match('/^[a-zA-Z]:[\\\\\/]/', $decodedPath)) {
            abort(403);
        }

        // Normalisasi separator
        $relativePath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $decodedPath), DIRECTORY_SEPARATOR);

        // Tentukan kandidat path fisik
        $privateStorageRoot = realpath(storage_path('app/private'));
        $publicStorageRoot  = realpath(storage_path('app/public'));

        $targetPath = storage_path('app/private' . DIRECTORY_SEPARATOR . $relativePath);
        if (!file_exists($targetPath)) {
            $targetPath = storage_path('app/public' . DIRECTORY_SEPARATOR . $relativePath);
            if (!file_exists($targetPath)) {
                if ($request->expectsJson() || $request->is('api/*') || !$request->headers->has('referer')) {
                    abort(404, 'Dokumen tidak ditemukan.');
                }
                return redirect()->back()->with('error', 'Berkas fisik belum diunggah atau tidak ditemukan di server. Silakan edit dan unggah ulang berkas.');
            }
        }

        $realTarget = realpath($targetPath);
        if ($realTarget === false) {
            if ($request->expectsJson() || $request->is('api/*') || !$request->headers->has('referer')) {
                abort(404, 'Dokumen tidak ditemukan.');
            }
            return redirect()->back()->with('error', 'Berkas fisik belum diunggah atau tidak ditemukan di server. Silakan edit dan unggah ulang berkas.');
        }

        // 3. Containment Check: Pastikan file berada di dalam storage root yang diizinkan
        $isInsidePrivate = $privateStorageRoot && (str_starts_with($realTarget, $privateStorageRoot . DIRECTORY_SEPARATOR) || $realTarget === $privateStorageRoot);
        $isInsidePublic  = $publicStorageRoot && (str_starts_with($realTarget, $publicStorageRoot . DIRECTORY_SEPARATOR) || $realTarget === $publicStorageRoot);

        if (!$isInsidePrivate && !$isInsidePublic) {
            abort(403);
        }

        // 4. Blokir Ekstensi & File Sensitif Sistem
        $forbiddenExtensions = ['env', 'php', 'htaccess', 'git', 'json', 'lock', 'yml', 'yaml', 'sqlite', 'log', 'key'];
        $extension = strtolower(pathinfo($realTarget, PATHINFO_EXTENSION));
        $filename  = basename($realTarget);

        if (in_array($extension, $forbiddenExtensions, true) || str_starts_with($filename, '.')) {
            abort(403);
        }

        // 5. Otorisasi Hak Akses (IDOR Protection)
        // Admin & Pimpinan memiliki hak akses membaca dokumen
        if (!$user->hasRole(['admin', 'pimpinan'])) {
            $ownerPegawaiId = $this->resolveFileOwnerPegawaiId($path);
            if ($ownerPegawaiId) {
                $userPegawai = Pegawai::where('email', $user->email)->orWhere('nip', $user->name)->first();
                if (!$userPegawai || $userPegawai->id !== $ownerPegawaiId) {
                    abort(403, 'Anda tidak memiliki hak akses untuk membuka dokumen ini.');
                }
            } else {
                abort(403);
            }
        }

        return response()->file($realTarget);
    }

    /**
     * Helper untuk melacak ID Pegawai pemilik berkas berdasarkan record DB
     */
    private function resolveFileOwnerPegawaiId(string $path): ?int
    {
        $cleanPath = ltrim(str_replace('\\', '/', $path), '/');

        $pegawai = Pegawai::where('file_sk_pertama', $cleanPath)
            ->orWhere('file_sk_pangkat_terakhir', $cleanPath)
            ->orWhere('file_sk_kgb_terakhir', $cleanPath)
            ->orWhere('foto', $cleanPath)
            ->first();
        if ($pegawai) return $pegawai->id;

        $rp = \App\Models\RiwayatPangkat::where('file_sk', $cleanPath)->first();
        if ($rp) return $rp->pegawai_id;

        $rj = \App\Models\RiwayatJabatan::where('file_sk', $cleanPath)->first();
        if ($rj) return $rj->pegawai_id;

        $rpend = \App\Models\RiwayatPendidikan::where('file_ijazah', $cleanPath)->orWhere('ijazah', $cleanPath)->first();
        if ($rpend) return $rpend->pegawai_id;

        $rd = \App\Models\RiwayatDiklat::where('file_sertifikat', $cleanPath)->orWhere('sertifikat', $cleanPath)->first();
        if ($rd) return $rd->pegawai_id;

        $mp = \App\Models\MutasiPegawai::where('file_sk', $cleanPath)->first();
        if ($mp) return $mp->pegawai_id;

        return null;
    }
}