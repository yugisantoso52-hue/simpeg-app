<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>📍</span> {{ __('Presensi Karyawan (GPS & Selfie Real-Time)') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">Sistem presensi terverifikasi Geolocation GPS (Maks. 75m) & Verifikasi Foto Kamera.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('presensi.history') }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                    📜 Riwayat Presensi Saya
                </a>
                @if(Auth::user()->hasRole(['admin', 'pimpinan']))
                    <a href="{{ route('admin.presensi.index') }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-lg text-xs font-semibold text-white hover:bg-blue-700 shadow-sm transition">
                        📊 Rekap Presensi Admin
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="py-6" x-data="attendanceApp()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm flex items-start justify-between">
                    <div class="flex items-center gap-2 text-green-800">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span class="font-medium text-sm">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm flex items-start justify-between">
                    <div class="flex items-center gap-2 text-red-800">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-medium text-sm">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Ringkasan Status Presensi Hari Ini -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 sm:p-5 bg-gradient-to-r from-slate-800 to-slate-900 text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-2xl backdrop-blur-sm">
                            🗓️
                        </div>
                        <div>
                            <div class="text-xs text-slate-300 font-medium uppercase tracking-wider">Status Kehadiran Hari Ini</div>
                            <div class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                                <span>{{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30">
                                    {{ Auth::user()->name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if($todayAttendance)
                            <div class="px-3 py-1.5 rounded-lg bg-white/10 border border-white/20 text-xs flex items-center gap-2">
                                <span class="text-emerald-400 font-bold">🟢 Masuk:</span>
                                <span>{{ $todayAttendance->check_in_time ? $todayAttendance->check_in_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-' }}</span>
                                <span class="text-[10px] uppercase font-semibold px-1.5 py-0.2 rounded bg-white/20">({{ strtoupper($todayAttendance->attendance_type) }})</span>
                            </div>
                            <div class="px-3 py-1.5 rounded-lg bg-white/10 border border-white/20 text-xs flex items-center gap-2">
                                <span class="text-amber-300 font-bold">🔴 Pulang:</span>
                                <span>{{ $todayAttendance->check_out_time ? $todayAttendance->check_out_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : 'Belum Check-Out' }}</span>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $todayAttendance->status === 'late' ? 'bg-amber-500/30 text-amber-200 border border-amber-400/40' : 'bg-green-500/30 text-green-200 border border-green-400/40' }}">
                                {{ $todayAttendance->status === 'late' ? 'Terlambat' : 'Hadir Tepat Waktu' }}
                            </span>
                        @else
                            <div class="px-3 py-1.5 rounded-lg bg-amber-500/20 border border-amber-400/30 text-amber-200 text-xs font-medium flex items-center gap-2">
                                <span>⚠️ Anda belum melakukan presensi masuk (Check-In) hari ini.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- GRID UTAMA: Panel Presensi & Peta Lokasi -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- KOLOM KIRI: Kamera & Aksi Presensi (5 kolom di desktop) -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-4">
                        
                        <!-- Pilihan Tipe Presensi: WFO vs WFH -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                1. Pilih Tipe Presensi:
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button"
                                        @click="setType('wfo')"
                                        :class="type === 'wfo' ? 'border-blue-600 bg-blue-50/80 text-blue-700 ring-2 ring-blue-500/20 font-bold' : 'border-gray-200 hover:border-gray-300 text-gray-600 bg-white'"
                                        class="p-3 border rounded-xl flex flex-col items-center justify-center gap-1.5 transition text-center">
                                    <span class="text-2xl">🏢</span>
                                    <span class="text-sm font-semibold">WFO (Kantor)</span>
                                    <span class="text-[10px] text-gray-500" x-text="locations.wfo.is_locked ? 'Titik Terkunci ✅' : 'Kunci Titik Baru 🔒'"></span>
                                </button>
                                <button type="button"
                                        @click="setType('wfh')"
                                        :class="type === 'wfh' ? 'border-indigo-600 bg-indigo-50/80 text-indigo-700 ring-2 ring-indigo-500/20 font-bold' : 'border-gray-200 hover:border-gray-300 text-gray-600 bg-white'"
                                        class="p-3 border rounded-xl flex flex-col items-center justify-center gap-1.5 transition text-center">
                                    <span class="text-2xl">🏠</span>
                                    <span class="text-sm font-semibold">WFH (Rumah)</span>
                                    <span class="text-[10px] text-gray-500" x-text="locations.wfh.is_locked ? 'Titik Terkunci ✅' : 'Kunci Titik Baru 🔒'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Status Titik Acuan Card -->
                        <div class="rounded-xl p-3.5 text-xs transition border"
                             :class="currentLocationInfo.is_locked ? 'bg-emerald-50/70 border-emerald-200 text-emerald-900' : 'bg-amber-50/80 border-amber-200 text-amber-900'">
                            <div class="flex items-start gap-2.5">
                                <span class="text-base" x-text="currentLocationInfo.is_locked ? '🔒' : '💡'"></span>
                                <div class="space-y-1">
                                    <div class="font-bold flex items-center gap-2">
                                        <span x-text="currentLocationInfo.is_locked ? 'Titik Acuan Terkunci' : 'Titik Acuan Belum Terkunci'"></span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded font-semibold uppercase"
                                              :class="currentLocationInfo.is_locked ? 'bg-emerald-200 text-emerald-800' : 'bg-amber-200 text-amber-800'"
                                              x-text="type.toUpperCase()"></span>
                                    </div>
                                    <template x-if="currentLocationInfo.is_locked">
                                        <div>
                                            <p class="text-emerald-700 leading-relaxed">
                                                Koordinat acuan telah terkunci tetap. Presensi berikutnya wajib berada di dalam <strong>radius toleransi maks 75 meter</strong>.
                                            </p>
                                            <div class="text-[11px] text-emerald-800 font-mono mt-1">
                                                Acuan: <span x-text="currentLocationInfo.lat.toFixed(6)"></span>, <span x-text="currentLocationInfo.lng.toFixed(6)"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!currentLocationInfo.is_locked">
                                        <p class="text-amber-800 leading-relaxed">
                                            <strong>One-Time Lock:</strong> Anda belum memiliki titik acuan untuk <strong x-text="type.toUpperCase()"></strong>. Saat Anda mengirim presensi pertama kali ini, posisi GPS Anda saat ini akan <strong>otomatis dikunci</strong> sebagai titik acuan tetap!
                                        </p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Kamera Real-Time (MediaDevices) -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    2. Verifikasi Foto Selfie Real-Time:
                                </label>
                                <button type="button"
                                        x-show="hasMultipleCameras"
                                        @click="switchCamera()"
                                        class="text-[11px] text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1">
                                    🔄 Ganti Kamera
                                </button>
                            </div>

                            <!-- Area Video & Snapshot -->
                            <div class="relative w-full aspect-[4/3] bg-black rounded-xl overflow-hidden shadow-inner flex items-center justify-center">
                                <!-- Video Stream Live -->
                                <video id="cameraStream"
                                       autoplay
                                       playsinline
                                       muted
                                       class="w-full h-full object-cover"
                                       :class="{ 'scale-x-[-1]': facingMode === 'user' }"
                                       x-show="!photoTaken && cameraActive">
                                </video>

                                <!-- Snapshot Preview -->
                                <img id="photoPreview"
                                     :src="photoData"
                                     x-show="photoTaken"
                                     class="w-full h-full object-cover">

                                <!-- Fallback / Loading Overlay -->
                                <div x-show="!cameraActive && !photoTaken" class="p-4 text-center text-gray-400 space-y-2">
                                    <div class="text-3xl animate-pulse">📷</div>
                                    <p class="text-xs" x-text="cameraErrorMessage || 'Menghubungkan kamera...'"></p>
                                    <button type="button"
                                            @click="initCamera()"
                                            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition shadow">
                                        Aktifkan Kamera
                                    </button>
                                </div>

                                <!-- Hidden Canvas for capture -->
                                <canvas id="captureCanvas" class="hidden"></canvas>

                                <!-- Overlay Guide Target -->
                                <div x-show="cameraActive && !photoTaken" class="absolute inset-0 pointer-events-none flex items-center justify-center p-6">
                                    <div class="w-44 h-56 border-2 border-dashed border-white/60 rounded-full"></div>
                                </div>
                            </div>

                            <!-- Action Tombol Kamera -->
                            <div class="flex gap-2 pt-1">
                                <button type="button"
                                        x-show="cameraActive && !photoTaken"
                                        @click="takeSnapshot()"
                                        class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition">
                                    <span>📸</span> Ambil Foto Selfie
                                </button>

                                <button type="button"
                                        x-show="photoTaken"
                                        @click="retakeSnapshot()"
                                        class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 border border-gray-300 transition">
                                    <span>🔄</span> Foto Ulang (Retake)
                                </button>
                            </div>
                        </div>

                        <!-- Catatan Opsional -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan Keterangan (Opsional):</label>
                            <input type="text"
                                   x-model="notes"
                                   placeholder="Contoh: Bekerja di ruang perawat / lab..."
                                   class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Tombol Submit Presensi -->
                        <div class="pt-2 border-t border-gray-100 space-y-2">
                            <!-- Tombol Check-In / Check-Out -->
                            @if(!$todayAttendance)
                                <button type="button"
                                        @click="submitAttendance('check_in')"
                                        :disabled="isSubmitting || !photoTaken || !gpsReady"
                                        class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20 transition transform active:scale-95">
                                    <span x-show="!isSubmitting">✅ Kirim Presensi Masuk (Check-In)</span>
                                    <span x-show="isSubmitting" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        Memproses Presensi...
                                    </span>
                                </button>
                            @elseif(!$todayAttendance->check_out_time)
                                <button type="button"
                                        @click="submitAttendance('check_out')"
                                        :disabled="isSubmitting || !photoTaken || !gpsReady"
                                        class="w-full py-3.5 px-4 bg-amber-600 hover:bg-amber-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 shadow-lg shadow-amber-600/20 transition transform active:scale-95">
                                    <span x-show="!isSubmitting">🚪 Kirim Presensi Pulang (Check-Out)</span>
                                    <span x-show="isSubmitting" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        Memproses Check-Out...
                                    </span>
                                </button>
                            @else
                                <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-center text-xs text-gray-500 font-medium">
                                    🎉 Anda telah menyelesaikan seluruh presensi (Masuk & Pulang) hari ini.
                                </div>
                            @endif

                            <!-- Error Message Banner -->
                            <div x-show="submitErrorMessage"
                                 x-transition
                                 class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 flex items-start gap-2">
                                <span class="text-sm">⚠️</span>
                                <span x-text="submitErrorMessage"></span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- KOLOM KANAN: Peta Interaktif & Status GPS (7 kolom di desktop) -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-4">
                        
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>🗺️</span> Peta Radius Presensi (Radius Maks. 75 m)
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">Posisi GPS Anda vs Titik Koordinat Acuan Terkunci.</p>
                            </div>

                            <button type="button"
                                    @click="refreshGpsLocation()"
                                    :disabled="gpsLoading"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 transition">
                                <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': gpsLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                <span>Perbarui Titik GPS</span>
                            </button>
                        </div>

                        <!-- Leaflet Map Container -->
                        <div class="relative w-full h-[380px] sm:h-[420px] rounded-xl overflow-hidden border border-gray-200 z-0">
                            <div id="attendanceMap" class="w-full h-full"></div>

                            <!-- Map Overlay Badge Status Jarak -->
                            <div class="absolute bottom-3 left-3 right-3 sm:right-auto sm:max-w-md z-[1000] bg-white/95 backdrop-blur-sm p-3 rounded-xl border shadow-lg text-xs"
                                 :class="distanceColorClass">
                                <div class="flex items-center justify-between gap-2 font-bold mb-1">
                                    <span class="flex items-center gap-1.5">
                                        <span x-text="distanceIcon"></span>
                                        <span x-text="distanceStatusTitle"></span>
                                    </span>
                                    <span class="font-mono text-sm px-2 py-0.5 rounded bg-gray-100" x-text="distanceText"></span>
                                </div>
                                <p class="text-[11px] leading-tight text-gray-600" x-text="distanceStatusDesc"></p>
                            </div>
                        </div>

                        <!-- Detail Informasi Koordinat Real-Time -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl space-y-1">
                                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">📍 Koordinat GPS Anda Saat Ini:</div>
                                <div class="text-xs font-mono text-gray-800" x-text="gpsReady ? currentLat.toFixed(6) + ', ' + currentLng.toFixed(6) : 'Mendeteksi GPS...'"></div>
                                <div class="text-[10px] text-gray-500" x-text="'Akurasi GPS: ± ' + (gpsAccuracy ? Math.round(gpsAccuracy) + ' meter' : '...')"></div>
                            </div>

                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl space-y-1">
                                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">🎯 Titik Acuan (<span x-text="type.toUpperCase()"></span>):</div>
                                <template x-if="currentLocationInfo.is_locked">
                                    <div>
                                        <div class="text-xs font-mono text-gray-800" x-text="currentLocationInfo.lat.toFixed(6) + ', ' + currentLocationInfo.lng.toFixed(6)"></div>
                                        <div class="text-[10px] text-emerald-600 font-medium">Terkunci pada: <span x-text="currentLocationInfo.locked_at"></span></div>
                                    </div>
                                </template>
                                <template x-if="!currentLocationInfo.is_locked">
                                    <div>
                                        <div class="text-xs text-amber-600 font-medium">Belum pernah dikunci</div>
                                        <div class="text-[10px] text-gray-500">Akan dikunci saat presensi pertama</div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- TABEL RIWAYAT PRESENSI PRIBADI (10 Terakhir) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Riwayat Presensi Pribadi Anda</h3>
                        <p class="text-xs text-gray-500">10 data kehadiran terakhir Anda yang tercatat pada sistem.</p>
                    </div>
                    <a href="{{ route('presensi.history') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                        Lihat Seluruh Riwayat &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                        <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Tipe</th>
                                <th class="px-4 py-3">Jam Masuk</th>
                                <th class="px-4 py-3">Jam Pulang</th>
                                <th class="px-4 py-3">Jarak</th>
                                <th class="px-4 py-3">Foto Selfie</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($recentAttendances as $item)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 font-semibold text-gray-900">
                                        {{ $item->attendance_date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] uppercase {{ $item->attendance_type === 'wfh' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ strtoupper($item->attendance_type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono font-medium text-gray-800">
                                        {{ $item->check_in_time ? $item->check_in_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono font-medium text-gray-800">
                                        {{ $item->check_out_time ? $item->check_out_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-gray-600">
                                        {{ number_format($item->check_in_distance_meters, 1) }} m
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5">
                                            @if($item->check_in_photo_path)
                                                <a href="{{ $item->check_in_photo_url }}" target="_blank" class="block w-8 h-8 rounded-lg overflow-hidden border border-gray-200 hover:ring-2 hover:ring-blue-500 transition">
                                                    <img src="{{ $item->check_in_photo_url }}" class="w-full h-full object-cover">
                                                </a>
                                            @endif
                                            @if($item->check_out_photo_path)
                                                <a href="{{ $item->check_out_photo_url }}" target="_blank" class="block w-8 h-8 rounded-lg overflow-hidden border border-gray-200 hover:ring-2 hover:ring-amber-500 transition">
                                                    <img src="{{ $item->check_out_photo_url }}" class="w-full h-full object-cover">
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $item->status_badge['class'] }}">
                                            {{ $item->status_badge['label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-400">
                                        Belum ada rekaman presensi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- SCRIPT LOGIKA KAMERA, GPS & LEAFLET MAP -->
    <script>
        function attendanceApp() {
            return {
                type: 'wfo',
                maxRadius: {{ $maxRadius }},
                locations: {
                    wfo: {
                        is_locked: {{ $location->isWfoLocked() ? 'true' : 'false' }},
                        lat: {{ $location->wfo_latitude ?? 0 }},
                        lng: {{ $location->wfo_longitude ?? 0 }},
                        locked_at: '{{ $location->wfo_locked_at ? $location->wfo_locked_at->timezone("Asia/Jakarta")->translatedFormat("d M Y H:i") : "" }}'
                    },
                    wfh: {
                        is_locked: {{ $location->isWfhLocked() ? 'true' : 'false' }},
                        lat: {{ $location->wfh_latitude ?? 0 }},
                        lng: {{ $location->wfh_longitude ?? 0 }},
                        locked_at: '{{ $location->wfh_locked_at ? $location->wfh_locked_at->timezone("Asia/Jakarta")->translatedFormat("d M Y H:i") : "" }}'
                    }
                },

                // GPS State
                currentLat: 0.5333,
                currentLng: 101.4500,
                gpsAccuracy: 0,
                gpsReady: false,
                gpsLoading: false,

                // Camera State
                cameraActive: false,
                facingMode: 'user',
                hasMultipleCameras: false,
                photoTaken: false,
                photoData: null,
                cameraErrorMessage: '',
                videoStream: null,

                // Map & Form State
                map: null,
                userMarker: null,
                refMarker: null,
                radiusCircle: null,
                notes: '',
                isSubmitting: false,
                submitErrorMessage: '',

                init() {
                    this.$nextTick(() => {
                        this.initMap();
                        this.detectGpsLocation();
                        this.initCamera();
                    });
                },

                get currentLocationInfo() {
                    return this.type === 'wfh' ? this.locations.wfh : this.locations.wfo;
                },

                setType(newType) {
                    this.type = newType;
                    this.updateMapLayers();
                },

                // -------------------------------------------------------------
                // 1. KAMERA REAL-TIME (MediaDevices API)
                // -------------------------------------------------------------
                async initCamera() {
                    this.cameraErrorMessage = '';
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.cameraErrorMessage = 'Browser ini tidak mendukung akses kamera langsung.';
                        return;
                    }

                    try {
                        const devices = await navigator.mediaDevices.enumerateDevices();
                        const videoDevices = devices.filter(d => d.kind === 'videoinput');
                        this.hasMultipleCameras = videoDevices.length > 1;

                        if (this.videoStream) {
                            this.videoStream.getTracks().forEach(track => track.stop());
                        }

                        const constraints = {
                            video: {
                                facingMode: this.facingMode,
                                width: { ideal: 640 },
                                height: { ideal: 480 }
                            },
                            audio: false
                        };

                        const stream = await navigator.mediaDevices.getUserMedia(constraints);
                        this.videoStream = stream;
                        const video = document.getElementById('cameraStream');
                        video.srcObject = stream;
                        await video.play();
                        this.cameraActive = true;
                    } catch (err) {
                        console.error('Camera access error:', err);
                        this.cameraActive = false;
                        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                            this.cameraErrorMessage = 'Izin kamera ditolak. Harap izinkan akses kamera di pengaturan peramban Anda.';
                        } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                            this.cameraErrorMessage = 'Kamera tidak ditemukan pada perangkat ini.';
                        } else {
                            this.cameraErrorMessage = 'Gagal mengakses kamera: ' + err.message;
                        }
                    }
                },

                async switchCamera() {
                    this.facingMode = this.facingMode === 'user' ? 'environment' : 'user';
                    await this.initCamera();
                },

                takeSnapshot() {
                    const video = document.getElementById('cameraStream');
                    const canvas = document.getElementById('captureCanvas');
                    if (!video || !canvas) return;

                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 480;
                    const ctx = canvas.getContext('2d');

                    if (this.facingMode === 'user') {
                        ctx.translate(canvas.width, 0);
                        ctx.scale(-1, 1);
                    }
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    this.photoData = canvas.toDataURL('image/jpeg', 0.85);
                    this.photoTaken = true;
                },

                retakeSnapshot() {
                    this.photoTaken = false;
                    this.photoData = null;
                },

                // -------------------------------------------------------------
                // 2. GEOLOCATION (HTML5 navigator.geolocation)
                // -------------------------------------------------------------
                detectGpsLocation() {
                    if (!navigator.geolocation) {
                        alert('Browser Anda tidak mendukung HTML5 Geolocation.');
                        return;
                    }

                    this.gpsLoading = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.currentLat = pos.coords.latitude;
                            this.currentLng = pos.coords.longitude;
                            this.gpsAccuracy = pos.coords.accuracy;
                            this.gpsReady = true;
                            this.gpsLoading = false;

                            this.updateMapLayers();
                        },
                        (err) => {
                            console.error('GPS error:', err);
                            this.gpsLoading = false;
                            let msg = 'Gagal mendeteksi lokasi GPS.';
                            if (err.code === 1) {
                                msg = 'Akses lokasi ditolak! Harap aktifkan izin GPS pada peramban Anda agar dapat melakukan presensi.';
                            } else if (err.code === 2) {
                                msg = 'Sinyal GPS tidak tersedia atau tidak akurat.';
                            }
                            this.submitErrorMessage = msg;
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 0
                        }
                    );
                },

                refreshGpsLocation() {
                    this.detectGpsLocation();
                },

                // -------------------------------------------------------------
                // 3. HAVERSINE FORMULA DI FRONTEND (Meter)
                // -------------------------------------------------------------
                calculateDistance(lat1, lon1, lat2, lon2) {
                    const R = 6371000;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a =
                        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    return Math.round(R * c * 10) / 10;
                },

                get currentDistance() {
                    if (!this.gpsReady) return 0;
                    const info = this.currentLocationInfo;
                    if (!info.is_locked) return 0;
                    return this.calculateDistance(this.currentLat, this.currentLng, info.lat, info.lng);
                },

                get isWithinRadius() {
                    if (!this.currentLocationInfo.is_locked) return true;
                    return this.currentDistance <= this.maxRadius;
                },

                get distanceText() {
                    if (!this.gpsReady) return 'Menunggu GPS...';
                    if (!this.currentLocationInfo.is_locked) return 'Titik Baru (0 m)';
                    return this.currentDistance.toFixed(1) + ' meter';
                },

                get distanceIcon() {
                    if (!this.currentLocationInfo.is_locked) return '🔒';
                    return this.isWithinRadius ? '✅' : '⚠️';
                },

                get distanceStatusTitle() {
                    if (!this.currentLocationInfo.is_locked) return 'Titik Baru Akan Dikunci';
                    return this.isWithinRadius ? 'Di Dalam Radius Presensi' : 'Di Luar Batas Radius!';
                },

                get distanceStatusDesc() {
                    if (!this.currentLocationInfo.is_locked) {
                        return 'Posisi saat ini akan dijadikan titik acuan tetap Anda saat presensi dikirim.';
                    }
                    if (this.isWithinRadius) {
                        return `Jarak Anda ${this.currentDistance.toFixed(1)}m dari titik acuan (Batas toleransi maks: 75m). Anda berhak presensi!`;
                    }
                    return `Anda berjarak ${this.currentDistance.toFixed(1)}m. Presensi akan ditolak karena melebihi batas 75m. Silakan mendekat ke lokasi acuan.`;
                },

                get distanceColorClass() {
                    if (!this.currentLocationInfo.is_locked) return 'border-amber-300 text-amber-900';
                    return this.isWithinRadius ? 'border-emerald-300 text-emerald-900' : 'border-red-300 text-red-900 bg-red-50/95';
                },

                // -------------------------------------------------------------
                // 4. LEAFLET MAP VISUALIZATION
                // -------------------------------------------------------------
                initMap() {
                    const startLat = this.currentLocationInfo.is_locked ? this.currentLocationInfo.lat : this.currentLat;
                    const startLng = this.currentLocationInfo.is_locked ? this.currentLocationInfo.lng : this.currentLng;

                    this.map = L.map('attendanceMap').setView([startLat, startLng], 17);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap SIKAP'
                    }).addTo(this.map);

                    this.updateMapLayers();
                },

                updateMapLayers() {
                    if (!this.map) return;

                    // Bersihkan marker & layer lama
                    if (this.userMarker) this.map.removeLayer(this.userMarker);
                    if (this.refMarker) this.map.removeLayer(this.refMarker);
                    if (this.radiusCircle) this.map.removeLayer(this.radiusCircle);

                    const info = this.currentLocationInfo;

                    if (info.is_locked) {
                        // Titik Acuan Terkunci
                        this.refMarker = L.marker([info.lat, info.lng]).addTo(this.map)
                            .bindPopup(`<b>Titik Acuan ${this.type.toUpperCase()}</b><br>Terkunci sejak: ${info.locked_at}`);

                        // Lingkaran Radius 75 Meter
                        this.radiusCircle = L.circle([info.lat, info.lng], {
                            color: this.isWithinRadius ? '#10b981' : '#ef4444',
                            fillColor: this.isWithinRadius ? '#10b981' : '#ef4444',
                            fillOpacity: 0.15,
                            radius: this.maxRadius
                        }).addTo(this.map);

                        // Titik GPS Pengguna
                        if (this.gpsReady) {
                            const userIcon = L.divIcon({
                                className: 'custom-user-marker',
                                html: '<div class="w-4 h-4 bg-blue-600 rounded-full border-2 border-white shadow-lg ring-4 ring-blue-400/40 animate-pulse"></div>',
                                iconSize: [16, 16],
                                iconAnchor: [8, 8]
                            });

                            this.userMarker = L.marker([this.currentLat, this.currentLng], { icon: userIcon }).addTo(this.map)
                                .bindPopup(`<b>Posisi Anda Saat Ini</b><br>Jarak: ${this.currentDistance.toFixed(1)} m`);

                            const bounds = L.latLngBounds([
                                [info.lat, info.lng],
                                [this.currentLat, this.currentLng]
                            ]);
                            this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 18 });
                        }
                    } else if (this.gpsReady) {
                        // Titik Belum Terkunci: tampilkan lingkaran radius di posisi saat ini sebagai simulasi
                        this.radiusCircle = L.circle([this.currentLat, this.currentLng], {
                            color: '#3b82f6',
                            fillColor: '#3b82f6',
                            fillOpacity: 0.15,
                            radius: this.maxRadius
                        }).addTo(this.map);

                        const userIcon = L.divIcon({
                            className: 'custom-user-marker',
                            html: '<div class="w-4 h-4 bg-amber-500 rounded-full border-2 border-white shadow-lg ring-4 ring-amber-400/40"></div>',
                            iconSize: [16, 16],
                            iconAnchor: [8, 8]
                        });

                        this.userMarker = L.marker([this.currentLat, this.currentLng], { icon: userIcon }).addTo(this.map)
                            .bindPopup(`<b>Calon Titik Acuan ${this.type.toUpperCase()}</b><br>Akan dikunci saat presensi.`);

                        this.map.setView([this.currentLat, this.currentLng], 17);
                    }
                },

                // -------------------------------------------------------------
                // 5. SUBMIT PRESENSI KE BACKEND
                // -------------------------------------------------------------
                async submitAttendance(action) {
                    this.submitErrorMessage = '';

                    if (!this.gpsReady) {
                        this.submitErrorMessage = 'Menunggu data koordinat GPS akurat. Harap pastikan GPS aktif.';
                        return;
                    }

                    if (!this.photoTaken || !this.photoData) {
                        this.submitErrorMessage = 'Harap ambil foto selfie verifikasi terlebih dahulu.';
                        return;
                    }

                    if (this.currentLocationInfo.is_locked && !this.isWithinRadius) {
                        this.submitErrorMessage = `Anda berada di luar radius presensi (Jarak: ${this.currentDistance.toFixed(1)} meter. Maksimal: 75 meter). Presensi ditolak.`;
                        return;
                    }

                    this.isSubmitting = true;

                    try {
                        const response = await fetch('{{ route("presensi.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                attendance_type: this.type,
                                action: action,
                                latitude: this.currentLat,
                                longitude: this.currentLng,
                                photo: this.photoData,
                                notes: this.notes
                            })
                        });

                        const result = await response.json();

                        if (!response.ok || !result.success) {
                            throw new Error(result.message || 'Gagal memproses presensi.');
                        }

                        // Berhasil! Reload halaman agar state sinkron
                        alert(result.message || 'Presensi berhasil dicatat!');
                        window.location.reload();

                    } catch (err) {
                        console.error('Submit attendance error:', err);
                        this.submitErrorMessage = err.message || 'Terjadi kesalahan sistem.';
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>
