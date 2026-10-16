<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengajuanCuti\ApprovePengajuanCutiRequest;
use App\Http\Requests\PengajuanCuti\StorePengajuanCutiRequest;
use App\Services\PengajuanCutiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PengajuanCutiController extends Controller
{
    public function __construct(
        protected PengajuanCutiService $service
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        $isAtasan = $user->isAtasan() || $user->hasRole('pimpinan');
        $isPegawaiOnly = !$isAdmin && !$isAtasan;
        $pegawaiId = $isPegawaiOnly ? $user->pegawai_id : null;

        if ($isPegawaiOnly && !$pegawaiId) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        // Scoping daftar cuti berdasarkan Matriks Hierarki E-Cuti:
        $scopedPegawaiIds = null;
        if (!$isAdmin && $isAtasan) {
            if ($user->pegawai) {
                $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
                $scopedPegawaiIds = $hierarchyService->getScopedCutiPegawaiIds($user->pegawai);
            } else {
                $scopedPegawaiIds = $user->getBawahanIds();
            }
        }

        $data = $this->service->filter(
            $request->get('search'),
            $request->get('jenis'),
            $request->get('status'),
            $pegawaiId,
            10,
            $scopedPegawaiIds
        );

        $statistics = $this->service->statistics($pegawaiId, $scopedPegawaiIds);
        $pegawai = $user->pegawai;

        return view('pengajuan-cuti.index', compact('data', 'statistics', 'isPegawaiOnly', 'pegawai', 'isAtasan', 'isAdmin'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        $isPegawaiOnly = !$isAdmin;
        $pegawai = $user->pegawai ?? ($user->pegawai_id ? \App\Models\Pegawai::find($user->pegawai_id) : null);
        $pegawaiList = $isAdmin ? $this->service->pegawaiList() : collect();

        if (!$isAdmin && !$pegawai) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        return view('pengajuan-cuti.create', compact('isPegawaiOnly', 'pegawai', 'pegawaiList'));
    }

    public function store(StorePengajuanCutiRequest $request)
    {
        $user = $request->user();
        $pegawaiId = $user->hasRole('admin') && $request->filled('pegawai_id')
            ? (int)$request->get('pegawai_id')
            : (int)($user->pegawai_id ?? $user->pegawai?->id);

        if (!$pegawaiId) {
            return redirect()->back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $validated = $request->safe()->except([
            'status', 'approved_by', 'approved_at', 'pertimbangan_atasan',
            'pybmc_id', 'atasan_langsung_id', 'catatan_pimpinan', 'catatan_atasan_langsung'
        ]);

        $this->service->create(
            $validated,
            $pegawaiId,
            $request->file('file_lampiran')
        );

        return redirect()
            ->route('pengajuan-cuti.index')
            ->with('success', 'Permohonan cuti berhasil diajukan dan sedang menunggu persetujuan pimpinan.');
    }

    public function show(int $id, Request $request)
    {
        $cuti = $this->service->find($id);
        $user = $request->user();

        // Otorisasi: Pegawai biasa hanya bisa melihat permohonannya sendiri atau permohonan bawahannya jika atasan
        $isAdmin    = $user->hasRole('admin');
        $isOwn      = $user->pegawai_id && $user->pegawai_id === $cuti->pegawai_id;

        $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
        $isAtasanLgs = $user->pegawai && $cuti->pegawai && $hierarchyService->isAtasanLangsungCuti($user->pegawai, $cuti->pegawai);
        $isPybmc     = $user->pegawai && $cuti->pegawai && $hierarchyService->isPybmcCuti($user->pegawai, $cuti->pegawai);
        $isDekan     = $user->pegawai && str_contains(strtoupper((string)($user->pegawai->jabatan->nama_jabatan ?? '')), 'DEKAN') && !str_contains(strtoupper((string)($user->pegawai->jabatan->nama_jabatan ?? '')), 'WAKIL');

        if (!$isAdmin && !$isOwn && !$isAtasanLgs && !$isPybmc && !$isDekan && !$user->hasRole('pimpinan')) {
            abort(403, 'Anda tidak diizinkan melihat data permohonan cuti ini.');
        }

        // Dapatkan informasi pejabat cuti dinamis berdasarkan kategori pegawai (Dosen/Tendik/Pimpinan)
        $pejabatInfo = $hierarchyService->getPejabatCutiInfo($cuti->pegawai);

        return view('pengajuan-cuti.show', compact('cuti', 'pejabatInfo'));
    }

    public function approve(ApprovePengajuanCutiRequest $request, int $id)
    {
        $validated = $request->validated();
        $tahunSurat = date('Y');

        if ($request->filled('nomor_surat_input')) {
            $nomorInput = trim((string)$request->input('nomor_surat_input'));
            $validated['nomor_surat'] = str_contains($nomorInput, '/UN19') 
                ? $nomorInput 
                : $nomorInput . '/UN19.5.1.1.10/KP/' . $tahunSurat;
        } elseif ($request->filled('nomor_surat')) {
            $nomorRaw = trim((string)$request->input('nomor_surat'));
            $validated['nomor_surat'] = str_contains($nomorRaw, '/UN19') 
                ? $nomorRaw 
                : $nomorRaw . '/UN19.5.1.1.10/KP/' . $tahunSurat;
        }

        $this->service->approve($id, $validated, (int)$request->user()->id);

        $status = $request->get('status');
        $msg = $status === 'Disetujui' 
            ? 'Permohonan cuti telah disetujui.' 
            : 'Permohonan cuti telah ditolak.';

        return redirect()
            ->route('pengajuan-cuti.show', $id)
            ->with('success', $msg);
    }

    public function cancel(int $id, Request $request)
    {
        $user = $request->user();
        $this->service->cancel($id, (int)$user->pegawai_id);

        return redirect()
            ->route('pengajuan-cuti.index')
            ->with('success', 'Permohonan cuti telah dibatalkan.');
    }

    public function updateNomorSurat(Request $request, int $id)
    {
        $user = $request->user();
        if (!$user->hasRole('admin') && !$user->canAccessExecutiveKepegawaianMenus()) {
            abort(403, 'Hanya Admin Kepegawaian yang berwenang mengisi atau mengubah nomor surat.');
        }

        $cuti = $this->service->find($id);
        $nomorInput = trim((string) $request->input('nomor_surat_input', ''));
        $tahunSurat = $cuti->created_at ? $cuti->created_at->format('Y') : date('Y');

        if ($nomorInput !== '') {
            $nomorSuratFormatted = str_contains($nomorInput, '/UN19') 
                ? $nomorInput 
                : $nomorInput . '/UN19.5.1.1.10/KP/' . $tahunSurat;
        } else {
            $nomorSuratFormatted = null;
        }

        $cuti->update(['nomor_surat' => $nomorSuratFormatted]);

        return redirect()
            ->route('pengajuan-cuti.show', $id)
            ->with('success', 'Nomor surat cuti berhasil disimpan oleh Admin: ' . ($nomorSuratFormatted ?? '(Dikosongkan)'));
    }

    public function destroy(int $id, Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Hanya Administrator yang memiliki wewenang untuk menghapus pengajuan cuti.');
        }

        $this->service->delete($id);

        return redirect()
            ->route('pengajuan-cuti.index')
            ->with('success', 'Data pengajuan cuti berhasil dihapus.');
    }

    public function cetakFormPdf(int $id, Request $request)
    {
        $cuti = $this->service->find($id);
        $user = $request->user();

        if ($user->hasRole('pegawai') && !$user->hasRole(['admin', 'pimpinan'])) {
            if ($user->pegawai_id !== $cuti->pegawai_id) {
                abort(403, 'Anda tidak diizinkan mencetak formulir cuti ini.');
            }
        }

        // Format Nomor Surat: Nomor: (nomor buat diisi manual)/UN19.5.1.1.10/KP/2026
        // Pengisian nomor surat dinas hanya diakomodasi dari Admin / Executive
        $isAdminOrExecutive = $user->hasRole('admin') || $user->hasRole('pimpinan') || $user->canAccessExecutiveKepegawaianMenus();
        $tahunSurat = $cuti->created_at ? $cuti->created_at->format('Y') : date('Y');
        $nomorInput = $isAdminOrExecutive 
            ? trim((string) $request->input('nomor_surat_input', $request->input('nomor_surat', '')))
            : '';

        if ($nomorInput !== '') {
            $nomorSuratFormatted = str_contains($nomorInput, '/UN19') 
                ? $nomorInput 
                : $nomorInput . '/UN19.5.1.1.10/KP/' . $tahunSurat;

            // Simpan ke database jika belum tersimpan dan user adalah admin/pimpinan
            if (empty($cuti->nomor_surat) && $isAdminOrExecutive) {
                $cuti->update(['nomor_surat' => $nomorSuratFormatted]);
            }
        } elseif (!empty($cuti->nomor_surat)) {
            $nomorSuratFormatted = str_contains($cuti->nomor_surat, '/UN19') 
                ? $cuti->nomor_surat 
                : $cuti->nomor_surat . '/UN19.5.1.1.10/KP/' . $tahunSurat;
        } else {
            // Default template jika belum diisi manual oleh Admin
            $nomorSuratFormatted = '............/UN19.5.1.1.10/KP/' . $tahunSurat;
        }

        $watermarkService = app(\App\Services\DocumentWatermarkService::class);
        $verifyCode = $watermarkService->generateVerificationCode('Formulir Permohonan Cuti', 'CUTI/' . $nomorSuratFormatted, $cuti->pegawai->nama_lengkap ?? $cuti->pegawai->nama);
        $verifyUrl = route('verify.document', ['code' => $verifyCode]);
        $qrCodeDataUri = \App\Services\QrCodeService::generateDataUri($verifyUrl);

        $pdf = Pdf::loadView('exports.pdf.formulir_cuti', compact('cuti', 'verifyUrl', 'verifyCode', 'qrCodeDataUri', 'nomorSuratFormatted'))
            ->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        $filename = 'Formulir_Cuti_' . ($cuti->pegawai->nip ?? 'Pegawai') . '_' . $cuti->id . '.pdf';
        return $pdf->stream($filename);
    }
}
