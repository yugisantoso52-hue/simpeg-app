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
            $query->where('kode_jabatan', 'like', '%'.$request->search.'%')
                  ->orWhere('nama_jabatan', 'like', '%'.$request->search.'%');
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
        $request->validate([
            'kode_jabatan' => 'required|unique:jabatan',
            'nama_jabatan' => 'required'
        ]);

        Jabatan::create($request->all());

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

        $request->validate([
            'kode_jabatan' => 'required|unique:jabatan,kode_jabatan,'.$id,
            'nama_jabatan' => 'required'
        ]);

        $jabatan->update($request->all());

        return redirect()
            ->route('jabatan.index')
            ->with('success','Data berhasil diupdate');
    }

    public function destroy($id)
    {
        Jabatan::destroy($id);

        return redirect()
            ->route('jabatan.index')
            ->with('success','Data berhasil dihapus');
    }
}