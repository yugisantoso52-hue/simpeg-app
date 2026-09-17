<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>📜</span> {{ __('Riwayat Presensi Saya') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">Daftar rekaman kehadiran dan kepulangan Anda.</p>
            </div>
            <div>
                <a href="{{ route('presensi.index') }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-lg text-xs font-semibold text-white hover:bg-blue-700 shadow-sm transition">
                    📍 Ke Halaman Presensi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <form method="GET" action="{{ route('presensi.history') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Bulan:</label>
                        <select name="month" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Semua Bulan --</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tahun:</label>
                        <select name="year" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Semua Tahun --</option>
                            @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold transition">
                            Filter Data
                        </button>
                        <a href="{{ route('presensi.history') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabel Riwayat -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                        <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Tipe</th>
                                <th class="px-4 py-3">Jam Masuk</th>
                                <th class="px-4 py-3">Jam Pulang</th>
                                <th class="px-4 py-3">Jarak Lokasi</th>
                                <th class="px-4 py-3">Foto Selfie Masuk</th>
                                <th class="px-4 py-3">Foto Selfie Pulang</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($attendances as $item)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">
                                        {{ $item->attendance_date->translatedFormat('l, d F Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] uppercase {{ $item->attendance_type === 'wfh' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ strtoupper($item->attendance_type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono font-medium text-gray-800 whitespace-nowrap">
                                        {{ $item->check_in_time ? $item->check_in_time->timezone('Asia/Jakarta')->format('H:i:s') . ' WIB' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono font-medium text-gray-800 whitespace-nowrap">
                                        {{ $item->check_out_time ? $item->check_out_time->timezone('Asia/Jakarta')->format('H:i:s') . ' WIB' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-gray-600 whitespace-nowrap">
                                        {{ number_format($item->check_in_distance_meters, 1) }} meter
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($item->check_in_photo_path)
                                            <a href="{{ Storage::disk('public')->url($item->check_in_photo_path) }}" target="_blank" class="block w-10 h-10 rounded-lg overflow-hidden border border-gray-200 hover:scale-105 transition shadow-sm">
                                                <img src="{{ Storage::disk('public')->url($item->check_in_photo_path) }}" class="w-full h-full object-cover">
                                            </a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($item->check_out_photo_path)
                                            <a href="{{ Storage::disk('public')->url($item->check_out_photo_path) }}" target="_blank" class="block w-10 h-10 rounded-lg overflow-hidden border border-gray-200 hover:scale-105 transition shadow-sm">
                                                <img src="{{ Storage::disk('public')->url($item->check_out_photo_path) }}" class="w-full h-full object-cover">
                                            </a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $item->status_badge['class'] }}">
                                            {{ $item->status_badge['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 max-w-xs truncate">
                                        {{ $item->notes ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                                        Tidak ada catatan kehadiran yang ditemukan.
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
    </div>
</x-app-layout>
