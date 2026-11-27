{{--
    ==========================================================================
    SIKAP Enterprise Stat Card (Kartu Statistik Terpusat)
    --------------------------------------------------------------------------
    Desain minimalis: latar putih, border tipis, bayangan halus. Warna hanya
    dipakai sebagai AKSEN (wadah ikon pastel, border saat aktif, tint lembut).

    Props:
      title / label   : Label kategori (teks kecil abu-abu netral)
      value           : Angka utama (numerik otomatis diformat 1.234)
      suffix          : Satuan kecil setelah angka (mis. "Orang", "%", "Hari")
      icon            : Nama ikon <x-icon> (atau slot "icon" untuk emoji/SVG)
      color           : blue|sky|cyan|teal|emerald|green|indigo|violet|purple|
                        fuchsia|pink|rose|red|orange|amber|yellow|slate
      subtitle        : Keterangan kecil di bawah angka
      trend           : Teks pill ringkas (mis. "+12%", "45% dari total")
      trendDirection  : up | down | neutral (warna pill tren)
      href            : Jadikan kartu tautan (filter server-side / navigasi)
      active          : Kondisi terpilih (server-side, boolean)
      activeWhen      : Ekspresi Alpine.js untuk kondisi terpilih client-side
      interactive     : Paksa efek hover/klik (mis. kartu dengan x-on:click)
      decimals        : Jumlah desimal untuk angka (default otomatis)
      valueText       : Ekspresi Alpine.js (x-text) untuk angka dinamis client-side

    Slot default     : Konten tambahan opsional (progress bar, rincian kecil)
    Slot iconSlot    : Ganti ikon dengan emoji/SVG kustom (<x-slot:iconSlot>)
    ==========================================================================
--}}
@props([
    'title' => null,
    'label' => null,
    'value' => 0,
    'suffix' => null,
    'icon' => 'chart',
    'color' => 'blue',
    'subtitle' => null,
    'trend' => null,
    'trendDirection' => 'neutral',
    'href' => null,
    'active' => false,
    'activeWhen' => null,
    'interactive' => false,
    'decimals' => null,
    'valueText' => null,
])

