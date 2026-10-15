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
            'penandatangan_role' => 'nullable|in:dekan,wadek2',
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

        // Pejabat Dekan & Wakil Dekan II FKP UNRI
        $pejabatDekan   = Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        $pejabatWd2     = Pegawai::where('nama', 'like', '%Safri%')->first();
        $pejabatKabag   = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();
        $pejabatKaPokja = Pegawai::where('nama', 'like', '%Dolli Vita%')->first();

        $rolePenandatangan = $request->input('penandatangan_role', 'dekan');

        if ($rolePenandatangan === 'wadek2') {
            $penandatanganNama    = $pejabatWd2 ? ($pejabatWd2->nama_lengkap ?? $pejabatWd2->nama) : 'Dr. Safri, M.Kep, Sp.Kep.M.B';
            $penandatanganNip     = $pejabatWd2 ? $pejabatWd2->nip : '198509092014041001';
            $penandatanganJabatan = 'Wakil Dekan Bidang Keuangan dan Umum';
        } else {
            $penandatanganNama    = $pejabatDekan ? ($pejabatDekan->nama_lengkap ?? $pejabatDekan->nama) : 'Prof. Wan Nishfa Dewi, S.Kp, MNg, PhD';
            $penandatanganNip     = $pejabatDekan ? $pejabatDekan->nip : '197508222001122001';
            $penandatanganJabatan = 'Dekan Fakultas Keperawatan';
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
        // Pegawai mandiri: wajib melalui antrean verifikasi ('diajukan')
        // Admin / Pimpinan eksekutif: dapat langsung mengesahkan atau membuat draf
        if ($isExecutiveOrAdmin && $request->filled('langsung_sahkan')) {
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
            $pesanSukses    = "Permohonan Surat Keterangan Aktif Bekerja nomor {$nomorSurat} berhasil diajukan dan sedang menunggu tindakan verifikasi berkas oleh Ka Pokja Keuangan & Kepegawaian.";
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

        if ($tahap === 'paraf_kapokja') {
            $surat->update([
                'status'           => 'diparaf_kapokja',
                'paraf_kapokja_at' => now(),
                'paraf_kapokja_by' => $pejabatLogin?->id,
                'catatan'          => $catatan ?? 'Telah diperiksa keaktifan pegawai dan kelayakan berkas oleh Ka Pokja Keu-Kepeg.',
            ]);
            $msg = 'Paraf 1 (Ka Pokja Keuangan & Kepegawaian) berhasil dibubuhkan.';
        } elseif ($tahap === 'paraf_kabag') {
            if (!$surat->paraf_kapokja_at && !$user->hasRole('admin')) {
                return back()->with('error', 'SOP Tata Naskah: Berkas harus diperiksa keaktifannya dan diparaf terlebih dahulu oleh Ka Pokja Keu-Kepeg sebelum ditelaah Kabag Umum.');
            }

            $dataKabag = [
                'status'         => 'diparaf_kabag',
                'paraf_kabag_at' => now(),
                'paraf_kabag_by' => $pejabatLogin?->id,
                'catatan'        => $catatan ?? 'Telah ditelaah format naskah dinas dan diparaf oleh Kepala Bagian Umum.',
            ];

            if (!$surat->paraf_kapokja_at && $user->hasRole('admin')) {
                $dataKabag['paraf_kapokja_at'] = now();
                $dataKabag['paraf_kapokja_by'] = $pejabatLogin?->id;
            }

            $surat->update($dataKabag);
            $msg = 'Paraf 2 (Kepala Bagian Umum) berhasil dibubuhkan.';
        } elseif ($tahap === 'ttd_pimpinan') {
            if (!$surat->paraf_kabag_at && !$user->hasRole('admin')) {
                return back()->with('error', 'SOP Tata Naskah: Konsep surat harus melalui telaah paraf koordinasi Kepala Bagian Umum sebelum ditandatangani Pimpinan.');
            }

            $dataTtd = [
                'status'  => 'selesai',
                'catatan' => $catatan ?? 'Telah disetujui dan ditandatangani secara elektronik oleh Pimpinan.',
            ];

            if (!$surat->paraf_kapokja_at && $user->hasRole('admin')) {
                $dataTtd['paraf_kapokja_at'] = now();
                $dataTtd['paraf_kapokja_by'] = $pejabatLogin?->id;
            }
            if (!$surat->paraf_kabag_at && $user->hasRole('admin')) {
                $dataTtd['paraf_kabag_at'] = now();
                $dataTtd['paraf_kabag_by'] = $pejabatLogin?->id;
            }

            $surat->update($dataTtd);
            $msg = 'Surat Keterangan Aktif Bekerja resmi disetujui dan ditandatangani secara elektronik oleh Pimpinan.';
        } elseif ($tahap === 'tolak') {
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
        $pejabatDekan   = Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        $pejabatKabag   = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();
        $pejabatKaPokja = Pegawai::where('nama', 'like', '%Dolli Vita%')->first();

        // Kondisi Faktual Verifikasi & Pengesahan
        $isDisahkan      = ($surat->status === 'selesai');
        $sudahParafPokja = !empty($surat->paraf_kapokja_at);
        $sudahParafKabag = !empty($surat->paraf_kabag_at);

        // QR Code Verifikasi Publik
        $verifyUrl     = route('verify.document', ['code' => $surat->qr_token]);
        $qrCodeDataUri = $isDisahkan ? QrCodeService::generateDataUri($verifyUrl) : null;

        // Mini QR Paraf Digital Tata Naskah (Kabag & Ka Pokja)
        $qrPokjaUri = $sudahParafPokja ? QrCodeService::generateDataUri($verifyUrl . '&paraf=kapokja&nip=' . ($pejabatKaPokja?->nip ?? '197605212014092001')) : null;
        $qrKabagUri = $sudahParafKabag ? QrCodeService::generateDataUri($verifyUrl . '&paraf=kabag&nip=' . ($pejabatKabag?->nip ?? '197507122005011002')) : null;

        // Tanggal & Waktu Paraf Digital Faktual
        $parafPokjaAt = $sudahParafPokja ? $surat->paraf_kapokja_at->format('d/m/Y H:i') . ' WIB' : null;
        $parafKabagAt = $sudahParafKabag ? $surat->paraf_kabag_at->format('d/m/Y H:i') . ' WIB' : null;

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
            'parafKabagAt',
            'isDisahkan',
            'sudahParafPokja',
            'sudahParafKabag'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('Surat_Keterangan_Aktif_' . $pegawai->nip . '.pdf');
    }
}
