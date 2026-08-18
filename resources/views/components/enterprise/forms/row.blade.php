@props([
    'cols' => 2,
])

@php

$class = match($cols){

    1 => 'grid grid-cols-1 gap-5',

    2 => 'grid grid-cols-1 md:grid-cols-2 gap-5',

    3 => 'grid grid-cols-1 md:grid-cols-3 gap-5',

    4 => 'grid grid-cols-1 md:grid-cols-4 gap-5',

    default => 'grid grid-cols-1 md:grid-cols-2 gap-5',

};

@endphp

<div class="{{ $class }}">

    {{ $slot }}

</div>