@props([
    'title',
    'value' => 0,
    'icon' => 'users',
    'color' => 'blue',
])

@php

$colors = [
    'blue' => [
        'card' => 'bg-blue-600',
        'icon' => 'bg-blue-500',
    ],

    'green' => [
        'card' => 'bg-green-600',
        'icon' => 'bg-green-500',
    ],

    'emerald' => [
        'card' => 'bg-emerald-600',
        'icon' => 'bg-emerald-500',
    ],

    'amber' => [
        'card' => 'bg-amber-500',
        'icon' => 'bg-amber-400',
    ],

    'red' => [
        'card' => 'bg-red-600',
        'icon' => 'bg-red-500',
    ],

    'purple' => [
        'card' => 'bg-purple-600',
        'icon' => 'bg-purple-500',
    ],

    'indigo' => [
        'card' => 'bg-indigo-600',
        'icon' => 'bg-indigo-500',
    ],
];

$theme = $colors[$color] ?? $colors['blue'];

@endphp

<div class="{{ $theme['card'] }} rounded-xl shadow-lg overflow-hidden">

    <div class="flex items-center justify-between p-6 min-h-[120px]">

        <div>

            <p class="text-sm text-white/80 font-medium uppercase tracking-wide">

                {{ $title }}

            </p>

            <h2 class="mt-3 text-4xl font-bold text-white">

                {{ number_format($value) }}

            </h2>

        </div>

        <div class="{{ $theme['icon'] }} w-16 h-16 rounded-xl flex items-center justify-center">

            @switch($icon)

                @case('users')

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-8 h-8 text-white"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M17 20h5V18a4 4 0 00-4-4h-1M9 20H4V18a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 4a4 4 0 00-3-3.87M5 16a4 4 0 013-3.87"/>

                    </svg>

                @break

                @case('briefcase')

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-8 h-8 text-white"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M20 13V8a2 2 0 00-2-2h-3V4a2 2 0 00-2-2h-2a2 2 0 00-2 2v2H6a2 2 0 00-2 2v5m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4"/>

                    </svg>

                @break

                @case('user-group')

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-8 h-8 text-white"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M17 20h5V18a4 4 0 00-4-4h-1M9 20H4V18a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8z"/>

                    </svg>

                @break

                @case('check-circle')

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-8 h-8 text-white"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12l2 2 4-4m6 2A9 9 0 1112 3a9 9 0 019 9z"/>

                    </svg>

                @break

                @default

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-8 h-8 text-white"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <circle cx="12"
                                cy="12"
                                r="9"
                                stroke-width="2"/>

                    </svg>

            @endswitch

        </div>

    </div>

</div>