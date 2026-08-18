<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Riwayat Kenaikan Pangkat
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                    <h3 class="text-white text-lg font-semibold">
                        Form Tambah Riwayat Pangkat / Golongan
                    </h3>
                    <p class="text-blue-100 text-sm">
                        Lengkapi seluruh informasi riwayat kenaikan pangkat pegawai.
                    </p>
                </div>

                <div class="p-6">
                    @if ($errors->any())
                        <div class="mb-6 rounded-lg bg-red-100 border border-red-300 p-4">
                            <div class="font-semibold text-red-700 mb-2">Terjadi kesalahan.</div>
                            <ul class="list-disc ml-5 text-red-600 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('riwayat-pangkat.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Pegawai --}}
                            <div>
                                <label class="block mb-2 font-semibold">Pegawai <span class="text-red-600">*</span></label>
                                <select name="pegawai_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">-- Pilih Pegawai --</option>
                                    @foreach($pegawai as $p)
                                        <option value="{{ $p->id }}" {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->nip }} - {{ $p->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Golongan / Pangkat --}}
                            <div>
                                <label class="block mb-2 font-semibold">Golongan / Pangkat Baru <span class="text-red-600">*</span></label>
                                <select name="golongan_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">-- Pilih Golongan --</option>
                                    @foreach($golongan as $g)
                                        <option value="{{ $g->id }}" {{ old('golongan_id') == $g->id ? 'selected' : '' }}>
                                            {{ $g->nama_golongan }} ({{ $g->pangkat ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Nomor SK --}}
                            <div>
                                <label class="block mb-2 font-semibold">Nomor SK</label>
                                <input type="text" name="nomor_sk" value="{{ old('nomor_sk') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Isi jika tersedia">
                            </div>

                            {{-- Tanggal SK --}}
                            <div>
                                <label class="block mb-2 font-semibold">Tanggal SK</label>
                                <input type="date" name="tanggal_sk" value="{{ old('tanggal_sk') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            {{-- TMT Pangkat --}}
                            <div>
                                <label class="block mb-2 font-semibold">TMT Pangkat</label>
                                <input type="date" name="tmt_pangkat" value="{{ old('tmt_pangkat') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            {{-- Pejabat Penetap --}}
                            <div>
                                <label class="block mb-2 font-semibold">Pejabat Penetap SK</label>
                                <input type="text" name="pejabat_penetap" value="{{ old('pejabat_penetap') }}" placeholder="Contoh: Kepala BKN / Bupati" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            {{-- Masa Kerja Tahun & Bulan --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block mb-2 font-semibold">Masa Kerja (Thn)</label>
                                    <input type="number" name="masa_kerja_tahun" value="{{ old('masa_kerja_tahun', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block mb-2 font-semibold">Masa Kerja (Bln)</label>
                                    <input type="number" name="masa_kerja_bulan" value="{{ old('masa_kerja_bulan', 0) }}" min="0" max="11" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="block mb-2 font-semibold">Status <span class="text-red-600">*</span></label>
                                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="Aktif" {{ old('status')=='Aktif'?'selected':'' }}>Aktif</option>
                                    <option value="Tidak Aktif" {{ old('status')=='Tidak Aktif'?'selected':'' }}>Tidak Aktif</option>
                                </select>
                            </div>

                            {{-- Upload SK --}}
                            <div class="md:col-span-2">
                                <label class="block mb-2 font-semibold">Upload File SK Pangkat</label>
                                <input id="file_sk" type="file" name="file_sk" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-blue-500">
                                <p id="namaFile" class="mt-2 text-sm text-gray-500">Belum ada file dipilih</p>
                                <p class="text-xs text-gray-400 mt-1">Format yang diperbolehkan: PDF, JPG, JPEG, PNG (Maks 2MB)</p>
                            </div>
                        </div>

                        <div class="border-t mt-8 pt-6">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('riwayat-pangkat.index') }}" class="px-5 py-2 rounded-lg bg-gray-500 text-white hover:bg-gray-600">
                                    ← Kembali
                                </a>
                                <button id="btnSubmit" type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-semibold">
                                    💾 Simpan Data
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('file_sk').addEventListener('change', function(){
            let nama = this.files.length ? this.files[0].name : 'Belum ada file dipilih';
            document.getElementById('namaFile').innerText = nama;
        });

        document.getElementById('btnSubmit').addEventListener('click', function(){
            if(this.form.checkValidity()){
                this.disabled = true;
                this.innerText = '⏳ Menyimpan...';
                this.form.submit();
            }
        });
    </script>
</x-app-layout>