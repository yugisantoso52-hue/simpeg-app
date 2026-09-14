<?php

namespace App\Http\Controllers;

use App\Models\Golongan;
use Illuminate\Http\Request;

class GolonganController extends Controller
{
    public function index(Request $request)
    {
        $query = Golongan::query();

        if ($request->search) {
            $query->where('nama_golongan', 'like', '%'.$request->search.'%')
                  ->orWhere('nama_pangkat', 'like', '%'.$request->search.'%')
                  ->orWhere('keterangan', 'like', '%'.$request->search.'%');
        }

        $golongan = $query->latest()->paginate(10);

        return view('golongan.index', compact('golongan'));
    }

    public function create()
    {
        return view('golongan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_golongan' => 'required|unique:golongan,nama_golongan',
            'nama_pangkat' => 'required',
            'keterangan' => 'nullable|max:255'
        ]);

        Golongan::create([
            'nama_golongan' => $request->nama_golongan,
            'nama_pangkat' => $request->nama_pangkat,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('golongan.index')
            ->with('success', 'Data berhasil disimpan');
    }

    public function edit($id)
    {
        $golongan = Golongan::findOrFail($id);

        return view('golongan.edit', compact('golongan'));
    }

    public function update(Request $request, $id)
    {
        $golongan = Golongan::findOrFail($id);

        $request->validate([
            'nama_golongan' => 'required|unique:golongan,nama_golongan,'.$id,
            'nama_pangkat' => 'required',
            'keterangan' => 'nullable|max:255'
        ]);

        $golongan->update([
            'nama_golongan' => $request->nama_golongan,
            'nama_pangkat' => $request->nama_pangkat,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('golongan.index')
            ->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        try {
            $model = Golongan::findOrFail($id);
            $model->delete();

            return redirect()
                ->route('golongan.index')
                ->with('success', 'Data Golongan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('golongan.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}