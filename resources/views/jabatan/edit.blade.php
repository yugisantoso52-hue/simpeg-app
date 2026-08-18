<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Jabatan') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">

                    <form method="POST" action="{{ route('jabatan.update', $jabatan->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="kode_jabatan">Kode Jabatan</label>
                            <input type="text"
                                   name="kode_jabatan"
                                   id="kode_jabatan"
                                   value="{{ old('kode_jabatan', $jabatan->kode_jabatan) }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm text-gray-900 @error('kode_jabatan') border-red-500 @enderror">
                            @error('kode_jabatan')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="nama_jabatan">Nama Jabatan</label>
                            <input type="text"
                                   name="nama_jabatan"
                                   id="nama_jabatan"
                                   value="{{ old('nama_jabatan', $jabatan->nama_jabatan) }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm text-gray-900 @error('nama_jabatan') border-red-500 @enderror">
                            @error('nama_jabatan')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="keterangan">Keterangan</label>
                            <textarea name="keterangan"
                                      id="keterangan"
                                      rows="3"
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm text-gray-900 @error('keterangan') border-red-500 @enderror">{{ old('keterangan', $jabatan->keterangan) }}</textarea>
                            @error('keterangan')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-3 border-t pt-4">
                            <button type="submit"
                                    class="text-white px-5 py-2 rounded-md text-sm font-medium shadow-sm cursor-pointer border-none"
                                    style="background-color: #16a34a;">
                                Update Data
                            </button>
                            
                            <a href="{{ route('jabatan.index') }}"
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