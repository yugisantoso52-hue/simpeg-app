<?php

namespace App\Services;

use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentWatermarkService
{
    /**
     * Terapkan Watermark Dinamis & Audit Footprint ke Dokumen DomPDF
     */
    public function applyWatermark(PDF $pdf, ?string $customTitle = null): PDF
    {
        $user = Auth::user();
        $ip = request()->ip() ?? '127.0.0.1';
        $now = Carbon::now('Asia/Jakarta')->format('d/m/Y H:i:s') . ' WIB';

        $pengunduh = $user ? "{$user->name} (" . ($user->pegawai?->nip ?? $user->email) . ")" : "Sistem SIKAP FKP";
        
        $watermarkText = "SIKAP FKP UNRI • DOKUMEN RESMI • DIAKSES OLEH: {$pengunduh} • {$now}";
        $footerTrack = "Jejak Digital SIKAP FKP: Diunduh oleh [{$pengunduh}] pada [{$now}] dari IP [{$ip}] • Verifikasi Keaslian Dokumen Terproteksi";

        $dompdf = $pdf->getDomPDF();
        $dompdf->render();
        $canvas = $dompdf->getCanvas();

        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($watermarkText, $footerTrack) {
            $font = $fontMetrics->getFont('Helvetica', 'bold');
            $fontLight = $fontMetrics->getFont('Helvetica', 'normal');

            // 1. Watermark Diagonal Transparan di Tengah Halaman
            $canvas->set_opacity(0.08);
            // Rotasi -35 derajat
            $canvas->text(50, 500, $watermarkText, $font, 12, [30, 41, 59], 0.0, 0.0, -35.0);

            // 2. Footer Bar Jejak Audit Digital di Bawah Halaman
            $canvas->set_opacity(0.55);
            $pageWidth = $canvas->get_width();
            $pageHeight = $canvas->get_height();
            $canvas->text(30, $pageHeight - 20, $footerTrack . " [Hal. {$pageNumber} dari {$pageCount}]", $fontLight, 7, [71, 85, 105]);
        });

        return $pdf;
    }

    /**
     * Buat Token & Kode Verifikasi Dokumen Kriptografis Berbasis HMAC
     */
    public function generateVerificationCode(string $documentType, string $documentNumber, string $employeeName, ?string $issueDate = null): string
    {
        $payload = [
            'doc_type' => $documentType,
            'doc_no'   => $documentNumber,
            'name'     => $employeeName,
            'issued'   => $issueDate ?? date('Y-m-d'),
            'ts'       => time(),
        ];

        $jsonPayload = json_encode($payload);
        $signature = hash_hmac('sha256', $jsonPayload, config('app.key'));
        $shortHash = substr($signature, 0, 16);

        // Base64 URL safe
        $encodedData = rtrim(strtr(base64_encode($jsonPayload), '+/', '-_'), '=');

        return "{$shortHash}.{$encodedData}";
    }

    /**
     * Validasi Kode Verifikasi Dokumen
     */
    public function verifyDocumentCode(string $code): ?array
    {
        $parts = explode('.', $code, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$shortHash, $encodedData] = $parts;

        $jsonPayload = base64_decode(strtr($encodedData, '-_', '+/'));
        if (!$jsonPayload) {
            return null;
        }

        $expectedHash = substr(hash_hmac('sha256', $jsonPayload, config('app.key')), 0, 16);

        if (!hash_equals($expectedHash, $shortHash)) {
            return null;
        }

        $data = json_decode($jsonPayload, true);
        if (!is_array($data)) {
            return null;
        }

        return $data;
    }
}
