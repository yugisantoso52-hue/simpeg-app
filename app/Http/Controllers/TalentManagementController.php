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

    /**
     * Simulasi Job Fit & Head-to-Head Comparison Kandidat Suksesi
     */
    public function komparasiKandidat(Jabatan $jabatan, Request $request, SuccessionMatchingService $matchingService)
    {
        $user = Auth::user();
        if (!$user->canAccessTalentManagement()) {
            abort(403, 'Akses terbatas untuk pimpinan dan tim manajemen kepegawaian.');
        }

        $tahun = (int) ($request->input('tahun', date('Y')));
        $candidateIds = $request->input('candidate_ids', []);

        // Pastikan kalkulasi talent mapping tahun ini tersedia
        if (!TalentMapping::where('tahun', $tahun)->exists()) {
            $this->scoringService->batchCalculate($tahun);
        }

        $allPool = $matchingService->findCandidatePool($jabatan, $tahun);

        if (!empty($candidateIds)) {
            $candidateIdsArray = is_array($candidateIds) ? $candidateIds : explode(',', (string) $candidateIds);
            $selectedCandidates = $allPool->filter(fn($c) => in_array($c->pegawai->id, $candidateIdsArray))->values();
            if ($selectedCandidates->isEmpty()) {
                $selectedCandidates = $allPool->take(3);
            }
        } else {
            $selectedCandidates = $allPool->take(3);
        }

        // Muat data relasi tambahan untuk perbandingan komparatif
        foreach ($selectedCandidates as $cand) {
            $cand->pegawai->load([
                'riwayatSkp' => fn($q) => $q->orderBy('tahun', 'desc')->take(2),
                'riwayatDiklat' => fn($q) => $q->orderBy('tanggal_selesai', 'desc'),
                'talentAssessments' => fn($q) => $q->orderBy('tanggal_asesmen', 'desc')->take(1),
                'riwayatJabatan' => fn($q) => $q->orderBy('tmt_jabatan', 'desc')->take(3),
            ]);
            $cand->total_jp = $cand->pegawai->riwayatDiklat->sum('jumlah_jam');
            $cand->latest_assessment = $cand->pegawai->talentAssessments->first();
        }

        $anjab = $jabatan->analisisJabatan;

        return view('talent.suksesi.komparasi', compact('jabatan', 'selectedCandidates', 'allPool', 'anjab', 'tahun'));
    }

    /**
     * Dashboard Rencana Pengembangan Kompetensi Individu (IDP - Hak 20 JP Pegawai ASN)
     */
    public function idpIndex(Request $request)
    {
        $user = Auth::user();
        if (!$user->canAccessTalentManagement()) {
            abort(403, 'Akses terbatas untuk pimpinan dan tim manajemen kepegawaian.');
        }

        $tahun = (int) ($request->input('tahun', date('Y')));
        $unitKerjaId = $request->input('unit_kerja_id');
        $statusJp = $request->input('status_jp'); // 'memenuhi', 'kurang'
        $search = $request->input('search');

        $query = Pegawai::with(['jabatan', 'unitKerja', 'golongan', 'riwayatDiklat', 'talentMappings' => function ($q) use ($tahun) {
            $q->where('tahun', $tahun);
        }])->where('status_pegawai', 'Aktif');

        if ($unitKerjaId) {
            $query->where('unit_kerja_id', $unitKerjaId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $allPegawais = $query->orderBy('nama')->get();

        // Hitung pemenuhan JP untuk masing-masing pegawai
        $pegawaiList = $allPegawais->map(function ($peg) {
            $totalJp = (float) $peg->riwayatDiklat->sum('jumlah_jam');
            $targetJp = 20;
            $sisaJp = max(0, $targetJp - $totalJp);
            $persen = min(100, round(($totalJp / $targetJp) * 100));
            $isMemenuhi = $totalJp >= $targetJp;

            $mapping = $peg->talentMappings->first();
            $kuadranBox = $mapping?->kuadran_box ?? 5;

            // Rekomendasi program pelatihan berdasarkan kuadran 9-kotak
            $rekomendasi = match (true) {
                $kuadranBox >= 8 => 'Pelatihan Kepemimpinan Nasional/Administrator (PKN/PKA) & Strategic Thinking',
                $kuadranBox === 7 => 'Executive Coaching, Manajemen Perubahan & Kepemimpinan Pengawas (PKP)',
                $kuadranBox >= 5 => 'Pelatihan Kompetensi Teknis Fungsional Keperawatan & Sertifikasi Keahlian',
                default => 'Pelatihan Dasar Teknis, Bimbingan Sikap Kerja, & Peningkatan Kinerja Tugas Pokok',
            };

            return [
                'pegawai' => $peg,
                'total_jp' => $totalJp,
                'sisa_jp' => $sisaJp,
                'persen' => $persen,
                'is_memenuhi' => $isMemenuhi,
                'kuadran_box' => $kuadranBox,
                'box_name' => $mapping?->box_name ?? 'Kotak 5',
                'rekomendasi' => $rekomendasi,
            ];
        });

        if ($statusJp === 'memenuhi') {
            $pegawaiList = $pegawaiList->filter(fn($p) => $p['is_memenuhi'])->values();
        } elseif ($statusJp === 'kurang') {
            $pegawaiList = $pegawaiList->filter(fn($p) => !$p['is_memenuhi'])->values();
        }

        // Agregasi Statistik
        $totalPegawai = $allPegawais->count();
        $totalMemenuhi = $allPegawais->filter(fn($p) => $p->riwayatDiklat->sum('jumlah_jam') >= 20)->count();
        $totalKurang = $totalPegawai - $totalMemenuhi;
        $totalJpSemua = $allPegawais->sum(fn($p) => $p->riwayatDiklat->sum('jumlah_jam'));
        $rataRataJp = $totalPegawai > 0 ? round($totalJpSemua / $totalPegawai, 1) : 0;

        $unitKerjas = UnitKerja::orderBy('nama_unit')->get();

        return view('talent.idp.index', compact(
            'pegawaiList',
            'tahun',
            'unitKerjaId',
            'statusJp',
            'search',
            'unitKerjas',
            'totalPegawai',
            'totalMemenuhi',
            'totalKurang',
            'rataRataJp'
        ));
    }

    /**
     * Detail Rencana Pengembangan Kompetensi Individu Pegawai (IDP Roadmap)
     */
    public function idpDetail(Pegawai $pegawai, Request $request)
    {
        $user = Auth::user();
        if (!$user->canAccessTalentManagement()) {
            abort(403, 'Akses terbatas untuk pimpinan dan tim manajemen kepegawaian.');
        }

        $tahun = (int) ($request->input('tahun', date('Y')));
        $pegawai->load(['jabatan', 'unitKerja', 'golongan', 'riwayatDiklat' => fn($q) => $q->orderBy('tanggal_selesai', 'desc'), 'talentAssessments']);

        $mapping = TalentMapping::where('pegawai_id', $pegawai->id)->where('tahun', $tahun)->first();
        if (!$mapping) {
            $mapping = $this->scoringService->calculatePegawaiTalent($pegawai, $tahun);
        }

        $totalJp = (float) $pegawai->riwayatDiklat->sum('jumlah_jam');
        $targetJp = 20;
        $sisaJp = max(0, $targetJp - $totalJp);
        $persen = min(100, round(($totalJp / $targetJp) * 100));

        return view('talent.idp.show', compact('pegawai', 'mapping', 'tahun', 'totalJp', 'targetJp', 'sisaJp', 'persen'));
    }

    /**
     * Dashboard Sidang Komite Talenta & Penetapan Talent Pool Resmi
     */
    public function sidangKomiteIndex(Request $request)
    {
        $user = Auth::user();
        if (!$user->canAccessTalentManagement()) {
            abort(403, 'Akses terbatas untuk pimpinan dan tim manajemen kepegawaian.');
        }

        $tahun = (int) ($request->input('tahun', date('Y')));
        $statusValidasi = $request->input('status_validasi');

        // Pastikan snapshot mapping tersedia
        if (!TalentMapping::where('tahun', $tahun)->exists()) {
            $this->scoringService->batchCalculate($tahun);
        }

        $query = TalentMapping::with(['pegawai.jabatan', 'pegawai.unitKerja', 'pegawai.golongan'])
            ->where('tahun', $tahun)
            ->whereIn('kuadran_box', [9, 8, 7]); // Anggota Talent Pool Potensial

        if ($statusValidasi) {
            $query->where('status_validasi', $statusValidasi);
        }

        $talentPoolCandidates = $query->orderBy('kuadran_box', 'desc')
            ->orderBy('sumbu_kinerja_nilai', 'desc')
            ->get();

        $successionPlans = SuccessionPlan::with(['jabatanTarget.unitKerja', 'pegawai.golongan'])
            ->where('tahun', $tahun)
            ->orderBy('peringkat_prioritas')
            ->get();

        // Rekap status
        $totalKandidat = TalentMapping::where('tahun', $tahun)->whereIn('kuadran_box', [9, 8, 7])->count();
        $totalDitetapkan = TalentMapping::where('tahun', $tahun)->whereIn('kuadran_box', [9, 8, 7])->where('status_validasi', 'Ditetapkan PPK')->count();
        $totalDitinjau = TalentMapping::where('tahun', $tahun)->whereIn('kuadran_box', [9, 8, 7])->where('status_validasi', 'Ditinjau Komite')->count();
        $totalDraft = TalentMapping::where('tahun', $tahun)->whereIn('kuadran_box', [9, 8, 7])->where('status_validasi', 'Draft')->count();

        return view('talent.sidang_komite', compact(
            'talentPoolCandidates',
            'successionPlans',
            'tahun',
            'statusValidasi',
            'totalKandidat',
            'totalDitetapkan',
            'totalDitinjau',
            'totalDraft'
        ));
    }

    /**
     * Simpan Keputusan Sidang Komite Talenta
     */
    public function tetapkanSidangKomite(Request $request)
    {
        $user = Auth::user();
        if (!$user->canManageTalentManagement()) {
            abort(403, 'Akses ditolak. Hanya Dewan Komite Talenta yang dapat menetapkan keputusan.');
        }

        $validated = $request->validate([
            'mapping_ids'      => 'required|array|min:1',
            'mapping_ids.*'    => 'exists:talent_mappings,id',
            'status_validasi'  => 'required|in:Draft,Ditinjau Komite,Ditetapkan PPK',
            'catatan_komite'   => 'nullable|string|max:1000',
        ]);

        TalentMapping::whereIn('id', $validated['mapping_ids'])->update([
            'status_validasi' => $validated['status_validasi'],
            'catatan_komite'  => $validated['catatan_komite'] ?? 'Disetujui dalam Sidang Komite Talenta Fakultas Keperawatan UNRI.',
            'validated_by'    => $user->id,
            'validated_at'    => now(),
        ]);

        return back()->with('success', count($validated['mapping_ids']) . " kandidat talent pool berhasil diperbarui statusnya menjadi '{$validated['status_validasi']}'.");
    }

    /**
     * Cetak Berita Acara Rapat Komite Talenta & Penetapan Talent Pool Resmi
     */
    public function cetakBeritaAcara(Request $request)
    {
        $user = Auth::user();
        if (!$user->canAccessTalentManagement()) {
            abort(403, 'Akses terbatas untuk pimpinan dan tim manajemen kepegawaian.');
        }

        $tahun = (int) ($request->input('tahun', date('Y')));

        $talentPool = TalentMapping::with(['pegawai.jabatan', 'pegawai.unitKerja', 'pegawai.golongan'])
            ->where('tahun', $tahun)
            ->whereIn('kuadran_box', [9, 8, 7])
            ->where('status_validasi', 'Ditetapkan PPK')
            ->orderBy('kuadran_box', 'desc')
            ->orderBy('sumbu_kinerja_nilai', 'desc')
            ->get();

        if ($talentPool->isEmpty()) {
            $talentPool = TalentMapping::with(['pegawai.jabatan', 'pegawai.unitKerja', 'pegawai.golongan'])
                ->where('tahun', $tahun)
                ->whereIn('kuadran_box', [9, 8, 7])
                ->orderBy('kuadran_box', 'desc')
                ->get();
        }

        $successionPlans = SuccessionPlan::with(['jabatanTarget.unitKerja', 'pegawai.golongan', 'pegawai.jabatan'])
            ->where('tahun', $tahun)
            ->orderBy('peringkat_prioritas')
            ->get();

        $watermarkService = app(\App\Services\DocumentWatermarkService::class);
        $verifyCode = $watermarkService->generateVerificationCode('Berita Acara Sidang Komite Talenta', 'BA-TALENTA/' . $tahun . '/' . date('Ymd'), 'Fakultas Keperawatan UNRI');
        $verifyUrl = route('verify.document', ['code' => $verifyCode]);
        $qrCodeDataUri = \App\Services\QrCodeService::generateDataUri($verifyUrl);

        return view('talent.berita_acara_print', compact('talentPool', 'successionPlans', 'tahun', 'verifyCode', 'verifyUrl', 'qrCodeDataUri'));
    }
}

