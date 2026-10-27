{{-- Panel Status Kelayakan & Kelengkapan Berkas Kenaikan Gaji Berkala (KGB) --}}
@if($statusKgb)
<div class="space-y-5">
    {{-- Kartu Status Waktu & Periode KGB --}}
    <div class="rounded-2xl border {{ $statusKgb['can_submit'] ? 'border-amber-300 bg-amber-50/50' : 'border-slate-200 bg-white' }} p-6 shadow-sm">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $statusKgb['can_submit'] ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                        {{ $statusKgb['can_submit'] ? '✓ Siap Diajukan' : '🔒 Waktu Pengusulan Belum Masuk' }}
                    </span>
                    <span class="text-xs text-slate-500 font-mono">Dasar: PP No. 7/1977 jo PP No. 5/2024 &amp; PermenPAN-RB No. 7/2023</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span>💰</span> Status Pengusulan Kenaikan Gaji Berkala (KGB)
                </h3>
            </div>

            <div class="shrink-0">
                @if($statusKgb['can_submit'])
                    <a href="{{ route('pengajuan-karir.create', ['jenis' => 'KGB']) }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-slate-950 shadow-md shadow-amber-500/20 transition transform active:scale-95">
                        <span>⚡ Buat Formulir Usulan KGB</span>
                    </a>
                @else
                    <button type="button" disabled class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-slate-200 text-slate-500 border border-slate-300 cursor-not-allowed">
                        <span>🔒 Belum Memasuki Waktu Pengusulan</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Indikator Parameter Kelayakan KGB --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 my-4">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[11px] text-slate-500 block font-medium">Status Kepegawaian:</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @if($statusKgb['is_eligible_role'])
                        <span class="text-emerald-600 font-bold text-xs">✓ {{ $userPegawai->jenis_pegawai ?? 'ASN' }} (Berhak KGB 2 Tahun)</span>
                    @else
                        <span class="text-rose-600 font-bold text-xs">⚠️ {{ $userPegawai->jenis_pegawai ?? 'Bukan ASN' }}</span>
                    @endif
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[11px] text-slate-500 block font-medium">TMT KGB Berikutnya:</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @if($statusKgb['waktu_cukup'])
                        <span class="text-emerald-600 font-bold text-xs font-mono">✓ {{ $statusKgb['kgb_berikutnya'] ?? 'Telah Jatuh Tempo' }}</span>
                    @else
                        <span class="text-amber-700 font-bold text-xs font-mono">⏳ {{ $statusKgb['kgb_berikutnya'] ?? 'Belum 2 Tahun' }}</span>
                    @endif
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[11px] text-slate-500 block font-medium">Ketentuan Waktu Pengusulan:</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-slate-700 font-medium text-xs">Dibuka H-3 Bulan Sebelum TMT</span>
                </div>
            </div>
        </div>

        @if(!$statusKgb['can_submit'])
            <div class="rounded-xl bg-amber-50/80 border border-amber-300 p-4 text-xs text-amber-900 space-y-1.5">
                <div class="font-bold flex items-center gap-1.5 text-amber-950">
                    <span>ℹ️</span> Keterangan Regulasi Mengapa Belum Bisa Mengajukan KGB:
                </div>
                <ul class="list-disc list-inside space-y-1 text-[11px] text-amber-900 pl-1">
                    @foreach($statusKgb['alasan_kunci'] as $alasan)
                        <li>{{ $alasan }}</li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="rounded-xl bg-amber-50 border border-amber-300 p-3.5 text-xs text-amber-900 flex items-center gap-2">
                <span class="text-base shrink-0">✅</span>
                <span><strong>Selamat!</strong> Masa kerja KGB Anda telah jatuh tempo 2 tahun atau telah memasuki jendela H-3 bulan. Silakan kirimkan formulir usulan KGB Anda.</span>
            </div>
        @endif
    </div>

    {{-- Panel Kelengkapan Berkas Persyaratan KGB --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span>📎</span> Pemeriksaan &amp; Kelengkapan Berkas Persyaratan KGB
                </h4>
                <p class="text-xs text-slate-500 mt-0.5">
                    Meskipun waktu pengajuan belum dibuka, Anda dapat melengkapi berkas persyaratan yang kurang di bawah ini agar saat jatuh tempo tiba berkas Anda sudah siap 100%.
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 shrink-0">
                Skor Kelengkapan: {{ $statusKgb['syarat_berkas']['skor'] }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
            {{-- 1. SK Pangkat Terakhir / SK Pengangkatan --}}
            @php $skP = $statusKgb['syarat_berkas']['items'][0]; @endphp
            <div class="p-4 rounded-xl border {{ $skP['status'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/20' }} flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-bold text-slate-800">1. SK Pangkat / Pengangkatan</div>
                        <p class="text-[11px] text-slate-500">Salinan SK Pangkat terakhir atau SK Pengangkatan CPNS/PPPK</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $skP['status'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $skP['status'] ? '✓ Lengkap' : '⚠️ Belum Ada' }}
                    </span>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    @if($skP['status'] && $skP['file_url'])
                        <a href="{{ $skP['file_url'] }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">
                            👁️ Lihat Berkas
                        </a>
                    @endif
                    <button type="button" 
                            @click="$dispatch('open-upload-syarat', { title: 'SK Pangkat / SK Pengangkatan', field: 'file_sk_pangkat_terakhir', tab: 'kgb' })"
                            class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $skP['status'] ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-amber-600 text-white hover:bg-amber-700' }} transition">
                        <span>{{ $skP['status'] ? '✏️ Ganti File' : '📤 Unggah Berkas' }}</span>
                    </button>
                </div>
            </div>

            {{-- 2. SK KGB Terakhir --}}
            @php $skKgb = $statusKgb['syarat_berkas']['items'][1]; @endphp
            <div class="p-4 rounded-xl border {{ $skKgb['status'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/20' }} flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-bold text-slate-800">2. Surat KGB Terakhir</div>
                        <p class="text-[11px] text-slate-500">Surat pemberitahuan kenaikan gaji berkala sebelumnya</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $skKgb['status'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $skKgb['status'] ? '✓ Lengkap' : '⚠️ Belum Ada' }}
                    </span>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    @if($skKgb['status'] && $skKgb['file_url'])
                        <a href="{{ $skKgb['file_url'] }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">
                            👁️ Lihat Berkas
                        </a>
                    @endif
                    <button type="button" 
                            @click="$dispatch('open-upload-syarat', { title: 'SK KGB Terakhir', field: 'file_sk_kgb_terakhir', tab: 'kgb' })"
                            class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $skKgb['status'] ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-amber-600 text-white hover:bg-amber-700' }} transition">
                        <span>{{ $skKgb['status'] ? '✏️ Ganti File' : '📤 Unggah Berkas' }}</span>
                    </button>
                </div>
            </div>

            {{-- 3. Penilaian Kinerja (SKP) 2 Tahun Terakhir --}}
            @php $skp = $statusKgb['syarat_berkas']['items'][2]; @endphp
            <div class="p-4 rounded-xl border {{ $skp['status'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/20' }} flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-bold text-slate-800">3. Penilaian Kinerja (SKP)</div>
                        <p class="text-[11px] text-slate-500">Predikat kinerja minimal bernilai "Baik" 2 tahun terakhir</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $skp['status'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $skp['status'] ? '✓ Lengkap (' . $skp['count'] . ' Thn)' : '⚠️ Kurang (' . $skp['count'] . '/2 Thn)' }}
                    </span>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('riwayat-skp.index') }}" class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        <span>📊 Buka Modul SKP</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
