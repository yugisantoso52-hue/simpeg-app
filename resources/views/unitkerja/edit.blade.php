<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Unit Kerja') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">

                    <form method="POST" action="{{ route('unit-kerja.update', $unitKerja->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="nama_unit">Nama Unit Kerja</label>
                            <input type="text"
                                   name="nama_unit"
                                   id="nama_unit"
                                   value="{{ old('nama_unit', $unitKerja->nama_unit) }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm text-gray-900 @error('nama_unit') border-red-500 @enderror">
                            @error('nama_unit')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="keterangan">Keterangan</label>
                            <textarea name="keterangan"
                                      id="keterangan"
                                      rows="3"
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm text-gray-900 @error('keterangan') border-red-500 @enderror">{{ old('keterangan', $unitKerja->keterangan) }}</textarea>
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
                            
                            <a href="{{ route('unit-kerja.index') }}"
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