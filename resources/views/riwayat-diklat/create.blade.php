<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    Tambah Riwayat Diklat
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Tambahkan data pendidikan dan pelatihan pegawai.
                </p>
            </div>

            <a href="{{ route('riwayat-diklat.index') }}"
               class="inline-flex items-center rounded-lg bg-gray-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-gray-700 transition">

                ← Kembali

            </a>

        </div>
    </x-slot>

    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl border border-gray-200 shadow-lg">

                <div class="border-b border-gray-200 px-8 py-5">

                    <h3 class="text-lg font-semibold text-gray-800">

                        Form Riwayat Diklat

                    </h3>

                    <p class="text-sm text-gray-500 mt-1">

                        Field bertanda
                        <span class="text-red-600 font-bold">*</span>
                        wajib diisi.

                    </p>

                </div>

                <form
                    action="{{ route('riwayat-diklat.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="form-diklat">

                    @csrf

                    <div class="p-8">

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                            {{-- ========================= --}}
                            {{-- KOLOM KIRI --}}
                            {{-- ========================= --}}

                            <div class="space-y-6">

                                {{-- Pegawai --}}
                                <div>

                                    <label class="block mb-2 text-sm font-semibold text-gray-700">

                                        Pegawai
                                        <span class="text-red-600">*</span>

                                    </label>

                                    <select
                                        name="pegawai_id"
                                        class="w-full rounded-lg border @error('pegawai_id') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">

                                        <option value="">
                                            -- Pilih Pegawai --
                                        </option>

                                        @foreach($pegawai as $p)

                                            <option
                                                value="{{ $p->id }}"
                                                {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>

                                                {{ $p->nip }}
                                                -
                                                {{ $p->nama_lengkap ?? $p->nama }}

                                            </option>

                                        @endforeach

                                    </select>

                                    @error('pegawai_id')

                                        <p class="mt-1 text-sm text-red-600">

                                            {{ $message }}

                                        </p>

                                    @enderror

                                </div>

                                {{-- Nama Diklat --}}
                                <div>

                                    <label class="block mb-2 text-sm font-semibold text-gray-700">

                                        Nama Diklat
                                        <span class="text-red-600">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="nama_diklat"
                                        value="{{ old('nama_diklat') }}"
                                        placeholder="Contoh : Pelatihan Kepemimpinan"

                                        class="w-full rounded-lg border @error('nama_diklat') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">

                                    @error('nama_diklat')

                                        <p class="mt-1 text-sm text-red-600">

                                            {{ $message }}

                                        </p>

                                    @enderror

                                </div>

                                {{-- Jenis Diklat --}}
                                <div>

                                    <label class="block mb-2 text-sm font-semibold text-gray-700">

                                        Jenis Diklat
                                        <span class="text-red-600">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="jenis_diklat"
                                        value="{{ old('jenis_diklat') }}"
                                        placeholder="Struktural / Teknis / Fungsional"

                                        class="w-full rounded-lg border @error('jenis_diklat') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">

                                    @error('jenis_diklat')

                                        <p class="mt-1 text-sm text-red-600">

                                            {{ $message }}

                                        </p>

                                    @enderror

                                </div>

                                {{-- Penyelenggara --}}
                                <div>

                                    <label class="block mb-2 text-sm font-semibold text-gray-700">

                                        Penyelenggara
                                        <span class="text-red-600">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="penyelenggara"
                                        value="{{ old('penyelenggara') }}"
                                        placeholder="Masukkan nama penyelenggara"

                                        class="w-full rounded-lg border @error('penyelenggara') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">

                                    @error('penyelenggara')

                                        <p class="mt-1 text-sm text-red-600">

                                            {{ $message }}

                                        </p>

                                    @enderror

                                </div>

                            </div>

                            {{-- KOLOM KANAN DIMULAI DI PART 2 --}}
                            <div class="space-y-6">

                                {{-- Tanggal Mulai --}}
                                <div>

                                    <label class="block mb-2 text-sm font-semibold text-gray-700">

                                        Tanggal Mulai
                                        <span class="text-red-600">*</span>

                                    </label>

                                    <input
                                        type="date"
                                        name="tanggal_mulai"
                                        value="{{ old('tanggal_mulai') }}"
                                        class="w-full rounded-lg border @error('tanggal_mulai') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">

                                    @error('tanggal_mulai')
                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>

                                {{-- Tanggal Selesai --}}
                                <div>

                                    <label class="block mb-2 text-sm font-semibold text-gray-700">

                                        Tanggal Selesai
                                        <span class="text-red-600">*</span>

                                    </label>

                                    <input
                                        type="date"
                                        name="tanggal_selesai"
                                        value="{{ old('tanggal_selesai') }}"
                                        class="w-full rounded-lg border @error('tanggal_selesai') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">

                                    @error('tanggal_selesai')
                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>

                                {{-- Nomor Sertifikat --}}
                                <div>

                                    <label class="block mb-2 text-sm font-semibold text-gray-700">

                                        Nomor Sertifikat

                                    </label>

                                    <input
                                        type="text"
                                        name="nomor_sertifikat"
                                        value="{{ old('nomor_sertifikat') }}"
                                        placeholder="Nomor Sertifikat"

                                        class="w-full rounded-lg border @error('nomor_sertifikat') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">

                                    @error('nomor_sertifikat')
                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>

                                {{-- Status --}}
                                <div>

                                    <label class="block mb-2 text-sm font-semibold text-gray-700">

                                        Status
                                        <span class="text-red-600">*</span>

                                    </label>

                                    <select
                                        name="status"
                                        class="w-full rounded-lg border @error('status') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">

                                        <option value="Aktif"
                                            {{ old('status','Aktif')=='Aktif' ? 'selected' : '' }}>
                                            Aktif
                                        </option>

                                        <option value="Tidak Aktif"
                                            {{ old('status')=='Tidak Aktif' ? 'selected' : '' }}>
                                            Tidak Aktif
                                        </option>

                                    </select>

                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>

                            </div>

                        </div>

                        {{-- Keterangan --}}
                        <div class="mt-8">

                            <label class="block mb-2 text-sm font-semibold text-gray-700">

                                Keterangan

                            </label>

                            <textarea
                                name="keterangan"
                                rows="4"
                                placeholder="Keterangan tambahan..."

                                class="w-full rounded-lg border @error('keterangan') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">{{ old('keterangan') }}</textarea>

                            @error('keterangan')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- Upload Sertifikat --}}
                        <div class="mt-8">

                            <label class="block mb-2 text-sm font-semibold text-gray-700">

                                Upload Sertifikat

                            </label>

                            <input
                                id="file_sertifikat"
                                type="file"
                                name="file_sertifikat"
                                accept=".pdf,.jpg,.jpeg,.png"

                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm">

                            @error('file_sertifikat')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                            <p class="mt-2 text-xs text-gray-500">

                                Format: PDF, JPG, JPEG, PNG (maks. 2 MB)

                            </p>

                        </div>

                        {{-- Preview File --}}
                        <div
                            id="preview-area"
                            class="hidden mt-6 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5">

                            <h4 class="font-semibold text-gray-700 mb-3">

                                Preview File

                            </h4>

                            <div id="file-info" class="text-sm text-gray-600"></div>

                            <img
                                id="image-preview"
                                class="hidden mt-4 max-h-72 rounded-lg border">

                            <iframe
                                id="pdf-preview"
                                class="hidden mt-4 w-full h-[500px] rounded-lg border"></iframe>

                        </div>

                        {{-- Tombol dan Javascript akan dibuat pada Part 3 --}}
                        {{-- ========================= --}}
                        {{-- Tombol Aksi --}}
                        {{-- ========================= --}}

                        <div class="mt-10 border-t pt-6">

                            <div class="flex flex-col sm:flex-row justify-end gap-3">

                                <a href="{{ route('riwayat-diklat.index') }}"
                                   class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-gray-700 transition">

                                    ← Kembali

                                </a>

                                <button
                                    type="submit"
                                    id="btn-submit"
                                    class="inline-flex items-center justify-center rounded-lg bg-green-600 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-green-700 transition">

                                    <svg
                                        id="loading-spinner"
                                        class="hidden animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24">

                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4">
                                        </circle>

                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                        </path>

                                    </svg>

                                    <span id="btn-text">

                                        💾 Simpan Data

                                    </span>

                                </button>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

    {{-- ========================= --}}
    {{-- Javascript Enterprise --}}
    {{-- ========================= --}}

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const input = document.getElementById('file_sertifikat');

            const previewArea = document.getElementById('preview-area');

            const fileInfo = document.getElementById('file-info');

            const imagePreview = document.getElementById('image-preview');

            const pdfPreview = document.getElementById('pdf-preview');

            input.addEventListener('change', function (e) {

                const file = e.target.files[0];

                if (!file) {

                    previewArea.classList.add('hidden');

                    return;

                }

                previewArea.classList.remove('hidden');

                imagePreview.classList.add('hidden');

                pdfPreview.classList.add('hidden');

                const size = (file.size / 1024 / 1024).toFixed(2);

                fileInfo.innerHTML = `
                    <div class="space-y-1">
                        <div><strong>Nama File :</strong> ${file.name}</div>
                        <div><strong>Ukuran :</strong> ${size} MB</div>
                        <div><strong>Tipe :</strong> ${file.type}</div>
                    </div>
                `;

                const reader = new FileReader();

                if (file.type.startsWith('image/')) {

                    reader.onload = function (event) {

                        imagePreview.src = event.target.result;

                        imagePreview.classList.remove('hidden');

                    };

                    reader.readAsDataURL(file);

                }

                else if (file.type === 'application/pdf') {

                    reader.onload = function (event) {

                        pdfPreview.src = event.target.result;

                        pdfPreview.classList.remove('hidden');

                    };

                    reader.readAsDataURL(file);

                }

            });

        });

    </script>

    <script>

        document.getElementById('form-diklat').addEventListener('submit', function () {

            document.getElementById('btn-submit').disabled = true;

            document.getElementById('loading-spinner').classList.remove('hidden');

            document.getElementById('btn-text').innerHTML = 'Menyimpan Data...';

        });

    </script>

    {{-- Penutup Layout akan dibuat pada Part 4 --}}
</x-app-layout>
