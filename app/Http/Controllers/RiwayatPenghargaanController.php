<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatPenghargaan\StoreRiwayatPenghargaanRequest;
use App\Http\Requests\RiwayatPenghargaan\UpdateRiwayatPenghargaanRequest;
use App\Services\RiwayatPenghargaanService;

class RiwayatPenghargaanController extends Controller
{
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
        $this->service->create(
            $request->validated(),
            $request->file('file_sk')
        );

        return redirect()
            ->route('riwayat-penghargaan.index')
            ->with('success', 'Riwayat Penghargaan berhasil disimpan.');
    }

    /**
     * Form edit penghargaan
     */
    public function edit($id)
    {
        return view('riwayat-penghargaan.edit', [
            'data'    => $this->service->find($id),
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    /**
     * Update penghargaan
     */
    public function update(UpdateRiwayatPenghargaanRequest $request, $id)
    {
        $this->service->update(
            $id,
            $request->validated(),
            $request->file('file_sk')
        );

        return redirect()
            ->route('riwayat-penghargaan.index')
            ->with('success', 'Riwayat Penghargaan berhasil diperbarui.');
    }

    /**
     * Hapus penghargaan
     */
    public function destroy($id)
    {
        $this->service->delete($id);

        return redirect()
            ->route('riwayat-penghargaan.index')
            ->with('success', 'Riwayat Penghargaan berhasil dihapus.');
    }
}
