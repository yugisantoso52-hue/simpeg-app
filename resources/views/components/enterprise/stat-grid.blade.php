{{--
    SIKAP Enterprise Stat Grid — wrapper grid responsif untuk <x-enterprise.stat-card>.
    Batas ideal 3–5 kartu per baris di desktop, 2 kolom di tablet, 1–2 kolom di mobile.

    Props:
      cols : 2 | 3 | 4 | 5 | 6  (6 → ditampilkan 3 per baris agar tidak sesak)
--}}
@props(['cols' => 4])

@php
    $map = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 min-[480px]:grid-cols-2',
        3 => 'grid-cols-1 min-[480px]:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 min-[480px]:grid-cols-2 lg:grid-cols-4',
        5 => 'grid-cols-1 min-[480px]:grid-cols-2 md:grid-cols-3 xl:grid-cols-5',
        6 => 'grid-cols-1 min-[480px]:grid-cols-2 lg:grid-cols-3',
    ];
    $colsClass = $map[(int) $cols] ?? $map[4];
@endphp

<div {{ $attributes->merge(['class' => 'grid gap-3 sm:gap-4 ' . $colsClass]) }}>
    {{ $slot }}
</div>
