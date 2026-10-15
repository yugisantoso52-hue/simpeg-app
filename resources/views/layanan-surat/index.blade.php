<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Layanan E-Surat Mandiri"
            subtitle="Penerbitan Surat Keterangan Aktif Bekerja Mandiri Resmi ber-QR Code untuk Dosen & Tendik"
        />
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

            @if(session('error'))
                <div class="rounded-xl border border-red-300 bg-red-50 px-5 py-4 text-red-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="text-red-700 font-bold hover:text-red-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- BANNER EDUKASI REGULASI & TATA NASKAH DINAS --}}
            <div class="rounded-xl border border-indigo-200 bg-gradient-to-r from-indigo-50 via-blue-50 to-white p-5 shadow-xs">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 bg-indigo-600 text-white rounded-lg shadow-xs shrink-0">
                            <x-icon name="scale" class="w-5 h-5" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-indigo-950 flex items-center gap-2">
                                <span>Pusat Edukasi & Landasan Hukum Tata Naskah Dinas Elektronik</span>
                                <span class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-semibold border border-indigo-200">PermenPAN-RB 1/2023 & UU 30/2014</span>
                            </h4>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Pelajari keabsahan mandat penandatanganan surat, SOP verifikasi alur paraf digital Katim TU, serta validitas hukum dokumen resmi ber-QR Code di SIMPEG UNRI.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 w-full md:w-auto">
                        <a href="{{ route('regulasi-tata-naskah.index') }}" class="flex-1 md:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                            <x-icon name="book-open" class="w-3.5 h-3.5" />
                            <span>Buka Panduan & Regulasi</span>
                        </a>
                        <a href="{{ route('regulasi-tata-naskah.pdf') }}" target="_blank" class="flex-1 md:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 text-indigo-700 border border-indigo-200 text-xs font-semibold rounded-lg shadow-xs transition">
                            <x-icon name="arrow-down" class="w-3.5 h-3.5" />
                            <span>Unduh PDF</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 1. FORM PENGAJUAN / PENERBITAN SURAT MANDIRI --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2.5 bg-blue-100 text-blue-700 rounded-lg text-xl">
                            📄
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Cetak Surat Keterangan Aktif Bekerja (Instan)</h3>
                            <p class="text-xs text-slate-500">
                                Diterbitkan secara elektronik dengan verifikasi QR Code resmi Fakultas Keperawatan Universitas Riau.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('layanan-surat.surat-aktif.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Pegawai Pemohon
                                </label>
                                @if($isExecutiveOrAdmin)
                                    <select name="pegawai_id" class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">-- Diri Sendiri ({{ Auth::user()->name }}) --</option>
                                        @foreach(\App\Models\Pegawai::where('status_pegawai', 'Aktif')->orderBy('nama')->get() as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama }} (NIP. {{ $p->nip }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-[11px] text-slate-400 mt-1">Sebagai Admin/Pimpinan, Anda dapat mencetak untuk diri sendiri atau pegawai lain.</p>
                                @else
                                    <input type="text" disabled 
                                           value="{{ $myPegawai ? ($myPegawai->nama_lengkap ?? $myPegawai->nama) . ' (NIP: ' . $myPegawai->nip . ')' : Auth::user()->name }}"
                                           class="w-full text-sm bg-slate-100 rounded-lg border-slate-300 text-slate-600">
                                @endif
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Nomor Surat <span class="text-xs font-normal text-slate-500">(Isi Manual)</span>
                                </label>
                                <div class="flex rounded-lg shadow-sm">
                                    <input type="text" name="nomor_surat_manual" 
                                           value="{{ old('nomor_surat_manual', $nextNomorPrefix ?? 'B/0001') }}"
                                           placeholder="Contoh: B/0001"
                                           class="flex-1 min-w-0 block w-full px-3 py-2 text-sm font-mono rounded-none rounded-l-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="inline-flex items-center px-2.5 rounded-r-lg border border-l-0 border-slate-300 bg-slate-100 text-slate-600 text-xs font-mono font-semibold select-none">
                                        /UN19.5.1.1.10/KP/{{ date('Y') }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Format: <em>(isi manual)/UN19.5.1.1.10/KP/{{ date('Y') }}</em></p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Pejabat Penandatangan Surat <span class="text-red-500">*</span>
                                </label>
                                <select name="penandatangan_role" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="dekan" {{ old('penandatangan_role') === 'dekan' ? 'selected' : '' }}>
                                        Prof. Wan Nishfa Dewi, S.Kp, MNg, PhD — Dekan (Utama / Visa / Beasiswa / Formal)
                                    </option>
                                    <option value="wadek2" {{ old('penandatangan_role') === 'wadek2' ? 'selected' : '' }}>
                                        Dr. Safri, M.Kep, Sp.Kep.M.B — a.n. Dekan, Wakil Dekan Bidang Keuangan & Umum (Bank / KPR / BPJS)
                                    </option>
                                </select>
                                <p class="text-[11px] text-slate-400 mt-1">Pilih Dekan (utama) atau Wakil Dekan II (a.n. Dekan) sesuai peruntukan surat.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Keperluan Surat <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="keperluan" required
                                       placeholder="Contoh: Pengajuan KPR Bank / Persyaratan Beasiswa"
                                       class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-[11px] text-slate-400 mt-1">Tuliskan instansi atau tujuan penggunaan surat secara spesifik.</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-2">
                            @if($isExecutiveOrAdmin)
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="langsung_sahkan" id="langsung_sahkan" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <label for="langsung_sahkan" class="text-xs text-slate-700 font-medium cursor-pointer">
                                        ⚡ Terbitkan & Sahkan Langsung (Mode Cepat Administrator)
                                    </label>
                                </div>
                            @else
                                <div class="text-[11px] text-slate-500">
                                    ℹ️ Permohonan surat akan melalui alur pemeriksaan Ka Pokja Keu-Kepeg & Kabag Umum sebelum disahkan Pimpinan.
                                </div>
                            @endif

                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow transition ml-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                {{ $isExecutiveOrAdmin ? 'Terbitkan Surat Keterangan' : 'Ajukan Permohonan Surat Keterangan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </x-enterprise.card>

            {{-- 2. DAFTAR RIWAYAT SURAT KETERANGAN --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Riwayat E-Surat Keterangan Diterbitkan</h3>
                            <p class="text-xs text-slate-500">Arsip surat digital yang telah diterbitkan dan dapat diunduh ulang sewaktu-waktu.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase">
                                <tr>
                                    <th class="px-4 py-3">Nomor Surat</th>
                                    <th class="px-4 py-3">Nama Pegawai & NIP</th>
                                    <th class="px-4 py-3">Penandatangan</th>
                                    <th class="px-4 py-3">Keperluan</th>
                                    <th class="px-4 py-3 text-center">Tanggal Terbit</th>
                                    <th class="px-4 py-3 text-center">Status Alur SOP</th>
                                    <th class="px-4 py-3 text-center">Aksi Dokumen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($surats as $item)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-800">
                                            {{ $item->nomor_surat }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-slate-900">{{ $item->pegawai->nama_lengkap ?? $item->pegawai->nama }}</div>
                                            <div class="text-xs text-slate-500">NIP: {{ $item->pegawai->nip }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(str_contains(strtolower($item->penandatangan_jabatan ?? ''), 'wakil dekan'))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    a.n. Dekan (Wadek II)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                    Dekan (Utama)
                                                </span>
                                            @endif
                                            <div class="text-[10.5px] text-slate-500 truncate max-w-[150px]" title="{{ $item->penandatangan_nama }}">{{ $item->penandatangan_nama }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-700 max-w-xs truncate" title="{{ $item->keperluan }}">
                                            {{ $item->keperluan }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-slate-600 text-xs font-medium">
                                            {{ $item->tanggal_surat ? $item->tanggal_surat->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($item->status === 'selesai')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    ● Sah & Selesai
                                                </span>
                                            @elseif($item->status === 'diparaf_kabag')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                    ⏳ Menunggu TTD Pimpinan
                                                </span>
                                            @elseif($item->status === 'diparaf_kapokja')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                                    ⏳ Menunggu Paraf Kabag
                                                </span>
                                            @elseif($item->status === 'ditolak')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                    ✕ Ditolak / Perbaikan
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                    ⏳ Menunggu Verifikasi Ka Pokja
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex flex-col items-center gap-1.5">
                                                <a href="{{ route('layanan-surat.surat-aktif.cetak', $item->id) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg {{ $item->status === 'selesai' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300' }} transition shadow-xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                    {{ $item->status === 'selesai' ? 'Cetak PDF (Sah)' : 'Cetak (Draf)' }}
                                                </a>

                                                {{-- Tombol Verifikasi SOP Berjenjang untuk Pimpinan / Verifikator --}}
                                                @if($isExecutiveOrAdmin && $item->status !== 'selesai' && $item->status !== 'ditolak')
                                                    <div class="flex items-center gap-1">
                                                        @if(!$item->paraf_kapokja_at)
                                                            <form action="{{ route('layanan-surat.surat-aktif.verifikasi', $item->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="tahap" value="paraf_kapokja">
                                                                <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-600 text-white hover:bg-sky-700 transition" title="1. Periksa berkas & bubuhkan paraf Ka Pokja">
                                                                    ✓ 1. Paraf Pokja
                                                                </button>
                                                            </form>
                                                        @elseif(!$item->paraf_kabag_at)
                                                            <form action="{{ route('layanan-surat.surat-aktif.verifikasi', $item->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="tahap" value="paraf_kabag">
                                                                <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-600 text-white hover:bg-indigo-700 transition" title="2. Telaah naskah & bubuhkan paraf Kabag">
                                                                    ✓ 2. Paraf Kabag
                                                                </button>
                                                            </form>
                                                        @elseif($item->status === 'diparaf_kabag')
                                                            <form action="{{ route('layanan-surat.surat-aktif.verifikasi', $item->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="tahap" value="ttd_pimpinan">
                                                                <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition" title="3. Sahkan & tanda tangani resmi Pimpinan">
                                                                    🎖️ 3. Sahkan
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <form action="{{ route('layanan-surat.surat-aktif.verifikasi', $item->id) }}" method="POST" onsubmit="return confirm('Kembalikan permohonan surat ini untuk perbaikan?')">
                                                            @csrf
                                                            <input type="hidden" name="tahap" value="tolak">
                                                            <button type="submit" class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition" title="Kembalikan / Tolak">
                                                                ✕
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-sm">
                                            Belum ada arsip surat keterangan yang diterbitkan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($surats->hasPages())
                        <div class="mt-4">
                            {{ $surats->links() }}
                        </div>
                    @endif
                </div>
            </x-enterprise.card>

        </div>
    </div>
</x-app-layout>
