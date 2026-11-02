<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight flex items-center gap-2">
                    <span>🔔</span> {{ __('Pusat Notifikasi & Radar Sistem') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Riwayat peringatan berkas, alur pengesahan berjenjang, dan pengingat hak kepegawaian Anda.
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(($unreadCount ?? 0) > 0)
                    <form action="{{ route('notifications.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                            <span>✓</span>
                            <span>Tandai Semua Dibaca</span>
                        </button>
                    </form>
                @endif
                <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold transition shadow-2xs">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            {{-- 3 Ringkasan Statistik Notifikasi --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('notifications.index') }}" 
                   class="p-4 rounded-2xl bg-white border {{ ($filter ?? 'all') === 'all' ? 'border-[#007a3d] ring-2 ring-[#007a3d]/20' : 'border-slate-200' }} shadow-xs hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Notifikasi</div>
                        <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalCount ?? 0 }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Semua riwayat pemberitahuan</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl font-bold">
                        📋
                    </div>
                </a>

                <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                   class="p-4 rounded-2xl bg-white border {{ ($filter ?? '') === 'unread' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-200' }} shadow-xs hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-rose-600 uppercase tracking-wider">Belum Dibaca</div>
                        <div class="text-2xl font-black text-rose-600 mt-1">{{ $unreadCount ?? 0 }}</div>
                        <div class="text-[11px] text-rose-500 mt-0.5">Memerlukan perhatian Anda</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                        🔔
                    </div>
                </a>

                <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
                   class="p-4 rounded-2xl bg-white border {{ ($filter ?? '') === 'read' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200' }} shadow-xs hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Sudah Dibaca</div>
                        <div class="text-2xl font-black text-emerald-600 mt-1">{{ max(0, ($totalCount ?? 0) - ($unreadCount ?? 0)) }}</div>
                        <div class="text-[11px] text-emerald-500 mt-0.5">Arsip notifikasi terselesaikan</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        ✓
                    </div>
                </a>
            </div>

            {{-- Filter Tabs & Toolbar --}}
            <div class="flex items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center gap-1.5 text-xs">
                    <a href="{{ route('notifications.index') }}" 
                       class="px-3 py-1.5 rounded-lg font-bold transition {{ ($filter ?? 'all') === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        Semua ({{ $totalCount ?? 0 }})
                    </a>
                    <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                       class="px-3 py-1.5 rounded-lg font-bold transition {{ ($filter ?? '') === 'unread' ? 'bg-rose-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        Belum Dibaca ({{ $unreadCount ?? 0 }})
                    </a>
                    <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
                       class="px-3 py-1.5 rounded-lg font-bold transition {{ ($filter ?? '') === 'read' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        Sudah Dibaca
                    </a>
                </div>

                @if(($unreadCount ?? 0) > 0)
                    <form action="{{ route('notifications.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-bold hover:underline cursor-pointer">
                            Tandai Semua Dibaca &check;
                        </button>
                    </form>
                @endif
            </div>

            {{-- Daftar Notifikasi --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden divide-y divide-slate-100">
                @forelse($notifications as $item)
                    @php
                        $isUnread = is_null($item->read_at);
                        $data = $item->data ?? [];
                        $type = $data['type'] ?? 'system';
                        $title = $data['title'] ?? 'Pemberitahuan Sistem';
                        $message = $data['message'] ?? '-';
                        $createdAt = $item->created_at ? $item->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : '';
                        $relativeTime = $item->created_at ? $item->created_at->timezone('Asia/Jakarta')->diffForHumans() : '';

                        // Icon & Color styling
                        $badgeIcon = '🔔';
                        $badgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
                        if (str_contains($type, 'cuti')) {
                            $badgeIcon = '🏖️';
                            $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                        } elseif (str_contains($type, 'kgb')) {
                            $badgeIcon = '💰';
                            $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
                        } elseif (str_contains($type, 'kp') || str_contains($type, 'pangkat')) {
                            $badgeIcon = '💼';
                            $badgeClass = 'bg-indigo-100 text-indigo-800 border-indigo-200';
                        } elseif (str_contains($type, 'str') || str_contains($type, 'sip')) {
                            $badgeIcon = '⏳';
                            $badgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
                        } elseif (str_contains($type, 'logbook')) {
                            $badgeIcon = '📝';
                            $badgeClass = 'bg-sky-100 text-sky-800 border-sky-200';
                        } elseif (str_contains($type, 'satya')) {
                            $badgeIcon = '🎖️';
                            $badgeClass = 'bg-purple-100 text-purple-800 border-purple-200';
                        } elseif (str_contains($type, 'pensiun')) {
                            $badgeIcon = '⚠️';
                            $badgeClass = 'bg-orange-100 text-orange-800 border-orange-200';
                        }
                    @endphp

                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition hover:bg-slate-50/80 {{ $isUnread ? 'bg-blue-50/30' : 'bg-white' }}">
                        <div class="flex items-start gap-3.5 flex-1 min-w-0">
                            {{-- Icon Avatar --}}
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-lg shrink-0 border {{ $badgeClass }} shadow-2xs">
                                {{ $badgeIcon }}
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-slate-900 truncate {{ $isUnread ? 'font-black text-blue-950' : '' }}">
                                        {{ $title }}
                                    </h4>
                                    @if($isUnread)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Baru</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    {{ $message }}
                                </p>
                                <div class="flex items-center gap-2 mt-2 text-[11px] text-slate-400 font-medium">
                                    <span>🕒 {{ $relativeTime }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $createdAt }} WIB</span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                            <a href="{{ route('notifications.readAndRedirect', $item->id) }}" 
                               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition shadow-2xs {{ $isUnread ? 'bg-[#007a3d] hover:bg-[#005a2d] text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                                Buka Berkas &rarr;
                            </a>

                            <form action="{{ route('notifications.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Hapus Notifikasi">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-12 px-4 text-center">
                        <div class="w-16 h-16 rounded-3xl bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-3">
                            🔔
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm">Tidak Ada Notifikasi</h4>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                            Saat ini belum ada notifikasi untuk kategori yang dipilih. Semua radar kepegawaian Anda terpantau aman.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination Links --}}
            @if($notifications->hasPages())
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
