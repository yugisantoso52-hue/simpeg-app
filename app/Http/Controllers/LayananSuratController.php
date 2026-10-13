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

        return view('layanan-surat.index', compact('surats', 'myPegawai', 'isExecutiveOrAdmin'));
    }

    /**
     * Buat Permohonan / Generate E-Surat Keterangan Aktif Bekerja Mandiri
     */
    public function storeSuratAktif(Request $request)
    {
        $request->validate([
            'keperluan' => 'required|string|max:500',
            'pegawai_id' => 'nullable|exists:pegawai,id',
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

        // Generate Nomor Surat Otomatis Berformat FKP UNRI
        // Contoh: B/1042/UN19.5.1.1/KP.01.04/2026
        $nomorUrut = LayananSurat::whereYear('created_at', date('Y'))->count() + 1;
        $nomorSurat = 'B/' . str_pad($nomorUrut, 4, '0', STR_PAD_LEFT) . '/UN19.5.1.1/KP.01.04/' . date('Y');

        // Dekan FKP UNRI sebagai Penandatangan default
        $pejabatDekan = Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        $penandatanganNama = $pejabatDekan ? ($pejabatDekan->nama_lengkap ?? $pejabatDekan->nama) : 'Prof. Dr. Wan Nishfa Dewi, S.Kp., M.Ng.';
        $penandatanganNip = $pejabatDekan ? $pejabatDekan->nip : '197505232001122001';
        $penandatanganJabatan = 'Dekan Fakultas Keperawatan';

        // Token Kriptografis Keaslian Dokumen
        $watermarkService = app(DocumentWatermarkService::class);
        $qrToken = $watermarkService->generateVerificationCode(
            'Surat Keterangan Aktif Bekerja',
            $nomorSurat,
            $pegawai->nama_lengkap ?? $pegawai->nama,
            date('Y-m-d')
        );

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

        $pegawai = $surat->pegawai;
        $pejabatDekan = Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();

        // QR Code Verifikasi Publik
        $verifyUrl = route('verify.document', ['code' => $surat->qr_token]);
        $qrCodeDataUri = QrCodeService::generateDataUri($verifyUrl);

        $watermarkService = app(DocumentWatermarkService::class);

        $pdf = Pdf::loadView('layanan-surat.surat_aktif_print', compact(
            'surat',
            'pegawai',
            'pejabatDekan',
            'verifyUrl',
            'qrCodeDataUri'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('Surat_Keterangan_Aktif_' . $pegawai->nip . '.pdf');
    }
}
