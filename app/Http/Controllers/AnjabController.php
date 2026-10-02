<?php

namespace App\Http\Controllers;

use App\Models\AnalisisJabatan;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class AnjabController extends Controller
{
    public function index(Request $request)
    {
        $query = AnalisisJabatan::with(['jabatan', 'unitKerja', 'uraianTugas']);

        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('jabatan', function ($q) use ($search) {
                $q->where('nama_jabatan', 'like', "%{$search}%")
                  ->orWhere('kode_jabatan', 'like', "%{$search}%");
            })->orWhere('kode_anjab', 'like', "%{$search}%");
        }

        $all = $query->get()->sortByDesc('hierarchy_order')->values();
        $page = (int) $request->input('page', 1);
        $perPage = 15;
        $anjabs = new \Illuminate\Pagination\LengthAwarePaginator(
            $all->forPage($page, $perPage),
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $unitKerjas = UnitKerja::orderBy('urutan')->get();

        return view('anjab.index', compact('anjabs', 'unitKerjas'));
    }

    public function show(AnalisisJabatan $anjab)
    {
        $anjab->load(['jabatan', 'unitKerja', 'uraianTugas']);
        return view('anjab.show', compact('anjab'));
    }

    public function create()
    {
        $existingJabatanIds = AnalisisJabatan::pluck('jabatan_id')->toArray();
        $jabatans = Jabatan::whereNotIn('id', $existingJabatanIds)->orderBy('nama_jabatan')->get();
        if ($jabatans->isEmpty()) {
            $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        }
        $unitKerjas = UnitKerja::orderBy('urutan')->get();

        return view('anjab.create', compact('jabatans', 'unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jabatan_id'             => 'required|exists:jabatan,id',
            'unit_kerja_id'          => 'nullable|exists:unit_kerja,id',
            'kode_anjab'             => 'nullable|string|max:50',
            'ikhtisar_jabatan'       => 'nullable|string',
            'kualifikasi_pendidikan' => 'nullable|string',
            'kualifikasi_pelatihan'  => 'nullable|string',
            'kualifikasi_pengalaman' => 'nullable|string',
            'bahan_kerja'            => 'nullable|string',
            'perangkat_kerja'        => 'nullable|string',
            'tanggung_jawab'         => 'nullable|string',
            'wewenang'               => 'nullable|string',
            'korelasi_jabatan'       => 'nullable|string',
            'kondisi_lingkungan'     => 'nullable|string',
            'resiko_bahaya'          => 'nullable|string',
            'syarat_keterampilan'    => 'nullable|string',
            'syarat_bakat'           => 'nullable|string',
            'syarat_temperamen'      => 'nullable|string',
            'syarat_minat'           => 'nullable|string',
            'syarat_upaya_fisik'     => 'nullable|string',
            'kondisi_fisik'          => 'nullable|string',
            'prestasi_diharapkan'    => 'nullable|string',
            'kelas_jabatan'          => 'nullable|integer|min:1|max:17',
            'status'                 => 'required|in:draft,disetujui',
        ]);

        $anjab = AnalisisJabatan::create($validated);

        // Update kelas jabatan pada tabel jabatan jika diisi
        if ($request->filled('kelas_jabatan')) {
            Jabatan::where('id', $validated['jabatan_id'])->update([
                'kelas_jabatan' => $validated['kelas_jabatan']
            ]);
        }

        return redirect()->route('anjab.show', $anjab)->with('success', 'Dokumen Analisis Jabatan berhasil disimpan.');
    }

    public function edit(AnalisisJabatan $anjab)
    {
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        $unitKerjas = UnitKerja::orderBy('urutan')->get();

        return view('anjab.edit', compact('anjab', 'jabatans', 'unitKerjas'));
    }

    public function update(Request $request, AnalisisJabatan $anjab)
    {
        $validated = $request->validate([
            'unit_kerja_id'          => 'nullable|exists:unit_kerja,id',
            'kode_anjab'             => 'nullable|string|max:50',
            'ikhtisar_jabatan'       => 'nullable|string',
            'kualifikasi_pendidikan' => 'nullable|string',
            'kualifikasi_pelatihan'  => 'nullable|string',
            'kualifikasi_pengalaman' => 'nullable|string',
            'bahan_kerja'            => 'nullable|string',
            'perangkat_kerja'        => 'nullable|string',
            'tanggung_jawab'         => 'nullable|string',
            'wewenang'               => 'nullable|string',
            'korelasi_jabatan'       => 'nullable|string',
            'kondisi_lingkungan'     => 'nullable|string',
            'resiko_bahaya'          => 'nullable|string',
            'syarat_keterampilan'    => 'nullable|string',
            'syarat_bakat'           => 'nullable|string',
            'syarat_temperamen'      => 'nullable|string',
            'syarat_minat'           => 'nullable|string',
            'syarat_upaya_fisik'     => 'nullable|string',
            'kondisi_fisik'          => 'nullable|string',
            'prestasi_diharapkan'    => 'nullable|string',
            'kelas_jabatan'          => 'nullable|integer|min:1|max:17',
            'status'                 => 'required|in:draft,disetujui',
        ]);

        $anjab->update($validated);

        if ($request->filled('kelas_jabatan')) {
            Jabatan::where('id', $anjab->jabatan_id)->update([
                'kelas_jabatan' => $validated['kelas_jabatan']
            ]);
        }

        return redirect()->route('anjab.show', $anjab)->with('success', 'Dokumen Analisis Jabatan berhasil diperbarui.');
    }

    public function destroy(AnalisisJabatan $anjab)
    {
        $anjab->delete();
        return redirect()->route('anjab.index')->with('success', 'Dokumen Analisis Jabatan berhasil dihapus.');
    }

    public function print(AnalisisJabatan $anjab)
    {
        $anjab->load(['jabatan', 'unitKerja', 'uraianTugas']);
        return view('anjab.print', compact('anjab'));
    }
}
