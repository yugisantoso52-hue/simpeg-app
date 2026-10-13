<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Penghargaan Satyalancana"
            subtitle="Daftar pegawai yang berhak menerima penghargaan Satyalancana Karya Satya (10, 20, 30 Tahun) & Manajemen Usulan Resmi"
        >
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    {{-- TOMBOL UNDUH SYARAT & JUKNIS RESMI --}}
                    <a href="{{ route('satyalancana.download-syarat') }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Syarat & Dokumen Pengusulan (PDF)
                    </a>

                    {{-- TOMBOL CETAK SURAT PENGANTAR KOLEKTIF KE REKTORAT --}}
                    @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                        <a href="{{ route('satyalancana.cetak-surat-pengantar') }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Cetak Surat Pengantar Usulan (Kolektif)
                        </a>
                    @endif
                </div>
            </x-slot>
        </x-enterprise.page-header>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="rounded-xl border border-emerald-300 bg-emerald-50 px-5 py-4 text-emerald-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="text-emerald-700 font-bold hover:text-emerald-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @if(session('warning'))
                <div class="rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 text-amber-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button type="button" class="text-amber-700 font-bold hover:text-amber-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- 1. INFORMASI ALUR & PERSYARATAN RESMI --}}
            <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-2xl p-5 text-white shadow-md">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-500/30 text-blue-200 border border-blue-400/30">
                            🏛️ PP No. 35/2010 & Petunjuk Teknis Setmilpres
                        </div>
                        <h3 class="text-lg font-bold">Alur Verifikasi, Paraf Tata Naskah & Pengesahan Bertingkat</h3>
                        <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                            Proses pengusulan berjalan secara transparan dan akuntabel: Radar Masa Kerja &rarr; Screening Admin (SKP, Hukdis, CLTN) &rarr; Paraf Kabag Umum &rarr; Paraf Ka Pokja &rarr; TTE Wakil Dekan II &rarr; Pengiriman Berkas Kolektif ke Rektorat.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <div class="bg-white/10 px-3 py-2 rounded-xl backdrop-blur-xs border border-white/10 text-center">
                            <span class="block text-slate-400 text-[10px]">Kategori</span>
                            <span class="font-bold text-amber-300">10, 20, 30 Thn</span>
                        </div>
                        <div class="bg-white/10 px-3 py-2 rounded-xl backdrop-blur-xs border border-white/10 text-center">
                            <span class="block text-slate-400 text-[10px]">Disiplin</span>
                            <span class="font-bold text-emerald-300">Nihil Hukdis</span>
                        </div>
                        <div class="bg-white/10 px-3 py-2 rounded-xl backdrop-blur-xs border border-white/10 text-center">
                            <span class="block text-slate-400 text-[10px]">SKP Kinerja</span>
                            <span class="font-bold text-sky-300">Min. "Baik"</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. DAFTAR PEGAWAI LAYAK MENDAPATKAN PENGHARGAAN (RADAR SISTEM) --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Daftar Nominasi Pegawai Memenuhi Kriteria</h3>
                            <p class="text-xs text-slate-500">Pegawai yang telah mengabdi tanpa cela selama 10, 20, atau 30 tahun dan berhak diusulkan.</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            {{ count($layakSatyalancana) }} Pegawai Memenuhi Syarat
                        </span>
                    </div>

                    <x-enterprise.data-table title="Daftar Nominasi Satyalancana">
                        <x-slot name="head">
                            <tr>
                                <th class="w-12 px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3 text-left">NIP & Nama Pegawai</th>
                                <th class="px-4 py-3 text-left">Unit Kerja & Golongan</th>
                                <th class="px-4 py-3 text-center">TMT CPNS / Pengangkatan</th>
                                <th class="px-4 py-3 text-center">Kategori Kelayakan</th>
                                <th class="px-4 py-3 text-center">Aksi & Dokumen DRH</th>
                            </tr>
                        </x-slot>

                        @forelse($layakSatyalancana as $row)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-center font-medium">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $row->nama_lengkap ?? $row->nama }}</div>
                                    <div class="text-xs text-slate-500">NIP: {{ $row->nip }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-800">{{ $row->unitKerja->nama_unit ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $row->golongan->nama_golongan ?? '-' }} ({{ $row->golongan->kode_golongan ?? '-' }})</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm font-medium text-slate-700">
                                        {{ $row->tmt_sk_pertama ? \Carbon\Carbon::parse($row->tmt_sk_pertama)->format('d-m-Y') : ($row->tanggal_masuk ? \Carbon\Carbon::parse($row->tanggal_masuk)->format('d-m-Y') : '-') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-enterprise.badge 
                                        color="{{ $row->kategori_satyalancana == '30 Tahun' ? 'amber' : ($row->kategori_satyalancana == '20 Tahun' ? 'blue' : 'green') }}">
                                        Satyalancana {{ $row->kategori_satyalancana }}
                                    </x-enterprise.badge>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- CETAK DOKUMEN DRH RESMI DENGAN KOP STANDAR CUTI & TTE --}}
                                        <a href="{{ route('satyalancana.cetak-drh', $row->id) }}"
                                           target="_blank"
                                           title="Cetak Daftar Riwayat Hidup Format 12 Poin BKN/Setmilpres"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            Cetak DRH
                                        </a>

                                        {{-- AJUKAN USULAN RESMI OLEH ADMIN KEPEGAWAIAN --}}
                                        @if($row->usulan_aktif)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-100 text-emerald-800">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                {{ ucfirst($row->usulan_aktif->status) }}
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('satyalancana.ajukan', $row->id) }}" onsubmit="return confirm('Ajukan usulan resmi tanda kehormatan Satyalancana {{ $row->kategori_satyalancana }} untuk {{ $row->nama }}?')">
                                                @csrf
                                                <input type="hidden" name="kategori" value="{{ $row->kategori_satyalancana }}">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Ajukan Usulan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-enterprise.empty-state
                                        title="Tidak Ada Usulan Satyalancana"
                                        description="Belum ada pegawai yang memenuhi kriteria pengabdian 10, 20, atau 30 tahun."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-enterprise.data-table>
                </div>
            </x-enterprise.card>

            {{-- 3. RIWAYAT USULAN RESMI & WORKFLOW APPROVAL / PARAF --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Tracking Berkas & Koordinasi Tata Naskah Usulan</h3>
                            <p class="text-xs text-slate-500">Monitoring status verifikasi berkas, paraf digital (Kabag Umum & Ka Pokja), serta TTE Pimpinan.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase">
                                <tr>
                                    <th class="px-4 py-3">No. Usulan & Periode</th>
                                    <th class="px-4 py-3">Nama Pegawai & NIP</th>
                                    <th class="px-4 py-3 text-center">Kategori</th>
                                    <th class="px-4 py-3 text-center">Progress Paraf & TTE</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi & Paraf Digital</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($daftarUsulan as $usulan)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3">
                                            <div class="font-mono text-xs font-bold text-slate-800">{{ $usulan->nomor_surat_usulan }}</div>
                                            <div class="text-[11px] text-blue-600 font-medium">{{ $usulan->periode_usulan ?? ('Tahun ' . $usulan->tahun_usulan) }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-slate-900">{{ $usulan->pegawai->nama_lengkap ?? $usulan->pegawai->nama }}</div>
                                            <div class="text-xs text-slate-500">NIP: {{ $usulan->pegawai->nip }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <x-enterprise.badge color="{{ $usulan->kategori == '30 Tahun' ? 'amber' : ($usulan->kategori == '20 Tahun' ? 'blue' : 'green') }}">
                                                {{ $usulan->kategori }}
                                            </x-enterprise.badge>
                                        </td>
                                        
                                        {{-- STEP-BY-STEP PROGRESS INDIKATOR PARAF --}}
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5 text-[10px]">
                                                {{-- 1. Verifikasi Admin --}}
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded font-semibold {{ $usulan->verifikasi_admin_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}" title="Verifikasi Admin">
                                                    Admin {{ $usulan->verifikasi_admin_at ? '✓' : '-' }}
                                                </span>
                                                &rarr;
                                                {{-- 2. Paraf Kabag Umum --}}
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded font-semibold {{ $usulan->paraf_kabag_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}" title="Paraf 1: Kabag Umum">
                                                    Kabag {{ $usulan->paraf_kabag_at ? '✓' : '-' }}
                                                </span>
                                                &rarr;
                                                {{-- 3. Paraf Ka Pokja --}}
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded font-semibold {{ $usulan->paraf_kapokja_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}" title="Paraf 2: Ka Pokja Keu-Kepeg">
                                                    Pokja {{ $usulan->paraf_kapokja_at ? '✓' : '-' }}
                                                </span>
                                                &rarr;
                                                {{-- 4. TTE WD2 --}}
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded font-semibold {{ $usulan->ttd_wd2_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}" title="TTE Pimpinan: WD2">
                                                    WD2 {{ $usulan->ttd_wd2_at ? '✓' : '-' }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            @if($usulan->status === 'disetujui_wd2' || $usulan->status === 'selesai')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                    ● Sah (Disetujui WD2)
                                                </span>
                                            @elseif($usulan->status === 'diparaf_kapokja')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                    ● Menunggu TTD WD2
                                                </span>
                                            @elseif($usulan->status === 'diparaf_kabag')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                    ● Menunggu Paraf Pokja
                                                </span>
                                            @elseif($usulan->status === 'diverifikasi_admin')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                    ● Menunggu Paraf Kabag
                                                </span>
                                            @elseif($usulan->status === 'ditolak')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                    ● Ditolak
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                    ● Diajukan
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                {{-- CETAK DRH DENGAN TTE & PARAF --}}
                                                <a href="{{ route('satyalancana.cetak-drh', $usulan->pegawai_id) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded hover:bg-indigo-100 shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                    DRH PDF
                                                </a>

                                                {{-- AKSI PARAF / VERIFIKASI SESUAI HAK AKSES JABATAN --}}
                                                @if($isAdmin && !$usulan->verifikasi_admin_at)
                                                    <form method="POST" action="{{ route('satyalancana.verifikasi', $usulan->id) }}" onsubmit="return confirm('Verifikasi kelengkapan berkas?')">
                                                        @csrf
                                                        <input type="hidden" name="tahap" value="verifikasi_admin">
                                                        <button type="submit" class="px-2 py-1 text-[11px] font-bold rounded bg-amber-600 text-white hover:bg-amber-700 shadow-2xs">
                                                            ✓ Verif Admin
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(($isKabag || $isAdmin) && !$usulan->paraf_kabag_at)
                                                    <form method="POST" action="{{ route('satyalancana.verifikasi', $usulan->id) }}" onsubmit="return confirm('Bubuhkan Paraf 1 (Kepala Bagian Umum)?')">
                                                        @csrf
                                                        <input type="hidden" name="tahap" value="paraf_kabag">
                                                        <button type="submit" class="px-2 py-1 text-[11px] font-bold rounded bg-blue-600 text-white hover:bg-blue-700 shadow-2xs">
                                                            ✒ Paraf Kabag
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(($isKaPokja || $isAdmin) && !$usulan->paraf_kapokja_at)
                                                    <form method="POST" action="{{ route('satyalancana.verifikasi', $usulan->id) }}" onsubmit="return confirm('Bubuhkan Paraf 2 (Ka Pokja Keu-Kepeg)?')">
                                                        @csrf
                                                        <input type="hidden" name="tahap" value="paraf_kapokja">
                                                        <button type="submit" class="px-2 py-1 text-[11px] font-bold rounded bg-teal-600 text-white hover:bg-teal-700 shadow-2xs">
                                                            ✒ Paraf Pokja
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(($isWd2 || $isAdmin) && !$usulan->ttd_wd2_at)
                                                    <form method="POST" action="{{ route('satyalancana.verifikasi', $usulan->id) }}" onsubmit="return confirm('Sahkan usulan secara resmi dengan TTE Wakil Dekan II?')">
                                                        @csrf
                                                        <input type="hidden" name="tahap" value="ttd_wd2">
                                                        <button type="submit" class="px-2 py-1 text-[11px] font-bold rounded bg-emerald-700 text-white hover:bg-emerald-800 shadow-2xs">
                                                            ✍ TTE WD II
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-sm">
                                            Belum ada berkas usulan resmi yang diajukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($daftarUsulan->hasPages())
                        <div class="mt-4">
                            {{ $daftarUsulan->links() }}
                        </div>
                    @endif
                </div>
            </x-enterprise.card>

        </div>
    </div>
</x-app-layout>