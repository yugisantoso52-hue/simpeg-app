<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Layanan Pengajuan Karir (KGB & Kenaikan Pangkat)"
            subtitle="Portal Mandiri Pegawai (Dosen & Tendik) dan Verifikasi Administratif Berjenjang"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-green-600 text-lg">✅</span>
                        <span class="font-medium text-green-800 text-sm">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 font-bold">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-rose-600 text-lg">⚠️</span>
                        <span class="font-medium text-rose-800 text-sm">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900 font-bold">✕</button>
                </div>
            @endif

            {{-- Kartu Aksi Buat Pengajuan Mandiri (Bagi Pegawai) --}}
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
                <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            Layanan Karir Mandiri ASN
                        </span>
                        <h2 class="text-xl md:text-2xl font-black mt-2 tracking-tight">Form Pengajuan Kenaikan Gaji Berkala & Pangkat</h2>
                        <p class="text-slate-300 text-xs md:text-sm mt-1 max-w-2xl leading-relaxed">
                            Diajukan oleh setiap pegawai (Dosen & Tendik PNS/PPPK) yang telah memenuhi syarat masa kerja dan evaluasi kinerja tahunan, diproses melalui paraf <strong>Kepala Bagian Umum</strong> dan <strong>Wakil Dekan II</strong> sebelum ditetapkan oleh <strong>Dekan</strong>.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                        <a href="{{ route('pengajuan-karir.create', ['jenis' => 'KGB']) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-lg shadow-amber-500/20 transition transform active:scale-95">
                            <span>⚡ Ajukan KGB (PNS / PPPK)</span>
                        </a>
                        <a href="{{ route('pengajuan-karir.create', ['jenis' => 'KP']) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 shadow-lg shadow-emerald-500/20 transition transform active:scale-95">
                            <span>🎖️ Ajukan Kenaikan Pangkat (PNS)</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Ringkasan 6 Periode Kenaikan Pangkat BKN --}}
            <div class="rounded-xl border border-indigo-100 bg-white p-4 shadow-xs">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    <span class="text-indigo-600">📅</span>
                    <span>Jadwal 6 Periode Kenaikan Pangkat PNS (BKN No. 4/2023):</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 text-[11px]">
                    <div class="p-2 rounded-lg bg-slate-50 border border-slate-200 text-center">
                        <strong class="text-indigo-900 block">1. Februari</strong>
                        <span class="text-slate-500 text-[10px]">15 Des - 15 Jan</span>
                    </div>
                    <div class="p-2 rounded-lg bg-slate-50 border border-slate-200 text-center">
                        <strong class="text-indigo-900 block">2. April</strong>
                        <span class="text-slate-500 text-[10px]">1 - 28 Feb</span>
                    </div>
                    <div class="p-2 rounded-lg bg-slate-50 border border-slate-200 text-center">
                        <strong class="text-indigo-900 block">3. Juni</strong>
                        <span class="text-slate-500 text-[10px]">1 - 30 Apr</span>
                    </div>
                    <div class="p-2 rounded-lg bg-slate-50 border border-slate-200 text-center">
                        <strong class="text-indigo-900 block">4. Agustus</strong>
                        <span class="text-slate-500 text-[10px]">1 - 30 Jun</span>
                    </div>
                    <div class="p-2 rounded-lg bg-slate-50 border border-slate-200 text-center">
                        <strong class="text-indigo-900 block">5. Oktober</strong>
                        <span class="text-slate-500 text-[10px]">1 - 31 Ags</span>
                    </div>
                    <div class="p-2 rounded-lg bg-slate-50 border border-slate-200 text-center">
                        <strong class="text-indigo-900 block">6. Desember</strong>
                        <span class="text-slate-500 text-[10px]">1 - 31 Okt</span>
                    </div>
                </div>
            </div>

            {{-- Tabel Daftar Pengajuan --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <h3 class="text-base font-bold text-slate-800">
                            {{ $isExecutive ? 'Daftar Pengajuan Karir Masuk & Verifikasi' : 'Riwayat Pengajuan Karir Saya' }}
                        </h3>
                        <div class="flex flex-wrap items-center gap-2">
                            @if($isExecutive)
                                <a href="{{ route('pengajuan-karir.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('scope') && !request('jenis') ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600' }}">Semua Usulan Masuk</a>
                                <a href="{{ route('pengajuan-karir.index', ['scope' => 'saya']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('scope') === 'saya' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600' }}">Usulan Saya Sendiri</a>
                            @else
                                <a href="{{ route('pengajuan-karir.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('jenis') ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600' }}">Semua</a>
                            @endif
                            <a href="{{ route('pengajuan-karir.index', ['jenis' => 'KGB']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('jenis') === 'KGB' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600' }}">KGB Saja</a>
                            <a href="{{ route('pengajuan-karir.index', ['jenis' => 'KP']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('jenis') === 'KP' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600' }}">KP Saja</a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-600 uppercase text-[11px] font-bold">
                                    <th class="py-3 px-3">No</th>
                                    <th class="py-3 px-3">Pegawai & Unit</th>
                                    <th class="py-3 px-3">Jenis & Periode</th>
                                    <th class="py-3 px-3">Status Verifikasi & Paraf</th>
                                    <th class="py-3 px-3 text-center">Tanggal Usulan</th>
                                    <th class="py-3 px-3 text-center">Aksi & Dokumen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($pengajuans as $item)
                                    @php
                                        $badge = $item->status_badge;
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-3 font-mono text-slate-400">{{ $loop->iteration }}</td>
                                        <td class="py-3 px-3">
                                             <div class="font-bold text-slate-900 text-sm">{{ $item->pegawai->nama_lengkap ?? $item->pegawai->nama }}</div>
                                            <div class="text-[11px] text-slate-500 font-mono">NIP: {{ $item->pegawai->nip }}</div>
                                            <div class="text-[11px] text-slate-600">{{ $item->pegawai->jabatan->nama_jabatan ?? '-' }}</div>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $item->jenis_pengajuan === 'KP' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $item->jenis_pengajuan === 'KP' ? 'Kenaikan Pangkat' : 'Kenaikan Gaji Berkala' }}
                                            </span>
                                            @if($item->jenis_pengajuan === 'KP')
                                                <div class="text-[11px] font-medium text-slate-700 mt-1">
                                                    Periode: <strong>{{ $item->periode_kp }} {{ $item->tahun_periode }}</strong>
                                                </div>
                                                <div class="text-[10px] text-slate-500">
                                                    {{ $item->golonganLama->nama_golongan ?? '-' }} ➔ {{ $item->golonganTujuan->nama_golongan ?? '-' }}
                                                </div>
                                            @else
                                                <div class="text-[11px] text-slate-600 mt-1">
                                                    TMT: {{ $item->tmt_baru ? \Carbon\Carbon::parse($item->tmt_baru)->format('d-m-Y') : '-' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-{{ $badge['color'] }}-100 text-{{ $badge['color'] }}-800 border border-{{ $badge['color'] }}-200">
                                                    {{ $badge['label'] }}
                                                </span>
                                                {{-- Tracking Mini Paraf 4 Tahap --}}
                                                <div class="flex items-center gap-1.5 text-[10px] text-slate-500 pt-0.5">
                                                    <span class="{{ $item->paraf_kapokja_at ? 'text-sky-700 font-bold' : 'text-slate-400' }}">
                                                        Pokja: {{ $item->paraf_kapokja_at ? '✓' : '...' }}
                                                    </span>
                                                    <span>•</span>
                                                    <span class="{{ $item->paraf_kabag_at ? 'text-indigo-700 font-bold' : 'text-slate-400' }}">
                                                        Kabag: {{ $item->paraf_kabag_at ? '✓' : '...' }}
                                                    </span>
                                                    <span>•</span>
                                                    <span class="{{ $item->paraf_wd2_at ? 'text-purple-700 font-bold' : 'text-slate-400' }}">
                                                        WD II: {{ $item->paraf_wd2_at ? '✓' : '...' }}
                                                    </span>
                                                    <span>•</span>
                                                    <span class="{{ $item->ttd_dekan_at ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                                                        Dekan: {{ $item->ttd_dekan_at ? '✓' : '...' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 text-center text-slate-500 font-mono text-[11px]">
                                            {{ $item->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="{{ route('pengajuan-karir.show', $item->id) }}" 
                                                   class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                                    Detail & Tracking
                                                </a>
                                                @if($item->jenis_pengajuan === 'KP')
                                                    <a href="{{ route('reports.kp.pdf', $item->id) }}" target="_blank"
                                                       class="px-2 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition"
                                                       title="Cetak Surat Usulan KP Resmi">
                                                        📄 PDF
                                                    </a>
                                                @else
                                                    <a href="{{ route('reports.kgb.pdf', $item->pegawai_id) }}" target="_blank"
                                                       class="px-2 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition"
                                                       title="Cetak Surat KGB Resmi">
                                                        📄 PDF
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-8 text-slate-400">
                                            Belum ada permohonan pengajuan karir yang diajukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $pengajuans->links() }}
                    </div>
                </div>
            </x-enterprise.card>

        </div>
    </div>
</x-app-layout>
