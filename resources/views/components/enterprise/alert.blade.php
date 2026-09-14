@props([

    'type' => 'info',

    'title' => null,

])

@php

$themes = [

    'success' => [

        'box' => 'bg-green-50 border-green-200',

        'icon' => 'text-green-600',

        'title' => 'text-green-800',

        'text' => 'text-green-700',

    ],

    'error' => [

        'box' => 'bg-red-50 border-red-200',

        'icon' => 'text-red-600',

        'title' => 'text-red-800',

        'text' => 'text-red-700',

    ],

    'warning' => [

        'box' => 'bg-amber-50 border-amber-200',

        'icon' => 'text-amber-600',

        'title' => 'text-amber-800',

        'text' => 'text-amber-700',

    ],

    'info' => [

        'box' => 'bg-blue-50 border-blue-200',

        'icon' => 'text-blue-600',

        'title' => 'text-blue-800',

        'text' => 'text-blue-700',

    ],

];

$theme = $themes[$type];

@endphp

<div

x-data="{show:true}"

x-show="show"

class="rounded-xl border p-4 {{ $theme['box'] }}"

>

    <div class="flex items-start justify-between">

        <div class="flex gap-3">

            <div class="{{ $theme['icon'] }}">

                @switch($type)

                    @case('success')

                        ✓

                    @break

                    @case('error')

                        ✕

                    @break

                    @case('warning')

                        ⚠

                    @break

                    @default

                        ℹ

                @endswitch

            </div>

            <div>

                @if($title)

                    <div class="font-semibold {{ $theme['title'] }}">

                        {{ $title }}

                    </div>

                @endif

                <div class="mt-1 text-sm {{ $theme['text'] }}">

                    {{ $slot }}

                </div>

            </div>

        </div>

        <button

            @click="show=false"

            class="text-gray-400 hover:text-gray-600"

        >

            ✕

        </button>

    </div>

</div>