<?php

namespace App\Http\Controllers;

use App\Models\JenisJabatan;
use Illuminate\Http\Request;

class JenisJabatanController extends Controller
{
    /**
     * Tampilkan daftar Master Jenis Jabatan.
     */
    public function index(Request $request)
    {
        $query = JenisJabatan::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_jenis_jabatan', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $jenisJabatan = $query->orderBy('nama_jenis_jabatan', 'asc')->paginate(10);

        return view('jenis-jabatan.index', compact('jenisJabatan'));
    }

    /**
     * Form tambah Jenis Jabatan.
     */
    public function create()
    {
        return view('jenis-jabatan.create');
    }

    /**
     * Simpan data Jenis Jabatan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jenis_jabatan' => 'required|string|max:100|unique:jenis_jabatan,nama_jenis_jabatan',
            'keterangan'         => 'nullable|string|max:255',
        ], [
            'nama_jenis_jabatan.required' => 'Nama Jenis Jabatan wajib diisi.',
            'nama_jenis_jabatan.unique'   => 'Nama Jenis Jabatan ini sudah terdaftar.',
            'nama_jenis_jabatan.max'      => 'Nama Jenis Jabatan maksimal 100 karakter.',
        ]);

        $validated['nama_jenis_jabatan'] = strtoupper(trim($validated['nama_jenis_jabatan']));

        JenisJabatan::create($validated);

        return redirect()
            ->route('jenis-jabatan.index')
            ->with('success', 'Data Jenis Jabatan berhasil disimpan.');
    }

    /**
     * Form edit Jenis Jabatan.
     */
    public function edit($id)
    {
        $jenisJabatan = JenisJabatan::findOrFail($id);

        return view('jenis-jabatan.edit', compact('jenisJabatan'));
    }

    /**
     * Perbarui data Jenis Jabatan.
     */
    public function update(Request $request, $id)
    {
        $jenisJabatan = JenisJabatan::findOrFail($id);

        $validated = $request->validate([
            'nama_jenis_jabatan' => 'required|string|max:100|unique:jenis_jabatan,nama_jenis_jabatan,' . $id,
            'keterangan'         => 'nullable|string|max:255',
        ], [
            'nama_jenis_jabatan.required' => 'Nama Jenis Jabatan wajib diisi.',
            'nama_jenis_jabatan.unique'   => 'Nama Jenis Jabatan ini sudah terdaftar.',
            'nama_jenis_jabatan.max'      => 'Nama Jenis Jabatan maksimal 100 karakter.',
        ]);

        $validated['nama_jenis_jabatan'] = strtoupper(trim($validated['nama_jenis_jabatan']));

        $jenisJabatan->update($validated);

        return redirect()
            ->route('jenis-jabatan.index')
            ->with('success', 'Data Jenis Jabatan berhasil diperbarui.');
    }

    /**
     * Hapus data Jenis Jabatan.
     */
    public function destroy($id)
    {
        $jenisJabatan = JenisJabatan::findOrFail($id);

        $jenisJabatan->delete();

        return redirect()
            ->route('jenis-jabatan.index')
            ->with('success', 'Data Jenis Jabatan berhasil dihapus.');
    }
}
