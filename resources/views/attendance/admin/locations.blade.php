<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>📍</span> {{ __('Manajemen Titik Acuan Lokasi Pegawai') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">Pantau dan kelola titik koordinat acuan tetap WFO & WFH karyawan yang terkunci.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.presensi.index') }}" class="inline-flex items-center px-3 py-1.5 bg-slate-800 border border-transparent rounded-lg text-xs font-semibold text-white hover:bg-slate-700 shadow-sm transition">
                    📊 Ke Rekap Presensi
                </a>
                <a href="{{ route('presensi.index') }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                    📸 Presensi Mandiri
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if(session('success'))
                <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm flex items-center justify-between">
                    <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Info Box One-Time Lock -->
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-start gap-3">
                <span class="text-lg">ℹ️</span>
                <div class="space-y-1">
                    <div class="font-bold text-sm text-blue-900">Ketentuan Kunci Titik Koordinat (One-Time Lock)</div>
                    <p class="leading-relaxed text-blue-700">
                        Setiap pegawai memiliki 2 slot titik acuan: <strong>Titik WFO</strong> (Kantor) dan <strong>Titik WFH</strong> (Rumah).
                        Saat pegawai pertama kali melakukan presensi, koordinat otomatis terkunci dan tidak dapat diubah sendiri oleh pegawai.
                        Gunakan tombol <strong>"Reset Titik"</strong> di bawah ini bila pegawai berpindah kantor, mutasi kerja, atau pindah alamat domisili.
                    </p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('admin.presensi.locations') }}" class="flex flex-col sm:flex-row gap-2">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari berdasarkan nama, email, atau NIP pegawai..."
                           class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold transition shrink-0">
                        Cari Pegawai
                    </button>
                    @if($search)
                        <a href="{{ route('admin.presensi.locations') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition shrink-0 text-center">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Tabel Titik Acuan Karyawan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                        <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Pegawai / User</th>
                                <th class="px-4 py-3">Titik Acuan WFO (Kantor)</th>
                                <th class="px-4 py-3">Titik Acuan WFH (Rumah)</th>
                                <th class="px-4 py-3 text-center">Aksi Reset Titik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($users as $user)
                                @php
                                    $loc = $user->attendanceLocation;
                                    $hasWfo = $loc && $loc->isWfoLocked();
                                    $hasWfh = $loc && $loc->isWfhLocked();
                                @endphp
                                <tr class="hover:bg-gray-50/80 transition">
                                    <!-- Profil Pegawai -->
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900">{{ $user->name }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono">
                                            {{ $user->pegawai->nip ?? $user->email }}
                                        </div>
                                        @if($user->pegawai && $user->pegawai->unitKerja)
                                            <div class="text-[10px] text-gray-400">
                                                {{ $user->pegawai->unitKerja->nama_unit ?? '' }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Status Titik WFO -->
                                    <td class="px-4 py-3">
                                        @if($hasWfo)
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                                    <span class="font-semibold text-emerald-800">Terkunci Tetap</span>
                                                </div>
                                                <div class="font-mono text-[11px] text-gray-600">
                                                    {{ number_format($loc->wfo_latitude, 6) }}, {{ number_format($loc->wfo_longitude, 6) }}
                                                </div>
                                                <div class="text-[10px] text-gray-400">
                                                    Dikunci: {{ $loc->wfo_locked_at ? $loc->wfo_locked_at->translatedFormat('d M Y H:i') : '-' }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-gray-400 italic">
                                                <span>🔓 Belum dikunci</span>
                                                <div class="text-[10px] text-gray-400">Akan dikunci saat presensi pertama</div>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Status Titik WFH -->
                                    <td class="px-4 py-3">
                                        @if($hasWfh)
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="inline-block w-2 h-2 rounded-full bg-indigo-500"></span>
                                                    <span class="font-semibold text-indigo-800">Terkunci Tetap</span>
                                                </div>
                                                <div class="font-mono text-[11px] text-gray-600">
                                                    {{ number_format($loc->wfh_latitude, 6) }}, {{ number_format($loc->wfh_longitude, 6) }}
                                                </div>
                                                <div class="text-[10px] text-gray-400">
                                                    Dikunci: {{ $loc->wfh_locked_at ? $loc->wfh_locked_at->translatedFormat('d M Y H:i') : '-' }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-gray-400 italic">
                                                <span>🔓 Belum dikunci</span>
                                                <div class="text-[10px] text-gray-400">Akan dikunci saat presensi pertama</div>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Aksi Reset -->
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- Reset WFO -->
                                            <form method="POST" action="{{ route('admin.presensi.reset-location', $user->id) }}" onsubmit="return confirm('Reset titik acuan WFO untuk {{ addslashes($user->name) }}?');">
                                                @csrf
                                                <input type="hidden" name="type" value="wfo">
                                                <button type="submit"
                                                        {{ !$hasWfo ? 'disabled' : '' }}
                                                        class="px-2 py-1 rounded bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 disabled:opacity-40 disabled:cursor-not-allowed font-medium text-[11px] transition">
                                                    Reset WFO
                                                </button>
                                            </form>

                                            <!-- Reset WFH -->
                                            <form method="POST" action="{{ route('admin.presensi.reset-location', $user->id) }}" onsubmit="return confirm('Reset titik acuan WFH untuk {{ addslashes($user->name) }}?');">
                                                @csrf
                                                <input type="hidden" name="type" value="wfh">
                                                <button type="submit"
                                                        {{ !$hasWfh ? 'disabled' : '' }}
                                                        class="px-2 py-1 rounded bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 disabled:opacity-40 disabled:cursor-not-allowed font-medium text-[11px] transition">
                                                    Reset WFH
                                                </button>
                                            </form>

                                            <!-- Reset Keduanya (All) -->
                                            <form method="POST" action="{{ route('admin.presensi.reset-location', $user->id) }}" onsubmit="return confirm('Reset SEMUA titik acuan (WFO & WFH) untuk {{ addslashes($user->name) }}?');">
                                                @csrf
                                                <input type="hidden" name="type" value="all">
                                                <button type="submit"
                                                        {{ (!$hasWfo && !$hasWfh) ? 'disabled' : '' }}
                                                        class="px-2 py-1 rounded bg-red-50 border border-red-200 text-red-700 hover:bg-red-100 disabled:opacity-40 disabled:cursor-not-allowed font-medium text-[11px] transition">
                                                    Reset Semua
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                        Tidak ada data pegawai yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
