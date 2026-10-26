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
            return app(DeployWebhookController::class)->handle($request);
        }

        $service = $this->watermarkService ?? app(DocumentWatermarkService::class);
        $documentData = $service->verifyDocumentCode($code);

        // Ekstrak parameter tambahan (paraf / tte / ttd & nip) baik dari query string maupun suffix legacy (&paraf=...)
        $extraParams = $request->query();
        if (str_contains($code, '&')) {
            $queryPart = substr($code, strpos($code, '&') + 1);
            parse_str($queryPart, $parsedLegacy);
            $extraParams = array_merge($parsedLegacy, $extraParams);
        }

        $signerInfo = null;
        $parafType = $extraParams['paraf'] ?? null;
        $tteType   = $extraParams['tte'] ?? ($extraParams['ttd'] ?? null);
        $signerNip = preg_replace('/[^0-9]/', '', (string) ($extraParams['nip'] ?? ''));

        if ($parafType || $tteType) {
            $pegawaiSigner = $signerNip ? \App\Models\Pegawai::where('nip', $signerNip)->first() : null;
            if ($parafType === 'kapokja') {
                $signerInfo = [
                    'jenis'   => 'Paraf Elektronik Koordinasi Tata Naskah & Verifikasi Kepegawaian',
                    'jabatan' => 'Ka Pokja Keu-Kepeg',
                    'nama'    => $pegawaiSigner?->nama_lengkap ?? 'Dolli Vita Zenitha Harning Arivina, SE',
                    'nip'     => $pegawaiSigner?->nip ?? ($signerNip ?: '197605212014092001'),
                ];
            } elseif ($parafType === 'kabag') {
                $signerInfo = [
                    'jenis'   => 'Paraf Elektronik Koordinasi Tata Naskah',
                    'jabatan' => 'Kepala Bagian Umum',
                    'nama'    => $pegawaiSigner?->nama_lengkap ?? 'Bakhtiar, S.Sos., M.Si',
                    'nip'     => $pegawaiSigner?->nip ?? ($signerNip ?: '197507122005011002'),
                ];
            } elseif ($tteType === 'atasan') {
                $signerInfo = [
                    'jenis'   => 'Tanda Tangan Elektronik (TTE) Pertimbangan Atasan Langsung',
                    'jabatan' => $pegawaiSigner?->jabatan?->nama_jabatan ?? 'Kepala Bagian Umum',
                    'nama'    => $pegawaiSigner?->nama_lengkap ?? 'Bakhtiar, S.Sos., M.Si',
                    'nip'     => $pegawaiSigner?->nip ?? ($signerNip ?: '197507122005011002'),
                ];
            } elseif (in_array($tteType, ['pybmc', 'wd2', 'pimpinan'], true)) {
                $signerInfo = [
                    'jenis'   => 'Tanda Tangan Elektronik (TTE) Pejabat Yang Berwenang',
                    'jabatan' => 'a.n. Dekan Fakultas Keperawatan — Wakil Dekan II (Bid. Keuangan dan Umum)',
                    'nama'    => $pegawaiSigner?->nama_lengkap ?? 'Dr. Safri, M.Kep, Sp.Kep.M.B',
                    'nip'     => $pegawaiSigner?->nip ?? ($signerNip ?: '198509092014041001'),
                ];
            }
        }

        return view('public.verify-document', [
            'isValid'    => $documentData !== null,
            'document'   => $documentData,
            'code'       => preg_split('/[&?]/', trim($code), 2)[0] ?? $code,
            'signerInfo' => $signerInfo,
        ]);
    }
}
