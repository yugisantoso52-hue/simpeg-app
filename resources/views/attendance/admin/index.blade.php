<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>📊</span> {{ __('Rekap Presensi Pegawai') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">Monitoring kehadiran, verifikasi foto selfie, dan radius koordinat GPS.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.presensi.export.excel', request()->query()) }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 border border-transparent rounded-lg text-xs font-semibold text-white hover:bg-emerald-700 shadow-sm transition">
                    📊 Export Excel
                </a>
                <a href="{{ route('admin.presensi.export.pdf', request()->query()) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-rose-600 border border-transparent rounded-lg text-xs font-semibold text-white hover:bg-rose-700 shadow-sm transition">
                    🖨️ Cetak PDF
                </a>
                <a href="{{ route('admin.presensi.locations') }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-lg text-xs font-semibold text-white hover:bg-indigo-700 shadow-sm transition">
                    📍 Kelola Titik Acuan
                </a>
                <a href="{{ route('presensi.index') }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                    📸 Presensi Mandiri
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ modalOpen: false, modalImgSrc: '', modalTitle: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if(session('success'))
                <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm flex items-center justify-between">
                    <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
                </div>
            @endif

            <!-- KPI Cards Statistik Hari Ini -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total User/Pegawai</div>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $statistics['total_users'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Terdaftar dalam sistem</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Hadir Hari Ini</div>
                    <div class="text-2xl font-black text-emerald-600 mt-1">{{ $statistics['total_present'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::today()->translatedFormat('d M Y') }}</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">WFO (Kantor)</div>
                    <div class="text-2xl font-black text-blue-600 mt-1">{{ $statistics['total_wfo'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Pegawai presensi kantor</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">WFH (Rumah)</div>
                    <div class="text-2xl font-black text-indigo-600 mt-1">{{ $statistics['total_wfh'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Pegawai presensi WFH</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm col-span-2 sm:col-span-1">
                    <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Terlambat</div>
                    <div class="text-2xl font-black text-amber-600 mt-1">{{ $statistics['total_late'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Check-in > 08:00 WIB</div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('admin.presensi.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        <!-- Tanggal Spesifik -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Spesifik:</label>
                            <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Tipe Presensi -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tipe Presensi:</label>
                            <select name="attendance_type" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Semua Tipe --</option>
                                <option value="wfo" {{ request('attendance_type') === 'wfo' ? 'selected' : '' }}>WFO (Kantor)</option>
                                <option value="wfh" {{ request('attendance_type') === 'wfh' ? 'selected' : '' }}>WFH (Rumah)</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Status Kehadiran:</label>
                            <select name="status" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Semua Status --</option>
                                <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Tepat Waktu</option>
                                <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
                            </select>
                        </div>

                        <!-- Pencarian Nama / NIP -->
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Cari Nama Pegawai / NIP:</label>
                            <div class="flex gap-2">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama atau NIP..." class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold transition shrink-0">
                                    Cari
                                </button>
                                <a href="{{ route('admin.presensi.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition shrink-0">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabel Data Rekap Presensi -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/50">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-gray-800 text-sm">📋 Rekap Presensi Karyawan</span>
                        <span class="px-2 py-0.5 text-[11px] font-semibold bg-blue-100 text-blue-700 rounded-full">
                            Total: {{ $attendances->total() }} Data
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.presensi.export.excel', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <span>📊</span> Unduh Excel
                        </a>
                        <a href="{{ route('admin.presensi.export.pdf', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <span>🖨️</span> Cetak PDF
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                        <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Pegawai</th>
                                <th class="px-4 py-3">Tanggal & Waktu</th>
                                <th class="px-4 py-3">Tipe</th>
                                <th class="px-4 py-3">Jarak GPS</th>
                                <th class="px-4 py-3">Koordinat Masuk</th>
                                <th class="px-4 py-3 text-center">Foto Masuk</th>
                                <th class="px-4 py-3 text-center">Foto Pulang</th>
                                <th class="px-4 py-3">Total Jam Kerja (Status)</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($attendances as $item)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900">{{ $item->user?->name ?? 'User #' . $item->user_id }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono">
                                            {{ $item->user?->pegawai?->nip ?? $item->user?->email ?? '-' }}
                                        </div>
                                        @if($item->user?->pegawai?->unitKerja)
                                            <div class="text-[10px] text-gray-400">
                                                {{ $item->user?->pegawai?->unitKerja?->nama_unit ?? '' }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-gray-800">{{ $item->attendance_date ? $item->attendance_date->translatedFormat('d/m/Y') : '-' }}</div>
                                        <div class="text-[11px] text-emerald-700 font-mono">
                                            Masuk: {{ $item->check_in_time ? $item->check_in_time->timezone('Asia/Jakarta')->format('H:i:s') . ' WIB' : '-' }}
                                        </div>
                                        <div class="text-[11px] text-amber-700 font-mono">
                                            Pulang: {{ $item->check_out_time ? $item->check_out_time->timezone('Asia/Jakarta')->format('H:i:s') . ' WIB' : '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] uppercase {{ ($item->attendance_type ?? '') === 'wfh' ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-blue-100 text-blue-700 border border-blue-200' }}">
                                            {{ strtoupper($item->attendance_type ?? 'wfo') }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap font-mono">
                                        <div class="flex items-center gap-1 font-semibold {{ $item->check_in_distance_meters <= 75 ? 'text-emerald-700' : 'text-red-600' }}">
                                            <span>{{ number_format($item->check_in_distance_meters, 1) }} m</span>
                                            @if($item->check_in_distance_meters <= 75)
                                                <span class="text-[10px] text-emerald-600">✓</span>
                                            @else
                                                <span class="text-[10px] text-red-600 font-bold">!</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 font-mono text-[11px] text-gray-500 whitespace-nowrap">
                                        {{ number_format($item->check_in_latitude, 5) }},<br>{{ number_format($item->check_in_longitude, 5) }}
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        @if($item->check_in_photo_path)
                                            <button type="button"
                                                    @click="modalOpen = true; modalImgSrc = '{{ $item->check_in_photo_url }}'; modalTitle = 'Foto Selfie Masuk - {{ addslashes($item->user?->name ?? 'User') }} ({{ $item->check_in_time ? $item->check_in_time->timezone('Asia/Jakarta')->format('H:i') : '' }})'"
                                                    class="inline-block w-10 h-10 rounded-lg overflow-hidden border border-gray-300 hover:ring-2 hover:ring-blue-500 shadow-sm transition">
                                                <img src="{{ $item->check_in_photo_url }}" class="w-full h-full object-cover" alt="Foto Masuk" loading="lazy">
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        @if($item->check_out_photo_path)
                                            <button type="button"
                                                    @click="modalOpen = true; modalImgSrc = '{{ $item->check_out_photo_url }}'; modalTitle = 'Foto Selfie Pulang - {{ addslashes($item->user?->name ?? 'User') }} ({{ $item->check_out_time ? $item->check_out_time->timezone('Asia/Jakarta')->format('H:i') : '' }})'"
                                                    class="inline-block w-10 h-10 rounded-lg overflow-hidden border border-gray-300 hover:ring-2 hover:ring-amber-500 shadow-sm transition">
                                                <img src="{{ $item->check_out_photo_url }}" class="w-full h-full object-cover" alt="Foto Pulang" loading="lazy">
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-bold text-gray-900 flex items-center gap-1.5 text-xs">
                                            <span class="text-slate-500">⏱️</span>
                                            <span>{{ $item->work_duration }}</span>
                                        </div>
                                        <div class="mt-1">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $item->status_badge['class'] }}">
                                                {{ $item->status_badge['label'] }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <form method="POST" action="{{ route('admin.presensi.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                                        Tidak ada data kehadiran yang sesuai dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($attendances->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $attendances->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- MODAL PREVIEW FOTO SELFIE UKURAN PENUH -->
        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            <div class="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl"
                 @click.outside="modalOpen = false">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h4 class="text-sm font-bold text-gray-900 truncate" x-text="modalTitle"></h4>
                    <button type="button" @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>
                <div class="p-4 flex items-center justify-center bg-gray-50">
                    <img :src="modalImgSrc" class="w-full max-h-[480px] object-contain rounded-xl shadow">
                </div>
                <div class="p-3 bg-gray-100 text-right">
                    <button type="button" @click="modalOpen = false" class="px-4 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-semibold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
