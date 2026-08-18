<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatJabatan\StoreRiwayatJabatanRequest;
use App\Http\Requests\RiwayatJabatan\UpdateRiwayatJabatanRequest;
use App\Services\RiwayatJabatanService;
use Illuminate\Http\Request;

class RiwayatJabatanController extends Controller
{
    public function __construct(
        protected RiwayatJabatanService $service
    ) {
    }

    /**
     * List
     */
    public function index(Request $request)
    {
        $data = $this->service->filter(
            $request->search,
            $request->status
        );

        return view('riwayat-jabatan.index', compact('data'));
    }

    /**
     * Form tambah
     */
    public function create()
{
    return view('riwayat-jabatan.create', [
        'pegawai'    => $this->service->pegawai(),
        'jabatan'    => $this->service->jabatan(),
        'unit_kerja' => $this->service->unitKerja(),
    ]);
}

    /**
     * Simpan
     */
    public function store(StoreRiwayatJabatanRequest $request)
    {
        $this->service->create(
            $request->validated(),
            $request->file('file_sk')
        );

        return redirect()
            ->route('riwayat-jabatan.index')
            ->with('success', 'Riwayat Jabatan berhasil disimpan.');
    }

    /**
     * Form edit
     */
   public function edit($id)
{
    return view('riwayat-jabatan.edit', [
        'data'        => $this->service->find($id),
        'pegawai'     => $this->service->pegawai(),
        'jabatan'     => $this->service->jabatan(),
        'unit_kerja'  => $this->service->unitKerja(),
    ]);
}

    /**
     * Update
     */
    public function update(
        UpdateRiwayatJabatanRequest $request,
        int $id
    ) {

        $this->service->update(

            $id,

            $request->validated(),

            $request->file('file_sk')

        );

        return redirect()
            ->route('riwayat-jabatan.index')
            ->with('success', 'Riwayat Jabatan berhasil diperbarui.');
    }

    /**
     * Hapus
     */
    public function destroy(int $id)
    {
        $this->service->delete($id);

        return redirect()
            ->route('riwayat-jabatan.index')
            ->with('success', 'Riwayat Jabatan berhasil dihapus.');
    }

    /**
     * Detail pegawai
     */
    public function showByPegawai(int $pegawaiId)
    {
        $data = $this->service->getByPegawai($pegawaiId);

        return view(
            'riwayat-jabatan.by-pegawai',
            compact('data')
        );
    }
}