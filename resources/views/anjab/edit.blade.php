<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('anjab.show', $anjab) }}" class="text-sm text-blue-600 hover:underline">← Kembali ke Detail Anjab</a>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">Edit Analisis Jabatan: {{ $anjab->jabatan->nama_jabatan ?? '-' }}</h1>
                <p class="text-sm text-gray-600">Instrumen 17 butir standar PermenPAN-RB No. 1 Tahun 2020</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <form method="POST" action="{{ route('anjab.update', $anjab) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Bagian 1: Identitas --}}
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-base font-bold text-gray-900 mb-3">1. Identitas Jabatan & Unit Kerja</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Jabatan</label>
                                <input type="text" disabled value="{{ $anjab->jabatan->nama_jabatan ?? '-' }} ({{ $anjab->jabatan->kode_jabatan ?? '-' }})" class="w-full text-sm rounded-lg bg-gray-100 border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Unit Kerja Penempatan</label>
                                <select name="unit_kerja_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    @foreach($unitKerjas as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_kerja_id', $anjab->unit_kerja_id) == $u->id ? 'selected' : '' }}>
                                            {{ $u->kode_unit }} - {{ $u->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kode Anjab</label>
                                <input type="text" name="kode_anjab" value="{{ old('kode_anjab', $anjab->kode_anjab) }}" class="w-full text-sm rounded-lg border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kelas Jabatan (Grade)</label>
                                <input type="number" name="kelas_jabatan" min="1" max="17" value="{{ old('kelas_jabatan', $anjab->kelas_jabatan ?? $anjab->jabatan->kelas_jabatan) }}" class="w-full text-sm rounded-lg border-gray-300">
                            </div>
                        </div>
                    </div>

                    {{-- Bagian 2: Ikhtisar & Kualifikasi --}}
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-base font-bold text-gray-900 mb-3">2. Ikhtisar & Kualifikasi Jabatan</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Ikhtisar Jabatan (Ringkasan Tugas Pokok)</label>
                                <textarea name="ikhtisar_jabatan" rows="3" class="w-full text-sm rounded-lg border-gray-300">{{ old('ikhtisar_jabatan', $anjab->ikhtisar_jabatan) }}</textarea>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kualifikasi Pendidikan</label>
                                    <input type="text" name="kualifikasi_pendidikan" value="{{ old('kualifikasi_pendidikan', $anjab->kualifikasi_pendidikan) }}" class="w-full text-sm rounded-lg border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kualifikasi Pelatihan/Diklat</label>
                                    <input type="text" name="kualifikasi_pelatihan" value="{{ old('kualifikasi_pelatihan', $anjab->kualifikasi_pelatihan) }}" class="w-full text-sm rounded-lg border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pengalaman Kerja</label>
                                    <input type="text" name="kualifikasi_pengalaman" value="{{ old('kualifikasi_pengalaman', $anjab->kualifikasi_pengalaman) }}" class="w-full text-sm rounded-lg border-gray-300">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian 3: Bahan & Perangkat Kerja --}}
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-base font-bold text-gray-900 mb-3">3. Bahan & Perangkat Kerja</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Bahan Kerja</label>
                                <textarea name="bahan_kerja" rows="4" class="w-full text-sm rounded-lg border-gray-300">{{ old('bahan_kerja', $anjab->bahan_kerja) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Perangkat Kerja</label>
                                <textarea name="perangkat_kerja" rows="4" class="w-full text-sm rounded-lg border-gray-300">{{ old('perangkat_kerja', $anjab->perangkat_kerja) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian 4: Tanggung Jawab & Wewenang --}}
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-base font-bold text-gray-900 mb-3">4. Tanggung Jawab & Wewenang</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tanggung Jawab</label>
                                <textarea name="tanggung_jawab" rows="4" class="w-full text-sm rounded-lg border-gray-300">{{ old('tanggung_jawab', $anjab->tanggung_jawab) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Wewenang</label>
                                <textarea name="wewenang" rows="4" class="w-full text-sm rounded-lg border-gray-300">{{ old('wewenang', $anjab->wewenang) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian 5: Korelasi, Lingkungan & Resiko --}}
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-base font-bold text-gray-900 mb-3">5. Korelasi, Kondisi Lingkungan & Resiko</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Korelasi Jabatan</label>
                                <textarea name="korelasi_jabatan" rows="3" class="w-full text-sm rounded-lg border-gray-300">{{ old('korelasi_jabatan', $anjab->korelasi_jabatan) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kondisi Lingkungan</label>
                                <textarea name="kondisi_lingkungan" rows="3" class="w-full text-sm rounded-lg border-gray-300">{{ old('kondisi_lingkungan', $anjab->kondisi_lingkungan) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Resiko Bahaya</label>
                                <textarea name="resiko_bahaya" rows="3" class="w-full text-sm rounded-lg border-gray-300">{{ old('resiko_bahaya', $anjab->resiko_bahaya) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian 6: Syarat Jabatan & Status --}}
                    <div class="space-y-4">
                        <h2 class="text-base font-bold text-gray-900">6. Syarat Jabatan (Psikologis & Fisik)</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Bakat Kerja</label>
                                <input type="text" name="syarat_bakat" value="{{ old('syarat_bakat', $anjab->syarat_bakat) }}" class="w-full text-sm rounded-lg border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Temperamen Kerja</label>
                                <input type="text" name="syarat_temperamen" value="{{ old('syarat_temperamen', $anjab->syarat_temperamen) }}" class="w-full text-sm rounded-lg border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Minat Kerja</label>
                                <input type="text" name="syarat_minat" value="{{ old('syarat_minat', $anjab->syarat_minat) }}" class="w-full text-sm rounded-lg border-gray-300">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Prestasi yang Diharapkan</label>
                                <input type="text" name="prestasi_diharapkan" value="{{ old('prestasi_diharapkan', $anjab->prestasi_diharapkan) }}" class="w-full text-sm rounded-lg border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status Dokumen</label>
                                <select name="status" class="w-full text-sm rounded-lg border-gray-300">
                                    <option value="draft" {{ old('status', $anjab->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="disetujui" {{ old('status', $anjab->status) == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('anjab.show', $anjab) }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 transition">
                            💾 Simpan Perubahan Anjab
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
