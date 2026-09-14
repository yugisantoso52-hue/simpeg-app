@props([
    'title',
    'subtitle' => null,
    'back' => null,
    'backLabel' => 'Kembali',
])

<div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">

    <div>

        <h1 class="text-2xl font-bold text-gray-800">

            {{ $title }}

        </h1>

        @if($subtitle)

            <p class="mt-1 text-sm text-gray-500">

                {{ $subtitle }}

            </p>

        @endif

    </div>

    @if($back)

        <div>

            <a
                href="{{ $back }}"
                class="inline-flex items-center rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-gray-700">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="mr-2 h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7" />

                </svg>

                {{ $backLabel }}

            </a>

        </div>

    @endif

</div>