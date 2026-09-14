@props([
    'colspan' => 1,
])

@php
$span = match($colspan){
    2 => 'md:col-span-2',
    3 => 'md:col-span-3',
    4 => 'md:col-span-4',
    default => '',
};
@endphp

<div {{ $attributes->merge(['class' => $span]) }}>
    {{ $slot }}
</div>