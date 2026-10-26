@props([
    'mandatPrefix'      => null,
    'jabatan'           => 'Wakil Dekan II (Bid. Keuangan dan Umum)',
    'pejabatNama'       => 'Dr. Safri, M.Kep, Sp.Kep.M.B',
    'pejabatNip'        => '198509092014041001',
    'isSigned'          => false,
    'signedAt'          => null,
    'qrDataUri'         => null,
    'align'             => 'left', // 'left' | 'center'
    'showDraftBadge'    => true,
    'manualSpaceHeight' => '58px',
])

@php
    $normalizedNama = str_replace(['M.kep', 'Sp.kep', 'Sp. Kep.M.B'], ['M.Kep', 'Sp.Kep', 'Sp.Kep.M.B'], (string) $pejabatNama);
    $resolvedMandat = $mandatPrefix;
    if ($resolvedMandat === null && str_contains(strtolower((string) $jabatan), 'wakil dekan')) {
        $resolvedMandat = 'a.n. Dekan Fakultas Keperawatan';
    }
@endphp

<div style="text-align: {{ $align }}; font-weight: normal; margin-bottom: 0; line-height: 1.25;">
    @if(!empty($resolvedMandat))
        {{ $resolvedMandat }}<br>
    @endif
    {{ $jabatan }}
</div>

@if($isSigned && !empty($qrDataUri))
    <div style="text-align: {{ $align }}; margin: 4px {{ $align === 'center' ? 'auto' : '0' }} 4px {{ $align === 'center' ? 'auto' : '0' }};">
        <img src="{{ $qrDataUri }}" style="width: 46px; height: 46px; display: inline-block;" alt="QR TTE" />
        <div style="font-size: 6.2pt; color: #15803d; font-weight: bold; margin-top: 1px;">
            Ditandatangani secara Elektronik (TTE)
        </div>
        @if(!empty($signedAt))
            <div style="font-size: 5.6pt; color: #64748b;">
                SIKAP FKP UNRI &bull; {{ $signedAt }}
            </div>
        @endif
    </div>
    <div style="text-align: {{ $align }}; font-weight: bold; text-decoration: underline;">{{ $normalizedNama }}</div>
    <div style="text-align: {{ $align }}; font-size: 8.5pt;">NIP. {{ $pejabatNip ?: '.....................................................' }}</div>
@elseif($showDraftBadge)
    <div style="text-align: {{ $align }}; margin: 6px 0 4px 0;">
        <span style="display: inline-block; padding: 2px 6px; border: 1px dashed #e11d48; background-color: #fff1f2; color: #be123c; font-size: 6.8pt; font-weight: bold; border-radius: 3px;">
            [ DRAF - MENUNGGU PENGESAHAN TTE ]
        </span>
    </div>
    <div style="text-align: {{ $align }}; color: #64748b; font-weight: bold; text-decoration: underline;">( {{ $normalizedNama }} )</div>
    <div style="text-align: {{ $align }}; color: #64748b;">NIP. {{ $pejabatNip ?: '.....................................................' }}</div>
@else
    <div style="height: {{ $manualSpaceHeight }};">&nbsp;</div>
    <div style="text-align: {{ $align }}; font-weight: bold; text-decoration: underline;">{{ $normalizedNama }}</div>
    <div style="text-align: {{ $align }}; font-size: 8.5pt;">NIP. {{ $pejabatNip ?: '.....................................................' }}</div>
@endif
