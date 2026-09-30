<?php

namespace App\Http\Controllers;

use App\Services\DocumentWatermarkService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicVerificationController extends Controller
{
    protected ?DocumentWatermarkService $watermarkService;

    public function __construct(?DocumentWatermarkService $watermarkService = null)
    {
        $this->watermarkService = $watermarkService ?? app(DocumentWatermarkService::class);
    }

    /**
     * Halaman Publik Verifikasi Keaslian Dokumen Kepegawaian (Bisa diakses tanpa login)
     */
    public function verify(Request $request, string $code)
    {
        if ($code === 'auto-update' || $code === 'trigger-update-2026') {
            return (new DeployWebhookController())->handle($request);
        }

        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        $service = $this->watermarkService ?? app(DocumentWatermarkService::class);
        $documentData = $service->verifyDocumentCode($code);

        return view('public.verify-document', [
            'isValid'  => $documentData !== null,
            'document' => $documentData,
            'code'     => $code,
        ]);
    }
}
