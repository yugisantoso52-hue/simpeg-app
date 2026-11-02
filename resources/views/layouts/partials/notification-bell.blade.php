@php
    $user = Auth::user();
    $unreadNotifications = $user ? $user->unreadNotifications()->take(8)->get() : collect();
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $totalCount = $user ? $user->notifications()->count() : 0;
@endphp

<div class="relative" x-data="{ openBell: false }" @click.outside="openBell = false" @keydown.escape.window="openBell = false">
    {{-- Trigger Bell Button --}}
    <button @click="openBell = !openBell" 
            type="button"
            class="relative inline-flex items-center justify-center p-2 rounded-full text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200 focus:outline-none transition shadow-2xs cursor-pointer active:scale-95"
            aria-label="Notifikasi & Radar Sistem"
            title="Pusat Notifikasi & Radar Sistem">
        
        {{-- Bell SVG Icon --}}
        <svg class="w-4 h-4 text-slate-700 transition group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        {{-- Unread Badge & Ping Animation --}}
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 flex h-4 min-w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full text-[9px] font-black bg-rose-600 text-white shadow-xs leading-none">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            </span>
        @endif
    </button>

    {{-- Dropdown Floating Popover --}}
    <div x-show="openBell"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
         class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white shadow-2xl border border-slate-200 z-50 overflow-hidden text-left"
         style="display: none;">
        
        {{-- Popover Header --}}
        <div class="px-4 py-3 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between border-b border-slate-700">
            <div class="flex items-center gap-2">
                <span class="text-base">🔔</span>
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-100">Notifikasi &amp; Radar</h4>
                    <p class="text-[10px] text-slate-300">Peringatan berkas &amp; alur kepegawaian</p>
                </div>
            </div>
            
            <div class="flex items-center gap-1.5">
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-400/30">
                        {{ $unreadCount }} Baru
                    </span>
                    <form action="{{ route('notifications.markAllRead') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="text-[10px] font-semibold text-slate-300 hover:text-white hover:underline transition cursor-pointer"
                                title="Tandai semua notifikasi telah dibaca">
                            Baca Semua
                        </button>
                    </form>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        Up-to-Date
                    </span>
                @endif
            </div>
        </div>

        {{-- Popover Body / Notifications List --}}
        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
            @php
                $recentList = $user ? $user->notifications()->take(6)->get() : collect();
            @endphp

            @forelse($recentList as $item)
                @php
                    $isUnread = is_null($item->read_at);
                    $data = $item->data ?? [];
                    $type = $data['type'] ?? 'system';
                    $title = $data['title'] ?? 'Pemberitahuan Sistem';
                    $message = $data['message'] ?? '-';
                    $createdAt = $item->created_at ? $item->created_at->timezone('Asia/Jakarta')->diffForHumans() : '';

                    // Icon & Color based on type
                    $badgeIcon = '🔔';
                    $badgeBg = 'bg-slate-100 text-slate-700';
                    if (str_contains($type, 'cuti')) {
                        $badgeIcon = '🏖️';
                        $badgeBg = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                    } elseif (str_contains($type, 'kgb')) {
                        $badgeIcon = '💰';
                        $badgeBg = 'bg-amber-100 text-amber-800 border-amber-200';
                    } elseif (str_contains($type, 'kp') || str_contains($type, 'pangkat')) {
                        $badgeIcon = '💼';
                        $badgeBg = 'bg-indigo-100 text-indigo-800 border-indigo-200';
                    } elseif (str_contains($type, 'str') || str_contains($type, 'sip')) {
                        $badgeIcon = '⏳';
                        $badgeBg = 'bg-rose-100 text-rose-800 border-rose-200';
                    } elseif (str_contains($type, 'logbook')) {
                        $badgeIcon = '📝';
                        $badgeBg = 'bg-sky-100 text-sky-800 border-sky-200';
                    } elseif (str_contains($type, 'satya')) {
                        $badgeIcon = '🎖️';
                        $badgeBg = 'bg-purple-100 text-purple-800 border-purple-200';
                    } elseif (str_contains($type, 'pensiun')) {
                        $badgeIcon = '⚠️';
                        $badgeBg = 'bg-orange-100 text-orange-800 border-orange-200';
                    }
                @endphp

                <a href="{{ route('notifications.readAndRedirect', $item->id) }}" 
                   class="block p-3 transition hover:bg-slate-50 relative {{ $isUnread ? 'bg-blue-50/40' : 'bg-white' }}">
                    <div class="flex items-start gap-2.5">
                        {{-- Icon Avatar --}}
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm shrink-0 border {{ $badgeBg }}">
                            {{ $badgeIcon }}
                        </div>

                        {{-- Text Snippet --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <h5 class="text-xs font-bold text-slate-900 truncate {{ $isUnread ? 'font-black text-blue-950' : '' }}">
                                    {{ $title }}
                                </h5>
                                @if($isUnread)
                                    <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0" title="Belum dibaca"></span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-600 line-clamp-2 mt-0.5 leading-snug">
                                {{ $message }}
                            </p>
                            <span class="text-[10px] text-slate-400 mt-1 block">
                                {{ $createdAt }}
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-6 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xl mx-auto mb-2">
                        ✓
                    </div>
                    <div class="text-xs font-bold text-slate-800">Semua Terpantau Aman</div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Belum ada pengingat atau peringatan baru dari sistem.</p>
                </div>
            @endforelse
        </div>

        {{-- Popover Footer --}}
        <div class="p-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-[11px] text-slate-500">
                Total: <strong>{{ $totalCount }}</strong> pemberitahuan
            </span>
            <a href="{{ route('notifications.index') }}" 
               class="font-bold text-[#007a3d] hover:text-[#005a2d] hover:underline flex items-center gap-1">
                <span>Lihat Semua Notifikasi</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>
</div>
