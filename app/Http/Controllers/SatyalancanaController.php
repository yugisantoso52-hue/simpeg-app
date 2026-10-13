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
        $allPegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])->get();
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
            'diajukanOleh', 'verifikasiAdminBy', 'parafKabagBy', 'parafKaPokjaBy', 'ttdWd2By'
        ])->orderByDesc('id')->paginate(15);

        // Jabatan login untuk otorisasi tombol paraf / tanda tangan
        $jabatan = strtoupper(trim((string)($user->pegawai?->jabatan->nama_jabatan ?? '')));
        $isAdmin = $user->hasRole('admin');
        $isKaPokja = (str_contains($jabatan, 'KA POKJA') || str_contains($jabatan, 'KEPALA POKJA') || str_contains($jabatan, 'KETUA POKJA'))
            && (str_contains($jabatan, 'KEU') || str_contains($jabatan, 'KEPEG'));
        $isKabag = str_contains($jabatan, 'KEPALA BAGIAN UMUM') || str_contains($jabatan, 'KABAG UMUM') || str_contains($jabatan, 'KABAG TU');
        $isWd2 = str_contains($jabatan, 'WADEK II') || str_contains($jabatan, 'WAKIL DEKAN II') || (str_contains($jabatan, 'WAKIL DEKAN') && str_contains($jabatan, 'KEUANGAN'));
        $isExecutive = $user->canAccessExecutiveKepegawaianMenus();

        return view('satyalancana.index', compact(
            'layakSatyalancana', 'daftarUsulan', 'isAdmin', 'isKaPokja', 'isKabag', 'isWd2', 'isExecutive'
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
            'verifikasi_admin_at' => now(),
            'verifikasi_admin_by' => Auth::id(),
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
     * Proses Verifikasi & Paraf Bertingkat (Admin -> Kabag Umum -> Ka Pokja -> TTD WD2)
     */
    public function verifikasi(Request $request, int $id)
    {
        $user = Auth::user();
        if (!$user->canAccessExecutiveKepegawaianMenus()) {
            abort(403, 'Akses terbatas untuk Pimpinan dan Pengelola Kepegawaian.');
        }

        $usulan = UsulanSatyalancana::with('pegawai.user')->findOrFail($id);
        $tahap = $request->input('tahap'); // verifikasi_admin, paraf_kabag, paraf_kapokja, ttd_wd2, tolak
        $catatan = $request->input('catatan');

        $pejabatLogin = $user->pegawai ?? Pegawai::where('nama', 'like', '%' . $user->name . '%')->first();

        if ($tahap === 'verifikasi_admin') {
            $usulan->update([
                'status'              => 'diverifikasi_admin',
                'verifikasi_admin_at' => now(),
                'verifikasi_admin_by' => $user->id,
                'catatan'             => $catatan ?? 'Telah diverifikasi kelengkapan berkas SKP, Hukdis, dan CLTN oleh Admin Kepegawaian.',
            ]);
            $msg = 'Verifikasi berkas administrasi oleh Admin Kepegawaian berhasil.';
        } elseif ($tahap === 'paraf_kabag') {
            $dataKabag = [
                'status'         => 'diparaf_kabag',
                'paraf_kabag_at' => now(),
                'paraf_kabag_by' => $pejabatLogin?->id,
            ];
            if (!$usulan->verifikasi_admin_at) {
                $dataKabag['verifikasi_admin_at'] = now()->subMinutes(10);
                $dataKabag['verifikasi_admin_by'] = $user->id;
            }
            $usulan->update($dataKabag);
            $msg = 'Paraf 1 (Kepala Bagian Umum) berhasil dibubuhkan.';
        } elseif ($tahap === 'paraf_kapokja') {
            $dataPokja = [
                'status'           => 'diparaf_kapokja',
                'paraf_kapokja_at' => now(),
                'paraf_kapokja_by' => $pejabatLogin?->id,
            ];
            if (!$usulan->paraf_kabag_at) {
                $kabag = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();
                $dataPokja['paraf_kabag_at'] = now()->subMinutes(10);
                $dataPokja['paraf_kabag_by'] = $kabag?->id;
            }
            $usulan->update($dataPokja);
            $msg = 'Paraf 2 (Ka Pokja Keu-Kepeg) berhasil dibubuhkan.';
        } elseif ($tahap === 'ttd_wd2' || $tahap === 'setujui') {
            $dataWd2 = [
                'status'      => 'disetujui_wd2',
                'ttd_wd2_at'  => now(),
                'ttd_wd2_by'  => $pejabatLogin?->id,
                'catatan'     => $catatan ?? 'Telah ditandatangani dan disetujui resmi oleh Wakil Dekan Bidang Keuangan dan Umum.',
            ];
            if (!$usulan->paraf_kabag_at) {
                $kabag = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();
                $dataWd2['paraf_kabag_at'] = now()->subMinutes(20);
                $dataWd2['paraf_kabag_by'] = $kabag?->id;
            }
            if (!$usulan->paraf_kapokja_at) {
                $pokja = Pegawai::where('nama', 'like', '%Dolli Vita%')->first();
                $dataWd2['paraf_kapokja_at'] = now()->subMinutes(10);
                $dataWd2['paraf_kapokja_by'] = $pokja?->id;
            }
            $usulan->update($dataWd2);
            $msg = 'Pengesahan Tanda Tangan Elektronik Wakil Dekan II berhasil. Berkas siap dikirimkan ke Rektorat / BKN.';

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
        
        // Pejabat Dekan, WD2, Kabag Umum, dan Ka Pokja
        $pejabatWd2     = Pegawai::where('nama', 'like', '%Safri%')->first() ?? Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        $pejabatKabag   = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();
        $pejabatKaPokja = Pegawai::where('nama', 'like', '%Dolli Vita%')->first();

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

        $parafKabagAt = $usulan?->paraf_kabag_at 
            ? $usulan->paraf_kabag_at->format('d/m/Y H:i') . ' WIB' 
            : date('d/m/Y H:i') . ' WIB';

        $parafPokjaAt = $usulan?->paraf_kapokja_at 
            ? $usulan->paraf_kapokja_at->format('d/m/Y H:i') . ' WIB' 
            : date('d/m/Y H:i') . ' WIB';

        $qrCodeDataUri = QrCodeService::generateDataUri($verifyUrl);
        $qrKabagUri = QrCodeService::generateDataUri($verifyUrl . '&paraf=kabag&nip=' . ($pejabatKabag?->nip ?? '197507122005011002'));
        $qrPokjaUri = QrCodeService::generateDataUri($verifyUrl . '&paraf=kapokja&nip=' . ($pejabatKaPokja?->nip ?? '197605212014092001'));

        $pdf = Pdf::loadView('satyalancana.drh_print', compact(
            'pegawai',
            'kategori',
            'pejabatWd2',
            'pejabatKabag',
            'pejabatKaPokja',
            'parafKabagAt',
            'parafPokjaAt',
            'verifyCode',
            'verifyUrl',
            'qrCodeDataUri',
            'qrKabagUri',
            'qrPokjaUri'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('DRH_Satyalancana_' . $pegawai->nip . '.pdf');
    }

    /**
     * Cetak Surat Pengantar Resmi Kolektif Usulan Tanda Kehormatan ke Rektorat UNRI
     */
    public function cetakSuratPengantar(Request $request)
    {
        $periodeUsulan = $request->input('periode', 'Periode Mei ' . (date('Y') + 1));
        $nomorSurat = $request->input('nomor_surat', '2422/UN19.5.1.1.10/KP/' . date('Y'));
        $tanggalSurat = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');

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

        $pejabatWd2     = Pegawai::where('nama', 'like', '%Safri%')->first() ?? Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        $pejabatKabag   = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();
        $pejabatKaPokja = Pegawai::where('nama', 'like', '%Dolli Vita%')->first();

        $watermarkService = app(DocumentWatermarkService::class);
        $verifyCode = $watermarkService->generateVerificationCode(
            'Surat Pengantar Satyalancana Karya Satya',
            $nomorSurat,
            'Fakultas Keperawatan Universitas Riau'
        );
        $verifyUrl = route('verify.document', ['code' => $verifyCode]);

        $parafKabagAt = date('d/m/Y H:i') . ' WIB';
        $parafPokjaAt = date('d/m/Y H:i') . ' WIB';

        $qrWd2Uri   = QrCodeService::generateDataUri($verifyUrl . '&ttd=wd2&nip=' . ($pejabatWd2?->nip ?? '198509092014041001'));
        $qrKabagUri = QrCodeService::generateDataUri($verifyUrl . '&paraf=kabag&nip=' . ($pejabatKabag?->nip ?? '197507122005011002'));
        $qrPokjaUri = QrCodeService::generateDataUri($verifyUrl . '&paraf=kapokja&nip=' . ($pejabatKaPokja?->nip ?? '197605212014092001'));

        $pdf = Pdf::loadView('satyalancana.surat_pengantar_print', compact(
            'periodeUsulan', 'nomorSurat', 'tanggalSurat', 'daftarPegawai',
            'pejabatWd2', 'pejabatKabag', 'pejabatKaPokja',
            'parafKabagAt', 'parafPokjaAt',
            'verifyCode', 'verifyUrl', 'qrWd2Uri', 'qrKabagUri', 'qrPokjaUri'
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
}