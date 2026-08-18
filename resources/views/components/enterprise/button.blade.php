@props([
    'href' => null,
    'type' => 'button',

    'color' => 'blue',
    'size' => 'md',

    'icon' => null,
])

@php

$colors = [

    'blue' => 'bg-blue-600 hover:bg-blue-700 text-white',

    'green' => 'bg-emerald-600 hover:bg-emerald-700 text-white',

    'red' => 'bg-red-600 hover:bg-red-700 text-white',

    'yellow' => 'bg-amber-500 hover:bg-amber-600 text-white',

    'gray' => 'bg-gray-600 hover:bg-gray-700 text-white',

    'white' => 'bg-white border border-gray-300 hover:bg-gray-50 text-gray-700',

];

$sizes = [

    'sm'=>'px-3 py-2 text-xs',

    'md'=>'px-4 py-2 text-sm',

    'lg'=>'px-5 py-3 text-base',

];

@endphp

@if($href)

<a

href="{{ $href }}"

{{ $attributes->merge([

'class'=>"inline-flex items-center rounded-lg font-semibold transition shadow-sm {$colors[$color]} {$sizes[$size]}"

]) }}

>

@if($icon)

<x-dynamic-component :component="$icon" class="w-4 h-4 mr-2"/>

@endif

{{ $slot }}

</a>

@else

<button

type="{{ $type }}"

{{ $attributes->merge([

'class'=>"inline-flex items-center rounded-lg font-semibold transition shadow-sm {$colors[$color]} {$sizes[$size]}"

]) }}

>

@if($icon)

<x-dynamic-component :component="$icon" class="w-4 h-4 mr-2"/>

@endif

{{ $slot }}

</button>

@endif