@props([
    'title' => null,
    'subtitle' => null,
    'label' => null,
    'icon' => null,
    'required' => false
])

{{-- Skenario 1: Jika dipanggil sebagai Header Card / Box Container --}}
@if($title || $subtitle || $icon)
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
            <div class="flex items-center gap-3">
                @if($icon)
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                        {!! $icon !!}
                    </div>
                @endif
                <div>
                    @if($title)
                        <h3 class="text-lg font-semibold text-slate-800">
                            {{ $title }}
                        </h3>
                    @endif
                    @if($subtitle)
                        <p class="text-sm text-slate-500">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="p-6">
            {{ $slot }}
        </div>
    </div>
@else
    {{-- Skenario 2: Jika dipanggil sebagai Wrapper Input/Field Form --}}
    <div {{ $attributes->merge(['class' => 'w-full']) }}>
        @if($label)
            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-200">
                {{ $label }}
                @if($required)
                    <span class="font-bold text-red-500">*</span>
                @endif
            </label>
        @endif

        {{-- Slot selalu di-render tanpa syarat di blok else --}}
        {{ $slot }}
    </div>
@endif