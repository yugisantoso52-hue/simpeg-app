<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatSkp\StoreRiwayatSkpRequest;
use App\Http\Requests\RiwayatSkp\UpdateRiwayatSkpRequest;
use App\Services\RiwayatSkpService;
use App\Traits\AuthorizesRiwayatOwner;
use Illuminate\Http\Request;

class RiwayatSkpController extends Controller
{
    use AuthorizesRiwayatOwner;

    public function __construct(
        protected RiwayatSkpService $service
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $isPegawaiOnly = $user->hasRole('pegawai') && !$user->hasRole(['admin', 'pimpinan']);
        $pegawaiId = $isPegawaiOnly ? $user->pegawai_id : null;

        $data = $this->service->filter(
            $request->get('search'),
            $request->filled('tahun') ? (int)$request->get('tahun') : null,
            $request->get('predikat'),
            $pegawaiId
        );

        $statistics = $this->service->statistics($pegawaiId);

        return view('riwayat-skp.index', compact('data', 'statistics', 'isPegawaiOnly'));
    }

    public function create()
    {
        return view('riwayat-skp.create', [
            'pegawai' => $this->service->pegawaiList(),
        ]);
    }

    public function store(StoreRiwayatSkpRequest $request)
    {
        $data = $request->validated();
        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            $data['pegawai_id'] = auth()->user()->pegawai_id;
        }

        $this->service->create(
            $data,
            $request->file('file_rencana_skp'),
            $request->file('file_evaluasi_skp')
        );

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Dokumen SKP berhasil disimpan.');
        }

        return redirect()
            ->route('riwayat-skp.index')
            ->with('success', 'Dokumen SKP berhasil disimpan.');
    }

    public function edit(int $id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        return view('riwayat-skp.edit', [
            'data'    => $existing,
            'pegawai' => $this->service->pegawaiList(),
        ]);
    }

    public function update(UpdateRiwayatSkpRequest $request, int $id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        $data = $request->validated();
        if (auth()->user()->hasRole('pegawai')) {
            $data['pegawai_id'] = $existing->pegawai_id;
        }

        $this->service->update(
            $id,
            $data,
            $request->file('file_rencana_skp'),
            $request->file('file_evaluasi_skp')
        );

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Dokumen SKP berhasil diperbarui.');
        }

        return redirect()
            ->route('riwayat-skp.index')
            ->with('success', 'Dokumen SKP berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        $this->service->delete($id);

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Data SKP berhasil dihapus.');
        }

        return redirect()
            ->route('riwayat-skp.index')
            ->with('success', 'Data SKP berhasil dihapus.');
    }
}
