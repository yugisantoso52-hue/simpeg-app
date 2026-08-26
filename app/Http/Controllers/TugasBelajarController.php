<?php

namespace App\Http\Controllers;

use App\Http\Requests\TugasBelajar\StoreTugasBelajarRequest;
use App\Http\Requests\TugasBelajar\UpdateTugasBelajarRequest;
use App\Services\TugasBelajarService;
use Illuminate\Http\Request;

class TugasBelajarController extends Controller
{
    public function __construct(
        protected TugasBelajarService $service
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $isPegawaiOnly = $user->hasRole('pegawai') && !$user->hasRole(['admin', 'pimpinan']);
        $pegawaiId = $isPegawaiOnly ? $user->pegawai_id : null;

        $data = $this->service->filter(
            $request->get('search'),
            $request->get('jenjang'),
            $request->get('status'),
            $pegawaiId
        );

        $statistics = $this->service->statistics($pegawaiId);

        return view('tugas-belajar.index', compact('data', 'statistics', 'isPegawaiOnly'));
    }

    public function create()
    {
        return view('tugas-belajar.create', [
            'pegawai' => $this->service->pegawaiList(),
        ]);
    }

    public function store(StoreTugasBelajarRequest $request)
    {
        $this->service->create(
            $request->validated(),
            $request->file('file_sk'),
            $request->file('file_laporan_progress')
        );

        return redirect()
            ->route('tugas-belajar.index')
            ->with('success', 'Data Tugas / Izin Belajar berhasil disimpan dan status pegawai telah disinkronkan.');
    }

    public function edit(int $id)
    {
        return view('tugas-belajar.edit', [
            'data'    => $this->service->find($id),
            'pegawai' => $this->service->pegawaiList(),
        ]);
    }

    public function update(UpdateTugasBelajarRequest $request, int $id)
    {
        $this->service->update(
            $id,
            $request->validated(),
            $request->file('file_sk'),
            $request->file('file_laporan_progress')
        );

        return redirect()
            ->route('tugas-belajar.index')
            ->with('success', 'Data Tugas / Izin Belajar berhasil diperbarui dan status pegawai telah disinkronkan.');
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return redirect()
            ->route('tugas-belajar.index')
            ->with('success', 'Data Tugas / Izin Belajar berhasil dihapus.');
    }
}
