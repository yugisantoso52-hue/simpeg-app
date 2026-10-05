<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                        Pola Karier &amp; Sistem Merit
                    </span>
                    <span class="text-xs text-gray-500 font-mono">UU No. 20/2023 &bull; PermenPAN-RB No. 3/2020 Bab V</span>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>👑</span> Rencana Suksesi Jabatan ASN (Succession Planning)
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Penetapan dan Pemetaan Calon Pengganti Jabatan Struktural &amp; Fungsional Strategis Berbasis Match Index Standar Anjab
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('manajemen-talenta.index', ['tahun' => $tahun]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                    <span>📊</span> Matriks 9-Kotak
                </a>
                <a href="{{ route('anjab.peta-jabatan') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition">
                    <span>🏛️</span> Peta Jabatan Digital
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

        {{-- Filter & Statistik --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <form method="GET" action="{{ route('manajemen-talenta.suksesi.index') }}" class="flex flex-wrap items-end justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <label for="tahun" class="block text-xs font-bold text-gray-700 mb-1">Tahun Rencana Suksesi</label>
                        <select name="tahun" id="tahun" onchange="this.form.submit()" class="text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-1.5 font-semibold">
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="unit_kerja_id" class="block text-xs font-bold text-gray-700 mb-1">Unit Kerja / Bagian</label>
                        <select name="unit_kerja_id" id="unit_kerja_id" onchange="this.form.submit()" class="text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-1.5 min-w-[220px]">
                            <option value="">Semua Unit Kerja</option>
                            @foreach($unitKerjas as $uk)
                                <option value="{{ $uk->id }}" {{ $unitKerjaId == $uk->id ? 'selected' : '' }}>{{ $uk->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-center">
                        <span class="block text-[10px] uppercase font-bold text-slate-500">Jabatan Sasaran</span>
                        <span class="text-lg font-black text-slate-800">{{ $jabatans->count() }}</span>
                    </div>
                    <div class="px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-xl text-center">
                        <span class="block text-[10px] uppercase font-bold text-indigo-600">Total Nominasi Suksesi</span>
                        <span class="text-lg font-black text-indigo-700">{{ $totalNominasi }}</span>
                    </div>
                    <div class="px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                        <span class="block text-[10px] uppercase font-bold text-emerald-600">Siap Sekarang (Ready Now)</span>
                        <span class="text-lg font-black text-emerald-700">{{ $readyNowCount }}</span>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabel Jabatan Target & Pipeline Suksesor --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Pipeline Rencana Suksesi Jabatan Instansi</h3>
                    <p class="text-[11px] text-gray-500">Pilih jabatan untuk melakukan analisis kesesuaian (Job Matching) dan menetapkan suksesor</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-200 text-slate-700 font-extrabold uppercase text-[11px]">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Jabatan Target</th>
                            <th class="py-3 px-4">Unit Kerja</th>
                            <th class="py-3 px-4 text-center">Kelas</th>
                            <th class="py-3 px-4">Pejabat Saat Ini (Inkumben)</th>
                            <th class="py-3 px-4">Kandidat Suksesor Dinominasikan</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($jabatans as $index => $jab)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-center font-mono text-gray-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-900 text-sm">
                                        {{ $jab->nama_jabatan }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-mono">
                                        Kode: {{ $jab->kode_jabatan ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-gray-700 font-medium">
                                        {{ $jab->unitKerja->nama_unit ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full font-mono font-bold bg-slate-100 text-slate-800 text-[11px]">
                                        {{ $jab->kelas_jabatan ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($jab->pegawai->count() > 0)
                                        @foreach($jab->pegawai as $p)
                                            <div class="font-semibold text-gray-900">{{ $p->nama_lengkap ?? $p->nama }}</div>
                                            <div class="text-[10px] text-gray-500 font-mono">NIP. {{ $p->nip }}</div>
                                        @endforeach
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                            Lowong (Kosong)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($jab->successionPlans->count() > 0)
                                        <div class="space-y-1.5">
                                            @foreach($jab->successionPlans as $plan)
                                                <div class="flex items-center justify-between gap-2 p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-[11px]">
                                                    <div class="truncate">
                                                        <span class="font-bold text-slate-900">#{{ $plan->peringkat_prioritas }} {{ $plan->pegawai->nama }}</span>
                                                        <span class="text-[10px] text-slate-500 block">Fit: <b>{{ $plan->match_score }}%</b> &bull; {{ $plan->status_kesiapan }}</span>
                                                    </div>
                                                    <span class="px-2 py-0.2 rounded-full text-[9px] font-bold shrink-0 {{ $plan->kesiapan_badge_class }}">
                                                        {{ $plan->status_nominasi }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-[11px] italic">Belum ada suksesor dinominasikan</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('manajemen-talenta.suksesi.jabatan', ['jabatan' => $jab->id, 'tahun' => $tahun]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-xs transition">
                                        <span>🔍</span> Job Matching
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-500">
                                    Tidak ada data jabatan yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
