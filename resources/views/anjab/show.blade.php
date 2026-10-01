<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('anjab.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke Daftar Anjab</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-xs font-mono uppercase bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-bold">{{ $anjab->kode_anjab ?? 'ANJAB' }}</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">
                    {{ $anjab->jabatan->nama_jabatan ?? 'Dokumen Analisis Jabatan' }}
                </h1>
                <p class="text-sm text-gray-600">
                    Unit: <span class="font-semibold text-gray-800">{{ $anjab->unitKerja->nama_unit ?? 'Fakultas Keperawatan UNRI' }}</span> |
                    Kelas Jabatan: <span class="font-bold text-amber-700">Grade {{ $anjab->kelas_jabatan ?? $anjab->jabatan->kelas_jabatan ?? '-' }}</span> |
                    Status: <span class="uppercase text-xs font-bold px-2 py-0.5 rounded-full {{ $anjab->status === 'disetujui' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">{{ $anjab->status }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('abk.edit', $anjab) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 transition">
                    <span>🧮</span> Kelola Butir Tugas ABK
                </a>
                <a href="{{ route('anjab.print', $anjab) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow hover:bg-emerald-700 transition">
                    <span>🖨️</span> Cetak Format MenPAN-RB
                </a>
                <a href="{{ route('anjab.edit', $anjab) }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <span>✏️</span> Edit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">

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

            {{-- Kartu Ringkasan Formasi & Beban Kerja --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase">Total Butir Tugas</div>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $anjab->uraianTugas->count() }} <span class="text-sm font-normal text-gray-500">Tugas Pokok</span></div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase">Jam Kerja Efektif (JKE)</div>
                    <div class="text-2xl font-black text-blue-600 mt-1">{{ number_format($anjab->total_jam_beban, 1) }} <span class="text-sm font-normal text-gray-500">Jam/Tahun</span></div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase">Kebutuhan Pegawai (ABK)</div>
                    <div class="text-2xl font-black text-indigo-600 mt-1">{{ $anjab->kebutuhan_pegawai }} <span class="text-sm font-normal text-gray-500">≈ {{ $anjab->formasi_pembulatan }} Orang</span></div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase">Bezetting (Pegawai Riil)</div>
                    <div class="text-2xl font-black text-gray-900 mt-1 flex items-baseline justify-between">
                        <span>{{ $anjab->bezetting }} <span class="text-sm font-normal text-gray-500">Orang</span></span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold {{ $anjab->selisih_formasi < 0 ? 'bg-rose-100 text-rose-700' : ($anjab->selisih_formasi > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                            {{ $anjab->status_formasi }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Konten Formulir 17 Butir Standar PermenPAN-RB No. 1/2020 --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="font-bold text-gray-800 text-base">INFORMASI JABATAN (PERMENPAN-RB NO. 1 TAHUN 2020)</h2>
                    <span class="text-xs font-mono text-gray-500">Standar BKN No. 12/2011</span>
                </div>

                <div class="divide-y divide-gray-100 text-sm">

                    {{-- 1. Nama Jabatan --}}
                    <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="font-bold text-gray-700">1. Nama Jabatan</div>
                        <div class="md:col-span-3 text-gray-900 font-semibold">{{ $anjab->jabatan->nama_jabatan ?? '-' }} (Kode: {{ $anjab->kode_anjab ?? $anjab->jabatan->kode_jabatan ?? '-' }})</div>
                    </div>

                    {{-- 2. Unit Kerja --}}
                    <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50/50">
                        <div class="font-bold text-gray-700">2. Unit Kerja</div>
                        <div class="md:col-span-3 text-gray-900">
                            <div><strong>Satuan Kerja:</strong> Fakultas Keperawatan Universitas Riau</div>
                            <div><strong>Unit Pelaksana:</strong> {{ $anjab->unitKerja->nama_unit ?? '-' }}</div>
                        </div>
                    </div>

                    {{-- 3. Ikhtisar Jabatan --}}
                    <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="font-bold text-gray-700">3. Ikhtisar Jabatan</div>
                        <div class="md:col-span-3 text-gray-800 leading-relaxed">
                            {{ $anjab->ikhtisar_jabatan ?? '-' }}
                        </div>
                    </div>

                    {{-- 4. Kualifikasi Jabatan --}}
                    <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50/50">
                        <div class="font-bold text-gray-700">4. Kualifikasi Jabatan</div>
                        <div class="md:col-span-3 space-y-2 text-gray-800">
                            <div><strong class="text-gray-700">a. Pendidikan Formal:</strong> {{ $anjab->kualifikasi_pendidikan ?? '-' }}</div>
                            <div><strong class="text-gray-700">b. Diklat / Pelatihan:</strong> {{ $anjab->kualifikasi_pelatihan ?? '-' }}</div>
                            <div><strong class="text-gray-700">c. Pengalaman Kerja:</strong> {{ $anjab->kualifikasi_pengalaman ?? '-' }}</div>
                        </div>
                    </div>

                    {{-- 5. Tugas Pokok & Analisis Beban Kerja --}}
                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="font-bold text-gray-700">5. Tugas Pokok & Perhitungan Beban Kerja (ABK)</div>
                            <a href="{{ route('abk.edit', $anjab) }}" class="text-xs text-blue-600 font-semibold hover:underline">
                                ✏️ Kelola Rincian Tugas
                            </a>
                        </div>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-100 font-bold text-gray-700 uppercase">
                                    <tr>
                                        <th class="p-2.5 text-center">No</th>
                                        <th class="p-2.5">Uraian Tugas Pokok</th>
                                        <th class="p-2.5 text-center">Satuan Hasil</th>
                                        <th class="p-2.5 text-center">Waktu Penyelesaian</th>
                                        <th class="p-2.5 text-center">Beban 1 Tahun</th>
                                        <th class="p-2.5 text-center">Waktu Beban (Menit)</th>
                                        <th class="p-2.5 text-center">Kebutuhan Pegawai</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($anjab->uraianTugas as $i => $t)
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-2.5 text-center font-medium text-gray-500">{{ $i + 1 }}</td>
                                            <td class="p-2.5 font-medium text-gray-900">{{ $t->uraian_tugas }}</td>
                                            <td class="p-2.5 text-center">{{ $t->satuan_hasil }}</td>
                                            <td class="p-2.5 text-center font-mono">{{ $t->norma_waktu_menit }} Menit</td>
                                            <td class="p-2.5 text-center font-mono">{{ $t->volume_1_tahun }}</td>
                                            <td class="p-2.5 text-center font-mono">{{ number_format($t->waktu_beban_menit) }}</td>
                                            <td class="p-2.5 text-center font-mono font-bold text-blue-700">{{ $t->kebutuhan_pegawai }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="p-6 text-center text-gray-500">
                                                Belum ada butir tugas. Klik "Kelola Rincian Tugas" untuk menambahkan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($anjab->uraianTugas->isNotEmpty())
                                    <tfoot class="bg-blue-50 font-bold text-gray-800">
                                        <tr>
                                            <td colspan="5" class="p-2.5 text-right uppercase">Total Jam Kerja Efektif & Kebutuhan Formasi:</td>
                                            <td class="p-2.5 text-center font-mono text-blue-900">{{ number_format($anjab->total_waktu_beban_menit) }} Menit ({{ $anjab->total_jam_beban }} Jam)</td>
                                            <td class="p-2.5 text-center font-mono text-blue-900 text-sm font-black">{{ $anjab->kebutuhan_pegawai }} ≈ {{ $anjab->formasi_pembulatan }} Orang</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- 6 & 7. Bahan & Perangkat Kerja --}}
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/50">
                        <div>
                            <div class="font-bold text-gray-700 mb-2">6. Bahan Kerja</div>
                            <div class="text-gray-800 whitespace-pre-line bg-white p-3 rounded border border-gray-200 text-xs">
                                {{ $anjab->bahan_kerja ?? 'Belum diisi.' }}
                            </div>
                        </div>
                        <div>
                            <div class="font-bold text-gray-700 mb-2">7. Perangkat / Alat Kerja</div>
                            <div class="text-gray-800 whitespace-pre-line bg-white p-3 rounded border border-gray-200 text-xs">
                                {{ $anjab->perangkat_kerja ?? 'Belum diisi.' }}
                            </div>
                        </div>
                    </div>

                    {{-- 8 & 9. Tanggung Jawab & Wewenang --}}
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="font-bold text-gray-700 mb-2">8. Tanggung Jawab</div>
                            <div class="text-gray-800 whitespace-pre-line bg-gray-50 p-3 rounded border border-gray-200 text-xs">
                                {{ $anjab->tanggung_jawab ?? 'Belum diisi.' }}
                            </div>
                        </div>
                        <div>
                            <div class="font-bold text-gray-700 mb-2">9. Wewenang</div>
                            <div class="text-gray-800 whitespace-pre-line bg-gray-50 p-3 rounded border border-gray-200 text-xs">
                                {{ $anjab->wewenang ?? 'Belum diisi.' }}
                            </div>
                        </div>
                    </div>

                    {{-- 10, 11, 12. Korelasi, Lingkungan & Resiko --}}
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50/50">
                        <div>
                            <div class="font-bold text-gray-700 mb-1">10. Korelasi Jabatan</div>
                            <div class="text-xs text-gray-800 whitespace-pre-line bg-white p-2.5 rounded border border-gray-200">
                                {{ $anjab->korelasi_jabatan ?? 'Belum diisi.' }}
                            </div>
                        </div>
                        <div>
                            <div class="font-bold text-gray-700 mb-1">11. Kondisi Lingkungan</div>
                            <div class="text-xs text-gray-800 whitespace-pre-line bg-white p-2.5 rounded border border-gray-200">
                                {{ $anjab->kondisi_lingkungan ?? 'Belum diisi.' }}
                            </div>
                        </div>
                        <div>
                            <div class="font-bold text-gray-700 mb-1">12. Resiko Bahaya</div>
                            <div class="text-xs text-gray-800 whitespace-pre-line bg-white p-2.5 rounded border border-gray-200">
                                {{ $anjab->resiko_bahaya ?? 'Belum diisi.' }}
                            </div>
                        </div>
                    </div>

                    {{-- 13. Syarat Jabatan --}}
                    <div class="p-6 space-y-3">
                        <div class="font-bold text-gray-700">13. Syarat Jabatan (Kompetensi Psikologis & Fisik)</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                            <div class="p-2.5 bg-gray-50 rounded border border-gray-200">
                                <span class="font-semibold text-gray-600 block">Keterampilan Kerja:</span>
                                {{ $anjab->syarat_keterampilan ?? '-' }}
                            </div>
                            <div class="p-2.5 bg-gray-50 rounded border border-gray-200">
                                <span class="font-semibold text-gray-600 block">Bakat Kerja:</span>
                                {{ $anjab->syarat_bakat ?? '-' }}
                            </div>
                            <div class="p-2.5 bg-gray-50 rounded border border-gray-200">
                                <span class="font-semibold text-gray-600 block">Temperamen Kerja:</span>
                                {{ $anjab->syarat_temperamen ?? '-' }}
                            </div>
                            <div class="p-2.5 bg-gray-50 rounded border border-gray-200">
                                <span class="font-semibold text-gray-600 block">Minat Kerja:</span>
                                {{ $anjab->syarat_minat ?? '-' }}
                            </div>
                            <div class="p-2.5 bg-gray-50 rounded border border-gray-200">
                                <span class="font-semibold text-gray-600 block">Upaya Fisik:</span>
                                {{ $anjab->syarat_upaya_fisik ?? '-' }}
                            </div>
                            <div class="p-2.5 bg-gray-50 rounded border border-gray-200">
                                <span class="font-semibold text-gray-600 block">Kondisi Fisik:</span>
                                {{ $anjab->kondisi_fisik ?? '-' }}
                            </div>
                        </div>
                    </div>

                    {{-- 14 & 15. Prestasi & Kelas Jabatan --}}
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50/50">
                        <div>
                            <div class="font-bold text-gray-700 mb-1">14. Prestasi Kerja yang Diharapkan</div>
                            <div class="text-xs text-gray-800">{{ $anjab->prestasi_diharapkan ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-bold text-gray-700 mb-1">15. Kelas Jabatan (Job Grading)</div>
                            <div class="text-sm font-bold text-amber-700">Grade {{ $anjab->kelas_jabatan ?? $anjab->jabatan->kelas_jabatan ?? '-' }}</div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
