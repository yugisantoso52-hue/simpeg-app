<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Jenis Jabatan') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">

                    <form method="POST" action="{{ route('jenis-jabatan.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="nama_jenis_jabatan">
                                Nama Jenis Jabatan <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="nama_jenis_jabatan"
                                   id="nama_jenis_jabatan"
                                   value="{{ old('nama_jenis_jabatan') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm text-gray-900 @error('nama_jenis_jabatan') border-red-500 @enderror"
                                   placeholder="Contoh: MEDIKAL BEDAH, GAWAT DARURAT, JIWA"
                                   required>
                            @error('nama_jenis_jabatan')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="keterangan">Keterangan</label>
                            <textarea name="keterangan"
                                      id="keterangan"
                                      rows="3"
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm text-gray-900 @error('keterangan') border-red-500 @enderror"
                                      placeholder="Tambahkan keterangan opsional jika ada...">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-3 border-t pt-4">
                            <button type="submit"
                                    class="text-white px-5 py-2 rounded-md text-sm font-medium shadow-sm cursor-pointer border-none"
                                    style="background-color: #2563eb;">
                                Simpan Data
                            </button>
                            
                            <a href="{{ route('jenis-jabatan.index') }}"
                               class="text-gray-700 px-5 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50"
                               style="text-decoration: none; background-color: #ffffff;">
                                Batal
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
