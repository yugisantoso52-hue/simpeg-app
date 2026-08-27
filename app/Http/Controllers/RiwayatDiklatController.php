<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatDiklat\StoreRiwayatDiklatRequest;
use App\Http\Requests\RiwayatDiklat\UpdateRiwayatDiklatRequest;
use App\Services\RiwayatDiklatService;

class RiwayatDiklatController extends Controller
{
    public function __construct(
        protected RiwayatDiklatService $service
    ) {}

    /**
     * List Data
     */
    public function index()
    {
        $data = $this->service->filter(
            request('search'),
            request('status')
        );

        $statistics = $this->service->statistics();

        return view('riwayat-diklat.index', compact(
            'data',
            'statistics'
        ));
    }

    /**
     * Form Tambah
     */
    public function create()
    {
        return view('riwayat-diklat.create', [
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    /**
     * Simpan
     */
    public function store(StoreRiwayatDiklatRequest $request)
    {
        $this->service->create(
            $request->validated(),
            $request->file('file_sertifikat')
        );

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
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
        return view('riwayat-diklat.edit', [
            'data' => $this->service->find($id),
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

        $this->service->update(
            $id,
            $request->validated(),
            $request->file('file_sertifikat')
        );

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
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
        $this->service->delete($id);

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
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