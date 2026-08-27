<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🏅</span> Edit Riwayat Penghargaan
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Perbarui data penghargaan / tanda jasa pegawai
                </p>
            </div>
            <a href="{{ route('riwayat-penghargaan.index') }}"
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

                <form action="{{ route('riwayat-penghargaan.update', $data->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Pegawai --}}
                    <div>
                        <label for="pegawai_id" class="block text-sm font-semibold text-gray-700 mb-1">
                            Pilih Pegawai <span class="text-red-500">*</span>
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

                    {{-- Nama Penghargaan & Jenis --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nama_penghargaan" class="block text-sm font-semibold text-gray-700 mb-1">
                                Nama Penghargaan / Tanda Jasa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_penghargaan" id="nama_penghargaan" value="{{ old('nama_penghargaan', $data->nama_penghargaan) }}" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="jenis_penghargaan" class="block text-sm font-semibold text-gray-700 mb-1">
                                Jenis Penghargaan
                            </label>
                            <input type="text" name="jenis_penghargaan" id="jenis_penghargaan" value="{{ old('jenis_penghargaan', $data->jenis_penghargaan) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- Instansi Pemberi & Tanggal Terima --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="instansi_pemberi" class="block text-sm font-semibold text-gray-700 mb-1">
                                Instansi / Lembaga Pemberi
                            </label>
                            <input type="text" name="instansi_pemberi" id="instansi_pemberi" value="{{ old('instansi_pemberi', $data->instansi_pemberi) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="tanggal_terima" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tanggal Diterima
                            </label>
                            <input type="date" name="tanggal_terima" id="tanggal_terima" value="{{ old('tanggal_terima', $data->tanggal_terima?->format('Y-m-d')) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- Nomor SK & Upload Berkas SK --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nomor_sk" class="block text-sm font-semibold text-gray-700 mb-1">
                                Nomor SK / Sertifikat
                            </label>
                            <input type="text" name="nomor_sk" id="nomor_sk" value="{{ old('nomor_sk', $data->nomor_sk) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="file_sk" class="block text-sm font-semibold text-gray-700 mb-1">
                                Ganti File Berkas SK / Piagam
                            </label>
                            <input type="file" name="file_sk" id="file_sk" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @if($data->file_sk)
                                <div class="mt-1">
                                    <a href="{{ route('document.preview', ['path' => $data->file_sk]) }}" target="_blank" class="text-xs text-blue-600 font-semibold hover:underline">
                                        Lihat berkas saat ini
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
                        <a href="{{ route('riwayat-penghargaan.index') }}"
                           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-sm transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm shadow transition">
                            💾 Perbarui Penghargaan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
