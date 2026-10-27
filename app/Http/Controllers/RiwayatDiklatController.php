<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatDiklat\StoreRiwayatDiklatRequest;
use App\Http\Requests\RiwayatDiklat\UpdateRiwayatDiklatRequest;
use App\Services\RiwayatDiklatService;
use App\Traits\AuthorizesRiwayatOwner;

class RiwayatDiklatController extends Controller
{
    use AuthorizesRiwayatOwner;

    public function __construct(
        protected RiwayatDiklatService $service
    ) {}

    /**
     * List Data
     */
    public function index()
    {
        $user = auth()->user();
        $isExecutive = $user && $user->canAccessExecutiveKepegawaianMenus();
        $pegawaiId = $isExecutive ? null : ($user?->pegawai_id ?: -1);

        $data = $this->service->filter(
            request('search'),
            request('status'),
            10,
            $pegawaiId
        );

        $statistics = $this->service->statistics($pegawaiId);

        return view('riwayat-diklat.index', compact('data', 'statistics'));
    }

    /**
     * Form Tambah
     */
    public function create()
    {
        $user = auth()->user();
        if (!$user->hasRole('admin') && (!$user->hasRole(['pegawai', 'pimpinan']) || !$user->pegawai_id)) {
            abort(403, 'Anda tidak memiliki hak untuk menambah data riwayat diklat.');
        }

        $isExecutive = $user->canAccessExecutiveKepegawaianMenus();
        $targetPegawaiId = $isExecutive ? null : $user->pegawai_id;

        return view('riwayat-diklat.create', [
            'pegawai' => $this->service->pegawai($targetPegawaiId),
        ]);
    }

    /**
     * Simpan
     */
    public function store(StoreRiwayatDiklatRequest $request)
    {
        $data = $request->validated();
        if (!auth()->user()->hasRole('admin') && auth()->user()->pegawai_id) {
            $data['pegawai_id'] = auth()->user()->pegawai_id;
        }

        $this->service->create(
            $data,
            $request->file('file_sertifikat')
        );

        if (!auth()->user()->hasRole('admin') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with(
                    'success',
                    'Riwayat Diklat berhasil disimpan.'
                );
        }

        return redirect()
            ->route('riwayat-diklat.index')
            ->with(
                'success',
                'Riwayat Diklat berhasil disimpan.'
            );
    }

    /**
     * Form Edit
     */
    public function edit($id)
    {
        $data = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($data);

        return view('riwayat-diklat.edit', [
            'data' => $data,
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    /**
     * Update
     */
    public function update(
        UpdateRiwayatDiklatRequest $request,
        $id
    ) {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        $this->service->update(
            $id,
            $request->validated(),
            $request->file('file_sertifikat')
        );

        if (!auth()->user()->hasRole('admin') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with(
                    'success',
                    'Riwayat Diklat berhasil diperbarui.'
                );
        }

        return redirect()
            ->route('riwayat-diklat.index')
            ->with(
                'success',
                'Riwayat Diklat berhasil diperbarui.'
            );
    }

    /**
     * Hapus
     */
    public function destroy($id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        $this->service->delete($id);

        if (!auth()->user()->hasRole('admin') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with(
                    'success',
                    'Riwayat Diklat berhasil dihapus.'
                );
        }

        return redirect()
            ->route('riwayat-diklat.index')
            ->with(
                'success',
                'Riwayat Diklat berhasil dihapus.'
            );
    }
}