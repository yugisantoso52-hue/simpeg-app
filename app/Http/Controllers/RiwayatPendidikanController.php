<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\RiwayatPendidikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiwayatPendidikanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $data = RiwayatPendidikan::with('pegawai')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('pegawai', function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
                })
                ->orWhere('institusi', 'like', "%{$search}%")
                ->orWhere('jurusan', 'like', "%{$search}%")
                ->orWhere('jenjang', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('riwayat-pendidikan.index', compact('data'));
    }

    public function create()
    {
        $pegawai = Pegawai::orderBy('nama')->get();

        return view('riwayat-pendidikan.create', compact('pegawai'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id'  => 'required',
            'jenjang'     => 'required',
            'institusi'   => 'required',
            'fakultas'    => 'nullable',
            'jurusan'     => 'nullable',
            'tahun_lulus' => 'nullable|numeric',
            'ijazah'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::transaction(function () use ($request) {
            $file = null;

            if ($request->hasFile('ijazah')) {
                $file = $request->file('ijazah')->store('ijazah', 'public');
            }

            RiwayatPendidikan::create([
                'pegawai_id'  => $request->pegawai_id,
                'jenjang'     => $request->jenjang,
                'institusi'   => $request->institusi,
                'fakultas'    => $request->fakultas,
                'jurusan'     => $request->jurusan,
                'tahun_lulus' => $request->tahun_lulus,
                'ijazah'      => $file
            ]);

            // Sync ke tabel utama Pegawai
            Pegawai::where('id', $request->pegawai_id)->update(['pendidikan' => $request->jenjang]);
        });

        return redirect()
            ->route('riwayat-pendidikan.index')
            ->with('success', 'Data berhasil disimpan');
    }

    public function edit($id)
    {
        $data = RiwayatPendidikan::findOrFail($id);
        $pegawai = Pegawai::orderBy('nama')->get();

        return view('riwayat-pendidikan.edit', compact('data', 'pegawai'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pegawai_id'  => 'required',
            'jenjang'     => 'required',
            'institusi'   => 'required',
            'fakultas'    => 'nullable',
            'jurusan'     => 'nullable',
            'tahun_lulus' => 'nullable|numeric',
            'ijazah'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::transaction(function () use ($request, $id) {
            $data = RiwayatPendidikan::findOrFail($id);
            $file = $data->ijazah;

            if ($request->hasFile('ijazah')) {
                $file = $request->file('ijazah')->store('ijazah', 'public');
            }

            // Pertahankan data lama jika input nullable dikirim kosong / null
            $updateData = [
                'pegawai_id'  => $request->pegawai_id,
                'jenjang'     => $request->jenjang,
                'institusi'   => $request->institusi,
                'fakultas'    => $request->filled('fakultas') ? $request->fakultas : $data->fakultas,
                'jurusan'     => $request->filled('jurusan') ? $request->jurusan : $data->jurusan,
                'tahun_lulus' => $request->filled('tahun_lulus') ? $request->tahun_lulus : $data->tahun_lulus,
                'ijazah'      => $file,
            ];

            $data->update($updateData);

            // Sync ke tabel utama Pegawai
            Pegawai::where('id', $request->pegawai_id)->update(['pendidikan' => $request->jenjang]);
        });

        return redirect()
            ->route('riwayat-pendidikan.index')
            ->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        RiwayatPendidikan::destroy($id);

        return redirect()
            ->route('riwayat-pendidikan.index')
            ->with('success', 'Data berhasil dihapus');
    }
}