<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Detail & Tracking Pengajuan {{ $pengajuan->jenis_pengajuan }}"
            subtitle="Lembar Monitoring Paraf Koordinasi Administrasi dan Persetujuan Dekan"
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
                        Status Alur Verifikasi & Paraf Koordinasi:
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        {{-- 1. Kabag Umum --}}
                        <div class="p-3 rounded-lg border {{ $pengajuan->paraf_kabag_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">1. Kabag Umum</span>
                                <span class="text-xs font-bold">{{ $pengajuan->paraf_kabag_at ? '✓ Paraf' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Bakhtiar, S.Sos., M.Si</div>
                            @if($pengajuan->paraf_kabag_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $pengajuan->paraf_kabag_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- 2. Wadek II --}}
                        <div class="p-3 rounded-lg border {{ $pengajuan->paraf_wd2_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">2. Wakil Dekan II</span>
                                <span class="text-xs font-bold">{{ $pengajuan->paraf_wd2_at ? '✓ Paraf' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Dr. Safri, M.Kep., Sp.Kep.M.B</div>
                            @if($pengajuan->paraf_wd2_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $pengajuan->paraf_wd2_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- 3. Dekan --}}
                        <div class="p-3 rounded-lg border {{ $pengajuan->ttd_dekan_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">3. Dekan</span>
                                <span class="text-xs font-bold">{{ $pengajuan->ttd_dekan_at ? '✓ Disetujui' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Prof. Wan Nishfa Dewi, PhD</div>
                            @if($pengajuan->ttd_dekan_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $pengajuan->ttd_dekan_at->format('d/m/Y H:i') }}
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
                            {{-- Tombol Paraf Kabag --}}
                            @if(!$pengajuan->paraf_kabag_at)
                                <button type="submit" name="tahap" value="paraf_kabag" class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition">
                                    ✓ Bubuhkan Paraf Kabag Umum
                                </button>
                            @endif

                            {{-- Tombol Paraf WD II --}}
                            @if($pengajuan->paraf_kabag_at && !$pengajuan->paraf_wd2_at)
                                <button type="submit" name="tahap" value="paraf_wd2" class="px-4 py-2 rounded-xl text-xs font-bold bg-purple-600 hover:bg-purple-700 text-white shadow-xs transition">
                                    ✓ Bubuhkan Paraf Wakil Dekan II
                                </button>
                            @endif

                            {{-- Tombol TTD Dekan --}}
                            @if($pengajuan->paraf_wd2_at && !$pengajuan->ttd_dekan_at)
                                <button type="submit" name="tahap" value="ttd_dekan" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition">
                                    🎖️ Setujui & Tanda Tangan Dekan
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
