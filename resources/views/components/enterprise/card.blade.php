@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
    'footer' => null,
])

<div
    {{ $attributes->merge([
        'class' => 'bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden'
    ]) }}>

    @if($title || $subtitle)

        <div class="border-b border-gray-100 px-6 py-5">

            @if($title)

                <h3 class="text-lg font-semibold text-gray-800">

                    {{ $title }}

                </h3>

            @endif

            @if($subtitle)

                <p class="mt-1 text-sm text-gray-500">

                    {{ $subtitle }}

                </p>

            @endif

        </div>

    @endif

    <div class="{{ $padding ? 'p-6' : '' }}">

        {{ $slot }}

    </div>

    @if($footer)

        <div class="border-t border-gray-100 bg-gray-50 px-6 py-4">

            {{ $footer }}

        </div>

    @endif

</div>