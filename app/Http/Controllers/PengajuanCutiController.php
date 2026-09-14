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
        $isPegawaiOnly = $user->hasRole('pegawai') && !$user->hasRole(['admin', 'pimpinan']);
        $pegawaiId = $isPegawaiOnly ? $user->pegawai_id : null;

        if ($isPegawaiOnly && !$pegawaiId) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        $data = $this->service->filter(
            $request->get('search'),
            $request->get('jenis'),
            $request->get('status'),
            $pegawaiId
        );

        $statistics = $this->service->statistics($pegawaiId);
        $pegawai = $isPegawaiOnly ? $user->pegawai : null;

        return view('pengajuan-cuti.index', compact('data', 'statistics', 'isPegawaiOnly', 'pegawai'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $isPegawaiOnly = $user->hasRole('pegawai') && !$user->hasRole(['admin', 'pimpinan']);
        $pegawai = $isPegawaiOnly ? $user->pegawai : null;
        $pegawaiList = $user->hasRole('admin') ? $this->service->pegawaiList() : collect();

        if ($isPegawaiOnly && !$pegawai) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        return view('pengajuan-cuti.create', compact('isPegawaiOnly', 'pegawai', 'pegawaiList'));
    }

    public function store(StorePengajuanCutiRequest $request)
    {
        $user = $request->user();
        $pegawaiId = $user->hasRole('admin') && $request->filled('pegawai_id')
            ? (int)$request->get('pegawai_id')
            : (int)$user->pegawai_id;

        if (!$pegawaiId) {
            return redirect()->back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $this->service->create(
            $request->validated(),
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

        // Otorisasi: Pegawai biasa hanya bisa melihat permohonannya sendiri
        if ($user->hasRole('pegawai') && !$user->hasRole(['admin', 'pimpinan'])) {
            if ($user->pegawai_id !== $cuti->pegawai_id) {
                abort(403, 'Anda tidak diizinkan melihat data permohonan cuti ini.');
            }
        }

        return view('pengajuan-cuti.show', compact('cuti'));
    }

    public function approve(ApprovePengajuanCutiRequest $request, int $id)
    {
        $this->service->approve($id, $request->validated(), (int)$request->user()->id);

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

    public function destroy(int $id)
    {
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

        $pdf = Pdf::loadView('exports.pdf.formulir_cuti', compact('cuti'))
            ->setPaper('a4', 'portrait');

        $filename = 'Formulir_Cuti_' . ($cuti->pegawai->nip ?? 'Pegawai') . '_' . $cuti->id . '.pdf';
        return $pdf->stream($filename);
    }
}
