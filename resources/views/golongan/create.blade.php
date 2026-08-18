<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Golongan') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">

                    <form method="POST" action="{{ route('golongan.store') }}">
                        @csrf

                        {{-- Input Nama Golongan --}}
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="nama_golongan">Nama Golongan</label>
                            <input type="text"
                                   name="nama_golongan"
                                   id="nama_golongan"
                                   value="{{ old('nama_golongan') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm text-gray-900 @error('nama_golongan') border-red-500 @enderror"
                                   placeholder="Contoh: III/a, IV/b">
                            @error('nama_golongan')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Input Nama Pangkat --}}
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="nama_pangkat">Nama Pangkat / Ruang</label>
                            <input type="text"
                                   name="nama_pangkat"
                                   id="nama_pangkat"
                                   value="{{ old('nama_pangkat') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm text-gray-900 @error('nama_pangkat') border-red-500 @enderror"
                                   placeholder="Contoh: Penata Muda, Pembina">
                            @error('nama_pangkat')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Input Keterangan --}}
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="keterangan">Keterangan</label>
                            <input type="text"
                                   name="keterangan"
                                   id="keterangan"
                                   value="{{ old('keterangan') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm text-gray-900 @error('keterangan') border-red-500 @enderror"
                                   placeholder="Contoh: PNS Ruang A, atau kosongkan jika tidak ada">
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
                            
                            <a href="{{ route('golongan.index') }}"
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