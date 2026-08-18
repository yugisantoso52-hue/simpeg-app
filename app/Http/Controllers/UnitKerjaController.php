<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitKerja\StoreUnitKerjaRequest;
use App\Http\Requests\UnitKerja\UpdateUnitKerjaRequest;
use App\Services\UnitKerjaService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class UnitKerjaController extends Controller
{
    protected UnitKerjaService $service;

    public function __construct(UnitKerjaService $service)
    {
        $this->service = $service;
    }

    /**
     * Daftar Unit Kerja
     */
    public function index(Request $request): View
    {
        // Paginate default 10 dengan pencarian
        $unitKerja = $this->service->paginate($request->search, 10);

        return view('unitkerja.index', compact('unitKerja'));
    }

    /**
     * Form Tambah
     */
    public function create(): View
    {
        return view('unitkerja.create');
    }

    /**
     * Simpan Data
     */
    public function store(StoreUnitKerjaRequest $request): RedirectResponse
    {
        try {
            $this->service->create($request->validated());

            return redirect()
                ->route('unit-kerja.index')
                ->with('success', 'Data Unit Kerja berhasil disimpan.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Form Edit
     */
    public function edit(int|string $unit_kerja): View
    {
        $unitKerja = $this->service->find($unit_kerja);

        return view('unitkerja.edit', compact('unitKerja'));
    }

    /**
     * Update Data
     */
    public function update(UpdateUnitKerjaRequest $request, int|string $unit_kerja): RedirectResponse
    {
        try {
            $this->service->update($unit_kerja, $request->validated());

            return redirect()
                ->route('unit-kerja.index')
                ->with('success', 'Data Unit Kerja berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Data
     */
    public function destroy(int|string $unit_kerja): RedirectResponse
    {
        try {
            $this->service->delete($unit_kerja);

            return redirect()
                ->route('unit-kerja.index')
                ->with('success', 'Data Unit Kerja berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()
                ->route('unit-kerja.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}