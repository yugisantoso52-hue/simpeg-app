<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Detail & Tracking Pengajuan {{ $pengajuan->jenis_pengajuan === 'SATYALANCANA' ? 'Satyalancana Karya Satya' : $pengajuan->jenis_pengajuan }}"
            subtitle="Lembar Monitoring Paraf Koordinasi Administrasi dan Persetujuan Wakil Dekan II"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 flex items-center justify-between shadow-xs">
                    <span class="font-medium text-green-800 text-sm">✅ {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 font-bold">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 flex items-center justify-between shadow-xs">
                    <span class="font-medium text-rose-800 text-sm">⚠️ {{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900 font-bold">✕</button>
                </div>
            @endif

            {{-- Kartu Ringkasan Status & Cetak Dokumen Resmi --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b pb-4 mb-4">
                    <div>
                        @if($pengajuan->jenis_pengajuan === 'SATYALANCANA')
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-100 text-indigo-900 border border-indigo-200">
                                🏆 Satyalancana Karya Satya ({{ $pengajuan->kategori }})
                            </span>
                        @elseif($pengajuan->jenis_pengajuan === 'KP')
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                🎖️ Kenaikan Pangkat PNS (BKN No. 4/2023)
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                                💰 Kenaikan Gaji Berkala (PP 5/2024 &amp; Perpres 11/2024)
                            </span>
                        @endif

                        <h2 class="text-xl font-bold text-slate-900 mt-2">
                            {{ $pengajuan->pegawai?->nama_lengkap ?? $pengajuan->pegawai?->nama ?? '-' }}
                        </h2>
                        <p class="text-xs text-slate-500 font-mono">NIP: {{ $pengajuan->pegawai?->nip ?? '-' }} | {{ $pengajuan->pegawai?->jabatan?->nama_jabatan ?? '-' }}</p>
                        <div class="mt-1">
                            @if($pengajuan->nomor_surat)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                    No. Surat: {{ $pengajuan->nomor_surat }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                    No. Surat: [Ditetapkan oleh Admin]
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @php
                            $isSah = in_array($pengajuan->status, ['disetujui_wd2', 'disetujui_dekan', 'selesai']);
                        @endphp
                        @if($pengajuan->jenis_pengajuan === 'SATYALANCANA')
                            <a href="{{ route('satyalancana.cetak-pdf', $pengajuan->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold {{ $isSah ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300' }} transition">
                                <span>📄 Cetak Surat Usulan {{ $isSah ? '(Resmi Sah)' : '(Konsep Draf)' }}</span>
                            </a>
                            <a href="{{ route('satyalancana.cetak-drh', $pengajuan->pegawai_id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition shadow-xs">
                                <span>📄 Cetak DRH 12 Poin</span>
                            </a>
                        @elseif($pengajuan->jenis_pengajuan === 'KP')
                            <a href="{{ route('reports.kp.pdf', $pengajuan->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold {{ $isSah ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300' }} transition">
                                <span>📄 Cetak Usulan KP {{ $isSah ? '(Resmi Sah)' : '(Konsep Draf)' }}</span>
                            </a>
                        @else
                            <a href="{{ route('reports.kgb.pdf', $pengajuan->pegawai_id ?? $pengajuan->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold {{ $isSah ? 'bg-amber-600 text-white hover:bg-amber-700 shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300' }} transition">
                                <span>📄 Cetak SK KGB {{ $isSah ? '(Resmi Sah)' : '(Konsep Draf)' }}</span>
                            </a>
                        @endif

                        {{-- Tombol Batal untuk Pemohon --}}
                        @php
                            $isOwner = (int)Auth::user()->pegawai_id === (int)$pengajuan->pegawai_id;
                            $cancelRoute = $pengajuan->jenis_pengajuan === 'SATYALANCANA' ? route('satyalancana.cancel', $pengajuan->id) : route('pengajuan-karir.cancel', $pengajuan->id);
                            $destroyRoute = $pengajuan->jenis_pengajuan === 'SATYALANCANA' ? route('satyalancana.destroy', $pengajuan->id) : route('pengajuan-karir.destroy', $pengajuan->id);
                        @endphp

                        @if($isOwner && !in_array($pengajuan->status, ['disetujui_wd2', 'disetujui_dekan', 'selesai', 'dibatalkan']))
                            <form action="{{ $cancelRoute }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?');" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-300 hover:bg-rose-100 transition shadow-xs">
                                    <span>✕</span> Batalkan Usulan
                                </button>
                            </form>
                        @endif

                        {{-- Tombol Hapus Permanen oleh Admin (Reset Uji Coba) --}}
                        @if($isAdmin ?? false)
                            <form action="{{ $destroyRoute }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen data pengajuan ini? (Aksi ini tidak dapat dibatalkan)');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl text-xs font-bold bg-red-600 text-white hover:bg-red-700 transition shadow-xs">
                                    <span>🗑️</span> Hapus Berkas (Admin)
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($isAdmin)
                    {{-- PANEL KHUSUS ADMIN: INPUT / UPDATE NOMOR SURAT RESMI INSTANSI --}}
                    <div class="mb-5 p-4 rounded-xl border border-indigo-200 bg-indigo-50/40">
                        <div class="flex items-center justify-between pb-2 border-b border-indigo-200/60 mb-2.5">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-950 flex items-center gap-1.5">
                                <span>🏛️</span> Penomoran Surat Usulan {{ $pengajuan->jenis_pengajuan === 'SATYALANCANA' ? 'Satyalancana' : $pengajuan->jenis_pengajuan }} (Khusus Admin Kepegawaian)
                            </h4>
                            @if($pengajuan->nomor_surat)
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-indigo-100 text-indigo-800 border border-indigo-300">
                                    {{ $pengajuan->nomor_surat }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    Belum Bernomor Resmi
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-indigo-900/80 mb-3 leading-relaxed">
                            Berdasarkan SOP Tata Naskah Dinas, penomoran naskah dinas resmi diisi dan dicatat oleh Admin Kepegawaian.
                        </p>
                        @php
                            $nomorExisting = $pengajuan->nomor_surat ? explode('/UN19', $pengajuan->nomor_surat)[0] : '';
                            if (($pengajuan->jenis_pengajuan === 'KP' || $pengajuan->jenis_pengajuan === 'SATYALANCANA') && str_starts_with($nomorExisting, 'B/')) {
                                $nomorExisting = substr($nomorExisting, 2);
                            }
                            $tahunVal = $pengajuan->tahun_usulan ?? $pengajuan->tahun_periode ?: ($pengajuan->created_at ? $pengajuan->created_at->format('Y') : date('Y'));
                            $updateNomorRoute = $pengajuan->jenis_pengajuan === 'SATYALANCANA' ? route('satyalancana.update-nomor', $pengajuan->id) : route('pengajuan-karir.update-nomor', $pengajuan->id);
                        @endphp
                        <form action="{{ $updateNomorRoute }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            @csrf
                            <div class="flex flex-1 rounded-lg shadow-xs">
                                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-indigo-300 bg-indigo-100/70 text-indigo-800 font-mono text-xs font-semibold">
                                    {{ in_array($pengajuan->jenis_pengajuan, ['KP', 'SATYALANCANA']) ? 'Nomor: B/' : 'Nomor:' }}
                                </span>
                                <input type="text" name="nomor_surat_input" 
                                       value="{{ $nomorExisting }}"
                                       placeholder="Ketik nomor urut surat..."
                                       class="flex-1 min-w-0 block w-full px-3 py-1.5 border border-indigo-300 text-sm font-mono font-bold text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-indigo-300 bg-indigo-100/70 text-indigo-800 font-mono text-xs font-semibold">
                                    /UN19.5.1.1.10/KP/{{ $tahunVal }}
                                </span>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center justify-center gap-1.5 whitespace-nowrap">
                                <span>💾</span> Simpan Nomor Surat
                            </button>
                        </form>
                    </div>
                @elseif($pengajuan->nomor_surat)
                    <div class="mb-5 p-3 rounded-xl border border-indigo-200 bg-indigo-50/30 flex items-center justify-between">
                        <span class="text-xs font-semibold text-indigo-900 flex items-center gap-1.5">
                            <span>🏛️</span> Nomor Surat Resmi Usulan {{ $pengajuan->jenis_pengajuan === 'SATYALANCANA' ? 'Satyalancana' : $pengajuan->jenis_pengajuan }}:
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-indigo-100 text-indigo-800 border border-indigo-300">
                            {{ $pengajuan->nomor_surat }}
                        </span>
                    </div>
                @endif

                {{-- Status Verifikasi Berjenjang (Stepper Paraf) --}}
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                        Status Alur Verifikasi &amp; Penandatanganan Resmi:
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        {{-- 1. Admin Kepegawaian --}}
                        <div class="p-3 rounded-lg border {{ $pengajuan->verifikasi_admin_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">1. Admin Kepegawaian</span>
                                <span class="text-xs font-bold">{{ $pengajuan->verifikasi_admin_at ? '✓ Selesai' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Screening &amp; Penomoran</div>
                            @if($pengajuan->verifikasi_admin_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $pengajuan->verifikasi_admin_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- 2. Ka Pokja Keu-Kepeg --}}
                        <div class="p-3 rounded-lg border {{ $pengajuan->paraf_kapokja_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">2. Ka Pokja Keu-Kepeg</span>
                                <span class="text-xs font-bold">{{ $pengajuan->paraf_kapokja_at ? '✓ Paraf' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Dolli Vita Zenitha, SE</div>
                            @if($pengajuan->paraf_kapokja_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $pengajuan->paraf_kapokja_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- 3. Kabag Umum --}}
                        <div class="p-3 rounded-lg border {{ $pengajuan->paraf_kabag_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">3. Kabag Umum</span>
                                <span class="text-xs font-bold">{{ $pengajuan->paraf_kabag_at ? '✓ Paraf' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Bakhtiar, S.Sos., M.Si</div>
                            @if($pengajuan->paraf_kabag_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $pengajuan->paraf_kabag_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- 4. Wadek II / Dekan (Pejabat Penandatangan Resmi) --}}
                        @php
                            $isSahTte = $pengajuan->paraf_wd2_at || $pengajuan->ttd_wd2_at || $pengajuan->ttd_dekan_at || $pengajuan->status === 'disetujui_wd2';
                            $waktuTte = $pengajuan->paraf_wd2_at ?? $pengajuan->ttd_wd2_at ?? $pengajuan->ttd_dekan_at;
                        @endphp
                        <div class="p-3 rounded-lg border {{ $isSahTte ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">4. TTE Pimpinan</span>
                                <span class="text-xs font-bold">{{ $isSahTte ? '✓ Sah (TTE)' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Dr. Safri, M.Kep / WD II</div>
                            @if($waktuTte)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $waktuTte->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Detail Parameter Pengajuan --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    @if($pengajuan->jenis_pengajuan === 'SATYALANCANA')
                        <div class="p-3 rounded-lg bg-slate-50 border">
                            <span class="text-slate-400 block font-medium">Kategori Penghargaan:</span>
                            <strong class="text-indigo-900 text-sm">🏆 Satyalancana Karya Satya {{ $pengajuan->kategori }}</strong>
                            <span class="text-slate-500 text-[11px] block mt-0.5">Dasar: UU No. 20/2009 &amp; PP No. 35/2010</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border">
                            <span class="text-slate-400 block font-medium">Periode &amp; Tahun Usulan:</span>
                            <strong class="text-slate-800 text-sm">{{ $pengajuan->periode_usulan ?? 'Periode Mei ' . (date('Y')+1) }} (Tahun {{ $pengajuan->tahun_usulan }})</strong>
                            <span class="text-slate-500 text-[11px] block mt-0.5">Diusulkan resmi ke Rektorat UNRI &amp; Setmilpres BKN</span>
                        </div>
                    @elseif($pengajuan->jenis_pengajuan === 'KP')
                        <div class="p-3 rounded-lg bg-slate-50 border">
                            <span class="text-slate-400 block font-medium">Periode Usulan KP:</span>
                            <strong class="text-slate-800 text-sm">{{ $pengajuan->periode_kp }} {{ $pengajuan->tahun_periode }}</strong>
                            <span class="text-slate-500 text-[11px] block mt-0.5">Siklus 6 Periode BKN (PerBKN No. 4/2023)</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border">
                            <span class="text-slate-400 block font-medium">Kenaikan Golongan:</span>
                            <strong class="text-slate-800 text-sm">
                                {{ $pengajuan->golonganLama->nama_golongan ?? '-' }} ➔ {{ $pengajuan->golonganTujuan->nama_golongan ?? '-' }} ({{ $pengajuan->golonganTujuan->nama_pangkat ?? '-' }})
                            </strong>
                            <span class="text-slate-500 text-[11px] block mt-0.5">PermenPAN-RB No. 1/2023</span>
                        </div>
                    @else
                        <div class="p-3 rounded-lg bg-slate-50 border">
                            <span class="text-slate-400 block font-medium">TMT KGB Baru:</span>
                            <strong class="text-slate-800 text-sm">{{ $pengajuan->tmt_baru ? \Carbon\Carbon::parse($pengajuan->tmt_baru)->translatedFormat('d F Y') : '-' }}</strong>
                            <span class="text-slate-500 text-[11px] block mt-0.5">Masa Kerja Golongan: {{ $pengajuan->mkg_tahun ?? 0 }} Thn {{ $pengajuan->mkg_bulan ?? 0 }} Bln</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border">
                            <span class="text-slate-400 block font-medium">Estimasi Gaji Pokok Baru:</span>
                            <strong class="text-emerald-700 text-sm font-mono">Rp {{ number_format($pengajuan->gaji_pokok_baru ?? 4390700, 0, ',', '.') }}</strong>
                            <span class="text-slate-500 text-[11px] block mt-0.5">Dasar: PP 5/2024 / Perpres 11/2024</span>
                        </div>
                    @endif
                </div>

                {{-- DOKUMEN ADMINISTRASI PERSYARATAN LENGKAP SESUAI KETENTUAN PERUNDANG-UNDANGAN --}}
                <div class="mt-6 border-t pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <span>📎</span> Berkas Administrasi Persyaratan (Pemeriksaan Ka Pokja &amp; Kabag)
                        </h4>
                        <span class="text-[11px] text-slate-500 font-mono">
                            {{ $pengajuan->jenis_pengajuan === 'SATYALANCANA' ? 'UU 20/2009 & PP 35/2010' : ($pengajuan->jenis_pengajuan === 'KP' ? 'PerBKN 4/2023 & PermenPAN-RB 1/2023' : 'PP 5/2024 & PermenPAN-RB 7/2023') }}
                        </span>
                    </div>

                    @if($pengajuan->jenis_pengajuan === 'SATYALANCANA')
                        {{-- PERSYARATAN RESMI SATYALANCANA KARYA SATYA --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                            {{-- 1. Formulir DRH 12 Poin --}}
                            <div class="p-3 rounded-xl border bg-blue-50/60 border-blue-200">
                                <div class="font-semibold text-slate-800">1. DRH 12 Poin Setmilpres / BKN</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Daftar Riwayat Hidup resmi terverifikasi</span>
                                <a href="{{ route('satyalancana.cetak-drh', $pengajuan->pegawai_id) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 hover:text-blue-900 mt-2">
                                    <span>📥</span> Buka DRH 12 Poin
                                </a>
                            </div>

                            {{-- 2. SK CPNS & SK PNS --}}
                            @php
                                $hasCpns = !empty($pengajuan->file_sk_cpns) || !empty($pengajuan->pegawai?->tmt_sk_pertama);
                            @endphp
                            <div class="p-3 rounded-xl border {{ $hasCpns ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">2. SK Pengangkatan CPNS &amp; PNS</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Bukti TMT awal masa pengabdian ASN</span>
                                @if(!empty($pengajuan->file_sk_cpns))
                                    <a href="{{ asset('storage/' . $pengajuan->file_sk_cpns) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-emerald-600 font-semibold mt-2 block">✓ Tersinkron di Riwayat PNS</span>
                                @endif
                            </div>

                            {{-- 3. SK Pangkat Terakhir --}}
                            @php
                                $hasPangkat = !empty($pengajuan->file_sk_pangkat_terakhir) || !empty($pengajuan->pegawai?->golongan_id);
                            @endphp
                            <div class="p-3 rounded-xl border {{ $hasPangkat ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">3. SK Kenaikan Pangkat Terakhir</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Golongan: {{ $pengajuan->pegawai?->golongan?->nama_golongan ?? '-' }}</span>
                                @if(!empty($pengajuan->file_sk_pangkat_terakhir))
                                    <a href="{{ asset('storage/' . $pengajuan->file_sk_pangkat_terakhir) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-emerald-600 font-semibold mt-2 block">✓ Tersinkron di Riwayat Kepangkatan</span>
                                @endif
                            </div>

                            {{-- 4. SK Jabatan Terakhir --}}
                            @php
                                $hasJabatan = !empty($pengajuan->file_sk_jabatan_terakhir) || !empty($pengajuan->pegawai?->jabatan_id);
                            @endphp
                            <div class="p-3 rounded-xl border {{ $hasJabatan ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">4. SK Jabatan Terakhir</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">{{ $pengajuan->pegawai?->jabatan?->nama_jabatan ?? '-' }}</span>
                                @if(!empty($pengajuan->file_sk_jabatan_terakhir))
                                    <a href="{{ asset('storage/' . $pengajuan->file_sk_jabatan_terakhir) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-emerald-600 font-semibold mt-2 block">✓ Tersinkron di Riwayat Jabatan</span>
                                @endif
                            </div>

                            {{-- 5. SKP 2 Tahun Terakhir --}}
                            @php
                                $skpCount = $pengajuan->pegawai?->riwayatSkp?->count() ?? 0;
                            @endphp
                            <div class="p-3 rounded-xl border {{ (!empty($pengajuan->file_skp_2_tahun) || $skpCount >= 2) ? 'bg-slate-50 border-slate-200' : 'bg-amber-50/60 border-amber-200 text-slate-600' }}">
                                <div class="font-semibold text-slate-700">5. SKP 2 Tahun Terakhir</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Predikat kinerja berstatus 'Baik'</span>
                                @if(!empty($pengajuan->file_skp_2_tahun))
                                    <a href="{{ asset('storage/' . $pengajuan->file_skp_2_tahun) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @elseif($skpCount >= 2)
                                    <span class="text-[11px] text-emerald-600 font-semibold mt-2 block">✓ Terverifikasi di Riwayat Kinerja</span>
                                @else
                                    <span class="text-[11px] text-amber-600 italic mt-2 block">Lampirkan bukti SKP N-1 &amp; N-2</span>
                                @endif
                            </div>

                            {{-- 6. Surat Bebas Hukdis & Pidana --}}
                            <div class="p-3 rounded-xl border {{ !empty($pengajuan->file_surat_pernyataan_tidak_hukdis) ? 'bg-slate-50 border-slate-200' : 'bg-slate-50/70 border-slate-200' }}">
                                <div class="font-semibold text-slate-700">6. Surat Pernyataan Bebas Hukdis</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Tidak pernah dipidana &amp; tidak dijatuhi hukuman disiplin</span>
                                @if(!empty($pengajuan->file_surat_pernyataan_tidak_hukdis))
                                    <a href="{{ asset('storage/' . $pengajuan->file_surat_pernyataan_tidak_hukdis) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-500 italic mt-2 block">Pernyataan otomatis dalam DRH</span>
                                @endif
                            </div>

                            {{-- 7. Piagam Satyalancana Sebelumnya --}}
                            @if(in_array($pengajuan->kategori, ['20 Tahun', '30 Tahun']))
                                <div class="p-3 rounded-xl border bg-amber-50/40 border-amber-200">
                                    <div class="font-semibold text-slate-700">7. Piagam Satyalancana Sebelumnya</div>
                                    <span class="text-[10px] text-slate-500 block mt-0.5">Wajib untuk pengusulan kategori {{ $pengajuan->kategori }}</span>
                                    <span class="text-[11px] text-amber-800 font-semibold mt-2 block">✓ Verifikasi arsip riwayat penghargaan</span>
                                </div>
                            @endif
                        </div>

                    @elseif($pengajuan->jenis_pengajuan === 'KP')
                        {{-- PERSYARATAN RESMI KENAIKAN PANGKAT PNS (PerBKN 4/2023 & PermenPAN-RB 1/2023) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                            {{-- 1. SK Pangkat Terakhir --}}
                            <div class="p-3 rounded-xl border {{ ($pengajuan->file_sk_pangkat_terakhir || $pengajuan->file_sk_terakhir) ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">1. SK Kenaikan Pangkat Terakhir</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Legalisir fotokopi/salinan digital</span>
                                @if($pengajuan->file_sk_pangkat_terakhir || $pengajuan->file_sk_terakhir)
                                    <a href="{{ asset('storage/' . ($pengajuan->file_sk_pangkat_terakhir ?? $pengajuan->file_sk_terakhir)) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                                @endif
                            </div>

                            {{-- 2. SK Jabatan Fungsional Terakhir --}}
                            <div class="p-3 rounded-xl border {{ $pengajuan->file_sk_terakhir ? 'bg-slate-50 border-slate-200' : 'bg-slate-50/60 border-slate-200' }}">
                                <div class="font-semibold text-slate-700">2. SK Jabatan Fungsional (Dosen/PLP)</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Khusus jabatan fungsional</span>
                                @if($pengajuan->file_sk_terakhir)
                                    <a href="{{ asset('storage/' . $pengajuan->file_sk_terakhir) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-emerald-600 font-semibold mt-2 block">✓ Tersinkron di Riwayat Jabatan</span>
                                @endif
                            </div>

                            {{-- 3. PAK Konversi / Integrasi --}}
                            <div class="p-3 rounded-xl border {{ $pengajuan->file_pak ? 'bg-slate-50 border-slate-200' : 'bg-slate-50/60 border-slate-200' }}">
                                <div class="font-semibold text-slate-700">3. Penetapan Angka Kredit (PAK)</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">PAK Konversi PermenPAN-RB 1/2023</span>
                                @if($pengajuan->file_pak)
                                    <a href="{{ asset('storage/' . $pengajuan->file_pak) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-500 italic mt-2 block">Sesuai ketentuan jabatan pegawai</span>
                                @endif
                            </div>

                            {{-- 4. SKP Tahun N-1 --}}
                            <div class="p-3 rounded-xl border {{ $pengajuan->file_skp_1 ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">4. Penilaian Kinerja / SKP Tahun (N-1)</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Predikat minimal 'Baik'</span>
                                @if($pengajuan->file_skp_1)
                                    <a href="{{ asset('storage/' . $pengajuan->file_skp_1) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                                @endif
                            </div>

                            {{-- 5. SKP 2 Tahun Lalu (N-2) --}}
                            <div class="p-3 rounded-xl border {{ $pengajuan->file_skp_2 ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">5. Penilaian Kinerja / SKP 2 Tahun Lalu (N-2)</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Predikat minimal 'Baik'</span>
                                @if($pengajuan->file_skp_2)
                                    <a href="{{ asset('storage/' . $pengajuan->file_skp_2) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                                @endif
                            </div>

                            {{-- 6. KARPEG / KTA ASN --}}
                            <div class="p-3 rounded-xl border {{ $pengajuan->file_karpeg ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">6. KARPEG / Identitas ASN Elektronik</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Kartu Pegawai resmi BKN</span>
                                @if($pengajuan->file_karpeg)
                                    <a href="{{ asset('storage/' . $pengajuan->file_karpeg) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                                @endif
                            </div>

                            {{-- 7. Surat Pernyataan Bebas Hukdis --}}
                            <div class="p-3 rounded-xl border bg-slate-50/60 border-slate-200">
                                <div class="font-semibold text-slate-700">7. Surat Bebas Hukdis</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Tidak sedang dijatuhi hukdis sedang/berat</span>
                                <span class="text-[11px] text-emerald-600 font-semibold mt-2 block">✓ Verifikasi otomatis integritas ASN</span>
                            </div>

                            {{-- 8. Berkas Pendukung (Serdos / Ijazah) --}}
                            @if($pengajuan->file_pendukung)
                                <div class="p-3 rounded-xl border bg-slate-50 border-slate-200">
                                    <div class="font-semibold text-slate-700">8. Dokumen Pendukung Lainnya</div>
                                    <span class="text-[10px] text-slate-500 block mt-0.5">Serdos / Ijazah peningkatan</span>
                                    <a href="{{ asset('storage/' . $pengajuan->file_pendukung) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                </div>
                            @endif
                        </div>

                    @else
                        {{-- PERSYARATAN RESMI KENAIKAN GAJI BERKALA (PP 5/2024 & PermenPAN-RB 7/2023) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                            {{-- 1. SK Pangkat Terakhir --}}
                            <div class="p-3 rounded-xl border {{ ($pengajuan->file_sk_pangkat_terakhir || $pengajuan->file_sk_terakhir) ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">1. SK Pangkat Terakhir</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Dasar penetapan golongan &amp; ruang</span>
                                @if($pengajuan->file_sk_pangkat_terakhir || $pengajuan->file_sk_terakhir)
                                    <a href="{{ asset('storage/' . ($pengajuan->file_sk_pangkat_terakhir ?? $pengajuan->file_sk_terakhir)) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                                @endif
                            </div>

                            {{-- 2. SK KGB Terakhir --}}
                            <div class="p-3 rounded-xl border {{ $pengajuan->file_sk_kgb_terakhir ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">2. SK Kenaikan Gaji Berkala (KGB) Terakhir</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Acuan penetapan TMT 2 tahunan</span>
                                @if($pengajuan->file_sk_kgb_terakhir)
                                    <a href="{{ asset('storage/' . $pengajuan->file_sk_kgb_terakhir) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                                @endif
                            </div>

                            {{-- 3. SKP Tahun N-1 --}}
                            <div class="p-3 rounded-xl border {{ $pengajuan->file_skp_1 ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">3. SKP Tahun (N-1)</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Penilaian kinerja tahun berjalan</span>
                                @if($pengajuan->file_skp_1)
                                    <a href="{{ asset('storage/' . $pengajuan->file_skp_1) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                                @endif
                            </div>

                            {{-- 4. SKP 2 Tahun Lalu (N-2) --}}
                            <div class="p-3 rounded-xl border {{ $pengajuan->file_skp_2 ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">4. SKP 2 Tahun Lalu (N-2)</div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Penilaian kinerja tahun sebelumnya</span>
                                @if($pengajuan->file_skp_2)
                                    <a href="{{ asset('storage/' . $pengajuan->file_skp_2) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                                @endif
                            </div>

                            {{-- Dokumen Pendukung --}}
                            @if($pengajuan->file_pendukung)
                                <div class="p-3 rounded-xl border bg-slate-50 border-slate-200">
                                    <div class="font-semibold text-slate-700">5. Dokumen Pendukung Lainnya</div>
                                    <a href="{{ asset('storage/' . $pengajuan->file_pendukung) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Catatan Verifikator Bila Ada --}}
                @if($pengajuan->catatan_verifikator || $pengajuan->catatan)
                    <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900">
                        <strong>Catatan Pengusulan / Verifikator:</strong> {{ $pengajuan->catatan_verifikator ?? $pengajuan->catatan }}
                    </div>
                @endif
            </div>

            {{-- PANEL AKSI KHUSUS PIMPINAN & KEPEGAWAIAN (APPROVAL & PARAF) --}}
            @if($isExecutive)
                @php
                    $formVerifAction = $pengajuan->jenis_pengajuan === 'SATYALANCANA' 
                        ? route('satyalancana.verifikasi', $pengajuan->id) 
                        : route('pengajuan-karir.verifikasi', $pengajuan->id);
                @endphp
                <div class="rounded-2xl border border-indigo-200 bg-white p-6 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <span>✍️</span> Tindakan Verifikasi &amp; Pembubuhan Paraf ({{ $pengajuan->jenis_pengajuan === 'SATYALANCANA' ? 'Satyalancana' : $pengajuan->jenis_pengajuan }})
                    </h3>

                    <form action="{{ $formVerifAction }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Verifikasi / Disposisi (Opsional)</label>
                            <input type="text" name="catatan" placeholder="Contoh: Berkas telah lengkap dan sesuai syarat masa kerja" class="w-full rounded-lg border-slate-300 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-2">
                            {{-- 1. Tombol Admin Screening Berkas --}}
                            @if($pengajuan->verifikasi_admin_at)
                                <span class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5" title="Selesai pada {{ $pengajuan->verifikasi_admin_at->format('d/m/Y H:i') }}">
                                    ✓ 1. Admin Screening: Selesai
                                </span>
                            @elseif($isAdmin)
                                <button type="submit" name="tahap" value="verifikasi_admin" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-xs transition">
                                    ✓ 1. Admin Screening Berkas
                                </button>
                            @else
                                <span class="px-3 py-2 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-slate-200 select-none">
                                    ⏳ 1. Menunggu Admin Screening Berkas
                                </span>
                            @endif

                            {{-- 2. Tombol Paraf Ka Pokja Keu-Kepeg --}}
                            @if($pengajuan->paraf_kapokja_at)
                                <span class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5" title="Selesai pada {{ $pengajuan->paraf_kapokja_at->format('d/m/Y H:i') }}">
                                    ✓ 2. Paraf Ka Pokja: Selesai
                                </span>
                            @elseif(!$pengajuan->verifikasi_admin_at)
                                <span class="px-3 py-2 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 select-none opacity-60" title="🔒 Terkunci: Menunggu Admin Screening Berkas">
                                    🔒 2. Paraf Pokja (Terkunci)
                                </span>
                            @elseif($isKaPokja)
                                <button type="submit" name="tahap" value="paraf_kapokja" class="px-4 py-2 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white shadow-xs transition">
                                    ✒ 2. Periksa &amp; Paraf Ka Pokja
                                </button>
                            @else
                                <span class="px-3 py-2 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-slate-200 select-none">
                                    ⏳ 2. Menunggu Paraf Ka Pokja
                                </span>
                            @endif

                            {{-- 3. Tombol Paraf Kabag Umum --}}
                            @if($pengajuan->paraf_kabag_at)
                                <span class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5" title="Selesai pada {{ $pengajuan->paraf_kabag_at->format('d/m/Y H:i') }}">
                                    ✓ 3. Paraf Kabag: Selesai
                                </span>
                            @elseif(!$pengajuan->paraf_kapokja_at)
                                <span class="px-3 py-2 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 select-none opacity-60" title="🔒 Terkunci: Menunggu Paraf Ka Pokja">
                                    🔒 3. Paraf Kabag (Terkunci)
                                </span>
                            @elseif($isKabag)
                                <button type="submit" name="tahap" value="paraf_kabag" class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition">
                                    ✒ 3. Telaah Naskah &amp; Paraf Kabag
                                </button>
                            @else
                                <span class="px-3 py-2 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-slate-200 select-none">
                                    ⏳ 3. Menunggu Paraf Kabag
                                </span>
                            @endif

                            {{-- 4. Tombol Tanda Tangan Wakil Dekan II --}}
                            @php
                                $isApprovedWd2 = $pengajuan->paraf_wd2_at || $pengajuan->ttd_wd2_at || $pengajuan->status === 'disetujui_wd2';
                                $valTte = $pengajuan->jenis_pengajuan === 'SATYALANCANA' ? 'ttd_wd2' : 'paraf_wd2';
                            @endphp
                            @if($isApprovedWd2)
                                <span class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5">
                                    ✓ 4. Sah (TTE WD2)
                                </span>
                            @elseif(!$pengajuan->paraf_kabag_at)
                                <span class="px-3 py-2 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 select-none opacity-60" title="🔒 Terkunci: Menunggu Paraf Kabag Umum">
                                    🔒 4. TTE WD2 (Terkunci)
                                </span>
                            @elseif($isWd2 || $isDekan)
                                <button type="submit" name="tahap" value="{{ $valTte }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition">
                                    ✍ 4. Setujui &amp; TTE Wakil Dekan II
                                </button>
                            @else
                                <span class="px-3 py-2 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-slate-200 select-none">
                                    ⏳ 4. Menunggu TTE WD II
                                </span>
                            @endif

                            {{-- Tombol Tolak / Kembalikan --}}
                            @php
                                $canRejectCurrent = (!$pengajuan->verifikasi_admin_at && $isAdmin)
                                    || ($pengajuan->verifikasi_admin_at && !$pengajuan->paraf_kapokja_at && $isKaPokja)
                                    || ($pengajuan->paraf_kapokja_at && !$pengajuan->paraf_kabag_at && $isKabag)
                                    || ($pengajuan->paraf_kabag_at && !$isApprovedWd2 && ($isWd2 || $isDekan));
                            @endphp
                            @if($pengajuan->status !== 'disetujui_wd2' && $pengajuan->status !== 'ditolak' && $canRejectCurrent)
                                <button type="submit" name="tahap" value="tolak" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition">
                                    ✕ Kembalikan / Tolak
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            @endif

            <div class="flex justify-start">
                @php
                    $tabBack = $pengajuan->jenis_pengajuan === 'SATYALANCANA' ? 'satyalancana' : ($pengajuan->jenis_pengajuan === 'KP' ? 'kp' : 'kgb');
                @endphp
                <a href="{{ route('pengajuan-karir.index', ['tab' => $tabBack]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                    ← Kembali ke Pusat Verifikasi Layanan Karir
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
