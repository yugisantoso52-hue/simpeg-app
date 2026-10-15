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
                                            @elseif(($isKaPokja ?? false) || ($isAdmin ?? false))
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
                                            @elseif(($isKabag ?? false) || ($isAdmin ?? false))
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
                                            @elseif(($isWd2 ?? false) || ($isAdmin ?? false))
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
