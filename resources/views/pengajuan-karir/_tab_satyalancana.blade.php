{{-- Partial Tab Satyalancana Karya Satya di Pusat Verifikasi Usulan Karir --}}
<div class="space-y-6">
    {{-- Banner Informasi & Aksi Cepat --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden border border-slate-700">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    Tanda Kehormatan Republik Indonesia
                </span>
                <h2 class="text-xl md:text-2xl font-black mt-2 tracking-tight">Usulan Resmi Satyalancana Karya Satya (X, XX, XXX Tahun)</h2>
                <p class="text-slate-300 text-xs md:text-sm mt-1 max-w-2xl leading-relaxed">
                    Pengusulan tanda kehormatan masa pengabdian ASN berdasarkan Keppres RI. Diproses melalui alur verifikasi naskah digital berjenjang: <strong>Admin Kepegawaian &rarr; Ka Pokja Keu-Kepeg &rarr; Kepala Bagian Umum &rarr; Pengesahan Wakil Dekan II</strong>.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a href="{{ route('satyalancana.download-syarat') }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/20 transition">
                    <span>📄 Syarat Pengusulan</span>
                </a>
                @if($isExecutive ?? false)
                    <a href="{{ route('satyalancana.index') }}" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-500 text-white shadow-lg shadow-blue-600/30 transition">
                        <span>↗️ Buka Modul Satyalancana Lengkap</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Status Kelayakan & Berkas Mandiri Pegawai --}}
    @if(isset($statusSatya) && $userPegawai)
        <div class="space-y-5">
            {{-- Kartu Status Waktu & Periode Satyalancana --}}
            <div class="rounded-2xl border {{ $statusSatya['can_submit'] ? 'border-indigo-300 bg-indigo-50/50' : 'border-slate-200 bg-white' }} p-6 shadow-sm">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $statusSatya['can_submit'] ? 'bg-indigo-100 text-indigo-800 border border-indigo-300' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                {{ $statusSatya['can_submit'] ? '✓ Memenuhi Syarat Masa Pengabdian' : '🔒 Belum Memenuhi Kriteria Masa Kerja' }}
                            </span>
                            <span class="text-xs text-slate-500 font-mono">Dasar: UU No. 20/2009 &amp; PP No. 35/2010</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span>🏆</span> Status Pengusulan Satyalancana Karya Satya
                        </h3>
                    </div>

                    <div class="shrink-0">
                        @if($statusSatya['usulan_aktif'])
                            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <span>✓ Usulan Telah Diajukan ({{ ucwords(str_replace('_', ' ', $statusSatya['usulan_aktif']->status)) }})</span>
                            </span>
                        @elseif($statusSatya['can_submit'])
                            <form action="{{ route('satyalancana.ajukan', $userPegawai->id) }}" method="POST" onsubmit="return confirm('Ajukan usulan resmi Satyalancana {{ $statusSatya['kategori_layak'] }} untuk diri Anda?')">
                                @csrf
                                <input type="hidden" name="kategori" value="{{ $statusSatya['kategori_layak'] }}">
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 transition transform active:scale-95">
                                    <span>🏆 Ajukan Satyalancana ({{ $statusSatya['kategori_layak'] }})</span>
                                </button>
                            </form>
                        @else
                            <button type="button" disabled class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-slate-200 text-slate-500 border border-slate-300 cursor-not-allowed">
                                <span>🔒 Belum Memenuhi Syarat Pengusulan</span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Indikator Parameter Kelayakan Satyalancana --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 my-4">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[11px] text-slate-500 block font-medium">Status Kepegawaian:</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            @if($statusSatya['is_pns'])
                                <span class="text-emerald-600 font-bold text-xs">✓ PNS (Berhak Satyalancana)</span>
                            @else
                                <span class="text-rose-600 font-bold text-xs">⚠️ {{ $userPegawai->jenis_pegawai ?? 'Bukan PNS' }} (Khusus PNS)</span>
                            @endif
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[11px] text-slate-500 block font-medium">Masa Kerja Pengabdian Riil:</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-indigo-900 font-bold text-xs font-mono">
                                {{ $statusSatya['masa_kerja_tahun'] }} Tahun {{ $statusSatya['masa_kerja_bulan'] }} Bulan
                            </span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[11px] text-slate-500 block font-medium">Kategori Kelayakan Nominasi:</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            @if($statusSatya['kategori_layak'])
                                <span class="text-emerald-700 font-bold text-xs">🏆 Satyalancana {{ $statusSatya['kategori_layak'] }}</span>
                            @else
                                <span class="text-slate-500 font-semibold text-xs">Belum Memenuhi (Min. 10 Tahun)</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if(!$statusSatya['can_submit'] && !$statusSatya['usulan_aktif'])
                    <div class="rounded-xl bg-amber-50/80 border border-amber-300 p-4 text-xs text-amber-900 space-y-1.5">
                        <div class="font-bold flex items-center gap-1.5 text-amber-950">
                            <span>ℹ️</span> Keterangan Regulasi Mengapa Belum Bisa Mengajukan:
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-[11px] text-amber-900 pl-1">
                            @foreach($statusSatya['alasan_kunci'] as $alasan)
                                <li>{{ $alasan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @elseif($statusSatya['can_submit'])
                    <div class="rounded-xl bg-indigo-50 border border-indigo-300 p-3.5 text-xs text-indigo-900 flex items-center gap-2">
                        <span class="text-base shrink-0">🎖️</span>
                        <span><strong>Selamat!</strong> Masa pengabdian Anda telah memenuhi syarat untuk tanda kehormatan <strong>Satyalancana Karya Satya {{ $statusSatya['kategori_layak'] }}</strong>. Silakan klik tombol ajukan di atas.</span>
                    </div>
                @endif
            </div>

            {{-- Panel Kelengkapan Berkas Satyalancana --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>📎</span> Kelengkapan Dokumen Persyaratan Satyalancana
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Lengkapi dokumen pendukung di bawah ini agar berkas Anda siap diverifikasi oleh verifikator kepegawaian.
                        </p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0">
                        Skor Kelengkapan: {{ $statusSatya['syarat_berkas']['skor'] }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
                    {{-- 1. SK Pertama / CPNS --}}
                    @php $sk1 = $statusSatya['syarat_berkas']['items'][0]; @endphp
                    <div class="p-4 rounded-xl border {{ $sk1['status'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/20' }} flex flex-col justify-between gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="font-bold text-slate-800">1. SK CPNS / Pengangkatan</div>
                                <p class="text-[11px] text-slate-500">SK Pengangkatan Pertama CPNS/ASN</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $sk1['status'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $sk1['status'] ? '✓ Lengkap' : '⚠️ Belum Ada' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                            @if($sk1['status'] && $sk1['file_url'])
                                <a href="{{ $sk1['file_url'] }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">
                                    👁️ Lihat Berkas
                                </a>
                            @endif
                            <button type="button" 
                                    @click="$dispatch('open-upload-syarat', { title: 'SK Pengangkatan Pertama / CPNS', field: 'file_sk_pertama', tab: 'satyalancana' })"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $sk1['status'] ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} transition">
                                <span>{{ $sk1['status'] ? '✏️ Ganti File' : '📤 Unggah Berkas' }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- 2. SK Pangkat Terakhir --}}
                    @php $skP = $statusSatya['syarat_berkas']['items'][1]; @endphp
                    <div class="p-4 rounded-xl border {{ $skP['status'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/20' }} flex flex-col justify-between gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="font-bold text-slate-800">2. SK Pangkat Terakhir</div>
                                <p class="text-[11px] text-slate-500">Salinan SK Pangkat terakhir yang dilegalisir</p>
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
                                    @click="$dispatch('open-upload-syarat', { title: 'SK Pangkat Terakhir', field: 'file_sk_pangkat_terakhir', tab: 'satyalancana' })"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $skP['status'] ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} transition">
                                <span>{{ $skP['status'] ? '✏️ Ganti File' : '📤 Unggah Berkas' }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- 3. Fotocopy Karpeg --}}
                    @php $kpg = $statusSatya['syarat_berkas']['items'][4]; @endphp
                    <div class="p-4 rounded-xl border {{ $kpg['status'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/20' }} flex flex-col justify-between gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="font-bold text-slate-800">3. Fotocopy Karpeg</div>
                                <p class="text-[11px] text-slate-500">Kartu Pegawai resmi BKN</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $kpg['status'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $kpg['status'] ? '✓ Lengkap' : '⚠️ Belum Ada' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                            @if($kpg['status'] && $kpg['file_url'])
                                <a href="{{ $kpg['file_url'] }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">
                                    👁️ Lihat Karpeg
                                </a>
                            @endif
                            <button type="button" 
                                    @click="$dispatch('open-upload-syarat', { title: 'Fotocopy Karpeg', field: 'file_karpeg', tab: 'satyalancana' })"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $kpg['status'] ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} transition">
                                <span>{{ $kpg['status'] ? '✏️ Ganti File' : '📤 Unggah Karpeg' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- 1. Daftar Usulan yang Sedang Diverifikasi / Berjalan --}}
    <x-enterprise.card>
        <div class="p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>📑 Daftar Usulan Satyalancana Masuk & Verifikasi</span>
                        @if($countSatyalancanaPending > 0)
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                {{ $countSatyalancanaPending }} Menunggu Paraf
                            </span>
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pemantauan tahapan persetujuan usulan tanda kehormatan</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-600 uppercase text-[11px] font-bold">
                            <th class="py-3 px-3">No</th>
                            <th class="py-3 px-3">Pegawai & NIP</th>
                            <th class="py-3 px-3">Kategori & Periode</th>
                            <th class="py-3 px-3">Status Verifikasi & Paraf</th>
                            <th class="py-3 px-3 text-center">Dokumen DRH</th>
                            <th class="py-3 px-3 text-center">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($daftarUsulanSatyalancana ?? [] as $usl)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 font-medium text-slate-500">{{ $loop->iteration }}</td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $usl->pegawai->nama_lengkap ?? $usl->pegawai->nama }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $usl->pegawai->nip }}</div>
                                    <div class="text-[11px] text-slate-600">{{ $usl->pegawai->jabatan->nama_jabatan ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $usl->kategori == '30 Tahun' ? 'bg-amber-100 text-amber-900 border border-amber-300' : ($usl->kategori == '20 Tahun' ? 'bg-slate-200 text-slate-800 border border-slate-300' : 'bg-orange-100 text-orange-900 border border-orange-300') }}">
                                        🏆 {{ $usl->kategori }}
                                    </span>
                                    <div class="text-[10px] text-slate-500 mt-1 font-mono flex items-center gap-1" x-data="{ editNomorTab: false, valNomor: '{{ explode('/UN19', $usl->nomor_surat_usulan)[0] }}' }">
                                        <span>No: {{ $usl->nomor_surat_usulan }}</span>
                                        @if($isExecutive ?? false)
                                            <button type="button" @click="editNomorTab = true" class="text-blue-600 hover:text-blue-800 p-0.5 text-[10px]" title="Edit Nomor Surat (Admin)">✏️</button>
                                            <div x-show="editNomorTab" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                                                <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-4 text-left" @click.outside="editNomorTab = false">
                                                    <h4 class="text-sm font-bold text-slate-800 mb-1">Penomoran Satyalancana (Admin)</h4>
                                                    <p class="text-xs text-slate-500 mb-3">Tuliskan nomor urut dinas resmi untuk usulan <strong>{{ $usl->pegawai->nama }}</strong>.</p>
                                                    <form action="{{ route('satyalancana.update-nomor', $usl->id) }}" method="POST">
                                                        @csrf
                                                        <div class="flex rounded-lg shadow-sm mb-3">
                                                            <input type="text" name="nomor_surat_input" x-model="valNomor" required class="flex-1 min-w-0 px-3 py-2 text-sm font-mono rounded-l-lg border-slate-300">
                                                            <span class="inline-flex items-center px-2 rounded-r-lg border border-l-0 border-slate-300 bg-slate-100 text-slate-600 text-xs font-mono font-semibold">
                                                                /UN19.5.1.1.10/KP/{{ $usl->tahun_usulan ?: date('Y') }}
                                                            </span>
                                                        </div>
                                                        <div class="flex justify-end gap-2">
                                                            <button type="button" @click="editNomorTab = false" class="px-3 py-1.5 rounded-lg text-xs bg-slate-100 text-slate-700 hover:bg-slate-200">Batal</button>
                                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 text-white hover:bg-blue-700">Simpan Nomor</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="space-y-1">
                                        @if($usl->status === 'disetujui_wd2')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                ✓ Sah TTE WD II
                                            </span>
                                        @elseif($usl->status === 'ditolak')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                                ✗ Ditolak / Perbaikan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                ⏳ {{ ucfirst(str_replace('_', ' ', $usl->status)) }}
                                            </span>
                                        @endif

                                        <div class="flex items-center gap-1 text-[10px] text-slate-500">
                                            <span class="{{ $usl->verifikasi_admin_at ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">Admin</span> &rarr;
                                            <span class="{{ $usl->paraf_kapokja_at ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">Ka Pokja</span> &rarr;
                                            <span class="{{ $usl->paraf_kabag_at ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">Kabag</span> &rarr;
                                            <span class="{{ $usl->ttd_wd2_at ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">WD II</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('satyalancana.cetak-drh', $usl->pegawai_id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition shadow-2xs">
                                        <span>📄 DRH 12 Poin</span>
                                    </a>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    {{-- Form Verifikasi Sesuai Jabatan Login (Bertingkat: Admin -> Pokja -> Kabag -> WD2) --}}
                                    <div class="flex items-center justify-center gap-1 flex-wrap">
                                        @if($isExecutive)
                                            {{-- 1. ADMIN SCREENING --}}
                                            @if($usl->verifikasi_admin_at)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="1. Admin: Selesai">
                                                    ✓ 1. Admin
                                                </span>
                                            @elseif($isAdmin ?? false)
                                                <form action="{{ route('satyalancana.verifikasi', $usl->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="tahap" value="verifikasi_admin">
                                                    <button type="submit" class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-600 text-white hover:bg-amber-700 shadow-xs transition" title="1. Verifikasi Screening Berkas">
                                                        ✓ 1. Admin
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" disabled class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu Screening Admin">
                                                    ⏳ 1. Admin
                                                </button>
                                            @endif

                                            {{-- 2. PARAF POKJA --}}
                                            @if($usl->paraf_kapokja_at)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="2. Paraf Pokja: Selesai">
                                                    ✓ 2. Pokja
                                                </span>
                                            @elseif(!$usl->verifikasi_admin_at)
                                                <button type="button" disabled class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 cursor-not-allowed opacity-60" title="🔒 Terkunci: Menunggu Admin Screening">
                                                    🔒 2. Pokja
                                                </button>
                                            @elseif($isKaPokja ?? false)
                                                <form action="{{ route('satyalancana.verifikasi', $usl->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="tahap" value="paraf_kapokja">
                                                    <button type="submit" class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-teal-600 text-white hover:bg-teal-700 shadow-xs transition" title="2. Bubuhkan Paraf Ka Pokja">
                                                        ✒ 2. Pokja
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" disabled class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu Ka Pokja">
                                                    ⏳ 2. Pokja
                                                </button>
                                            @endif

                                            {{-- 3. PARAF KABAG --}}
                                            @if($usl->paraf_kabag_at)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="3. Paraf Kabag: Selesai">
                                                    ✓ 3. Kabag
                                                </span>
                                            @elseif(!$usl->paraf_kapokja_at)
                                                <button type="button" disabled class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 cursor-not-allowed opacity-60" title="🔒 Terkunci: Menunggu Paraf Ka Pokja">
                                                    🔒 3. Kabag
                                                </button>
                                            @elseif($isKabag ?? false)
                                                <form action="{{ route('satyalancana.verifikasi', $usl->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="tahap" value="paraf_kabag">
                                                    <button type="submit" class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition" title="3. Bubuhkan Paraf Kabag Umum">
                                                        ✒ 3. Kabag
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" disabled class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu Kabag Umum">
                                                    ⏳ 3. Kabag
                                                </button>
                                            @endif

                                            {{-- 4. TTE WD2 --}}
                                            @if($usl->ttd_wd2_at)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="4. Disahkan TTE WD2">
                                                    ✓ 4. Sah (WD2)
                                                </span>
                                            @elseif(!$usl->paraf_kabag_at)
                                                <button type="button" disabled class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 cursor-not-allowed opacity-60" title="🔒 Terkunci: Menunggu Paraf Kabag Umum">
                                                    🔒 4. WD2
                                                </button>
                                            @elseif(($isWd2 ?? false) || ($isDekan ?? false))
                                                <form action="{{ route('satyalancana.verifikasi', $usl->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="tahap" value="ttd_wd2">
                                                    <button type="submit" class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-700 text-white hover:bg-emerald-800 shadow-xs transition" title="4. Sahkan Usulan Resmi TTE WD II">
                                                        ✍ 4. TTE WD2
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" disabled class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu TTE WD II">
                                                    ⏳ 4. WD2
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400">
                                    Belum ada usulan Satyalancana aktif yang sedang diajukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($daftarUsulanSatyalancana) && $daftarUsulanSatyalancana->hasPages())
                <div class="mt-4">
                    {{ $daftarUsulanSatyalancana->links() }}
                </div>
            @endif
        </div>
    </x-enterprise.card>

    {{-- 2. Daftar Pegawai yang Memenuhi Syarat & Berhak Diajukan --}}
    <x-enterprise.card>
        <div class="p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>🎖️ Daftar Pegawai Memenuhi Kriteria Masa Kerja (Siap Diajukan)</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            {{ count($layakSatyalancana ?? []) }} Pegawai
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pegawai aktif dengan masa kerja 10, 20, atau 30 tahun yang memenuhi kriteria pengusulan</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-600 uppercase text-[11px] font-bold">
                            <th class="py-3 px-3">No</th>
                            <th class="py-3 px-3">Nama Pegawai & NIP</th>
                            <th class="py-3 px-3">Unit & Jabatan</th>
                            <th class="py-3 px-3">TMT CPNS / Pengangkatan</th>
                            <th class="py-3 px-3 text-center">Kategori Nominasi</th>
                            <th class="py-3 px-3 text-center">Aksi Pengusulan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($layakSatyalancana ?? [] as $peg)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 font-medium text-slate-500">{{ $loop->iteration }}</td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $peg->nama_lengkap ?? $peg->nama }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $peg->nip }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="text-slate-800">{{ $peg->unitKerja->nama_unit ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $peg->jabatan->nama_jabatan ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3 font-mono">
                                    {{ $peg->tmt_sk_pertama ? \Carbon\Carbon::parse($peg->tmt_sk_pertama)->format('d-m-Y') : ($peg->tanggal_masuk ? \Carbon\Carbon::parse($peg->tanggal_masuk)->format('d-m-Y') : '-') }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $peg->kategori_satyalancana == '30 Tahun' ? 'bg-amber-100 text-amber-900 border border-amber-300' : ($peg->kategori_satyalancana == '20 Tahun' ? 'bg-slate-200 text-slate-800 border border-slate-300' : 'bg-orange-100 text-orange-900 border border-orange-300') }}">
                                        🏆 {{ $peg->kategori_satyalancana }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($peg->usulan_aktif)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            ✓ Sudah Diajukan ({{ $peg->usulan_aktif->status }})
                                        </span>
                                    @else
                                        <form action="{{ route('satyalancana.ajukan', $peg->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="kategori" value="{{ $peg->kategori_satyalancana }}">
                                            <button type="submit" 
                                                    onclick="return confirm('Ajukan usulan resmi Satyalancana {{ $peg->kategori_satyalancana }} an. {{ $peg->nama }}?')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-600 hover:bg-amber-500 text-white shadow-xs transition active:scale-95">
                                                <span>⚡ Ajukan Usulan</span>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400">
                                    Tidak ada pegawai yang memenuhi kriteria baru pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-enterprise.card>
</div>
