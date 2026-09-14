@props([
    'color'=>'green'
])

@php

$colors=[

'green'=>'bg-green-100 text-green-700',

'red'=>'bg-red-100 text-red-700',

'yellow'=>'bg-yellow-100 text-yellow-700',

'blue'=>'bg-blue-100 text-blue-700',

'purple'=>'bg-purple-100 text-purple-700',

];

@endphp

<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $colors[$color] ?? $colors['green'] }}">

{{ $slot }}

</span>