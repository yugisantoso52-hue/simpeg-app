<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>📊</span> Tambah Arsip SKP (Sasaran Kinerja Pegawai)
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Pengarsipan Dokumen Rencana SKP & Dokumen Evaluasi Penilaian Kinerja Tahunan
                </p>
            </div>
            <a href="{{ auth()->user()->hasRole('pegawai') ? route('pegawai.show', auth()->user()->pegawai_id) : (request('pegawai_id') ? route('pegawai.show', request('pegawai_id')) : route('riwayat-skp.index')) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 md:p-8">

                @if($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                        <div class="font-bold mb-1">Terdapat kesalahan input:</div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('riwayat-skp.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Pegawai --}}
                    @if(auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id)
                        <input type="hidden" name="pegawai_id" value="{{ auth()->user()->pegawai_id }}">
                    @else
                        <div>
                            <label for="pegawai_id" class="block text-sm font-semibold text-gray-700 mb-1">
                                Pilih Pegawai <span class="text-red-500">*</span>
                            </label>
                            <select name="pegawai_id" id="pegawai_id" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Pilih Pegawai --</option>
                                @foreach($pegawai as $p)
                                    <option value="{{ $p->id }}" @selected(old('pegawai_id', request('pegawai_id')) == $p->id)>
                                        {{ $p->nama_lengkap ?? $p->nama }} (NIP: {{ $p->nip ?? '-' }}) - {{ $p->jabatan->nama_jabatan ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Tahun SKP & Predikat Kinerja --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tahun" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tahun SKP <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="tahun" id="tahun" value="{{ old('tahun', request('tahun', now()->year)) }}" min="1990" max="2099" required
                                   class="w-full rounded-lg border-gray-300 font-mono text-sm focus:ring-blue-500 focus:border-blue-500">
                            <span class="text-[11px] text-gray-500 mt-1 block">Tahun berjalan: {{ now()->year }}, Tahun sebelumnya: {{ now()->year - 1 }}</span>
                        </div>

                        <div>
                            <label for="predikat_kinerja" class="block text-sm font-semibold text-gray-700 mb-1">
                                Predikat Kinerja Akhir Tahun
                            </label>
                            <select name="predikat_kinerja" id="predikat_kinerja"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Belum Ada Predikat (Draft / Sedang Berjalan) --</option>
                                <option value="Sangat Baik" @selected(old('predikat_kinerja') == 'Sangat Baik')>Sangat Baik</option>
                                <option value="Baik" @selected(old('predikat_kinerja') == 'Baik')>Baik</option>
                                <option value="Butuh Perbaikan" @selected(old('predikat_kinerja') == 'Butuh Perbaikan')>Butuh Perbaikan</option>
                                <option value="Kurang" @selected(old('predikat_kinerja') == 'Kurang')>Kurang</option>
                                <option value="Sangat Kurang" @selected(old('predikat_kinerja') == 'Sangat Kurang')>Sangat Kurang</option>
                            </select>
                        </div>
                    </div>

                    {{-- Pejabat Penilai --}}
                    <div>
                        <label for="pejabat_penilai" class="block text-sm font-semibold text-gray-700 mb-1">
                            Nama Pejabat Penilai Kinerja / Atasan Langsung
                        </label>
                        <input type="text" name="pejabat_penilai" id="pejabat_penilai" value="{{ old('pejabat_penilai') }}"
                               placeholder="Contoh: Dekan Fakultas Keperawatan / Wakil Dekan II / KTU"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- AREA UPLOAD 2 BERKAS UTAMA --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/70 p-5 rounded-xl border border-gray-200">
                        {{-- 1. File Rencana SKP --}}
                        <div class="space-y-2">
                            <label for="file_rencana_skp" class="block text-sm font-bold text-gray-800 flex items-center gap-1.5">
                                <span>📄</span> 1. Dokumen Rencana SKP (Awal Tahun)
                            </label>
                            <input type="file" name="file_rencana_skp" id="file_rencana_skp" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <span class="text-[11px] text-gray-500 block">Format: PDF/JPG/PNG (Maks 5MB)</span>
                        </div>

                        {{-- 2. File Evaluasi SKP --}}
                        <div class="space-y-2">
                            <label for="file_evaluasi_skp" class="block text-sm font-bold text-gray-800 flex items-center gap-1.5">
                                <span>📑</span> 2. Dokumen Evaluasi Kinerja SKP (Akhir Tahun)
                            </label>
                            <input type="file" name="file_evaluasi_skp" id="file_evaluasi_skp" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <span class="text-[11px] text-gray-500 block">Format: PDF/JPG/PNG (Maks 5MB)</span>
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <label for="keterangan" class="block text-sm font-semibold text-gray-700 mb-1">
                            Catatan / Keterangan Tambahan
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="2"
                                  placeholder="Catatan nilai perilaku, umpan balik berkelanjutan, atau catatan khusus..."
                                  class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('keterangan') }}</textarea>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('riwayat-skp.index') }}"
                           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-sm transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm shadow transition">
                            💾 Simpan Arsip SKP
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
