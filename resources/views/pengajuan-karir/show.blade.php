<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Detail & Tracking Pengajuan {{ $pengajuan->jenis_pengajuan }}"
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

            {{-- Kartu Ringkasan Status & Cetak Dokumen Resmi --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b pb-4 mb-4">
                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $pengajuan->jenis_pengajuan === 'KP' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            Usulan {{ $pengajuan->jenis_pengajuan === 'KP' ? 'Kenaikan Pangkat (PNS)' : 'Kenaikan Gaji Berkala' }}
                        </span>
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
                        @if($pengajuan->jenis_pengajuan === 'KP')
                            <a href="{{ route('reports.kp.pdf', $pengajuan->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold {{ $isSah ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300' }} transition">
                                <span>📄 Cetak Usulan KP {{ $isSah ? '(Resmi Sah)' : '(Konsep Draf)' }}</span>
                            </a>
                        @else
                            <a href="{{ route('reports.kgb.pdf', $pengajuan->pegawai_id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold {{ $isSah ? 'bg-amber-600 text-white hover:bg-amber-700 shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300' }} transition">
                                <span>📄 Cetak SK KGB {{ $isSah ? '(Resmi Sah)' : '(Konsep Draf)' }}</span>
                            </a>
                        @endif
                    </div>
                </div>

                @if($isAdmin)
                    {{-- PANEL KHUSUS ADMIN: INPUT / UPDATE NOMOR SURAT RESMI INSTANSI --}}
                    <div class="mb-5 p-4 rounded-xl border border-indigo-200 bg-indigo-50/40">
                        <div class="flex items-center justify-between pb-2 border-b border-indigo-200/60 mb-2.5">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-950 flex items-center gap-1.5">
                                <span>🏛️</span> Penomoran Surat Usulan {{ $pengajuan->jenis_pengajuan }} (Khusus Admin Kepegawaian)
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
                            if ($pengajuan->jenis_pengajuan === 'KP' && str_starts_with($nomorExisting, 'B/')) {
                                $nomorExisting = substr($nomorExisting, 2);
                            }
                            $tahunVal = $pengajuan->tahun_periode ?: ($pengajuan->created_at ? $pengajuan->created_at->format('Y') : date('Y'));
                        @endphp
                        <form action="{{ route('pengajuan-karir.update-nomor', $pengajuan->id) }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            @csrf
                            <div class="flex flex-1 rounded-lg shadow-xs">
                                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-indigo-300 bg-indigo-100/70 text-indigo-800 font-mono text-xs font-semibold">
                                    {{ $pengajuan->jenis_pengajuan === 'KP' ? 'Nomor: B/' : 'Nomor:' }}
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
                            <span>🏛️</span> Nomor Surat Resmi Usulan {{ $pengajuan->jenis_pengajuan }}:
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-indigo-100 text-indigo-800 border border-indigo-300">
                            {{ $pengajuan->nomor_surat }}
                        </span>
                    </div>
                @endif

                {{-- Status Verifikasi Berjenjang (Stepper Paraf) --}}
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                        Status Alur Verifikasi & Penandatanganan Resmi:
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        {{-- 1. Admin Kepegawaian --}}
                        <div class="p-3 rounded-lg border {{ $pengajuan->verifikasi_admin_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">1. Admin Kepegawaian</span>
                                <span class="text-xs font-bold">{{ $pengajuan->verifikasi_admin_at ? '✓ Selesai' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Screening & Penomoran</div>
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
                        <div class="p-3 rounded-lg border {{ ($pengajuan->paraf_wd2_at || $pengajuan->ttd_dekan_at) ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">4. TTE Pimpinan</span>
                                <span class="text-xs font-bold">{{ ($pengajuan->paraf_wd2_at || $pengajuan->ttd_dekan_at) ? '✓ Sah (TTE)' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Dr. Safri, M.Kep / Dekan</div>
                            @if($pengajuan->paraf_wd2_at || $pengajuan->ttd_dekan_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ ($pengajuan->paraf_wd2_at ?? $pengajuan->ttd_dekan_at)->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Detail Parameter Pengajuan --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    @if($pengajuan->jenis_pengajuan === 'KP')
                        <div class="p-3 rounded-lg bg-slate-50 border">
                            <span class="text-slate-400 block font-medium">Periode Usulan KP:</span>
                            <strong class="text-slate-800 text-sm">{{ $pengajuan->periode_kp }} {{ $pengajuan->tahun_periode }}</strong>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border">
                            <span class="text-slate-400 block font-medium">Kenaikan Golongan:</span>
                            <strong class="text-slate-800 text-sm">
                                {{ $pengajuan->golonganLama->nama_golongan ?? '-' }} ➔ {{ $pengajuan->golonganTujuan->nama_golongan ?? '-' }} ({{ $pengajuan->golonganTujuan->nama_pangkat ?? '-' }})
                            </strong>
                        </div>
                    @else
                        <div class="p-3 rounded-lg bg-slate-50 border">
                            <span class="text-slate-400 block font-medium">TMT KGB Baru:</span>
                            <strong class="text-slate-800 text-sm">{{ $pengajuan->tmt_baru ? \Carbon\Carbon::parse($pengajuan->tmt_baru)->translatedFormat('d F Y') : '-' }}</strong>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border">
                            <span class="text-slate-400 block font-medium">Estimasi Gaji Pokok Baru (PP 5/2024 / Perpres 11/2024):</span>
                            <strong class="text-emerald-700 text-sm font-mono">Rp {{ number_format($pengajuan->gaji_pokok_baru ?? 4390700, 0, ',', '.') }}</strong>
                        </div>
                    @endif
                </div>

                {{-- DOKUMEN ADMINISTRASI PERSYARATAN --}}
                <div class="mt-6 border-t pt-4">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <span>📎</span> Berkas Administrasi Persyaratan (Pemeriksaan Ka Pokja & Kabag)
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                        {{-- SK Pangkat Terakhir --}}
                        <div class="p-3 rounded-xl border {{ ($pengajuan->file_sk_pangkat_terakhir || $pengajuan->file_sk_terakhir) ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                            <div class="font-semibold text-slate-700">1. SK Pangkat Terakhir</div>
                            @if($pengajuan->file_sk_pangkat_terakhir || $pengajuan->file_sk_terakhir)
                                <a href="{{ asset('storage/' . ($pengajuan->file_sk_pangkat_terakhir ?? $pengajuan->file_sk_terakhir)) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                    <span>📥</span> Buka Dokumen
                                </a>
                            @else
                                <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                            @endif
                        </div>

                        {{-- SK KGB Terakhir (Khusus KGB) --}}
                        @if($pengajuan->jenis_pengajuan === 'KGB')
                            <div class="p-3 rounded-xl border {{ $pengajuan->file_sk_kgb_terakhir ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">2. SK KGB Terakhir</div>
                                @if($pengajuan->file_sk_kgb_terakhir)
                                    <a href="{{ asset('storage/' . $pengajuan->file_sk_kgb_terakhir) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                                @endif
                            </div>
                        @endif

                        {{-- SKP N-1 --}}
                        <div class="p-3 rounded-xl border {{ $pengajuan->file_skp_1 ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                            <div class="font-semibold text-slate-700">{{ $pengajuan->jenis_pengajuan === 'KGB' ? '3' : '2' }}. SKP Tahun (N-1)</div>
                            @if($pengajuan->file_skp_1)
                                <a href="{{ asset('storage/' . $pengajuan->file_skp_1) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                    <span>📥</span> Buka Dokumen
                                </a>
                            @else
                                <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                            @endif
                        </div>

                        {{-- SKP N-2 --}}
                        <div class="p-3 rounded-xl border {{ $pengajuan->file_skp_2 ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                            <div class="font-semibold text-slate-700">{{ $pengajuan->jenis_pengajuan === 'KGB' ? '4' : '3' }}. SKP 2 Tahun Lalu (N-2)</div>
                            @if($pengajuan->file_skp_2)
                                <a href="{{ asset('storage/' . $pengajuan->file_skp_2) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                    <span>📥</span> Buka Dokumen
                                </a>
                            @else
                                <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                            @endif
                        </div>

                        {{-- KARPEG (Khusus KP) --}}
                        @if($pengajuan->jenis_pengajuan === 'KP')
                            <div class="p-3 rounded-xl border {{ $pengajuan->file_karpeg ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-100 text-slate-400' }}">
                                <div class="font-semibold text-slate-700">4. KARPEG / Identitas ASN</div>
                                @if($pengajuan->file_karpeg)
                                    <a href="{{ asset('storage/' . $pengajuan->file_karpeg) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                @else
                                    <span class="text-[11px] text-rose-500 italic mt-2 block">Belum diunggah</span>
                                @endif
                            </div>

                            @if($pengajuan->file_pak)
                                <div class="p-3 rounded-xl border bg-slate-50 border-slate-200">
                                    <div class="font-semibold text-slate-700">5. Penetapan Angka Kredit (PAK)</div>
                                    <a href="{{ asset('storage/' . $pengajuan->file_pak) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                        <span>📥</span> Buka Dokumen
                                    </a>
                                </div>
                            @endif
                        @endif

                        {{-- Dokumen Pendukung --}}
                        @if($pengajuan->file_pendukung)
                            <div class="p-3 rounded-xl border bg-slate-50 border-slate-200">
                                <div class="font-semibold text-slate-700">Dokumen Pendukung Lainnya</div>
                                <a href="{{ asset('storage/' . $pengajuan->file_pendukung) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 mt-2">
                                    <span>📥</span> Buka Dokumen
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Catatan Verifikator Bila Ada --}}
                @if($pengajuan->catatan_verifikator)
                    <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900">
                        <strong>Catatan Verifikator / Pimpinan:</strong> {{ $pengajuan->catatan_verifikator }}
                    </div>
                @endif
            </div>

            {{-- PANEL AKSI KHUSUS PIMPINAN & KEPEGAWAIAN (APPROVAL & PARAF) --}}
            @if($isExecutive)
                <div class="rounded-2xl border border-indigo-200 bg-white p-6 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <span>✍️</span> Tindakan Verifikasi & Pembubuhan Paraf
                    </h3>

                    <form action="{{ route('pengajuan-karir.verifikasi', $pengajuan->id) }}" method="POST" class="space-y-4">
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
                                    ✒ 2. Periksa & Paraf Ka Pokja
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
                                    ✒ 3. Telaah Naskah & Paraf Kabag
                                </button>
                            @else
                                <span class="px-3 py-2 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-slate-200 select-none">
                                    ⏳ 3. Menunggu Paraf Kabag
                                </span>
                            @endif

                            {{-- 4. Tombol Tanda Tangan Wakil Dekan II --}}
                            @if($pengajuan->paraf_wd2_at || $pengajuan->status === 'disetujui_wd2')
                                <span class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5">
                                    ✓ 4. Sah (TTE WD2)
                                </span>
                            @elseif(!$pengajuan->paraf_kabag_at)
                                <span class="px-3 py-2 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 select-none opacity-60" title="🔒 Terkunci: Menunggu Paraf Kabag Umum">
                                    🔒 4. TTE WD2 (Terkunci)
                                </span>
                            @elseif($isWd2 || $isDekan)
                                <button type="submit" name="tahap" value="paraf_wd2" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition">
                                    ✍ 4. Setujui & TTE Wakil Dekan II
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
                                    || ($pengajuan->paraf_kabag_at && !$pengajuan->paraf_wd2_at && ($isWd2 || $isDekan));
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
                <a href="{{ route('pengajuan-karir.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                    ← Kembali ke Daftar Pengajuan
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
