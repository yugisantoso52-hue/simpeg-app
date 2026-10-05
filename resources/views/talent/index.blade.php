<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                        Sistem Merit Terakreditasi
                    </span>
                    <span class="text-xs text-gray-500 font-mono">
                        UU No. 20/2023 &bull; PermenPAN-RB No. 3/2020 &bull; PermenPAN-RB No. 6/2022
                    </span>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>🎯</span> Matriks Manajemen Talenta ASN (9-Box Grid)
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Pemetaan Kuadran Kinerja (Sumbu X) vs Potensi (Sumbu Y) untuk Perencanaan Suksesi Jabatan & Mobilitas Talenta Fakultas Keperawatan UNRI
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(Auth::user()->canManageTalentManagement())
                    <form action="{{ route('manajemen-talenta.calculate') }}" method="POST" onsubmit="return confirm('Mulai sinkronisasi dan kalkulasi ulang seluruh talenta pegawai untuk tahun {{ $tahun }}?');">
                        @csrf
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                        @if($unitKerjaId)
                            <input type="hidden" name="unit_kerja_id" value="{{ $unitKerjaId }}">
                        @endif
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 shadow-sm transition">
                            <span>⚡</span> Hitung Ulang Skor
                        </button>
                    </form>
                    <a href="{{ route('manajemen-talenta.asesmen.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                        <span>📝</span> Data Asesmen BKN
                    </a>
                @endif
                <a href="{{ route('manajemen-talenta.rekap', ['tahun' => $tahun]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition">
                    <span>📋</span> Rekap Tabel Talenta
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm flex items-center gap-3">
                <span class="text-xl">ℹ️</span>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        {{-- Filter & Summary Cards --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <form method="GET" action="{{ route('manajemen-talenta.index') }}" class="flex flex-wrap items-end justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <label for="tahun" class="block text-xs font-bold text-gray-700 mb-1">Tahun Evaluasi</label>
                        <select name="tahun" id="tahun" onchange="this.form.submit()" class="text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-1.5 font-semibold">
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="unit_kerja_id" class="block text-xs font-bold text-gray-700 mb-1">Unit Kerja / Jurusan</label>
                        <select name="unit_kerja_id" id="unit_kerja_id" onchange="this.form.submit()" class="text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-1.5 min-w-[220px]">
                            <option value="">Semua Unit Kerja Fakultas</option>
                            @foreach($unitKerjas as $uk)
                                <option value="{{ $uk->id }}" {{ $unitKerjaId == $uk->id ? 'selected' : '' }}>{{ $uk->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Indikator Statistik --}}
                <div class="flex flex-wrap items-center gap-3">
                    <div class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-center">
                        <span class="block text-[10px] uppercase font-bold text-slate-500">Total ASN Dinilai</span>
                        <span class="text-lg font-black text-slate-800">{{ $distribution['total'] }}</span>
                    </div>
                    <div class="px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                        <span class="block text-[10px] uppercase font-bold text-emerald-600">Talent Pool Suksesi</span>
                        <span class="text-lg font-black text-emerald-700">{{ $distribution['eligible_suksesi'] }}</span>
                    </div>
                    <div class="px-4 py-2 bg-blue-50 border border-blue-200 rounded-xl text-center">
                        <span class="block text-[10px] uppercase font-bold text-blue-600">Pekerja Inti / Efektif</span>
                        @php
                            $coreCount = ($distribution['boxes'][5]['count'] ?? 0) + ($distribution['boxes'][4]['count'] ?? 0) + ($distribution['boxes'][7]['count'] ?? 0);
                        @endphp
                        <span class="text-lg font-black text-blue-700">{{ $coreCount }}</span>
                    </div>
                </div>
            </form>
        </div>

        {{-- MATRIKS 9-KOTAK INTERAKTIF (PERMENPAN-RB NO. 3 TAHUN 2020) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 overflow-hidden">
            
            {{-- Header Penjelasan Matriks & Panduan Sumbu --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-gray-200 gap-2 mb-6">
                <div>
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span>📊</span> Matriks Kuadran Pemetaan 9-Kotak
                    </h2>
                    <p class="text-xs text-gray-500">
                        Sumbu Y (Potensi: Kualifikasi, 20 JP Diklat, Rekam Jejak, Asesmen) &bull; Sumbu X (Kinerja: SKP 2 Tahun, Kehadiran, Logbook)
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5 text-emerald-700 font-semibold">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Kotak 9 & 8 (Siap Suksesi / Promosi)
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-blue-700 font-semibold">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span> Kotak 4, 5, 7 (Dipertahankan / Rotasi)
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-rose-700 font-semibold">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span> Kotak 1, 2, 3 (Pembinaan & Penataan)
                    </span>
                </div>
            </div>

            {{-- GRID LAYOUT 3x3 DENGAN SIDEBAR SUMBU Y --}}
            <div class="flex items-stretch gap-3">
                
                {{-- Side Indicator Sumbu Y (Potensi) --}}
                <div class="hidden md:flex flex-col items-center justify-between py-6 px-1.5 bg-slate-50 border border-slate-200 rounded-2xl select-none shrink-0 w-8">
                    <span class="text-[10px] font-black text-slate-700">▲</span>
                    <span class="text-[10px] font-extrabold text-slate-500 uppercase [writing-mode:vertical-lr] rotate-180 tracking-widest my-auto py-2">
                        SUMBU Y: POTENSI PEGAWAI
                    </span>
                    <span class="text-[9px] font-bold text-slate-400">0</span>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="space-y-4">

                    {{-- ========================================== --}}
                    {{-- BARIS 1: POTENSI TINGGI (Skor Y >= 90)     --}}
                    {{-- ========================================== --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2 text-xs font-extrabold text-slate-500 uppercase">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold">Potensi Tinggi (&ge; 90)</span>
                            <span class="text-[11px] text-gray-400 font-normal">Kualifikasi S3/Doktor, &ge;20 JP Diklat, Rekam Jejak Unggul, Asesmen Memenuhi Syarat</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                            {{-- KOTAK 3: Kinerja Rendah, Potensi Tinggi --}}
                            @php $b3 = $distribution['boxes'][3] ?? ['count' => 0, 'items' => collect()]; @endphp
                            <div class="rounded-xl border border-amber-300 bg-amber-50/50 p-4 hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-amber-200">
                                        <div>
                                            <span class="inline-block px-2 py-0.5 text-[10px] font-black rounded bg-amber-200 text-amber-900 uppercase">Kotak III</span>
                                            <h3 class="text-xs font-extrabold text-amber-950 mt-1">Kurang Selaras (Misaligned)</h3>
                                            <p class="text-[10px] text-amber-700 mt-0.5">Kinerja Rendah & Potensi Tinggi</p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-500 text-white shadow-2xs">{{ $b3['count'] }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2 max-h-48 overflow-y-auto pr-1">
                                        @forelse($b3['items']->take(5) as $m)
                                            <a href="{{ route('manajemen-talenta.show', $m->pegawai_id) }}" class="block p-2 rounded-lg bg-white border border-amber-200 hover:border-amber-400 transition text-xs group">
                                                <div class="font-bold text-gray-900 group-hover:text-amber-800 truncate">{{ $m->pegawai->nama_lengkap ?? $m->pegawai->nama }}</div>
                                                <div class="text-[10px] text-gray-500 truncate">{{ $m->pegawai->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="mt-1 flex items-center gap-2 text-[10px] font-mono text-gray-600">
                                                    <span>Kinerja: <b>{{ $m->sumbu_kinerja_nilai }}</b></span>
                                                    <span>Potensi: <b>{{ $m->sumbu_potensi_nilai }}</b></span>
                                                </div>
                                            </a>
                                        @empty
                                            <p class="text-[11px] text-amber-600 italic py-4 text-center">Tidak ada pegawai di kuadran ini</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="pt-3 mt-3 border-t border-amber-200 text-[10px] text-amber-800">
                                    <b>Tindak Lanjut:</b> Konseling, evaluasi <i>job mismatch</i>, rotasi penugasan.
                                </div>
                            </div>

                            {{-- KOTAK 6: Kinerja Sedang, Potensi Tinggi --}}
                            @php $b6 = $distribution['boxes'][6] ?? ['count' => 0, 'items' => collect()]; @endphp
                            <div class="rounded-xl border border-indigo-300 bg-indigo-50/50 p-4 hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-indigo-200">
                                        <div>
                                            <span class="inline-block px-2 py-0.5 text-[10px] font-black rounded bg-indigo-200 text-indigo-900 uppercase">Kotak VI</span>
                                            <h3 class="text-xs font-extrabold text-indigo-950 mt-1">Calon Bintang (Promising)</h3>
                                            <p class="text-[10px] text-indigo-700 mt-0.5">Kinerja Sedang & Potensi Tinggi</p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-indigo-600 text-white shadow-2xs">{{ $b6['count'] }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2 max-h-48 overflow-y-auto pr-1">
                                        @forelse($b6['items']->take(5) as $m)
                                            <a href="{{ route('manajemen-talenta.show', $m->pegawai_id) }}" class="block p-2 rounded-lg bg-white border border-indigo-200 hover:border-indigo-400 transition text-xs group">
                                                <div class="font-bold text-gray-900 group-hover:text-indigo-800 truncate">{{ $m->pegawai->nama_lengkap ?? $m->pegawai->nama }}</div>
                                                <div class="text-[10px] text-gray-500 truncate">{{ $m->pegawai->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="mt-1 flex items-center gap-2 text-[10px] font-mono text-gray-600">
                                                    <span>Kinerja: <b>{{ $m->sumbu_kinerja_nilai }}</b></span>
                                                    <span>Potensi: <b>{{ $m->sumbu_potensi_nilai }}</b></span>
                                                </div>
                                            </a>
                                        @empty
                                            <p class="text-[11px] text-indigo-600 italic py-4 text-center">Tidak ada pegawai di kuadran ini</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="pt-3 mt-3 border-t border-indigo-200 text-[10px] text-indigo-800">
                                    <b>Tindak Lanjut:</b> Bimbingan <i>coaching & mentoring</i> intensif pimpinan.
                                </div>
                            </div>

                            {{-- KOTAK 9: Kinerja Tinggi, Potensi Tinggi (TOP STAR / SUKSESI) --}}
                            @php $b9 = $distribution['boxes'][9] ?? ['count' => 0, 'items' => collect()]; @endphp
                            <div class="rounded-xl border-2 border-emerald-500 bg-emerald-50 p-4 shadow-sm hover:shadow-lg transition flex flex-col justify-between ring-2 ring-emerald-400/30">
                                <div>
                                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-emerald-200">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-2 py-0.5 text-[10px] font-black rounded bg-emerald-600 text-white uppercase">Kotak IX</span>
                                                <span class="text-xs">⭐</span>
                                            </div>
                                            <h3 class="text-xs font-black text-emerald-950 mt-1">Talenta Unggul (Star)</h3>
                                            <p class="text-[10px] text-emerald-700 font-semibold mt-0.5">Kinerja Tinggi & Potensi Tinggi</p>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-600 text-white shadow-sm">{{ $b9['count'] }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2 max-h-48 overflow-y-auto pr-1">
                                        @forelse($b9['items']->take(5) as $m)
                                            <a href="{{ route('manajemen-talenta.show', $m->pegawai_id) }}" class="block p-2 rounded-lg bg-white border border-emerald-300 hover:border-emerald-500 transition text-xs group shadow-2xs">
                                                <div class="font-black text-emerald-950 group-hover:text-emerald-700 truncate flex items-center justify-between">
                                                    <span>{{ $m->pegawai->nama_lengkap ?? $m->pegawai->nama }}</span>
                                                    <span class="text-[9px] px-1 py-0.2 rounded bg-emerald-100 text-emerald-800 font-bold">Siap Promosi</span>
                                                </div>
                                                <div class="text-[10px] text-gray-500 truncate">{{ $m->pegawai->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="mt-1 flex items-center gap-2 text-[10px] font-mono text-emerald-800">
                                                    <span>Kinerja: <b>{{ $m->sumbu_kinerja_nilai }}</b></span>
                                                    <span>Potensi: <b>{{ $m->sumbu_potensi_nilai }}</b></span>
                                                </div>
                                            </a>
                                        @empty
                                            <p class="text-[11px] text-emerald-700 italic py-4 text-center">Belum ada pegawai memenuhi syarat Kuadran 9</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="pt-3 mt-3 border-t border-emerald-200 text-[10px] text-emerald-900 font-semibold">
                                    <b>Tindak Lanjut:</b> Talent Pool Prioritas 1, Promosi Jabatan Target / JPT, Retensi.
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- BARIS 2: POTENSI SEDANG (Skor Y 75 - 89.99) --}}
                    {{-- ========================================== --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2 text-xs font-extrabold text-slate-500 uppercase">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold">Potensi Sedang (75 - 89.99)</span>
                            <span class="text-[11px] text-gray-400 font-normal">Kualifikasi S2/Magister, 10-19 JP Diklat, Pengalaman Memadai</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                            {{-- KOTAK 2: Kinerja Rendah, Potensi Sedang --}}
                            @php $b2 = $distribution['boxes'][2] ?? ['count' => 0, 'items' => collect()]; @endphp
                            <div class="rounded-xl border border-orange-300 bg-orange-50/50 p-4 hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-orange-200">
                                        <div>
                                            <span class="inline-block px-2 py-0.5 text-[10px] font-black rounded bg-orange-200 text-orange-900 uppercase">Kotak II</span>
                                            <h3 class="text-xs font-extrabold text-orange-950 mt-1">Kurang Efektif</h3>
                                            <p class="text-[10px] text-orange-700 mt-0.5">Kinerja Rendah & Potensi Sedang</p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-orange-500 text-white shadow-2xs">{{ $b2['count'] }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2 max-h-48 overflow-y-auto pr-1">
                                        @forelse($b2['items']->take(5) as $m)
                                            <a href="{{ route('manajemen-talenta.show', $m->pegawai_id) }}" class="block p-2 rounded-lg bg-white border border-orange-200 hover:border-orange-400 transition text-xs group">
                                                <div class="font-bold text-gray-900 group-hover:text-orange-800 truncate">{{ $m->pegawai->nama_lengkap ?? $m->pegawai->nama }}</div>
                                                <div class="text-[10px] text-gray-500 truncate">{{ $m->pegawai->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="mt-1 flex items-center gap-2 text-[10px] font-mono text-gray-600">
                                                    <span>Kinerja: <b>{{ $m->sumbu_kinerja_nilai }}</b></span>
                                                    <span>Potensi: <b>{{ $m->sumbu_potensi_nilai }}</b></span>
                                                </div>
                                            </a>
                                        @empty
                                            <p class="text-[11px] text-orange-600 italic py-4 text-center">Tidak ada pegawai di kuadran ini</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="pt-3 mt-3 border-t border-orange-200 text-[10px] text-orange-800">
                                    <b>Tindak Lanjut:</b> Peringatan kinerja, pembinaan supervisi ketat atasan.
                                </div>
                            </div>

                            {{-- KOTAK 5: Kinerja Sedang, Potensi Sedang (CORE EMPLOYEE) --}}
                            @php $b5 = $distribution['boxes'][5] ?? ['count' => 0, 'items' => collect()]; @endphp
                            <div class="rounded-xl border border-blue-300 bg-blue-50/50 p-4 hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-blue-200">
                                        <div>
                                            <span class="inline-block px-2 py-0.5 text-[10px] font-black rounded bg-blue-200 text-blue-900 uppercase">Kotak V</span>
                                            <h3 class="text-xs font-extrabold text-blue-950 mt-1">Pekerja Inti (Core Employee)</h3>
                                            <p class="text-[10px] text-blue-700 mt-0.5">Kinerja Sedang & Potensi Sedang</p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-blue-600 text-white shadow-2xs">{{ $b5['count'] }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2 max-h-48 overflow-y-auto pr-1">
                                        @forelse($b5['items']->take(5) as $m)
                                            <a href="{{ route('manajemen-talenta.show', $m->pegawai_id) }}" class="block p-2 rounded-lg bg-white border border-blue-200 hover:border-blue-400 transition text-xs group">
                                                <div class="font-bold text-gray-900 group-hover:text-blue-800 truncate">{{ $m->pegawai->nama_lengkap ?? $m->pegawai->nama }}</div>
                                                <div class="text-[10px] text-gray-500 truncate">{{ $m->pegawai->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="mt-1 flex items-center gap-2 text-[10px] font-mono text-gray-600">
                                                    <span>Kinerja: <b>{{ $m->sumbu_kinerja_nilai }}</b></span>
                                                    <span>Potensi: <b>{{ $m->sumbu_potensi_nilai }}</b></span>
                                                </div>
                                            </a>
                                        @empty
                                            <p class="text-[11px] text-blue-600 italic py-4 text-center">Tidak ada pegawai di kuadran ini</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="pt-3 mt-3 border-t border-blue-200 text-[10px] text-blue-800">
                                    <b>Tindak Lanjut:</b> Rotasi tugas setara berkala, pemenuhan minimal 20 JP diklat.
                                </div>
                            </div>

                            {{-- KOTAK 8: Kinerja Tinggi, Potensi Sedang (SUKSESI CADANGAN) --}}
                            @php $b8 = $distribution['boxes'][8] ?? ['count' => 0, 'items' => collect()]; @endphp
                            <div class="rounded-xl border border-teal-400 bg-teal-50/60 p-4 hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-teal-200">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-2 py-0.5 text-[10px] font-black rounded bg-teal-600 text-white uppercase">Kotak VIII</span>
                                                <span class="text-xs">✨</span>
                                            </div>
                                            <h3 class="text-xs font-extrabold text-teal-950 mt-1">Kinerja Di Atas Ekspektasi</h3>
                                            <p class="text-[10px] text-teal-700 mt-0.5">Kinerja Tinggi & Potensi Sedang</p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-teal-600 text-white shadow-2xs">{{ $b8['count'] }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2 max-h-48 overflow-y-auto pr-1">
                                        @forelse($b8['items']->take(5) as $m)
                                            <a href="{{ route('manajemen-talenta.show', $m->pegawai_id) }}" class="block p-2 rounded-lg bg-white border border-teal-200 hover:border-teal-400 transition text-xs group">
                                                <div class="font-bold text-gray-900 group-hover:text-teal-800 truncate">{{ $m->pegawai->nama_lengkap ?? $m->pegawai->nama }}</div>
                                                <div class="text-[10px] text-gray-500 truncate">{{ $m->pegawai->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="mt-1 flex items-center gap-2 text-[10px] font-mono text-teal-800">
                                                    <span>Kinerja: <b>{{ $m->sumbu_kinerja_nilai }}</b></span>
                                                    <span>Potensi: <b>{{ $m->sumbu_potensi_nilai }}</b></span>
                                                </div>
                                            </a>
                                        @empty
                                            <p class="text-[11px] text-teal-600 italic py-4 text-center">Tidak ada pegawai di kuadran ini</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="pt-3 mt-3 border-t border-teal-200 text-[10px] text-teal-900 font-semibold">
                                    <b>Tindak Lanjut:</b> Talent Pool Suksesi Cadangan, dorong diklat ke Kotak IX.
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- BARIS 3: POTENSI RENDAH (Skor Y < 75)      --}}
                    {{-- ========================================== --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2 text-xs font-extrabold text-slate-500 uppercase">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold">Potensi Rendah (&lt; 75)</span>
                            <span class="text-[11px] text-gray-400 font-normal">Kualifikasi D3/S1 Awal, Minim Jam Diklat, Belum Asesmen Kompetensi</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                            {{-- KOTAK 1: Kinerja Rendah, Potensi Rendah --}}
                            @php $b1 = $distribution['boxes'][1] ?? ['count' => 0, 'items' => collect()]; @endphp
                            <div class="rounded-xl border border-rose-300 bg-rose-50/50 p-4 hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-rose-200">
                                        <div>
                                            <span class="inline-block px-2 py-0.5 text-[10px] font-black rounded bg-rose-200 text-rose-900 uppercase">Kotak I</span>
                                            <h3 class="text-xs font-extrabold text-rose-950 mt-1">Perlu Pembinaan Khusus</h3>
                                            <p class="text-[10px] text-rose-700 mt-0.5">Kinerja Rendah & Potensi Rendah</p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-rose-600 text-white shadow-2xs">{{ $b1['count'] }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2 max-h-48 overflow-y-auto pr-1">
                                        @forelse($b1['items']->take(5) as $m)
                                            <a href="{{ route('manajemen-talenta.show', $m->pegawai_id) }}" class="block p-2 rounded-lg bg-white border border-rose-200 hover:border-rose-400 transition text-xs group">
                                                <div class="font-bold text-gray-900 group-hover:text-rose-800 truncate">{{ $m->pegawai->nama_lengkap ?? $m->pegawai->nama }}</div>
                                                <div class="text-[10px] text-gray-500 truncate">{{ $m->pegawai->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="mt-1 flex items-center gap-2 text-[10px] font-mono text-gray-600">
                                                    <span>Kinerja: <b>{{ $m->sumbu_kinerja_nilai }}</b></span>
                                                    <span>Potensi: <b>{{ $m->sumbu_potensi_nilai }}</b></span>
                                                </div>
                                            </a>
                                        @empty
                                            <p class="text-[11px] text-rose-600 italic py-4 text-center">Tidak ada pegawai di kuadran ini</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="pt-3 mt-3 border-t border-rose-200 text-[10px] text-rose-800">
                                    <b>Tindak Lanjut:</b> Konseling disiplin, evaluasi jabatan, mekanisme PP 94/2021.
                                </div>
                            </div>

                            {{-- KOTAK 4: Kinerja Sedang, Potensi Rendah --}}
                            @php $b4 = $distribution['boxes'][4] ?? ['count' => 0, 'items' => collect()]; @endphp
                            <div class="rounded-xl border border-amber-300 bg-amber-50/50 p-4 hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-amber-200">
                                        <div>
                                            <span class="inline-block px-2 py-0.5 text-[10px] font-black rounded bg-amber-200 text-amber-900 uppercase">Kotak IV</span>
                                            <h3 class="text-xs font-extrabold text-amber-950 mt-1">Pekerja Efektif</h3>
                                            <p class="text-[10px] text-amber-700 mt-0.5">Kinerja Sedang & Potensi Rendah</p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-500 text-white shadow-2xs">{{ $b4['count'] }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2 max-h-48 overflow-y-auto pr-1">
                                        @forelse($b4['items']->take(5) as $m)
                                            <a href="{{ route('manajemen-talenta.show', $m->pegawai_id) }}" class="block p-2 rounded-lg bg-white border border-amber-200 hover:border-amber-400 transition text-xs group">
                                                <div class="font-bold text-gray-900 group-hover:text-amber-800 truncate">{{ $m->pegawai->nama_lengkap ?? $m->pegawai->nama }}</div>
                                                <div class="text-[10px] text-gray-500 truncate">{{ $m->pegawai->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="mt-1 flex items-center gap-2 text-[10px] font-mono text-gray-600">
                                                    <span>Kinerja: <b>{{ $m->sumbu_kinerja_nilai }}</b></span>
                                                    <span>Potensi: <b>{{ $m->sumbu_potensi_nilai }}</b></span>
                                                </div>
                                            </a>
                                        @empty
                                            <p class="text-[11px] text-amber-600 italic py-4 text-center">Tidak ada pegawai di kuadran ini</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="pt-3 mt-3 border-t border-amber-200 text-[10px] text-amber-800">
                                    <b>Tindak Lanjut:</b> Dipertahankan, pelatihan penyegaran tugas teknis ABK.
                                </div>
                            </div>

                            {{-- KOTAK 7: Kinerja Tinggi, Potensi Rendah --}}
                            @php $b7 = $distribution['boxes'][7] ?? ['count' => 0, 'items' => collect()]; @endphp
                            <div class="rounded-xl border border-cyan-300 bg-cyan-50/50 p-4 hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-cyan-200">
                                        <div>
                                            <span class="inline-block px-2 py-0.5 text-[10px] font-black rounded bg-cyan-200 text-cyan-900 uppercase">Kotak VII</span>
                                            <h3 class="text-xs font-extrabold text-cyan-950 mt-1">Spesialis (Solid Performer)</h3>
                                            <p class="text-[10px] text-cyan-700 mt-0.5">Kinerja Tinggi & Potensi Rendah</p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-cyan-600 text-white shadow-2xs">{{ $b7['count'] }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2 max-h-48 overflow-y-auto pr-1">
                                        @forelse($b7['items']->take(5) as $m)
                                            <a href="{{ route('manajemen-talenta.show', $m->pegawai_id) }}" class="block p-2 rounded-lg bg-white border border-cyan-200 hover:border-cyan-400 transition text-xs group">
                                                <div class="font-bold text-gray-900 group-hover:text-cyan-800 truncate">{{ $m->pegawai->nama_lengkap ?? $m->pegawai->nama }}</div>
                                                <div class="text-[10px] text-gray-500 truncate">{{ $m->pegawai->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="mt-1 flex items-center gap-2 text-[10px] font-mono text-cyan-800">
                                                    <span>Kinerja: <b>{{ $m->sumbu_kinerja_nilai }}</b></span>
                                                    <span>Potensi: <b>{{ $m->sumbu_potensi_nilai }}</b></span>
                                                </div>
                                            </a>
                                        @empty
                                            <p class="text-[11px] text-cyan-600 italic py-4 text-center">Tidak ada pegawai di kuadran ini</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="pt-3 mt-3 border-t border-cyan-200 text-[10px] text-cyan-800">
                                    <b>Tindak Lanjut:</b> Dipertahankan pada posisinya saat ini, apresiasi kinerja.
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                {{-- Label Sumbu X (Horizontal - Kinerja) --}}
                <div class="mt-6 pt-4 border-t border-slate-200 grid grid-cols-3 text-center text-xs font-extrabold text-slate-500 uppercase">
                    <div>
                        <span class="block text-slate-800">Kinerja Rendah (&lt; 75)</span>
                        <span class="text-[10px] text-slate-400 font-normal">Di Bawah Ekspektasi / Butuh Perbaikan</span>
                    </div>
                    <div>
                        <span class="block text-slate-800">Kinerja Sedang (75 - 89.99)</span>
                        <span class="text-[10px] text-slate-400 font-normal">Sesuai Ekspektasi / Predikat Baik</span>
                    </div>
                    <div>
                        <span class="block text-emerald-800 font-black">Kinerja Tinggi (&ge; 90)</span>
                        <span class="text-[10px] text-emerald-600 font-normal">Di Atas Ekspektasi / Sangat Baik</span>
                    </div>
                </div>
                <div class="text-center text-xs font-black tracking-widest text-slate-400 uppercase mt-2 select-none">
                    SUMBU X: KINERJA PEGAWAI (SKP, LOGBOOK & PRESENSI)
                </div>

                </div>
            </div>

        </div>

    </div>
</x-app-layout>
