<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UsulanSatyalancana;
use App\Services\SatyalancanaService;
use App\Services\DocumentWatermarkService;
use App\Services\QrCodeService;
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

        $daftarUsulan = UsulanSatyalancana::with(['pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('satyalancana.index', compact('layakSatyalancana', 'daftarUsulan'));
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
        $existing = UsulanSatyalancana::where('pegawai_id', $pegawai->id)
            ->where('tahun_usulan', $tahunIni)
            ->where('kategori', $kategori)
            ->first();

        if ($existing) {
            return back()->with('warning', "Usulan Satyalancana {$kategori} untuk {$pegawai->nama} tahun {$tahunIni} sudah diajukan sebelumnya.");
        }

        $nomorUsulan = 'USL-SLKS/' . date('Ymd') . '/' . str_pad($pegawai->id, 4, '0', STR_PAD_LEFT);

        UsulanSatyalancana::create([
            'pegawai_id'         => $pegawai->id,
            'kategori'           => $kategori,
            'tahun_usulan'       => $tahunIni,
            'nomor_surat_usulan' => $nomorUsulan,
            'status'             => 'diajukan',
            'catatan'            => $request->input('catatan', 'Diajukan melalui modul Satyalancana SIKAP FKP UNRI.'),
            'diajukan_oleh'      => Auth::id(),
        ]);

        return back()->with('success', "Usulan resmi Satyalancana {$kategori} untuk {$pegawai->nama} berhasil diajukan dengan nomor {$nomorUsulan}.");
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
        
        // Pejabat Dekan & Wakil Dekan Bidang Keuangan dan Umum (Dr. Safri, M.Kep, Sp.Kep.M.B)
        $pejabatWd2 = Pegawai::where('nama', 'like', '%Safri%')->first();
        if (!$pejabatWd2) {
            $pejabatWd2 = Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        }

        $watermarkService = app(DocumentWatermarkService::class);
        $verifyCode = $watermarkService->generateVerificationCode(
            'DRH Usulan Satyalancana Karya Satya ' . $kategori,
            'DRH-SLKS/' . $pegawai->nip . '/' . date('Y'),
            $pegawai->nama_lengkap ?? $pegawai->nama
        );
        $verifyUrl = route('verify.document', ['code' => $verifyCode]);
        $qrCodeDataUri = QrCodeService::generateDataUri($verifyUrl);

        $pdf = Pdf::loadView('satyalancana.drh_print', compact(
            'pegawai',
            'kategori',
            'pejabatWd2',
            'verifyCode',
            'verifyUrl',
            'qrCodeDataUri'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('DRH_Satyalancana_' . $pegawai->nip . '.pdf');
    }
}