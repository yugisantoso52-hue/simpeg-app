@props([
    'status' => '',
])

@php

$status = trim((string) $status);

$styles = [

    'Aktif' => [
        'class' => 'bg-green-100 text-green-700',
        'icon' => '✓',
    ],

    'Tidak Aktif' => [
        'class' => 'bg-red-100 text-red-700',
        'icon' => '✕',
    ],

    'Pensiun' => [
        'class' => 'bg-gray-100 text-gray-700',
        'icon' => '🧓',
    ],

    'Mutasi' => [
        'class' => 'bg-yellow-100 text-yellow-700',
        'icon' => '⇄',
    ],

    'Disetujui' => [
        'class' => 'bg-blue-100 text-blue-700',
        'icon' => '✓',
    ],

    'Menunggu' => [
        'class' => 'bg-yellow-100 text-yellow-700',
        'icon' => '⏳',
    ],

    'Ditolak' => [
        'class' => 'bg-red-100 text-red-700',
        'icon' => '✕',
    ],

];

$config = $styles[$status] ?? [

    'class' => 'bg-gray-100 text-gray-700',

    'icon' => '•',

];

@endphp

<span
    {{ $attributes->merge([
        'class' => 'inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold '.$config['class']
    ]) }}>

    <span>

        {{ $config['icon'] }}

    </span>

    <span>

        {{ $status }}

    </span>

</span>