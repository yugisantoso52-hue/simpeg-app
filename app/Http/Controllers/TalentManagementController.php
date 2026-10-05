<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Models\TalentMapping;
use App\Models\TalentAssessment;
use App\Models\SuccessionPlan;
use App\Services\TalentScoringService;
use App\Services\SuccessionMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TalentManagementController extends Controller
{
    protected TalentScoringService $scoringService;

    public function __construct(TalentScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Dashboard Interaktif Matriks 9-Kotak Talenta ASN (PermenPAN-RB No. 3/2020)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->canAccessTalentManagement()) {
            return redirect()->route('manajemen-talenta.my-talent')
                ->with('info', 'Anda dialihkan ke halaman Transparansi Profil Talenta Mandiri.');
        }

        $tahun = (int) ($request->input('tahun', date('Y')));
        $unitKerjaId = $request->filled('unit_kerja_id') ? (int) $request->input('unit_kerja_id') : null;

        // Cek apakah data tahun ini sudah ada snapshot-nya. Jika belum, hitung otomatis
        $exists = TalentMapping::where('tahun', $tahun)->exists();
        if (!$exists) {
            $this->scoringService->batchCalculate($tahun, $unitKerjaId);
        }

        $distribution = $this->scoringService->getNineBoxDistribution($tahun, $unitKerjaId);
        $unitKerjas = UnitKerja::orderBy('nama_unit')->get();

        // Opsi tahun evaluasi (5 tahun terakhir)
        $availableYears = range(date('Y'), date('Y') - 4);

        return view('talent.index', compact('distribution', 'tahun', 'unitKerjaId', 'unitKerjas', 'availableYears'));
    }

    /**
     * Rekap Data Tabel Talenta & Filter Detail
     */
    public function rekap(Request $request)
    {
        $user = Auth::user();
        if (!$user->canAccessTalentManagement()) {
            abort(403, 'Akses terbatas untuk pimpinan dan tim manajemen kepegawaian.');
        }

        $tahun = (int) ($request->input('tahun', date('Y')));
        $unitKerjaId = $request->input('unit_kerja_id');
        $kuadran = $request->input('kuadran');
        $search = $request->input('search');
        $onlySuksesi = $request->boolean('only_suksesi');

        $query = TalentMapping::with(['pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan'])
            ->where('tahun', $tahun);

        if ($unitKerjaId) {
            $query->where('unit_kerja_saat_ini_id', $unitKerjaId);
        }

        if ($kuadran) {
            $query->where('kuadran_box', $kuadran);
        }

        if ($onlySuksesi) {
            $query->where('is_suksesi_eligible', true);
        }

        if ($search) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $mappings = $query->orderBy('kuadran_box', 'desc')
                          ->orderBy('sumbu_kinerja_nilai', 'desc')
                          ->paginate(20)
                          ->withQueryString();

        $unitKerjas = UnitKerja::orderBy('nama_unit')->get();
        $availableYears = range(date('Y'), date('Y') - 4);

        return view('talent.rekap', compact('mappings', 'tahun', 'unitKerjaId', 'kuadran', 'search', 'onlySuksesi', 'unitKerjas', 'availableYears'));
    }

    /**
     * Hitung Ulang Skor Talenta secara Massal (Sync Batch)
     */
    public function calculate(Request $request)
    {
        $user = Auth::user();
        if (!$user->canManageTalentManagement()) {
            abort(403, 'Anda tidak memiliki hak akses untuk memicu sinkronisasi kalkulasi talenta.');
        }

        $tahun = (int) ($request->input('tahun', date('Y')));
        $unitKerjaId = $request->filled('unit_kerja_id') ? (int) $request->input('unit_kerja_id') : null;

        $result = $this->scoringService->batchCalculate($tahun, $unitKerjaId);

        return back()->with('success', "Kalkulasi talenta berhasil diperbarui untuk {$result['processed']} ASN pada tahun evaluasi {$tahun}.");
    }

    /**
     * Profil Detail Talenta & Breakdown Penilaian ASN
     */
    public function show(Pegawai $pegawai, Request $request)
    {
        $user = Auth::user();
        
        // Cek otorisasi: Pimpinan/Admin atau Pegawai yang bersangkutan
        if (!$user->canAccessTalentManagement() && $user->pegawai_id !== $pegawai->id) {
            abort(403, 'Anda tidak memiliki otorisasi untuk melihat profil talenta pegawai ini.');
        }

        $tahun = (int) ($request->input('tahun', date('Y')));

        // Pastikan mapping tahun ini terhitung
        $mapping = TalentMapping::where('pegawai_id', $pegawai->id)
            ->where('tahun', $tahun)
            ->first();

        if (!$mapping) {
            $mapping = $this->scoringService->calculatePegawaiTalent($pegawai, $tahun);
        }

        // Riwayat pemetaan tahun-tahun lainnya
        $historyMappings = TalentMapping::where('pegawai_id', $pegawai->id)
            ->orderBy('tahun', 'desc')
            ->get();

        // Riwayat Asesmen Kompetensi
        $assessments = $pegawai->talentAssessments()
            ->orderBy('tanggal_asesmen', 'desc')
            ->get();

        // Riwayat SKP & Diklat
        $skpRecords = $pegawai->riwayatSkp()->orderBy('tahun', 'desc')->take(3)->get();
        $diklatRecords = $pegawai->riwayatDiklat()->orderBy('tanggal_selesai', 'desc')->take(5)->get();

        return view('talent.show', compact('pegawai', 'mapping', 'tahun', 'historyMappings', 'assessments', 'skpRecords', 'diklatRecords'));
    }

    /**
     * Validasi Status oleh Komite Talenta
     */
    public function validateStatus(Pegawai $pegawai, Request $request)
    {
        $user = Auth::user();
        if (!$user->canManageTalentManagement()) {
            abort(403, 'Hanya Komite Talenta atau Admin yang dapat memvalidasi data talenta.');
        }

        $request->validate([
            'tahun'               => 'required|integer',
            'status_validasi'     => 'required|in:Draft,Ditinjau Komite,Ditetapkan PPK',
            'is_suksesi_eligible' => 'required|boolean',
            'catatan_komite'      => 'nullable|string|max:1000',
        ]);

        $mapping = TalentMapping::where('pegawai_id', $pegawai->id)
            ->where('tahun', $request->input('tahun'))
            ->firstOrFail();

        $mapping->update([
            'status_validasi'     => $request->input('status_validasi'),
            'is_suksesi_eligible' => $request->boolean('is_suksesi_eligible'),
            'catatan_komite'      => $request->input('catatan_komite'),
            'validated_by'        => $user->id,
            'validated_at'        => now(),
        ]);

        return back()->with('success', 'Status validasi Komite Talenta berhasil disimpan.');
    }

    /**
     * Transparansi Profil Talenta Mandiri untuk Pegawai yang Sedang Login
     */
    public function myTalent(Request $request)
    {
        $user = Auth::user();
        if (!$user->pegawai_id) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pegawai ASN.');
        }

        $pegawai = $user->pegawai;
        $tahun = (int) ($request->input('tahun', date('Y')));

        $mapping = TalentMapping::where('pegawai_id', $pegawai->id)
            ->where('tahun', $tahun)
            ->first();

        if (!$mapping) {
            $mapping = $this->scoringService->calculatePegawaiTalent($pegawai, $tahun);
        }

        $historyMappings = TalentMapping::where('pegawai_id', $pegawai->id)
            ->orderBy('tahun', 'desc')
            ->get();

        $assessments = $pegawai->talentAssessments()
            ->orderBy('tanggal_asesmen', 'desc')
            ->get();

        $skpRecords = $pegawai->riwayatSkp()->orderBy('tahun', 'desc')->take(3)->get();
        $diklatRecords = $pegawai->riwayatDiklat()->orderBy('tanggal_selesai', 'desc')->take(5)->get();

        return view('talent.my-talent', compact('pegawai', 'mapping', 'tahun', 'historyMappings', 'assessments', 'skpRecords', 'diklatRecords'));
    }

    /**
     * Halaman Katalog Rekam Jejak Uji Kompetensi & Asesmen
     */
    public function asesmenIndex(Request $request)
    {
        $user = Auth::user();
        if (!$user->canAccessTalentManagement()) {
            abort(403, 'Akses terbatas untuk pimpinan dan tim manajemen kepegawaian.');
        }

        $search = $request->input('search');

        $query = TalentAssessment::with('pegawai.jabatan', 'pegawai.unitKerja');

        if ($search) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            })->orWhere('lembaga_penyelenggara', 'like', "%{$search}%")
              ->orWhere('nomor_surat', 'like', "%{$search}%");
        }

        $assessments = $query->orderBy('tanggal_asesmen', 'desc')->paginate(15)->withQueryString();

        return view('talent.asesmen.index', compact('assessments', 'search'));
    }

    /**
     * Form Tambah Hasil Asesmen Baru
     */
    public function asesmenCreate()
    {
        $user = Auth::user();
        if (!$user->canManageTalentManagement()) {
            abort(403, 'Akses ditolak.');
        }

        $pegawais = Pegawai::where('status_pegawai', 'Aktif')
            ->orderBy('nama')
            ->get(['id', 'nip', 'nama', 'gelar_depan', 'gelar_belakang']);

        return view('talent.asesmen.create', compact('pegawais'));
    }

    /**
     * Simpan Data Asesmen Baru & Auto Refresh Kalkulasi Talenta Pegawai
     */
    public function storeAsesmen(Request $request)
    {
        $user = Auth::user();
        if (!$user->canManageTalentManagement()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'pegawai_id'              => 'required|exists:pegawai,id',
            'tanggal_asesmen'         => 'required|date',
            'nomor_surat'             => 'nullable|string|max:100',
            'lembaga_penyelenggara'   => 'required|string|max:150',
            'metode_asesmen'          => 'required|string|max:150',
            'skor_manajerial'         => 'nullable|numeric|between:0,100',
            'skor_sosio_kultural'     => 'nullable|numeric|between:0,100',
            'skor_teknis'             => 'nullable|numeric|between:0,100',
            'skor_potensi'            => 'nullable|numeric|between:0,100',
            'skor_total'              => 'required|numeric|between:0,100',
            'kategori_kelayakan'      => 'required|string|max:50',
            'ringkasan_kompetensi'    => 'nullable|string',
            'rekomendasi_pengembangan'=> 'nullable|string',
            'file_laporan'            => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        if ($request->hasFile('file_laporan')) {
            $path = $request->file('file_laporan')->store('talent_assessments', 'private');
            $validated['file_laporan'] = $path;
        }

        $validated['created_by'] = $user->id;

        $assessment = TalentAssessment::create($validated);

        // Langsung refresh nilai talenta pegawai untuk tahun asesmen tersebut
        $pegawai = Pegawai::findOrFail($validated['pegawai_id']);
        $year = (int) date('Y', strtotime($validated['tanggal_asesmen']));
        $this->scoringService->calculatePegawaiTalent($pegawai, $year);

        return redirect()->route('manajemen-talenta.asesmen.index')
            ->with('success', 'Data hasil asesmen berhasil disimpan dan posisi talenta pegawai telah dikalkulasi ulang.');
    }

    /**
     * Hapus Data Asesmen
     */
    public function destroyAsesmen(TalentAssessment $asesmen)
    {
        $user = Auth::user();
        if (!$user->canManageTalentManagement()) {
            abort(403, 'Akses ditolak.');
        }

        $pegawai = $asesmen->pegawai;
        $year = (int) date('Y', strtotime($asesmen->tanggal_asesmen));

        if ($asesmen->file_laporan && Storage::disk('private')->exists($asesmen->file_laporan)) {
            Storage::disk('private')->delete($asesmen->file_laporan);
        }

        $asesmen->delete();

        // Refresh kalkulasi
        if ($pegawai) {
            $this->scoringService->calculatePegawaiTalent($pegawai, $year);
        }

        return back()->with('success', 'Data asesmen berhasil dihapus.');
    }

    /**
     * Dashboard Rencana Suksesi Jabatan (Succession Planning Overview)
     */
    public function suksesiIndex(Request $request)
    {
        $user = Auth::user();
        if (!$user->canAccessTalentManagement()) {
            abort(403, 'Akses terbatas untuk pimpinan dan tim manajemen kepegawaian.');
        }

        $tahun = (int) ($request->input('tahun', date('Y')));
        $unitKerjaId = $request->input('unit_kerja_id');

        // Daftar Jabatan di Fakultas (Terutama Jabatan Struktural / Pimpinan / Pokja / Koorprodi)
        $queryJabatan = Jabatan::with(['unitKerja', 'analisisJabatan', 'pegawai' => function ($q) {
            $q->where('status_pegawai', 'Aktif');
        }, 'successionPlans' => function ($q) use ($tahun) {
            $q->where('tahun', $tahun)->with('pegawai.golongan');
        }]);

        if ($unitKerjaId) {
            $queryJabatan->where('unit_kerja_id', $unitKerjaId);
        }

        $jabatans = $queryJabatan->get()->sortBy(function ($j) {
            return $j->hierarchy_order;
        })->values();

        $unitKerjas = UnitKerja::orderBy('nama_unit')->get();
        $availableYears = range(date('Y'), date('Y') - 4);

        // Rekap Statistik Suksesi
        $totalNominasi = SuccessionPlan::where('tahun', $tahun)->count();
        $readyNowCount = SuccessionPlan::where('tahun', $tahun)->where('status_kesiapan', 'Siap Sekarang')->count();

        return view('talent.suksesi.index', compact('jabatans', 'tahun', 'unitKerjaId', 'unitKerjas', 'availableYears', 'totalNominasi', 'readyNowCount'));
    }

    /**
     * Detail Job Matching & Gap Analysis Kandidat Suksesi untuk Jabatan Target Tertentu
     */
    public function suksesiJabatan(Jabatan $jabatan, Request $request, SuccessionMatchingService $matchingService)
    {
        $user = Auth::user();
        if (!$user->canAccessTalentManagement()) {
            abort(403, 'Akses terbatas untuk pimpinan dan tim manajemen kepegawaian.');
        }

        $tahun = (int) ($request->input('tahun', date('Y')));

        // Pastikan talent mapping tahun ini sudah tersedia
        if (!TalentMapping::where('tahun', $tahun)->exists()) {
            $this->scoringService->batchCalculate($tahun);
        }

        $candidates = $matchingService->findCandidatePool($jabatan, $tahun);

        $existingPlans = SuccessionPlan::with('pegawai.jabatan', 'pegawai.unitKerja')
            ->where('jabatan_target_id', $jabatan->id)
            ->where('tahun', $tahun)
            ->orderBy('peringkat_prioritas')
            ->get();

        $incumbents = $jabatan->pegawai()->where('status_pegawai', 'Aktif')->get();
        $anjab = $jabatan->analisisJabatan;

        return view('talent.suksesi.show', compact('jabatan', 'candidates', 'existingPlans', 'incumbents', 'anjab', 'tahun'));
    }

    /**
     * Simpan Penetapan Nominasi Kandidat Suksesi ke Jabatan Target
     */
    public function storeSuksesiNominasi(Request $request, SuccessionMatchingService $matchingService)
    {
        $user = Auth::user();
        if (!$user->canManageTalentManagement()) {
            abort(403, 'Akses ditolak. Hanya Komite Talenta yang dapat menetapkan nominasi suksesi.');
        }

        $validated = $request->validate([
            'jabatan_target_id'   => 'required|exists:jabatan,id',
            'pegawai_id'          => 'required|exists:pegawai,id',
            'tahun'               => 'required|integer',
            'peringkat_prioritas' => 'required|integer|min:1|max:10',
            'status_kesiapan'     => 'required|in:Siap Sekarang,Siap 1-2 Tahun,Potensial Jangka Panjang',
            'status_nominasi'     => 'required|in:Kandidat Terpilih,Dalam Seleksi,Ditetapkan PPK,Batal',
            'catatan_komite'      => 'nullable|string|max:1000',
        ]);

        $jabatan = Jabatan::findOrFail($validated['jabatan_target_id']);
        $pegawai = Pegawai::findOrFail($validated['pegawai_id']);

        // Dapatkan mapping talenta
        $mapping = TalentMapping::where('pegawai_id', $pegawai->id)
            ->where('tahun', $validated['tahun'])
            ->first();

        if (!$mapping) {
            $mapping = $this->scoringService->calculatePegawaiTalent($pegawai, $validated['tahun']);
        }

        $matchResult = $matchingService->calculateMatchScore($pegawai, $mapping, $jabatan, $jabatan->analisisJabatan);

        SuccessionPlan::updateOrCreate(
            [
                'jabatan_target_id' => $validated['jabatan_target_id'],
                'pegawai_id'        => $validated['pegawai_id'],
                'tahun'             => $validated['tahun'],
            ],
            [
                'peringkat_prioritas' => $validated['peringkat_prioritas'],
                'match_score'         => $matchResult['total_score'],
                'gap_analysis'        => $matchResult['gap_items'],
                'status_kesiapan'     => $validated['status_kesiapan'],
                'status_nominasi'     => $validated['status_nominasi'],
                'catatan_komite'      => $validated['catatan_komite'],
                'created_by'          => $user->id,
            ]
        );

        return back()->with('success', "Pegawai {$pegawai->nama} berhasil ditetapkan ke dalam Rencana Suksesi Jabatan {$jabatan->nama_jabatan}.");
    }

    /**
     * Hapus / Batalkan Nominasi Suksesi
     */
    public function destroySuksesiNominasi(SuccessionPlan $plan)
    {
        $user = Auth::user();
        if (!$user->canManageTalentManagement()) {
            abort(403, 'Akses ditolak.');
        }

        $plan->delete();

        return back()->with('success', 'Nominasi kandidat suksesi berhasil dihapus.');
    }
}
