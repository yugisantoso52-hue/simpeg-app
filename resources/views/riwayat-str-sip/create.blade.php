<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    Tambah Riwayat STR / SIP
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Pencatatan Surat Tanda Registrasi atau Surat Izin Praktik Tenaga Kesehatan
                </p>
            </div>
            <a href="{{ route('riwayat-str-sip.index') }}"
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

                <form action="{{ route('riwayat-str-sip.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ isSeumurHidup: false }">
                    @csrf

                    {{-- Pegawai --}}
                    <div>
                        <label for="pegawai_id" class="block text-sm font-semibold text-gray-700 mb-1">
                            Pilih Pegawai / Dosen <span class="text-red-500">*</span>
                        </label>
                        <select name="pegawai_id" id="pegawai_id" required
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($pegawai as $p)
                                <option value="{{ $p->id }}" @selected(old('pegawai_id', request('pegawai_id')) == $p->id)>
                                    {{ $p->nama_lengkap ?? $p->nama }} (NIP: {{ $p->nip ?? '-' }}) - {{ $p->jenis_pegawai ?? 'Pegawai' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jenis Dokumen & Nomor Registrasi --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="jenis_dokumen" class="block text-sm font-semibold text-gray-700 mb-1">
                                Jenis Dokumen <span class="text-red-500">*</span>
                            </label>
                            <select name="jenis_dokumen" id="jenis_dokumen" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="STR" @selected(old('jenis_dokumen') == 'STR')>STR (Surat Tanda Registrasi)</option>
                                <option value="SIP" @selected(old('jenis_dokumen') == 'SIP')>SIP (Surat Izin Praktik)</option>
                                <option value="SIKP" @selected(old('jenis_dokumen') == 'SIKP')>SIKP (Surat Izin Kerja Perawat)</option>
                            </select>
                        </div>

                        <div>
                            <label for="nomor_registrasi" class="block text-sm font-semibold text-gray-700 mb-1">
                                Nomor Registrasi / Nomor STR/SIP <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nomor_registrasi" id="nomor_registrasi" value="{{ old('nomor_registrasi') }}" required
                                   placeholder="Contoh: 1401521234567890"
                                   class="w-full rounded-lg border-gray-300 font-mono text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- Kualifikasi Profesi & Instansi Penerbit --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nama_dokumen" class="block text-sm font-semibold text-gray-700 mb-1">
                                Kualifikasi / Kompetensi Profesi
                            </label>
                            <input type="text" name="nama_dokumen" id="nama_dokumen" value="{{ old('nama_dokumen') }}"
                                   placeholder="Contoh: Perawat / Ners / Spesialis Keperawatan Jiwa"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="instansi_penerbit" class="block text-sm font-semibold text-gray-700 mb-1">
                                Instansi Penerbit
                            </label>
                            <input type="text" name="instansi_penerbit" id="instansi_penerbit" value="{{ old('instansi_penerbit') }}"
                                   placeholder="Contoh: KTKI / Kemenkes RI / Dinkes Pekanbaru"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- Opsi Seumur Hidup --}}
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_seumur_hidup" value="1" x-model="isSeumurHidup" @checked(old('is_seumur_hidup'))
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 h-5 w-5">
                            <span class="ms-2.5 text-sm font-bold text-blue-900">
                                STR Berlaku Seumur Hidup (Sesuai UU Kesehatan No. 17 Tahun 2023)
                            </span>
                        </label>
                        <p class="text-xs text-blue-700 mt-1 ms-7">
                            Centang opsi ini jika STR sudah berstatus seumur hidup sehingga tidak memerlukan tanggal kedaluwarsa.
                        </p>
                    </div>

                    {{-- Tanggal Terbit & Berakhir --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tanggal_terbit" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tanggal Terbit <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_terbit" id="tanggal_terbit" value="{{ old('tanggal_terbit') }}" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div x-show="!isSeumurHidup">
                            <label for="tanggal_berakhir" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tanggal Kedaluwarsa / Berakhir <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_berakhir" id="tanggal_berakhir" value="{{ old('tanggal_berakhir') }}"
                                   :required="!isSeumurHidup"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <span class="text-[11px] text-gray-500 mt-1 block">Radar pengingat akan aktif 6 bulan sebelum tanggal ini.</span>
                        </div>
                    </div>

                    {{-- Status & Berkas Scan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">
                                Status Dokumen
                            </label>
                            <select name="status" id="status"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="Aktif" @selected(old('status') == 'Aktif')>Aktif</option>
                                <option value="Dalam Proses Perpanjangan" @selected(old('status') == 'Dalam Proses Perpanjangan')>Dalam Proses Perpanjangan</option>
                                <option value="Kedaluwarsa" @selected(old('status') == 'Kedaluwarsa')>Kedaluwarsa</option>
                            </select>
                        </div>

                        <div>
                            <label for="file_dokumen" class="block text-sm font-semibold text-gray-700 mb-1">
                                Unggah Berkas Scan (PDF / Gambar)
                            </label>
                            <input type="file" name="file_dokumen" id="file_dokumen" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <span class="text-[11px] text-gray-500 mt-1 block">Format: PDF, JPG, PNG (Maks 4MB).</span>
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <label for="keterangan" class="block text-sm font-semibold text-gray-700 mb-1">
                            Keterangan Tambahan
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="3"
                                  placeholder="Catatan tempat praktik, nomor SIP di RS/Puskesmas tertentu, dll..."
                                  class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('keterangan') }}</textarea>
                    </div>

                    {{-- Tombol Simpan --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('riwayat-str-sip.index') }}"
                           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-sm transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm shadow transition">
                            💾 Simpan Data STR / SIP
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
