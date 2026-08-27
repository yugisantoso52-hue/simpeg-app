<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🏛️</span> Edit Riwayat Organisasi
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Perbarui riwayat keanggotaan organisasi pegawai
                </p>
            </div>
            <a href="{{ route('riwayat-organisasi.index') }}"
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

                <form action="{{ route('riwayat-organisasi.update', $data->id) }}" method="POST" class="space-y-6">
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

                    {{-- Nama Organisasi & Jabatan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nama_organisasi" class="block text-sm font-semibold text-gray-700 mb-1">
                                Nama Organisasi <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_organisasi" id="nama_organisasi" value="{{ old('nama_organisasi', $data->nama_organisasi) }}" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="jabatan_organisasi" class="block text-sm font-semibold text-gray-700 mb-1">
                                Jabatan / Peran dalam Organisasi
                            </label>
                            <input type="text" name="jabatan_organisasi" id="jabatan_organisasi" value="{{ old('jabatan_organisasi', $data->jabatan_organisasi) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- Periode & Status Aktif --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <div>
                            <label for="tahun_mulai" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tahun Mulai
                            </label>
                            <input type="number" name="tahun_mulai" id="tahun_mulai" value="{{ old('tahun_mulai', $data->tahun_mulai) }}" min="1950" max="{{ date('Y') + 1 }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="tahun_selesai" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tahun Selesai
                            </label>
                            <input type="number" name="tahun_selesai" id="tahun_selesai" value="{{ old('tahun_selesai', $data->tahun_selesai) }}" min="1950" max="{{ date('Y') + 1 }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="pt-5">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="masih_aktif" value="1" @checked(old('masih_aktif', $data->masih_aktif))
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Masih Aktif Saat Ini</span>
                            </label>
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
                        <a href="{{ route('riwayat-organisasi.index') }}"
                           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-sm transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm shadow transition">
                            💾 Perbarui Organisasi
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
