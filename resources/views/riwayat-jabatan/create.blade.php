<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Riwayat Jabatan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                    <h3 class="text-white text-lg font-semibold">
                        Form Tambah Riwayat Jabatan
                    </h3>
                    <p class="text-blue-100 text-sm">
                        Lengkapi seluruh informasi riwayat jabatan pegawai.
                    </p>
                </div>

                <div class="p-6">
                    @if ($errors->any())
                        <div class="mb-6 rounded-lg bg-red-100 border border-red-300 p-4">
                            <div class="font-semibold text-red-700 mb-2">Terjadi kesalahan:</div>
                            <ul class="list-disc ml-5 text-red-600 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('riwayat-jabatan.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Pegawai --}}
                            <div>
                                <label class="block mb-2 font-semibold">Pegawai <span class="text-red-600">*</span></label>
                                @if(auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id)
                                    @php
                                        $currentPegawai = $pegawai->firstWhere('id', auth()->user()->pegawai_id);
                                    @endphp
                                    <input type="hidden" name="pegawai_id" value="{{ auth()->user()->pegawai_id }}">
                                    <input type="text" readonly disabled value="{{ $currentPegawai ? $currentPegawai->nip . ' - ' . ($currentPegawai->nama_lengkap ?? $currentPegawai->nama) : 'Data Pegawai Anda' }}" class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-700">
                                @else
                                    <select name="pegawai_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="">-- Pilih Pegawai --</option>
                                        @foreach($pegawai as $p)
                                            <option value="{{ $p->id }}" {{ (old('pegawai_id', request('pegawai_id')) == $p->id) ? 'selected' : '' }}>
                                                {{ $p->nip }} - {{ $p->nama_lengkap ?? $p->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('pegawai_id')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Jabatan --}}
                            <div>
                                <label class="block mb-2 font-semibold">Jabatan <span class="text-red-600">*</span></label>
                                <select name="jabatan_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">-- Pilih Jabatan --</option>
                                    @foreach($jabatan as $j)
                                        <option value="{{ $j->id }}" {{ old('jabatan_id') == $j->id ? 'selected' : '' }}>
                                            {{ $j->nama_jabatan }} ({{ $j->jenis_jabatan ?? 'Struktural/Fungsional' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('jabatan_id')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Unit Kerja --}}
                            <div>
                                <label class="block mb-2 font-semibold">Unit Kerja <span class="text-red-600">*</span></label>
                                <select name="unit_kerja_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    @foreach($unit_kerja as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_kerja_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_kerja_id')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- TMT Jabatan --}}
                            <div>
                                <label class="block mb-2 font-semibold">TMT Jabatan <span class="text-red-600">*</span></label>
                                <input type="date" name="tmt_jabatan" value="{{ old('tmt_jabatan', date('Y-m-d')) }}" required class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @error('tmt_jabatan')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Nomor SK --}}
                            <div>
                                <label class="block mb-2 font-semibold">Nomor SK</label>
                                <input type="text" name="nomor_sk" value="{{ old('nomor_sk') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Nomor SK Jabatan">
                                @error('nomor_sk')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Upload File SK --}}
                            <div>
                                <label class="block mb-2 font-semibold">Upload File SK Jabatan</label>
                                <input id="file_sk" type="file" name="file_sk" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-blue-500">
                                <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG (Maks 2MB)</p>
                                @error('file_sk')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Keterangan --}}
                            <div class="md:col-span-2">
                                <label class="block mb-2 font-semibold">Keterangan</label>
                                <textarea name="keterangan" rows="3" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Catatan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                            </div>
                        </div>

                        <div class="border-t mt-8 pt-6">
                            <div class="flex justify-end gap-3">
                                @php
                                    $backPegawaiId = request('pegawai_id', auth()->user()->pegawai_id);
                                @endphp
                                @if($backPegawaiId)
                                    <a href="{{ route('pegawai.show', $backPegawaiId) }}" class="px-5 py-2 rounded-lg bg-gray-500 text-white hover:bg-gray-600">
                                        ← Kembali ke Profil
                                    </a>
                                @else
                                    <a href="{{ url()->previous() }}" class="px-5 py-2 rounded-lg bg-gray-500 text-white hover:bg-gray-600">
                                        ← Kembali
                                    </a>
                                @endif
                                <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-semibold shadow">
                                    💾 Simpan Data Jabatan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
