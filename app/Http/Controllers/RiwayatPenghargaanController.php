<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatPenghargaan\StoreRiwayatPenghargaanRequest;
use App\Http\Requests\RiwayatPenghargaan\UpdateRiwayatPenghargaanRequest;
use App\Services\RiwayatPenghargaanService;
use App\Traits\AuthorizesRiwayatOwner;

class RiwayatPenghargaanController extends Controller
{
    use AuthorizesRiwayatOwner;

    public function __construct(
        protected RiwayatPenghargaanService $service
    ) {}

    /**
     * List semua data penghargaan
     */
    public function index()
    {
        $data = $this->service->search(request('search'));

        return view('riwayat-penghargaan.index', compact('data'));
    }

    /**
     * Form tambah penghargaan
     */
    public function create()
    {
        return view('riwayat-penghargaan.create', [
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    /**
     * Simpan penghargaan baru
     */
    public function store(StoreRiwayatPenghargaanRequest $request)
    {
        $data = $request->validated();
        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            $data['pegawai_id'] = auth()->user()->pegawai_id;
        }

        $penghargaan = $this->service->create(
            $data,
            $request->file('file_sk')
        );

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Riwayat Penghargaan berhasil disimpan.');
        }

        return redirect()
            ->route('riwayat-penghargaan.index')
            ->with('success', 'Riwayat Penghargaan berhasil disimpan.');
    }

    /**
     * Form edit penghargaan
     */
    public function edit($id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        return view('riwayat-penghargaan.edit', [
            'data'    => $existing,
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    /**
     * Update penghargaan
     */
    public function update(UpdateRiwayatPenghargaanRequest $request, $id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        $data = $request->validated();
        if (auth()->user()->hasRole('pegawai')) {
            $data['pegawai_id'] = $existing->pegawai_id;
        }

        $penghargaan = $this->service->update(
            $id,
            $data,
            $request->file('file_sk')
        );

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Riwayat Penghargaan berhasil diperbarui.');
        }

        return redirect()
            ->route('riwayat-penghargaan.index')
            ->with('success', 'Riwayat Penghargaan berhasil diperbarui.');
    }

    /**
     * Hapus penghargaan
     */
    public function destroy($id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        $this->service->delete($id);

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Riwayat Penghargaan berhasil dihapus.');
        }

        return redirect()
            ->route('riwayat-penghargaan.index')
            ->with('success', 'Riwayat Penghargaan berhasil dihapus.');
    }
}
