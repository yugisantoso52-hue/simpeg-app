@props([
    'title' => 'Panduan & Prosedur Operasional Standar (SOP)',
    'module' => '',
    'buttonLabel' => 'Panduan & SOP',
    'badge' => 'SOP Resmi',
    'color' => 'indigo' // emerald, blue, indigo, rose, amber
])

@php
    $theme = match($color) {
        'emerald' => [
            'btn' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-200',
            'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'header' => 'from-emerald-700 to-teal-800',
            'accent' => 'text-emerald-600',
        ],
        'blue' => [
            'btn' => 'bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-200',
            'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
            'header' => 'from-blue-700 to-indigo-800',
            'accent' => 'text-blue-600',
        ],
        'rose' => [
            'btn' => 'bg-rose-50 text-rose-700 hover:bg-rose-100 border-rose-200',
            'badge' => 'bg-rose-100 text-rose-800 border-rose-200',
            'header' => 'from-rose-700 to-pink-800',
            'accent' => 'text-rose-600',
        ],
        'amber' => [
            'btn' => 'bg-amber-50 text-amber-800 hover:bg-amber-100 border-amber-200',
            'badge' => 'bg-amber-100 text-amber-900 border-amber-200',
            'header' => 'from-amber-700 to-orange-800',
            'accent' => 'text-amber-600',
        ],
        default => [
            'btn' => 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border-indigo-200',
            'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'header' => 'from-indigo-800 to-blue-900',
            'accent' => 'text-indigo-600',
        ]
    };
@endphp

<div x-data="{ open: false }" class="inline-block">
    <!-- Tombol Trigger SOP -->
    <button type="button" 
            @click="open = true" 
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl border shadow-2xs transition-all duration-150 cursor-pointer {{ $theme['btn'] }}">
        <x-icon name="help-circle" class="w-4 h-4 shrink-0 stroke-[2]" />
        <span>{{ $buttonLabel }}</span>
    </button>

    <!-- Modal Pop-Up Dialog SOP (Teleport to Body) -->
    <template x-teleport="body">
        <div x-show="open" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <!-- Backdrop Gelap Blur -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="open = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
                <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden text-left"
                     @click.outside="open = false">
                    
                    <!-- Header Modal SOP -->
                    <div class="bg-gradient-to-r {{ $theme['header'] }} px-6 py-5 text-white">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                                    <x-icon name="book-open" class="w-5 h-5 text-white stroke-[2]" />
                                </div>
                                <div>
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-white/20 text-white tracking-wider uppercase mb-1">
                                        {{ $badge }}
                                    </span>
                                    <h3 class="text-base sm:text-lg font-black tracking-tight text-white leading-tight">
                                        {{ $title }}
                                    </h3>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="open = false" 
                                    class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-1.5 rounded-xl transition cursor-pointer">
                                <span class="sr-only">Tutup</span>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Body Modal SOP -->
                    <div class="p-6 max-h-[75vh] overflow-y-auto space-y-5 text-xs text-slate-700 leading-relaxed">
                        {{ $slot }}
                    </div>

                    <!-- Footer Modal SOP -->
                    <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Standar Layanan Kepegawaian FKP UNRI
                        </span>
                        <button type="button" 
                                @click="open = false" 
                                class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition cursor-pointer shadow-xs">
                            Saya Mengerti &rarr;
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </template>
</div>
