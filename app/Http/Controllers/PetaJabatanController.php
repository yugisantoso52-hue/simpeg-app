<?php

namespace App\Http\Controllers;

use App\Models\AnalisisJabatan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class PetaJabatanController extends Controller
{
    /**
     * Tampilan Peta Jabatan Interaktif Berbasis Struktur Organisasi FKP UNRI
     */
    public function index(Request $request)
    {
        // Ambil struktur unit kerja dengan hierarki
        $unitKerjas = UnitKerja::with([
            'children.children',
            'jabatan.analisisJabatan.uraianTugas',
            'jabatan.pegawai' => function ($q) {
                $q->where('status_pegawai', 'Aktif')
                  ->select('id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'jabatan_id', 'unit_kerja_id', 'foto');
            }
        ])->whereNull('parent_id')
          ->orderBy('urutan')
          ->get();

        // Data ringkasan bezetting & kebutuhan
        $allAnjabs = AnalisisJabatan::with('uraianTugas')->get();
        $totalKebutuhan = $allAnjabs->sum('formasi_pembulatan');
        $totalPegawaiAktif = Pegawai::where('status_pegawai', 'Aktif')->count();
        $totalJabatan = Jabatan::count();
        $totalUnitKerja = UnitKerja::count();

        return view('anjab.peta_jabatan', compact(
            'unitKerjas',
            'totalKebutuhan',
            'totalPegawaiAktif',
            'totalJabatan',
            'totalUnitKerja'
        ));
    }

    /**
     * Endpoint JSON data detail jabatan dan pegawai aktif untuk Modal / Pop-up
     */
    public function getJabatanDetail(Jabatan $jabatan)
    {
        $jabatan->load([
            'unitKerja',
            'analisisJabatan.uraianTugas',
            'pegawai' => function ($q) {
                $q->where('status_pegawai', 'Aktif')
                  ->select('id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'jabatan_id', 'unit_kerja_id', 'no_hp', 'email', 'foto');
            }
        ]);

        $anjab = $jabatan->analisisJabatan;

        return response()->json([
            'success'          => true,
            'jabatan'          => $jabatan,
            'bezetting'        => $jabatan->pegawai->count(),
            'kebutuhan'        => $anjab ? $anjab->formasi_pembulatan : 1,
            'status_formasi'   => $anjab ? $anjab->status_formasi : 'Belum Ditentukan',
            'status_color'     => $anjab ? $anjab->status_color : 'gray',
            'total_jam_beban'  => $anjab ? $anjab->total_jam_beban : 0,
            'pegawai_list'     => $jabatan->pegawai,
            'ikhtisar'         => $anjab->ikhtisar_jabatan ?? $jabatan->ikhtisar_jabatan ?? '-',
            'kualifikasi'      => $anjab->kualifikasi_pendidikan ?? '-',
        ]);
    }
}
