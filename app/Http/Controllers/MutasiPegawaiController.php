<?php

namespace App\Http\Controllers;

use App\Models\MutasiPegawai;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Http\Requests\StoreMutasiRequest;
use App\Http\Requests\UpdateMutasiRequest; // Menggunakan UpdateMutasiRequest baru
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MutasiPegawaiController extends Controller
{
    public function index()
    {
        $data = MutasiPegawai::with([
            'pegawai',
            'unitLama',
            'unitBaru',
            'jabatanLama',
            'jabatanBaru'
        ])
        ->latest()
        ->paginate(10);

        return view('mutasi-pegawai.index', compact('data'));
    }

    public function create()
    {
        $pegawai = Pegawai::orderByRaw("CASE WHEN status_pegawai = 'Aktif' THEN 0 ELSE 1 END")->orderBy('nama')->get();
        $unitKerja = UnitKerja::orderBy('nama_unit')->get();
        $jabatan = Jabatan::orderBy('nama_jabatan')->get();

        return view('mutasi-pegawai.create', compact('pegawai', 'unitKerja', 'jabatan'));
    }

    public function store(StoreMutasiRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                
                $file = null;
                if ($request->hasFile('file_sk')) {
                    $file = $request->file('file_sk')->store('mutasi-pegawai', 'local');
                }

                // 1. Simpan Log Riwayat Mutasi
                MutasiPegawai::create([
                    'pegawai_id'       => $request->pegawai_id,
                    'unit_lama_id'     => $request->unit_lama_id,
                    'unit_baru_id'     => $request->unit_baru_id,
                    'jabatan_lama_id'  => $request->jabatan_lama_id,
                    'jabatan_baru_id'  => $request->jabatan_baru_id,
                    'tmt'              => $request->tmt,
                    'nomor_sk'         => $request->nomor_sk,
                    'file_sk'          => $file,
                    'keterangan'       => $request->keterangan,
                ]);

                // 2. Update status terkini di master tabel Pegawai
                $pegawai = Pegawai::findOrFail($request->pegawai_id);
                $pegawai->update([
                    'unit_kerja_id' => $request->unit_baru_id,
                    'jabatan_id'    => $request->jabatan_baru_id,
                ]);

                if ($request->hasFile('file_sk')) {
                    app(\App\Services\GoogleDriveGasService::class)->uploadDokumen($pegawai, $request->file('file_sk'), 'SK_MUTASI', '02_RIWAYAT_SK', 'Riwayat Mutasi Pegawai');
                }
            });

            return redirect()
                ->route('mutasi-pegawai.index')
                ->with('success', 'Mutasi pegawai berhasil disimpan dan data pegawai disinkronkan.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memproses mutasi: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        return redirect()->route('mutasi-pegawai.index');
    }

    public function edit(string $id)
    {
        $mutasi = MutasiPegawai::findOrFail($id);
        $pegawai = Pegawai::orderByRaw("CASE WHEN status_pegawai = 'Aktif' THEN 0 ELSE 1 END")->orderBy('nama')->get();
        $unitKerja = UnitKerja::orderBy('nama_unit')->get();
        $jabatan = Jabatan::orderBy('nama_jabatan')->get();

        return view('mutasi-pegawai.edit', compact('mutasi', 'pegawai', 'unitKerja', 'jabatan'));
    }

    public function update(UpdateMutasiRequest $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $mutasi = MutasiPegawai::findOrFail($id);
                $file = $mutasi->file_sk;

                if ($request->hasFile('file_sk')) {
                    if ($mutasi->file_sk) {
                        if (Storage::disk('local')->exists($mutasi->file_sk)) {
                            Storage::disk('local')->delete($mutasi->file_sk);
                        } else {
                            Storage::disk('public')->delete($mutasi->file_sk);
                        }
                    }
                    $file = $request->file('file_sk')->store('mutasi-pegawai', 'local');
                }

                // Pertahankan data nullable existing jika input kosong
                $mutasi->update([
                    'pegawai_id'       => $request->pegawai_id,
                    'unit_lama_id'     => $request->unit_lama_id,
                    'unit_baru_id'     => $request->unit_baru_id,
                    'jabatan_lama_id'  => $request->jabatan_lama_id,
                    'jabatan_baru_id'  => $request->jabatan_baru_id,
                    'tmt'              => $request->tmt,
                    'nomor_sk'         => $request->filled('nomor_sk') ? $request->nomor_sk : $mutasi->nomor_sk,
                    'file_sk'          => $file,
                    'keterangan'       => $request->filled('keterangan') ? $request->keterangan : $mutasi->keterangan,
                ]);

                // Update kembali master tabel Pegawai dengan data terbaru
                $pegawai = Pegawai::findOrFail($request->pegawai_id);
                $pegawai->update([
                    'unit_kerja_id' => $request->unit_baru_id,
                    'jabatan_id'    => $request->jabatan_baru_id,
                ]);

                if ($request->hasFile('file_sk')) {
                    app(\App\Services\GoogleDriveGasService::class)->uploadDokumen($pegawai, $request->file('file_sk'), 'SK_MUTASI', '02_RIWAYAT_SK', 'Update Riwayat Mutasi Pegawai');
                }
            });

            return redirect()
                ->route('mutasi-pegawai.index')
                ->with('success', 'Data mutasi dan posisi pegawai berhasil diupdate.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal mengubah data mutasi: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $mutasi = MutasiPegawai::findOrFail($id);

                // Kembalikan posisi unit kerja dan jabatan pegawai ke "Unit Lama" & "Jabatan Lama" sebelum dihapus
                $pegawai = Pegawai::findOrFail($mutasi->pegawai_id);
                $pegawai->update([
                    'unit_kerja_id' => $mutasi->unit_lama_id,
                    'jabatan_id'    => $mutasi->jabatan_lama_id,
                ]);

                if ($mutasi->file_sk) {
                    Storage::disk('public')->delete($mutasi->file_sk);
                }

                $mutasi->delete();
            });

            return redirect()
                ->route('mutasi-pegawai.index')
                ->with('success', 'Data mutasi dihapus, posisi pegawai dikembalikan ke unit asal.');

        } catch (\Exception $e) {
            return redirect()
                ->route('mutasi-pegawai.index')
                ->with('error', 'Gagal menghapus data mutasi: ' . $e->getMessage());
        }
    }

    public function getPegawai($id)
    {
        $pegawai = Pegawai::with([
            'unitKerja',
            'jabatan'
        ])->findOrFail($id);

        return response()->json([
            'unit_id'    => $pegawai->unit_kerja_id,
            'unit'       => $pegawai->unitKerja->nama_unit ?? '',
            'jabatan_id' => $pegawai->jabatan_id,
            'jabatan'    => $pegawai->jabatan->nama_jabatan ?? ''
        ]);
    }
}