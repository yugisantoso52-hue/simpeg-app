@php
    $otentikasiQr   = $qrCodeDataUri ?? (isset($verifyUrl) ? \App\Services\QrCodeService::generateDataUri($verifyUrl) : null);
    $otentikasiCode = $verifyCode ?? ($surat->qr_token ?? ($qrToken ?? ($verifyToken ?? null)));
    $otentikasiUrl  = $verifyUrl ?? null;
@endphp

@if(!empty($otentikasiQr) || !empty($otentikasiUrl))
<table class="otentikasi-table-dokumen" style="width: 100% !important; max-width: 100% !important; border: 1px dashed #0284c7 !important; background-color: #f0f9ff !important; border-collapse: collapse !important; margin: 5px 0 0 0 !important; font-family: Arial, sans-serif !important; page-break-inside: avoid !important;">
    <tr>
        @if(!empty($otentikasiQr))
            <td style="width: 42px !important; min-width: 42px !important; vertical-align: middle !important; text-align: center !important; padding: 3px 4px 3px 6px !important; border: none !important;">
                <img src="{{ $otentikasiQr }}" style="width: 36px !important; height: 36px !important; display: block !important; margin: 0 auto !important;" alt="QR Otentikasi">
            </td>
        @endif
        <td style="vertical-align: middle !important; padding: 3px 6px !important; border: none !important; font-size: 6.2pt !important; line-height: 1.25 !important; color: #0c4a6e !important;">
            <strong style="color: #0369a1 !important; font-size: 6.8pt !important; display: block !important; margin-bottom: 1px !important;">DOKUMEN RESMI ELEKTRONIK SIKAP</strong>
            Dokumen ini sah diterbitkan secara elektronik melalui SIKAP FKP UNRI. Validitas dokumen dapat diverifikasi secara daring.<br>
            @if(!empty($otentikasiCode))
                <span style="font-family: monospace !important; font-size: 5.6pt !important; color: #0284c7 !important;">Kode: {{ strlen($otentikasiCode) > 38 ? substr($otentikasiCode, 0, 34) . '...' : $otentikasiCode }}</span>
            @endif
            @if(!empty($otentikasiUrl))
                @if(!empty($otentikasiCode)) &bull; @endif
                <a href="{{ $otentikasiUrl }}" style="color: #0284c7 !important; text-decoration: underline !important; font-size: 5.6pt !important; font-family: monospace !important;">Tautan Verifikasi Keabsahan</a>
            @endif
        </td>
    </tr>
</table>
@endif
