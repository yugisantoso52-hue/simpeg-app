<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatPublikasi\StoreRiwayatPublikasiRequest;
use App\Http\Requests\RiwayatPublikasi\UpdateRiwayatPublikasiRequest;
use App\Services\RiwayatPublikasiService;
use App\Traits\AuthorizesRiwayatOwner;

class RiwayatPublikasiController extends Controller
{
    use AuthorizesRiwayatOwner;

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
        $data = $request->validated();
        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            $data['pegawai_id'] = auth()->user()->pegawai_id;
        }

        $this->service->create(
            $data,
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
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        return view('riwayat-publikasi.edit', [
            'data'    => $existing,
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    /**
     * Update data publikasi
     */
    public function update(UpdateRiwayatPublikasiRequest $request, $id)
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
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

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
