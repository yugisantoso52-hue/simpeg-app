@props([
    'title',
    'value' => 0,
    'icon' => 'users',
    'color' => 'blue',
    'href' => null,
    'active' => false,
])

@php

$colors = [
    'blue' => [
        'card' => 'bg-blue-600',
        'icon' => 'bg-blue-500/80',
        'ring' => 'ring-blue-600',
    ],

    'green' => [
        'card' => 'bg-green-600',
        'icon' => 'bg-green-500/80',
        'ring' => 'ring-green-600',
    ],

    'emerald' => [
        'card' => 'bg-emerald-600',
        'icon' => 'bg-emerald-500/80',
        'ring' => 'ring-emerald-600',
    ],

    'amber' => [
        'card' => 'bg-amber-500',
        'icon' => 'bg-amber-400/80',
        'ring' => 'ring-amber-500',
    ],

    'red' => [
        'card' => 'bg-red-600',
        'icon' => 'bg-red-500/80',
        'ring' => 'ring-red-600',
    ],

    'purple' => [
        'card' => 'bg-purple-600',
        'icon' => 'bg-purple-500/80',
        'ring' => 'ring-purple-600',
    ],

    'indigo' => [
        'card' => 'bg-indigo-600',
        'icon' => 'bg-indigo-500/80',
        'ring' => 'ring-indigo-600',
    ],
];

$theme = $colors[$color] ?? $colors['blue'];
$activeClass = $active ? 'ring-4 ring-offset-2 ' . $theme['ring'] . ' shadow-2xl z-10' : 'hover:shadow-xl';

@endphp

@if($href)
<a href="{{ $href }}" class="block h-full no-underline select-none transition-all duration-150 group focus:outline-none">
@endif

<div class="{{ $theme['card'] }} {{ $activeClass }} h-full min-h-[110px] rounded-xl shadow-md transition-shadow duration-150 relative overflow-hidden flex flex-col justify-between p-4 xl:p-5">

    {{-- Badge Absolute: Tidak merubah tinggi ataupun lebar kartu --}}
    @if($active)
        <div class="absolute top-2.5 right-2.5 flex items-center gap-1 bg-white/90 text-slate-900 text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
            <span>Aktif</span>
        </div>
    @endif

    <div class="flex items-center justify-between w-full h-full">

        <div class="pr-2 flex flex-col justify-center">

            <p class="text-xs font-bold text-white/90 uppercase tracking-wide truncate">
                {{ $title }}
            </p>

            <h2 class="mt-1 text-2xl xl:text-3xl font-extrabold text-white leading-none">
                {{ number_format($value) }}
            </h2>

        </div>

        <div class="{{ $theme['icon'] }} w-11 h-11 xl:w-13 xl:h-13 rounded-xl flex-shrink-0 flex items-center justify-center shadow-inner">

            @switch($icon)

                @case('users')

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-6 h-6 text-white"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M17 20h5V18a4 4 0 00-4-4h-1M9 20H4V18a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 4a4 4 0 00-3-3.87M5 16a4 4 0 013-3.87"/>

                    </svg>

                @break

                @case('academic')
                @case('academic-cap')

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-6 h-6 text-white"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>

                    </svg>

                @break

                @case('briefcase')

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-6 h-6 text-white"
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
                         class="w-6 h-6 text-white"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M17 20h5V18a4 4 0 00-4-4h-1M9 20H4V18a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8z"/>

                    </svg>

                @break

                @case('clipboard')

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-6 h-6 text-white"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>

                    </svg>

                @break

                @case('check-circle')

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-6 h-6 text-white"
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
                         class="w-6 h-6 text-white"
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

@if($href)
</a>
@endif