@props([
    'label' => 'Simpan Data',
    'loadingLabel' => 'Menyimpan...',
    'color' => 'blue',
    'icon' => true,
    'type' => 'submit',
])

@php

$colors = [

    'blue' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',

    'green' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',

    'red' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',

    'yellow' => 'bg-yellow-500 hover:bg-yellow-600 focus:ring-yellow-500',

    'gray' => 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500',

];

$buttonColor = $colors[$color] ?? $colors['blue'];

@endphp

<button
    type="{{ $type }}"
    x-data="{ loading:false }"
    x-on:click="loading=true"
    x-bind:disabled="loading"
    {{ $attributes->merge([
        'class' =>
        'inline-flex items-center rounded-lg px-5 py-2.5 font-semibold text-white shadow transition duration-200 focus:outline-none focus:ring-2 '.$buttonColor
    ]) }}>

    {{-- Icon Save --}}

    @if($icon)

    <svg
        x-show="!loading"
        xmlns="http://www.w3.org/2000/svg"
        class="mr-2 h-5 w-5"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor">

        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M5 13l4 4L19 7"/>

    </svg>

    @endif

    {{-- Spinner --}}

    <svg
        x-show="loading"
        class="mr-2 h-5 w-5 animate-spin"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24">

        <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"/>

        <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>

    </svg>

    <span x-show="!loading">

        {{ $label }}

    </span>

    <span x-show="loading">

        {{ $loadingLabel }}

    </span>

</button>