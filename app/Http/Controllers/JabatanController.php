<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JabatanController extends Controller
{
    public const KELOMPOK_OPTIONS = [
        'Pimpinan Fakultas',
        'Badan Pertimbangan (Senat)',
        'Penjaminan Mutu (SPMF)',
        'Pimpinan Jurusan',
        'Koordinator Program Studi',
        'Kelompok Jabatan Fungsional Dosen (KJFD)',
        'Unit-Unit Fungsional',
        'Laboratorium Keperawatan',
        'Tenaga Kependidikan & Tata Usaha',
        'Jabatan Fungsional Dosen',
        'Fungsional Tertentu & Pelaksana',
        'Pelaksana & Administrasi',
        'Organisasi Mahasiswa & Alumni',
    ];

    public function index(Request $request)
    {
        $query = Jabatan::with('unitKerja')->withCount('pegawai');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_jabatan', 'like', "%{$search}%")
                  ->orWhere('kode_jabatan', 'like', "%{$search}%")
                  ->orWhere('kelompok_jabatan', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelompok_jabatan')) {
            $query->where('kelompok_jabatan', $request->kelompok_jabatan);
        }

        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        $jabatan = $query->orderByRaw("CASE 
            WHEN kelompok_jabatan = 'Pimpinan Fakultas' THEN 1
            WHEN kelompok_jabatan = 'Badan Pertimbangan (Senat)' THEN 2
            WHEN kelompok_jabatan = 'Penjaminan Mutu (SPMF)' THEN 3
            WHEN kelompok_jabatan = 'Pimpinan Jurusan' THEN 4
            WHEN kelompok_jabatan = 'Koordinator Program Studi' THEN 5
            WHEN kelompok_jabatan = 'Kelompok Jabatan Fungsional Dosen (KJFD)' THEN 6
            WHEN kelompok_jabatan = 'Unit-Unit Fungsional' THEN 7
            WHEN kelompok_jabatan = 'Laboratorium Keperawatan' THEN 8
            WHEN kelompok_jabatan = 'Tenaga Kependidikan & Tata Usaha' THEN 9
            WHEN kelompok_jabatan = 'Jabatan Fungsional Dosen' THEN 10
            WHEN kelompok_jabatan = 'Fungsional Tertentu & Pelaksana' THEN 11
            WHEN kelompok_jabatan = 'Pelaksana & Administrasi' THEN 12
            ELSE 13 END, kelas_jabatan DESC, nama_jabatan ASC")
            ->paginate(50)
            ->withQueryString();

        $unitKerjas = UnitKerja::orderBy('urutan')->orderBy('nama_unit')->get();
        $kelompokOptions = self::KELOMPOK_OPTIONS;

        return view('jabatan.index', compact('jabatan', 'unitKerjas', 'kelompokOptions'));
    }

    public function create()
    {
        $unitKerjas = UnitKerja::orderBy('urutan')->orderBy('nama_unit')->get();
        $kelompokOptions = self::KELOMPOK_OPTIONS;

        return view('jabatan.create', compact('unitKerjas', 'kelompokOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_jabatan'     => 'nullable|string|max:50|unique:jabatan,kode_jabatan',
            'nama_jabatan'     => 'required|string|max:150',
            'unit_kerja_id'    => 'nullable|exists:unit_kerja,id',
            'kelas_jabatan'    => 'nullable|integer|min:1|max:17',
            'kelompok_jabatan' => 'nullable|string|max:100',
            'ikhtisar_jabatan' => 'nullable|string',
            'keterangan'       => 'nullable|string|max:255',
        ]);

        if (empty($validated['kode_jabatan'])) {
            $validated['kode_jabatan'] = 'JAB-' . strtoupper(substr(uniqid(), -6));
        }

        Jabatan::create($validated);

        return redirect()
            ->route('jabatan.index')
            ->with('success', 'Data Jabatan berhasil ditambahkan ke Master Data.');
    }

    public function edit($id)
    {
        $jabatan = Jabatan::findOrFail($id);
        $unitKerjas = UnitKerja::orderBy('urutan')->orderBy('nama_unit')->get();
        $kelompokOptions = self::KELOMPOK_OPTIONS;

        return view('jabatan.edit', compact('jabatan', 'unitKerjas', 'kelompokOptions'));
    }

    public function update(Request $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);

        $validated = $request->validate([
            'kode_jabatan'     => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('jabatan', 'kode_jabatan')->ignore($id),
            ],
            'nama_jabatan'     => 'required|string|max:150',
            'unit_kerja_id'    => 'nullable|exists:unit_kerja,id',
            'kelas_jabatan'    => 'nullable|integer|min:1|max:17',
            'kelompok_jabatan' => 'nullable|string|max:100',
            'ikhtisar_jabatan' => 'nullable|string',
            'keterangan'       => 'nullable|string|max:255',
        ]);

        if (empty($validated['kode_jabatan'])) {
            unset($validated['kode_jabatan']);
        }

        try {
            $jabatan->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['kode_jabatan' => 'Kode jabatan sudah digunakan oleh jabatan lain.']);
            }
            throw $e;
        }

        return redirect()
            ->route('jabatan.index')
            ->with('success', 'Data Master Jabatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $model = Jabatan::findOrFail($id);
            $model->delete();

            return redirect()
                ->route('jabatan.index')
                ->with('success', 'Data jabatan berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()
                ->route('jabatan.index')
                ->with('error', 'Gagal menghapus! Jabatan ini sedang digunakan oleh data pegawai atau dokumen Anjab.');
        }
    }
}