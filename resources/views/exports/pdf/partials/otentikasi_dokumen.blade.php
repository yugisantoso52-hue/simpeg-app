@php
    $otentikasiQr   = $qrCodeDataUri ?? (isset($verifyUrl) ? \App\Services\QrCodeService::generateDataUri($verifyUrl) : null);
    $otentikasiCode = $verifyCode ?? ($surat->qr_token ?? ($qrToken ?? ($verifyToken ?? null)));
    $otentikasiUrl  = $verifyUrl ?? null;
@endphp

@if(!empty($otentikasiQr) || !empty($otentikasiUrl))
<div style="margin-top: 8px; border: 1px dashed #0284c7; padding: 4px 6px; border-radius: 4px; background-color: #f0f9ff; font-family: Arial, sans-serif; page-break-inside: avoid; width: 100%; box-sizing: border-box;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            @if(!empty($otentikasiQr))
                <td style="width: 44px; vertical-align: middle; text-align: center; padding-right: 6px;">
                    <img src="{{ $otentikasiQr }}" style="width: 40px; height: 40px; display: block; margin: 0 auto;" alt="QR Otentikasi">
                </td>
            @endif
            <td style="vertical-align: middle; font-size: 6.2pt; line-height: 1.25; color: #0c4a6e;">
                <strong style="color: #0369a1; font-size: 6.8pt; display: block; margin-bottom: 1px;">DOKUMEN RESMI ELEKTRONIK SIKAP</strong>
                Dokumen ini sah diterbitkan secara elektronik melalui SIKAP FKP UNRI. Validitas dokumen dapat diverifikasi secara daring.<br>
                @if(!empty($otentikasiCode))
                    <span style="font-family: monospace; font-size: 5.6pt; color: #0284c7;">Kode: {{ strlen($otentikasiCode) > 38 ? substr($otentikasiCode, 0, 34) . '...' : $otentikasiCode }}</span>
                @endif
                @if(!empty($otentikasiUrl))
                    @if(!empty($otentikasiCode)) &bull; @endif
                    <a href="{{ $otentikasiUrl }}" style="color: #0284c7; text-decoration: underline; font-size: 5.6pt; font-family: monospace;">Tautan Verifikasi Keabsahan</a>
                @endif
            </td>
        </tr>
    </table>
</div>
@endif
