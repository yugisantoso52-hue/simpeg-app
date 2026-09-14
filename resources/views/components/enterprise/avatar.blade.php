@props([
    'src' => null,
    'name' => '',
    'size' => 'md',
])

@php

$sizes = [

    'sm' => 'w-10 h-10 text-sm',

    'md' => 'w-12 h-12 text-base',

    'lg' => 'w-16 h-16 text-lg',

];

$class = $sizes[$size] ?? $sizes['md'];

$initial = collect(explode(' ', trim($name)))
            ->map(fn($x)=>mb_substr($x,0,1))
            ->take(2)
            ->implode('');

@endphp

@if($src)

<img
    src="{{ $src }}"
    alt="{{ $name }}"
    class="{{ $class }} rounded-full object-cover border-2 border-white shadow"
/>

@else

<div
    class="{{ $class }} rounded-full bg-blue-600 text-white font-bold flex items-center justify-center shadow">

    {{ strtoupper($initial) }}

</div>

@endif