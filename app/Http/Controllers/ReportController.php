<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Exports\DukExport;
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
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $pegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])->findOrFail($id);

        $isExecutive = $user->canAccessExecutiveKepegawaianMenus();
        $isOwn = ($user->pegawai_id == $pegawai->id || $user->pegawai?->id == $pegawai->id);
        if (!$isExecutive && !$isOwn) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak dokumen pegawai lain.');
        }

        $isPppk  = str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PPPK');

        // 1. Tanggal TMT Dasar dan Perhitungan Masa Kerja Golongan (MKG)
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

        // 2. Otomatisasi Nilai Gaji Pokok Berdasarkan PP 5/2024 (PNS) & Perpres 11/2024 (PPPK)
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

        // 3. Pejabat Penandatangan & Paraf Digital
        $pejabatDekan   = Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        $pejabatWd2     = Pegawai::where('nama', 'like', '%Safri%')->first();
        $pejabatKabag   = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();
        $pejabatKaPokja = Pegawai::where('nama', 'like', '%Dolli Vita%')->first();

        // 4. Data Dasar SK Terakhir (Poin a s.d. e)
        $tahunSurat     = date('Y');
        $tanggalSurat   = \Carbon\Carbon::now()->translatedFormat('d F Y');
        $dasarSkTanggal = $pegawai->tanggal_sk_kgb_terakhir 
            ? \Carbon\Carbon::parse($pegawai->tanggal_sk_kgb_terakhir)->translatedFormat('d F Y') 
            : ($pegawai->tanggal_sk_pangkat_terakhir ? \Carbon\Carbon::parse($pegawai->tanggal_sk_pangkat_terakhir)->translatedFormat('d F Y') : '4 Juni 2024');
        
        $dasarSkNomor   = $pegawai->nomor_sk_kgb_terakhir 
            ?? ($pegawai->nomor_sk_pangkat_terakhir ?? '827/UN19.5.1.1.10/KP/2024');
        
        $dasarSkTmt     = $tmtDasar 
            ? \Carbon\Carbon::parse($tmtDasar)->translatedFormat('d F Y') 
            : '01 Oktober 2029';
            
        $dasarMkgTahun  = $mkgTahunLama;

        // 5. Otentikasi Digital SIKAP FKP UNRI
        $watermarkService = app(\App\Services\DocumentWatermarkService::class);
        $verifyCode = $watermarkService->generateVerificationCode('SK Kenaikan Gaji Berkala', 'KGB/' . $pegawai->nip . '/' . date('Y'), $pegawai->nama_lengkap ?? $pegawai->nama);
        $verifyUrl  = route('verify.document', ['code' => $verifyCode]);

        $pdf = Pdf::loadView('exports.pdf.sk-kgb', compact(
            'kgb', 'pegawai', 'verifyUrl', 'verifyCode', 
            'pejabatDekan', 'pejabatWd2', 'pejabatKabag', 'pejabatKaPokja', 'isPppk',
            'tahunSurat', 'tanggalSurat', 'dasarSkTanggal', 'dasarSkNomor', 'dasarSkTmt', 'dasarMkgTahun'
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
                'paraf_kabag_at'   => now(),
                'paraf_wd2_at'     => now(),
                'ttd_dekan_at'     => now(),
            ];
        }

        $isExecutive = $user->canAccessExecutiveKepegawaianMenus();
        $targetPegawaiId = $pengajuan->pegawai_id ?? ($pengajuan->pegawai->id ?? null);
        $isOwn = $targetPegawaiId && ($user->pegawai_id == $targetPegawaiId || $user->pegawai?->id == $targetPegawaiId);
        if (!$isExecutive && !$isOwn) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak dokumen pegawai lain.');
        }

        $pejabatDekan = Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        $pejabatWd2   = Pegawai::where('nama', 'like', '%Safri%')->first();
        $pejabatKabag = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();

        $watermarkService = app(\App\Services\DocumentWatermarkService::class);
        $pdf = Pdf::loadView('exports.pdf.surat-usulan-kp', compact('pengajuan', 'pejabatDekan', 'pejabatWd2', 'pejabatKabag'))
            ->setPaper('a4', 'portrait');

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
        $normalizedPath = ltrim(str_replace('\\', '/', $decodedPath), '/');
        $relativePath   = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $decodedPath), DIRECTORY_SEPARATOR);

        // 3. Blokir Ekstensi & File Sensitif Sistem
        $forbiddenExtensions = ['env', 'php', 'htaccess', 'git', 'json', 'lock', 'yml', 'yaml', 'sqlite', 'log', 'key'];
        $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));
        $filename  = basename($normalizedPath);

        if (in_array($extension, $forbiddenExtensions, true) || str_starts_with($filename, '.')) {
            abort(403);
        }

        // 4. Otorisasi Hak Akses (IDOR Protection)
        // Admin & Pimpinan memiliki hak akses membaca dokumen
        if (!$user->hasRole(['admin', 'pimpinan'])) {
            $ownerPegawaiId = $this->resolveFileOwnerPegawaiId($path);
            if ($ownerPegawaiId) {
                $userPegawaiId = $user->pegawai_id;
                if (!$userPegawaiId) {
                    $up = Pegawai::where('email', $user->email)->orWhere('nip', $user->name)->first();
                    $userPegawaiId = $up?->id;
                }

                // Pengguna dapat melihat dokumen jika miliknya sendiri, ATAU jika pengguna adalah atasan langsung pemilik dokumen
                $isOwn = $userPegawaiId && $userPegawaiId === $ownerPegawaiId;
                $isAtasan = $userPegawaiId && Pegawai::where('id', $ownerPegawaiId)->where('atasan_id', $userPegawaiId)->exists();

                if (!$isOwn && !$isAtasan) {
                    abort(403, 'Anda tidak memiliki hak akses untuk membuka dokumen ini.');
                }
            } else {
                abort(403);
            }
        }

        // 5. Cek via Storage Disks (kompatibel dengan Supabase, Cloudflare R2, S3 dan Local Storage)
        $defaultDiskName = config('filesystems.default', 'local');
        if (in_array($defaultDiskName, ['supabase', 's3', 'r2'])) {
            $cloudDisk = \Illuminate\Support\Facades\Storage::disk($defaultDiskName);
            if ($cloudDisk->exists($normalizedPath)) {
                return $cloudDisk->response($normalizedPath);
            }
        }

        $localDisk = \Illuminate\Support\Facades\Storage::disk('local');
        $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');

        if ($localDisk->exists($normalizedPath)) {
            if (config('filesystems.disks.local.driver') === 's3') {
                return $localDisk->response($normalizedPath);
            }
            return response()->file($localDisk->path($normalizedPath));
        }

        if ($publicDisk->exists($normalizedPath)) {
            if (config('filesystems.disks.public.driver') === 's3') {
                return $publicDisk->response($normalizedPath);
            }
            return response()->file($publicDisk->path($normalizedPath));
        }

        // 6. Fallback direct file checks pada storage fisik lokal
        $privateStorageRoot = realpath(storage_path('app/private'));
        $publicStorageRoot  = realpath(storage_path('app/public'));

        $targetPath = storage_path('app/private' . DIRECTORY_SEPARATOR . $relativePath);
        if (!file_exists($targetPath)) {
            $targetPath = storage_path('app/public' . DIRECTORY_SEPARATOR . $relativePath);
        }

        if (file_exists($targetPath)) {
            $realTarget = realpath($targetPath);
            if ($realTarget !== false) {
                $isInsidePrivate = $privateStorageRoot && (str_starts_with($realTarget, $privateStorageRoot . DIRECTORY_SEPARATOR) || $realTarget === $privateStorageRoot);
                $isInsidePublic  = $publicStorageRoot && (str_starts_with($realTarget, $publicStorageRoot . DIRECTORY_SEPARATOR) || $realTarget === $publicStorageRoot);

                if ($isInsidePrivate || $isInsidePublic) {
                    return response()->file($realTarget);
                }
            }
        }

        if ($request->expectsJson() || $request->is('api/*') || !$request->headers->has('referer')) {
            abort(404, 'Dokumen tidak ditemukan.');
        }
        return redirect()->back()->with('error', 'Berkas fisik belum diunggah atau tidak ditemukan di server. Silakan edit dan unggah ulang berkas.');
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
            ->orWhere('file_karpeg', $cleanPath)
            ->orWhere('file_pak', $cleanPath)
            ->orWhere('foto', $cleanPath)
            ->first();
        if ($pegawai) return $pegawai->id;

        $rp = \App\Models\RiwayatPangkat::where('file_sk', $cleanPath)->first();
        if ($rp) return $rp->pegawai_id;

        $rj = \App\Models\RiwayatJabatan::where('file_sk', $cleanPath)->first();
        if ($rj) return $rj->pegawai_id;

        $rpend = \App\Models\RiwayatPendidikan::where('ijazah', $cleanPath)->first();
        if ($rpend) return $rpend->pegawai_id;

        $rd = \App\Models\RiwayatDiklat::where('file_sertifikat', $cleanPath)->first();
        if ($rd) return $rd->pegawai_id;

        $mp = \App\Models\MutasiPegawai::where('file_sk', $cleanPath)->first();
        if ($mp) return $mp->pegawai_id;

        $skp = \App\Models\RiwayatSkp::where('file_rencana_skp', $cleanPath)->orWhere('file_evaluasi_skp', $cleanPath)->first();
        if ($skp) return $skp->pegawai_id;

        $str = \App\Models\RiwayatStrSip::where('file_dokumen', $cleanPath)->first();
        if ($str) return $str->pegawai_id;

        $tb = \App\Models\TugasBelajar::where('file_sk', $cleanPath)->orWhere('file_laporan_progress', $cleanPath)->first();
        if ($tb) return $tb->pegawai_id;

        $cuti = \App\Models\PengajuanCuti::where('file_lampiran', $cleanPath)->first();
        if ($cuti) return $cuti->pegawai_id;

        $logbook = \App\Models\Logbook::where('file_lampiran', $cleanPath)->first();
        if ($logbook) return $logbook->pegawai_id;

        $pub = \App\Models\RiwayatPublikasi::where('file_publikasi', $cleanPath)->first();
        if ($pub) return $pub->pegawai_id;

        $penghargaan = \App\Models\RiwayatPenghargaan::where('file_sk', $cleanPath)->first();
        if ($penghargaan) return $penghargaan->pegawai_id;

        return null;
    }
}