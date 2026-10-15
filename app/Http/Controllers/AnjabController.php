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
        
        $verifyCode = $anjab->verification_token;
        $verifyUrl = $verifyCode ? route('verify.document', ['code' => $verifyCode]) : null;
        $qrCodeDataUri = $verifyUrl ? \App\Services\QrCodeService::generateDataUri($verifyUrl) : null;

        return view('anjab.print', compact('anjab', 'verifyCode', 'verifyUrl', 'qrCodeDataUri'));
    }

    /**
     * Verifikasi & Validasi Digital Dokumen Anjab oleh Pimpinan / Admin
     */
    public function verifikasi(Request $request, AnalisisJabatan $anjab)
    {
        $user = auth()->user();
        if (!$user->canAccessExecutiveKepegawaianMenus()) {
            abort(403, 'Akses terbatas untuk pimpinan dan pengelola kepegawaian.');
        }

        $action = $request->input('action', 'setujui');
        $namaJabatan = $anjab->jabatan->nama_jabatan ?? 'Jabatan';

        if ($action === 'setujui') {
            $anjab->update(['status' => 'disetujui']);
            $msg = "Dokumen Analisis Jabatan {$namaJabatan} berhasil diverifikasi & disahkan secara digital oleh Dekanat FKP UNRI.";
        } else {
            $anjab->update(['status' => 'draft']);
            $msg = "Status Dokumen Anjab {$namaJabatan} berhasil dikembalikan ke status Draft.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Halaman Evaluasi Jabatan FES (Factor Evaluation System) PermenPAN-RB No. 34/2011 & 39/2013
     */
    public function evaluasiJabatan(AnalisisJabatan $anjab)
    {
        $anjab->load(['jabatan', 'unitKerja', 'uraianTugas']);
        return view('anjab.evaluasi_jabatan', compact('anjab'));
    }

    /**
     * Simpan Hasil Evaluasi Jabatan & Perbarui Kelas Jabatan
     */
    public function updateEvaluasiJabatan(Request $request, AnalisisJabatan $anjab)
    {
        $validated = $request->validate([
            'faktor_1' => 'required|integer',
            'faktor_2' => 'required|integer',
            'faktor_3' => 'required|integer',
            'faktor_4' => 'required|integer',
            'faktor_5' => 'required|integer',
            'faktor_6' => 'required|integer',
            'faktor_7' => 'required|integer',
            'faktor_8' => 'required|integer',
            'faktor_9' => 'required|integer',
            'catatan_evaluasi' => 'nullable|string|max:1000',
        ]);

        $totalNilai = $validated['faktor_1'] + $validated['faktor_2'] + $validated['faktor_3']
                    + $validated['faktor_4'] + $validated['faktor_5'] + $validated['faktor_6']
                    + $validated['faktor_7'] + $validated['faktor_8'] + $validated['faktor_9'];

        $kelasJabatan = self::konversiNilaiKeKelas($totalNilai);

        $anjab->update([
            'kelas_jabatan' => $kelasJabatan,
        ]);

        if ($anjab->jabatan_id) {
            Jabatan::where('id', $anjab->jabatan_id)->update([
                'kelas_jabatan' => $kelasJabatan
            ]);
        }

        return redirect()->route('anjab.show', $anjab)
            ->with('success', "Evaluasi jabatan berhasil disimpan! Total Nilai Jabatan: {$totalNilai} -> Ditetapkan Kelas Jabatan: Grade {$kelasJabatan}.");
    }

    /**
     * Konversi Total Nilai FES ke Kelas Jabatan 1 s/d 17 (Standar BKN & PermenPAN-RB)
     */
    public static function konversiNilaiKeKelas(int $nilai): int
    {
        if ($nilai <= 240) return 1;
        if ($nilai <= 300) return 2;
        if ($nilai <= 370) return 3;
        if ($nilai <= 450) return 4;
        if ($nilai <= 650) return 5;
        if ($nilai <= 850) return 6;
        if ($nilai <= 1100) return 7;
        if ($nilai <= 1350) return 8;
        if ($nilai <= 1600) return 9;
        if ($nilai <= 1850) return 10;
        if ($nilai <= 2100) return 11;
        if ($nilai <= 2350) return 12;
        if ($nilai <= 2750) return 13;
        if ($nilai <= 3150) return 14;
        if ($nilai <= 3600) return 15;
        if ($nilai <= 4050) return 16;
        return 17;
    }
}