@php
    $palette = [
        'blue'    => ['badge' => 'bg-blue-50 text-blue-600 ring-blue-100',       'border' => 'border-blue-500',    'tint' => 'bg-blue-50/40',    'ring' => 'ring-blue-500/15',    'dot' => 'bg-blue-500',    'pill' => 'bg-blue-50 text-blue-700 ring-blue-200',       'hover' => 'hover:border-blue-300'],
        'sky'     => ['badge' => 'bg-sky-50 text-sky-600 ring-sky-100',          'border' => 'border-sky-500',     'tint' => 'bg-sky-50/40',     'ring' => 'ring-sky-500/15',     'dot' => 'bg-sky-500',     'pill' => 'bg-sky-50 text-sky-700 ring-sky-200',          'hover' => 'hover:border-sky-300'],
        'cyan'    => ['badge' => 'bg-cyan-50 text-cyan-600 ring-cyan-100',       'border' => 'border-cyan-500',    'tint' => 'bg-cyan-50/40',    'ring' => 'ring-cyan-500/15',    'dot' => 'bg-cyan-500',    'pill' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',       'hover' => 'hover:border-cyan-300'],
        'teal'    => ['badge' => 'bg-teal-50 text-teal-600 ring-teal-100',       'border' => 'border-teal-500',    'tint' => 'bg-teal-50/40',    'ring' => 'ring-teal-500/15',    'dot' => 'bg-teal-500',    'pill' => 'bg-teal-50 text-teal-700 ring-teal-200',       'hover' => 'hover:border-teal-300'],
        'emerald' => ['badge' => 'bg-emerald-50 text-emerald-600 ring-emerald-100', 'border' => 'border-emerald-500', 'tint' => 'bg-emerald-50/40', 'ring' => 'ring-emerald-500/15', 'dot' => 'bg-emerald-500', 'pill' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'hover' => 'hover:border-emerald-300'],
        'green'   => ['badge' => 'bg-green-50 text-green-600 ring-green-100',    'border' => 'border-green-500',   'tint' => 'bg-green-50/40',   'ring' => 'ring-green-500/15',   'dot' => 'bg-green-500',   'pill' => 'bg-green-50 text-green-700 ring-green-200',    'hover' => 'hover:border-green-300'],
        'indigo'  => ['badge' => 'bg-indigo-50 text-indigo-600 ring-indigo-100', 'border' => 'border-indigo-500',  'tint' => 'bg-indigo-50/40',  'ring' => 'ring-indigo-500/15',  'dot' => 'bg-indigo-500',  'pill' => 'bg-indigo-50 text-indigo-700 ring-indigo-200', 'hover' => 'hover:border-indigo-300'],
        'violet'  => ['badge' => 'bg-violet-50 text-violet-600 ring-violet-100', 'border' => 'border-violet-500',  'tint' => 'bg-violet-50/40',  'ring' => 'ring-violet-500/15',  'dot' => 'bg-violet-500',  'pill' => 'bg-violet-50 text-violet-700 ring-violet-200', 'hover' => 'hover:border-violet-300'],
        'purple'  => ['badge' => 'bg-purple-50 text-purple-600 ring-purple-100', 'border' => 'border-purple-500',  'tint' => 'bg-purple-50/40',  'ring' => 'ring-purple-500/15',  'dot' => 'bg-purple-500',  'pill' => 'bg-purple-50 text-purple-700 ring-purple-200', 'hover' => 'hover:border-purple-300'],
        'fuchsia' => ['badge' => 'bg-fuchsia-50 text-fuchsia-600 ring-fuchsia-100', 'border' => 'border-fuchsia-500', 'tint' => 'bg-fuchsia-50/40', 'ring' => 'ring-fuchsia-500/15', 'dot' => 'bg-fuchsia-500', 'pill' => 'bg-fuchsia-50 text-fuchsia-700 ring-fuchsia-200', 'hover' => 'hover:border-fuchsia-300'],
        'pink'    => ['badge' => 'bg-pink-50 text-pink-600 ring-pink-100',       'border' => 'border-pink-500',    'tint' => 'bg-pink-50/40',    'ring' => 'ring-pink-500/15',    'dot' => 'bg-pink-500',    'pill' => 'bg-pink-50 text-pink-700 ring-pink-200',       'hover' => 'hover:border-pink-300'],
        'rose'    => ['badge' => 'bg-rose-50 text-rose-600 ring-rose-100',       'border' => 'border-rose-500',    'tint' => 'bg-rose-50/40',    'ring' => 'ring-rose-500/15',    'dot' => 'bg-rose-500',    'pill' => 'bg-rose-50 text-rose-700 ring-rose-200',       'hover' => 'hover:border-rose-300'],
        'red'     => ['badge' => 'bg-red-50 text-red-600 ring-red-100',          'border' => 'border-red-500',     'tint' => 'bg-red-50/40',     'ring' => 'ring-red-500/15',     'dot' => 'bg-red-500',     'pill' => 'bg-red-50 text-red-700 ring-red-200',          'hover' => 'hover:border-red-300'],
        'orange'  => ['badge' => 'bg-orange-50 text-orange-600 ring-orange-100', 'border' => 'border-orange-500',  'tint' => 'bg-orange-50/40',  'ring' => 'ring-orange-500/15',  'dot' => 'bg-orange-500',  'pill' => 'bg-orange-50 text-orange-700 ring-orange-200', 'hover' => 'hover:border-orange-300'],
        'amber'   => ['badge' => 'bg-amber-50 text-amber-600 ring-amber-100',    'border' => 'border-amber-500',   'tint' => 'bg-amber-50/40',   'ring' => 'ring-amber-500/15',   'dot' => 'bg-amber-500',   'pill' => 'bg-amber-50 text-amber-700 ring-amber-200',    'hover' => 'hover:border-amber-300'],
        'yellow'  => ['badge' => 'bg-yellow-50 text-yellow-600 ring-yellow-100', 'border' => 'border-yellow-500',  'tint' => 'bg-yellow-50/40',  'ring' => 'ring-yellow-500/15',  'dot' => 'bg-yellow-500',  'pill' => 'bg-yellow-50 text-yellow-700 ring-yellow-200', 'hover' => 'hover:border-yellow-300'],
        'slate'   => ['badge' => 'bg-slate-100 text-slate-600 ring-slate-200',   'border' => 'border-slate-500',   'tint' => 'bg-slate-50/60',   'ring' => 'ring-slate-500/15',   'dot' => 'bg-slate-500',   'pill' => 'bg-slate-100 text-slate-700 ring-slate-200',   'hover' => 'hover:border-slate-300'],
    ];
    $aliases = ['gray' => 'slate', 'grey' => 'slate', 'zinc' => 'slate', 'neutral' => 'slate', 'lime' => 'green'];
    $color = $aliases[$color] ?? $color;
    $theme = $palette[$color] ?? $palette['blue'];

    // Normalisasi nama ikon lama ke pustaka <x-icon>
    $iconAliases = [
        'academic' => 'graduation-cap', 'academic-cap' => 'graduation-cap',
        'clipboard' => 'clipboard-list', 'user-group' => 'users',
        'document' => 'file-text', 'document-text' => 'file-text',
        'x' => 'x-circle', 'exclamation' => 'alert-triangle', 'clock-history' => 'history',
    ];
    $iconName = $iconAliases[$icon] ?? $icon;

    $label = $title ?? $label ?? '';

    // Format angka utama (gaya Indonesia: 1.234 / 12,5)
    if (is_numeric($value)) {
        $num = $value + 0;
        $dec = $decimals ?? ((is_float($num) && floor($num) != $num) ? 1 : 0);
        $display = number_format($num, $dec, ',', '.');
    } else {
        $display = $value;
    }

    $trendStyles = [
        'up' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'down' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'neutral' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
    $trendClass = $trendStyles[$trendDirection] ?? $trendStyles['neutral'];

    $isClickable = $href || $interactive || $activeWhen
        || $attributes->has('x-on:click') || $attributes->has('@click') || $attributes->has('onclick');

    $base = 'group relative flex h-full w-full flex-col rounded-2xl border bg-white p-4 sm:p-5 text-left shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-slate-300';
    $activeCls = $theme['border'] . ' ' . $theme['tint'] . ' ring-4 ' . $theme['ring'];
    $idleCls = 'border-slate-200/80' . ($isClickable ? ' ' . $theme['hover'] . ' hover:-translate-y-0.5 hover:shadow-[0_6px_16px_-6px_rgba(15,23,42,0.12)]' : '');
    $stateCls = $activeWhen ? '' : ($active ? $activeCls : $idleCls);

    $tag = $href ? 'a' : ($isClickable ? 'button' : 'div');
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    @if($tag === 'button') type="button" @endif
    @if($activeWhen) x-bind:class="({{ $activeWhen }}) ? '{{ $activeCls }}' : '{{ $idleCls }}'" @endif
    {{ $attributes->merge(['class' => trim($base . ' ' . $stateCls . ($isClickable ? ' cursor-pointer select-none no-underline' : ''))]) }}
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-slate-500 leading-snug break-words notranslate" translate="no">
                {{ $label }}
            </p>
            <p class="mt-2 flex items-baseline gap-1 text-2xl sm:text-[1.75rem] font-bold tracking-tight leading-none text-slate-900 tabular-nums">
                @if($valueText)
                    <span class="truncate" x-text="{{ $valueText }}">{{ $display }}</span>
                @else
                    <span class="truncate">{{ $display }}</span>
                @endif
                @if($suffix)
                    <span class="text-xs sm:text-sm font-semibold text-slate-400 tracking-normal">{{ $suffix }}</span>
                @endif
            </p>
        </div>

        <div class="flex h-10 w-10 sm:h-11 sm:w-11 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset {{ $theme['badge'] }}">
            @isset($iconSlot)
                {{ $iconSlot }}
            @else
                <x-icon :name="$iconName" class="w-5 h-5" stroke-width="2" />
            @endisset
        </div>
    </div>

    @if($subtitle || $trend || $active || $activeWhen)
        <div class="mt-3 flex flex-wrap items-center gap-1.5 text-[11px] leading-tight">
            @if($trend)
                <span class="inline-flex items-center rounded-full px-2 py-0.5 font-semibold ring-1 ring-inset {{ $trendClass }}">{{ $trend }}</span>
            @endif
            @if($subtitle)
                <span class="text-slate-500">{{ $subtitle }}</span>
            @endif
            @if($active && !$activeWhen)
                <span class="ml-auto inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset {{ $theme['pill'] }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $theme['dot'] }}"></span> Terpilih
                </span>
            @elseif($activeWhen)
                <span x-cloak x-show="{{ $activeWhen }}" class="ml-auto inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset {{ $theme['pill'] }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $theme['dot'] }}"></span> Terpilih
                </span>
            @endif
        </div>
    @endif

    @if(trim($slot) !== '')
        <div class="mt-3">{{ $slot }}</div>
    @endif
</{{ $tag }}>
