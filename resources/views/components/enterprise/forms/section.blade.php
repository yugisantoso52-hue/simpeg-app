@props([
    'title' => null,
    'description' => null,
    'icon' => null,
])

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
    @if($title || $description || $icon)
        <div class="border-b bg-slate-50 px-6 py-4">
            <div class="flex items-start gap-3">
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
                    @if($description)
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $description }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>
</div>