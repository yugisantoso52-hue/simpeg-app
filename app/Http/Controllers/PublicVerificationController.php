<?php

namespace App\Http\Controllers;

use App\Services\DocumentWatermarkService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicVerificationController extends Controller
{
    protected DocumentWatermarkService $watermarkService;

    public function __construct(DocumentWatermarkService $watermarkService)
    {
        $this->watermarkService = $watermarkService;
    }

    /**
     * Halaman Publik Verifikasi Keaslian Dokumen Kepegawaian (Bisa diakses tanpa login)
     */
    public function verify(Request $request, string $code): View
    {
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        $documentData = $this->watermarkService->verifyDocumentCode($code);

        return view('public.verify-document', [
            'isValid'  => $documentData !== null,
            'document' => $documentData,
            'code'     => $code,
        ]);
    }
}
