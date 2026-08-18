<div x-data="{ open: false }" class="relative">
    <!-- Tombol Lonceng -->
    <button @click="open = !open" class="relative p-2 text-gray-500 rounded-full hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Badge Jumlah Unread -->
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute top-0 right-0 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" 
         @click.away="open = false" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 z-50 w-80 mt-2 bg-white rounded-lg shadow-lg border border-gray-100 overflow-hidden" 
         style="display: none;">
        
        <div class="p-3 bg-gray-50 border-b flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-700">Notifikasi</h3>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.markAllRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-indigo-600 hover:underline">Tandai Dibaca</button>
                </form>
            @endif
        </div>

        <div class="max-h-64 overflow-y-auto divide-y divide-gray-100">
            @forelse(auth()->user()->unreadNotifications as $notification)
                <a href="{{ $notification->data['url'] ?? '#' }}" class="block p-3 hover:bg-gray-50 transition">
                    <p class="text-xs font-bold text-gray-800">{{ $notification->data['title'] ?? 'Notifikasi' }}</p>
                    <p class="text-xs text-gray-600 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                    <span class="text-[10px] text-gray-400 mt-2 block">{{ $notification->created_at->diffForHumans() }}</span>
                </a>
            @empty
                <div class="p-4 text-center text-xs text-gray-500">
                    Tidak ada notifikasi baru.
                </div>
            @endforelse
        </div>
    </div>
</div>