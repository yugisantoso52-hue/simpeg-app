<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>⚖️</span> Verifikasi & Rekap Logbook Kinerja
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Monitoring, Evaluasi, & Pengesahan Aktivitas Harian Pegawai FKP UNRI
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.logbook.export.pdf', request()->all()) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Cetak Rekap PDF
                </a>

                <a href="{{ route('admin.logbook.export.excel', request()->all()) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export Excel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ selectedIds: [], selectAll: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- NOTIFIKASI FLASH --}}
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
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                    <button type="button" class="text-rose-700 font-bold hover:text-rose-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @if(isset($isRestrictedToBawahan) && $isRestrictedToBawahan)
                <div class="rounded-xl border border-blue-200 bg-blue-50/80 px-5 py-3 text-blue-900 flex items-center gap-3 text-sm shadow-sm">
                    <span class="text-xl">👥</span>
                    <div>
                        <span class="font-bold">Meja Verifikasi Atasan Langsung:</span> Menampilkan daftar logbook kinerja harian dari seluruh staf / pegawai yang berada di bawah bimbingan dan tanggung jawab langsung Anda.
                    </div>
                </div>
            @endif

            {{-- STATISTIK VERIFIKASI ADMIN --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-200">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Entri</div>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $statistics['total'] }}</div>
                    <div class="text-[11px] text-gray-500 mt-0.5">{{ $statistics['total_jam'] }} Jam Kinerja</div>
                </div>

                <div class="bg-amber-50 rounded-2xl shadow-sm p-4 border border-amber-200">
                    <div class="text-xs font-bold text-amber-700 uppercase tracking-wider">Menunggu Verifikasi</div>
                    <div class="text-2xl font-black text-amber-700 mt-1">{{ $statistics['menunggu'] }}</div>
                    <div class="text-[11px] text-amber-600 mt-0.5">Perlu Disahkan</div>
                </div>

                <div class="bg-emerald-50 rounded-2xl shadow-sm p-4 border border-emerald-200">
                    <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Telah Disetujui</div>
                    <div class="text-2xl font-black text-emerald-700 mt-1">{{ $statistics['disetujui'] }}</div>
                    <div class="text-[11px] text-emerald-600 mt-0.5">Kinerja Valid</div>
                </div>

                <div class="bg-orange-50 rounded-2xl shadow-sm p-4 border border-orange-200">
                    <div class="text-xs font-bold text-orange-700 uppercase tracking-wider">Perlu Revisi</div>
                    <div class="text-2xl font-black text-orange-700 mt-1">{{ $statistics['perlu_revisi'] }}</div>
                    <div class="text-[11px] text-orange-600 mt-0.5">Dikembalikan</div>
                </div>

                <div class="bg-rose-50 rounded-2xl shadow-sm p-4 border border-rose-200">
                    <div class="text-xs font-bold text-rose-700 uppercase tracking-wider">Ditolak</div>
                    <div class="text-2xl font-black text-rose-700 mt-1">{{ $statistics['ditolak'] }}</div>
                    <div class="text-[11px] text-rose-600 mt-0.5">Tidak Memenuhi</div>
                </div>
            </div>

            {{-- FILTER & PENCARIAN --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                <form method="GET" action="{{ route('admin.logbook.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                    <div class="lg:col-span-2">
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
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Unit Kerja</label>
                        <select name="unit_kerja_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Unit Kerja</option>
                            @foreach($unitKerjaList as $u)
                                <option value="{{ $u->id }}" {{ $unitKerjaId == $u->id ? 'selected' : '' }}>{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kategori Pegawai</label>
                        <select name="kategori_pegawai" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="semua" {{ $kategoriPegawai == 'semua' ? 'selected' : '' }}>Semua</option>
                            <option value="Dosen" {{ $kategoriPegawai == 'Dosen' ? 'selected' : '' }}>Dosen</option>
                            <option value="Tenaga Kependidikan" {{ $kategoriPegawai == 'Tenaga Kependidikan' ? 'selected' : '' }}>Tendik</option>
                            <option value="Pegawai Harian Lepas" {{ $kategoriPegawai == 'Pegawai Harian Lepas' ? 'selected' : '' }}>PHL</option>
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status Verifikasi</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="semua" {{ $status == 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="diajukan" {{ $status == 'diajukan' ? 'selected' : '' }}>⏳ Menunggu Verifikasi</option>
                            <option value="disetujui" {{ $status == 'disetujui' ? 'selected' : '' }}>✅ Disetujui</option>
                            <option value="perlu_revisi" {{ $status == 'perlu_revisi' ? 'selected' : '' }}>⚠️ Perlu Revisi</option>
                            <option value="ditolak" {{ $status == 'ditolak' ? 'selected' : '' }}>❌ Ditolak</option>
                            <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>📝 Draft</option>
                        </select>
                    </div>

                    <div class="lg:col-span-9">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Cari Pegawai / Aktivitas</label>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Ketik Nama Pegawai, NIP, atau Ringkasan Pekerjaan..."
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="lg:col-span-3 flex items-center gap-2">
                        <button type="submit" class="w-full rounded-lg bg-blue-600 py-2.5 px-3 text-xs font-semibold text-white shadow hover:bg-blue-700 transition">
                            Cari & Filter
                        </button>
                        <a href="{{ route('admin.logbook.index') }}" class="rounded-lg bg-gray-100 py-2.5 px-3 text-xs font-semibold text-gray-600 hover:bg-gray-200 transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- TOOLBAR PERSETUJUAN MASSAL (BULK APPROVAL) --}}
            <form id="bulkVerifyForm" method="POST" action="{{ route('admin.logbook.bulk-verify') }}"
                  onsubmit="return confirm('Apakah Anda yakin ingin menyetujui sekaligus seluruh aktivitas yang dipilih?')">
                @csrf
                <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 flex flex-col sm:flex-row justify-between items-center gap-3"
                     x-show="selectedIds.length > 0" x-cloak>
                    <div class="text-xs text-indigo-900 font-semibold flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-indigo-200 text-indigo-800 font-bold" x-text="selectedIds.length"></span>
                        <span>Aktivitas dipilih untuk diverifikasi secara massal.</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="status" value="disetujui">
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="logbook_ids[]" :value="id">
                        </template>

                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui Semua yang Dipilih (Bulk Approve)
                        </button>
                    </div>
                </div>
            </form>

            {{-- TABEL VERIFIKASI LOGBOOK --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-700 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3.5 w-10 text-center">
                                    <input type="checkbox"
                                           @change="
                                               selectAll = !selectAll;
                                               if (selectAll) {
                                                   selectedIds = Array.from(document.querySelectorAll('.logbook-checkbox')).map(el => el.value);
                                               } else {
                                                   selectedIds = [];
                                               }
                                           "
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-4 py-3.5">Pegawai</th>
                                <th class="px-4 py-3.5">Tanggal & Waktu</th>
                                <th class="px-4 py-3.5">Aktivitas & Rincian</th>
                                <th class="px-4 py-3.5">Output</th>
                                <th class="px-4 py-3.5">Bukti</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-4 py-3.5 text-center w-36">Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($logbooks as $item)
                                <tr class="hover:bg-blue-50/40 transition">
                                    <td class="px-4 py-3.5 text-center">
                                        @if($item->status === 'diajukan')
                                            <input type="checkbox" value="{{ $item->id }}"
                                                   x-model="selectedIds"
                                                   class="logbook-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">{{ $item->pegawai?->nama }}</div>
                                        <div class="text-xs text-gray-500">NIP: {{ $item->pegawai?->nip ?? '-' }}</div>
                                        <div class="text-[11px] text-blue-600 font-medium">{{ $item->pegawai?->unitKerja?->nama_unit ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900">
                                            {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('D MMM Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $item->jam_kerja_formatted }}</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-800 mt-1">
                                            ⏱️ {{ $item->durasi_formatted }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-600 mb-1">
                                            {{ $item->kategori_kegiatan }}
                                        </div>
                                        <div class="font-bold text-gray-900 line-clamp-1">{{ $item->aktivitas }}</div>
                                        <div class="text-xs text-gray-500 line-clamp-2 mt-0.5">{{ $item->deskripsi_kegiatan }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="font-semibold text-gray-800">
                                            {{ $item->jumlah_output }} {{ $item->satuan_output }}
                                        </div>
                                        @if($item->output_kegiatan)
                                            <div class="text-xs text-gray-500 truncate max-w-xs">{{ $item->output_kegiatan }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        @if($item->file_lampiran)
                                            <a href="{{ route('document.preview', $item->file_lampiran) }}" target="_blank"
                                               class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-semibold bg-blue-50 px-2 py-1 rounded border border-blue-200">
                                                📎 Berkas
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
                                        <div class="flex items-center justify-center gap-1.5" x-data="{ openVerifyModal: false }">
                                            <a href="{{ route('logbook.show', $item->id) }}"
                                               class="p-1.5 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Lihat Detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>

                                            {{-- TOMBOL BUKA MODAL VERIFIKASI --}}
                                            <button type="button" @click="openVerifyModal = true"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200 transition">
                                                ⚖️ Verifikasi
                                            </button>

                                            {{-- MODAL VERIFIKASI CEPAT --}}
                                            <div x-show="openVerifyModal" x-cloak
                                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 text-left">
                                                <div @click.away="openVerifyModal = false"
                                                     class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                                                    <div class="flex justify-between items-center border-b pb-3">
                                                        <h3 class="text-base font-bold text-gray-900">
                                                            Verifikasi Logbook: {{ $item->pegawai?->nama }}
                                                        </h3>
                                                        <button type="button" @click="openVerifyModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">×</button>
                                                    </div>

                                                    <div class="text-xs text-gray-600 bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-1">
                                                        <div><strong>Tanggal:</strong> {{ $item->tanggal_formatted }} ({{ $item->jam_kerja_formatted }} WIB - {{ $item->durasi_formatted }})</div>
                                                        <div><strong>Aktivitas:</strong> {{ $item->aktivitas }}</div>
                                                        <div><strong>Output:</strong> {{ $item->jumlah_output }} {{ $item->satuan_output }} ({{ $item->output_kegiatan ?? '-' }})</div>
                                                    </div>

                                                    <form method="POST" action="{{ route('admin.logbook.verify', $item->id) }}" class="space-y-4">
                                                        @csrf
                                                        <div>
                                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Keputusan</label>
                                                            <select name="status" required class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                                                <option value="disetujui" {{ $item->status === 'disetujui' ? 'selected' : '' }}>✅ Disetujui</option>
                                                                <option value="perlu_revisi" {{ $item->status === 'perlu_revisi' ? 'selected' : '' }}>⚠️ Perlu Revisi (Kembalikan ke Pegawai)</option>
                                                                <option value="ditolak" {{ $item->status === 'ditolak' ? 'selected' : '' }}>❌ Ditolak</option>
                                                            </select>
                                                        </div>

                                                        <div>
                                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan Koreksi / Evaluasi</label>
                                                            <textarea name="catatan_atasan" rows="3" placeholder="Tuliskan catatan evaluasi atau alasan revisi..."
                                                                      class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">{{ $item->catatan_atasan }}</textarea>
                                                        </div>

                                                        <div class="flex justify-end gap-2 pt-2">
                                                            <button type="button" @click="openVerifyModal = false"
                                                                    class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                                                                Batal
                                                            </button>
                                                            <button type="submit"
                                                                    class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition shadow">
                                                                Simpan Keputusan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <span class="text-4xl">📭</span>
                                            <p class="font-medium text-gray-500">Tidak ada data logbook yang cocok dengan kriteria pencarian.</p>
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
