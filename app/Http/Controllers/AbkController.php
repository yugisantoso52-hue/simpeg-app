<?php

namespace App\Http\Controllers;

use App\Models\AnalisisJabatan;
use App\Models\AbkUraianTugas;
use App\Models\UnitKerja;
use App\Models\Pegawai;
use Carbon\Carbon;
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

        $anjabs = $query->get()->sortByDesc('hierarchy_order')->values();

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

        $anjabs = $query->get()->sortByDesc('hierarchy_order')->values();
        $selectedUnit = $request->filled('unit_kerja_id') ? UnitKerja::find($request->unit_kerja_id) : null;

        return view('abk.print_rekap', compact('anjabs', 'selectedUnit'));
    }

    /**
     * Simulasi & Usulan Formasi CASN / PPPK Terpadu (E-Formasi & Radar Pensiun BUP)
     */
    public function simulasiFormasi(Request $request)
    {
        $query = AnalisisJabatan::with(['jabatan.pegawai' => function ($q) {
            $q->where('status_pegawai', 'Aktif')->with(['golongan']);
        }, 'unitKerja', 'uraianTugas']);

        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        $anjabs = $query->get()->sortByDesc('hierarchy_order')->values();
        $now = Carbon::now();

        // Hitung proyeksi pensiun per jabatan & simulasi usulan formasi
        $simulationData = $anjabs->map(function ($anjab) use ($now) {
            $pegawais = $anjab->jabatan?->pegawai ?? collect();
            
            $pensiun1Thn = 0;
            $pensiun3Thn = 0;
            $pensiun5Thn = 0;
            $pegawaiPensiunList = [];

            foreach ($pegawais as $peg) {
                // Tentukan Batas Usia Pensiun (BUP)
                $bup = 58; // Default Tendik Pelaksana
                $namaJabatan = strtolower($peg->jabatan?->nama_jabatan ?? '');
                $jenisJabatan = strtolower($peg->jenis_pegawai ?? '');

                if (str_contains($namaJabatan, 'guru besar') || str_contains($namaJabatan, 'profesor')) {
                    $bup = 70;
                } elseif (str_contains($namaJabatan, 'dosen') || str_contains($namaJabatan, 'lektor') || str_contains($namaJabatan, 'asisten ahli') || $jenisJabatan === 'dosen') {
                    $bup = 65;
                } elseif (str_contains($namaJabatan, 'kepala bagian') || str_contains($namaJabatan, 'kabag') || str_contains($namaJabatan, 'fungsional madya')) {
                    $bup = 60;
                }

                if ($peg->tanggal_lahir) {
                    $tglLahir = Carbon::parse($peg->tanggal_lahir);
                    $tglPensiun = $tglLahir->copy()->addYears($bup);
                    $sisaBulan = $now->diffInMonths($tglPensiun, false);

                    if ($sisaBulan <= 12 && $sisaBulan >= -6) {
                        $pensiun1Thn++;
                        $pegawaiPensiunList[] = [
                            'pegawai' => $peg,
                            'sisa' => '1 Tahun (' . max(0, $sisaBulan) . ' bln)',
                            'bup' => $bup,
                            'tgl_pensiun' => $tglPensiun->format('d/m/Y'),
                        ];
                    } elseif ($sisaBulan <= 36 && $sisaBulan > 12) {
                        $pensiun3Thn++;
                    } elseif ($sisaBulan <= 60 && $sisaBulan > 36) {
                        $pensiun5Thn++;
                    }
                }
            }

            $kebutuhan = $anjab->formasi_pembulatan;
            $bezetting = $anjab->bezetting;
            $selisih = $anjab->selisih_formasi; // negatif jika kurang

            // Hitung usulan formasi CASN
            $usulanPns = 0;
            $usulanPppk = 0;

            if ($selisih < 0) {
                $defisit = abs($selisih);
                $isDosen = str_contains(strtolower($anjab->jabatan?->nama_jabatan ?? ''), 'dosen') 
                        || str_contains(strtolower($anjab->jabatan?->nama_jabatan ?? ''), 'lektor');

                if ($isDosen) {
                    $usulanPns = (int) ceil($defisit * 0.6);
                    $usulanPppk = $defisit - $usulanPns;
                } else {
                    $usulanPns = (int) floor($defisit * 0.3);
                    $usulanPppk = $defisit - $usulanPns;
                }
            } elseif ($pensiun1Thn > 0) {
                // Antisipasi kekosongan jabatan akibat BUP dalam 1 tahun ke depan
                $usulanPppk += $pensiun1Thn;
            }

            return [
                'anjab' => $anjab,
                'kebutuhan' => $kebutuhan,
                'bezetting' => $bezetting,
                'selisih' => $selisih,
                'pensiun_1_thn' => $pensiun1Thn,
                'pensiun_3_thn' => $pensiun3Thn,
                'pensiun_5_thn' => $pensiun5Thn,
                'pegawai_pensiun_list' => $pegawaiPensiunList,
                'usulan_pns' => $usulanPns,
                'usulan_pppk' => $usulanPppk,
                'total_usulan' => $usulanPns + $usulanPppk,
                'prioritas' => ($selisih < 0 || $pensiun1Thn > 0) ? ($selisih <= -2 ? 'Mendesak / Sangat Tinggi' : 'Tinggi') : 'Normal',
            ];
        });

        // Rekapitulasi Global Simulasi
        $totalKebutuhan = $simulationData->sum('kebutuhan');
        $totalBezetting = $simulationData->sum('bezetting');
        $totalDefisit = $simulationData->filter(fn($d) => $d['selisih'] < 0)->sum(fn($d) => abs($d['selisih']));
        $totalPensiun1Thn = $simulationData->sum('pensiun_1_thn');
        $totalPensiun3Thn = $simulationData->sum('pensiun_3_thn');
        $totalPensiun5Thn = $simulationData->sum('pensiun_5_thn');
        $totalUsulanPns = $simulationData->sum('usulan_pns');
        $totalUsulanPppk = $simulationData->sum('usulan_pppk');

        $unitKerjas = UnitKerja::orderBy('urutan')->get();

        return view('abk.simulasi_formasi', compact(
            'simulationData',
            'unitKerjas',
            'totalKebutuhan',
            'totalBezetting',
            'totalDefisit',
            'totalPensiun1Thn',
            'totalPensiun3Thn',
            'totalPensiun5Thn',
            'totalUsulanPns',
            'totalUsulanPppk'
        ));
    }
}
