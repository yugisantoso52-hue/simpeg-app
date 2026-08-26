<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatStrSip\StoreRiwayatStrSipRequest;
use App\Http\Requests\RiwayatStrSip\UpdateRiwayatStrSipRequest;
use App\Services\RiwayatStrSipService;

class RiwayatStrSipController extends Controller
{
    public function __construct(
        protected RiwayatStrSipService $service
    ) {}

    public function index()
    {
        $data = $this->service->filter(
            request('search'),
            request('jenis'),
            request('status')
        );

        $statistics = $this->service->statistics();

        return view('riwayat-str-sip.index', compact('data', 'statistics'));
    }

    public function create()
    {
        return view('riwayat-str-sip.create', [
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    public function store(StoreRiwayatStrSipRequest $request)
    {
        $this->service->create(
            $request->validated(),
            $request->file('file_dokumen')
        );

        return redirect()
            ->route('riwayat-str-sip.index')
            ->with('success', 'Data STR / SIP berhasil disimpan.');
    }

    public function edit(int $id)
    {
        return view('riwayat-str-sip.edit', [
            'data'    => $this->service->find($id),
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    public function update(UpdateRiwayatStrSipRequest $request, int $id)
    {
        $this->service->update(
            $id,
            $request->validated(),
            $request->file('file_dokumen')
        );

        return redirect()
            ->route('riwayat-str-sip.index')
            ->with('success', 'Data STR / SIP berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return redirect()
            ->route('riwayat-str-sip.index')
            ->with('success', 'Data STR / SIP berhasil dihapus.');
    }
}
