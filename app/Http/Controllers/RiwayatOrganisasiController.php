<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatOrganisasi\StoreRiwayatOrganisasiRequest;
use App\Http\Requests\RiwayatOrganisasi\UpdateRiwayatOrganisasiRequest;
use App\Services\RiwayatOrganisasiService;

class RiwayatOrganisasiController extends Controller
{
    public function __construct(
        protected RiwayatOrganisasiService $service
    ) {}

    /**
     * List semua data keanggotaan organisasi
     */
    public function index()
    {
        $data = $this->service->search(request('search'));

        return view('riwayat-organisasi.index', compact('data'));
    }

    /**
     * Form tambah organisasi
     */
    public function create()
    {
        return view('riwayat-organisasi.create', [
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    /**
     * Simpan data organisasi baru
     */
    public function store(StoreRiwayatOrganisasiRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('riwayat-organisasi.index')
            ->with('success', 'Riwayat Organisasi berhasil disimpan.');
    }

    /**
     * Form edit organisasi
     */
    public function edit($id)
    {
        return view('riwayat-organisasi.edit', [
            'data'    => $this->service->find($id),
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    /**
     * Update data organisasi
     */
    public function update(UpdateRiwayatOrganisasiRequest $request, $id)
    {
        $this->service->update($id, $request->validated());

        return redirect()
            ->route('riwayat-organisasi.index')
            ->with('success', 'Riwayat Organisasi berhasil diperbarui.');
    }

    /**
     * Hapus data organisasi
     */
    public function destroy($id)
    {
        $this->service->delete($id);

        return redirect()
            ->route('riwayat-organisasi.index')
            ->with('success', 'Riwayat Organisasi berhasil dihapus.');
    }
}
