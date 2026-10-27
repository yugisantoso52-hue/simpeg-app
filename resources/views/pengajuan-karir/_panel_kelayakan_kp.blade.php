{{-- Panel Status Kelayakan & Kelengkapan Berkas Kenaikan Pangkat (KP) --}}
@if($statusKp)
<div class="space-y-5">
    {{-- Kartu Status Waktu & Periode KP --}}
    <div class="rounded-2xl border {{ $statusKp['can_submit'] ? 'border-emerald-300 bg-emerald-50/50' : 'border-amber-200 bg-white' }} p-6 shadow-sm">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $statusKp['can_submit'] ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                        {{ $statusKp['can_submit'] ? '✓ Siap Diajukan' : '🔒 Waktu / Periode Belum Masuk' }}
                    </span>
                    <span class="text-xs text-slate-500 font-mono">Dasar: Peraturan BKN No. 4/2023 &amp; UU ASN No. 20/2023</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span>🎖️</span> Status Pengusulan Kenaikan Pangkat (KP) PNS
                </h3>
            </div>

            <div class="shrink-0">
                @if($statusKp['can_submit'])
                    <a href="{{ route('pengajuan-karir.create', ['jenis' => 'KP']) }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 transition transform active:scale-95">
                        <span>🚀 Buat Formulir Usulan KP</span>
                    </a>
                @else
                    <button type="button" disabled class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-slate-200 text-slate-500 border border-slate-300 cursor-not-allowed">
                        <span>🔒 Belum Dapat Mengajukan Usulan</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Indikator Parameter Kelayakan --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 my-4">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[11px] text-slate-500 block font-medium">Status Kepegawaian:</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @if($statusKp['is_pns'])
                        <span class="text-emerald-600 font-bold text-xs">✓ PNS (Berhak KP Reguler)</span>
                    @else
                        <span class="text-amber-700 font-bold text-xs">⚠️ {{ $userPegawai->jenis_pegawai ?? 'Bukan PNS' }} (Khusus PNS)</span>
                    @endif
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[11px] text-slate-500 block font-medium">TMT KP Berikutnya:</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @if($statusKp['waktu_cukup'])
                        <span class="text-emerald-600 font-bold text-xs font-mono">✓ {{ $statusKp['kp_berikutnya'] ?? 'Telah Jatuh Tempo' }}</span>
                    @else
                        <span class="text-amber-700 font-bold text-xs font-mono">⏳ {{ $statusKp['kp_berikutnya'] ?? 'Belum 4 Tahun' }}</span>
                    @endif
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[11px] text-slate-500 block font-medium">Jendela Periode BKN Saat Ini:</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @if($statusKp['periode_bkn']['is_open'])
                        <span class="text-emerald-700 font-bold text-xs">✓ Periode {{ $statusKp['periode_bkn']['active_periode'] }} Dibuka</span>
                    @else
                        <span class="text-slate-600 font-semibold text-xs">🔒 Ditutup (Berikutnya: {{ $statusKp['periode_bkn']['active_periode'] }})</span>
                    @endif
                </div>
            </div>
        </div>

        @if(!$statusKp['can_submit'])
            <div class="rounded-xl bg-amber-50/80 border border-amber-300 p-4 text-xs text-amber-900 space-y-1.5">
                <div class="font-bold flex items-center gap-1.5 text-amber-950">
                    <span>ℹ️</span> Keterangan Regulasi Mengapa Belum Bisa Mengajukan:
                </div>
                <ul class="list-disc list-inside space-y-1 text-[11px] text-amber-900 pl-1">
                    @foreach($statusKp['alasan_kunci'] as $alasan)
                        <li>{{ $alasan }}</li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="rounded-xl bg-emerald-50 border border-emerald-300 p-3.5 text-xs text-emerald-900 flex items-center gap-2">
                <span class="text-base shrink-0">✅</span>
                <span><strong>Selamat!</strong> Anda telah memenuhi syarat masa kerja dan jendela pengusulan BKN sedang dibuka. Silakan isi formulir usulan KP dan pastikan dokumen pendukung telah lengkap.</span>
            </div>
        @endif
    </div>

    {{-- Panel Kelengkapan Berkas Persyaratan KP --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span>📎</span> Pemeriksaan &amp; Kelengkapan Berkas Persyaratan KP
                </h4>
                <p class="text-xs text-slate-500 mt-0.5">
                    Meskipun waktu pengajuan belum dibuka sesuai peraturan, Anda dapat melengkapi dan mencicil dokumen yang kurang di bawah ini agar saat periode dibuka berkas Anda sudah siap 100%.
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0">
                Skor Kelengkapan: {{ $statusKp['syarat_berkas']['skor'] }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 text-xs">
            {{-- 1. SK Pangkat Terakhir --}}
            @php $skP = $statusKp['syarat_berkas']['sk_pangkat']; @endphp
            <div class="p-4 rounded-xl border {{ $skP['status'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/20' }} flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-bold text-slate-800">1. SK Kenaikan Pangkat Terakhir</div>
                        <p class="text-[11px] text-slate-500">Salinan SK Pangkat terakhir / Pengangkatan CPNS/PNS</p>
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
                            @click="$dispatch('open-upload-syarat', { title: 'SK Pangkat Terakhir', field: 'file_sk_pangkat_terakhir', tab: 'kp' })"
                            class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $skP['status'] ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} transition">
                        <span>{{ $skP['status'] ? '✏️ Ganti File' : '📤 Unggah Berkas' }}</span>
                    </button>
                </div>
            </div>

            {{-- 2. SK KGB Terakhir --}}
            @php $skKgb = $statusKp['syarat_berkas']['sk_kgb']; @endphp
            <div class="p-4 rounded-xl border {{ $skKgb['status'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/20' }} flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-bold text-slate-800">2. Surat Pemberitahuan KGB Terakhir</div>
                        <p class="text-[11px] text-slate-500">Salinan SK Kenaikan Gaji Berkala terakhir</p>
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
                            @click="$dispatch('open-upload-syarat', { title: 'SK KGB Terakhir', field: 'file_sk_kgb_terakhir', tab: 'kp' })"
                            class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $skKgb['status'] ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} transition">
                        <span>{{ $skKgb['status'] ? '✏️ Ganti File' : '📤 Unggah Berkas' }}</span>
                    </button>
                </div>
            </div>

            {{-- 3. Penilaian Kinerja (SKP) 2 Tahun Terakhir --}}
            @php $skp = $statusKp['syarat_berkas']['skp_2_tahun']; @endphp
            <div class="p-4 rounded-xl border {{ $skp['status'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/20' }} flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-bold text-slate-800">3. Penilaian Kinerja (SKP) 2 Tahun Terakhir</div>
                        <p class="text-[11px] text-slate-500">Minimal 2 arsip SKP dengan predikat bernilai minimal "Baik"</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $skp['status'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $skp['status'] ? '✓ Lengkap (' . $skp['count'] . ' Thn)' : '⚠️ Kurang (' . $skp['count'] . '/2 Thn)' }}
                    </span>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('riwayat-skp.index') }}" class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        <span>📊 Buka Modul SKP &amp; Kinerja</span>
                    </a>
                </div>
            </div>

            {{-- 4. Fotocopy Karpeg --}}
            @php $karpeg = $statusKp['syarat_berkas']['karpeg']; @endphp
            <div class="p-4 rounded-xl border {{ $karpeg['status'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/20' }} flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-bold text-slate-800">4. Fotocopy KARPEG / Identitas ASN</div>
                        <p class="text-[11px] text-slate-500">Kartu Pegawai resmi BKN</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $karpeg['status'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $karpeg['status'] ? '✓ Lengkap' : '⚠️ Belum Ada' }}
                    </span>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    @if($karpeg['status'] && $karpeg['file_url'])
                        <a href="{{ $karpeg['file_url'] }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">
                            👁️ Lihat Karpeg
                        </a>
                    @endif
                    <button type="button" 
                            @click="$dispatch('open-upload-syarat', { title: 'Fotocopy Karpeg', field: 'file_karpeg', tab: 'kp' })"
                            class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $karpeg['status'] ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} transition">
                        <span>{{ $karpeg['status'] ? '✏️ Ganti File' : '📤 Unggah Karpeg' }}</span>
                    </button>
                </div>
            </div>

            {{-- 5. Dokumen PAK (Bagi Dosen & PLP) --}}
            @if($statusKp['syarat_berkas']['is_wajib_pak'])
                @php $pak = $statusKp['syarat_berkas']['pak']; @endphp
                <div class="md:col-span-2 p-4 rounded-xl border {{ $pak['status'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/20' }} flex flex-col justify-between gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-slate-800">5. Penetapan Angka Kredit (PAK) / Konversi SISTER BKN</div>
                            <p class="text-[11px] text-slate-500">Wajib bagi Jabatan Fungsional Dosen &amp; Pranata Laboratorium Pendidikan (PLP)</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $pak['status'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $pak['status'] ? '✓ Lengkap' : '⚠️ Belum Ada' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                        @if($pak['status'] && $pak['file_url'])
                            <a href="{{ $pak['file_url'] }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">
                                👁️ Lihat Dokumen PAK
                            </a>
                        @endif
                        <button type="button" 
                                @click="$dispatch('open-upload-syarat', { title: 'Dokumen PAK (Angka Kredit)', field: 'file_pak', tab: 'kp' })"
                                class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $pak['status'] ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} transition">
                            <span>{{ $pak['status'] ? '✏️ Ganti File' : '📤 Unggah Dokumen PAK' }}</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endif
