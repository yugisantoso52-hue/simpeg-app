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

            <!-- Ketentuan Jam Kerja Resmi ASN Universitas Riau -->
            <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-sky-50 rounded-xl border border-blue-200/80 p-4 sm:p-5 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-blue-200/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                            ⏰
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-blue-950">Ketentuan Jam Kerja ASN Universitas Riau</h3>
                            <p class="text-xs text-blue-700">Dasar: Standar Jam Kerja Instansi Pemerintah (37,5 Jam/Minggu — 7,5 Jam/Hari Efektif)</p>
                        </div>
                    </div>
                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-[11px] font-semibold border border-blue-200">
                        <span>🏛️</span> FKP Universitas Riau
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                    <div class="bg-white/80 backdrop-blur-sm rounded-lg p-3 border border-blue-100 shadow-2xs">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Senin — Kamis (7,5 Jam)</div>
                        <div class="mt-1 flex items-baseline gap-1.5">
                            <span class="text-xs font-extrabold text-blue-900">07:30 — 16:00</span>
                            <span class="text-[10px] text-slate-500">WIB</span>
                        </div>
                        <div class="text-[10px] text-slate-600 mt-1">
                            🥪 Istirahat: <strong>12:00 — 13:00</strong> (60 mnt)
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm rounded-lg p-3 border border-blue-100 shadow-2xs">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Jumat (7,5 Jam)</div>
                        <div class="mt-1 flex items-baseline gap-1.5">
                            <span class="text-xs font-extrabold text-blue-900">07:30 — 16:30</span>
                            <span class="text-[10px] text-slate-500">WIB</span>
                        </div>
                        <div class="text-[10px] text-slate-600 mt-1">
                            🕌 Istirahat: <strong>11:45 — 13:15</strong> (90 mnt)
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm rounded-lg p-3 border border-amber-200/80 bg-amber-50/40 shadow-2xs">
                        <div class="text-[10px] font-bold text-amber-900 uppercase tracking-wider">Disiplin & Sanksi Waktu</div>
                        <div class="text-[11px] text-amber-950 font-medium mt-1 leading-snug">
                            Akumulasi telat & pulang cepat mencapai <strong>7,5 jam (450 mnt)</strong> dalam sebulan setara <strong>1 hari tidak hadir</strong>.
                        </div>
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

                        <!-- Telemetri Keamanan & Sensor GPS -->
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5 font-bold text-slate-700">
                                    <span class="text-base">🛡️</span>
                                    <span>Token Anti-Replay:</span>
                                    <span class="font-mono px-1.5 py-0.5 rounded bg-white border border-slate-200" x-text="tokenRemaining + 's'"></span>
                                </div>
                                <span class="text-[10px] px-2 py-0.5 rounded font-semibold"
                                      :class="tokenRemaining > 15 ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-rose-100 text-rose-700 border border-rose-200 animate-pulse'">
                                    <span x-text="tokenRemaining > 15 ? 'Aktif' : 'Memperbarui...'"></span>
                                </span>
                            </div>
                            <!-- Bar Countdown Token -->
                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 transition-all duration-1000"
                                     :class="tokenRemaining > 15 ? 'bg-emerald-500' : 'bg-rose-500'"
                                     :style="'width: ' + ((tokenRemaining / 60) * 100) + '%'"></div>
                            </div>

                            <!-- Sensor Akurasi GPS & Mock Check -->
                            <div class="flex flex-wrap items-center justify-between gap-1 pt-1 text-[11px] border-t border-slate-200/60">
                                <span class="text-slate-500">Sensor GPS:</span>
                                <span class="font-semibold px-2 py-0.5 rounded text-[10px] flex items-center gap-1"
                                      :class="gpsBadgeClass">
                                    <span x-text="gpsBadgeIcon"></span>
                                    <span x-text="gpsBadgeText"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Kamera Real-Time & Liveness Detection (MediaDevices) -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>2. Uji Keaktifan & Foto Real-Time:</span>
                                </label>
                                <button type="button"
                                        x-show="hasMultipleCameras"
                                        @click="switchCamera()"
                                        class="text-[11px] text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1">
                                    🔄 Ganti Kamera
                                </button>
                            </div>

                            <!-- Area Video & Snapshot with HUD -->
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
                                <div x-show="!cameraActive && !photoTaken" class="p-4 text-center text-gray-400 space-y-2 z-20">
                                    <div class="text-3xl animate-pulse">📷</div>
                                    <p class="text-xs" x-text="cameraErrorMessage || 'Menghubungkan kamera...'"></p>
                                    <button type="button"
                                            @click="initCamera()"
                                            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition shadow">
                                        Aktifkan Kamera
                                    </button>
                                </div>

                                <!-- Hidden Canvases for capture and analysis -->
                                <canvas id="captureCanvas" class="hidden"></canvas>
                                <canvas id="analysisCanvas" class="hidden" width="160" height="120"></canvas>

                                <!-- Dynamic Face Target Oval -->
                                <div x-show="cameraActive && !photoTaken" class="absolute inset-0 pointer-events-none flex items-center justify-center p-6 z-10">
                                    <div class="w-44 h-56 border-2 rounded-full transition-all duration-300 flex flex-col items-center justify-between py-4"
                                         :class="livenessVerified ? 'border-emerald-400 shadow-[0_0_20px_rgba(16,185,129,0.5)] bg-emerald-500/10' : (faceDetected ? 'border-amber-400 animate-pulse bg-amber-500/5' : 'border-dashed border-white/60')">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full text-white backdrop-blur-sm"
                                              :class="livenessVerified ? 'bg-emerald-600' : 'bg-black/50'"
                                              x-text="livenessVerified ? '✓ TERVERIFIKASI' : (faceDetected ? 'WAJAH TERDETEKSI' : 'POSISIKAN WAJAH')"></span>
                                        
                                        <span class="text-2xl" x-show="livenessVerified">✅</span>
                                        
                                        <span class="text-[9px] text-white/80 font-mono" x-text="faceDetected ? 'Tunggal (1 Wajah)' : 'Mencari Wajah...'"></span>
                                    </div>
                                </div>

                                <!-- Floating Liveness Challenge HUD -->
                                <div x-show="cameraActive && !photoTaken"
                                     class="absolute bottom-2 left-2 right-2 bg-slate-900/85 backdrop-blur-md text-white p-2.5 rounded-xl border border-white/15 text-xs z-20 space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl" x-text="currentChallenge.icon"></span>
                                            <div>
                                                <div class="text-[10px] text-amber-300 font-bold uppercase tracking-wider">Uji Keaktifan Wajah</div>
                                                <div class="font-bold text-xs" x-text="currentChallenge.label"></div>
                                            </div>
                                        </div>
                                        <button type="button" @click="pickRandomChallenge()" title="Ganti Tantangan" class="text-[10px] text-slate-300 hover:text-white underline ml-2">
                                            Acak
                                        </button>
                                    </div>

                                    <!-- Progress Keaktifan -->
                                    <div class="w-full bg-slate-700/80 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 transition-all duration-200"
                                             :class="livenessVerified ? 'bg-emerald-400' : 'bg-gradient-to-r from-amber-400 to-emerald-400'"
                                             :style="'width: ' + livenessProgress + '%'"></div>
                                    </div>

                                    <div class="flex items-center justify-between text-[10px] text-slate-300 font-medium">
                                        <span x-text="livenessStatusHint"></span>
                                        <span class="font-mono font-bold" x-text="livenessProgress + '%'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Tombol Kamera -->
                            <div class="flex gap-2 pt-1">
                                <button type="button"
                                        x-show="cameraActive && !photoTaken"
                                        @click="takeSnapshot()"
                                        :disabled="!livenessVerified"
                                        :class="livenessVerified ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                        class="w-full py-2.5 px-4 text-white rounded-xl text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition">
                                    <span x-text="livenessVerified ? '📸 Ambil Foto Selfie (Keaktifan Valid)' : '⏳ Selesaikan Uji Keaktifan Dahulu'"></span>
                                </button>

                                <button type="button"
                                        x-show="photoTaken"
                                        @click="retakeSnapshot()"
                                        class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 border border-gray-300 transition">
                                    <span>🔄</span> Uji & Foto Ulang (Retake)
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
                                        :disabled="isSubmitting || !photoTaken || !gpsReady || !canSubmitSecurity"
                                        class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20 transition transform active:scale-95">
                                    <span x-show="!isSubmitting">✅ Kirim Presensi Masuk (Check-In)</span>
                                    <span x-show="isSubmitting" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        Memvalidasi & Memproses Presensi...
                                    </span>
                                </button>
                            @elseif(!$todayAttendance->check_out_time)
                                <button type="button"
                                        @click="submitAttendance('check_out')"
                                        :disabled="isSubmitting || !photoTaken || !gpsReady || !canSubmitSecurity"
                                        class="w-full py-3.5 px-4 bg-amber-600 hover:bg-amber-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 shadow-lg shadow-amber-600/20 transition transform active:scale-95">
                                    <span x-show="!isSubmitting">🚪 Kirim Presensi Pulang (Check-Out)</span>
                                    <span x-show="isSubmitting" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        Memvalidasi & Memproses Check-Out...
                                    </span>
                                </button>
                            @else
                                <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-center text-xs text-gray-500 font-medium">
                                    🎉 Anda telah menyelesaikan seluruh presensi (Masuk & Pulang) hari ini.
                                </div>
                            @endif

                            <!-- Petunjuk Alasan Tombol Belum Aktif jika ada syarat belum terpenuhi -->
                            <div x-show="!canSubmitSecurity && !isSubmitting" class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 p-2.5 rounded-lg space-y-1">
                                <div class="font-bold flex items-center gap-1">
                                    <span>⚠️</span> <span>Syarat Kelengkapan Presensi:</span>
                                </div>
                                <ul class="list-disc list-inside space-y-0.5 text-[10px]">
                                    <li :class="gpsReady && gpsAccuracy > 0 && gpsAccuracy <= maxRadius ? 'text-emerald-700 line-through' : 'text-amber-800'">Sinyal GPS akurat (≤ 75 meter).</li>
                                    <li :class="!isMock ? 'text-emerald-700 line-through' : 'text-rose-700 font-bold'">Bukan peramban mock/emulator otomatis.</li>
                                    <li :class="livenessVerified ? 'text-emerald-700 line-through' : 'text-amber-800'">Uji keaktifan wajah lolos tantangan.</li>
                                    <li :class="photoTaken ? 'text-emerald-700 line-through' : 'text-amber-800'">Foto selfie berhasil diambil.</li>
                                    <li :class="isWithinRadius ? 'text-emerald-700 line-through' : 'text-rose-700 font-bold'">Berada di dalam radius lokasi acuan (≤ 75m).</li>
                                </ul>
                            </div>

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

    <!-- SCRIPT LOGIKA KAMERA, GPS, LIVENESS DETECTION & LEAFLET MAP -->
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

                // Pilar 2: Kamera & Liveness Detection State
                cameraActive: false,
                facingMode: 'user',
                hasMultipleCameras: false,
                photoTaken: false,
                photoData: null,
                cameraErrorMessage: '',
                videoStream: null,

                challenges: [
                    { id: 'blink', label: 'Kedipkan mata Anda 2 kali', icon: '👁️', hint: 'Kedipkan mata Anda secara wajar di depan kamera' },
                    { id: 'smile', label: 'Tersenyum lebar ke arah kamera', icon: '😊', hint: 'Tersenyumlah hingga ekspresi wajah berubah' },
                    { id: 'head_turn', label: 'Tengokkan kepala sedikit ke samping', icon: '↔️', hint: 'Tolehkan kepala sedikit ke samping lalu kembali ke tengah' }
                ],
                currentChallengeIndex: 0,
                faceDetected: false,
                livenessVerified: false,
                livenessProgress: 0,
                livenessStatusHint: 'Posisikan wajah Anda tepat di dalam lingkaran',
                blinkCount: 0,
                lastBlinkDip: false,
                smileHoldCount: 0,
                headTurnPhase: 0, // 0: center, 1: turned, 2: returned
                baselineLum: null,
                prevFrameData: null,
                livenessLoopId: null,

                // Pilar 3: Token Anti-Replay & Integritas Perangkat
                attendanceToken: '{{ $attendanceToken }}',
                tokenRemaining: {{ $tokenTtl ?? 60 }},
                tokenTimer: null,
                deviceFingerprint: '',
                devicePlatform: navigator.platform || (navigator.userAgentData ? navigator.userAgentData.platform : 'Unknown'),
                isMock: false,

                // Map & Form State
                map: null,
                userMarker: null,
                refMarker: null,
                radiusCircle: null,
                notes: '',
                isSubmitting: false,
                submitErrorMessage: '',

                async init() {
                    this.pickRandomChallenge();
                    this.startTokenCountdown();
                    this.detectMockOrHeadless();
                    this.deviceFingerprint = await this.generateDeviceFingerprint();

                    this.$nextTick(() => {
                        this.initMap();
                        this.detectGpsLocation();
                        this.initCamera();
                    });
                },

                get currentLocationInfo() {
                    return this.type === 'wfh' ? this.locations.wfh : this.locations.wfo;
                },

                get currentChallenge() {
                    return this.challenges[this.currentChallengeIndex];
                },

                get canSubmitSecurity() {
                    return this.gpsReady &&
                           this.gpsAccuracy > 0 &&
                           this.gpsAccuracy <= this.maxRadius &&
                           !this.isMock &&
                           this.livenessVerified &&
                           this.photoTaken &&
                           this.isWithinRadius &&
                           this.tokenRemaining > 0;
                },

                // -------------------------------------------------------------
                // PILAR 3: TOKEN ANTI-REPLAY & DEVICE FINGERPRINT
                // -------------------------------------------------------------
                startTokenCountdown() {
                    if (this.tokenTimer) clearInterval(this.tokenTimer);
                    this.tokenTimer = setInterval(() => {
                        if (this.tokenRemaining > 0) {
                            this.tokenRemaining--;
                            // Auto-refresh token jika sisa <= 5 detik agar presensi tidak gagal di tengah jalan
                            if (this.tokenRemaining <= 5 && !this.isSubmitting) {
                                this.refreshToken();
                            }
                        }
                    }, 1000);
                },

                async refreshToken() {
                    try {
                        const response = await fetch('{{ route("presensi.token") }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (data.success && data.token) {
                            this.attendanceToken = data.token;
                            this.tokenRemaining = data.expires_in || 60;
                        }
                    } catch (e) {
                        console.warn('Auto refresh token gagal:', e);
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
                        // Fallback DJB2 hash
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

                            // Cek akurasi emulator
                            if (this.gpsAccuracy <= 0) {
                                this.isMock = true;
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
                    if (!this.gpsReady) return 'bg-gray-100 text-gray-700 border border-gray-200';
                    if (this.gpsAccuracy <= 0 || this.isMock) return 'bg-rose-100 text-rose-800 border border-rose-300';
                    if (this.gpsAccuracy > this.maxRadius) return 'bg-rose-100 text-rose-800 border border-rose-300';
                    if (this.gpsAccuracy <= 25) return 'bg-emerald-100 text-emerald-800 border border-emerald-300';
                    return 'bg-amber-100 text-amber-800 border border-amber-300';
                },

                get gpsBadgeIcon() {
                    if (!this.gpsReady) return '⏳';
                    if (this.gpsAccuracy <= 0 || this.isMock) return '⛔';
                    if (this.gpsAccuracy > this.maxRadius) return '⚠️';
                    return '✅';
                },

                get gpsBadgeText() {
                    if (!this.gpsReady) return 'Mendeteksi sinyal GPS...';
                    if (this.gpsAccuracy <= 0) return 'Mock/Fake GPS (0m)';
                    if (this.gpsAccuracy > this.maxRadius) return 'Sinyal Lemah: ±' + Math.round(this.gpsAccuracy) + 'm (>75m)';
                    return 'Akurasi: ±' + Math.round(this.gpsAccuracy) + 'm (Presisi)';
                },

                // -------------------------------------------------------------
                // PILAR 2: KAMERA & LIVENESS DETECTION (BROWSER-BASED)
                // -------------------------------------------------------------
                pickRandomChallenge() {
                    this.currentChallengeIndex = Math.floor(Math.random() * this.challenges.length);
                    this.resetLivenessState();
                },

                resetLivenessState() {
                    this.livenessVerified = false;
                    this.livenessProgress = 0;
                    this.blinkCount = 0;
                    this.lastBlinkDip = false;
                    this.smileHoldCount = 0;
                    this.headTurnPhase = 0;
                    this.baselineLum = null;
                    this.faceDetected = false;
                    this.livenessStatusHint = this.currentChallenge.hint;
                },

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

                        // Mulai Real-Time Liveness Detection Analyzer
                        this.startLivenessDetectionLoop();
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

                startLivenessDetectionLoop() {
                    if (this.livenessLoopId) clearInterval(this.livenessLoopId);

                    const canvas = document.getElementById('analysisCanvas');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d', { willReadFrequently: true });
                    const video = document.getElementById('cameraStream');

                    this.livenessLoopId = setInterval(() => {
                        if (!this.cameraActive || this.photoTaken || this.livenessVerified) return;
                        if (!video || video.readyState < 2) return;

                        // Draw downsampled frame to mini canvas (160x120)
                        ctx.drawImage(video, 0, 0, 160, 120);
                        const frame = ctx.getImageData(0, 0, 160, 120);
                        const data = frame.data;

                        // 1. Single Face & Center Check
                        // Ambil region tengah wajah (oval: x: 45..115, y: 25..95)
                        let centerLumSum = 0;
                        let centerVariance = 0;
                        let count = 0;
                        let skinPixelCount = 0;

                        for (let y = 25; y < 95; y += 2) {
                            for (let x = 45; x < 115; x += 2) {
                                const idx = (y * 160 + x) * 4;
                                const r = data[idx];
                                const g = data[idx + 1];
                                const b = data[idx + 2];
                                const lum = 0.299 * r + 0.587 * g + 0.114 * b;
                                centerLumSum += lum;
                                count++;

                                // Cek tone wajah/kulit dasar
                                if (r > 60 && g > 40 && b > 20 && r > b && (r - g) > 10) {
                                    skinPixelCount++;
                                }
                            }
                        }

                        const avgLum = centerLumSum / count;
                        const skinRatio = skinPixelCount / count;

                        // Wajah terdeteksi jika terdapat kontras & warna kulit alami di tengah frame
                        const hasFace = (avgLum > 30 && avgLum < 240 && skinRatio > 0.15);
                        this.faceDetected = hasFace;

                        if (!hasFace) {
                            this.livenessStatusHint = 'Posisikan wajah Anda tepat di dalam lingkaran';
                            return;
                        }

                        // Baseline luminance tracker
                        if (this.baselineLum === null) {
                            this.baselineLum = avgLum;
                        } else {
                            this.baselineLum = (this.baselineLum * 0.9) + (avgLum * 0.1);
                        }

                        // 2. Analisa Tantangan Keaktifan Spesifik
                        const challenge = this.currentChallenge.id;

                        if (challenge === 'blink') {
                            // Analisa area mata (y: 35..55, x: 50..110)
                            let eyeRegionLum = 0;
                            let eyeCount = 0;
                            for (let y = 35; y < 55; y += 2) {
                                for (let x = 50; x < 110; x += 2) {
                                    const idx = (y * 160 + x) * 4;
                                    eyeRegionLum += (0.299 * data[idx] + 0.587 * data[idx + 1] + 0.114 * data[idx + 2]);
                                    eyeCount++;
                                }
                            }
                            const currentEyeLum = eyeRegionLum / eyeCount;

                            // Saat berkedip, luminance/kontras kelopak mata turun dibanding baseline
                            const dipThreshold = this.baselineLum * 0.92;
                            if (currentEyeLum < dipThreshold) {
                                this.lastBlinkDip = true;
                            } else if (this.lastBlinkDip) {
                                // Kedipan selesai (recovery)
                                this.lastBlinkDip = false;
                                this.blinkCount++;
                                this.livenessProgress = Math.min(100, this.blinkCount * 50);
                                this.livenessStatusHint = this.blinkCount === 1 ? 'Bagus! Kedipkan 1 kali lagi...' : 'Kedipan terverifikasi!';

                                if (this.blinkCount >= 2) {
                                    this.onLivenessSuccess();
                                }
                            }
                        } else if (challenge === 'smile') {
                            // Analisa area senyum/mulut (y: 65..95, x: 55..105)
                            let mouthLum = 0;
                            let mouthEdgeEnergy = 0;
                            let mCount = 0;

                            for (let y = 65; y < 95; y += 2) {
                                for (let x = 55; x < 105; x += 2) {
                                    const idx = (y * 160 + x) * 4;
                                    const nextIdx = (y * 160 + (x + 2)) * 4;
                                    const lum = 0.299 * data[idx] + 0.587 * data[idx + 1] + 0.114 * data[idx + 2];
                                    const nextLum = 0.299 * data[nextIdx] + 0.587 * data[nextIdx + 1] + 0.114 * data[nextIdx + 2];
                                    mouthLum += lum;
                                    mouthEdgeEnergy += Math.abs(nextLum - lum);
                                    mCount++;
                                }
                            }

                            const avgMouthLum = mouthLum / mCount;
                            const avgEdge = mouthEdgeEnergy / mCount;

                            // Senyuman memicu pelebaran kontras tepi horizontal di area bibir
                            if (avgEdge > 12 || avgMouthLum > (this.baselineLum * 1.05)) {
                                this.smileHoldCount++;
                                this.livenessProgress = Math.min(100, this.smileHoldCount * 25);
                                this.livenessStatusHint = 'Pertahankan senyum Anda...';

                                if (this.smileHoldCount >= 4) {
                                    this.onLivenessSuccess();
                                }
                            } else if (this.smileHoldCount > 0) {
                                this.smileHoldCount = Math.max(0, this.smileHoldCount - 1);
                            }
                        } else if (challenge === 'head_turn') {
                            // Analisa perpindahan bobot center of mass horizontal
                            let leftEnergy = 0;
                            let rightEnergy = 0;

                            for (let y = 30; y < 90; y += 2) {
                                for (let x = 40; x < 80; x += 2) {
                                    const idx = (y * 160 + x) * 4;
                                    leftEnergy += (data[idx] + data[idx + 1] + data[idx + 2]);
                                }
                                for (let x = 80; x < 120; x += 2) {
                                    const idx = (y * 160 + x) * 4;
                                    rightEnergy += (data[idx] + data[idx + 1] + data[idx + 2]);
                                }
                            }

                            const balanceRatio = Math.abs(leftEnergy - rightEnergy) / (leftEnergy + rightEnergy + 1);

                            if (this.headTurnPhase === 0) {
                                if (balanceRatio > 0.12) {
                                    this.headTurnPhase = 1;
                                    this.livenessProgress = 60;
                                    this.livenessStatusHint = 'Bagus! Sekarang kembali menghadap tengah...';
                                }
                            } else if (this.headTurnPhase === 1) {
                                if (balanceRatio < 0.05) {
                                    this.headTurnPhase = 2;
                                    this.livenessProgress = 100;
                                    this.onLivenessSuccess();
                                }
                            }
                        }

                    }, 120);
                },

                onLivenessSuccess() {
                    this.livenessVerified = true;
                    this.livenessProgress = 100;
                    this.livenessStatusHint = '✅ Uji Keaktifan Berhasil! Wajah Hidup Terverifikasi.';
                    if (this.livenessLoopId) {
                        clearInterval(this.livenessLoopId);
                        this.livenessLoopId = null;
                    }

                    // Otomatis ambil snapshot setelah verifikasi berhasil (delay 400ms untuk ekspresi stabil)
                    setTimeout(() => {
                        if (!this.photoTaken && this.cameraActive) {
                            this.takeSnapshot();
                        }
                    }, 400);
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
                    this.pickRandomChallenge();
                    this.startLivenessDetectionLoop();
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
                // LEAFLET MAP VISUALIZATION
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
                // SUBMIT PRESENSI KE BACKEND DENGAN VALIDASI LENGKAP
                // -------------------------------------------------------------
                async submitAttendance(action) {
                    this.submitErrorMessage = '';

                    if (!this.gpsReady) {
                        this.submitErrorMessage = 'Menunggu data koordinat GPS. Pastikan GPS aktif.';
                        return;
                    }

                    if (this.gpsAccuracy <= 0 || this.isMock) {
                        this.submitErrorMessage = 'Presensi ditolak: Sinyal GPS tidak valid atau terdeteksi Mock/Fake GPS emulator.';
                        return;
                    }

                    if (this.gpsAccuracy > this.maxRadius) {
                        this.submitErrorMessage = `Akurasi sinyal GPS Anda terlalu lemah (${Math.round(this.gpsAccuracy)}m > batas toleransi 75m). Harap berada di area terbuka.`;
                        return;
                    }

                    if (!this.livenessVerified) {
                        this.submitErrorMessage = 'Uji keaktifan wajah (Liveness Detection) wajib diselesaikan.';
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
                                notes: this.notes,
                                accuracy: this.gpsAccuracy,
                                altitude: this.gpsAltitude,
                                speed: this.gpsSpeed,
                                is_mock: this.isMock,
                                liveness_verified: this.livenessVerified,
                                liveness_challenge: this.currentChallenge.id,
                                device_fingerprint: this.deviceFingerprint,
                                device_platform: this.devicePlatform,
                                token: this.attendanceToken
                            })
                        });

                        const result = await response.json();

                        if (!response.ok || !result.success) {
                            throw new Error(result.message || 'Gagal memproses presensi.');
                        }

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
