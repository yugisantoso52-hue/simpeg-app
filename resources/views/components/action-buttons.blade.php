@props([
    'editUrl' => null,
    'deleteUrl' => null,
    'detailUrl' => null,
    'confirmMessage' => 'Apakah Anda yakin ingin menghapus data ini?'
])

<div class="flex items-center justify-center space-x-1.5">
    {{-- Tombol Detail / Lihat --}}
    @if($detailUrl)
        <a href="{{ $detailUrl }}" 
           class="inline-flex items-center justify-center p-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-700 focus:ring-2 focus:ring-blue-300 transition-all duration-150 shadow-sm"
           title="Lihat Detail">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </a>
    @endif

    {{-- Tombol Edit --}}
    @if($editUrl)
        <a href="{{ $editUrl }}" 
           class="inline-flex items-center justify-center p-2 text-sm font-medium text-amber-600 bg-amber-50 rounded-lg hover:bg-amber-100 hover:text-amber-700 focus:ring-2 focus:ring-amber-300 transition-all duration-150 shadow-sm"
           title="Ubah Data">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
        </a>
    @endif

    {{-- Tombol Hapus --}}
    @if($deleteUrl)
        <form action="{{ $deleteUrl }}" method="POST" class="inline-block" onsubmit="return confirm('{{ $confirmMessage }}')">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="inline-flex items-center justify-center p-2 text-sm font-medium text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 hover:text-rose-700 focus:ring-2 focus:ring-rose-300 transition-all duration-150 shadow-sm"
                    title="Hapus Data">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </form>
    @endif
</div>