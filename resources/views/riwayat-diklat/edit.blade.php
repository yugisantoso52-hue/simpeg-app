<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="text-2xl font-bold text-gray-800">

                    Edit Riwayat Diklat

                </h2>

                <p class="text-sm text-gray-500 mt-1">

                    Perbarui data riwayat pendidikan dan pelatihan pegawai.

                </p>

            </div>

            <a href="{{ route('riwayat-diklat.index') }}"
               class="inline-flex items-center rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-700 transition">

                ← Kembali

            </a>

        </div>

    </x-slot>

    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-xl shadow-sm border border-gray-200">

                <form
                    id="form-diklat"
                    action="{{ route('riwayat-diklat.update',$data->id) }}"
                    method="POST"
                    enctype="multipart/form-data">

                    @csrf
                    @method('PUT')

                    <div class="p-8">

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                            {{-- ===================== --}}
                            {{-- KOLOM KIRI --}}
                            {{-- ===================== --}}

                            <div class="space-y-6">

                                {{-- Pegawai --}}
                                <div>

                                    <label class="block mb-2 text-sm font-semibold text-gray-700">

                                        Pegawai
                                        <span class="text-red-600">*</span>

                                    </label>

                                    <select
                                        name="pegawai_id"
                                        class="w-full rounded-lg border @error('pegawai_id') border-red-500 @else border-gray-300 @enderror">

                                        @foreach($pegawai as $item)

                                            <option
                                                value="{{ $item->id }}"
                                                {{ old('pegawai_id',$data->pegawai_id)==$item->id ? 'selected' : '' }}>

                                                {{ $item->nip }} - {{ $item->nama }}

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
                                        value="{{ old('nama_diklat',$data->nama_diklat) }}"
                                        class="w-full rounded-lg border @error('nama_diklat') border-red-500 @else border-gray-300 @enderror">

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

                                    </label>

                                    <input
                                        type="text"
                                        name="jenis_diklat"
                                        value="{{ old('jenis_diklat',$data->jenis_diklat) }}"
                                        class="w-full rounded-lg border border-gray-300">

                                </div>

                                {{-- Penyelenggara --}}
                                <div>

                                    <label class="block mb-2 text-sm font-semibold text-gray-700">

                                        Penyelenggara

                                    </label>

                                    <input
                                        type="text"
                                        name="penyelenggara"
                                        value="{{ old('penyelenggara',$data->penyelenggara) }}"
                                        class="w-full rounded-lg border border-gray-300">

                                </div>

                            </div>

                            {{-- KOLOM KANAN DIMULAI PADA PART 2 --}}
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
                                        value="{{ old('tanggal_mulai', optional($data->tanggal_mulai)->format('Y-m-d') ?? $data->tanggal_mulai) }}"
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
                                        value="{{ old('tanggal_selesai', optional($data->tanggal_selesai)->format('Y-m-d') ?? $data->tanggal_selesai) }}"
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
                                        value="{{ old('nomor_sertifikat', $data->nomor_sertifikat) }}"
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
                                            {{ old('status', $data->status) == 'Aktif' ? 'selected' : '' }}>
                                            Aktif
                                        </option>

                                        <option value="Tidak Aktif"
                                            {{ old('status', $data->status) == 'Tidak Aktif' ? 'selected' : '' }}>
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

                        {{-- ======================= --}}
                        {{-- Keterangan --}}
                        {{-- ======================= --}}

                        <div class="mt-8">

                            <label class="block mb-2 text-sm font-semibold text-gray-700">

                                Keterangan

                            </label>

                            <textarea
                                name="keterangan"
                                rows="4"
                                class="w-full rounded-lg border @error('keterangan') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">{{ old('keterangan', $data->keterangan) }}</textarea>

                            @error('keterangan')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- ======================= --}}
                        {{-- Sertifikat Lama --}}
                        {{-- ======================= --}}

                        @if($data->file_sertifikat)

                        <div class="mt-8 rounded-xl border border-green-200 bg-green-50 p-5">

                            <h3 class="font-semibold text-green-700 mb-4">

                                Sertifikat Saat Ini

                            </h3>

                            <div class="text-sm text-gray-700 mb-4">

                                {{ basename($data->file_sertifikat) }}

                            </div>

                            @php

                                $ext = strtolower(pathinfo($data->file_sertifikat, PATHINFO_EXTENSION));

                            @endphp

                            @if(in_array($ext,['jpg','jpeg','png','webp']))

                                <img
                                    src="{{ route('document.preview', ['path' => $data->file_sertifikat]) }}"
                                    class="rounded-lg border max-h-72">

                            @elseif($ext=='pdf')

                                <iframe
                                    src="{{ route('document.preview', ['path' => $data->file_sertifikat]) }}"
                                    class="w-full h-[500px] rounded-lg border"></iframe>

                            @endif

                        </div>

                        @endif

                        {{-- ======================= --}}
                        {{-- Upload File Baru --}}
                        {{-- ======================= --}}

                        <div class="mt-8">

                            <label class="block mb-2 text-sm font-semibold text-gray-700">

                                Ganti Sertifikat

                            </label>

                            <input
                                type="file"
                                id="file_sertifikat"
                                name="file_sertifikat"
                                accept=".pdf,.jpg,.jpeg,.png"

                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm">

                            @error('file_sertifikat')

                                <p class="mt-1 text-sm text-red-600">

                                    {{ $message }}

                                </p>

                            @enderror

                            <p class="mt-2 text-xs text-gray-500">

                                Kosongkan apabila tidak ingin mengganti file.

                            </p>

                        </div>

                        {{-- Preview File Baru dibuat di Part 3 --}}
                        {{-- ======================= --}}
                        {{-- Preview File Baru --}}
                        {{-- ======================= --}}

                        <div
                            id="preview-area"
                            class="hidden mt-8 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5">

                            <h3 class="font-semibold text-gray-700 mb-4">

                                Preview File Baru

                            </h3>

                            <div
                                id="file-info"
                                class="text-sm text-gray-600 space-y-1">

                            </div>

                            <img
                                id="image-preview"
                                class="hidden mt-4 rounded-lg border max-h-72">

                            <iframe
                                id="pdf-preview"
                                class="hidden mt-4 w-full h-[500px] rounded-lg border">

                            </iframe>

                        </div>

                        {{-- ======================= --}}
                        {{-- Tombol --}}
                        {{-- ======================= --}}

                        <div class="mt-10 border-t pt-6">

                            <div class="flex flex-col sm:flex-row justify-end gap-3">

                                <a
                                    href="{{ route('riwayat-diklat.index') }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-gray-700 transition">

                                    ← Kembali

                                </a>

                                <button
                                    id="btn-submit"
                                    type="submit"
                                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">

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

                                        💾 Update Data

                                    </span>

                                </button>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

    {{-- ======================= --}}
    {{-- Javascript Enterprise --}}
    {{-- ======================= --}}

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const input = document.getElementById('file_sertifikat');

            const previewArea = document.getElementById('preview-area');

            const imagePreview = document.getElementById('image-preview');

            const pdfPreview = document.getElementById('pdf-preview');

            const fileInfo = document.getElementById('file-info');

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
                    <div><strong>Nama File :</strong> ${file.name}</div>
                    <div><strong>Ukuran :</strong> ${size} MB</div>
                    <div><strong>Tipe :</strong> ${file.type}</div>
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

            document.getElementById('btn-text').innerHTML = 'Mengupdate Data...';

        });

    </script>

    {{-- Penutup Layout dibuat pada Part 4 --}}
</x-app-layout>

