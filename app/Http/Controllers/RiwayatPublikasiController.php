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

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Riwayat Publikasi berhasil disimpan.');
        }

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

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Riwayat Publikasi berhasil diperbarui.');
        }

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

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Riwayat Publikasi berhasil dihapus.');
        }

        return redirect()
            ->route('riwayat-publikasi.index')
            ->with('success', 'Riwayat Publikasi berhasil dihapus.');
    }
}
