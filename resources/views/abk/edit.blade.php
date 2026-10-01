<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('abk.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke Rekap ABK</a>
                    <span class="text-gray-400">/</span>
                    <a href="{{ route('anjab.show', $anjab) }}" class="text-sm text-gray-600 hover:underline">Detail Anjab</a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">
                    Kelola Beban Kerja: {{ $anjab->jabatan->nama_jabatan ?? '-' }}
                </h1>
                <p class="text-sm text-gray-600">
                    Standar WKE: 1.250 Jam / 75.000 Menit per Tahun (Peraturan BKN No. 19/2011 & PermenPAN-RB No. 1/2020)
                </p>
            </div>
            <a href="{{ route('anjab.show', $anjab) }}" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Lihat Formulir Anjab
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ editingTugas: null }">
        <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✅</span>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">✕</button>
                </div>
            @endif

            {{-- Kartu Ringkasan Formasi Jabatan --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase">Total Beban Waktu</div>
                    <div class="text-2xl font-black text-blue-600 mt-1">
                        {{ number_format($anjab->total_waktu_beban_menit) }} <span class="text-xs font-normal text-gray-500">Menit</span>
                    </div>
                    <div class="text-xs text-gray-600 mt-0.5">{{ $anjab->total_jam_beban }} Jam Kerja Efektif</div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase">Kebutuhan Pegawai (ABK)</div>
                    <div class="text-2xl font-black text-indigo-600 mt-1">
                        {{ $anjab->kebutuhan_pegawai }} <span class="text-xs font-normal text-gray-500">Orang</span>
                    </div>
                    <div class="text-xs font-bold text-indigo-700 mt-0.5">Formasi Pembulatan: {{ $anjab->formasi_pembulatan }} Orang</div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase">Bezetting (Pegawai Riil)</div>
                    <div class="text-2xl font-black text-gray-900 mt-1">
                        {{ $anjab->bezetting }} <span class="text-xs font-normal text-gray-500">Pegawai Aktif</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $anjab->unitKerja->nama_unit ?? 'FKP UNRI' }}</div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase">Status Formasi</div>
                    <div class="text-xl font-bold mt-1">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black {{ $anjab->selisih_formasi < 0 ? 'bg-rose-100 text-rose-800' : ($anjab->selisih_formasi > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                            {{ $anjab->status_formasi }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 font-mono">Selisih: {{ $anjab->selisih_formasi > 0 ? '+' : '' }}{{ $anjab->selisih_formasi }}</div>
                </div>
            </div>

            {{-- Form Tambah Butir Tugas Baru --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span>➕</span> Tambah Butir Tugas Pokok Baru
                </h2>
                <form method="POST" action="{{ route('abk.tugas.store', $anjab) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Uraian Tugas Pokok <span class="text-red-500">*</span></label>
                        <input type="text" name="uraian_tugas" required placeholder="Contoh: Memeriksa dan merekap kehadiran harian pegawai di sistem..."
                               class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Satuan Hasil <span class="text-red-500">*</span></label>
                            <input type="text" name="satuan_hasil" required placeholder="Kegiatan / Dokumen / Berkas"
                                   class="w-full text-sm rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Norma Waktu (Menit) <span class="text-red-500">*</span></label>
                            <input type="number" step="any" min="1" name="norma_waktu_menit" required placeholder="Contoh: 60"
                                   class="w-full text-sm rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Volume 1 Tahun <span class="text-red-500">*</span></label>
                            <input type="number" step="any" min="0.1" name="volume_1_tahun" required placeholder="Contoh: 240"
                                   class="w-full text-sm rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Keterangan (Opsional)</label>
                            <input type="text" name="keterangan" placeholder="Catatan tambahan"
                                   class="w-full text-sm rounded-lg border-gray-300">
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 transition">
                            ➕ Simpan Butir Tugas
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabel Daftar Butir Tugas Eksisting --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="font-bold text-gray-800 text-base">RINCIAN BEBAN KERJA JABATAN ({{ $anjab->uraianTugas->count() }} BUTIR TUGAS)</h2>
                    <span class="text-xs text-gray-500 font-mono">Norma Waktu × Volume 1 Thn ÷ 75.000 = Kebutuhan Formasi</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-700">
                        <thead class="bg-gray-100 font-bold text-xs text-gray-600 uppercase">
                            <tr>
                                <th class="p-3 text-center">No</th>
                                <th class="p-3">Uraian Tugas Pokok</th>
                                <th class="p-3 text-center">Satuan Hasil</th>
                                <th class="p-3 text-center">Norma Waktu</th>
                                <th class="p-3 text-center">Volume (1 Thn)</th>
                                <th class="p-3 text-center">Waktu Beban</th>
                                <th class="p-3 text-center">Kebutuhan (Orang)</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($anjab->uraianTugas as $idx => $tugas)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-3 text-center font-medium text-gray-500">{{ $idx + 1 }}</td>
                                    <td class="p-3 font-semibold text-gray-900">{{ $tugas->uraian_tugas }}</td>
                                    <td class="p-3 text-center text-xs text-gray-600">{{ $tugas->satuan_hasil }}</td>
                                    <td class="p-3 text-center font-mono font-medium text-gray-800">{{ $tugas->norma_waktu_menit }} Menit</td>
                                    <td class="p-3 text-center font-mono font-medium text-gray-800">{{ $tugas->volume_1_tahun }}</td>
                                    <td class="p-3 text-center font-mono font-bold text-gray-900">{{ number_format($tugas->waktu_beban_menit) }} Menit</td>
                                    <td class="p-3 text-center font-mono font-bold text-blue-700">{{ $tugas->kebutuhan_pegawai }}</td>
                                    <td class="p-3 text-right space-x-1 whitespace-nowrap">
                                        <form method="POST" action="{{ route('abk.tugas.destroy', $tugas) }}" class="inline" onsubmit="return confirm('Hapus butir tugas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-50 rounded hover:bg-rose-100 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-gray-500">
                                        Belum ada butir tugas beban kerja yang ditambahkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($anjab->uraianTugas->isNotEmpty())
                            <tfoot class="bg-blue-50 font-bold text-gray-900 text-xs border-t-2 border-blue-200">
                                <tr>
                                    <td colspan="5" class="p-3 text-right uppercase">TOTAL WAKTU BEBAN KERJA & KEBUTUHAN FORMASI:</td>
                                    <td class="p-3 text-center font-mono text-blue-900 text-sm">{{ number_format($anjab->total_waktu_beban_menit) }} Menit</td>
                                    <td class="p-3 text-center font-mono text-blue-900 text-sm font-black">{{ $anjab->kebutuhan_pegawai }} ≈ {{ $anjab->formasi_pembulatan }} Orang</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
