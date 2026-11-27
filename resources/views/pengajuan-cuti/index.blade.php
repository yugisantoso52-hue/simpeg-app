<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🏖️</span> {{ ($isPegawaiOnly || ($showMine ?? false)) ? 'Permohonan Cuti Saya' : 'E-Cuti Pegawai (Monitoring Seluruh Cuti)' }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Layanan Mandiri Pengajuan Cuti Online &amp; Verifikasi Persetujuan Pimpinan — Mengacu Peraturan BKN Nomor 7 Tahun 2022
                </p>
            </div>

            <div class="flex items-center gap-2">
                {{-- Tombol Buku Cuti Mandiri / Pegawai --}}
                <a href="{{ route('pengajuan-cuti.buku-cuti') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-teal-300 bg-teal-50 px-3.5 py-2.5 text-xs font-semibold text-teal-800 shadow-xs hover:bg-teal-100 transition">
                    <span>📖</span> {{ Auth::user()->pegawai_id ? 'Buku Cuti Saya' : 'Buku Kendali Cuti' }}
                </a>

                {{-- Khusus Admin & Pimpinan --}}
                @if(Auth::user()->hasRole('admin'))
                    <a href="{{ route('pengajuan-cuti.rekap-kuota') }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-300 bg-indigo-50 px-3.5 py-2.5 text-xs font-semibold text-indigo-800 shadow-xs hover:bg-indigo-100 transition">
                        <span>📊</span> Rekap Kuota Pegawai
                    </a>
                @endif

                @if(Auth::user()->hasRole('pimpinan') || Auth::user()->hasRole('admin') || Auth::user()->isAtasan())
                    <a href="{{ route('pengajuan-cuti.monitoring-pimpinan') }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-purple-300 bg-purple-50 px-3.5 py-2.5 text-xs font-semibold text-purple-800 shadow-xs hover:bg-purple-100 transition">
                        <span>🏛️</span> Monitoring Pimpinan
                    </a>
                @endif

                {{-- Tombol SOP E-Cuti --}}
                <x-sop-modal title="SOP Pengajuan & Pemisahan Hak Cuti ASN (PNS vs PPPK)" 
                             buttonLabel="SOP & Syarat Cuti" 
                             badge="Peraturan BKN Nomor 7 Tahun 2022" 
                             color="amber">
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200">
                            <h4 class="font-bold text-amber-950 text-xs mb-1 flex items-center gap-1.5">
                                <x-icon name="calendar-off" class="w-4 h-4 text-amber-700" />
                                Pemisahan Hak Cuti PNS dan PPPK (Peraturan BKN Nomor 7 Tahun 2022)
                            </h4>
                            <p class="text-amber-900 text-[11px] leading-relaxed">
                                • <strong>PNS (7 Jenis Cuti)</strong>: Cuti Tahunan (12 hk), Cuti Besar (3 bln, MK ≥ 5 thn), Cuti Sakit, Cuti Melahirkan (3 bln), Cuti Alasan Penting / CAP (maks 1 bln), CLTN (maks 3 thn), dan Cuti Bersama.<br>
                                • <strong>PPPK (4 Jenis Cuti — Pasal 4 Perbkn No. 7/2022)</strong>: Hanya berhak atas <strong>Cuti Tahunan (12 hk), Cuti Sakit (maks 1 bln / kecelakaan kerja), Cuti Melahirkan (3 bln), dan Cuti Bersama</strong>. PPPK tidak memiliki Cuti Besar, CAP tersendiri, maupun CLTN. Keperluan mendesak keluarga inti sakit keras/meninggal atau pernikahan pertama bagi PPPK menggunakan <strong>Cuti Tahunan Pasal 9 (maks 6 hari kerja)</strong>.
                            </p>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-amber-600 text-white flex items-center justify-center text-[10px]">1</span>
                                Syarat &amp; Berkas Bukti Dukung Utama
                            </h4>
                            <div class="space-y-2 ps-2 border-l-2 border-amber-200">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Cuti Tahunan &amp; Pengecualian Pasal 9 PPPK</strong>
                                    <span class="text-slate-600 text-[11px]">Masa kerja minimal 1 tahun (12 hk). Akumulasi N-1 maks 18 hk (kontrak &gt; 2 thn), N-2 maks 24 hk (kontrak &gt; 3 thn). Khusus PPPK masa kerja &lt; 1 tahun dengan alasan mendesak Pasal 9 diberikan maks 6 hk (wajib Surat Rawat Inap jika keluarga sakit keras).</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Cuti Sakit (Pasal 12 – 17)</strong>
                                    <span class="text-slate-600 text-[11px]">Sakit 1–14 hari wajib melampirkan Surat Keterangan Dokter. Bagi PPPK sakit &gt; 14 hari (maks 1 bulan) wajib melampirkan <strong>Surat Keterangan Dokter Pemerintah</strong>. Gugur kandungan maks 1,5 bulan.</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Cuti Melahirkan (Pasal 18 – 19)</strong>
                                    <span class="text-slate-600 text-[11px]">Diberikan selama 3 bulan untuk kelahiran anak ke-1 s.d. ke-3 (bagi PPPK dihitung saat sudah berstatus PPPK). Melampirkan surat keterangan HPL dari dokter/bidan.</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">2</span>
                                Alur Persetujuan Bertingkat (Hirarki Jabatan)
                            </h4>
                            <div class="space-y-2 ps-2 border-l-2 border-emerald-200">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Tahap 1: Pertimbangan Atasan Langsung</strong>
                                    <span class="text-slate-600 text-[11px]">Atasan langsung (Ka Pokja/Kajur/Koorprodi/Kabag) memberikan pertimbangan menyetujui, mengubah, menangguhkan, atau menolak.</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Tahap 2: Keputusan Pejabat Yang Berwenang (PYBMC / Dekan)</strong>
                                    <span class="text-slate-600 text-[11px]">Dekan / Pejabat berwenang menerbitkan Surat Keputusan Cuti resmi bertanda tangan elektronik QR Code.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-sop-modal>

                <a href="{{ route('pengajuan-cuti.create') }}"
                   class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-700 transition">
                    + Ajukan Cuti Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10 space-y-6">

            @if(session('success'))
                <div class="rounded-lg border border-green-300 bg-green-50 px-5 py-4 text-green-700 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="text-green-700 font-bold hover:text-green-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-lg border border-red-300 bg-red-50 px-5 py-4 text-red-700 flex items-center justify-between shadow-sm">
                    <span>{{ session('error') }}</span>
                    <button type="button" class="text-red-700 font-bold hover:text-red-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- JIKA PEGAWAI BIASA ATAU TAB PERMOHONAN CUTI SAYA: TAMPILKAN KARTU KUOTA CUTI TAHUNAN (N, N-1, N-2) --}}
            @if(($isPegawaiOnly || ($showMine ?? false)) && $pegawai)
                @php
                    $catatanCutiIndex = $pegawai->getCatatanCutiTahunan((int) now()->year);
                    $isPppkIndex = $pegawai->isPppk();
                @endphp
                <x-enterprise.stat-grid :cols="3">
                    <x-enterprise.stat-card
                        :title="'Sisa Kuota Cuti Tahunan (Tahun ' . now()->year . ')'"
                        :value="$pegawai->sisa_cuti_tahunan"
                        suffix="/ 12 Hari Kerja"
                        icon="cuti"
                        color="emerald"
                        :trend="'Status: ' . $pegawai->status_asn_cuti"
                        trendDirection="up"
                        :subtitle="$isPppkIndex ? '4 Jenis Hak Cuti — Perbkn No. 7/2022' : '7 Jenis Hak Cuti ASN'"
                    >
                        <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-600">
                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200 font-semibold">
                                {{ $catatanCutiIndex['N']['label_tahun'] }}: <strong>{{ $catatanCutiIndex['N']['sisa_formatted'] }}</strong>
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200">
                                {{ $catatanCutiIndex['N-1']['label_tahun'] }}: <strong>{{ $catatanCutiIndex['N-1']['sisa_formatted'] }}</strong>
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200">
                                {{ $catatanCutiIndex['N-2']['label_tahun'] }}: <strong>{{ $catatanCutiIndex['N-2']['sisa_formatted'] }}</strong>
                            </span>
                        </div>
                    </x-enterprise.stat-card>

                    <x-enterprise.stat-card
                        title="Total Cuti Diajukan"
                        :value="$statistics['total'] ?? 0"
                        suffix="Permohonan"
                        icon="clipboard-list"
                        color="blue"
                        subtitle="Seluruh riwayat pengajuan cuti Anda"
                    />

                    <x-enterprise.stat-card
                        title="Sedang Diproses"
                        :value="$statistics['menunggu'] ?? 0"
                        suffix="Permohonan"
                        icon="hourglass"
                        color="amber"
                        subtitle="Menunggu verifikasi & persetujuan"
                    />
                </x-enterprise.stat-grid>
            @else
                {{-- STATISTIK UNTUK ADMIN & PIMPINAN --}}
                <x-enterprise.stat-grid :cols="5">
                    <x-enterprise.stat-card
                        title="Total Pengajuan"
                        :value="$statistics['total'] ?? 0"
                        icon="clipboard-list"
                        color="slate"
                    />

                    <x-enterprise.stat-card
                        title="Menunggu"
                        :value="$statistics['menunggu'] ?? 0"
                        icon="hourglass"
                        color="amber"
                    />

                    <x-enterprise.stat-card
                        title="Disetujui"
                        :value="$statistics['disetujui'] ?? 0"
                        icon="check-circle"
                        color="emerald"
                    />

                    <x-enterprise.stat-card
                        title="Ditolak"
                        :value="$statistics['ditolak'] ?? 0"
                        icon="x-circle"
                        color="rose"
                    />

                    <x-enterprise.stat-card
                        title="Cuti Hari Ini"
                        :value="$statistics['hari_ini'] ?? 0"
                        icon="calendar-off"
                        color="blue"
                    />
                </x-enterprise.stat-grid>
            @endif

            {{-- PANEL RINGKASAN SYARAT, KETENTUAN & PEMISAHAN HAK CUTI PNS vs PPPK --}}
            @include('pengajuan-cuti._ringkasan_regulasi_bkn', ['defaultOpen' => false])

            {{-- FILTER & PENCARIAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-5 border-b border-gray-100">
                    <form method="GET" action="{{ route('pengajuan-cuti.index') }}">
                        @if($showMine ?? false)
                            <input type="hidden" name="mine" value="1">
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-5">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Cari Alasan / Nomor Surat / Nama Pegawai..."
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            </div>

                            <div class="md:col-span-3">
                                <select name="jenis" class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Semua Jenis Cuti</option>
                                    <option value="Cuti Tahunan" @selected(request('jenis') == 'Cuti Tahunan')>Cuti Tahunan</option>
                                    <option value="Cuti Sakit" @selected(request('jenis') == 'Cuti Sakit')>Cuti Sakit</option>
                                    <option value="Cuti Melahirkan" @selected(request('jenis') == 'Cuti Melahirkan')>Cuti Melahirkan</option>
                                    <option value="Cuti Alasan Penting" @selected(request('jenis') == 'Cuti Alasan Penting')>Cuti Alasan Penting</option>
                                    <option value="Cuti Besar" @selected(request('jenis') == 'Cuti Besar')>Cuti Besar</option>
                                    <option value="Cuti di Luar Tanggungan Negara" @selected(request('jenis') == 'Cuti di Luar Tanggungan Negara')>CLTN</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Semua Status</option>
                                    <option value="Menunggu Persetujuan" @selected(request('status') == 'Menunggu Persetujuan')>Menunggu</option>
                                    <option value="Disetujui" @selected(request('status') == 'Disetujui')>Disetujui</option>
                                    <option value="Ditolak" @selected(request('status') == 'Ditolak')>Ditolak</option>
                                    <option value="Dibatalkan" @selected(request('status') == 'Dibatalkan')>Dibatalkan</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 flex gap-2">
                                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-sm font-semibold py-2 transition">
                                    Filter
                                </button>
                                @if(request()->hasAny(['search', 'jenis', 'status']))
                                    <a href="{{ route('pengajuan-cuti.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm transition">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                {{-- TABEL DATA PENGAJUAN CUTI --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">No</th>
                                @if(!$isPegawaiOnly)
                                    <th class="py-3.5 px-4">Pegawai</th>
                                @endif
                                <th class="py-3.5 px-4">Jenis Cuti</th>
                                <th class="py-3.5 px-4">Tanggal Pelaksanaan</th>
                                <th class="py-3.5 px-4 text-center">Durasi</th>
                                <th class="py-3.5 px-4">Alasan</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($data as $row)
                                <tr class="hover:bg-gray-50/70 transition">
                                    <td class="py-3 px-4 text-center text-gray-400 font-mono">
                                        {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                                    </td>
                                    @if(!$isPegawaiOnly)
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1.5 font-bold text-gray-900">
                                                <a href="{{ $row->pegawai_id ? route('pegawai.show', $row->pegawai_id) : '#' }}" class="hover:text-emerald-600">
                                                    {{ $row->pegawai?->nama_lengkap ?? $row->pegawai?->nama ?? '-' }}
                                                </a>
                                                @if($row->pegawai)
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase {{ $row->pegawai->isPppk() ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                                        {{ $row->pegawai->status_asn_cuti }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono">NIP. {{ $row->pegawai?->nip ?? '-' }}</div>
                                            @if($row->pegawai)
                                                <div class="text-[11px] text-gray-600 mt-0.5">{{ $row->pegawai->unit_kerja_tampil }} — {{ $row->pegawai->jabatan_tampil }}</div>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="py-3 px-4 font-semibold text-gray-800">
                                        {{ $row->jenis_cuti }}
                                        @if($row->nomor_surat)
                                            <div class="text-[11px] text-gray-500 font-mono">No: {{ $row->nomor_surat }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-xs font-semibold text-gray-900">
                                            {{ $row->tanggal_mulai ? $row->tanggal_mulai->translatedFormat('d M Y') : '-' }} s.d.
                                        </div>
                                        <div class="text-xs text-gray-700">
                                            {{ $row->tanggal_selesai ? $row->tanggal_selesai->translatedFormat('d M Y') : '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-block px-2 py-1 rounded bg-gray-100 text-gray-800 text-xs font-bold font-mono">
                                            {{ $row->jumlah_hari }} Hari
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs truncate" title="{{ $row->alasan }}">
                                        {{ $row->alasan }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $s1 = !empty($row->created_at);
                                            $s2 = !empty($row->verifikasi_admin_at);
                                            $s3 = !empty($row->paraf_kapokja_at);
                                            $s4 = !empty($row->pertimbangan_atasan_at) || (!empty($row->pertimbangan_atasan) && $row->pertimbangan_atasan !== 'Menunggu Persetujuan');
                                            $s5 = in_array($row->status, ['Disetujui', 'Ditolak']) || !empty($row->approved_at);
                                            $isDitolakCuti = strtolower($row->status) === 'ditolak';
                                            $totalCompletedCuti = (int)$s1 + (int)$s2 + (int)$s3 + (int)$s4 + (int)$s5;
                                            $pctCuti = round(($totalCompletedCuti / 5) * 100);
                                        @endphp
                                        <div class="space-y-1.5 min-w-[160px]">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $row->status_badge_class }}">
                                                    {{ $row->status_label ?? $row->status }}
                                                </span>
                                                <span class="text-[10px] font-mono font-bold {{ $isDitolakCuti ? 'text-rose-600' : ($totalCompletedCuti == 5 ? 'text-emerald-700' : 'text-teal-700') }}">
                                                    {{ $pctCuti }}%
                                                </span>
                                            </div>

                                            {{-- Segmented 5-Step Visual Progress Bar --}}
                                            <div class="flex items-center gap-1">
                                                <div class="h-1.5 flex-1 rounded-full {{ $isDitolakCuti ? 'bg-rose-500' : ($s1 ? 'bg-emerald-500' : 'bg-slate-200') }}" title="1. Diajukan Pegawai"></div>
                                                <div class="h-1.5 flex-1 rounded-full {{ $isDitolakCuti ? 'bg-rose-500' : ($s2 ? 'bg-emerald-500' : ($s1 ? 'bg-teal-500 animate-pulse' : 'bg-slate-200')) }}" title="2. Admin Screening"></div>
                                                <div class="h-1.5 flex-1 rounded-full {{ $isDitolakCuti ? 'bg-rose-500' : ($s3 ? 'bg-emerald-500' : ($s2 ? 'bg-teal-500 animate-pulse' : 'bg-slate-200')) }}" title="3. Paraf Pokja"></div>
                                                <div class="h-1.5 flex-1 rounded-full {{ $isDitolakCuti ? 'bg-rose-500' : ($s4 ? 'bg-emerald-500' : ($s3 ? 'bg-teal-500 animate-pulse' : 'bg-slate-200')) }}" title="4. Atasan Langsung"></div>
                                                <div class="h-1.5 flex-1 rounded-full {{ $isDitolakCuti ? 'bg-rose-500' : ($s5 ? 'bg-emerald-500' : ($s4 ? 'bg-teal-500 animate-pulse' : 'bg-slate-200')) }}" title="5. TTE PYBMC"></div>
                                            </div>

                                            {{-- Step Labels --}}
                                            <div class="flex items-center gap-1 text-[10px] text-slate-500">
                                                <span class="{{ $s2 ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">Admin {{ $s2 ? '✓' : '⏳' }}</span>
                                                <span class="text-slate-300">›</span>
                                                <span class="{{ $s3 ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">Pokja {{ $s3 ? '✓' : '⏳' }}</span>
                                                <span class="text-slate-300">›</span>
                                                <span class="{{ $s4 ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">Atasan {{ $s4 ? '✓' : '⏳' }}</span>
                                                <span class="text-slate-300">›</span>
                                                <span class="{{ $s5 ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">PYBMC {{ $s5 ? '✓' : '⏳' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('pengajuan-cuti.show', $row->id) }}"
                                               class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition" title="Lihat Detail & Persetujuan">
                                                🔍
                                            </a>
                                            <a href="{{ route('pengajuan-cuti.cetak-pdf', $row->id) }}" target="_blank"
                                               class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded transition" title="Cetak Formulir Cuti BKN">
                                                📄
                                            </a>
                                            @if(Auth::user()->hasRole('admin'))
                                                <form action="{{ route('pengajuan-cuti.destroy', $row->id) }}" method="POST"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data cuti ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition" title="Hapus">
                                                        🗑️
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isPegawaiOnly ? 7 : 8 }}" class="py-8 text-center text-gray-500 italic">
                                        Belum ada riwayat permohonan cuti.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($data->total() > 0)
                    <div class="p-4 border-t border-gray-100">
                        {{ $data->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
