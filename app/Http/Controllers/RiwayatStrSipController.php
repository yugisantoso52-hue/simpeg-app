<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatStrSip\StoreRiwayatStrSipRequest;
use App\Http\Requests\RiwayatStrSip\UpdateRiwayatStrSipRequest;
use App\Services\RiwayatStrSipService;
use App\Traits\AuthorizesRiwayatOwner;

class RiwayatStrSipController extends Controller
{
    use AuthorizesRiwayatOwner;

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
        $data = $request->validated();
        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            $data['pegawai_id'] = auth()->user()->pegawai_id;
        }

        $this->service->create(
            $data,
            $request->file('file_dokumen')
        );

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Data STR / SIP berhasil disimpan.');
        }

        return redirect()
            ->route('riwayat-str-sip.index')
            ->with('success', 'Data STR / SIP berhasil disimpan.');
    }

    public function edit(int $id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        return view('riwayat-str-sip.edit', [
            'data'    => $existing,
            'pegawai' => $this->service->pegawai(),
        ]);
    }

    public function update(UpdateRiwayatStrSipRequest $request, int $id)
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
            $request->file('file_dokumen')
        );

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Data STR / SIP berhasil diperbarui.');
        }

        return redirect()
            ->route('riwayat-str-sip.index')
            ->with('success', 'Data STR / SIP berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        $this->service->delete($id);

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Data STR / SIP berhasil dihapus.');
        }

        return redirect()
            ->route('riwayat-str-sip.index')
            ->with('success', 'Data STR / SIP berhasil dihapus.');
    }
}
