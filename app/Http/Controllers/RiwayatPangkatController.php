<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatPangkat\StoreRiwayatPangkatRequest;
use App\Http\Requests\RiwayatPangkat\UpdateRiwayatPangkatRequest;
use App\Services\RiwayatPangkatService;
use Illuminate\Http\Request;

class RiwayatPangkatController extends Controller
{
    public function __construct(
        protected RiwayatPangkatService $service
    ) {}

    public function index(Request $request)
    {
        $data = $this->service->filter(
            $request->input('search'),
            $request->input('status')
        );

        $statistics = $this->service->getStatistics();

        return view('riwayat-pangkat.index', compact(
            'data',
            'statistics'
        ));
    }

    public function create()
    {
        return view('riwayat-pangkat.create', [
            'pegawai'  => $this->service->pegawai(),
            'golongan' => $this->service->golongan(),
        ]);
    }

    public function store(StoreRiwayatPangkatRequest $request)
    {
        $this->service->create(
            $request->validated(),
            $request->file('file_sk')
        );

        return redirect()
            ->route('riwayat-pangkat.index')
            ->with('success', 'Riwayat pangkat berhasil disimpan.');
    }

    public function edit($id)
    {
        return view('riwayat-pangkat.edit', [
            'data'     => $this->service->find($id),
            'pegawai'  => $this->service->pegawai(),
            'golongan' => $this->service->golongan(),
        ]);
    }

    public function update(UpdateRiwayatPangkatRequest $request, $id)
    {
        $this->service->update(
            $id,
            $request->validated(),
            $request->file('file_sk')
        );

        return redirect()
            ->route('riwayat-pangkat.index')
            ->with('success', 'Riwayat pangkat berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return redirect()
            ->route('riwayat-pangkat.index')
            ->with('success', 'Riwayat pangkat berhasil dihapus.');
    }
}