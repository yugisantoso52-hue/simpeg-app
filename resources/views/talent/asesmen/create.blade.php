<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manajemen-talenta.asesmen.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                        &larr; Kembali ke Katalog Asesmen
                    </a>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>➕</span> Tambah Hasil Uji Asesmen Kompetensi ASN
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Perekaman Hasil Assessment Center BKN / Penyelenggara Uji Kompetensi Terakreditasi
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <span class="font-bold block mb-1">Terdapat kesalahan pengisian data:</span>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8">
            <form action="{{ route('manajemen-talenta.asesmen.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Pilih Pegawai --}}
                <div>
                    <label for="pegawai_id" class="block text-xs font-bold text-gray-700 mb-1">Pilih ASN / Pegawai <span class="text-rose-500">*</span></label>
                    <select name="pegawai_id" id="pegawai_id" required class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                        <option value="">-- Pilih Pegawai ASN --</option>
                        @foreach($pegawais as $p)
                            <option value="{{ $p->id }}" {{ (old('pegawai_id', request('pegawai_id')) == $p->id) ? 'selected' : '' }}>
                                {{ $p->nama_lengkap ?? $p->nama }} (NIP. {{ $p->nip }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal, Nomor Surat & Penyelenggara --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="tanggal_asesmen" class="block text-xs font-bold text-gray-700 mb-1">Tanggal Asesmen <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_asesmen" id="tanggal_asesmen" value="{{ old('tanggal_asesmen', date('Y-m-d')) }}" required class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                    </div>

                    <div>
                        <label for="nomor_surat" class="block text-xs font-bold text-gray-700 mb-1">Nomor Surat Hasil Asesmen</label>
                        <input type="text" name="nomor_surat" id="nomor_surat" value="{{ old('nomor_surat') }}" placeholder="Contoh: 124/BKN/AK/2026" class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                    </div>

                    <div>
                        <label for="lembaga_penyelenggara" class="block text-xs font-bold text-gray-700 mb-1">Lembaga Penyelenggara <span class="text-rose-500">*</span></label>
                        <input type="text" name="lembaga_penyelenggara" id="lembaga_penyelenggara" value="{{ old('lembaga_penyelenggara', 'Pusat Penilaian Kompetensi ASN BKN') }}" required class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                    </div>
                </div>

                <div>
                    <label for="metode_asesmen" class="block text-xs font-bold text-gray-700 mb-1">Metode Uji Kompetensi <span class="text-rose-500">*</span></label>
                    <input type="text" name="metode_asesmen" id="metode_asesmen" value="{{ old('metode_asesmen', 'Assessment Center Komprehensif (BKN)') }}" required class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                </div>

                {{-- Dimensi Nilai (0 - 100) --}}
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                    <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider block">
                        Dimensi Kompetensi (Skala 0 - 100)
                    </span>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label for="skor_manajerial" class="block text-[11px] font-bold text-gray-600 mb-1">Manajerial</label>
                            <input type="number" step="0.01" min="0" max="100" name="skor_manajerial" id="skor_manajerial" value="{{ old('skor_manajerial') }}" placeholder="0-100" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        </div>

                        <div>
                            <label for="skor_sosio_kultural" class="block text-[11px] font-bold text-gray-600 mb-1">Sosio Kultural</label>
                            <input type="number" step="0.01" min="0" max="100" name="skor_sosio_kultural" id="skor_sosio_kultural" value="{{ old('skor_sosio_kultural') }}" placeholder="0-100" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        </div>

                        <div>
                            <label for="skor_teknis" class="block text-[11px] font-bold text-gray-600 mb-1">Teknis Jabatan</label>
                            <input type="number" step="0.01" min="0" max="100" name="skor_teknis" id="skor_teknis" value="{{ old('skor_teknis') }}" placeholder="0-100" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        </div>

                        <div>
                            <label for="skor_potensi" class="block text-[11px] font-bold text-gray-600 mb-1">Potensi / Psikometri</label>
                            <input type="number" step="0.01" min="0" max="100" name="skor_potensi" id="skor_potensi" value="{{ old('skor_potensi') }}" placeholder="0-100" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-200">
                        <div>
                            <label for="skor_total" class="block text-xs font-bold text-indigo-900 mb-1">Total Skor Komposit Asesmen (0 - 100) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" name="skor_total" id="skor_total" value="{{ old('skor_total') }}" required placeholder="Contoh: 88.50" class="w-full text-xs rounded-lg border-indigo-300 focus:border-indigo-500 focus:ring-indigo-500 py-2 font-bold text-indigo-900">
                        </div>

                        <div>
                            <label for="kategori_kelayakan" class="block text-xs font-bold text-gray-700 mb-1">Kategori Kelayakan Asesor <span class="text-rose-500">*</span></label>
                            <select name="kategori_kelayakan" id="kategori_kelayakan" required class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2">
                                <option value="Memenuhi Syarat (MS)">Memenuhi Syarat (MS)</option>
                                <option value="Masih Memenuhi Syarat (MMS)">Masih Memenuhi Syarat (MMS)</option>
                                <option value="Kurang Memenuhi Syarat (KMS)">Kurang Memenuhi Syarat (KMS)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan & Rekomendasi --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ringkasan_kompetensi" class="block text-xs font-bold text-gray-700 mb-1">Ringkasan Ulasan Kompetensi</label>
                        <textarea name="ringkasan_kompetensi" id="ringkasan_kompetensi" rows="3" placeholder="Ulasan kekuatan dan aspek kompetensi pegawai..." class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-2.5">{{ old('ringkasan_kompetensi') }}</textarea>
                    </div>

                    <div>
                        <label for="rekomendasi_pengembangan" class="block text-xs font-bold text-gray-700 mb-1">Rekomendasi Area Pengembangan</label>
                        <textarea name="rekomendasi_pengembangan" id="rekomendasi_pengembangan" rows="3" placeholder="Saran pelatihan/coaching untuk menutup kesenjangan..." class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-2.5">{{ old('rekomendasi_pengembangan') }}</textarea>
                    </div>
                </div>

                {{-- Upload Laporan PDF --}}
                <div>
                    <label for="file_laporan" class="block text-xs font-bold text-gray-700 mb-1">Unggah Berkas Laporan Hasil Asesmen (PDF, max 5MB)</label>
                    <input type="file" name="file_laporan" id="file_laporan" accept=".pdf,.jpg,.png" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('manajemen-talenta.asesmen.index') }}" class="px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-xs transition">
                        💾 Simpan &amp; Kalkulasi Ulang Talenta
                    </button>
                </div>

            </form>
        </div>

    </div>
</x-app-layout>
