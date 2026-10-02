<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <span>✏️</span> {{ __('Edit Master Jabatan') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                <div class="p-6">

                    <form method="POST" action="{{ route('jabatan.update', $jabatan->id) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="nama_jabatan">
                                    Nama Jabatan <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       name="nama_jabatan"
                                       id="nama_jabatan"
                                       value="{{ old('nama_jabatan', $jabatan->nama_jabatan) }}"
                                       required
                                       class="w-full border-gray-300 rounded-lg shadow-2xs focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm text-gray-900 @error('nama_jabatan') border-red-500 @enderror">
                                @error('nama_jabatan')
                                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="kode_jabatan">
                                    Kode Jabatan
                                </label>
                                <input type="text"
                                       name="kode_jabatan"
                                       id="kode_jabatan"
                                       value="{{ old('kode_jabatan', $jabatan->kode_jabatan) }}"
                                       class="w-full border-gray-300 rounded-lg shadow-2xs focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm text-gray-900 @error('kode_jabatan') border-red-500 @enderror">
                                @error('kode_jabatan')
                                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="kelompok_jabatan">
                                    Kelompok Jabatan
                                </label>
                                <select name="kelompok_jabatan" id="kelompok_jabatan" class="w-full border-gray-300 rounded-lg shadow-2xs focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm text-gray-900">
                                    <option value="">-- Pilih Kelompok Jabatan --</option>
                                    @foreach($kelompokOptions as $kel)
                                        <option value="{{ $kel }}" {{ old('kelompok_jabatan', $jabatan->kelompok_jabatan) == $kel ? 'selected' : '' }}>
                                            {{ $kel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelompok_jabatan')
                                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="kelas_jabatan">
                                    Kelas Jabatan (Grade 1 - 17)
                                </label>
                                <input type="number"
                                       name="kelas_jabatan"
                                       id="kelas_jabatan"
                                       min="1" max="17"
                                       value="{{ old('kelas_jabatan', $jabatan->kelas_jabatan) }}"
                                       class="w-full border-gray-300 rounded-lg shadow-2xs focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm text-gray-900">
                                @error('kelas_jabatan')
                                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="unit_kerja_id">
                                Unit Kerja / Satuan Organisasi Terkait
                            </label>
                            <select name="unit_kerja_id" id="unit_kerja_id" class="w-full border-gray-300 rounded-lg shadow-2xs focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm text-gray-900">
                                <option value="">-- Tidak Terikat / Terhubung ke Unit Kerja Spesifik --</option>
                                @foreach($unitKerjas as $u)
                                    <option value="{{ $u->id }}" {{ old('unit_kerja_id', $jabatan->unit_kerja_id) == $u->id ? 'selected' : '' }}>
                                        @if($u->parent_id) └─ @endif {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_kerja_id')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="ikhtisar_jabatan">
                                Ikhtisar Tugas & Fungsi Jabatan
                            </label>
                            <textarea name="ikhtisar_jabatan"
                                      id="ikhtisar_jabatan"
                                      rows="3"
                                      class="w-full border-gray-300 rounded-lg shadow-2xs focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm text-gray-900"
                                      placeholder="Ringkasan tugas pokok dan peran jabatan...">{{ old('ikhtisar_jabatan', $jabatan->ikhtisar_jabatan) }}</textarea>
                            @error('ikhtisar_jabatan')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="keterangan">
                                Keterangan Tambahan
                            </label>
                            <textarea name="keterangan"
                                      id="keterangan"
                                      rows="2"
                                      class="w-full border-gray-300 rounded-lg shadow-2xs focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm text-gray-900">{{ old('keterangan', $jabatan->keterangan) }}</textarea>
                            @error('keterangan')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-3 border-t pt-4 mt-6">
                            <button type="submit"
                                    class="text-white px-5 py-2.5 rounded-lg text-xs font-bold shadow hover:bg-emerald-700 transition cursor-pointer bg-emerald-600">
                                💾 Simpan Pembaruan
                            </button>
                            
                            <a href="{{ route('jabatan.index') }}"
                               class="text-gray-700 px-5 py-2.5 rounded-lg text-xs font-semibold border border-gray-300 hover:bg-gray-50 transition">
                                Batal
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>