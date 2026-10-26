<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengajuanCuti\ApprovePengajuanCutiRequest;
use App\Http\Requests\PengajuanCuti\StorePengajuanCutiRequest;
use App\Models\Pegawai;
use App\Models\User;
use App\Services\NomorSuratService;
use App\Services\PengajuanCutiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PengajuanCutiController extends Controller
{
    public function __construct(
        protected PengajuanCutiService $service
    ) {}

    /**
     * Resolusi otomatis relasi Pegawai untuk User yang sedang login
     */
    protected function resolveUserPegawai(?User $user): ?Pegawai
    {
        if (!$user) {
            return null;
        }

        if ($user->pegawai) {
            return $user->pegawai;
        }

        if ($user->pegawai_id) {
            $pegawai = Pegawai::find($user->pegawai_id);
            if ($pegawai) {
                return $pegawai;
            }
        }

        $pegawai = Pegawai::where('email', $user->email)
            ->orWhere('nip', $user->name)
            ->first();

        if ($pegawai) {
            $user->update(['pegawai_id' => $pegawai->id]);
            $user->setRelation('pegawai', $pegawai);
            return $pegawai;
        }

        return null;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $pegawai = $this->resolveUserPegawai($user);
        $isAdmin = $user->hasRole('admin');
        $isAtasan = $user->isAtasan() || $user->hasRole('pimpinan');
        $isPegawaiOnly = !$isAdmin && !$isAtasan;
        $pegawaiId = $isPegawaiOnly ? ($user->pegawai_id ?? $pegawai?->id) : null;

        if ($isPegawaiOnly && !$pegawaiId) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        // Scoping daftar cuti berdasarkan Matriks Hierarki E-Cuti:
        $scopedPegawaiIds = null;
        if (!$isAdmin && $isAtasan) {
            if ($pegawai) {
                $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
                $scopedPegawaiIds = $hierarchyService->getScopedCutiPegawaiIds($pegawai);
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

        return view('pengajuan-cuti.index', compact('data', 'statistics', 'isPegawaiOnly', 'pegawai', 'isAtasan', 'isAdmin'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        $isPegawaiOnly = !$isAdmin;
        $pegawai = $this->resolveUserPegawai($user);
        $pegawaiList = $isAdmin ? $this->service->pegawaiList() : collect();

        if (!$isAdmin && !$pegawai) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        return view('pengajuan-cuti.create', compact('isPegawaiOnly', 'pegawai', 'pegawaiList'));
    }

    public function store(StorePengajuanCutiRequest $request)
    {
        $user = $request->user();
        $pegawai = $this->resolveUserPegawai($user);
        $pegawaiId = $user->hasRole('admin') && $request->filled('pegawai_id')
            ? (int)$request->get('pegawai_id')
            : (int)($user->pegawai_id ?? $pegawai?->id);

        if (!$pegawaiId) {
            return redirect()->back()->withInput()->with('error', 'Data pegawai tidak ditemukan.');
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

        $isKaPokja   = $user->isKaPokjaKepeg();
        $isKabag     = $user->isKabagUmum();
        $isWd2       = $user->isWadek2();
        $isDekan     = $user->isDekan();
        $isExecutive = $user->canAccessExecutiveKepegawaianMenus();

        return view('pengajuan-cuti.show', compact(
            'cuti', 'pejabatInfo', 'isAdmin', 'isKaPokja', 'isKabag', 'isWd2', 'isDekan', 'isExecutive'
        ));
    }

    public function approve(ApprovePengajuanCutiRequest $request, int $id)
    {
        $validated = $request->validated();
        $tahunSurat = date('Y');

        $nomorSuratService = app(\App\Services\NomorSuratService::class);
        if ($request->filled('nomor_surat_input')) {
            $nomorInput = trim((string)$request->input('nomor_surat_input'));
            $validated['nomor_surat'] = $nomorSuratService->formatCuti($nomorInput, (int) $tahunSurat);
        } elseif ($request->filled('nomor_surat')) {
            $nomorRaw = trim((string)$request->input('nomor_surat'));
            $validated['nomor_surat'] = $nomorSuratService->formatCuti($nomorRaw, (int) $tahunSurat);
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

    /**
     * Standarisasi Verifikasi Berkas & Paraf Koordinasi Pengajuan Cuti (SOP SIKAP):
     * Tahap 1: Admin Screening Berkas Lampiran & Penomoran Resmi (verifikasi_admin)
     * Tahap 2: Paraf Koordinasi Tata Naskah & Verifikasi Kepegawaian oleh Ka Pokja Keu-Kepeg (paraf_kapokja)
     * Catatan: Kepala Bagian Umum bertindak sebagai Pejabat Penandatangan TTE pada kolom VI. Pertimbangan Atasan Langsung,
     *          sehingga di bagian Paraf Koordinasi Tata Naskah & Verifikasi Kepegawaian hanya terdapat Ka Pokja Keu-Kepeg.
     * Tahap Penolakan: Pengembalian Berkas dengan Catatan Wajib (tolak)
     */
    public function verifikasi(Request $request, int $id)
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $cuti = $this->service->find($id);
        $tahap = $request->input('tahap');
        $catatan = $request->input('catatan');
        $pejabatLogin = $user->pegawai;

        $isAdmin   = $user->hasRole('admin');
        $isKaPokja = $user->isKaPokjaKepeg();
        $isKabag   = $user->isKabagUmum();
        $isWd2     = $user->isWadek2();
        $isDekan   = $user->isDekan();

        if ($tahap === 'verifikasi_admin') {
            if (!$isAdmin) {
                return back()->with('error', 'Otorisasi Ditolak: Tahap 1 (Admin Screening Berkas & Penomoran) hanya berhak dilakukan oleh Admin Kepegawaian.');
            }

            $updateData = [
                'verifikasi_admin_at' => now(),
                'verifikasi_admin_by' => $user->id,
                'catatan_verifikator' => $catatan ?? 'Telah diverifikasi kelengkapan berkas fisik, lampiran, dan kuota cuti oleh Admin Kepegawaian.',
            ];

            if ($request->filled('nomor_surat_input')) {
                $inputNomor = trim((string) $request->input('nomor_surat_input'));
                $tahunSurat = $cuti->created_at ? $cuti->created_at->format('Y') : date('Y');
                $updateData['nomor_surat'] = app(NomorSuratService::class)->formatCuti($inputNomor, (int) $tahunSurat);
            }

            $cuti->update($updateData);
            return back()->with('success', '1. Verifikasi Screening Berkas Lampiran & Penomoran Cuti oleh Admin Kepegawaian berhasil.');
        } elseif ($tahap === 'paraf_kapokja') {
            if (!$isKaPokja && !$isAdmin) {
                return back()->with('error', 'Otorisasi Ditolak: Paraf Koordinasi Tata Naskah & Verifikasi Kepegawaian hanya berhak dilakukan oleh Ka Pokja Keu-Kepeg.');
            }

            if (!$cuti->verifikasi_admin_at) {
                return back()->with('error', 'SOP Tata Naskah: Berkas cuti harus diperiksa kelengkapannya (Admin Screening Berkas) terlebih dahulu sebelum diparaf Ka Pokja Keu-Kepeg.');
            }

            $cuti->update([
                'paraf_kapokja_at'    => now(),
                'paraf_kapokja_by'    => $pejabatLogin?->id,
                'catatan_verifikator' => $catatan ?? 'Telah diperiksa status kepegawaian dan kuota cuti oleh Ka Pokja Keu-Kepeg.',
            ]);

            return back()->with('success', '2. Paraf Koordinasi Tata Naskah & Verifikasi Kepegawaian oleh Ka Pokja Keu-Kepeg berhasil dibubuhkan.');
        } elseif ($tahap === 'paraf_kabag') {
            // Untuk Cuti, Kepala Bagian Umum memberikan TTE pada kolom VI. Pertimbangan Atasan Langsung
            if (!$isKabag && !$isAdmin) {
                return back()->with('error', 'Otorisasi Ditolak: Pertimbangan Atasan Langsung hanya berhak dilakukan oleh Kepala Bagian Umum.');
            }

            $kabagPegawai = app(\App\Services\PejabatService::class)->getKabagPegawai();
            $kabagUser = $kabagPegawai ? User::where('pegawai_id', $kabagPegawai->id)->first() : null;

            $cuti->update([
                'paraf_kabag_at'          => now(),
                'paraf_kabag_by'          => $pejabatLogin?->id ?? $kabagPegawai?->id,
                'pertimbangan_atasan'     => $cuti->pertimbangan_atasan ?: 'Disetujui',
                'pertimbangan_atasan_at'  => now(),
                'atasan_langsung_id'      => $kabagUser?->id ?? $user->id,
                'catatan_atasan_langsung' => $catatan ?? $cuti->catatan_atasan_langsung ?? 'Disetujui oleh Kepala Bagian Umum.',
                'status'                  => in_array($cuti->status, ['Disetujui', 'Ditolak']) ? $cuti->status : 'Disetujui Atasan (Menunggu PYBMC)',
            ]);

            return back()->with('success', 'Pertimbangan Atasan Langsung (TTE Kepala Bagian Umum) berhasil disimpan.');
        } elseif ($tahap === 'tolak') {
            $canReject = $isAdmin || $isKaPokja || $isKabag || $isWd2 || $isDekan || $user->isAtasan() || $user->hasRole('pimpinan');
            if (!$canReject) {
                return back()->with('error', 'Anda tidak memiliki hak wewenang untuk menolak permohonan cuti ini.');
            }

            $cuti->update([
                'status'              => 'Ditolak',
                'approved_by'         => $user->id,
                'approved_at'         => now(),
                'catatan_pimpinan'    => $catatan ?? 'Berkas permohonan cuti ditolak / tidak disetujui.',
                'catatan_verifikator' => $catatan ?? 'Berkas permohonan cuti ditolak / tidak disetujui.',
            ]);

            return back()->with('success', 'Permohonan cuti telah ditolak / dikembalikan.');
        }

        return back()->with('error', 'Tahapan verifikasi tidak valid.');
    }

    public function updateNomorSurat(Request $request, int $id)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Hanya Admin Kepegawaian yang berwenang mengisi atau mengubah nomor surat.');
        }

        $cuti = $this->service->find($id);
        $nomorInput = trim((string) $request->input('nomor_surat_input', ''));
        $tahunSurat = $cuti->created_at ? $cuti->created_at->format('Y') : date('Y');

        if ($nomorInput !== '') {
            $nomorSuratFormatted = app(\App\Services\NomorSuratService::class)->formatCuti($nomorInput, (int) $tahunSurat);
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

        $nomorSuratService = app(\App\Services\NomorSuratService::class);
        if ($nomorInput !== '') {
            $nomorSuratFormatted = $nomorSuratService->formatCuti($nomorInput, (int) $tahunSurat);

            // Simpan ke database jika belum tersimpan dan user adalah admin/pimpinan
            if (empty($cuti->nomor_surat) && $isAdminOrExecutive) {
                $cuti->update(['nomor_surat' => $nomorSuratFormatted]);
            }
        } elseif (!empty($cuti->nomor_surat)) {
            $nomorSuratFormatted = $nomorSuratService->formatCuti($cuti->nomor_surat, (int) $tahunSurat);
        } else {
            // Default template jika belum diisi manual oleh Admin
            $nomorSuratFormatted = $nomorSuratService->formatCuti('', (int) $tahunSurat);
        }

        $watermarkService = app(\App\Services\DocumentWatermarkService::class);
        $verifyCode = $watermarkService->generateVerificationCode('Formulir Permohonan Cuti', 'CUTI/' . $nomorSuratFormatted, $cuti->pegawai->nama_lengkap ?? $cuti->pegawai->nama);
        $verifyUrl = route('verify.document', ['code' => $verifyCode]);
        $qrCodeDataUri = \App\Services\QrCodeService::generateDataUri($verifyUrl);

        $pejabatService = app(\App\Services\PejabatService::class);
        $kabagData      = $pejabatService->getKabag();
        $kapokjaData    = $pejabatService->getKaPokja();

        $pejabatKabag   = $kabagData['pegawai'];
        $pejabatKaPokja = $kapokjaData['pegawai'];

        $sudahParafPokja = !empty($cuti->paraf_kapokja_at);
        $sudahParafKabag = !empty($cuti->paraf_kabag_at);
        $parafPokjaAt    = $sudahParafPokja ? $cuti->paraf_kapokja_at->format('d/m/Y H:i') . ' WIB' : null;
        $parafKabagAt    = $sudahParafKabag ? $cuti->paraf_kabag_at->format('d/m/Y H:i') . ' WIB' : null;
        $qrPokjaUri      = $sudahParafPokja ? \App\Services\QrCodeService::generateDataUri($verifyUrl . '&paraf=kapokja&nip=' . ($pejabatKaPokja?->nip ?? '')) : null;
        $qrKabagUri      = $sudahParafKabag ? \App\Services\QrCodeService::generateDataUri($verifyUrl . '&paraf=kabag&nip=' . ($pejabatKabag?->nip ?? '')) : null;

        $pdf = Pdf::loadView('exports.pdf.formulir_cuti', compact(
            'cuti', 'verifyUrl', 'verifyCode', 'qrCodeDataUri', 'nomorSuratFormatted',
            'pejabatKabag', 'pejabatKaPokja', 'sudahParafPokja', 'sudahParafKabag',
            'parafPokjaAt', 'parafKabagAt', 'qrPokjaUri', 'qrKabagUri'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        $filename = 'Formulir_Cuti_' . ($cuti->pegawai->nip ?? 'Pegawai') . '_' . $cuti->id . '.pdf';
        return $pdf->stream($filename);
    }
}
