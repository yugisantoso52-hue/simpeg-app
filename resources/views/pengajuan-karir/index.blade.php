<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Layanan & Verifikasi Usulan Karir ASN (KP, KGB & Satyalancana)"
            subtitle="Pusat Layanan Mandiri Pegawai, Verifikasi Administratif Berjenjang & Monitoring Jatuh Tempo"
        />
    </x-slot>

    <div class="py-6" x-data="{ uploadModalOpen: false, modalTitle: '', modalField: '', modalRedirectTab: '{{ $tab }}' }"
         @open-upload-syarat.window="modalTitle = $event.detail.title; modalField = $event.detail.field; modalRedirectTab = $event.detail.tab || '{{ $tab }}'; uploadModalOpen = true">
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

            {{-- NAVIGASI TAB CLUSTER LAYANAN KARIR & SATYALANCANA --}}
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
                <a href="{{ route('pengajuan-karir.index', ['tab' => 'all']) }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs {{ in_array($tab, ['all']) && !request()->filled('jenis') ? 'bg-indigo-600 text-white shadow-indigo-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200' }}">
                    <span>📑 Semua Usulan Karir</span>
                </a>

                <a href="{{ route('pengajuan-karir.index', ['tab' => 'kp', 'jenis' => 'KP']) }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs {{ $tab === 'kp' || request('jenis') === 'KP' ? 'bg-emerald-600 text-white shadow-emerald-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200' }}">
                    <span>🎖️ Kenaikan Pangkat (KP)</span>
                    @if(($countKpPending ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $tab === 'kp' || request('jenis') === 'KP' ? 'bg-emerald-800 text-white' : 'bg-emerald-100 text-emerald-800' }}">{{ $countKpPending }}</span>
                    @endif
                </a>

                <a href="{{ route('pengajuan-karir.index', ['tab' => 'kgb', 'jenis' => 'KGB']) }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs {{ $tab === 'kgb' || request('jenis') === 'KGB' ? 'bg-amber-600 text-white shadow-amber-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200' }}">
                    <span>💰 Gaji Berkala (KGB)</span>
                    @if(($countKgbPending ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $tab === 'kgb' || request('jenis') === 'KGB' ? 'bg-amber-800 text-white' : 'bg-amber-100 text-amber-800' }}">{{ $countKgbPending }}</span>
                    @endif
                </a>

                <a href="{{ route('pengajuan-karir.index', ['tab' => 'satyalancana']) }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs {{ $tab === 'satyalancana' ? 'bg-indigo-700 text-white shadow-indigo-600/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200' }}">
                    <span>🏆 Satyalancana Karya Satya</span>
                    @if(($countSatyalancanaPending ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $tab === 'satyalancana' ? 'bg-indigo-950 text-white' : 'bg-indigo-100 text-indigo-800' }}">{{ $countSatyalancanaPending }}</span>
                    @endif
                </a>

                @if($isExecutive ?? false)
                    <a href="{{ route('pengajuan-karir.index', ['tab' => 'radar']) }}" 
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs {{ $tab === 'radar' ? 'bg-blue-600 text-white shadow-blue-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200' }}">
                        <span>🎯 Radar Monitoring (KP & KGB)</span>
                        @if((($reminderKp->count() ?? 0) + ($reminderKgb->count() ?? 0)) > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $tab === 'radar' ? 'bg-blue-800 text-white' : 'bg-blue-100 text-blue-800' }}">{{ ($reminderKp->count() ?? 0) + ($reminderKgb->count() ?? 0) }}</span>
                        @endif
                    </a>
                @endif
            </div>

            @if(($tab ?? '') === 'satyalancana')
                @include('pengajuan-karir._tab_satyalancana')
            @elseif(($tab ?? '') === 'radar')
                @include('pengajuan-karir._tab_radar')
            @elseif(($tab ?? '') === 'kp' || request('jenis') === 'KP')
                @include('pengajuan-karir._panel_kelayakan_kp')
            @elseif(($tab ?? '') === 'kgb' || request('jenis') === 'KGB')
                @include('pengajuan-karir._panel_kelayakan_kgb')
            @else
                @if($userPegawai && !$isExecutive)
                    <div class="space-y-6">
                        @include('pengajuan-karir._panel_kelayakan_kp')
                        @include('pengajuan-karir._panel_kelayakan_kgb')
                    </div>
                @else
                    {{-- Kartu Aksi Buat Pengajuan Mandiri (Bagi Pegawai) --}}
                    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
                        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                            <div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    Layanan Karir Mandiri ASN
                                </span>
                                <h2 class="text-xl md:text-2xl font-black mt-2 tracking-tight">Form Pengajuan Kenaikan Gaji Berkala & Pangkat</h2>
                                <p class="text-slate-300 text-xs md:text-sm mt-1 max-w-2xl leading-relaxed">
                                    Diajukan oleh setiap pegawai (Dosen & Tendik PNS/PPPK) yang telah memenuhi syarat masa kerja dan evaluasi kinerja tahunan, diproses melalui verifikasi administrasi <strong>Ka Pokja Keu-Kepeg</strong>, paraf <strong>Kepala Bagian Umum</strong>, dan penetapan resmi oleh <strong>Wakil Dekan Bidang Keuangan dan Umum</strong>.
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
                @endif
            @endif

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
                                             <div class="font-bold text-slate-900 text-sm">{{ $item->pegawai?->nama_lengkap ?? $item->pegawai?->nama ?? '-' }}</div>
                                            <div class="text-[11px] text-slate-500 font-mono">NIP: {{ $item->pegawai?->nip ?? '-' }}</div>
                                            <div class="text-[11px] text-slate-600">{{ $item->pegawai?->jabatan?->nama_jabatan ?? '-' }}</div>
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
                                            @if($item->nomor_surat)
                                                <div class="text-[10px] font-mono font-bold text-indigo-700 mt-1">
                                                    No: {{ $item->nomor_surat }}
                                                </div>
                                            @else
                                                <div class="text-[10px] text-slate-400 italic mt-0.5">
                                                    No: [Ditetapkan Admin]
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-{{ $badge['color'] }}-100 text-{{ $badge['color'] }}-800 border border-{{ $badge['color'] }}-200">
                                                    {{ $badge['label'] }}
                                                </span>
                                                {{-- Tracking Mini 4 Tahap Bertingkat --}}
                                                <div class="flex items-center gap-1 text-[10px] text-slate-500 pt-0.5">
                                                    <span class="{{ $item->verifikasi_admin_at ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                                                        Admin: {{ $item->verifikasi_admin_at ? '✓' : '...' }}
                                                    </span>
                                                    <span>➔</span>
                                                    <span class="{{ $item->paraf_kapokja_at ? 'text-sky-700 font-bold' : 'text-slate-400' }}">
                                                        Pokja: {{ $item->paraf_kapokja_at ? '✓' : '...' }}
                                                    </span>
                                                    <span>➔</span>
                                                    <span class="{{ $item->paraf_kabag_at ? 'text-indigo-700 font-bold' : 'text-slate-400' }}">
                                                        Kabag: {{ $item->paraf_kabag_at ? '✓' : '...' }}
                                                    </span>
                                                    <span>➔</span>
                                                    <span class="{{ $item->paraf_wd2_at ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                                                        WD II: {{ $item->paraf_wd2_at ? '✓' : '...' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 text-center text-slate-500 font-mono text-[11px]">
                                            {{ $item->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <div class="flex flex-col items-center gap-1.5">
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
                                                        <a href="{{ route('reports.kgb.pdf', $item->id) }}" target="_blank"
                                                           class="px-2 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition"
                                                           title="Cetak Surat KGB Resmi">
                                                            📄 PDF
                                                        </a>
                                                    @endif
                                                </div>

                                                {{-- TOMBOL VERIFIKASI BERTINGKAT TERKUNCI (ADMIN -> POKJA -> KABAG -> WD2) --}}
                                                @if($isExecutive ?? false)
                                                    <div class="flex items-center justify-center gap-1 flex-wrap pt-1 border-t border-slate-100">
                                                        {{-- 1. ADMIN SCREENING --}}
                                                        @if($item->verifikasi_admin_at)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="1. Admin: Selesai">
                                                                ✓ 1. Admin
                                                            </span>
                                                        @elseif(($isAdmin ?? false) && $item->status !== 'disetujui_wd2' && $item->status !== 'ditolak')
                                                            <form action="{{ route('pengajuan-karir.verifikasi', $item->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="tahap" value="verifikasi_admin">
                                                                <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-600 text-white hover:bg-amber-700 transition" title="1. Verifikasi Screening Berkas">
                                                                    ✓ 1. Admin
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button type="button" disabled class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu Screening Admin">
                                                                ⏳ 1. Admin
                                                            </button>
                                                        @endif

                                                        {{-- 2. PARAF POKJA --}}
                                                        @if($item->paraf_kapokja_at)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="2. Paraf Pokja: Selesai">
                                                                ✓ 2. Pokja
                                                            </span>
                                                        @elseif(!$item->verifikasi_admin_at)
                                                            <button type="button" disabled class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 cursor-not-allowed opacity-60" title="🔒 Terkunci: Menunggu Admin Screening Berkas">
                                                                🔒 2. Pokja
                                                            </button>
                                                        @elseif(($isKaPokja ?? false) && $item->status !== 'disetujui_wd2' && $item->status !== 'ditolak')
                                                            <form action="{{ route('pengajuan-karir.verifikasi', $item->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="tahap" value="paraf_kapokja">
                                                                <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-600 text-white hover:bg-teal-700 transition" title="2. Bubuhkan Paraf Ka Pokja">
                                                                    ✒ 2. Pokja
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button type="button" disabled class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu Ka Pokja">
                                                                ⏳ 2. Pokja
                                                            </button>
                                                        @endif

                                                        {{-- 3. PARAF KABAG --}}
                                                        @if($item->paraf_kabag_at)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="3. Paraf Kabag: Selesai">
                                                                ✓ 3. Kabag
                                                            </span>
                                                        @elseif(!$item->paraf_kapokja_at)
                                                            <button type="button" disabled class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 cursor-not-allowed opacity-60" title="🔒 Terkunci: Menunggu Paraf Ka Pokja">
                                                                🔒 3. Kabag
                                                            </button>
                                                        @elseif(($isKabag ?? false) && $item->status !== 'disetujui_wd2' && $item->status !== 'ditolak')
                                                            <form action="{{ route('pengajuan-karir.verifikasi', $item->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="tahap" value="paraf_kabag">
                                                                <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white hover:bg-blue-700 transition" title="3. Bubuhkan Paraf Kabag Umum">
                                                                    ✒ 3. Kabag
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button type="button" disabled class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu Kabag Umum">
                                                                ⏳ 3. Kabag
                                                            </button>
                                                        @endif

                                                        {{-- 4. TTE WD2 --}}
                                                        @if($item->paraf_wd2_at || $item->status === 'disetujui_wd2')
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="4. Disahkan TTE WD2">
                                                                ✓ 4. Sah (WD2)
                                                            </span>
                                                        @elseif(!$item->paraf_kabag_at)
                                                            <button type="button" disabled class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 cursor-not-allowed opacity-60" title="🔒 Terkunci: Menunggu Paraf Kabag Umum">
                                                                🔒 4. WD2
                                                            </button>
                                                        @elseif((($isWd2 ?? false) || ($isDekan ?? false)) && $item->status !== 'ditolak')
                                                            <form action="{{ route('pengajuan-karir.verifikasi', $item->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="tahap" value="paraf_wd2">
                                                                <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-700 text-white hover:bg-emerald-800 transition" title="4. Sahkan Usulan Resmi TTE WD II">
                                                                    ✍ 4. TTE WD2
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button type="button" disabled class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu TTE WD II">
                                                                ⏳ 4. WD2
                                                            </button>
                                                        @endif
                                                    </div>
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

            {{-- Modal Upload / Lengkapi Berkas Mandiri --}}
            @include('pengajuan-karir._modal_upload_syarat')

        </div>
    </div>
</x-app-layout>
