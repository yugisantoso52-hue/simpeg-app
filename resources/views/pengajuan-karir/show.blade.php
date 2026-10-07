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
                            {{ $pengajuan->pegawai->nama_lengkap ?? $pengajuan->pegawai->nama }}
                        </h2>
                        <p class="text-xs text-slate-500 font-mono">NIP: {{ $pengajuan->pegawai->nip }} | {{ $pengajuan->pegawai->jabatan->nama_jabatan ?? '-' }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if($pengajuan->jenis_pengajuan === 'KP')
                            <a href="{{ route('reports.kp.pdf', $pengajuan->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-700 shadow-md transition">
                                <span>📄 Cetak Surat Usulan KP (PDF)</span>
                            </a>
                        @else
                            <a href="{{ route('reports.kgb.pdf', $pengajuan->pegawai_id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 text-white hover:bg-amber-700 shadow-md transition">
                                <span>📄 Cetak Surat Pemberitahuan KGB (PDF)</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Status Verifikasi Berjenjang (Stepper Paraf) --}}
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                        Status Alur Verifikasi & Penandatanganan Resmi:
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        {{-- 1. Ka Pokja Keu-Kepeg --}}
                        <div class="p-3 rounded-lg border {{ $pengajuan->paraf_kapokja_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">1. Ka Pokja Keu-Kepeg</span>
                                <span class="text-xs font-bold">{{ $pengajuan->paraf_kapokja_at ? '✓ Paraf' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Dolli Vita Zenitha, SE</div>
                            @if($pengajuan->paraf_kapokja_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $pengajuan->paraf_kapokja_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- 2. Kabag Umum --}}
                        <div class="p-3 rounded-lg border {{ $pengajuan->paraf_kabag_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">2. Kabag Umum</span>
                                <span class="text-xs font-bold">{{ $pengajuan->paraf_kabag_at ? '✓ Paraf' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Bakhtiar, S.Sos., M.Si</div>
                            @if($pengajuan->paraf_kabag_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $pengajuan->paraf_kabag_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- 3. Wadek II (Pejabat Penandatangan Resmi) --}}
                        <div class="p-3 rounded-lg border {{ $pengajuan->paraf_wd2_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">3. Wakil Dekan II (Penandatangan)</span>
                                <span class="text-xs font-bold">{{ $pengajuan->paraf_wd2_at ? '✓ Ditandatangani' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Dr. Safri, M.Kep., Sp.Kep.M.B</div>
                            @if($pengajuan->paraf_wd2_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $pengajuan->paraf_wd2_at->format('d/m/Y H:i') }}
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
                            {{-- 1. Tombol Paraf Ka Pokja Keu-Kepeg --}}
                            @if(!$pengajuan->paraf_kapokja_at && ($isAdmin || $isKaPokja))
                                <button type="submit" name="tahap" value="paraf_kapokja" class="px-4 py-2 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white shadow-xs transition">
                                    ✓ Bubuhkan Paraf Ka Pokja Keu-Kepeg
                                </button>
                            @endif

                            {{-- 2. Tombol Paraf Kabag Umum --}}
                            @if(!$pengajuan->paraf_kabag_at && ($isAdmin || $isKabag))
                                <button type="submit" name="tahap" value="paraf_kabag" class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition">
                                    ✓ Bubuhkan Paraf Kabag Umum
                                </button>
                            @endif

                            {{-- 3. Tombol Tanda Tangan Wakil Dekan II --}}
                            @if(!$pengajuan->paraf_wd2_at && ($isAdmin || $isWd2))
                                <button type="submit" name="tahap" value="paraf_wd2" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition">
                                    🎖️ Setujui & Tanda Tangan Wakil Dekan II
                                </button>
                            @endif

                            {{-- Tombol Tolak / Kembalikan --}}
                            <button type="submit" name="tahap" value="tolak" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition">
                                ✕ Kembalikan / Perlu Perbaikan
                            </button>
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
