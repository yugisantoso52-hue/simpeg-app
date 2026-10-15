<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Services\DocumentWatermarkService;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegulasiTataNaskahController extends Controller
{
    /**
     * Tampilkan Halaman Edukasi & Landasan Hukum Regulasi Tata Naskah Dinas SIKAP
     */
    public function index()
    {
        $user = Auth::user();

        // Pejabat Struktural Terkait
        $pejabatDekan   = Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        $pejabatWd2     = Pegawai::where('nama', 'like', '%Safri%')->first();
        $pejabatKabag   = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();
        $pejabatKaPokja = Pegawai::where('nama', 'like', '%Dolli Vita%')->first();

        return view('regulasi.index', compact(
            'user',
            'pejabatDekan',
            'pejabatWd2',
            'pejabatKabag',
            'pejabatKaPokja'
        ));
    }

    /**
     * Unduh Dokumen Resmi Buku Panduan Regulasi Tata Naskah Dinas (PDF)
     */
    public function exportPdf()
    {
        $pejabatDekan   = Pegawai::where('nama', 'like', '%Wan Nishfa Dewi%')->first();
        $pejabatWd2     = Pegawai::where('nama', 'like', '%Safri%')->first();
        $pejabatKabag   = Pegawai::where('nama', 'like', '%Bakhtiar%')->first();
        $pejabatKaPokja = Pegawai::where('nama', 'like', '%Dolli Vita%')->first();

        $watermarkService = app(DocumentWatermarkService::class);
        $verifyCode = $watermarkService->generateVerificationCode(
            'Buku Panduan Regulasi Tata Naskah Dinas',
            'PANDUAN/TND/FKP/' . date('Y'),
            'Fakultas Keperawatan Universitas Riau'
        );
        $verifyUrl = route('verify.document', ['code' => $verifyCode]);
        $qrCodeDataUri = QrCodeService::generateDataUri($verifyUrl);

        $pdf = Pdf::loadView('regulasi.panduan_pdf', compact(
            'pejabatDekan',
            'pejabatWd2',
            'pejabatKabag',
            'pejabatKaPokja',
            'verifyCode',
            'verifyUrl',
            'qrCodeDataUri'
        ))->setPaper('a4', 'portrait');

        $pdf = $watermarkService->applyWatermark($pdf);

        return $pdf->stream('Buku_Panduan_Regulasi_Tata_Naskah_SIKAP_UNRI.pdf');
    }
}
