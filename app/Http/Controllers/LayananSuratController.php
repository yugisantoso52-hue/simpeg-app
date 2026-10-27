<?php

namespace App\Http\Controllers;

use App\Http\Requests\LayananSurat\StoreSuratAktifRequest;
use App\Http\Requests\LayananSurat\VerifikasiSuratAktifRequest;
use App\Models\LayananSurat;
use App\Models\Pegawai;
use App\Services\DocumentWatermarkService;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LayananSuratController extends Controller
{
    /**
     * Halaman Utama Layanan Mandiri Surat Keterangan Pegawai
     */
    public function index()
    {
        $user = Auth::user();
        $isExecutiveOrAdmin = $user->canAccessExecutiveKepegawaianMenus();
        $isKaPokja = $user->isKaPokjaKepeg();
        $isKabag   = $user->isKabagUmum();
        $isWd2     = $user->isWadek2();
        $isDekan   = $user->isDekan();
        $isAdmin   = $user->isScreeningAdmin();

        if ($isExecutiveOrAdmin) {
            $surats = LayananSurat::with(['pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan'])
                ->orderByDesc('id')
                ->paginate(15);
        } else {
            $pegawaiId = $user->pegawai_id ?? $user->pegawai?->id;
            $surats = LayananSurat::with(['pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan'])
                ->where('pegawai_id', $pegawaiId)
                ->orderByDesc('id')
                ->paginate(15);
        }

        $myPegawai = $user->pegawai ?? ($user->pegawai_id ? Pegawai::find($user->pegawai_id) : null);

        // Nomor Surat Otomatis Berikutnya Sebagai Default
        $tahunIni = date('Y');
        $nextNomorUrut = LayananSurat::whereYear('tanggal_surat', $tahunIni)
            ->orWhereYear('created_at', $tahunIni)
            ->count() + 1;
        $nextNomorPrefix = 'B/' . str_pad($nextNomorUrut, 4, '0', STR_PAD_LEFT);

        return view('layanan-surat.index', compact(
            'surats',
            'myPegawai',
            'isExecutiveOrAdmin',
            'isAdmin',
            'isKaPokja',
            'isKabag',
            'isWd2',
            'isDekan',
            'nextNomorPrefix'
        ));
    }

    /**
     * Buat Permohonan / Generate E-Surat Keterangan Aktif Bekerja Mandiri
     */
    public function storeSuratAktif(StoreSuratAktifRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $isExecutiveOrAdmin = $user->canAccessExecutiveKepegawaianMenus();

        // Tentukan pegawai target
        if ($isExecutiveOrAdmin && $request->filled('pegawai_id')) {
            $pegawai = Pegawai::findOrFail($request->pegawai_id);
        } else {
            $pegawai = $user->pegawai ?? Pegawai::find($user->pegawai_id);
        }

        if (!$pegawai) {
            return back()->with('error', 'Profil data kepegawaian Anda belum terhubung. Hubungi Admin Kepegawaian.');
        }

        // Format Nomor Surat terpusat via NomorSuratService
        $tahunSurat = date('Y');
        $nomorSuratService = app(\App\Services\NomorSuratService::class);

        if ($isAdmin && $request->filled('nomor_surat_manual')) {
            $nomorSurat = $nomorSuratService->formatSuratAktif($request->nomor_surat_manual, (int) $tahunSurat);
        } else {
            // Pengajuan mandiri oleh pegawai: nomor urut draft instansi (ditetapkan & disahkan Admin saat verifikasi)
            $nomorUrut = LayananSurat::whereYear('tanggal_surat', $tahunSurat)
                ->orWhereYear('created_at', $tahunSurat)
                ->count() + 1;
            $nomorSurat = $nomorSuratService->formatDraft($nomorUrut, 'KP', (int) $tahunSurat, true);
        }

        // Cek duplikasi nomor surat jika diinput manual oleh Admin
        if ($isAdmin && LayananSurat::where('nomor_surat', $nomorSurat)->exists()) {
            return back()->with('error', "Nomor surat {$nomorSurat} sudah pernah digunakan. Silakan tentukan nomor urut manual yang berbeda.")->withInput();
        }

        // Resolusi Pejabat Penandatangan Dinamis via PejabatService
        $pejabatService = app(\App\Services\PejabatService::class);
        $dekanData      = $pejabatService->getDekan();
        $wadek2Data     = $pejabatService->getWadek2();
        $kabagData      = $pejabatService->getKabag();
        $kapokjaData    = $pejabatService->getKaPokja();

        $pejabatDekan   = $dekanData['pegawai'];
        $pejabatWd2     = $wadek2Data['pegawai'];
        $pejabatKabag   = $kabagData['pegawai'];
        $pejabatKaPokja = $kapokjaData['pegawai'];

        $rolePenandatangan = $request->input('penandatangan_role', 'dekan');

        if ($rolePenandatangan === 'wadek2') {
            $penandatanganNama    = $wadek2Data['nama'];
            $penandatanganNip     = $wadek2Data['nip'];
            $penandatanganJabatan = $wadek2Data['jabatan'];
        } else {
            $penandatanganNama    = $dekanData['nama'];
            $penandatanganNip     = $dekanData['nip'];
            $penandatanganJabatan = $dekanData['jabatan'];
        }

        // Token Kriptografis Keaslian Dokumen
        $watermarkService = app(DocumentWatermarkService::class);
        $qrToken = $watermarkService->generateVerificationCode(
            'Surat Keterangan Aktif Bekerja',
            $nomorSurat,
            $pegawai->nama_lengkap ?? $pegawai->nama,
            date('Y-m-d')
        );

        $now = Carbon::now();

        // Tentukan status awal berdasarkan inisiator permohonan
        // Pegawai mandiri / pengusul: wajib melalui antrean verifikasi ('diajukan')
        // Admin: dapat menggunakan bypass langsung sahkan jika darurat
        if ($isAdmin && $request->filled('langsung_sahkan')) {
            $statusAwal     = 'selesai';
            $parafPokjaAt   = $now;
            $parafPokjaBy   = $pejabatKaPokja?->id;
            $parafKabagAt   = $now;
            $parafKabagBy   = $pejabatKabag?->id;
            $pesanSukses    = "Surat Keterangan Aktif Bekerja nomor {$nomorSurat} berhasil diterbitkan dan disahkan secara resmi.";
        } else {
            $statusAwal     = 'diajukan';
            $parafPokjaAt   = null;
            $parafPokjaBy   = null;
            $parafKabagAt   = null;
            $parafKabagBy   = null;
            $pesanSukses    = "Permohonan Surat Keterangan Aktif Bekerja nomor {$nomorSurat} berhasil diajukan dan sedang menunggu tindakan verifikasi berkas oleh Admin Kepegawaian.";
        }

        $surat = LayananSurat::create([
            'pegawai_id'            => $pegawai->id,
            'jenis_surat'           => 'Surat Keterangan Aktif Bekerja',
            'nomor_surat'           => $nomorSurat,
            'keperluan'             => $request->keperluan,
            'tanggal_surat'         => Carbon::today(),
            'status'                => $statusAwal,
            'penandatangan_nama'    => $penandatanganNama,
            'penandatangan_nip'     => $penandatanganNip,
            'penandatangan_jabatan' => $penandatanganJabatan,
            'paraf_kapokja_at'      => $parafPokjaAt,
            'paraf_kapokja_by'      => $parafPokjaBy,
            'paraf_kabag_at'        => $parafKabagAt,
            'paraf_kabag_by'        => $parafKabagBy,
            'qr_token'              => $qrToken,
            'created_by'            => $user->id,
        ]);

        return redirect()->route('layanan-surat.index')->with('success', $pesanSukses);
    }

    /**
     * Proses Verifikasi & Paraf Bertingkat Surat Keterangan (SOP 3 Tahap)
     */
    public function verifikasi(Request $request, int $id)
    {
        $user = Auth::user();
        if (!$user->canAccessExecutiveKepegawaianMenus()) {
            abort(403, 'Akses terbatas untuk Pengelola Kepegawaian dan Pimpinan.');
        }

        $surat = LayananSurat::findOrFail($id);
        $tahap = $request->input('tahap');
        $catatan = $request->input('catatan');
        $pejabatLogin = $user->pegawai;

        $isKaPokja = $user->isKaPokjaKepeg();
        $isKabag   = $user->isKabagUmum();
        $isWd2     = $user->isWadek2();
        $isDekan   = $user->isDekan();
        $isAdmin   = $user->isScreeningAdmin();

        if ($tahap === 'verifikasi_admin') {
            if (!$isAdmin) {
                return back()->with('error', 'Otorisasi Ditolak: Tahap 1 (Admin Screening & Penomoran) hanya berhak dilakukan oleh akun Admin Kepegawaian.');
            }

            $updateData = [
                'status'              => 'diverifikasi_admin',
                'verifikasi_admin_at' => now(),
                'verifikasi_admin_by' => $user->id,
                'catatan'             => $catatan ?? 'Telah diverifikasi kelengkapan berkas permohonan surat oleh Admin Kepegawaian.',
            ];

            if ($request->filled('nomor_surat_manual')) {
                $inputNomor = trim($request->nomor_surat_manual);
                $tahunSurat = $surat->created_at ? $surat->created_at->format('Y') : date('Y');
                $suffixStandard = "/UN19.5.1.1.10/KP/{$tahunSurat}";
                $updateData['nomor_surat'] = str_contains($inputNomor, '/UN19.') 
                    ? $inputNomor 
                    : rtrim($inputNomor, '/') . $suffixStandard;
            }

            $surat->update($updateData);
            $msg = '1. Verifikasi Screening Berkas & Penomoran oleh Admin Kepegawaian berhasil.';
        } elseif ($tahap === 'paraf_kapokja') {
            if (!$isKaPokja) {
                return back()->with('error', 'Otorisasi Ditolak: Tahap 2 (Paraf Ka Pokja) hanya berhak dilakukan oleh Kepala Pokja Keuangan & Kepegawaian.');
            }

            if (!$surat->verifikasi_admin_at) {
                return back()->with('error', 'SOP Tata Naskah: Berkas harus diperiksa kelengkapannya (Admin Screening Berkas) terlebih dahulu sebelum diparaf Ka Pokja.');
            }

            $dataPokja = [
                'status'           => 'diparaf_kapokja',
                'paraf_kapokja_at' => now(),
                'paraf_kapokja_by' => $pejabatLogin?->id,
                'catatan'          => $catatan ?? 'Telah diperiksa keaktifan pegawai dan kelayakan berkas oleh Ka Pokja Keu-Kepeg.',
            ];

            $surat->update($dataPokja);
            $msg = '2. Paraf Ka Pokja Keuangan & Kepegawaian berhasil dibubuhkan.';
        } elseif ($tahap === 'paraf_kabag') {
            if (!$isKabag) {
                return back()->with('error', 'Otorisasi Ditolak: Tahap 3 (Paraf Kabag Umum) hanya berhak dilakukan oleh Kepala Bagian Umum.');
            }

            if (!$surat->paraf_kapokja_at) {
                return back()->with('error', 'SOP Tata Naskah: Berkas harus diperiksa keaktifannya dan diparaf terlebih dahulu oleh Ka Pokja Keu-Kepeg sebelum ditelaah Kabag Umum.');
            }

            $dataKabag = [
                'status'         => 'diparaf_kabag',
                'paraf_kabag_at' => now(),
                'paraf_kabag_by' => $pejabatLogin?->id,
                'catatan'        => $catatan ?? 'Telah ditelaah format naskah dinas dan diparaf oleh Kepala Bagian Umum.',
            ];

            $surat->update($dataKabag);
            $msg = '3. Paraf Kepala Bagian Umum berhasil dibubuhkan.';
        } elseif ($tahap === 'ttd_pimpinan') {
            $isWadekLetter = str_contains(strtolower($surat->penandatangan_jabatan ?? ''), 'wakil dekan') 
                          || str_contains(strtolower($surat->penandatangan_jabatan ?? ''), 'wadek');

            $canSign = $isWadekLetter ? ($isWd2 || $isDekan) : $isDekan;

            if (!$canSign) {
                $pejabatTujuan = $isWadekLetter ? 'Wakil Dekan II / Dekan' : 'Dekan Fakultas Keperawatan';
                return back()->with('error', "Otorisasi Ditolak: Tahap 4 (TTE Pengesahan Pimpinan) hanya berhak disahkan oleh {$pejabatTujuan}.");
            }

            if (!$surat->paraf_kabag_at) {
                return back()->with('error', 'SOP Tata Naskah: Konsep surat harus melalui telaah paraf koordinasi Kepala Bagian Umum sebelum ditandatangani Pimpinan.');
            }

            $dataTtd = [
                'status'          => 'selesai',
                'ttd_pimpinan_at' => now(),
                'ttd_pimpinan_by' => $pejabatLogin?->id,
                'catatan'         => $catatan ?? 'Telah disetujui dan ditandatangani secara elektronik oleh Pimpinan.',
            ];

            $surat->update($dataTtd);
            $msg = '4. Surat Keterangan Aktif Bekerja resmi disahkan dan ditandatangani secara elektronik oleh Pimpinan.';
        } elseif ($tahap === 'tolak') {
            $canUserReject = false;
            if (!$surat->verifikasi_admin_at && $isAdmin) {
                $canUserReject = true;
            } elseif ($surat->verifikasi_admin_at && !$surat->paraf_kapokja_at && $isKaPokja) {
                $canUserReject = true;
            } elseif ($surat->paraf_kapokja_at && !$surat->paraf_kabag_at && $isKabag) {
                $canUserReject = true;
            } elseif ($surat->paraf_kabag_at && $surat->status !== 'selesai') {
                $isWadekLetter = str_contains(strtolower($surat->penandatangan_jabatan ?? ''), 'wakil dekan') 
                              || str_contains(strtolower($surat->penandatangan_jabatan ?? ''), 'wadek');
                if ($isWadekLetter ? ($isWd2 || $isDekan) : $isDekan) {
                    $canUserReject = true;
                }
            }

            if (!$canUserReject) {
                return back()->with('error', 'Otorisasi Ditolak: Anda tidak memiliki wewenang untuk menolak / mengembalikan surat pada tahapan alur saat ini.');
            }

            $surat->update([
                'status'  => 'ditolak',
                'catatan' => $catatan ?? 'Permohonan surat ditolak atau dikembalikan untuk perbaikan.',
            ]);
            $msg = 'Permohonan surat keterangan berhasil ditolak / dikembalikan ke pegawai pemohon.';
        } else {
            return back()->with('error', 'Tahap verifikasi tidak dikenali.');
        }

        return back()->with('success', $msg);
    }

    /**
     * Cetak PDF Surat Keterangan Aktif Bekerja dengan QR Code & Stempel Digital Faktual
     */
    public function cetakSuratAktif(int $id)
    {
        $surat = LayananSurat::with(['pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan'])->findOrFail($id);
        $user = Auth::user();

        // Otorisasi Akses
        $isExecutiveOrAdmin = $user->canAccessExecutiveKepegawaianMenus();
        $isOwner = ($user->pegawai_id && $user->pegawai_id == $surat->pegawai_id) || ($user->id == $surat->created_by);

        if (!$isExecutiveOrAdmin && !$isOwner) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak surat pegawai lain.');
        }

        $pegawai        = $surat->pegawai;
        $pejabatService = app(\App\Services\PejabatService::class);
        $dekanData      = $pejabatService->getDekan();
        $kabagData      = $pejabatService->getKabag();
        $kapokjaData    = $pejabatService->getKaPokja();

        $pejabatDekan   = $dekanData['pegawai'];
        $pejabatKabag   = $kabagData['pegawai'];
        $pejabatKaPokja = $kapokjaData['pegawai'];

        // Kondisi Faktual Verifikasi & Pengesahan
        $isDisahkan      = ($surat->status === 'selesai');
        $sudahParafPokja = !empty($surat->paraf_kapokja_at);
        $sudahParafKabag = !empty($surat->paraf_kabag_at);

        // QR Code Verifikasi Publik
        $qrToken       = $surat->qr_token ?: md5($surat->id . '-' . ($surat->nomor_surat ?? uniqid()));
        $verifyUrl     = route('verify.document', ['code' => $qrToken]);
        $qrCodeDataUri = $isDisahkan ? QrCodeService::generateDataUri($verifyUrl) : null;

        // Mini QR Paraf Digital Tata Naskah (Kabag & Ka Pokja)
        $qrPokjaUri = $sudahParafPokja ? QrCodeService::generateDataUri($verifyUrl . '?paraf=kapokja&nip=' . $kapokjaData['nip']) : null;
        $qrKabagUri = $sudahParafKabag ? QrCodeService::generateDataUri($verifyUrl . '?paraf=kabag&nip=' . $kabagData['nip']) : null;

        // Tanggal & Waktu Paraf Digital Faktual
        $parafPokjaAt = $sudahParafPokja ? $surat->paraf_kapokja_at->format('d/m/Y H:i') . ' WIB' : null;
        $parafKabagAt = $sudahParafKabag ? $surat->paraf_kabag_at->format('d/m/Y H:i') . ' WIB' : null;

        // Tanggal & QR Code TTE Pimpinan Faktual
        $disahkanAt = $isDisahkan 
            ? ($surat->updated_at ? $surat->updated_at->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB') 
            : null;
        $qrTtePimpinanUri = $isDisahkan 
            ? QrCodeService::generateDataUri($verifyUrl . '?tte=pimpinan&nip=' . ($surat->penandatangan_nip ?? '')) 
            : null;

        $watermarkService = app(DocumentWatermarkService::class);

        $pdf = Pdf::loadView('layanan-surat.surat_aktif_print', compact(
            'surat',
            'pegawai',
            'pejabatDekan',
            'pejabatKabag',
            'pejabatKaPokja',
            'verifyUrl',
            'qrCodeDataUri',
            'qrPokjaUri',
            'qrKabagUri',
            'qrTtePimpinanUri',
            'disahkanAt',
            'parafPokjaAt',
            'parafKabagAt',
            'isDisahkan',
            'sudahParafPokja',
            'sudahParafKabag'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('Surat_Keterangan_Aktif_' . $pegawai->nip . '.pdf');
    }

    /**
     * Update Nomor Surat Khusus Admin Kepegawaian
     */
    public function updateNomorSurat(Request $request, int $id)
    {
        $user = $request->user() ?? Auth::user();
        if (!$user || !$user->isScreeningAdmin()) {
            abort(403, 'Hanya Admin Kepegawaian yang berwenang mengisi atau mengubah nomor surat resmi.');
        }

        $request->validate([
            'nomor_surat_manual' => 'required|string|max:100',
        ]);

        $surat = LayananSurat::findOrFail($id);
        $inputNomor = trim($request->nomor_surat_manual);
        $tahunSurat = $surat->created_at ? $surat->created_at->format('Y') : date('Y');
        $suffixStandard = "/UN19.5.1.1.10/KP/{$tahunSurat}";
        $nomorSurat = str_contains($inputNomor, '/UN19.') 
            ? $inputNomor 
            : rtrim($inputNomor, '/') . $suffixStandard;

        $surat->update(['nomor_surat' => $nomorSurat]);

        return back()->with('success', "Nomor surat berhasil diperbarui oleh Admin: {$nomorSurat}");
    }
}
