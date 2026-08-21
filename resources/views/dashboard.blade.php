<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Ucapan Selamat Datang --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold">Selamat Datang, {{ Auth::user()->name ?? 'Administrator' }}!</h3>
                    <p class="text-sm text-gray-500 mt-1">Berikut adalah ringkasan data sistem informasi kepegawaian saat ini.</p>
                </div>
            </div>

            {{-- BARIS 1: STATISTIK UTAMA PEGAWAI --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="p-6 rounded-lg shadow-sm text-white flex items-center justify-between" style="background-color: #1d4ed8;">
                    <div>
                        <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Total Pegawai</h3>
                        <p class="text-3xl font-bold mt-2">{{ $statistik['total'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-20 rounded-full">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>

                <div class="p-6 rounded-lg shadow-sm text-white flex items-center justify-between" style="background-color: #15803d;">
                    <div>
                        <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Pegawai Aktif</h3>
                        <p class="text-3xl font-bold mt-2">{{ $statistik['aktif'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-20 rounded-full">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <div class="p-6 rounded-lg shadow-sm text-white flex items-center justify-between" style="background-color: #0284c7;">
                    <div>
                        <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Status ASN</h3>
                        <p class="text-3xl font-bold mt-2">{{ $statistik['asn'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-20 rounded-full">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"/></svg>
                    </div>
                </div>

                <div class="p-6 rounded-lg shadow-sm text-white flex items-center justify-between" style="background-color: #b45309;">
                    <div>
                        <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Pegawai Pensiun</h3>
                        <p class="text-3xl font-bold mt-2">{{ $statistik['pensiun'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-20 rounded-full">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                </div>
            </div>

            {{-- 🔔 BARIS 2: PUSAT REMINDER TRANSAKSI KEPEGAWAIAN (GRID 4 KOLOM) 🔔 --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center space-x-2 border-b border-gray-100 pb-3 mb-4">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <h3 class="text-lg font-bold text-gray-800">Pusat Reminder Transaksi Kepegawaian</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    {{-- Card 1: Reminder KGB --}}
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h4 class="font-semibold text-amber-800 flex justify-between items-center mb-2">
                                <div>
                                    <span>Gaji Berkala (KGB)</span>
                                    <span class="block text-[10px] text-amber-600 font-normal">3 Bulan ke Depan</span>
                                </div>
                                <span class="bg-amber-200 text-amber-900 text-xs px-2 py-0.5 rounded-full font-bold">{{ count($reminder['kgb'] ?? []) }}</span>
                            </h4>
                            @if(isset($reminder['kgb']) && count($reminder['kgb']) > 0)
                                <ul class="text-xs text-amber-900 divide-y divide-amber-200 max-h-48 overflow-y-auto">
                                    @foreach($reminder['kgb'] as $r)
                                        <li class="py-1.5 flex justify-between items-center">
                                            <span class="truncate mr-2" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                            <strong class="text-amber-700 font-mono flex-shrink-0">{{ $r->tanggal_kegiatan }}</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-xs text-amber-600 mt-2 italic">Aman. Tidak ada jatuh tempo.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Card 2: Reminder Kenaikan Pangkat --}}
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h4 class="font-semibold text-emerald-800 flex justify-between items-center mb-2">
                                <div>
                                    <span>Kenaikan Pangkat (KP)</span>
                                    <span class="block text-[10px] text-emerald-600 font-normal">3 Bulan ke Depan</span>
                                </div>
                                <span class="bg-emerald-200 text-emerald-900 text-xs px-2 py-0.5 rounded-full font-bold">{{ count($reminder['kp'] ?? []) }}</span>
                            </h4>
                            @if(isset($reminder['kp']) && count($reminder['kp']) > 0)
                                <ul class="text-xs text-emerald-900 divide-y divide-emerald-200 max-h-48 overflow-y-auto">
                                    @foreach($reminder['kp'] as $r)
                                        <li class="py-1.5 flex justify-between items-center">
                                            <span class="truncate mr-2" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                            <strong class="text-emerald-700 font-mono flex-shrink-0">{{ $r->tanggal_kegiatan }}</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-xs text-emerald-600 mt-2 italic">Aman. Tidak ada jatuh tempo.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Card 3: Reminder Satyalancana --}}
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h4 class="font-semibold text-indigo-800 flex justify-between items-center mb-2">
                                <div>
                                    <span>Satyalancana</span>
                                    <span class="block text-[10px] text-indigo-600 font-normal">3 Bulan ke Depan</span>
                                </div>
                                <span class="bg-indigo-200 text-indigo-900 text-xs px-2 py-0.5 rounded-full font-bold">{{ count($reminder['satyalancana'] ?? []) }}</span>
                            </h4>
                            @if(isset($reminder['satyalancana']) && count($reminder['satyalancana']) > 0)
                                <ul class="text-xs text-indigo-900 divide-y divide-indigo-200 max-h-48 overflow-y-auto">
                                    @foreach($reminder['satyalancana'] as $r)
                                        <li class="py-1.5 flex justify-between items-center">
                                            <span class="truncate mr-2" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                            <strong class="text-indigo-700 font-mono flex-shrink-0">{{ $r->tanggal_kegiatan }}</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-xs text-indigo-600 mt-2 italic">Aman. Tidak ada jatuh tempo.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Card 4: Reminder Pensiun --}}
                    <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h4 class="font-semibold text-rose-800 flex justify-between items-center mb-2">
                                <div>
                                    <span>Masa Pensiun (BUP 58)</span>
                                    <span class="block text-[10px] text-rose-600 font-semibold">1 Tahun ke Depan</span>
                                </div>
                                <span class="bg-rose-200 text-rose-900 text-xs px-2 py-0.5 rounded-full font-bold">{{ count($reminder['pensiun'] ?? []) }}</span>
                            </h4>
                            @if(isset($reminder['pensiun']) && count($reminder['pensiun']) > 0)
                                <ul class="text-xs text-rose-900 divide-y divide-rose-200 max-h-48 overflow-y-auto">
                                    @foreach($reminder['pensiun'] as $r)
                                        <li class="py-1.5 flex justify-between items-center">
                                            <span class="truncate mr-2" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                            <strong class="text-rose-700 font-mono flex-shrink-0">{{ $r->tanggal_kegiatan }}</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-xs text-rose-600 mt-2 italic">Aman. Tidak ada masa pensiun terdekat.</p>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- BARIS 3: SUB-STATISTIK JENIS PEGAWAI --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-500 text-sm uppercase tracking-wider">Pegawai PNS</h3>
                        <p class="text-3xl font-extrabold mt-2 text-blue-600">{{ $statistik['pns'] ?? 0 }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-500 text-sm uppercase tracking-wider">Pegawai PPPK</h3>
                        <p class="text-3xl font-extrabold mt-2 text-emerald-600">{{ $statistik['pppk'] ?? 0 }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-500 text-sm uppercase tracking-wider">Pegawai Honorer</h3>
                        <p class="text-3xl font-extrabold mt-2 text-purple-600">{{ $statistik['honorer'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</x-app-layout>