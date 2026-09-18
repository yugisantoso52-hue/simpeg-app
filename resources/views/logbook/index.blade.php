<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>📝</span> E-Logbook Kinerja Harian
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Pencatatan Aktivitas Harian, Pelaporan Output Kerja, & Verifikasi Kinerja Pegawai FKP UNRI
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('logbook.export.pdf', ['bulan' => $month, 'tahun' => $year]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Cetak PDF Bulanan
                </a>

                <a href="{{ route('logbook.export.excel', ['bulan' => $month, 'tahun' => $year]) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export Excel
                </a>

                <a href="{{ route('logbook.create') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    + Catat Aktivitas
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ALERT NOTIFIKASI --}}
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="p-1 rounded-full bg-emerald-100 text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" class="text-emerald-700 font-bold hover:text-emerald-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="p-1 rounded-full bg-rose-100 text-rose-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </span>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" class="text-rose-700 font-bold hover:text-rose-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @if(session('info'))
                <div class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 text-blue-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="p-1 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span class="text-sm font-medium">{{ session('info') }}</span>
                    </div>
                    <button type="button" class="text-blue-700 font-bold hover:text-blue-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- KARTU RINGKASAN KINERJA BULANAN --}}
            <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-indigo-800 rounded-2xl shadow-lg p-6 text-white">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <div class="text-blue-200 text-xs uppercase tracking-wider font-bold">
                            Rekap Capaian Kinerja • {{ \Carbon\Carbon::createFromDate($year, $month, 1)->locale('id')->isoFormat('MMMM Y') }}
                        </div>
                        <div class="text-2xl md:text-3xl font-extrabold mt-1">
                            {{ $pegawai->nama }}
                        </div>
                        <p class="text-xs text-blue-200 mt-1 flex items-center gap-2">
                            <span>NIP: {{ $pegawai->nip ?? '-' }}</span>
                            <span>•</span>
                            <span>{{ $pegawai->unitKerja?->nama_unit ?? 'FKP UNRI' }}</span>
                            <span>•</span>
                            <span>{{ $pegawai->jabatan?->nama_jabatan ?? '-' }}</span>
                        </p>
                    </div>

                    {{-- STATS GRID --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 w-full md:w-auto">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 text-center border border-white/10">
                            <div class="text-[11px] text-blue-200 font-medium">Total Aktivitas</div>
                            <div class="text-2xl font-black mt-0.5">{{ $statistics['total_aktivitas'] }}</div>
                            <div class="text-[10px] text-blue-200">{{ $statistics['total_output'] }} output</div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 text-center border border-white/10">
                            <div class="text-[11px] text-blue-200 font-medium">Total Jam Kerja</div>
                            <div class="text-2xl font-black mt-0.5">{{ $statistics['total_jam'] }}</div>
                            <div class="text-[10px] text-blue-200">Jam tercatat</div>
                        </div>

                        <div class="bg-emerald-500/20 backdrop-blur-md rounded-xl p-3 text-center border border-emerald-400/30">
                            <div class="text-[11px] text-emerald-200 font-medium">Disetujui</div>
                            <div class="text-2xl font-black text-emerald-300 mt-0.5">{{ $statistics['disetujui'] }}</div>
                            <div class="text-[10px] text-emerald-200">Terverifikasi</div>
                        </div>

                        <div class="bg-amber-500/20 backdrop-blur-md rounded-xl p-3 text-center border border-amber-400/30">
                            <div class="text-[11px] text-amber-200 font-medium">Menunggu</div>
                            <div class="text-2xl font-black text-amber-300 mt-0.5">{{ $statistics['diajukan'] }}</div>
                            <div class="text-[10px] text-amber-200">Verifikasi</div>
                        </div>
                    </div>
                </div>

                {{-- ACTION BULK SUBMIT JIKA ADA DRAFT ATAU REVISI --}}
                @if($statistics['draft'] > 0 || $statistics['perlu_revisi'] > 0)
                    <div class="mt-5 pt-4 border-t border-white/15 flex flex-col sm:flex-row justify-between items-center gap-3">
                        <div class="text-xs text-blue-100 flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            <span>Terdapat <strong>{{ $statistics['draft'] }} draft</strong> dan <strong>{{ $statistics['perlu_revisi'] }} perlu revisi</strong> yang belum diajukan untuk periode ini.</span>
                        </div>
                        <form method="POST" action="{{ route('logbook.submit-bulk') }}" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan seluruh aktivitas draft/revisi periode {{ \Carbon\Carbon::createFromDate($year, $month, 1)->locale('id')->isoFormat('MMMM Y') }} ke atasan?')">
                            @csrf
                            <input type="hidden" name="bulan" value="{{ $month }}">
                            <input type="hidden" name="tahun" value="{{ $year }}">
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold px-4 py-2 text-xs transition shadow">
                                🚀 Ajukan Semua Draft Bulan Ini ke Atasan
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- FILTER & PENCARIAN --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                <form method="GET" action="{{ route('logbook.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Bulan</label>
                        <select name="bulan" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $m, 1)->locale('id')->isoFormat('MMMM') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun</label>
                        <select name="tahun" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="semua" {{ $status == 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="diajukan" {{ $status == 'diajukan' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="disetujui" {{ $status == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="perlu_revisi" {{ $status == 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                            <option value="ditolak" {{ $status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kategori</label>
                        <select name="kategori" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="semua" {{ $kategori == 'semua' ? 'selected' : '' }}>Semua Kategori</option>
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ $kategori == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2 flex items-center gap-2">
                        <button type="submit" class="w-full rounded-lg bg-blue-600 py-2.5 px-3 text-xs font-semibold text-white shadow hover:bg-blue-700 transition">
                            Filter
                        </button>
                        <a href="{{ route('logbook.index') }}" class="rounded-lg bg-gray-100 py-2.5 px-3 text-xs font-semibold text-gray-600 hover:bg-gray-200 transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- TABEL DAFTAR LOGBOOK --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-700 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3.5 w-12 text-center">No</th>
                                <th class="px-4 py-3.5">Hari / Tanggal</th>
                                <th class="px-4 py-3.5">Waktu & Durasi</th>
                                <th class="px-4 py-3.5">Kategori & Aktivitas</th>
                                <th class="px-4 py-3.5">Output</th>
                                <th class="px-4 py-3.5">Bukti</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-4 py-3.5 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($logbooks as $index => $item)
                                <tr class="hover:bg-blue-50/40 transition">
                                    <td class="px-4 py-3.5 text-center text-gray-500 font-medium">
                                        {{ $logbooks->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900">
                                            {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('D MMMM Y') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="text-xs font-semibold text-gray-900">
                                            {{ $item->jam_kerja_formatted }}
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-[11px] font-medium bg-blue-100 text-blue-800">
                                            ⏱️ {{ $item->durasi_formatted }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-600 mb-1">
                                            {{ $item->kategori_kegiatan }}
                                        </div>
                                        <div class="font-semibold text-gray-900 line-clamp-1">
                                            {{ $item->aktivitas }}
                                        </div>
                                        <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">
                                            {{ $item->deskripsi_kegiatan }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="font-semibold text-gray-800">
                                            {{ $item->jumlah_output }} {{ $item->satuan_output }}
                                        </div>
                                        @if($item->output_kegiatan)
                                            <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ $item->output_kegiatan }}">
                                                {{ $item->output_kegiatan }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        @if($item->file_lampiran)
                                            <a href="{{ route('document.preview', $item->file_lampiran) }}" target="_blank"
                                               class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-semibold bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-lg border border-blue-200 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                Berkas
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $item->status_badge_class }}">
                                            {{ $item->status_label }}
                                        </span>
                                        @if($item->catatan_atasan)
                                            <div class="text-[11px] text-rose-600 mt-1 font-medium max-w-xs truncate" title="{{ $item->catatan_atasan }}">
                                                Catatan: {{ $item->catatan_atasan }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('logbook.show', $item->id) }}"
                                               class="p-1.5 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Lihat Detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>

                                            @if($item->canEditBy(auth()->user()))
                                                <a href="{{ route('logbook.edit', $item->id) }}"
                                                   class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition" title="Edit / Koreksi">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>

                                                <form method="POST" action="{{ route('logbook.submit', $item->id) }}" class="inline" onsubmit="return confirm('Ajukan aktivitas ini ke atasan sekarang?')">
                                                    @csrf
                                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition" title="Ajukan ke Atasan">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($item->canDeleteBy(auth()->user()))
                                                <form method="POST" action="{{ route('logbook.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Hapus catatan aktivitas ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition" title="Hapus Draft">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <span class="text-4xl">📋</span>
                                            <p class="font-medium text-gray-500">Belum ada catatan aktivitas logbook pada periode ini.</p>
                                            <a href="{{ route('logbook.create') }}" class="mt-2 text-xs font-semibold text-blue-600 hover:underline">
                                                + Klik di sini untuk mencatat aktivitas harian Anda
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logbooks->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $logbooks->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
