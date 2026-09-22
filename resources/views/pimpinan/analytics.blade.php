<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>📊</span> {{ __('Executive Analytics Dashboard (Pimpinan & Dekanat)') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Visualisasi strategis beban kerja, kedisiplinan presensi, radar pensiun, dan profil SDM Fakultas Keperawatan UNRI
                </p>
            </div>

            <!-- Filter Bulan & Tahun + Tombol Cetak -->
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('pimpinan.analytics') }}" class="flex items-center gap-2">
                    <select name="month" class="text-xs font-semibold rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 py-1.5 bg-white shadow-2xs">
                        @foreach($namaBulan as $mNum => $mName)
                            <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                        @endforeach
                    </select>

                    <select name="year" class="text-xs font-semibold rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 py-1.5 bg-white shadow-2xs">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>

                    <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition shadow-2xs">
                        Terapkan
                    </button>
                </form>

                <a href="{{ route('pimpinan.analytics.pdf', ['month' => $month, 'year' => $year]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-2xs">
                    <span>📄</span> Cetak PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 1. KARTU KPI EKSEKUTIF UTAMA (INTERAKTIF & DAPAT DIKLIK) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total SDM Aktif (Klik untuk menuju Master Pegawai) -->
                <a href="{{ route('pegawai.index') }}" class="group bg-white rounded-2xl border border-slate-200 hover:border-blue-500 p-5 shadow-2xs hover:shadow-md transition-all flex items-center justify-between block cursor-pointer" title="Klik untuk membuka Master Seluruh Pegawai">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-blue-600 transition">Total SDM Aktif</span>
                        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['total_aktif'] }} <span class="text-xs font-normal text-slate-500">Orang</span></h3>
                        <p class="text-[11px] text-blue-600 font-semibold mt-1">
                            👨‍🏫 {{ $kpis['total_dosen'] }} Dosen • 🧑‍💼 {{ $kpis['total_tendik'] }} Tendik
                            @if(($kpis['total_phl'] ?? 0) > 0)
                                • 👷 {{ $kpis['total_phl'] }} PHL
                            @endif
                        </p>
                        <span class="text-[10px] text-blue-500 font-semibold group-hover:underline flex items-center gap-1 mt-1.5">
                            Buka Data Pegawai →
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center text-2xl transition-colors">
                        👥
                    </div>
                </a>

                <!-- Capaian Logbook Bulanan (Klik untuk menuju Verifikasi Logbook) -->
                <a href="{{ route('admin.logbook.index') }}" class="group bg-white rounded-2xl border border-slate-200 hover:border-emerald-500 p-5 shadow-2xs hover:shadow-md transition-all flex items-center justify-between block cursor-pointer" title="Klik untuk membuka Halaman Verifikasi Logbook">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-emerald-600 transition">Verifikasi Logbook</span>
                        <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $kpis['logbook_rate'] }}%</h3>
                        <p class="text-[11px] text-slate-500 mt-1">
                            ✓ {{ $kpis['logbook_approved'] }} Selesai • ⏳ {{ $kpis['logbook_pending'] }} Menunggu
                        </p>
                        <span class="text-[10px] text-emerald-600 font-semibold group-hover:underline flex items-center gap-1 mt-1.5">
                            Buka Verifikasi Logbook →
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center text-2xl transition-colors">
                        📝
                    </div>
                </a>

                <!-- Kepatuhan Presensi (Klik untuk menuju Rekap Presensi) -->
                <a href="{{ route('admin.presensi.index') }}" class="group bg-white rounded-2xl border border-slate-200 hover:border-indigo-500 p-5 shadow-2xs hover:shadow-md transition-all flex items-center justify-between block cursor-pointer" title="Klik untuk membuka Rekapitulasi Presensi">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-indigo-600 transition">Kepatuhan Presensi</span>
                        <h3 class="text-2xl font-black text-indigo-600 mt-1">{{ $kpis['presensi_rate'] }}%</h3>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Tepat: {{ $kpis['presensi_tepat'] }} • Telat: {{ $kpis['presensi_telat'] }}
                        </p>
                        <span class="text-[10px] text-indigo-600 font-semibold group-hover:underline flex items-center gap-1 mt-1.5">
                            Buka Rekap Presensi →
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center text-2xl transition-colors">
                        📍
                    </div>
                </a>

                <!-- Radar Pensiun 5 Tahun (Klik untuk scroll ke Tabel Radar Pensiun) -->
                <a href="#tabel-radar-pensiun" class="group bg-white rounded-2xl border border-slate-200 hover:border-amber-500 p-5 shadow-2xs hover:shadow-md transition-all flex items-center justify-between block cursor-pointer" title="Klik untuk melihat Daftar Nama Pegawai Mendekati Pensiun">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-amber-600 transition">Radar BUP Pensiun</span>
                        <h3 class="text-2xl font-black text-amber-600 mt-1">{{ $kpis['pensiun_5_tahun'] }} <span class="text-xs font-normal text-slate-500">Pegawai</span></h3>
                        <p class="text-[11px] text-amber-700 font-semibold mt-1">
                            Mendekati BUP (Rentang 1-5 Thn)
                        </p>
                        <span class="text-[10px] text-amber-700 font-semibold group-hover:underline flex items-center gap-1 mt-1.5">
                            Lihat Daftar Pegawai ↓
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 group-hover:bg-amber-500 group-hover:text-white flex items-center justify-center text-2xl transition-colors">
                        ⏳
                    </div>
                </a>
            </div>

            <!-- 2. GRID GRAFIK UTAMA: BEBAN KERJA LOGBOOK & TREN PRESENSI -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Grafik 1: Beban Kerja Jam Efektif Logbook per Unit -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-1.5">
                                    <span>⏱️</span> Beban Kerja Jam Logbook per Unit Kerja
                                </h3>
                                <p class="text-[11px] text-slate-500">Total akumulasi jam kerja efektif bulan {{ $namaBulan[$month] }} {{ $year }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="relative h-64">
                        <canvas id="chartWorkload"></canvas>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-3 pt-2 border-t border-slate-100 leading-tight">
                        💡 <strong>Struktur Unit:</strong> Unit <em>Fakultas Keperawatan</em> menaungi seluruh Dosen (Tenaga Pendidik) & Pimpinan Fakultas, sedangkan unit lainnya merupakan Subbagian Tata Usaha (Tenaga Kependidikan).
                    </p>
                </div>

                <!-- Grafik 2: Tren Presensi Harian Fakultas -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-1.5">
                                <span>📈</span> Tren Presensi Harian (Senin - Jumat)
                            </h3>
                            <p class="text-[11px] text-slate-500">Perbandingan kehadiran tepat waktu vs terlambat</p>
                        </div>
                    </div>
                    <div class="relative h-64">
                        <canvas id="chartAttendance"></canvas>
                    </div>
                </div>
            </div>

            <!-- 3. GRID PROFIL SDM & RADAR PENSIUN -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Grafik 3: Jabatan Fungsional Dosen -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
                    <h3 class="font-bold text-slate-800 text-sm mb-1 flex items-center gap-1.5">
                        <span>🎓</span> Jabatan Akademik Dosen
                    </h3>
                    <p class="text-[11px] text-slate-500 mb-3">Distribusi Guru Besar s/d Asisten Ahli</p>
                    <div class="relative h-56 flex items-center justify-center">
                        <canvas id="chartJafung"></canvas>
                    </div>
                </div>

                <!-- Grafik 4: Kualifikasi Pendidikan Terakhir -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
                    <h3 class="font-bold text-slate-800 text-sm mb-1 flex items-center gap-1.5">
                        <span>📚</span> Kualifikasi Pendidikan Dosen
                    </h3>
                    <p class="text-[11px] text-slate-500 mb-3">Proporsi jenjang S3 (Doktor) vs S2 (Magister)</p>
                    <div class="relative h-56 flex items-center justify-center">
                        <canvas id="chartPendidikan"></canvas>
                    </div>
                </div>

                <!-- Grafik 5 / Ringkasan: Radar Proyeksi Pensiun -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm mb-1 flex items-center gap-1.5">
                            <span>⏳</span> Proyeksi Masa Pensiun ASN
                        </h3>
                        <p class="text-[11px] text-slate-500 mb-4">Estimasi kebutuhan formasi pengganti 1-5 tahun</p>
                        
                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-rose-50 border border-rose-200">
                                <span class="font-semibold text-rose-800">Tahun Ini ({{ date('Y') }})</span>
                                <strong class="font-bold text-rose-900 font-mono text-sm">{{ $retirementRadar['summary']['tahun_ini'] }} Pegawai</strong>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50 border border-amber-200">
                                <span class="font-semibold text-amber-800">1 Tahun ke Depan ({{ date('Y') + 1 }})</span>
                                <strong class="font-bold text-amber-900 font-mono text-sm">{{ $retirementRadar['summary']['tahun_1'] }} Pegawai</strong>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-blue-50 border border-blue-200">
                                <span class="font-semibold text-blue-800">2 Tahun ke Depan ({{ date('Y') + 2 }})</span>
                                <strong class="font-bold text-blue-900 font-mono text-sm">{{ $retirementRadar['summary']['tahun_2'] }} Pegawai</strong>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="font-semibold text-slate-700">3 - 5 Tahun ke Depan</span>
                                <strong class="font-bold text-slate-800 font-mono text-sm">{{ $retirementRadar['summary']['tahun_3_sd_5'] }} Pegawai</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Total Akumulasi 5 Tahun:</span>
                        <strong class="text-slate-800 font-bold font-mono">{{ $retirementRadar['summary']['total_5_tahun'] }} ASN</strong>
                    </div>
                </div>
            </div>

            <!-- 4. TABEL DETAIL RADAR PENSIUN (ACCORDION DETAIL) -->
            @if($retirementRadar['details']['tahun_ini']->isNotEmpty() || $retirementRadar['details']['tahun_1']->isNotEmpty())
            <div id="tabel-radar-pensiun" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs overflow-hidden scroll-mt-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm flex items-center gap-1.5">
                            <span>📋</span> Daftar Pegawai Mendekati Batas Usia Pensiun (Tahun Ini & Tahun Depan)
                        </h3>
                        <p class="text-[11px] text-slate-500">Prioritas penyusunan SK Pensiun & usulan formasi pengganti</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-2.5">Nama & NIP</th>
                                <th class="px-4 py-2.5">Unit Kerja</th>
                                <th class="px-4 py-2.5">Jabatan</th>
                                <th class="px-4 py-2.5 text-center">BUP (Thn)</th>
                                <th class="px-4 py-2.5 text-center">TMT Pensiun</th>
                                <th class="px-4 py-2.5 text-center">Sisa Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($retirementRadar['details']['tahun_ini']->merge($retirementRadar['details']['tahun_1']) as $p)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-3">
                                        <strong class="text-slate-800 font-semibold block">{{ $p->nama }}</strong>
                                        <span class="text-[10px] text-slate-400 font-mono">NIP. {{ $p->nip }}</span>
                                    </td>
                                    <td class="px-4 py-3">{{ $p->unit }}</td>
                                    <td class="px-4 py-3">{{ $p->jabatan }}</td>
                                    <td class="px-4 py-3 text-center font-mono font-bold">{{ $p->bup }}</td>
                                    <td class="px-4 py-3 text-center font-mono text-slate-700 font-semibold">{{ $p->tgl_pensiun }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->sisa_bulan <= 6 ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                            {{ $p->sisa_bulan }} Bulan Lagi
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Palette Warna Modern
            const colors = {
                primary: '#1d4ed8',
                primaryLight: '#3b82f6',
                emerald: '#10b981',
                amber: '#f59e0b',
                rose: '#ef4444',
                indigo: '#6366f1',
                slate: '#64748b',
                cyan: '#06b6d4',
            };

            // 1. Chart Beban Kerja Logbook per Unit Kerja
            const workloadData = @json($logbookWorkload);
            const ctxWorkload = document.getElementById('chartWorkload');
            if (ctxWorkload && workloadData.labels.length > 0) {
                new Chart(ctxWorkload, {
                    type: 'bar',
                    data: {
                        labels: workloadData.labels,
                        datasets: [
                            {
                                label: 'Total Jam Kerja Efektif',
                                data: workloadData.hours,
                                backgroundColor: 'rgba(59, 130, 246, 0.85)',
                                borderColor: '#2563eb',
                                borderWidth: 1,
                                borderRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + ' Jam Efektif';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Total Jam', font: { size: 10 } }
                            },
                            x: {
                                ticks: { maxRotation: 25, minRotation: 0, font: { size: 10 } }
                            }
                        }
                    }
                });
            }

            // 2. Chart Tren Presensi Harian Fakultas
            const attData = @json($attendanceTrends);
            const ctxAtt = document.getElementById('chartAttendance');
            if (ctxAtt && attData.labels.length > 0) {
                new Chart(ctxAtt, {
                    type: 'line',
                    data: {
                        labels: attData.labels,
                        datasets: [
                            {
                                label: 'Tepat Waktu',
                                data: attData.present,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.3,
                                fill: true,
                            },
                            {
                                label: 'Terlambat',
                                data: attData.late,
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                tension: 0.3,
                                fill: true,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, font: { size: 10 } } }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                            x: { ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }

            // 3. Chart Jabatan Fungsional Dosen
            const staffComp = @json($staffComposition);
            const ctxJafung = document.getElementById('chartJafung');
            if (ctxJafung) {
                new Chart(ctxJafung, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(staffComp.jafung),
                        datasets: [{
                            data: Object.values(staffComp.jafung),
                            backgroundColor: ['#8b5cf6', '#3b82f6', '#06b6d4', '#10b981', '#94a3b8'],
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } }
                        }
                    }
                });
            }

            // 4. Chart Kualifikasi Pendidikan Dosen
            const ctxPendidikan = document.getElementById('chartPendidikan');
            if (ctxPendidikan) {
                new Chart(ctxPendidikan, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(staffComp.pendidikan),
                        datasets: [{
                            data: Object.values(staffComp.pendidikan),
                            backgroundColor: ['#1d4ed8', '#10b981', '#f59e0b', '#cbd5e1'],
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
