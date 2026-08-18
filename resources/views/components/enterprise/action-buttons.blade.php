@props([
    'show' => null,
    'edit' => null,
    'delete' => null,

    'showLabel' => true,   {{-- Diubah jadi true --}}
    'editLabel' => true,   {{-- Diubah jadi true --}}
    'deleteLabel' => true, {{-- Diubah jadi true --}}

    'size' => 'sm',        {{-- Diubah ke 'sm' agar ukurannya pas & tidak kebesaran --}}

    'confirm' => 'Apakah Anda yakin ingin menghapus data ini?',
])

@php
$buttonSize = match($size) {
    'sm' => 'px-2 py-1 text-xs',
    'lg' => 'px-4 py-2 text-sm',
    default => 'px-3 py-1.5 text-sm',
};
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }}>

    {{-- DETAIL --}}
    @if($show)
        <a
            href="{{ $show }}"
            class="{{ $buttonSize }} inline-flex items-center gap-1.5 rounded-lg bg-sky-600 font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition"
            title="Detail"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7-8.268 0-9.542-7-9.542-7z" />
            </svg>

            @if($showLabel)
                <span>Detail</span>
            @endif
        </a>
    @endif

    {{-- EDIT --}}
    @if($edit)
        <a
            href="{{ $edit }}"
            class="{{ $buttonSize }} inline-flex items-center gap-1.5 rounded-lg bg-amber-500 font-medium text-white hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition"
            title="Edit"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>

            @if($editLabel)
                <span>Edit</span>
            @endif
        </a>
    @endif

    {{-- DELETE --}}
    @if($delete)
        <form
            method="POST"
            action="{{ $delete }}"
            class="inline"
            x-data
        >
            @csrf
            @method('DELETE')

            <button
                type="submit"
                @click.prevent="if (confirm(@js($confirm))) $el.closest('form').submit()"
                class="{{ $buttonSize }} inline-flex items-center gap-1.5 rounded-lg bg-red-600 font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
                title="Hapus"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>

                @if($deleteLabel)
                    <span>Hapus</span>
                @endif
            </button>
        </form>
    @endif

</div>