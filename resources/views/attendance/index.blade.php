<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white flex items-center justify-center text-lg shadow-sm shrink-0">
                    📍
                </div>
                <div>
                    <h2 class="font-bold text-lg sm:text-xl text-slate-800 leading-tight">
                        {{ __('Presensi Kehadiran Pegawai') }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Presensi cepat dengan GPS Radius ≤ 75m &amp; Selfie 1-Klik</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('presensi.history') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 shadow-2xs transition">
                    <span>📜</span>
                    <span>Riwayat Saya</span>
                </a>
                @if(Auth::user()->hasRole(['admin', 'pimpinan']) || Auth::user()->isAtasan())
                    <a href="{{ route('admin.presensi.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 border border-transparent rounded-xl text-xs font-semibold text-white shadow-2xs transition">
                        <span>📊</span>
                        <span>Rekap Presensi</span>
                    </a>
                @endif
                @if(Auth::user()->hasRole('admin') || Auth::user()->canAccessExecutiveKepegawaianMenus())
                    <a href="{{ route('admin.presensi.locations') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-xl text-xs font-semibold text-indigo-700 transition">
                        <span>🎯</span>
                        <span>Titik Lokasi</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="py-5" x-data="attendanceApp()">
        <div class="w-full max-w-[1600px] mx-auto px-3 sm:px-6 lg:px-8 space-y-5">

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl shadow-2xs flex items-center gap-3 text-emerald-800">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm shrink-0">✓</span>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl shadow-2xs flex items-center gap-3 text-rose-800">
                    <span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-sm shrink-0">!</span>
                    <span class="font-medium text-sm">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Banner Status Jaringan & Antrean Presensi PWA Offline-First -->
            <div x-show="!isOnline || offlineQueue.length > 0 || offlineSyncSuccessMessage" x-transition class="rounded-2xl border shadow-2xs overflow-hidden"
                 :class="!isOnline ? 'bg-amber-50 border-amber-300' : (offlineQueue.length > 0 ? 'bg-blue-50 border-blue-300' : 'bg-emerald-50 border-emerald-300')">
                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shrink-0"
                             :class="!isOnline ? 'bg-amber-200/70 text-amber-800' : (offlineQueue.length > 0 ? 'bg-blue-200/70 text-blue-800' : 'bg-emerald-200/70 text-emerald-800')">
                            <span x-text="!isOnline ? '📴' : (offlineQueue.length > 0 ? '🔄' : '✅')"></span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-xs sm:text-sm font-bold"
                                    :class="!isOnline ? 'text-amber-900' : (offlineQueue.length > 0 ? 'text-blue-900' : 'text-emerald-900')"
                                    x-text="!isOnline ? 'Mode Presensi Offline-First Aktif (Tanpa Sinyal Internet)' : (offlineQueue.length > 0 ? 'Antrean Presensi Offline Siap Disinkronkan (' + offlineQueue.length + ' Data)' : 'Sinkronisasi Presensi Offline Berhasil')">
                                </h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                      :class="isOnline ? 'bg-emerald-100 text-emerald-700 border border-emerald-300' : 'bg-amber-200 text-amber-900'">
                                    <span x-text="isOnline ? '🟢 Online' : '⚡ Offline PWA'"></span>
                                </span>
                            </div>
                            <p class="text-xs mt-0.5 leading-relaxed"
                               :class="!isOnline ? 'text-amber-800' : (offlineQueue.length > 0 ? 'text-blue-800' : 'text-emerald-800')"
                               x-text="offlineSyncSuccessMessage ? offlineSyncSuccessMessage : (!isOnline ? 'Anda tetap dapat melakukan Check-In/Check-Out selama GPS berada di dalam radius ≤ 75m & telah mengambil foto selfie. Data disimpan aman di perangkat dan otomatis terkirim saat sinyal kembali.' : 'Terdapat rekaman presensi di memori perangkat Anda yang menunggu pengiriman ke server.')">
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0" x-show="offlineQueue.length > 0">
                        <button type="button"
                                @click="syncOfflineQueue()"
                                :disabled="!isOnline || isSyncingOffline"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:bg-slate-400 transition shadow-2xs flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isSyncingOffline }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span x-text="isSyncingOffline ? 'Menyinkronkan...' : 'Sinkronkan Sekarang (' + offlineQueue.length + ')'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Baris Atas: Ringkasan Status Kehadiran Hari Ini & Jam Kerja ASN -->
            <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/90 overflow-hidden">
                <div class="p-4 sm:p-5 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/15 flex items-center justify-center text-2xl shrink-0">
                            🗓️
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[11px] text-slate-300 font-semibold uppercase tracking-wider">Kehadiran Hari Ini</span>
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/30 font-medium">
                                    {{ Auth::user()->name }}
                                </span>
                            </div>
                            <div class="text-base sm:text-lg font-bold text-white mt-0.5">
                                {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        @if($todayAttendance)
                            <div class="px-3.5 py-2 rounded-xl bg-white/10 border border-white/15 text-xs flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span class="text-slate-300">Masuk:</span>
                                <span class="font-bold text-white font-mono">{{ $todayAttendance->check_in_time ? $todayAttendance->check_in_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-' }}</span>
                                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-white/15 text-blue-200">{{ strtoupper($todayAttendance->attendance_type) }}</span>
                            </div>
                            <div class="px-3.5 py-2 rounded-xl bg-white/10 border border-white/15 text-xs flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $todayAttendance->check_out_time ? 'bg-sky-400' : 'bg-amber-400 animate-pulse' }}"></span>
                                <span class="text-slate-300">Pulang:</span>
                                <span class="font-bold text-white font-mono">{{ $todayAttendance->check_out_time ? $todayAttendance->check_out_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : 'Belum Check-Out' }}</span>
                            </div>
                            <span class="px-3 py-2 rounded-xl text-xs font-bold {{ $todayAttendance->status === 'late' ? 'bg-amber-500/25 text-amber-200 border border-amber-400/30' : 'bg-emerald-500/25 text-emerald-200 border border-emerald-400/30' }}">
                                {{ $todayAttendance->status === 'late' ? '⏱️ Terlambat' : '✅ Tepat Waktu' }}
                            </span>
                        @else
                            <div class="px-3.5 py-2 rounded-xl bg-amber-500/20 border border-amber-400/30 text-amber-200 text-xs font-medium flex items-center gap-2">
                                <span>⏳</span>
                                <span>Anda belum melakukan presensi masuk (Check-In) hari ini.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Bar Info Ringkas Jam Kerja ASN UNRI -->
                <div class="px-4 sm:px-5 py-2.5 bg-slate-50 border-t border-slate-200/80 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span class="font-semibold text-slate-700 flex items-center gap-1.5">
                            <span>⏰</span> Jam Kerja ASN UNRI:
                        </span>
                        <span><strong>Senin–Kamis:</strong> 07:30–16:00 WIB <span class="text-slate-400">(Istirahat 12:00–13:00)</span></span>
                        <span class="hidden sm:inline text-slate-300">•</span>
                        <span><strong>Jumat:</strong> 07:30–16:30 WIB <span class="text-slate-400">(Istirahat 11:45–13:15)</span></span>
                    </div>
                    <span class="text-[11px] text-slate-500">Standar 37,5 Jam/Minggu (7,5 Jam Efektif/Hari)</span>
                </div>
            </div>

            <!-- GRID UTAMA: Panel Presensi Sederhana (Kiri) & Peta Lokasi (Kanan) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

                <!-- KOLOM KIRI: Form Presensi 3 Langkah Mudah (5 kolom di desktop) -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/90 p-5 space-y-5">

                        <!-- LANGKAH 1: Pilih Lokasi Kerja (WFO / WFH) -->
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[11px] flex items-center justify-center font-bold">1</span>
                                    <span>Pilih Lokasi Kerja</span>
                                </label>
                                <span class="text-[11px] font-medium px-2 py-0.5 rounded-full"
                                      :class="currentLocationInfo.is_locked ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                                      x-text="currentLocationInfo.is_locked ? '🔒 Titik Acuan Terkunci' : '📍 Otomatis Kunci Titik Baru'">
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <button type="button"
                                        @click="setType('wfo')"
                                        :class="type === 'wfo' ? 'border-blue-600 bg-blue-50/70 text-blue-800 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300 text-slate-600 bg-white'"
                                        class="p-3 border rounded-xl flex items-center gap-3 transition text-left">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shrink-0"
                                         :class="type === 'wfo' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600'">
                                        🏢
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs sm:text-sm font-bold truncate">WFO (Kantor)</div>
                                        <div class="text-[10px] text-slate-500 truncate" x-text="locations.wfo.is_locked ? 'Acuan Terkunci ✓' : 'Belum Dikunci'"></div>
                                    </div>
                                </button>

                                <button type="button"
                                        @click="setType('wfh')"
                                        :class="type === 'wfh' ? 'border-indigo-600 bg-indigo-50/70 text-indigo-800 ring-2 ring-indigo-500/20' : 'border-slate-200 hover:border-slate-300 text-slate-600 bg-white'"
                                        class="p-3 border rounded-xl flex items-center gap-3 transition text-left">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shrink-0"
                                         :class="type === 'wfh' ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600'">
                                        🏠
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs sm:text-sm font-bold truncate">WFH (Rumah)</div>
                                        <div class="text-[10px] text-slate-500 truncate" x-text="locations.wfh.is_locked ? 'Acuan Terkunci ✓' : 'Belum Dikunci'"></div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- LANGKAH 2: Ambil Foto Selfie 1-Klik -->
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[11px] flex items-center justify-center font-bold">2</span>
                                    <span>Ambil Foto Selfie</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <span x-show="cameraActive && !photoTaken" class="text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                        🟢 Kamera Siap
                                    </span>
                                    <button type="button"
                                            x-show="hasMultipleCameras && !photoTaken"
                                            @click="switchCamera()"
                                            class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1">
                                        🔄 Putar Kamera
                                    </button>
                                </div>
                            </div>

                            <!-- Viewfinder Kamera Bersih & Profesional -->
                            <div class="relative w-full aspect-[4/3] bg-slate-900 rounded-2xl overflow-hidden shadow-inner flex items-center justify-center border border-slate-200">
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

                                <!-- Badge Status Saat Foto Sudah Diambil -->
                                <div x-show="photoTaken" class="absolute top-3 right-3 z-20 bg-emerald-600/95 backdrop-blur-xs text-white px-3 py-1 rounded-full text-xs font-bold shadow-md flex items-center gap-1.5">
                                    <span>✅</span>
                                    <span>Foto Tersimpan</span>
                                </div>

                                <!-- Panduan Bingkai Wajah Sederhana (Tanpa Tantangan Gerakan yang Menyulitkan) -->
                                <div x-show="cameraActive && !photoTaken" class="absolute inset-0 pointer-events-none flex flex-col items-center justify-between p-4 z-10">
                                    <div class="px-3 py-1 rounded-full bg-slate-900/60 backdrop-blur-xs text-white text-[11px] font-medium border border-white/15">
                                        Posisikan wajah di tengah lalu klik tombol Ambil Foto
                                    </div>
                                    <div class="w-36 h-44 sm:w-40 sm:h-48 border-2 border-dashed border-white/60 rounded-full"></div>
                                    <div></div>
                                </div>

                                <!-- Fallback / Loading Overlay -->
                                <div x-show="!cameraActive && !photoTaken" class="p-6 text-center text-slate-300 space-y-3 z-20">
                                    <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl mx-auto">📷</div>
                                    <p class="text-xs text-slate-300 max-w-xs mx-auto" x-text="cameraErrorMessage || 'Menyiapkan kamera perangkat...'"></p>
                                    <button type="button"
                                            @click="initCamera()"
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition shadow">
                                        Aktifkan Kamera
                                    </button>
                                </div>

                                <!-- Hidden Canvas for Snapshot -->
                                <canvas id="captureCanvas" class="hidden"></canvas>
                            </div>

                            <!-- Tombol Ambil Foto 1-Klik / Foto Ulang -->
                            <div>
                                <button type="button"
                                        x-show="cameraActive && !photoTaken"
                                        @click="takeSnapshot()"
                                        class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.99]">
                                    <span>📸</span>
                                    <span>Ambil Foto Selfie</span>
                                </button>

                                <button type="button"
                                        x-show="photoTaken"
                                        @click="retakeSnapshot()"
                                        class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center justify-center gap-2 border border-slate-200 transition">
                                    <span>🔄</span>
                                    <span>Ambil Ulang Foto</span>
                                </button>
                            </div>
                        </div>

                        <!-- LANGKAH 3: Status Kelengkapan (3 Syarat Sederhana) & Kirim Presensi -->
                        <div class="space-y-3 pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[11px] flex items-center justify-center font-bold">3</span>
                                    <span>Kirim Presensi</span>
                                </label>
                                <span class="text-[11px] font-semibold"
                                      :class="canSubmitSecurity ? 'text-emerald-600' : 'text-slate-500'"
                                      x-text="canSubmitSecurity ? '✓ Semua Syarat Terpenuhi' : 'Lengkapi 3 syarat di bawah'">
                                </span>
                            </div>

                            <!-- 3 Indikator Syarat Sederhana -->
                            <div class="grid grid-cols-3 gap-2">
                                <!-- Syarat 1: GPS Aktif -->
                                <div class="p-2.5 rounded-xl border text-center transition"
                                     :class="gpsReady && gpsAccuracy > 0 && !isMock ? 'bg-emerald-50/80 border-emerald-200 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-500'">
                                    <div class="text-sm font-bold" x-text="gpsReady && gpsAccuracy > 0 && !isMock ? '✅' : '⏳'"></div>
                                    <div class="text-[11px] font-bold mt-0.5">GPS Aktif</div>
                                    <div class="text-[10px] opacity-80 truncate" x-text="gpsReady ? '±' + Math.round(gpsAccuracy) + 'm' : 'Mencari...'"></div>
                                </div>

                                <!-- Syarat 2: Radius <= 75m -->
                                <div class="p-2.5 rounded-xl border text-center transition"
                                     :class="gpsReady && isWithinRadius ? 'bg-emerald-50/80 border-emerald-200 text-emerald-800' : (gpsReady && !isWithinRadius ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-slate-50 border-slate-200 text-slate-500')">
                                    <div class="text-sm font-bold" x-text="gpsReady && isWithinRadius ? '✅' : (gpsReady && !isWithinRadius ? '⚠️' : '⏳')"></div>
                                    <div class="text-[11px] font-bold mt-0.5">Radius ≤ 75m</div>
                                    <div class="text-[10px] opacity-80 truncate" x-text="distanceText"></div>
                                </div>

                                <!-- Syarat 3: Foto Selfie -->
                                <div class="p-2.5 rounded-xl border text-center transition"
                                     :class="photoTaken ? 'bg-emerald-50/80 border-emerald-200 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-500'">
                                    <div class="text-sm font-bold" x-text="photoTaken ? '✅' : '📷'"></div>
                                    <div class="text-[11px] font-bold mt-0.5">Foto Selfie</div>
                                    <div class="text-[10px] opacity-80 truncate" x-text="photoTaken ? 'Siap Kirim' : 'Belum Foto'"></div>
                                </div>
                            </div>

                            <!-- Catatan Opsional -->
                            <div>
                                <input type="text"
                                       x-model="notes"
                                       placeholder="Catatan aktivitas / keterangan singkat (opsional)..."
                                       class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/60 focus:bg-white focus:border-blue-500 focus:ring-blue-500 py-2.5 px-3">
                            </div>

                            <!-- Tombol Utama Check-In / Check-Out -->
                            @if(!$todayAttendance)
                                <button type="button"
                                        @click="submitAttendance('check_in')"
                                        :disabled="isSubmitting || !canSubmitSecurity"
                                        class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 shadow-md shadow-emerald-600/15 disabled:shadow-none transition transform active:scale-[0.99]">
                                    <span x-show="!isSubmitting">✅ Kirim Presensi Masuk (Check-In)</span>
                                    <span x-show="isSubmitting" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        Memproses Presensi Masuk...
                                    </span>
                                </button>
                            @elseif(!$todayAttendance->check_out_time)
                                <button type="button"
                                        @click="submitAttendance('check_out')"
                                        :disabled="isSubmitting || !canSubmitSecurity"
                                        class="w-full py-3.5 px-4 bg-amber-600 hover:bg-amber-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 shadow-md shadow-amber-600/15 disabled:shadow-none transition transform active:scale-[0.99]">
                                    <span x-show="!isSubmitting">🚪 Kirim Presensi Pulang (Check-Out)</span>
                                    <span x-show="isSubmitting" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        Memproses Presensi Pulang...
                                    </span>
                                </button>
                            @else
                                <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-center text-xs text-emerald-800 font-semibold">
                                    🎉 Anda telah menyelesaikan seluruh presensi (Masuk &amp; Pulang) hari ini.
                                </div>
                            @endif

                            <!-- Error Message Banner -->
                            <div x-show="submitErrorMessage"
                                 x-transition
                                 class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 flex items-start gap-2">
                                <span class="text-sm shrink-0">⚠️</span>
                                <span x-text="submitErrorMessage"></span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- KOLOM KANAN: Peta Radius & Riwayat Terakhir (7 kolom di desktop) -->
                <div class="lg:col-span-7 space-y-5">

                    <!-- Kartu Peta Lokasi -->
                    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/90 p-5 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <span>🗺️</span>
                                    <span>Peta Lokasi &amp; Radius Presensi (Maks. 75 Meter)</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Pastikan titik biru berada di dalam lingkaran area lokasi acuan Anda.</p>
                            </div>

                            <button type="button"
                                    @click="refreshGpsLocation()"
                                    :disabled="gpsLoading"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition self-start sm:self-auto">
                                <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': gpsLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                <span>Perbarui GPS</span>
                            </button>
                        </div>

                        <!-- Leaflet Map Container -->
                        <div class="relative w-full h-[320px] sm:h-[360px] rounded-2xl overflow-hidden border border-slate-200 z-0">
                            <div id="attendanceMap" class="w-full h-full"></div>

                            <!-- Map Overlay Badge Status Jarak -->
                            <div class="absolute bottom-3 left-3 right-3 sm:right-auto sm:max-w-md z-[1000] bg-white/95 backdrop-blur-xs p-3 rounded-xl border shadow-md text-xs"
                                 :class="distanceColorClass">
                                <div class="flex items-center justify-between gap-2 font-bold mb-0.5">
                                    <span class="flex items-center gap-1.5">
                                        <span x-text="distanceIcon"></span>
                                        <span x-text="distanceStatusTitle"></span>
                                    </span>
                                    <span class="font-mono text-xs px-2 py-0.5 rounded-lg bg-slate-100 text-slate-800" x-text="distanceText"></span>
                                </div>
                                <p class="text-[11px] leading-snug text-slate-600" x-text="distanceStatusDesc"></p>
                            </div>
                        </div>

                        <!-- Ringkasan Koordinat -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between">
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">📍 Posisi GPS Anda</div>
                                    <div class="text-xs font-mono font-semibold text-slate-800 mt-0.5" x-text="gpsReady ? currentLat.toFixed(6) + ', ' + currentLng.toFixed(6) : 'Mendeteksi lokasi...'"></div>
                                </div>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" :class="gpsBadgeClass" x-text="gpsReady ? '±' + Math.round(gpsAccuracy) + 'm' : '...'"></span>
                            </div>

                            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between">
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">🎯 Titik Acuan (<span x-text="type.toUpperCase()"></span>)</div>
                                    <template x-if="currentLocationInfo.is_locked">
                                        <div class="text-xs font-mono font-semibold text-slate-800 mt-0.5" x-text="currentLocationInfo.lat.toFixed(6) + ', ' + currentLocationInfo.lng.toFixed(6)"></div>
                                    </template>
                                    <template x-if="!currentLocationInfo.is_locked">
                                        <div class="text-xs font-medium text-amber-700 mt-0.5">Akan dikunci saat presensi pertama</div>
                                    </template>
                                </div>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                      :class="currentLocationInfo.is_locked ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                      x-text="currentLocationInfo.is_locked ? 'Terkunci' : 'Baru'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Riwayat Presensi Terakhir -->
                    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/90 overflow-hidden">
                        <div class="p-4 sm:px-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800">Riwayat Presensi Terakhir</h3>
                                <p class="text-[11px] text-slate-500">Catatan kehadiran terbaru Anda di sistem.</p>
                            </div>
                            <a href="{{ route('presensi.history') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                                Lihat Semua &rarr;
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 text-xs text-left">
                                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="px-4 py-2.5">Tanggal</th>
                                        <th class="px-4 py-2.5">Tipe</th>
                                        <th class="px-4 py-2.5">Masuk</th>
                                        <th class="px-4 py-2.5">Pulang</th>
                                        <th class="px-4 py-2.5">Jarak</th>
                                        <th class="px-4 py-2.5">Selfie</th>
                                        <th class="px-4 py-2.5">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse($recentAttendances as $item)
                                        <tr class="hover:bg-slate-50/70 transition">
                                            <td class="px-4 py-2.5 font-semibold text-slate-800 whitespace-nowrap">
                                                {{ $item->attendance_date->translatedFormat('d M Y') }}
                                            </td>
                                            <td class="px-4 py-2.5">
                                                <span class="px-2 py-0.5 rounded-full font-bold text-[10px] uppercase {{ $item->attendance_type === 'wfh' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700' }}">
                                                    {{ strtoupper($item->attendance_type) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2.5 font-mono font-medium text-slate-700 whitespace-nowrap">
                                                {{ $item->check_in_time ? $item->check_in_time->timezone('Asia/Jakarta')->format('H:i') : '-' }}
                                            </td>
                                            <td class="px-4 py-2.5 font-mono font-medium text-slate-700 whitespace-nowrap">
                                                {{ $item->check_out_time ? $item->check_out_time->timezone('Asia/Jakarta')->format('H:i') : '-' }}
                                            </td>
                                            <td class="px-4 py-2.5 font-mono text-slate-500 whitespace-nowrap">
                                                {{ number_format($item->check_in_distance_meters, 1) }}m
                                            </td>
                                            <td class="px-4 py-2.5">
                                                <div class="flex items-center gap-1.5">
                                                    @if($item->check_in_photo_path)
                                                        <a href="{{ $item->check_in_photo_url }}" target="_blank" title="Foto Masuk" class="block w-7 h-7 rounded-lg overflow-hidden border border-slate-200 hover:ring-2 hover:ring-blue-500 transition">
                                                            <img src="{{ $item->check_in_photo_url }}" class="w-full h-full object-cover">
                                                        </a>
                                                    @endif
                                                    @if($item->check_out_photo_path)
                                                        <a href="{{ $item->check_out_photo_url }}" target="_blank" title="Foto Pulang" class="block w-7 h-7 rounded-lg overflow-hidden border border-slate-200 hover:ring-2 hover:ring-amber-500 transition">
                                                            <img src="{{ $item->check_out_photo_url }}" class="w-full h-full object-cover">
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 whitespace-nowrap">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $item->status_badge['class'] }}">
                                                    {{ $item->status_badge['label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-6 text-center text-slate-400">
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

        </div>
    </div>

    <!-- SCRIPT LOGIKA KAMERA 1-KLIK, GPS, LEAFLET MAP & PWA OFFLINE-FIRST -->
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

                // Pilar 1: GPS & Sensor State
                currentLat: 0.5333,
                currentLng: 101.4500,
                gpsAccuracy: 0,
                gpsAltitude: null,
                gpsSpeed: null,
                gpsReady: false,
                gpsLoading: false,

                // Pilar 2: Kamera Live 1-Klik (Sederhana & Ramah Pengguna)
                cameraActive: false,
                facingMode: 'user',
                hasMultipleCameras: false,
                photoTaken: false,
                photoData: null,
                cameraErrorMessage: '',
                videoStream: null,
                livenessVerified: true,

                // Pilar 3: Token Anti-Replay & Integritas Perangkat
                attendanceToken: '{{ $attendanceToken }}',
                tokenRemaining: {{ $tokenTtl ?? 60 }},
                tokenTimer: null,
                deviceFingerprint: '',
                devicePlatform: navigator.platform || (navigator.userAgentData ? navigator.userAgentData.platform : 'Unknown'),
                isMock: false,

                // Pilar 4: PWA Offline-First State & IndexedDB Queue
                offlineSigningKey: '{{ $offlineSigningKey ?? "" }}',
                isOnline: navigator.onLine,
                offlineQueue: [],
                isSyncingOffline: false,
                offlineSyncSuccessMessage: '',

                // Map & Form State
                map: null,
                userMarker: null,
                refMarker: null,
                radiusCircle: null,
                notes: '',
                isSubmitting: false,
                submitErrorMessage: '',

                async init() {
                    this.startTokenCountdown();
                    this.detectMockOrHeadless();
                    this.deviceFingerprint = await this.generateDeviceFingerprint();
                    await this.loadOfflineQueue();

                    window.addEventListener('online', () => {
                        this.isOnline = true;
                        this.refreshToken();
                        this.syncOfflineQueue();
                    });
                    window.addEventListener('offline', () => {
                        this.isOnline = false;
                    });
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.addEventListener('message', (event) => {
                            if (event.data && event.data.type === 'SYNC_OFFLINE_ATTENDANCE') {
                                this.syncOfflineQueue();
                            }
                        });
                    }

                    if (this.isOnline && this.offlineQueue.length > 0) {
                        this.syncOfflineQueue();
                    }

                    this.$nextTick(() => {
                        this.initMap();
                        this.detectGpsLocation();
                        this.initCamera();
                    });
                },

                get currentLocationInfo() {
                    return this.type === 'wfh' ? this.locations.wfh : this.locations.wfo;
                },

                get canSubmitSecurity() {
                    return this.gpsReady &&
                           this.gpsAccuracy > 0 &&
                           !this.isMock &&
                           this.photoTaken &&
                           this.isWithinRadius &&
                           (this.tokenRemaining > 0 || !this.isOnline || !!this.offlineSigningKey);
                },

                // -------------------------------------------------------------
                // PILAR 3: TOKEN ANTI-REPLAY & DEVICE FINGERPRINT
                // -------------------------------------------------------------
                startTokenCountdown() {
                    if (this.tokenTimer) clearInterval(this.tokenTimer);
                    this.tokenTimer = setInterval(() => {
                        if (this.tokenRemaining > 0) {
                            this.tokenRemaining--;
                            if (this.tokenRemaining <= 5 && !this.isSubmitting && this.isOnline) {
                                this.refreshToken();
                            }
                        }
                    }, 1000);
                },

                async refreshToken() {
                    if (!this.isOnline) return;
                    try {
                        const response = await fetch('{{ route("presensi.token") }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (data.success && data.token) {
                            this.attendanceToken = data.token;
                            this.tokenRemaining = data.expires_in || 60;
                            if (data.offline_signing_key) {
                                this.offlineSigningKey = data.offline_signing_key;
                            }
                        }
                    } catch (e) {
                        console.warn('Auto refresh token gagal (kemungkinan mode offline):', e);
                    }
                },

                detectMockOrHeadless() {
                    if (navigator.webdriver === true) {
                        this.isMock = true;
                    }
                    if (window.chrome && (window.chrome.webdriver || window._phantom || window.__nightmare)) {
                        this.isMock = true;
                    }
                },

                async generateDeviceFingerprint() {
                    try {
                        const canvas = document.createElement('canvas');
                        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                        let webglInfo = '';
                        if (gl) {
                            const ext = gl.getExtension('WEBGL_debug_renderer_info');
                            if (ext) {
                                webglInfo = gl.getParameter(ext.UNMASKED_VENDOR_WEBGL) + '~' + gl.getParameter(ext.UNMASKED_RENDERER_WEBGL);
                            }
                        }
                        const raw = [
                            navigator.userAgent,
                            navigator.platform,
                            navigator.language,
                            screen.width + 'x' + screen.height + 'x' + screen.colorDepth,
                            Intl.DateTimeFormat().resolvedOptions().timeZone,
                            navigator.hardwareConcurrency || 1,
                            webglInfo
                        ].join('|||');

                        const msgBuffer = new TextEncoder().encode(raw);
                        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
                        const hashArray = Array.from(new Uint8Array(hashBuffer));
                        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('').substring(0, 32);
                    } catch (e) {
                        let hash = 5381;
                        const str = (navigator.userAgent || '') + (screen.width || '') + (screen.height || '');
                        for (let i = 0; i < str.length; i++) {
                            hash = ((hash << 5) + hash) + str.charCodeAt(i);
                        }
                        return 'fp_' + Math.abs(hash).toString(16).padStart(16, '0');
                    }
                },

                // -------------------------------------------------------------
                // PILAR 1: GEOLOCATION & SENSOR ACCURACY
                // -------------------------------------------------------------
                detectGpsLocation() {
                    if (!navigator.geolocation) {
                        this.submitErrorMessage = 'Peramban Anda tidak mendukung HTML5 Geolocation.';
                        return;
                    }

                    this.gpsLoading = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.currentLat = pos.coords.latitude;
                            this.currentLng = pos.coords.longitude;
                            this.gpsAccuracy = pos.coords.accuracy;
                            this.gpsAltitude = pos.coords.altitude || null;
                            this.gpsSpeed = pos.coords.speed || null;
                            this.gpsReady = true;
                            this.gpsLoading = false;

                            if (this.gpsAccuracy <= 0) {
                                this.isMock = true;
                            }

                            if (pos.coords.isMock === true || pos.coords.mocked === true || pos.mocked === true) {
                                this.isMock = true;
                            }

                            if (pos.timestamp && Math.abs(Date.now() - pos.timestamp) > 120000) {
                                this.isMock = true;
                                this.submitErrorMessage = 'Peringatan: Koordinat GPS kedaluwarsa atau terdeteksi injeksi lokasi statis.';
                            }

                            this.updateMapLayers();
                        },
                        (err) => {
                            console.error('GPS error:', err);
                            this.gpsLoading = false;
                            let msg = 'Gagal mendeteksi lokasi GPS.';
                            if (err.code === 1) {
                                msg = 'Izin lokasi GPS ditolak! Harap aktifkan izin lokasi di peramban Anda.';
                            } else if (err.code === 2) {
                                msg = 'Sinyal GPS tidak tersedia atau perangkat di ruang tertutup.';
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

                get gpsBadgeClass() {
                    if (!this.gpsReady) return 'bg-slate-100 text-slate-600';
                    if (this.gpsAccuracy <= 0 || this.isMock) return 'bg-rose-100 text-rose-700';
                    return 'bg-emerald-100 text-emerald-700';
                },

                // -------------------------------------------------------------
                // PILAR 2: KAMERA LIVE 1-KLIK
                // -------------------------------------------------------------
                async initCamera() {
                    this.cameraErrorMessage = '';
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.cameraErrorMessage = 'Peramban ini tidak mendukung akses kamera langsung.';
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
                        this.livenessVerified = true;
                    } catch (err) {
                        console.error('Camera access error:', err);
                        this.cameraActive = false;
                        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                            this.cameraErrorMessage = 'Izin kamera ditolak. Harap izinkan akses kamera di pengaturan peramban.';
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
                    this.livenessVerified = true;
                },

                retakeSnapshot() {
                    this.photoTaken = false;
                    this.photoData = null;
                },

                // -------------------------------------------------------------
                // HAVERSINE FORMULA DI FRONTEND (Meter)
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
                    return this.currentDistance.toFixed(1) + ' m';
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
                        return `Jarak Anda ${this.currentDistance.toFixed(1)}m dari titik acuan (Maks: 75m). Siap presensi!`;
                    }
                    return `Jarak Anda ${this.currentDistance.toFixed(1)}m (melebihi batas 75m). Silakan mendekat ke lokasi acuan.`;
                },

                get distanceColorClass() {
                    if (!this.currentLocationInfo.is_locked) return 'border-amber-300 text-amber-900';
                    return this.isWithinRadius ? 'border-emerald-300 text-emerald-900' : 'border-rose-300 text-rose-900 bg-rose-50/95';
                },

                // -------------------------------------------------------------
                // LEAFLET MAP VISUALIZATION
                // -------------------------------------------------------------
                initMap() {
                    if (typeof L === 'undefined') return;
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

                    if (this.userMarker) this.map.removeLayer(this.userMarker);
                    if (this.refMarker) this.map.removeLayer(this.refMarker);
                    if (this.radiusCircle) this.map.removeLayer(this.radiusCircle);

                    const info = this.currentLocationInfo;

                    if (info.is_locked) {
                        this.refMarker = L.marker([info.lat, info.lng]).addTo(this.map)
                            .bindPopup(`<b>Titik Acuan ${this.type.toUpperCase()}</b><br>Terkunci sejak: ${info.locked_at}`);

                        this.radiusCircle = L.circle([info.lat, info.lng], {
                            color: this.isWithinRadius ? '#10b981' : '#ef4444',
                            fillColor: this.isWithinRadius ? '#10b981' : '#ef4444',
                            fillOpacity: 0.15,
                            radius: this.maxRadius
                        }).addTo(this.map);

                        if (this.gpsReady) {
                            const userIcon = L.divIcon({
                                className: 'custom-user-marker',
                                html: '<div class="w-4 h-4 bg-blue-600 rounded-full border-2 border-white shadow-lg ring-4 ring-blue-400/40 animate-pulse"></div>',
                                iconSize: [16, 16],
                                iconAnchor: [8, 8]
                            });

                            this.userMarker = L.marker([this.currentLat, this.currentLng], { icon: userIcon }).addTo(this.map)
                                .bindPopup(`<b>Posisi Anda Saat Ini</b><br>Jarak: ${this.currentDistance.toFixed(1)} m<br>Akurasi GPS: ±${Math.round(this.gpsAccuracy)} m`);

                            const bounds = L.latLngBounds([
                                [info.lat, info.lng],
                                [this.currentLat, this.currentLng]
                            ]);
                            this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 18 });
                        }
                    } else if (this.gpsReady) {
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

                setType(newType) {
                    this.type = newType;
                    this.updateMapLayers();
                },

                // -------------------------------------------------------------
                // PILAR 4: PWA OFFLINE-FIRST INDEXEDDB & CRYPTOGRAPHIC PROOF
                // -------------------------------------------------------------
                async computeSha256(message) {
                    if (window.crypto && window.crypto.subtle) {
                        const msgBuffer = new TextEncoder().encode(message);
                        const hashBuffer = await window.crypto.subtle.digest('SHA-256', msgBuffer);
                        const hashArray = Array.from(new Uint8Array(hashBuffer));
                        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                    }
                    function rightRotate(value, amount) { return (value >>> amount) | (value << (32 - amount)); }
                    const mathPow = Math.pow, maxWord = mathPow(2, 32);
                    let result = '', words = [], asciiBitLength = message.length * 8;
                    let hash = [], k = [], primeCounter = 0, isComposite = {};
                    for (let candidate = 2; primeCounter < 64; candidate++) {
                        if (!isComposite[candidate]) {
                            for (let i = 0; i < 313; i += candidate) isComposite[i] = candidate;
                            hash[primeCounter] = (mathPow(candidate, .5) * maxWord) | 0;
                            k[primeCounter++] = (mathPow(candidate, 1 / 3) * maxWord) | 0;
                        }
                    }
                    message += '\x80';
                    while (message.length % 64 - 56) message += '\x00';
                    for (let i = 0; i < message.length; i++) {
                        const j = message.charCodeAt(i);
                        words[i >> 2] |= j << ((3 - i) % 4) * 8;
                    }
                    words[words.length] = ((asciiBitLength / maxWord) | 0);
                    words[words.length] = (asciiBitLength);
                    for (let j = 0; j < words.length;) {
                        const w = words.slice(j, j += 16), oldHash = hash.slice(0);
                        for (let i = 0; i < 64; i++) {
                            const w15 = w[i - 15], w2 = w[i - 2];
                            const a = hash[0], e = hash[4];
                            const temp1 = hash[7]
                                + (rightRotate(e, 6) ^ rightRotate(e, 11) ^ rightRotate(e, 25))
                                + ((e & hash[5]) ^ ((~e) & hash[6]))
                                + k[i]
                                + (w[i] = (i < 16) ? w[i] : (
                                    w[i - 16]
                                    + (rightRotate(w15, 7) ^ rightRotate(w15, 18) ^ (w15 >>> 3))
                                    + w[i - 7]
                                    + (rightRotate(w2, 17) ^ rightRotate(w2, 19) ^ (w2 >>> 10))
                                ) | 0);
                            const temp2 = (rightRotate(a, 2) ^ rightRotate(a, 13) ^ rightRotate(a, 22))
                                + ((a & hash[1]) ^ (a & hash[2]) ^ (hash[1] & hash[2]));
                            hash = [(temp1 + temp2) | 0].concat(hash);
                            hash[4] = (hash[4] + temp1) | 0;
                            hash.pop();
                        }
                        for (let i = 0; i < 8; i++) hash[i] = (hash[i] + oldHash[i]) | 0;
                    }
                    for (let i = 0; i < 8; i++) {
                        for (let j = 3; j + 1; j--) {
                            const b = (hash[i] >> (j * 8)) & 255;
                            result += ((b < 16) ? 0 : '') + b.toString(16);
                        }
                    }
                    return result;
                },

                openOfflineDb() {
                    return new Promise((resolve, reject) => {
                        if (!('indexedDB' in window)) {
                            return reject(new Error('IndexedDB tidak didukung pada peramban ini.'));
                        }
                        const request = indexedDB.open('sikap_offline_attendance_db', 1);
                        request.onupgradeneeded = (e) => {
                            const db = e.target.result;
                            if (!db.objectStoreNames.contains('outbox')) {
                                db.createObjectStore('outbox', { keyPath: 'id', autoIncrement: true });
                            }
                        };
                        request.onsuccess = (e) => resolve(e.target.result);
                        request.onerror = (e) => reject(e.target.error);
                    });
                },

                async loadOfflineQueue() {
                    try {
                        const db = await this.openOfflineDb();
                        const tx = db.transaction('outbox', 'readonly');
                        const store = tx.objectStore('outbox');
                        const req = store.getAll();
                        this.offlineQueue = await new Promise((res, rej) => {
                            req.onsuccess = () => res(req.result || []);
                            req.onerror = () => rej(req.error);
                        });
                    } catch (e) {
                        this.offlineQueue = [];
                    }
                },

                async saveToOfflineQueue(payload) {
                    const db = await this.openOfflineDb();
                    const tx = db.transaction('outbox', 'readwrite');
                    const store = tx.objectStore('outbox');
                    await new Promise((res, rej) => {
                        const req = store.add(payload);
                        req.onsuccess = () => res(req.result);
                        req.onerror = () => rej(req.error);
                    });
                    await this.loadOfflineQueue();
                    if ('serviceWorker' in navigator && 'SyncManager' in window) {
                        try {
                            const reg = await navigator.serviceWorker.ready;
                            await reg.sync.register('sync-offline-attendance');
                        } catch (_) {}
                    }
                },

                async removeOfflineItem(id) {
                    const db = await this.openOfflineDb();
                    const tx = db.transaction('outbox', 'readwrite');
                    const store = tx.objectStore('outbox');
                    await new Promise((res, rej) => {
                        const req = store.delete(id);
                        req.onsuccess = () => res();
                        req.onerror = () => rej(req.error);
                    });
                    await this.loadOfflineQueue();
                },

                async syncOfflineQueue() {
                    if (!this.isOnline || this.isSyncingOffline) return;
                    await this.loadOfflineQueue();
                    if (this.offlineQueue.length === 0) return;

                    this.isSyncingOffline = true;
                    this.submitErrorMessage = '';
                    let syncedCount = 0;

                    try {
                        for (const item of this.offlineQueue) {
                            const response = await fetch('{{ route("presensi.store") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify(item.payload)
                            });

                            const result = await response.json();
                            if (response.ok && result.success) {
                                await this.removeOfflineItem(item.id);
                                syncedCount++;
                            } else if (response.status === 422) {
                                await this.removeOfflineItem(item.id);
                                this.submitErrorMessage = result.message || 'Data presensi offline ditolak oleh validasi server.';
                            }
                        }

                        if (syncedCount > 0) {
                            this.offlineSyncSuccessMessage = `Berhasil menyinkronkan ${syncedCount} data presensi offline ke server SIKAP! Memuat ulang...`;
                            setTimeout(() => window.location.reload(), 1500);
                        }
                    } catch (e) {
                        console.warn('Sinkronisasi antrean presensi offline tertunda:', e);
                    } finally {
                        this.isSyncingOffline = false;
                    }
                },

                // -------------------------------------------------------------
                // SUBMIT PRESENSI KE BACKEND DENGAN VALIDASI & OFFLINE FALLBACK
                // -------------------------------------------------------------
                async submitAttendance(action) {
                    this.submitErrorMessage = '';

                    if (!this.gpsReady) {
                        this.submitErrorMessage = 'Menunggu koordinat lokasi GPS. Pastikan GPS perangkat Anda aktif.';
                        return;
                    }

                    if (this.gpsAccuracy <= 0 || this.isMock) {
                        this.submitErrorMessage = 'Presensi ditolak: Sinyal lokasi tidak valid atau terdeteksi Mock/Fake GPS.';
                        return;
                    }

                    if (!this.photoTaken || !this.photoData) {
                        this.submitErrorMessage = 'Silakan klik tombol Ambil Foto Selfie terlebih dahulu.';
                        return;
                    }

                    if (this.currentLocationInfo.is_locked && !this.isWithinRadius) {
                        this.submitErrorMessage = `Anda berada di luar radius lokasi acuan (Jarak: ${this.currentDistance.toFixed(1)} meter, maksimal: 75 meter).`;
                        return;
                    }

                    this.isSubmitting = true;
                    const capturedAtSec = Math.floor(Date.now() / 1000);
                    const latFixed = Number(this.currentLat).toFixed(6);
                    const lngFixed = Number(this.currentLng).toFixed(6);
                    const canonical = `${this.offlineSigningKey}|${this.type.toLowerCase()}|${action}|${latFixed}|${lngFixed}|${capturedAtSec}|${this.deviceFingerprint}`;
                    const offlineProofHash = await this.computeSha256(canonical);

                    const basePayload = {
                        attendance_type: this.type,
                        action: action,
                        latitude: this.currentLat,
                        longitude: this.currentLng,
                        photo: this.photoData,
                        notes: this.notes,
                        accuracy: this.gpsAccuracy,
                        altitude: this.gpsAltitude,
                        speed: this.gpsSpeed,
                        is_mock: this.isMock,
                        client_timestamp: Date.now(),
                        liveness_verified: true,
                        liveness_challenge: 'live_camera',
                        device_fingerprint: this.deviceFingerprint,
                        device_platform: this.devicePlatform,
                        token: this.attendanceToken
                    };

                    if (!this.isOnline || this.tokenRemaining <= 0) {
                        try {
                            await this.saveToOfflineQueue({
                                captured_at: capturedAtSec,
                                payload: {
                                    ...basePayload,
                                    is_offline_sync: true,
                                    offline_captured_at: capturedAtSec,
                                    offline_proof_hash: offlineProofHash
                                }
                            });
                            this.offlineSyncSuccessMessage = 'Presensi berhasil disimpan di memori perangkat (Mode Offline PWA) dan akan otomatis dikirim saat sinyal internet kembali aktif!';
                            alert('📴 Mode Offline PWA: Presensi Anda telah berhasil direkam pada pukul ' + new Date(capturedAtSec * 1000).toLocaleTimeString('id-ID') + '. Data akan otomatis disinkronkan ke server begitu sinyal kembali.');
                        } catch (dbErr) {
                            this.submitErrorMessage = 'Gagal menyimpan presensi ke memori offline: ' + dbErr.message;
                        } finally {
                            this.isSubmitting = false;
                        }
                        return;
                    }

                    try {
                        const response = await fetch('{{ route("presensi.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(basePayload)
                        });

                        const result = await response.json();

                        if (!response.ok || !result.success) {
                            throw new Error(result.message || 'Gagal memproses presensi.');
                        }

                        alert(result.message || 'Presensi berhasil dicatat!');
                        window.location.reload();

                    } catch (err) {
                        if (err instanceof TypeError || !navigator.onLine) {
                            this.isOnline = false;
                            await this.saveToOfflineQueue({
                                captured_at: capturedAtSec,
                                payload: {
                                    ...basePayload,
                                    is_offline_sync: true,
                                    offline_captured_at: capturedAtSec,
                                    offline_proof_hash: offlineProofHash
                                }
                            });
                            this.offlineSyncSuccessMessage = 'Koneksi terputus saat mengirim. Presensi Anda telah diamankan di antrean Offline PWA dan akan otomatis terkirim saat sinyal pulih!';
                            return;
                        }
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
