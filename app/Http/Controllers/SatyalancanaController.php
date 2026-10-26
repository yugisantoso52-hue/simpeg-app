<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UsulanSatyalancana;
use App\Models\User;
use App\Services\SatyalancanaService;
use App\Services\DocumentWatermarkService;
use App\Services\QrCodeService;
use App\Notifications\SatyalancanaWorkflowNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SatyalancanaController extends Controller
{
    protected SatyalancanaService $satyalancanaService;

    public function __construct(SatyalancanaService $satyalancanaService)
    {
        $this->satyalancanaService = $satyalancanaService;
    }

    public function index()
    {
        $user = Auth::user();
        $allPegawai = Pegawai::with([
            'unitKerja', 'jabatan', 'golongan',
            'riwayatSkp' => fn($q) => $q->orderBy('tahun', 'desc'),
            'riwayatPangkat' => fn($q) => $q->orderBy('tmt', 'desc'),
            'riwayatJabatan' => fn($q) => $q->orderBy('tmt_jabatan', 'desc'),
            'riwayatPenghargaan' => fn($q) => $q->orderBy('tanggal_terima', 'desc'),
        ])->get();
        $layakSatyalancana = [];

        // Ambil usulan yang sudah diajukan untuk tahun berjalan
        $tahunIni = (int) date('Y');
        $usulanTerkini = UsulanSatyalancana::where('tahun_usulan', $tahunIni)
            ->get()
            ->keyBy('pegawai_id');

        foreach ($allPegawai as $pegawai) {
            $kategori = $this->satyalancanaService->cekKelayakanSatyalancana($pegawai);
            if ($kategori) {
                // Tempelkan informasi kategori penghargaan & status usulan
                $pegawai->kategori_satyalancana = $kategori;
                $pegawai->usulan_aktif = $usulanTerkini->get($pegawai->id);
                $layakSatyalancana[] = $pegawai;
            }
        }

        $daftarUsulan = UsulanSatyalancana::with([
            'pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan',
            'pegawai.riwayatSkp', 'pegawai.riwayatPangkat.golongan', 'pegawai.riwayatJabatan.jabatan', 'pegawai.riwayatPenghargaan',
            'diajukanOleh', 'verifikasiAdminBy', 'parafKabagBy', 'parafKaPokjaBy', 'ttdWd2By'
        ])->orderByDesc('id')->paginate(15);

        // Jabatan login untuk otorisasi tombol paraf / tanda tangan
        $jabatan = strtoupper(trim((string)($user->pegawai?->jabatan->nama_jabatan ?? '')));
        $isAdmin = $user->hasRole('admin');
        $isKaPokja = (str_contains($jabatan, 'KA POKJA') || str_contains($jabatan, 'KEPALA POKJA') || str_contains($jabatan, 'KETUA POKJA'))
            && (str_contains($jabatan, 'KEU') || str_contains($jabatan, 'KEPEG'));
        $isKabag = str_contains($jabatan, 'KEPALA BAGIAN UMUM') || str_contains($jabatan, 'KABAG UMUM') || str_contains($jabatan, 'KABAG TU');
        $isWd2 = str_contains($jabatan, 'WADEK II') || str_contains($jabatan, 'WAKIL DEKAN II') || (str_contains($jabatan, 'WAKIL DEKAN') && str_contains($jabatan, 'KEUANGAN'));
        $isDekan = str_contains($jabatan, 'DEKAN') && !str_contains($jabatan, 'WAKIL') && !str_contains($jabatan, 'WADEK');
        $isExecutive = $user->canAccessExecutiveKepegawaianMenus();

        return view('satyalancana.index', compact(
            'layakSatyalancana', 'daftarUsulan', 'isAdmin', 'isKaPokja', 'isKabag', 'isWd2', 'isDekan', 'isExecutive'
        ));
    }

    /**
     * Ajukan usulan resmi tanda kehormatan Satyalancana Karya Satya
     */
    public function ajukan(Pegawai $pegawai, Request $request)
    {
        $kategori = $request->input('kategori') ?? $this->satyalancanaService->cekKelayakanSatyalancana($pegawai);

        if (!$kategori) {
            return back()->with('error', 'Pegawai belum memenuhi kriteria masa kerja Satyalancana.');
        }

        $tahunIni = (int) date('Y');
        $periode = $request->input('periode_usulan', 'Periode Mei ' . ($tahunIni + 1));
        
        $existing = UsulanSatyalancana::where('pegawai_id', $pegawai->id)
            ->where('tahun_usulan', $tahunIni)
            ->where('kategori', $kategori)
            ->first();

        if ($existing) {
            return back()->with('warning', "Usulan Satyalancana {$kategori} untuk {$pegawai->nama} tahun {$tahunIni} sudah diajukan sebelumnya.");
        }

        $nomorUsulan = 'USL-SLKS/' . date('Ymd') . '/' . str_pad($pegawai->id, 4, '0', STR_PAD_LEFT);

        $usulan = UsulanSatyalancana::create([
            'pegawai_id'          => $pegawai->id,
            'kategori'            => $kategori,
            'tahun_usulan'        => $tahunIni,
            'periode_usulan'      => $periode,
            'nomor_surat_usulan'  => $nomorUsulan,
            'status'              => 'diajukan',
            'catatan'             => $request->input('catatan', 'Diajukan melalui modul Satyalancana SIKAP FKP UNRI.'),
            'diajukan_oleh'       => Auth::id(),
            'verifikasi_admin_at' => null,
            'verifikasi_admin_by' => null,
        ]);

        // Kirim notifikasi ke pegawai yang bersangkutan
        if ($pegawai->user) {
            $pegawai->user->notify(new SatyalancanaWorkflowNotification(
                $usulan,
                'dinominasikan',
                '🏆 Nominasi Tanda Kehormatan Satyalancana Karya Satya',
                "Selamat! Anda telah dinominasikan menerima Tanda Kehormatan Satyalancana Karya Satya {$kategori} ({$periode}). Dokumen DRH dan persyaratan dapat diakses di sistem."
            ));
        }

        // Kirim notifikasi ke pejabat Kabag Umum & Ka Pokja untuk tahapan paraf
        $pejabatParaf = User::whereHas('pegawai.jabatan', function ($q) {
            $q->where('nama_jabatan', 'like', '%Bagian Umum%')
              ->orWhere('nama_jabatan', 'like', '%Pokja%');
        })->get();

        foreach ($pejabatParaf as $pjb) {
            $pjb->notify(new SatyalancanaWorkflowNotification(
                $usulan,
                'perlu_paraf',
                '📑 Usulan Satyalancana Memerlukan Paraf Digital',
                "Terdapat usulan Satyalancana {$kategori} an. {$pegawai->nama} yang memerlukan paraf digital koordinasi tata naskah."
            ));
        }

        return back()->with('success', "Usulan resmi Satyalancana {$kategori} untuk {$pegawai->nama} berhasil diajukan dengan nomor {$nomorUsulan} dan siap diverifikasi.");
    }

    /**
     * Proses Verifikasi & Paraf Bertingkat (Urutan: Admin Kepegawaian -> Ka Pokja Keu-Kepeg -> Kepala Bagian Umum -> TTE Wakil Dekan II)
     */
    public function verifikasi(Request $request, int $id)
    {
        $user = Auth::user();
        if (!$user->canAccessExecutiveKepegawaianMenus()) {
            abort(403, 'Akses terbatas untuk Pimpinan dan Pengelola Kepegawaian.');
        }

        $usulan = UsulanSatyalancana::with('pegawai.user')->findOrFail($id);
        $tahap = $request->input('tahap'); // verifikasi_admin, paraf_kapokja, paraf_kabag, ttd_wd2, tolak
        $catatan = $request->input('catatan');

        $pejabatLogin = $user->pegawai ?? Pegawai::where('nama', 'like', '%' . $user->name . '%')->first();

        // Otorisasi peran per tahap
        $jabatan = strtoupper(trim((string)($user->pegawai?->jabatan->nama_jabatan ?? '')));
        $isAdmin = $user->hasRole('admin');
        $isKaPokja = (str_contains($jabatan, 'KA POKJA') || str_contains($jabatan, 'KEPALA POKJA') || str_contains($jabatan, 'KETUA POKJA'))
            && (str_contains($jabatan, 'KEU') || str_contains($jabatan, 'KEPEG'));
        $isKabag = str_contains($jabatan, 'KEPALA BAGIAN UMUM') || str_contains($jabatan, 'KABAG UMUM') || str_contains($jabatan, 'KABAG TU');
        $isWd2 = str_contains($jabatan, 'WADEK II') || str_contains($jabatan, 'WAKIL DEKAN II') || (str_contains($jabatan, 'WAKIL DEKAN') && str_contains($jabatan, 'KEUANGAN'));
        $isDekan = str_contains($jabatan, 'DEKAN') && !str_contains($jabatan, 'WAKIL') && !str_contains($jabatan, 'WADEK');

        // 1. TAHAP VERIFIKASI ADMIN KEPEGAWAIAN
        if ($tahap === 'verifikasi_admin') {
            if (!$isAdmin) {
                return back()->with('error', 'Tahap 1 Screening Berkas khusus untuk Admin Kepegawaian.');
            }

            $updateData = [
                'status'              => 'diverifikasi_admin',
                'verifikasi_admin_at' => now(),
                'verifikasi_admin_by' => $user->id,
                'catatan'             => $catatan ?? 'Telah diverifikasi kelengkapan berkas DRH 12 poin, SK CPNS, SK Pangkat, SK Jabatan, SKP 2 tahun, Surat Bebas Hukdis, dan Piagam sebelumnya oleh Admin Kepegawaian.',
            ];

            if ($request->filled('nomor_surat_input')) {
                $nomorInput = trim((string) $request->input('nomor_surat_input'));
                $tahun = $usulan->tahun_usulan ?: date('Y');
                $updateData['nomor_surat_usulan'] = app(\App\Services\NomorSuratService::class)->formatSatyalancana($nomorInput, (int) $tahun);
            }

            $usulan->update($updateData);
            $msg = '1. Verifikasi kelengkapan berkas & penomoran oleh Admin Kepegawaian berhasil.';
        }
        // 2. TAHAP PARAF 1: KA POKJA KEUANGAN & KEPEGAWAIAN
        elseif ($tahap === 'paraf_kapokja') {
            if (!$isKaPokja) {
                return back()->with('error', 'Tahap 2 Paraf Ka Pokja khusus untuk Kepala Pokja Keuangan dan Kepegawaian.');
            }

            if (!$usulan->verifikasi_admin_at) {
                return back()->with('error', 'Usulan belum diverifikasi kelengkapan berkasnya oleh Admin Kepegawaian.');
            }

            $usulan->update([
                'status'           => 'diparaf_kapokja',
                'paraf_kapokja_at' => now(),
                'paraf_kapokja_by' => $pejabatLogin?->id,
            ]);
            $msg = 'Paraf 1 (Ka Pokja Keuangan & Kepegawaian) berhasil dibubuhkan.';
        }
        // 3. TAHAP PARAF 2: KEPALA BAGIAN UMUM
        elseif ($tahap === 'paraf_kabag') {
            if (!$isKabag) {
                return back()->with('error', 'Tahap 3 Paraf Kabag khusus untuk Kepala Bagian Umum.');
            }

            if (!$usulan->paraf_kapokja_at) {
                return back()->with('error', 'Usulan harus diparaf terlebih dahulu oleh Ka Pokja Keuangan & Kepegawaian.');
            }

            $usulan->update([
                'status'         => 'diparaf_kabag',
                'paraf_kabag_at' => now(),
                'paraf_kabag_by' => $pejabatLogin?->id,
            ]);
            $msg = 'Paraf 2 (Kepala Bagian Umum) berhasil dibubuhkan.';
        }
        // 4. TAHAP PENGESAHAN: TTE WAKIL DEKAN II / DEKAN
        elseif ($tahap === 'ttd_wd2' || $tahap === 'setujui') {
            if (!$isWd2 && !$isDekan) {
                return back()->with('error', 'Tahap 4 Pengesahan TTE khusus untuk Wakil Dekan II / Dekan.');
            }

            if (!$usulan->paraf_kabag_at) {
                return back()->with('error', 'Usulan belum lengkap diparaf berjenjang (Ka Pokja & Kabag Umum). Dokumen tidak dapat disahkan sebelum paraf koordinasi selesai.');
            }

            $usulan->update([
                'status'      => 'disetujui_wd2',
                'ttd_wd2_at'  => now(),
                'ttd_wd2_by'  => $pejabatLogin?->id,
                'catatan'     => $catatan ?? 'Telah ditandatangani dan disetujui resmi oleh Wakil Dekan Bidang Keuangan dan Umum.',
            ]);

            // SINKRONISASI OTOMATIS: Catat ke Riwayat Penghargaan & update satyalancana_terakhir di profil pegawai
            if ($usulan->pegawai) {
                $namaPenghargaan = 'Satyalancana Karya Satya ' . $usulan->kategori;
                \App\Models\RiwayatPenghargaan::updateOrCreate(
                    [
                        'pegawai_id'        => $usulan->pegawai_id,
                        'nama_penghargaan'  => $namaPenghargaan,
                    ],
                    [
                        'jenis_penghargaan' => 'Satyalancana',
                        'instansi_pemberi'  => 'Presiden Republik Indonesia',
                        'tanggal_terima'    => now()->toDateString(),
                        'nomor_sk'          => $usulan->nomor_surat_usulan,
                        'file_sk'           => $usulan->file_surat_pengantar,
                        'keterangan'        => 'Disahkan melalui alur persetujuan Satyalancana SIKAP FKp UNRI (' . ($usulan->periode_usulan ?? 'Tahun ' . $usulan->tahun_usulan) . ')',
                    ]
                );

                $usulan->pegawai->update([
                    'satyalancana_terakhir' => $usulan->kategori
                ]);
            }

            $msg = 'Pengesahan Tanda Tangan Elektronik (TTE) Wakil Dekan II berhasil. Berkas sah, tercatat pada riwayat penghargaan, dan siap dikirimkan ke Rektorat / BKN.';

            // Notifikasi ke pegawai bahwa usulannya telah disetujui pimpinan
            if ($usulan->pegawai && $usulan->pegawai->user) {
                $usulan->pegawai->user->notify(new SatyalancanaWorkflowNotification(
                    $usulan,
                    'disetujui',
                    '✅ Usulan Satyalancana Disahkan Pimpinan',
                    "Usulan Tanda Kehormatan Satyalancana Karya Satya Anda telah ditandatangani resmi oleh Wakil Dekan II dan siap diteruskan ke Rektorat / BKN."
                ));
            }
        } elseif ($tahap === 'tolak') {
            $canReject = false;
            if (!$usulan->verifikasi_admin_at && $isAdmin) {
                $canReject = true;
            } elseif ($usulan->verifikasi_admin_at && !$usulan->paraf_kapokja_at && $isKaPokja) {
                $canReject = true;
            } elseif ($usulan->paraf_kapokja_at && !$usulan->paraf_kabag_at && $isKabag) {
                $canReject = true;
            } elseif ($usulan->paraf_kabag_at && !$usulan->ttd_wd2_at && ($isWd2 || $isDekan)) {
                $canReject = true;
            }

            if (!$canReject) {
                return back()->with('error', 'Anda hanya dapat menolak usulan pada tahapan verifikasi yang menjadi wewenang aktif Anda.');
            }

            $usulan->update([
                'status'  => 'ditolak',
                'catatan' => $catatan ?? 'Usulan belum memenuhi persyaratan administratif tanda kehormatan.',
            ]);
            $msg = 'Usulan Satyalancana telah ditolak / dikembalikan.';
        } else {
            return back()->with('error', 'Tahapan verifikasi tidak valid.');
        }

        return back()->with('success', $msg);
    }

    /**
     * Cetak Dokumen DRH (Daftar Riwayat Hidup) Usulan Tanda Kehormatan Format Resmi 12 Poin Setmilpres / BKN
     */
    public function cetakDrh(Pegawai $pegawai)
    {
        $user = Auth::user();
        if (!$user->canAccessExecutiveKepegawaianMenus() && (int)$user->pegawai_id !== (int)$pegawai->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh DRH pegawai ini.');
        }

        $pegawai->load([
            'unitKerja',
            'jabatan',
            'golongan',
            'riwayatPendidikan' => fn($q) => $q->orderBy('tahun_lulus', 'desc'),
            'riwayatPangkat'    => fn($q) => $q->orderBy('tmt', 'desc'),
            'riwayatJabatan'    => fn($q) => $q->orderBy('tmt_jabatan', 'desc'),
            'riwayatDiklat',
            'riwayatPenghargaan',
        ]);

        $kategori = $this->satyalancanaService->cekKelayakanSatyalancana($pegawai) ?? '10 Tahun';
        
        // Pejabat Dekan, WD2, Ka Pokja, dan Kabag Umum dinamis
        $pejabatService = app(\App\Services\PejabatService::class);
        $pejabatWd2     = $pejabatService->getWadek2Pegawai() ?? $pejabatService->getDekanPegawai();
        $pejabatKaPokja = $pejabatService->getKaPokjaPegawai();
        $pejabatKabag   = $pejabatService->getKabagPegawai();

        // Cari data usulan untuk waktu paraf faktual jika ada
        $usulan = UsulanSatyalancana::where('pegawai_id', $pegawai->id)
            ->where('tahun_usulan', date('Y'))
            ->latest()
            ->first();

        $watermarkService = app(DocumentWatermarkService::class);
        $verifyCode = $watermarkService->generateVerificationCode(
            'DRH Usulan Satyalancana Karya Satya ' . $kategori,
            'DRH-SLKS/' . $pegawai->nip . '/' . date('Y'),
            $pegawai->nama_lengkap ?? $pegawai->nama
        );
        $verifyUrl = route('verify.document', ['code' => $verifyCode]);

        // Kondisi faktual: hanya generate waktu dan QR code jika benar-benar SUDAH diverifikasi/diparaf/disetujui
        $sudahVerifAdmin = (bool) ($usulan && $usulan->verifikasi_admin_at);
        $sudahParafPokja = (bool) ($usulan && $usulan->paraf_kapokja_at);
        $sudahParafKabag = (bool) ($usulan && $usulan->paraf_kabag_at);
        $sudahTtdWd2     = (bool) ($usulan && $usulan->ttd_wd2_at);

        $parafPokjaAt = $sudahParafPokja ? $usulan->paraf_kapokja_at->format('d/m/Y H:i') . ' WIB' : null;
        $parafKabagAt = $sudahParafKabag ? $usulan->paraf_kabag_at->format('d/m/Y H:i') . ' WIB' : null;
        $ttdWd2At     = $sudahTtdWd2     ? $usulan->ttd_wd2_at->format('d/m/Y H:i') . ' WIB' : null;

        $qrCodeDataUri = QrCodeService::generateDataUri($verifyUrl);
        $qrPokjaUri    = $sudahParafPokja ? QrCodeService::generateDataUri($verifyUrl . '?paraf=kapokja&nip=' . ($pejabatKaPokja?->nip ?? '197605212014092001')) : null;
        $qrKabagUri    = $sudahParafKabag ? QrCodeService::generateDataUri($verifyUrl . '?paraf=kabag&nip=' . ($pejabatKabag?->nip ?? '197507122005011002')) : null;
        $qrWd2Uri      = $sudahTtdWd2     ? QrCodeService::generateDataUri($verifyUrl . '?ttd=wd2&nip=' . ($pejabatWd2?->nip ?? '198509092014041001')) : null;

        $pdf = Pdf::loadView('satyalancana.drh_print', compact(
            'pegawai',
            'usulan',
            'kategori',
            'pejabatWd2',
            'pejabatKaPokja',
            'pejabatKabag',
            'sudahVerifAdmin',
            'sudahParafPokja',
            'sudahParafKabag',
            'sudahTtdWd2',
            'parafPokjaAt',
            'parafKabagAt',
            'ttdWd2At',
            'verifyCode',
            'verifyUrl',
            'qrCodeDataUri',
            'qrPokjaUri',
            'qrKabagUri',
            'qrWd2Uri'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('DRH_Satyalancana_' . $pegawai->nip . '.pdf');
    }

    /**
     * Cetak Surat Pengantar Resmi Kolektif Usulan Tanda Kehormatan ke Rektorat UNRI
     */
    public function cetakSuratPengantar(Request $request)
    {
        $user = Auth::user();
        $isExecutiveOrAdmin = $user && ($user->canAccessExecutiveKepegawaianMenus() || $user->hasRole('admin'));
        $nomorSuratInput = $isExecutiveOrAdmin ? trim((string) $request->input('nomor_surat_input', '')) : '';

        if ($nomorSuratInput !== '') {
            $nomorSurat = app(\App\Services\NomorSuratService::class)->formatSatyalancana($nomorSuratInput, (int) date('Y'));
        } else {
            // Ambil nomor surat yang sudah ditetapkan Admin pada usulan tahun berjalan
            $existingNomor = UsulanSatyalancana::where('tahun_usulan', date('Y'))
                ->whereNotNull('nomor_surat_usulan')
                ->where('nomor_surat_usulan', 'like', '%/UN19%')
                ->value('nomor_surat_usulan');
            $nomorSurat = $existingNomor ?? app(\App\Services\NomorSuratService::class)->formatSatyalancana($request->input('nomor_surat', '2422'), (int) date('Y'));
        }

        $tanggalSuratInput = $request->input('tanggal_surat');
        $tanggalSurat = $tanggalSuratInput 
            ? \Carbon\Carbon::parse($tanggalSuratInput)->locale('id')->translatedFormat('d F Y') 
            : \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');

        $suratRujukanNomor   = $request->input('surat_rujukan_nomor', '200/DST/UN19.5.2.2.1/KP.11.02/' . date('Y'));
        $suratRujukanTanggal = $request->input('surat_rujukan_tanggal', '22 Juli ' . date('Y'));
        $suratRujukanPerihal = $request->input('surat_rujukan_perihal', 'Pengusulan Tanda Satyalancana Karya Satya Periode Mei ' . (date('Y') + 1));
        $periodeUsulan       = $request->input('periode', 'Periode Mei ' . (date('Y') + 1));

        // Ambil pegawai yang berhak atau pegawai yang sudah diusulkan resmi
        $usulans = UsulanSatyalancana::with(['pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan'])
            ->where('tahun_usulan', date('Y'))
            ->get();

        $daftarPegawai = collect();

        if ($usulans->isNotEmpty()) {
            foreach ($usulans as $u) {
                $p = $u->pegawai;
                if ($p) {
                    $p->kategori_satyalancana = $u->kategori;
                    $daftarPegawai->push($p);
                }
            }
        } else {
            // Ambil dari deteksi kelayakan sistem jika belum diajukan
            $allPegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])->get();
            foreach ($allPegawai as $pegawai) {
                $kategori = $this->satyalancanaService->cekKelayakanSatyalancana($pegawai);
                if ($kategori) {
                    $pegawai->kategori_satyalancana = $kategori;
                    $daftarPegawai->push($pegawai);
                }
            }
        }

        $pejabatService = app(\App\Services\PejabatService::class);
        $pejabatWd2     = $pejabatService->getWadek2Pegawai() ?? $pejabatService->getDekanPegawai();
        $pejabatKaPokja = $pejabatService->getKaPokjaPegawai();
        $pejabatKabag   = $pejabatService->getKabagPegawai();

        // Hitung status kolektif dari usulan yang ada
        // Minimal ada usulan yang telah diverifikasi/diparaf
        $sudahParafPokja = $usulans->isNotEmpty() && $usulans->whereNotNull('paraf_kapokja_at')->count() === $usulans->count();
        $sudahParafKabag = $usulans->isNotEmpty() && $usulans->whereNotNull('paraf_kabag_at')->count() === $usulans->count();
        $sudahTtdWd2     = $usulans->isNotEmpty() && $usulans->whereNotNull('ttd_wd2_at')->count() === $usulans->count();

        $tglPokjaLatest = $usulans->whereNotNull('paraf_kapokja_at')->sortByDesc('paraf_kapokja_at')->first()?->paraf_kapokja_at;
        $tglKabagLatest = $usulans->whereNotNull('paraf_kabag_at')->sortByDesc('paraf_kabag_at')->first()?->paraf_kabag_at;
        $tglWd2Latest   = $usulans->whereNotNull('ttd_wd2_at')->sortByDesc('ttd_wd2_at')->first()?->ttd_wd2_at;

        $parafPokjaAt = $sudahParafPokja && $tglPokjaLatest ? $tglPokjaLatest->format('d/m/Y H:i') . ' WIB' : null;
        $parafKabagAt = $sudahParafKabag && $tglKabagLatest ? $tglKabagLatest->format('d/m/Y H:i') . ' WIB' : null;
        $ttdWd2At     = $sudahTtdWd2 && $tglWd2Latest ? $tglWd2Latest->format('d/m/Y H:i') . ' WIB' : null;

        $watermarkService = app(DocumentWatermarkService::class);
        $verifyCode = $watermarkService->generateVerificationCode(
            'Surat Pengantar Satyalancana Karya Satya',
            $nomorSurat,
            'Fakultas Keperawatan Universitas Riau'
        );
        $verifyUrl = route('verify.document', ['code' => $verifyCode]);

        $qrWd2Uri      = $sudahTtdWd2 ? QrCodeService::generateDataUri($verifyUrl . '?ttd=wd2&nip=' . ($pejabatWd2?->nip ?? '198509092014041001')) : null;
        $qrPokjaUri    = $sudahParafPokja ? QrCodeService::generateDataUri($verifyUrl . '?paraf=kapokja&nip=' . ($pejabatKaPokja?->nip ?? '197605212014092001')) : null;
        $qrKabagUri    = $sudahParafKabag ? QrCodeService::generateDataUri($verifyUrl . '?paraf=kabag&nip=' . ($pejabatKabag?->nip ?? '197507122005011002')) : null;
        $qrCodeDataUri = QrCodeService::generateDataUri($verifyUrl);

        $pdf = Pdf::loadView('satyalancana.surat_pengantar_print', compact(
            'periodeUsulan', 'nomorSurat', 'tanggalSurat', 'daftarPegawai',
            'suratRujukanNomor', 'suratRujukanTanggal', 'suratRujukanPerihal',
            'pejabatWd2', 'pejabatKaPokja', 'pejabatKabag',
            'sudahParafPokja', 'sudahParafKabag', 'sudahTtdWd2',
            'parafPokjaAt', 'parafKabagAt', 'ttdWd2At',
            'verifyCode', 'verifyUrl', 'qrCodeDataUri', 'qrWd2Uri', 'qrPokjaUri', 'qrKabagUri'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('Surat_Pengantar_Satyalancana_' . date('Y') . '.pdf');
    }

    /**
     * Unduh Panduan & Dokumen Persyaratan Resmi Satyalancana Karya Satya
     */
    public function downloadSyarat()
    {
        $watermarkService = app(DocumentWatermarkService::class);
        $pdf = Pdf::loadView('satyalancana.syarat_panduan')
            ->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('Petunjuk_Persyaratan_Satyalancana_FKP_UNRI.pdf');
    }

    /**
     * Update Nomor Surat Pengantar Satyalancana Khusus Admin Kepegawaian
     */
    public function updateNomorSurat(Request $request, int $id)
    {
        $user = $request->user() ?? Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Hanya Admin Kepegawaian yang berwenang mengisi atau mengubah nomor surat.');
        }

        $usulan = UsulanSatyalancana::findOrFail($id);
        $nomorInput = trim((string) $request->input('nomor_surat_input', ''));
        $tahun = $usulan->tahun_usulan ?: date('Y');

        if ($nomorInput !== '') {
            $nomorSuratFormatted = app(\App\Services\NomorSuratService::class)->formatSatyalancana($nomorInput, (int) $tahun);
        } else {
            $nomorSuratFormatted = null;
        }

        $usulan->update(['nomor_surat_usulan' => $nomorSuratFormatted]);

        return back()->with('success', 'Nomor surat usulan Satyalancana berhasil disimpan oleh Admin: ' . ($nomorSuratFormatted ?? '(Dikosongkan)'));
    }
}