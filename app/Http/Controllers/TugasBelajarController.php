<?php

namespace App\Http\Controllers;

use App\Http\Requests\TugasBelajar\StoreTugasBelajarRequest;
use App\Http\Requests\TugasBelajar\UpdateTugasBelajarRequest;
use App\Services\TugasBelajarService;
use App\Traits\AuthorizesRiwayatOwner;
use Illuminate\Http\Request;

class TugasBelajarController extends Controller
{
    use AuthorizesRiwayatOwner;

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
        $data = $request->validated();
        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            $data['pegawai_id'] = auth()->user()->pegawai_id;
        }

        $this->service->create(
            $data,
            $request->file('file_sk'),
            $request->file('file_laporan_progress')
        );

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Data Tugas / Izin Belajar berhasil disimpan.');
        }

        return redirect()
            ->route('tugas-belajar.index')
            ->with('success', 'Data Tugas / Izin Belajar berhasil disimpan dan status pegawai telah disinkronkan.');
    }

    public function edit(int $id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        return view('tugas-belajar.edit', [
            'data'    => $existing,
            'pegawai' => $this->service->pegawaiList(),
        ]);
    }

    public function update(UpdateTugasBelajarRequest $request, int $id)
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
            $request->file('file_sk'),
            $request->file('file_laporan_progress')
        );

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Data Tugas / Izin Belajar berhasil diperbarui.');
        }

        return redirect()
            ->route('tugas-belajar.index')
            ->with('success', 'Data Tugas / Izin Belajar berhasil diperbarui dan status pegawai telah disinkronkan.');
    }

    public function destroy(int $id)
    {
        $existing = $this->service->find($id);
        $this->authorizeOwnerOrAdmin($existing);

        $this->service->delete($id);

        if (auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id) {
            return redirect()
                ->route('pegawai.show', auth()->user()->pegawai_id)
                ->with('success', 'Data Tugas / Izin Belajar berhasil dihapus.');
        }

        return redirect()
            ->route('tugas-belajar.index')
            ->with('success', 'Data Tugas / Izin Belajar berhasil dihapus.');
    }
}
