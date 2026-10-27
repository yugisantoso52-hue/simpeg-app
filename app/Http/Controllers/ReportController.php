<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Exports\DukExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Export Daftar Urut Kepangkatan (DUK) ke PDF
     */
    public function exportDukPdf(Request $request)
    {
        if (!auth()->user()?->canAccessExecutiveKepegawaianMenus()) {
            abort(403, 'Akses ekspor seluruh DUK dibatasi hanya untuk Pimpinan dan Administrator demi privasi ASN.');
        }

        $query = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])
            ->where('status_pegawai', 'Aktif')
            ->orderByDuk();

        if ($request->filled('unit_kerja_id')) {
            $query->where('pegawai.unit_kerja_id', $request->unit_kerja_id);
        }

        // Fetch dan urutkan data pegawai menggunakan scope DUK standar BKN
        $pegawais = $query->get();

        $pdf = Pdf::loadView('exports.pdf.duk', compact('pegawais'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('DUK_Pegawai_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export Daftar Urut Kepangkatan (DUK) ke Excel
     */
    public function exportDukExcel(Request $request)
    {
        if (!auth()->user()?->canAccessExecutiveKepegawaianMenus()) {
            abort(403, 'Akses ekspor seluruh DUK dibatasi hanya untuk Pimpinan dan Administrator demi privasi ASN.');
        }

        $unitKerjaId = $request->get('unit_kerja_id');
        return Excel::download(new DukExport($unitKerjaId), 'DUK_Pegawai_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Surat Keputusan / Pemberitahuan KGB ke PDF
     */
    public function exportKgbPdf($id)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        // 1. Resolusi Pegawai & PengajuanKarir
        $pengajuan = \App\Models\PengajuanKarir::with(['pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan'])->find($id);
        if ($pengajuan) {
            $pegawai = $pengajuan->pegawai;
        } else {
            $pegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])->findOrFail($id);
            $pengajuan = \App\Models\PengajuanKarir::where('pegawai_id', $pegawai->id)
                ->where('jenis_pengajuan', 'KGB')
                ->latest()
                ->first();
        }

        $isExecutive = $user->canAccessExecutiveKepegawaianMenus();
        $isOwn = ($user->pegawai_id == $pegawai->id || $user->pegawai?->id == $pegawai->id);
        if (!$isExecutive && !$isOwn) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak dokumen pegawai lain.');
        }

        $isPppk  = str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PPPK');

        // 2. Tanggal TMT Dasar dan Perhitungan Masa Kerja Golongan (MKG)
        $tmtDasar = $pegawai->tmt_kgb_terakhir ?? ($pegawai->tmt_pangkat_terakhir ?? ($pegawai->tanggal_masuk ?? $pegawai->tmt_sk_pertama));
        
        $mkgTahunLama = (int) ($pegawai->mkg_tahun ?? 0);
        $mkgBulanLama = (int) ($pegawai->mkg_bulan ?? 0);

        // Jika MKG tercatat 0 namun ada riwayat TMT
        if ($mkgTahunLama === 0 && $tmtDasar) {
            $diff = \Carbon\Carbon::parse($tmtDasar)->diff(\Carbon\Carbon::now());
            // Untuk pegawai baru/tmt dasar awal
            $mkgTahunLama = 0;
            $mkgBulanLama = 0;
        }

        // Kenaikan Gaji Berkala menambah masa kerja golongan sebanyak 2 tahun
        $mkgTahunBaru = $mkgTahunLama + 2;
        $mkgBulanBaru = $mkgBulanLama;

        // 3. Otomatisasi Nilai Gaji Pokok Berdasarkan PP 5/2024 (PNS) & Perpres 11/2024 (PPPK)
        $namaGolongan = $pegawai->golongan->nama_golongan ?? ($isPppk ? 'IX' : 'III/c');
        $gajiLama     = \App\Services\GajiService::hitungGajiPegawai($pegawai, $mkgTahunLama);
        $gajiBaru     = \App\Services\GajiService::hitungGajiPegawai($pegawai, $mkgTahunBaru);

        // Tanggal TMT KGB Baru (2 tahun dari TMT lama)
        $tmtKgbBaru = $pegawai->kgb_berikutnya 
            ? $pegawai->kgb_berikutnya->format('Y-m-d') 
            : ($tmtDasar ? \Carbon\Carbon::parse($tmtDasar)->addYears(2)->format('Y-m-d') : date('Y-m-d'));

        $kgb = (object) [
            'id'                  => $pegawai->id,
            'pegawai'             => $pegawai,
            'gaji_lama'           => $gajiLama,
            'gaji_baru'           => $gajiBaru,
            'masa_kerja_tahun'    => $mkgTahunBaru,
            'masa_kerja_bulan'    => $mkgBulanBaru,
            'tmt_kgb_baru'        => $tmtKgbBaru,
        ];

        // 4. Pejabat Penandatangan & Paraf Digital Dinamis
        $pejabatService = app(\App\Services\PejabatService::class);
        $dekanData      = $pejabatService->getDekan();
        $wadek2Data     = $pejabatService->getWadek2();
        $kabagData      = $pejabatService->getKabag();
        $kapokjaData    = $pejabatService->getKaPokja();

        $pejabatDekan   = $dekanData['pegawai'];
        $pejabatWd2     = $wadek2Data['pegawai'];
        $pejabatKabag   = $kabagData['pegawai'];
        $pejabatKaPokja = $kapokjaData['pegawai'];

        // 5. Data Dasar SK Terakhir (Poin a s.d. e)
        $tahunSurat     = date('Y');
        $tanggalSurat   = \Carbon\Carbon::now()->translatedFormat('d F Y');
        $dasarSkTanggal = $pegawai->tanggal_sk_kgb_terakhir 
            ? \Carbon\Carbon::parse($pegawai->tanggal_sk_kgb_terakhir)->translatedFormat('d F Y') 
            : ($pegawai->tanggal_sk_pangkat_terakhir ? \Carbon\Carbon::parse($pegawai->tanggal_sk_pangkat_terakhir)->translatedFormat('d F Y') : '4 Juni 2024');
        
        $dasarSkNomor   = $pegawai->nomor_sk_kgb_terakhir 
            ?? ($pegawai->nomor_sk_pangkat_terakhir ?? app(\App\Services\NomorSuratService::class)->format('827', 'KP', 2024));
        
        $dasarSkTmt     = $tmtDasar 
            ? \Carbon\Carbon::parse($tmtDasar)->translatedFormat('d F Y') 
            : '01 Oktober 2029';
            
        $dasarMkgTahun  = $mkgTahunLama;

        // 6. Otentikasi Digital SIKAP FKP UNRI & Mini QR Paraf Digital Faktual
        $watermarkService = app(\App\Services\DocumentWatermarkService::class);
        $verifyCode = $watermarkService->generateVerificationCode('SK Kenaikan Gaji Berkala', 'KGB/' . $pegawai->nip . '/' . date('Y'), $pegawai->nama_lengkap ?? $pegawai->nama);
        $verifyUrl  = route('verify.document', ['code' => $verifyCode]);

        // Status Faktual Verifikasi & Paraf Berjenjang
        $isDisahkan      = $pengajuan && in_array($pengajuan->status, ['disetujui_wd2', 'disetujui_dekan', 'selesai']);
        $sudahParafPokja = (bool) ($pengajuan && $pengajuan->paraf_kapokja_at);
        $sudahParafKabag = (bool) ($pengajuan && $pengajuan->paraf_kabag_at);

        $parafPokjaAt = $sudahParafPokja ? $pengajuan->paraf_kapokja_at->format('d/m/Y H:i') . ' WIB' : null;
        $parafKabagAt = $sudahParafKabag ? $pengajuan->paraf_kabag_at->format('d/m/Y H:i') . ' WIB' : null;

        $qrPokjaUri = $sudahParafPokja 
            ? \App\Services\QrCodeService::generateDataUri($verifyUrl . '?paraf=kapokja&nip=' . $kapokjaData['nip']) 
            : null;
        $qrKabagUri = $sudahParafKabag 
            ? \App\Services\QrCodeService::generateDataUri($verifyUrl . '?paraf=kabag&nip=' . $kabagData['nip']) 
            : null;
        $qrCodeDataUri = $isDisahkan ? \App\Services\QrCodeService::generateDataUri($verifyUrl) : null;
        $qrTteWd2Uri = $isDisahkan ? \App\Services\QrCodeService::generateDataUri($verifyUrl . '?tte=wd2&nip=' . ($pejabatWd2?->nip ?? '198509092014041001')) : null;
        $disahkanAt = $isDisahkan ? ($pengajuan?->updated_at ? $pengajuan->updated_at->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB') : null;

        $nomorSuratFormatted = !empty($pengajuan?->nomor_surat) 
            ? $pengajuan->nomor_surat 
            : app(\App\Services\NomorSuratService::class)->formatKarir('', (int) ($tahunSurat ?? date('Y')), false);

        $pdf = Pdf::loadView('exports.pdf.sk-kgb', compact(
            'kgb', 'pegawai', 'verifyUrl', 'verifyCode', 'qrCodeDataUri', 'qrTteWd2Uri', 'disahkanAt',
            'pejabatDekan', 'pejabatWd2', 'pejabatKabag', 'pejabatKaPokja', 'isPppk',
            'tahunSurat', 'tanggalSurat', 'dasarSkTanggal', 'dasarSkNomor', 'dasarSkTmt', 'dasarMkgTahun',
            'pengajuan', 'parafPokjaAt', 'parafKabagAt', 'qrPokjaUri', 'qrKabagUri',
            'isDisahkan', 'sudahParafPokja', 'sudahParafKabag', 'nomorSuratFormatted'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('SK_KGB_' . $pegawai->nip . '.pdf');
    }

    /**
     * Export Surat Pengantar Usulan Kenaikan Pangkat ke Rektor (PDF)
     */
    public function exportUsulanKpPdf($id)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        // $id bisa ID PengajuanKarir atau ID Pegawai
        $pengajuan = \App\Models\PengajuanKarir::with(['pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan', 'golonganLama', 'golonganTujuan'])->find($id);

        if (!$pengajuan) {
            $pegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])->findOrFail($id);
            // Default mock objek pengajuan untuk pegawai langsung
            $targetGolongan = \App\Models\Golongan::where('id', '>', $pegawai->golongan_id ?? 0)->first() ?? $pegawai->golongan;
            $pengajuan = (object) [
                'pegawai'          => $pegawai,
                'pegawai_id'       => $pegawai->id,
                'periode_kp'       => 'Februari',
                'tahun_periode'    => date('Y'),
                'golonganLama'     => $pegawai->golongan,
                'golonganTujuan'   => $targetGolongan,
                'tmt_lama'         => $pegawai->tmt_pangkat_terakhir,
                'status'           => 'draft',
                'paraf_kapokja_at' => null,
                'paraf_kabag_at'   => null,
                'paraf_wd2_at'     => null,
            ];
        }

        $isExecutive = $user->canAccessExecutiveKepegawaianMenus();
        $targetPegawaiId = $pengajuan->pegawai_id ?? ($pengajuan->pegawai->id ?? null);
        $isOwn = $targetPegawaiId && ($user->pegawai_id == $targetPegawaiId || $user->pegawai?->id == $targetPegawaiId);
        if (!$isExecutive && !$isOwn) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak dokumen pegawai lain.');
        }

        // Pejabat Penandatangan Dinamis via PejabatService
        $pejabatService = app(\App\Services\PejabatService::class);
        $dekanData      = $pejabatService->getDekan();
        $wadek2Data     = $pejabatService->getWadek2();
        $kabagData      = $pejabatService->getKabag();
        $kapokjaData    = $pejabatService->getKaPokja();

        $pejabatDekan   = $dekanData['pegawai'];
        $pejabatWd2     = $wadek2Data['pegawai'];
        $pejabatKabag   = $kabagData['pegawai'];
        $pejabatKaPokja = $kapokjaData['pegawai'];

        $watermarkService = app(\App\Services\DocumentWatermarkService::class);
        $verifyCodeKp = $watermarkService->generateVerificationCode('Surat Usulan Kenaikan Pangkat', 'KP/' . $pengajuan->pegawai->nip . '/' . date('Y'), $pengajuan->pegawai->nama_lengkap ?? $pengajuan->pegawai->nama);
        $verifyCode = $verifyCodeKp;
        $verifyUrl  = route('verify.document', ['code' => $verifyCodeKp]);

        // Status Faktual Verifikasi & Paraf Berjenjang
        $isDisahkan      = $pengajuan && in_array($pengajuan->status ?? '', ['disetujui_wd2', 'disetujui_dekan', 'selesai']);
        $sudahParafPokja = (bool) ($pengajuan && !empty($pengajuan->paraf_kapokja_at));
        $sudahParafKabag = (bool) ($pengajuan && !empty($pengajuan->paraf_kabag_at));

        $parafPokjaAt = $sudahParafPokja ? Carbon::parse($pengajuan->paraf_kapokja_at)->format('d/m/Y H:i') . ' WIB' : null;
        $parafKabagAt = $sudahParafKabag ? Carbon::parse($pengajuan->paraf_kabag_at)->format('d/m/Y H:i') . ' WIB' : null;

        $qrPokjaUri = $sudahParafPokja 
            ? \App\Services\QrCodeService::generateDataUri($verifyUrl . '?paraf=kapokja&nip=' . $kapokjaData['nip']) 
            : null;
        $qrKabagUri = $sudahParafKabag 
            ? \App\Services\QrCodeService::generateDataUri($verifyUrl . '?paraf=kabag&nip=' . $kabagData['nip']) 
            : null;
        $qrCodeDataUri = $isDisahkan ? \App\Services\QrCodeService::generateDataUri($verifyUrl) : null;
        $qrTteWd2Uri = $isDisahkan ? \App\Services\QrCodeService::generateDataUri($verifyUrl . '?tte=wd2&nip=' . ($pejabatWd2?->nip ?? '198509092014041001')) : null;
        $disahkanAt = $isDisahkan ? ($pengajuan?->updated_at ? Carbon::parse($pengajuan->updated_at)->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB') : null;

        $nomorSuratFormatted = !empty($pengajuan->nomor_surat)
            ? $pengajuan->nomor_surat
            : app(\App\Services\NomorSuratService::class)->formatKarir('', (int) ($pengajuan->tahun_periode ?? date('Y')), true);

        $pdf = Pdf::loadView('exports.pdf.surat-usulan-kp', compact(
            'pengajuan', 'pejabatDekan', 'pejabatWd2', 'pejabatKabag', 'pejabatKaPokja',
            'verifyUrl', 'verifyCode', 'qrCodeDataUri', 'qrTteWd2Uri', 'disahkanAt', 'parafPokjaAt', 'parafKabagAt', 'qrPokjaUri', 'qrKabagUri',
            'isDisahkan', 'sudahParafPokja', 'sudahParafKabag', 'nomorSuratFormatted'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('Usulan_KP_' . $pengajuan->pegawai->nip . '.pdf');
    }

    /**
     * Export Daftar Pengingat Kepegawaian (KGB, KP, Pensiun, Satyalancana) ke PDF
     */
    public function exportReminderPdf(Request $request)
    {
        $type = $request->get('type', 'all');
        $repository = app(\App\Repositories\Contracts\DashboardRepositoryInterface::class);
        $reminder = $repository->getReminder();

        $watermarkService = app(\App\Services\DocumentWatermarkService::class);

        $pdf = Pdf::loadView('exports.pdf.reminder', compact('reminder', 'type'))
            ->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

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
     * Layanan Stream / Preview & Download Berkas Privat Terproteksi Autentikasi & Autorisasi (SEC-NEW-01 Fix)
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
        $trimmedPath = trim($decodedPath, " \t\n\r\0\x0B/\\");

        // Tolak secara eksplisit jika mengandung komponen '..' atau karakter absolut drive letter
        if (str_contains($trimmedPath, '..') || preg_match('/^[a-zA-Z]:[\\\\\/]/', $trimmedPath)) {
            abort(403);
        }

        // Normalisasi separator
        $normalizedPath = str_replace('\\', '/', $trimmedPath);
        $relativePath   = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $trimmedPath);

        // 3. Blokir Ekstensi & File Sensitif Sistem
        $forbiddenExtensions = ['env', 'php', 'htaccess', 'git', 'json', 'lock', 'yml', 'yaml', 'sqlite', 'log', 'key'];
        $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));
        $filename  = basename($normalizedPath);

        if (in_array($extension, $forbiddenExtensions, true) || str_starts_with($filename, '.')) {
            abort(403);
        }

        // 4. Otorisasi Hak Akses (IDOR Protection)
        // Admin & Pimpinan memiliki hak akses membaca seluruh berkas
        if (!$user->hasRole(['admin', 'pimpinan'])) {
            $ownerPegawaiId = $this->resolveFileOwnerPegawaiId($normalizedPath);
            if ($ownerPegawaiId) {
                $userPegawaiId = $user->pegawai_id;
                if (!$userPegawaiId) {
                    $up = Pegawai::where('email', $user->email)->orWhere('nip', $user->name)->first();
                    $userPegawaiId = $up?->id;
                }

                // Pengguna dapat melihat dokumen jika miliknya sendiri, ATAU jika pengguna adalah atasan langsung pemilik dokumen
                $isOwn = $userPegawaiId && (int)$userPegawaiId === (int)$ownerPegawaiId;
                $isAtasan = $userPegawaiId && Pegawai::where('id', $ownerPegawaiId)->where('atasan_id', $userPegawaiId)->exists();

                if (!$isOwn && !$isAtasan) {
                    abort(403, 'Anda tidak memiliki hak akses untuk membuka dokumen ini.');
                }
            } else {
                // Fallback cek kecocokan NIP pengguna dengan path
                $userNip = $user->pegawai?->nip;
                if ($userNip && str_contains($normalizedPath, $userNip)) {
                    // Berkas berada di folder NIP pengguna
                } else {
                    abort(403, 'Anda tidak memiliki hak akses untuk membuka dokumen ini.');
                }
            }
        }

        $isDownload = $request->boolean('download') || $request->has('dl');
        $disposition = $isDownload ? 'attachment' : 'inline';

        // 5. Cek via Storage Disks (kompatibel dengan Supabase, Cloudflare R2, S3 dan Local Storage)
        $defaultDiskName = config('filesystems.default', 'local');
        if (in_array($defaultDiskName, ['supabase', 's3', 'r2'])) {
            try {
                $cloudDisk = \Illuminate\Support\Facades\Storage::disk($defaultDiskName);
                if ($cloudDisk->exists($normalizedPath)) {
                    $headers = [
                        'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
                    ];
                    return $cloudDisk->response($normalizedPath, $isDownload ? $filename : null, $headers);
                }
            } catch (\Throwable $e) {
                // Fallback ke disk lokal apabila Cloud Storage sedang gangguan/paused
            }
        }

        // Hilangkan kemungkinan duplikasi prefix folder disk ('private/', 'public/', 'storage/')
        $strippedPath = preg_replace('#^(private|public|storage)[/\\\\]#i', '', $normalizedPath);
        $strippedRelative = str_replace('/', DIRECTORY_SEPARATOR, $strippedPath);

        $localDisk = \Illuminate\Support\Facades\Storage::disk('local');
        foreach (array_unique([$normalizedPath, $strippedPath]) as $p) {
            if ($localDisk->exists($p)) {
                if (config('filesystems.disks.local.driver') === 's3') {
                    return $localDisk->response($p, $isDownload ? $filename : null, [
                        'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
                    ]);
                }
                return $this->buildFileStreamResponse($localDisk->path($p), $filename, $isDownload);
            }
        }

        try {
            $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
            foreach (array_unique([$normalizedPath, $strippedPath]) as $p) {
                if ($publicDisk->exists($p)) {
                    if (config('filesystems.disks.public.driver') === 's3') {
                        return $publicDisk->response($p, $isDownload ? $filename : null, [
                            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
                        ]);
                    }
                    return $this->buildFileStreamResponse($publicDisk->path($p), $filename, $isDownload);
                }
            }
        } catch (\Throwable $e) {
            // Fallback ke pemeriksaan file fisik lokal
        }

        // 6. Fallback direct file checks pada storage fisik lokal
        $candidatePaths = [
            storage_path('app/private' . DIRECTORY_SEPARATOR . $relativePath),
            storage_path('app/private' . DIRECTORY_SEPARATOR . $strippedRelative),
            storage_path('app/public' . DIRECTORY_SEPARATOR . $relativePath),
            storage_path('app/public' . DIRECTORY_SEPARATOR . $strippedRelative),
            storage_path('app' . DIRECTORY_SEPARATOR . $relativePath),
            storage_path('app' . DIRECTORY_SEPARATOR . $strippedRelative),
            public_path('storage' . DIRECTORY_SEPARATOR . $relativePath),
            public_path('storage' . DIRECTORY_SEPARATOR . $strippedRelative),
        ];

        foreach ($candidatePaths as $targetPath) {
            if (file_exists($targetPath) && is_file($targetPath)) {
                $realTarget = realpath($targetPath);
                if ($realTarget !== false) {
                    return $this->buildFileStreamResponse($realTarget, $filename, $isDownload);
                }
            }
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas fisik pindaian (scan) belum diunggah atau tidak ditemukan di penyimpanan server.'
            ], 404);
        }

        return response()->view('errors.document_not_found', [
            'path' => $path,
            'message' => 'Berkas fisik pindaian (scan) belum diunggah ke server penyimpanan. Namun demikian, data administratif (Nomor SK, Tanggal, dan TMT) tetap sah tercatat pada database SIKAP FKP UNRI.'
        ], 404);
    }

    /**
     * Membangun respons file dengan MIME type dan header Content-Disposition yang tepat
     */
    protected function buildFileStreamResponse(string $filePath, string $filename, bool $isDownload = false)
    {
        $mime = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $detected = @mime_content_type($filePath);
            if ($detected) $mime = $detected;
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
        ];
        if (isset($mimeMap[$ext])) {
            $mime = $mimeMap[$ext];
        }

        $disposition = $isDownload ? 'attachment' : 'inline';

        return response()->file($filePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Helper untuk melacak ID Pegawai pemilik berkas berdasarkan record DB
     */
    private function resolveFileOwnerPegawaiId(string $path): ?int
    {
        $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
        $altPath   = str_replace('/', '\\', $cleanPath);
        $strippedPath = preg_replace('#^(private|public|storage)/#i', '', $cleanPath);
        $altStripped  = str_replace('/', '\\', $strippedPath);

        $pathVariants = array_unique([$cleanPath, $altPath, $strippedPath, $altStripped]);

        $pegawai = Pegawai::where(function ($q) use ($pathVariants) {
            $q->whereIn('file_sk_pertama', $pathVariants)
              ->orWhereIn('file_sk_pangkat_terakhir', $pathVariants)
              ->orWhereIn('file_sk_kgb_terakhir', $pathVariants)
              ->orWhereIn('file_karpeg', $pathVariants)
              ->orWhereIn('file_pak', $pathVariants)
              ->orWhereIn('file_str', $pathVariants)
              ->orWhereIn('file_sip', $pathVariants)
              ->orWhereIn('foto', $pathVariants);
        })->first();
        if ($pegawai) return $pegawai->id;

        $rp = \App\Models\RiwayatPangkat::whereIn('file_sk', $pathVariants)->first();
        if ($rp) return $rp->pegawai_id;

        $rj = \App\Models\RiwayatJabatan::whereIn('file_sk', $pathVariants)->first();
        if ($rj) return $rj->pegawai_id;

        $rpend = \App\Models\RiwayatPendidikan::whereIn('ijazah', $pathVariants)->first();
        if ($rpend) return $rpend->pegawai_id;

        $rd = \App\Models\RiwayatDiklat::whereIn('file_sertifikat', $pathVariants)->first();
        if ($rd) return $rd->pegawai_id;

        $mp = \App\Models\MutasiPegawai::whereIn('file_sk', $pathVariants)->first();
        if ($mp) return $mp->pegawai_id;

        $skp = \App\Models\RiwayatSkp::whereIn('file_rencana_skp', $pathVariants)
            ->orWhereIn('file_evaluasi_skp', $pathVariants)
            ->first();
        if ($skp) return $skp->pegawai_id;

        $str = \App\Models\RiwayatStrSip::whereIn('file_dokumen', $pathVariants)->first();
        if ($str) return $str->pegawai_id;

        $tb = \App\Models\TugasBelajar::whereIn('file_sk', $pathVariants)
            ->orWhereIn('file_laporan_progress', $pathVariants)
            ->first();
        if ($tb) return $tb->pegawai_id;

        $cuti = \App\Models\PengajuanCuti::whereIn('file_lampiran', $pathVariants)->first();
        if ($cuti) return $cuti->pegawai_id;

        $logbook = \App\Models\Logbook::whereIn('file_lampiran', $pathVariants)->first();
        if ($logbook) return $logbook->pegawai_id;

        $pub = \App\Models\RiwayatPublikasi::whereIn('file_publikasi', $pathVariants)->first();
        if ($pub) return $pub->pegawai_id;

        $penghargaan = \App\Models\RiwayatPenghargaan::whereIn('file_sk', $pathVariants)->first();
        if ($penghargaan) return $penghargaan->pegawai_id;

        $karir = \App\Models\PengajuanKarir::where(function ($q) use ($pathVariants) {
            $q->whereIn('file_sk_terakhir', $pathVariants)
              ->orWhereIn('file_sk_pangkat_terakhir', $pathVariants)
              ->orWhereIn('file_sk_kgb_terakhir', $pathVariants)
              ->orWhereIn('file_skp_1', $pathVariants)
              ->orWhereIn('file_skp_2', $pathVariants)
              ->orWhereIn('file_karpeg', $pathVariants)
              ->orWhereIn('file_pak', $pathVariants)
              ->orWhereIn('file_pendukung', $pathVariants);
        })->first();
        if ($karir) return $karir->pegawai_id;

        // Fallback: Ekstraksi NIP dari format nama folder arsip digital: /([0-9]{8,18})\s*-\s*/
        if (preg_match('/(?:^|\/)([0-9]{8,18})\s*-\s*/', $cleanPath, $matches)) {
            $nipFound = $matches[1];
            $pegawaiByNip = Pegawai::where('nip', $nipFound)->first();
            if ($pegawaiByNip) {
                return $pegawaiByNip->id;
            }
        }

        return null;
    }
}