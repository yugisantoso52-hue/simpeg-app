<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>📚</span> Edit Riwayat Publikasi Ilmiah
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Perbarui data publikasi karya ilmiah dosen / pegawai
                </p>
            </div>
            <a href="{{ auth()->user()->hasRole('pegawai') ? route('pegawai.show', auth()->user()->pegawai_id) : route('riwayat-publikasi.index') }}"
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

                <form action="{{ route('riwayat-publikasi.update', $data->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Pegawai --}}
                    <div>
                        <label for="pegawai_id" class="block text-sm font-semibold text-gray-700 mb-1">
                            Pilih Pegawai / Dosen <span class="text-red-500">*</span>
                        </label>
                        <select name="pegawai_id" id="pegawai_id" required
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach($pegawai as $p)
                                <option value="{{ $p->id }}" @selected(old('pegawai_id', $data->pegawai_id) == $p->id)>
                                    {{ $p->nama_lengkap ?? $p->nama }} (NIP: {{ $p->nip ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Judul Publikasi --}}
                    <div>
                        <label for="judul_publikasi" class="block text-sm font-semibold text-gray-700 mb-1">
                            Judul Publikasi / Karya Ilmiah <span class="text-red-500">*</span>
                        </label>
                        <textarea name="judul_publikasi" id="judul_publikasi" rows="2" required
                                  class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('judul_publikasi', $data->judul_publikasi) }}</textarea>
                    </div>

                    {{-- Jenis Publikasi & Indeksasi --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="jenis_publikasi" class="block text-sm font-semibold text-gray-700 mb-1">
                                Jenis Publikasi <span class="text-red-500">*</span>
                            </label>
                            <select name="jenis_publikasi" id="jenis_publikasi" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="Jurnal" @selected(old('jenis_publikasi', $data->jenis_publikasi) == 'Jurnal')>Jurnal Ilmiah</option>
                                <option value="Prosiding" @selected(old('jenis_publikasi', $data->jenis_publikasi) == 'Prosiding')>Prosiding Konferensi / Seminar</option>
                                <option value="Buku" @selected(old('jenis_publikasi', $data->jenis_publikasi) == 'Buku')>Buku / Monograf / Buku Ajar</option>
                                <option value="Book Chapter" @selected(old('jenis_publikasi', $data->jenis_publikasi) == 'Book Chapter')>Book Chapter</option>
                                <option value="Paten" @selected(old('jenis_publikasi', $data->jenis_publikasi) == 'Paten')>Paten (Granted / Registered)</option>
                                <option value="HKI" @selected(old('jenis_publikasi', $data->jenis_publikasi) == 'HKI')>Hak Cipta / HKI</option>
                                <option value="Lainnya" @selected(old('jenis_publikasi', $data->jenis_publikasi) == 'Lainnya')>Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label for="indeksasi" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tingkat Indeksasi / Akreditasi
                            </label>
                            <select name="indeksasi" id="indeksasi"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Tidak Terindeks / Non Akreditasi --</option>
                                <option value="Scopus" @selected(old('indeksasi', $data->indeksasi) == 'Scopus')>Scopus (Q1/Q2/Q3/Q4)</option>
                                <option value="WoS" @selected(old('indeksasi', $data->indeksasi) == 'WoS')>Web of Science (WoS)</option>
                                <option value="SINTA 1" @selected(old('indeksasi', $data->indeksasi) == 'SINTA 1')>SINTA 1</option>
                                <option value="SINTA 2" @selected(old('indeksasi', $data->indeksasi) == 'SINTA 2')>SINTA 2</option>
                                <option value="SINTA 3" @selected(old('indeksasi', $data->indeksasi) == 'SINTA 3')>SINTA 3</option>
                                <option value="SINTA 4" @selected(old('indeksasi', $data->indeksasi) == 'SINTA 4')>SINTA 4</option>
                                <option value="SINTA 5" @selected(old('indeksasi', $data->indeksasi) == 'SINTA 5')>SINTA 5</option>
                                <option value="SINTA 6" @selected(old('indeksasi', $data->indeksasi) == 'SINTA 6')>SINTA 6</option>
                                <option value="Nasional Terakreditasi" @selected(old('indeksasi', $data->indeksasi) == 'Nasional Terakreditasi')>Nasional Terakreditasi</option>
                                <option value="Nasional Tidak Terakreditasi" @selected(old('indeksasi', $data->indeksasi) == 'Nasional Tidak Terakreditasi')>Nasional Tidak Terakreditasi</option>
                                <option value="Lainnya" @selected(old('indeksasi', $data->indeksasi) == 'Lainnya')>Lainnya</option>
                            </select>
                        </div>
                    </div>

                    {{-- Nama Jurnal & Penerbit --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nama_jurnal" class="block text-sm font-semibold text-gray-700 mb-1">
                                Nama Jurnal / Forum Ilmiah
                            </label>
                            <input type="text" name="nama_jurnal" id="nama_jurnal" value="{{ old('nama_jurnal', $data->nama_jurnal) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="penerbit" class="block text-sm font-semibold text-gray-700 mb-1">
                                Penerbit / Penyelenggara
                            </label>
                            <input type="text" name="penerbit" id="penerbit" value="{{ old('penerbit', $data->penerbit) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- Tahun Terbit & Volume/Nomor --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tahun_terbit" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tahun Terbit
                            </label>
                            <input type="number" name="tahun_terbit" id="tahun_terbit" value="{{ old('tahun_terbit', $data->tahun_terbit) }}" min="1950" max="{{ date('Y') + 1 }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="volume_nomor" class="block text-sm font-semibold text-gray-700 mb-1">
                                Volume & Nomor / Halaman
                            </label>
                            <input type="text" name="volume_nomor" id="volume_nomor" value="{{ old('volume_nomor', $data->volume_nomor) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- URL DOI & Upload File PDF --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="url_doi" class="block text-sm font-semibold text-gray-700 mb-1">
                                URL DOI / Link Publikasi
                            </label>
                            <input type="url" name="url_doi" id="url_doi" value="{{ old('url_doi', $data->url_doi) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="file_publikasi" class="block text-sm font-semibold text-gray-700 mb-1">
                                Ganti File Dokumen Publikasi (PDF)
                            </label>
                            <input type="file" name="file_publikasi" id="file_publikasi" accept=".pdf"
                                   class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @if($data->file_publikasi)
                                <div class="mt-1">
                                    <a href="{{ route('document.preview', ['path' => $data->file_publikasi]) }}" target="_blank" class="text-xs text-blue-600 font-semibold hover:underline">
                                        Lihat dokumen saat ini
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <label for="keterangan" class="block text-sm font-semibold text-gray-700 mb-1">
                            Catatan / Keterangan Tambahan
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="2"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('keterangan', $data->keterangan) }}</textarea>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ auth()->user()->hasRole('pegawai') ? route('pegawai.show', auth()->user()->pegawai_id) : route('riwayat-publikasi.index') }}"
                           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-sm transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm shadow transition">
                            💾 Perbarui Publikasi
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
