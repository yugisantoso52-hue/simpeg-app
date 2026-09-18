<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>📊</span> {{ __('Rekap Presensi Pegawai') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">Pemantauan keberadaan, verifikasi foto selfie, dan radius koordinat GPS.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.presensi.export.excel', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 border border-transparent rounded-lg text-xs font-semibold text-white shadow-sm transition" style="background-color: #059669; color: #ffffff;">
                    <span>📊</span> Ekspor Excel
                </a>
                <a href="{{ route('admin.presensi.export.pdf', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 border border-transparent rounded-lg text-xs font-semibold text-white shadow-sm transition" style="background-color: #dc2626; color: #ffffff;">
                    <span>🖨️</span> Cetak PDF
                </a>
                <a href="{{ route('admin.presensi.locations') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-lg text-xs font-semibold text-white shadow-sm transition" style="background-color: #4f46e5; color: #ffffff;">
                    <span>📍</span> Kelola Titik Acuan
                </a>
                <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                    <span>📸</span> Presensi Mandiri
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ modalOpen: false, modalImgSrc: '', modalTitle: '', detailOpen: false, detailData: {} }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if(session('success'))
                <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm flex items-center justify-between">
                    <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Banner Peringatan Keamanan & Integritas Presensi -->
            @if(($statistics['total_suspicious'] ?? 0) > 0)
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-sm flex items-start gap-3">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <h4 class="text-sm font-bold text-rose-900">Peringatan Integritas Presensi: Ditemukan {{ $statistics['total_suspicious'] }} Anomali Hari Ini!</h4>
                        <p class="text-xs text-rose-700 mt-0.5">Sistem mendeteksi indikasi titip absen / penggunaan perangkat multi-akun atau anomali perpindahan lokasi yang tidak wajar (Impossible Travel). Silakan periksa badge peringatan merah pada tabel log harian di bawah.</p>
                    </div>
                </div>
            @endif

            <!-- KPI Cards Statistik Hari Ini -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">TOTAL PEGAWAI</div>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $statistics['total_pegawai'] ?? $statistics['total_users'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Terdaftar dalam sistem</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">HADIR HARI INI</div>
                    <div class="text-2xl font-black text-emerald-600 mt-1">{{ $statistics['total_present'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">WFO (KANTOR)</div>
                    <div class="text-2xl font-black text-blue-600 mt-1">{{ $statistics['total_wfo'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Pegawai presensi kantor</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">WFH (RUMAH)</div>
                    <div class="text-2xl font-black text-indigo-600 mt-1">{{ $statistics['total_wfh'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Pegawai presensi WFH</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">TERLAMBAT</div>
                    <div class="text-2xl font-black text-amber-600 mt-1">{{ $statistics['total_late'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Waktu check-in &gt; 07.30 WIB</div>
                </div>

                <div class="bg-white p-4 rounded-xl border {{ ($statistics['total_suspicious'] ?? 0) > 0 ? 'border-rose-300 bg-rose-50/40' : 'border-gray-200' }} shadow-sm">
                    <div class="text-[11px] font-bold {{ ($statistics['total_suspicious'] ?? 0) > 0 ? 'text-rose-600' : 'text-gray-500' }} uppercase tracking-wider">ANOMALI / TITIP</div>
                    <div class="text-2xl font-black {{ ($statistics['total_suspicious'] ?? 0) > 0 ? 'text-rose-600' : 'text-gray-900' }} mt-1">{{ $statistics['total_suspicious'] ?? 0 }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Indikasi pelanggaran</div>
                </div>
            </div>

            <!-- Tab Switcher Mode Presensi -->
            <div class="bg-gray-100 p-1 rounded-xl border border-gray-200 flex items-center gap-1">
                <a href="{{ route('admin.presensi.index', ['mode' => 'daily', 'date' => request('date', date('Y-m-d'))]) }}"
                   class="flex-1 text-center py-2.5 px-4 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 {{ ($mode ?? 'daily') === 'daily' ? 'bg-white text-slate-900 shadow-sm border border-gray-200' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/60' }}">
                    <span>📋</span>
                    <span>Log Harian (Detail GPS & Foto)</span>
                </a>
                <a href="{{ route('admin.presensi.index', ['mode' => 'monthly', 'month' => request('month', date('n')), 'year' => request('year', date('Y'))]) }}"
                   class="flex-1 text-center py-2.5 px-4 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 {{ ($mode ?? 'daily') === 'monthly' ? 'bg-white text-slate-900 shadow-sm border border-gray-200' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/60' }}">
                    <span>📅</span>
                    <span>Matriks Kalender Bulanan (1 - 31)</span>
                </a>
            </div>

            @if(($mode ?? 'daily') === 'daily')
                <!-- ============================================================= -->
                <!-- MODE 1: LOG HARIAN (DETAIL FOTO, GPS, DAN JAM KERJA)         -->
                <!-- ============================================================= -->

                <!-- Filter Card Harian -->
                <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-sm">
                    <form method="GET" action="{{ route('admin.presensi.index') }}" class="space-y-4">
                        <input type="hidden" name="mode" value="daily">
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
                                    <a href="{{ route('admin.presensi.index', ['mode' => 'daily']) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition shrink-0">
                                        Mengatur ulang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabel Data Rekap Presensi Harian -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-800 text-sm">📋 Rekap Kehadiran Pegawai</span>
                            <span class="px-2 py-0.5 text-[11px] font-semibold bg-blue-100 text-blue-700 rounded-full">
                                Total: {{ $attendances->total() }} Data
                            </span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                            <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="px-4 py-3">Pegawai</th>
                                    <th class="px-4 py-3">Tanggal & Waktu</th>
                                    <th class="px-4 py-3">Tipe</th>
                                    <th class="px-4 py-3">Jarak & GPS</th>
                                    <th class="px-4 py-3 text-center">Foto Masuk</th>
                                    <th class="px-4 py-3 text-center">Foto Pulang</th>
                                    <th class="px-4 py-3">Jam Kerja (Status)</th>
                                    <th class="px-4 py-3 text-center">Integritas</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($attendances as $item)
                                    <tr class="hover:bg-gray-50/80 transition {{ $item->is_suspicious ? 'bg-rose-50/40' : '' }}">
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
                                            @if($item->is_suspicious)
                                                <div class="mt-1">
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300" title="{{ $item->suspicious_reason }}">
                                                        ⚠️ Anomali / Titip Absen
                                                    </span>
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
                                            <div class="text-[10px] text-gray-400 font-sans mt-0.5">
                                                Akurasi: ± {{ round($item->gps_accuracy ?? 0) }}m
                                                @if($item->is_mock_location)
                                                    <span class="text-[9px] font-bold text-rose-600 bg-rose-100 px-1 rounded ml-1">MOCK</span>
                                                @endif
                                            </div>
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
                                            <div class="mt-1 flex flex-wrap items-center gap-1">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $item->status_badge['class'] }}">
                                                    {{ $item->status_badge['label'] }}
                                                </span>
                                                @if($item->late_minutes > 0)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                        +{{ $item->late_minutes }}m telat
                                                    </span>
                                                @endif
                                                @if($item->early_leave_minutes > 0)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-300">
                                                        -{{ $item->early_leave_minutes }}m PSW
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @if($item->is_suspicious)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                                    ⚠️ Anomali
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                    ✓ Terverifikasi
                                                </span>
                                            @endif
                                            <div class="text-[10px] text-gray-400 mt-0.5">
                                                {{ strtoupper($item->liveness_challenge ?? 'VALID') }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 whitespace-nowrap text-center space-y-1">
                                            <button type="button"
                                                    @click="detailOpen = true; detailData = {
                                                        nama: '{{ addslashes($item->user?->name ?? 'User') }}',
                                                        nip: '{{ addslashes($item->user?->pegawai?->nip ?? '-') }}',
                                                        tanggal: '{{ $item->attendance_date ? $item->attendance_date->translatedFormat('d F Y') : '-' }}',
                                                        status: '{{ $item->status_badge['label'] }}',
                                                        status_badge: '{{ $item->status_badge['class'] }}',
                                                        in: '{{ $item->check_in_time ? $item->check_in_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-' }}',
                                                        out: '{{ $item->check_out_time ? $item->check_out_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-' }}',
                                                        late_minutes: {{ $item->late_minutes }},
                                                        early_leave_minutes: {{ $item->early_leave_minutes }},
                                                        duration: '{{ $item->work_duration }}',
                                                        tipe: '{{ strtoupper($item->attendance_type) }}',
                                                        distance: '{{ number_format($item->check_in_distance_meters, 1) }} meter',
                                                        accuracy: '{{ $item->gps_accuracy ? '± ' . round($item->gps_accuracy) . ' meter' : '-' }}',
                                                        altitude: '{{ $item->gps_altitude ? round($item->gps_altitude, 1) . ' m' : '-' }}',
                                                        speed: '{{ $item->gps_speed ? round($item->gps_speed, 1) . ' m/s' : '-' }}',
                                                        is_mock: {{ $item->is_mock_location ? 'true' : 'false' }},
                                                        is_suspicious: {{ $item->is_suspicious ? 'true' : 'false' }},
                                                        suspicious_reason: '{{ addslashes($item->suspicious_reason ?? '') }}',
                                                        ip_address: '{{ $item->ip_address ?? '-' }}',
                                                        device_platform: '{{ addslashes($item->device_platform ?? '-') }}',
                                                        device_fingerprint: '{{ $item->device_fingerprint ?? '-' }}',
                                                        liveness_verified: {{ $item->liveness_verified ? 'true' : 'false' }},
                                                        liveness_challenge: '{{ strtoupper($item->liveness_challenge ?? '-') }}',
                                                        photo_in: '{{ $item->check_in_photo_url }}',
                                                        photo_out: '{{ $item->check_out_photo_url }}'
                                                    }"
                                                    class="inline-block px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                                🔍 Detail
                                            </button>

                                            <form method="POST" action="{{ route('admin.presensi.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-[11px] block mx-auto">
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

            @else
                <!-- ============================================================= -->
                <!-- MODE 2: MATRIKS PRESENSI BULANAN (KALENDER 1 - 31)           -->
                <!-- ============================================================= -->

                <!-- Filter Card Matriks Bulanan -->
                <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-sm">
                    <form method="GET" action="{{ route('admin.presensi.index') }}" class="space-y-4">
                        <input type="hidden" name="mode" value="monthly">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- Pilihan Bulan -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Bulan:</label>
                                <select name="month" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @foreach([
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ] as $mNum => $mLabel)
                                        <option value="{{ $mNum }}" {{ (int)request('month', $month ?? date('n')) === $mNum ? 'selected' : '' }}>
                                            {{ $mLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pilihan Tahun -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Tahun:</label>
                                <select name="year" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}" {{ (int)request('year', $year ?? date('Y')) === $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
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
                                    <a href="{{ route('admin.presensi.index', ['mode' => 'monthly']) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition shrink-0">
                                        Mengatur ulang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabel Matriks Bulanan -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-800 text-sm">📅 Matriks Presensi Bulanan Pegawai ({{ $matrixData['month_name'] }})</span>
                            <span class="px-2 py-0.5 text-[11px] font-semibold bg-blue-100 text-blue-700 rounded-full">
                                Total: {{ $matrixData['paginator']->total() }} Pegawai
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs text-left border-collapse">
                            <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="px-3 py-2.5 text-center sticky left-0 z-20 bg-gray-50 border-r border-gray-200" style="min-width: 40px;">No</th>
                                    <th class="px-3 py-2.5 sticky left-[40px] z-20 bg-gray-50 border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]" style="min-width: 220px;">Pegawai</th>
                                    @foreach($matrixData['days'] as $d => $dayInfo)
                                        <th class="px-1 py-1.5 text-center border-r border-gray-200 {{ $dayInfo['is_weekend'] ? 'bg-slate-100 text-slate-400' : ($dayInfo['is_today'] ? 'bg-blue-100/60 text-blue-700 font-black' : '') }}" style="min-width: 28px;">
                                            <div class="text-[11px]">{{ $d }}</div>
                                            <div class="text-[8px] font-normal uppercase opacity-75">{{ $dayInfo['day_short'][0] }}</div>
                                        </th>
                                    @endforeach
                                    <th class="px-2.5 py-2 text-center bg-gray-50 border-l border-gray-200" style="min-width: 50px;">Hadir</th>
                                    <th class="px-2.5 py-2 text-center bg-gray-50 border-r border-gray-200" style="min-width: 55px;">Telat</th>
                                    <th class="px-2.5 py-2 text-center bg-gray-50 border-r border-gray-200" style="min-width: 55px;">PSW</th>
                                    <th class="px-3 py-2 text-center bg-gray-50 border-r border-gray-200" style="min-width: 110px;">Total Durasi</th>
                                    <th class="px-3 py-2 text-center bg-gray-50" style="min-width: 110px;">Sanksi Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($matrixData['rows'] as $index => $row)
                                    <tr class="hover:bg-blue-50/40 transition">
                                        <td class="px-3 py-2 text-center font-bold text-gray-500 sticky left-0 z-10 bg-white border-r border-gray-200">
                                            {{ $matrixData['paginator']->firstItem() + $index }}
                                        </td>
                                        <td class="px-3 py-2 sticky left-[40px] z-10 bg-white border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                            <div class="font-bold text-gray-900 truncate max-w-[200px]">{{ $row['pegawai']->nama }}</div>
                                            <div class="text-[10px] text-gray-500 font-mono">{{ $row['pegawai']->nip ?? '-' }}</div>
                                            @if($row['pegawai']->unitKerja)
                                                <div class="text-[9px] text-gray-400 truncate max-w-[200px]">{{ $row['pegawai']->unitKerja->nama_unit }}</div>
                                            @endif
                                        </td>

                                        @foreach($matrixData['days'] as $d => $dayInfo)
                                            @php
                                                $dayRecord = $row['days'][$d] ?? null;
                                            @endphp
                                            <td class="p-0.5 text-center border-r border-gray-100 {{ $dayInfo['is_weekend'] ? 'bg-slate-50/70' : ($dayInfo['is_today'] ? 'bg-blue-50/30' : '') }}">
                                                @if(($dayRecord['status'] ?? '') === 'present')
                                                    <button type="button"
                                                            @click="detailOpen = true; detailData = {
                                                                nama: '{{ addslashes($row['pegawai']->nama) }}',
                                                                tanggal: '{{ $d }} {{ $matrixData['month_name'] }}',
                                                                tipe: '{{ strtoupper($dayRecord['type'] ?? 'WFO') }}',
                                                                in: '{{ $dayRecord['in'] ?? '-' }}',
                                                                out: '{{ $dayRecord['out'] ?? '-' }}',
                                                                duration: '{{ $dayRecord['duration'] ?? '-' }}',
                                                                late_minutes: {{ $dayRecord['late_minutes'] ?? 0 }},
                                                                early_leave_minutes: {{ $dayRecord['early_leave_minutes'] ?? 0 }},
                                                                distance: '{{ number_format($dayRecord['distance'] ?? 0, 1) }} m',
                                                                photo_in: '{{ $dayRecord['photo_in'] ?? '' }}',
                                                                photo_out: '{{ $dayRecord['photo_out'] ?? '' }}',
                                                                status: 'Hadir Tepat Waktu',
                                                                status_badge: 'bg-green-100 text-green-800'
                                                            }"
                                                            title="Tgl {{ $d }}: Hadir ({{ $dayRecord['in'] ?? '-' }} s/d {{ $dayRecord['out'] ?? '-' }})"
                                                            class="w-6 h-6 rounded flex items-center justify-center font-bold text-[10px] bg-emerald-100 text-emerald-800 hover:ring-2 hover:ring-emerald-400 mx-auto transition">
                                                        H
                                                    </button>
                                                @elseif(($dayRecord['status'] ?? '') === 'late')
                                                    <button type="button"
                                                            @click="detailOpen = true; detailData = {
                                                                nama: '{{ addslashes($row['pegawai']->nama) }}',
                                                                tanggal: '{{ $d }} {{ $matrixData['month_name'] }}',
                                                                tipe: '{{ strtoupper($dayRecord['type'] ?? 'WFO') }}',
                                                                in: '{{ $dayRecord['in'] ?? '-' }}',
                                                                out: '{{ $dayRecord['out'] ?? '-' }}',
                                                                duration: '{{ $dayRecord['duration'] ?? '-' }}',
                                                                late_minutes: {{ $dayRecord['late_minutes'] ?? 0 }},
                                                                early_leave_minutes: {{ $dayRecord['early_leave_minutes'] ?? 0 }},
                                                                distance: '{{ number_format($dayRecord['distance'] ?? 0, 1) }} m',
                                                                photo_in: '{{ $dayRecord['photo_in'] ?? '' }}',
                                                                photo_out: '{{ $dayRecord['photo_out'] ?? '' }}',
                                                                status: 'Terlambat (+{{ $dayRecord['late_minutes'] ?? 0 }}m)',
                                                                status_badge: 'bg-amber-100 text-amber-800'
                                                            }"
                                                            title="Tgl {{ $d }}: Terlambat {{ $dayRecord['late_minutes'] ?? 0 }} mnt ({{ $dayRecord['in'] ?? '-' }} s/d {{ $dayRecord['out'] ?? '-' }})"
                                                            class="w-6 h-6 rounded flex items-center justify-center font-bold text-[10px] bg-amber-100 text-amber-800 hover:ring-2 hover:ring-amber-400 mx-auto transition">
                                                        T
                                                    </button>
                                                @elseif(($dayRecord['status'] ?? '') === 'absent')
                                                    <span class="w-5 h-5 flex items-center justify-center text-[10px] font-semibold text-rose-500 mx-auto" title="Tgl {{ $d }}: Tidak Hadir">
                                                        A
                                                    </span>
                                                @elseif(($dayRecord['status'] ?? '') === 'weekend')
                                                    <span class="w-5 h-5 flex items-center justify-center text-[10px] text-gray-300 mx-auto" title="Akhir Pekan">
                                                        —
                                                    </span>
                                                @else
                                                    <span class="w-5 h-5 flex items-center justify-center text-[10px] text-gray-200 mx-auto">
                                                        ·
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td class="px-2.5 py-2 text-center font-bold font-mono text-emerald-700 bg-gray-50/50 border-l border-gray-200">
                                            {{ $row['total_hadir'] }}
                                        </td>
                                        <td class="px-2.5 py-2 text-center font-bold font-mono bg-gray-50/50 border-r border-gray-200">
                                            <div class="{{ $row['total_late'] > 0 ? 'text-amber-700' : 'text-gray-400' }}">{{ $row['total_late'] }}x</div>
                                            @if($row['total_late_minutes'] > 0)
                                                <div class="text-[9px] text-amber-600 font-normal">({{ $row['total_late_minutes'] }}m)</div>
                                            @endif
                                        </td>
                                        <td class="px-2.5 py-2 text-center font-bold font-mono bg-gray-50/50 border-r border-gray-200">
                                            <div class="{{ $row['total_early_count'] > 0 ? 'text-orange-700' : 'text-gray-400' }}">{{ $row['total_early_count'] }}x</div>
                                            @if($row['total_early_minutes'] > 0)
                                                <div class="text-[9px] text-orange-600 font-normal">({{ $row['total_early_minutes'] }}m)</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-center font-bold font-mono text-slate-800 bg-gray-50/50 text-[11px] whitespace-nowrap border-r border-gray-200">
                                            {{ $row['total_duration'] }}
                                        </td>
                                        <td class="px-3 py-2 text-center font-mono bg-gray-50/50 text-[11px] whitespace-nowrap">
                                            @if($row['sanksi_hari'] > 0)
                                                <span class="px-2 py-0.5 rounded font-bold text-rose-700 bg-rose-100 border border-rose-300">
                                                    {{ $row['sanksi_hari'] }} Hari
                                                </span>
                                                <div class="text-[9px] text-slate-500 font-normal mt-0.5">Sisa {{ $row['sisa_menit_sanksi'] }} mnt</div>
                                            @elseif($row['total_violation_minutes'] > 0)
                                                <span class="text-slate-600 font-semibold">0 Hari</span>
                                                <div class="text-[9px] text-slate-400 font-normal mt-0.5">Total {{ $row['formatted_violation_time'] }}</div>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 6 + $matrixData['days_in_month'] }}" class="px-4 py-8 text-center text-gray-400">
                                            Tidak ada data pegawai yang sesuai dengan filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Legenda Keterangan -->
                    <div class="px-5 py-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/30 text-xs">
                        <div class="flex flex-wrap items-center gap-4 text-gray-600">
                            <span class="font-semibold text-gray-700">Keterangan:</span>
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px] flex items-center justify-center">H</span>
                                <span>Hadir Tepat Waktu</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded bg-amber-100 text-amber-800 font-bold text-[10px] flex items-center justify-center">T</span>
                                <span>Terlambat</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded font-bold text-[10px] text-rose-500 flex items-center justify-center">A</span>
                                <span>Tidak Hadir (Alpa)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400 font-bold">—</span>
                                <span class="text-gray-400">Akhir Pekan / Libur</span>
                            </div>
                        </div>
                    </div>

                    @if($matrixData['paginator']->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $matrixData['paginator']->links() }}
                        </div>
                    @endif
                </div>

            @endif

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

        <!-- MODAL DETAIL PRESENSI DARI MATRIKS BULANAN -->
        <div x-show="detailOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl"
                 @click.outside="detailOpen = false">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900" x-text="detailData.nama"></h4>
                        <div class="text-[11px] text-gray-500 font-medium" x-text="'Presensi: ' + detailData.tanggal + (detailData.nip && detailData.nip !== '-' ? ' | NIP: ' + detailData.nip : '')"></div>
                    </div>
                    <button type="button" @click="detailOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>
                <div class="p-4 space-y-3 text-xs bg-gray-50/50 max-h-[80vh] overflow-y-auto">
                    <!-- Status & Peringatan Integritas -->
                    <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-gray-200">
                        <span class="text-gray-500">Status Kehadiran:</span>
                        <span :class="detailData.status_badge" class="px-2.5 py-0.5 rounded-full font-bold text-[11px]" x-text="detailData.status"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-white p-3 rounded-xl border border-gray-200">
                            <div class="text-[10px] text-gray-400 font-bold uppercase">Jam Masuk</div>
                            <div class="text-sm font-black text-emerald-700 mt-0.5" x-text="detailData.in || '-'"></div>
                            <div class="text-[10px] mt-1 font-semibold" :class="detailData.late_minutes > 0 ? 'text-rose-600' : 'text-emerald-600'" x-text="detailData.late_minutes > 0 ? '⚠️ Telat: ' + detailData.late_minutes + ' mnt' : '✅ Tepat Waktu (≤07:30)'"></div>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-gray-200">
                            <div class="text-[10px] text-gray-400 font-bold uppercase">Jam Pulang</div>
                            <div class="text-sm font-black text-amber-700 mt-0.5" x-text="detailData.out || '-'"></div>
                            <div class="text-[10px] mt-1 font-semibold" :class="detailData.early_leave_minutes > 0 ? 'text-orange-600' : 'text-emerald-600'" x-text="detailData.early_leave_minutes > 0 ? '⚠️ PSW: ' + detailData.early_leave_minutes + ' mnt' : '✅ Jam Pulang Sah'"></div>
                        </div>
                    </div>

                    <!-- Panel Audit Integritas & Keamanan Perangkat -->
                    <div class="bg-white p-3 rounded-xl border border-gray-200 space-y-2">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-1.5">
                            <span class="text-[11px] font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                                <span>🛡️</span> Audit Keamanan Presensi
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                  :class="detailData.is_suspicious ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300'"
                                  x-text="detailData.is_suspicious ? '⚠️ ANOMALI TERDETEKSI' : '✅ VALID & AMAN'"></span>
                        </div>

                        <!-- Banner Detail Anomali Jika Ada -->
                        <template x-if="detailData.is_suspicious && detailData.suspicious_reason">
                            <div class="p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-[11px] text-rose-800 space-y-1">
                                <div class="font-bold flex items-center gap-1">
                                    <span>⚠️</span> <span>Detail Pelanggaran / Anomali:</span>
                                </div>
                                <div class="font-mono text-[10px] whitespace-pre-line" x-text="detailData.suspicious_reason"></div>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div>
                                <span class="text-gray-400 block text-[10px]">Akurasi GPS Sensor:</span>
                                <span class="font-mono text-gray-800 font-semibold" x-text="detailData.accuracy || '-'"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px]">Uji Keaktifan (Liveness):</span>
                                <span class="font-semibold text-emerald-700" x-text="detailData.liveness_challenge ? '✓ Lolos (' + detailData.liveness_challenge + ')' : '-'"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px]">IP Address:</span>
                                <span class="font-mono text-gray-800" x-text="detailData.ip_address || '-'"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px]">Platform / OS:</span>
                                <span class="text-gray-800 truncate block" x-text="detailData.device_platform || '-'"></span>
                            </div>
                        </div>

                        <div class="pt-1 border-t border-gray-100 text-[10px]">
                            <span class="text-gray-400">Device Fingerprint:</span>
                            <span class="font-mono text-gray-600 block truncate" x-text="detailData.device_fingerprint || '-'"></span>
                        </div>
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-gray-200 space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Jam Kerja Efektif:</span>
                            <span class="font-bold text-gray-900" x-text="detailData.duration || '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tipe Presensi:</span>
                            <span class="font-semibold text-gray-800" x-text="detailData.tipe || '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Jarak Lokasi GPS:</span>
                            <span class="font-mono text-gray-800" x-text="detailData.distance || '-'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2" x-show="detailData.photo_in || detailData.photo_out">
                        <div x-show="detailData.photo_in" class="bg-white p-2 rounded-xl border border-gray-200 text-center">
                            <div class="text-[10px] font-bold text-gray-500 mb-1">Foto Masuk</div>
                            <img :src="detailData.photo_in" class="w-full h-28 object-cover rounded-lg shadow-sm">
                        </div>
                        <div x-show="detailData.photo_out" class="bg-white p-2 rounded-xl border border-gray-200 text-center">
                            <div class="text-[10px] font-bold text-gray-500 mb-1">Foto Pulang</div>
                            <img :src="detailData.photo_out" class="w-full h-28 object-cover rounded-lg shadow-sm">
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-gray-100 text-right">
                    <button type="button" @click="detailOpen = false" class="px-4 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-semibold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
