<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatOrganisasi\StoreRiwayatOrganisasiRequest;
use App\Http\Requests\RiwayatOrganisasi\UpdateRiwayatOrganisasiRequest;
use App\Services\RiwayatOrganisasiService;
use App\Traits\AuthorizesRiwayatOwner;

class RiwayatOrganisasiController extends Controller
{
    use AuthorizesRiwayatOwner;

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
        $data = $request->validated();
        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            $data['pegawai_id'] = auth()->user()->pegawai_id;
        }

        $this->service->create($data);

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Riwayat Organisasi berhasil disimpan.');
        }

        return redirect()
            ->route('riwayat-organisasi.index')
            ->with('success', 'Riwayat Organisasi berhasil disimpan.');
    }

    /**
     * Form edit organisasi
     */
    public function edit($id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        return view('riwayat-organisasi.edit', [
            'data'    => $existing,
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    /**
     * Update data organisasi
     */
    public function update(UpdateRiwayatOrganisasiRequest $request, $id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        $data = $request->validated();
        if (auth()->user()->hasRole('pegawai')) {
            $data['pegawai_id'] = $existing->pegawai_id;
        }

        $this->service->update($id, $data);

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Riwayat Organisasi berhasil diperbarui.');
        }

        return redirect()
            ->route('riwayat-organisasi.index')
            ->with('success', 'Riwayat Organisasi berhasil diperbarui.');
    }

    /**
     * Hapus data organisasi
     */
    public function destroy($id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        $this->service->delete($id);

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Riwayat Organisasi berhasil dihapus.');
        }

        return redirect()
            ->route('riwayat-organisasi.index')
            ->with('success', 'Riwayat Organisasi berhasil dihapus.');
    }
}
