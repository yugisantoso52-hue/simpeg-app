<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🏛️</span> Monitoring Eksekutif E-Cuti (Pimpinan)
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Laporan Pengawasan & Pengambilan Keputusan Cuti Pegawai Fakultas Keperawatan
                </p>
            </div>

            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('pengajuan-cuti.monitoring-pimpinan') }}" class="flex items-center gap-2">
                    <label class="text-xs font-bold text-gray-700 uppercase">Tahun:</label>
                    <select name="tahun" onchange="this.form.submit()"
                            class="rounded-lg border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500 font-semibold py-1.5">
                        @for($y = now()->year + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" @selected($tahun == $y)>Tahun {{ $y }}</option>
                        @endfor
                    </select>
                </form>
                <a href="{{ route('pengajuan-cuti.index') }}"
                   class="inline-flex items-center px-3.5 py-2 border border-gray-300 shadow-xs text-xs font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                    ← Kembali ke E-Cuti
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            {{-- INDIKATOR KUNCI --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pegawai Aktif</div>
                    <div class="mt-1 text-2xl font-black text-slate-900">{{ $totalPegawaiAktif }}</div>
                </div>

                <div class="bg-indigo-50/70 rounded-xl shadow-xs border border-indigo-200 p-4">
                    <div class="text-xs font-bold text-indigo-900 uppercase tracking-wider">Total Cuti Diajukan</div>
                    <div class="mt-1 text-2xl font-black text-indigo-950">{{ $totalCutiTahunIni }}</div>
                </div>

                <div class="bg-amber-50/70 rounded-xl shadow-xs border border-amber-200 p-4">
                    <div class="text-xs font-bold text-amber-900 uppercase tracking-wider">Menunggu Persetujuan</div>
                    <div class="mt-1 text-2xl font-black text-amber-950">{{ $cutiMenunggu }}</div>
                </div>

                <div class="bg-emerald-50/70 rounded-xl shadow-xs border border-emerald-200 p-4">
                    <div class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Cuti Disetujui</div>
                    <div class="mt-1 text-2xl font-black text-emerald-950">{{ $cutiDisetujui }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- WIDGET 1: PERINGATAN KUOTA RAWAN HANGUS (TAHUN N-2) --}}
                <div class="bg-white rounded-xl shadow-xs border border-rose-200 overflow-hidden">
                    <div class="bg-rose-50/80 px-4 py-3 border-b border-rose-200 flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-rose-950 flex items-center gap-1.5">
                            <span>⚠️</span> Peringatan Kuota Rawan Hangus (Tahun {{ $tahun - 2 }} / N-2)
                        </h3>
                        <span class="text-[10px] text-rose-700 bg-rose-100 border border-rose-300 px-2 py-0.5 rounded-full font-semibold">
                            Batas Akhir: 31 Des {{ $tahun }}
                        </span>
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-slate-600 mb-3 leading-relaxed">
                            Berdasarkan <strong>Peraturan BKN No. 24 Tahun 2017</strong>, hak cuti tahunan yang ditangguhkan maksimal berlaku selama 2 tahun kalender berturut-turut. Daftar berikut memiliki sisa kuota <strong>N-2 ({{ $tahun - 2 }})</strong> yang akan kedaluwarsa jika tidak digunakan:
                        </p>
                        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                            @forelse($rawanHangusList as $item)
                                <div class="py-2 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $item->pegawai->nama_lengkap ?? $item->pegawai->nama }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $item->pegawai->unitKerja?->nama_unit ?? '-' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 font-mono font-bold text-xs">
                                            {{ $item->sisa_kuota }} Hari
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-gray-400 italic">
                                    Tidak ada sisa kuota N-2 yang berisiko hangus pada tahun {{ $tahun }}.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- WIDGET 2: REKAP PENGAJUAN CUTI KHUSUS / DURASI PANJANG --}}
                <div class="bg-white rounded-xl shadow-xs border border-purple-200 overflow-hidden">
                    <div class="bg-purple-50/80 px-4 py-3 border-b border-purple-200 flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-purple-950 flex items-center gap-1.5">
                            <span>🏥</span> Pengawasan Cuti Khusus & Durasi Panjang
                        </h3>
                        <span class="text-[10px] text-purple-700 bg-purple-100 border border-purple-300 px-2 py-0.5 rounded-full font-semibold">
                            Tahun {{ $tahun }}
                        </span>
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-slate-600 mb-3 leading-relaxed">
                            Monitoring cuti berdurasi signifikan (Cuti Melahirkan, Cuti Besar, CLTN, Cuti Sakit > 3 hari) guna mitigasi kecukupan staf akademik dan layanan:
                        </p>
                        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                            @forelse($cutiKhusus as $ck)
                                <div class="py-2.5 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $ck->pegawai?->nama_lengkap ?? $ck->pegawai?->nama }}</div>
                                        <div class="text-[11px] font-semibold text-purple-800">{{ $ck->jenis_cuti }} ({{ $ck->jumlah_hari }} Hari)</div>
                                        <div class="text-[10px] text-slate-500 font-mono">
                                            {{ $ck->tanggal_mulai ? $ck->tanggal_mulai->format('d/m/Y') : '-' }} s.d. {{ $ck->tanggal_selesai ? $ck->tanggal_selesai->format('d/m/Y') : '-' }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $ck->status_badge_class }}">
                                            {{ $ck->status }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-gray-400 italic">
                                    Belum ada pengajuan cuti khusus pada tahun {{ $tahun }}.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            {{-- SEBARAN CUTI PER UNIT KERJA --}}
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 p-5">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span>🏢</span> Sebaran Pegawai Mengambil Cuti per Unit Kerja (Tahun {{ $tahun }})
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($unitCutiStats as $u)
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-center">
                            <div class="text-xs text-slate-600 font-semibold truncate" title="{{ $u->nama_unit }}">{{ $u->nama_unit }}</div>
                            <div class="mt-1 text-xl font-black text-indigo-700 font-mono">{{ $u->jumlah_cuti }}</div>
                            <div class="text-[10px] text-slate-400">Pegawai Cuti</div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
