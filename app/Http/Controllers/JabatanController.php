<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Jabatan::query();

        if ($request->search) {
            $query->where('nama_jabatan', 'like', '%'.$request->search.'%')
                  ->orWhere('keterangan', 'like', '%'.$request->search.'%');
        }

        $jabatan = $query->latest()->paginate(10);

        return view('jabatan.index', compact('jabatan'));
    }

    public function create()
    {
        return view('jabatan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_jabatan' => 'nullable|string|max:50|unique:jabatan,kode_jabatan',
            'nama_jabatan' => 'required|string|max:150',
            'keterangan'   => 'nullable|string|max:255',
        ]);

        if (empty($validated['kode_jabatan'])) {
            $validated['kode_jabatan'] = 'JAB-' . strtoupper(substr(uniqid(), -6));
        }

        Jabatan::create($validated);

        return redirect()
            ->route('jabatan.index')
            ->with('success','Data berhasil disimpan');
    }

    public function edit($id)
    {
        $jabatan = Jabatan::findOrFail($id);

        return view('jabatan.edit', compact('jabatan'));
    }

    public function update(Request $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);

        $validated = $request->validate([
            'kode_jabatan' => [
                'nullable',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('jabatan', 'kode_jabatan')->ignore($id),
            ],
            'nama_jabatan' => 'required|string|max:150',
            'keterangan'   => 'nullable|string|max:255',
        ]);

        // Jika kode_jabatan dikosongkan, jangan ubah kode_jabatan yang sudah ada
        if (empty($validated['kode_jabatan'])) {
            unset($validated['kode_jabatan']);
        }

        try {
            $jabatan->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani error duplikat kode_jabatan
            if ($e->getCode() == 23000) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['kode_jabatan' => 'Kode jabatan sudah digunakan oleh jabatan lain. Gunakan kode yang berbeda atau kosongkan field ini.']);
            }
            throw $e;
        }

        return redirect()
            ->route('jabatan.index')
            ->with('success','Data jabatan berhasil diupdate.');
    }


    public function destroy($id)
    {
        try {
            $model = Jabatan::findOrFail($id);
            $model->delete();

            return redirect()
                ->route('jabatan.index')
                ->with('success', 'Data Jabatan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('jabatan.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}