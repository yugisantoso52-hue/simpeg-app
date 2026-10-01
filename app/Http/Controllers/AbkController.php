<?php

namespace App\Http\Controllers;

use App\Models\AnalisisJabatan;
use App\Models\AbkUraianTugas;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class AbkController extends Controller
{
    /**
     * Dashboard Rekapitulasi Formasi & Analisis Beban Kerja
     */
    public function index(Request $request)
    {
        $query = AnalisisJabatan::with(['jabatan', 'unitKerja', 'uraianTugas']);

        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        $anjabs = $query->get();

        // Rekapitulasi agregat
        $totalKebutuhan = $anjabs->sum('formasi_pembulatan');
        $totalBezetting = $anjabs->sum('bezetting');
        $totalKurang = $anjabs->filter(fn($a) => $a->selisih_formasi < 0)->sum(fn($a) => abs($a->selisih_formasi));
        $totalLebih  = $anjabs->filter(fn($a) => $a->selisih_formasi > 0)->sum('selisih_formasi');

        $unitKerjas = UnitKerja::orderBy('urutan')->get();

        return view('abk.index', compact('anjabs', 'unitKerjas', 'totalKebutuhan', 'totalBezetting', 'totalKurang', 'totalLebih'));
    }

    /**
     * Halaman Kelola Butir Tugas ABK untuk 1 Jabatan
     */
    public function edit(AnalisisJabatan $anjab)
    {
        $anjab->load(['jabatan', 'unitKerja', 'uraianTugas']);
        return view('abk.edit', compact('anjab'));
    }

    /**
     * Tambah Butir Tugas Baru
     */
    public function storeTugas(Request $request, AnalisisJabatan $anjab)
    {
        $validated = $request->validate([
            'uraian_tugas'       => 'required|string',
            'satuan_hasil'       => 'required|string|max:100',
            'norma_waktu_menit'  => 'required|numeric|min:1',
            'volume_1_tahun'     => 'required|numeric|min:0.1',
            'keterangan'         => 'nullable|string',
        ]);

        $maxUrutan = (int) $anjab->uraianTugas()->max('urutan');
        $validated['urutan'] = $maxUrutan + 1;
        $validated['analisis_jabatan_id'] = $anjab->id;

        AbkUraianTugas::create($validated);

        return redirect()->route('abk.edit', $anjab)->with('success', 'Butir tugas berhasil ditambahkan ke beban kerja.');
    }

    /**
     * Update Butir Tugas
     */
    public function updateTugas(Request $request, AbkUraianTugas $tugas)
    {
        $validated = $request->validate([
            'uraian_tugas'       => 'required|string',
            'satuan_hasil'       => 'required|string|max:100',
            'norma_waktu_menit'  => 'required|numeric|min:1',
            'volume_1_tahun'     => 'required|numeric|min:0.1',
            'keterangan'         => 'nullable|string',
        ]);

        $tugas->update($validated);

        return redirect()->route('abk.edit', $tugas->analisis_jabatan_id)->with('success', 'Butir tugas berhasil diperbarui.');
    }

    /**
     * Hapus Butir Tugas
     */
    public function destroyTugas(AbkUraianTugas $tugas)
    {
        $anjabId = $tugas->analisis_jabatan_id;
        $tugas->delete();

        return redirect()->route('abk.edit', $anjabId)->with('success', 'Butir tugas berhasil dihapus.');
    }

    /**
     * Cetak Rekapitulasi Formasi ABK Standar BKN/KemenPAN-RB
     */
    public function printRekap(Request $request)
    {
        $query = AnalisisJabatan::with(['jabatan', 'unitKerja', 'uraianTugas']);

        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        $anjabs = $query->get();
        $selectedUnit = $request->filled('unit_kerja_id') ? UnitKerja::find($request->unit_kerja_id) : null;

        return view('abk.print_rekap', compact('anjabs', 'selectedUnit'));
    }
}
