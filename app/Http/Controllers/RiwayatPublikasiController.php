<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatPublikasi\StoreRiwayatPublikasiRequest;
use App\Http\Requests\RiwayatPublikasi\UpdateRiwayatPublikasiRequest;
use App\Services\RiwayatPublikasiService;

class RiwayatPublikasiController extends Controller
{
    public function __construct(
        protected RiwayatPublikasiService $service
    ) {}

    /**
     * List semua data publikasi ilmiah
     */
    public function index()
    {
        $data = $this->service->search(request('search'));

        return view('riwayat-publikasi.index', compact('data'));
    }

    /**
     * Form tambah publikasi
     */
    public function create()
    {
        return view('riwayat-publikasi.create', [
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    /**
     * Simpan publikasi baru
     */
    public function store(StoreRiwayatPublikasiRequest $request)
    {
        $this->service->create(
            $request->validated(),
            $request->file('file_publikasi')
        );

        return redirect()
            ->route('riwayat-publikasi.index')
            ->with('success', 'Riwayat Publikasi berhasil disimpan.');
    }

    /**
     * Form edit publikasi
     */
    public function edit($id)
    {
        return view('riwayat-publikasi.edit', [
            'data'    => $this->service->find($id),
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    /**
     * Update data publikasi
     */
    public function update(UpdateRiwayatPublikasiRequest $request, $id)
    {
        $this->service->update(
            $id,
            $request->validated(),
            $request->file('file_publikasi')
        );

        return redirect()
            ->route('riwayat-publikasi.index')
            ->with('success', 'Riwayat Publikasi berhasil diperbarui.');
    }

    /**
     * Hapus data publikasi
     */
    public function destroy($id)
    {
        $this->service->delete($id);

        return redirect()
            ->route('riwayat-publikasi.index')
            ->with('success', 'Riwayat Publikasi berhasil dihapus.');
    }
}
