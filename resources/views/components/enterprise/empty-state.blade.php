@props([
    'title' => 'Belum ada data',
    'description' => 'Silakan tambahkan data baru untuk memulai.',
    'buttonText' => null,
    'buttonLink' => null,
    'icon' => 'folder',
])

<div class="rounded-xl border border-dashed border-gray-300 bg-white py-16 px-8 text-center shadow-sm">

    {{-- Icon --}}
    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-blue-50">

        @switch($icon)

            @case('users')

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-10 w-10 text-blue-600"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M17 20h5V18a4 4 0 00-5-3.87M9 20H4V18a4 4 0 015-3.87m8-6a4 4 0 11-8 0 4 4 0 018 0z"/>

                </svg>

            @break

            @case('document')

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-10 w-10 text-blue-600"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 12h6m-6 4h6M7 4h8l4 4v12H7z"/>

                </svg>

            @break

            @default

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-10 w-10 text-blue-600"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>

                </svg>

        @endswitch

    </div>

    {{-- Title --}}
    <h3 class="text-xl font-bold text-gray-800">

        {{ $title }}

    </h3>

    {{-- Description --}}
    <p class="mx-auto mt-3 max-w-xl text-gray-500">

        {{ $description }}

    </p>

    {{-- Button --}}
    @if($buttonText && $buttonLink)

        <div class="mt-8">

            <a href="{{ $buttonLink }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white shadow hover:bg-blue-700 transition">

                {{ $buttonText }}

            </a>

        </div>

    @endif

</div>