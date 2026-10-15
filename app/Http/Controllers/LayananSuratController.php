<?php

namespace App\Http\Controllers;

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

        return view('layanan-surat.index', compact('surats', 'myPegawai', 'isExecutiveOrAdmin', 'nextNomorPrefix'));
    }

    /**
     * Buat Permohonan / Generate E-Surat Keterangan Aktif Bekerja Mandiri
     */
    public function storeSuratAktif(Request $request)
    {
        $request->validate([
            'keperluan'          => 'required|string|max:500',
            'pegawai_id'         => 'nullable|exists:pegawai,id',
            'nomor_surat_manual' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
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

        // Format Nomor Surat: (isi manual)/UN19.5.1.1.10/KP/{tahun}
        $tahunSurat = date('Y');
        $suffixStandard = "/UN19.5.1.1.10/KP/{$tahunSurat}";

        if ($request->filled('nomor_surat_manual')) {
            $inputNomor = trim($request->nomor_surat_manual);
            if (str_contains($inputNomor, '/UN19.')) {
                $nomorSurat = $inputNomor;
            } else {
                $inputNomor = rtrim($inputNomor, '/');
                $nomorSurat = $inputNomor . $suffixStandard;
            }
        } else {
            $nomorUrut = LayananSurat::whereYear('tanggal_surat', $tahunSurat)
                ->orWhereYear('created_at', $tahunSurat)
                ->count() + 1;
            $nomorSurat = 'B/' . str_pad($nomorUrut, 4, '0', STR_PAD_LEFT) . $suffixStandard;
        }

        // Cek duplikasi nomor surat agar aman dan ramah pengguna
        if (LayananSurat::where('nomor_surat', $nomorSurat)->exists()) {
            return back()->with('error', "Nomor surat {$nomorSurat} sudah pernah digunakan. Silakan tentukan nomor urut manual yang berbeda.")->withInput();
        }

        // Pejabat Dekan FKP UNRI sebagai Penandatangan default
        $pejabatDekan   = Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        $pejabatKabag   = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();
        $pejabatKaPokja = Pegawai::where('nama', 'like', '%Dolli Vita%')->first();

        $penandatanganNama    = $pejabatDekan ? ($pejabatDekan->nama_lengkap ?? $pejabatDekan->nama) : 'Prof. Dr. Wan Nishfa Dewi, S.Kp., M.Ng.';
        $penandatanganNip     = $pejabatDekan ? $pejabatDekan->nip : '197508222001122001';
        $penandatanganJabatan = 'Dekan Fakultas Keperawatan';

        // Token Kriptografis Keaslian Dokumen
        $watermarkService = app(DocumentWatermarkService::class);
        $qrToken = $watermarkService->generateVerificationCode(
            'Surat Keterangan Aktif Bekerja',
            $nomorSurat,
            $pegawai->nama_lengkap ?? $pegawai->nama,
            date('Y-m-d')
        );

        $now = Carbon::now();

        $surat = LayananSurat::create([
            'pegawai_id'            => $pegawai->id,
            'jenis_surat'           => 'Surat Keterangan Aktif Bekerja',
            'nomor_surat'           => $nomorSurat,
            'keperluan'             => $request->keperluan,
            'tanggal_surat'         => Carbon::today(),
            'status'                => 'selesai',
            'penandatangan_nama'    => $penandatanganNama,
            'penandatangan_nip'     => $penandatanganNip,
            'penandatangan_jabatan' => $penandatanganJabatan,
            'paraf_kapokja_at'      => $now->copy()->subMinutes(12),
            'paraf_kapokja_by'      => $pejabatKaPokja?->id,
            'paraf_kabag_at'        => $now->copy()->subMinutes(5),
            'paraf_kabag_by'        => $pejabatKabag?->id,
            'qr_token'              => $qrToken,
            'created_by'            => $user->id,
        ]);

        return redirect()->route('layanan-surat.index')
            ->with('success', "Surat Keterangan Aktif Bekerja dengan nomor {$nomorSurat} berhasil diterbitkan dan siap diunduh.");
    }

    /**
     * Cetak PDF Surat Keterangan Aktif Bekerja dengan QR Code & Stempel Digital
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
        $pejabatDekan   = Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        $pejabatKabag   = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();
        $pejabatKaPokja = Pegawai::where('nama', 'like', '%Dolli Vita%')->first();

        // QR Code Verifikasi Publik
        $verifyUrl     = route('verify.document', ['code' => $surat->qr_token]);
        $qrCodeDataUri = QrCodeService::generateDataUri($verifyUrl);

        // Mini QR Paraf Digital Tata Naskah (Kabag & Ka Pokja)
        $qrPokjaUri = QrCodeService::generateDataUri($verifyUrl . '&paraf=kapokja&nip=' . ($pejabatKaPokja?->nip ?? '197605212014092001'));
        $qrKabagUri = QrCodeService::generateDataUri($verifyUrl . '&paraf=kabag&nip=' . ($pejabatKabag?->nip ?? '197507122005011002'));

        // Tanggal & Waktu Paraf Digital
        $parafPokjaAt = $surat->paraf_kapokja_at 
            ? $surat->paraf_kapokja_at->format('d/m/Y H:i') . ' WIB'
            : ($surat->created_at ? $surat->created_at->format('d/m/Y H:i') . ' WIB' : Carbon::parse($surat->tanggal_surat)->setTime(10, 15)->format('d/m/Y H:i') . ' WIB');

        $parafKabagAt = $surat->paraf_kabag_at 
            ? $surat->paraf_kabag_at->format('d/m/Y H:i') . ' WIB'
            : ($surat->created_at ? $surat->created_at->addMinutes(7)->format('d/m/Y H:i') . ' WIB' : Carbon::parse($surat->tanggal_surat)->setTime(10, 25)->format('d/m/Y H:i') . ' WIB');

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
            'parafPokjaAt',
            'parafKabagAt'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('Surat_Keterangan_Aktif_' . $pegawai->nip . '.pdf');
    }
}
