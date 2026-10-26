@props([
    'title'   => 'PARAF DIGITAL KOORDINASI TATA NASKAH:',
    'items'   => [],
    'variant' => 'list', // 'cuti' | 'list'
])

@if($variant === 'cuti')
    @php
        $item = $items[0] ?? [];
        $isSigned     = !empty($item['isSigned']);
        $qrDataUri    = $item['qrDataUri'] ?? null;
        $roleLabel    = $item['roleLabel'] ?? 'Ka Pokja Keu-Kepeg';
        $pejabatNama  = $item['pejabatNama'] ?? 'Dolli Vita Zenitha Harning Arivina, SE';
        $pejabatNip   = $item['pejabatNip'] ?? '197605212014092001';
        $signedAt     = $item['signedAt'] ?? '';
        $waitingLabel = $item['waitingLabel'] ?? '[ Menunggu Verifikasi & Paraf Tata Naskah ]';
    @endphp
    <div style="font-weight: bold; color: #0f172a; margin-bottom: 2px; border-bottom: 1px solid #94a3b8; padding-bottom: 1.5px; font-size: 7pt;">
        {{ $title }}
    </div>
    <table style="width: 100%; border-collapse: collapse; font-size: 7pt; line-height: 1.2;">
        <tr>
            <td style="width: 36px; vertical-align: middle; padding: 2px 5px 2px 0; text-align: center;">
                @if($isSigned && !empty($qrDataUri))
                    <img src="{{ $qrDataUri }}" style="width: 32px; height: 32px; display: block; margin: 0 auto;" alt="QR Paraf Elektronik" />
                @else
                    <div style="font-size: 5.5pt; color: #94a3b8; border: 1px dashed #cbd5e1; padding: 4px 2px; border-radius: 2px; line-height: 1.1;">Belum<br>Paraf</div>
                @endif
            </td>
            <td style="vertical-align: middle; padding: 1px 0;">
                <div>
                    <span style="font-weight: bold; color: #0f172a;">{{ $roleLabel }}:</span>
                    @if($isSigned)
                        <span style="color: #047857; font-weight: bold;">[Diparaf <span class="checkbox-mark" style="font-size: 7.5pt;">&#10003;</span>]</span>
                    @else
                        <span style="color: #94a3b8;">[Menunggu Paraf]</span>
                    @endif
                </div>
                <div style="color: #1e293b; font-size: 6.8pt; font-weight: bold;">{{ $pejabatNama }}</div>
                <div style="color: #475569; font-size: 6.2pt;">NIP. {{ $pejabatNip }}</div>
                @if($isSigned)
                    <div style="color: #047857; font-weight: bold; font-size: 6pt;">
                        [ Terverifikasi &amp; Diparaf Digital ] @if(!empty($signedAt)) &bull; {{ $signedAt }} @endif
                    </div>
                @else
                    <div style="color: #64748b; font-size: 6pt; font-style: italic;">
                        {{ $waitingLabel }}
                    </div>
                @endif
            </td>
        </tr>
    </table>
@else
    <div style="border: 1px solid #1e293b; padding: 4px 6px; border-radius: 4px; background-color: #f8fafc; font-family: 'Times New Roman', Times, serif;">
        <div style="font-weight: bold; text-transform: uppercase; margin-bottom: 3px; color: #0f172a; border-bottom: 1px solid #94a3b8; padding-bottom: 2px; font-size: 7pt; letter-spacing: 0.2px;">
            {{ $title }}
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 7pt; line-height: 1.15;">
            @foreach($items as $idx => $item)
                @php
                    $isLast       = $idx === count($items) - 1;
                    $isSigned     = !empty($item['isSigned']);
                    $qrDataUri    = $item['qrDataUri'] ?? null;
                    $roleLabel    = $item['roleLabel'] ?? '';
                    $pejabatNama  = $item['pejabatNama'] ?? '';
                    $signedAt     = $item['signedAt'] ?? '';
                    $waitingLabel = $item['waitingLabel'] ?? '[ Menunggu Verifikasi & Paraf ]';
                @endphp
                <tr @if(!$isLast) style="border-bottom: 1px dashed #cbd5e1;" @endif>
                    <td style="width: 32px; vertical-align: middle; padding: 2.5px 4px 2px 0; text-align: center;">
                        @if($isSigned && !empty($qrDataUri))
                            <img src="{{ $qrDataUri }}" style="width: 28px; height: 28px; display: block; margin: 0 auto;" alt="QR Paraf Digital" />
                        @else
                            <div style="font-size: 5.5pt; color: #94a3b8; border: 1px dashed #cbd5e1; padding: 4px 1px; border-radius: 2px; line-height: 1.1;">Belum<br>Paraf</div>
                        @endif
                    </td>
                    <td style="vertical-align: middle; padding: 2.5px 0;">
                        <div style="font-weight: bold; color: #0f172a; font-size: 7pt;">{{ $roleLabel }}</div>
                        <div style="color: #1e293b; font-size: 6.8pt;">{{ $pejabatNama }}</div>
                        @if($isSigned)
                            <div style="color: #047857; font-weight: bold; font-size: 6.2pt;">
                                [ Terverifikasi &amp; Diparaf Digital ] @if(!empty($signedAt)) &bull; {{ $signedAt }} @endif
                            </div>
                        @else
                            <div style="color: #b45309; font-weight: bold; font-size: 6.2pt;">
                                {{ $waitingLabel }}
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endif
