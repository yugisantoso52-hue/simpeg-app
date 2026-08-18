<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Riwayat Pangkat
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-6xl mx-auto">

            <div class="bg-white shadow-xl rounded-xl overflow-hidden">

                {{-- Header Card --}}
                <div class="px-6 py-4 border-b bg-gradient-to-r from-amber-500 to-orange-600">

                    <h3 class="text-lg font-semibold text-white">
                        Edit Riwayat Pangkat
                    </h3>

                    <p class="text-orange-100 text-sm mt-1">
                        Perbarui data Riwayat Pangkat Pegawai.
                    </p>

                </div>

                {{-- FORM --}}
                <form
                    id="formPangkat"
                    action="{{ route('riwayat-pangkat.update',$data->id) }}"
                    method="POST"
                    enctype="multipart/form-data">

                    @csrf
                    @method('PUT')

                    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">

                        {{-- ================================================= --}}
                        {{-- KOLOM KIRI --}}
                        {{-- ================================================= --}}

                        <div class="space-y-5">

                            {{-- Pegawai --}}
                            <div>

                                <label class="block font-semibold mb-2">
                                    Pegawai
                                </label>

                                <select
                                    name="pegawai_id"
                                    autofocus
                                    class="w-full rounded-lg border px-4 py-2
                                    @error('pegawai_id')
                                        border-red-500 ring-red-300
                                    @else
                                        border-gray-300
                                    @enderror">

                                    <option value="">
                                        -- Pilih Pegawai --
                                    </option>

                                    @foreach($pegawai as $p)

                                        <option
                                            value="{{ $p->id }}"
                                            {{ old('pegawai_id',$data->pegawai_id)==$p->id ? 'selected' : '' }}>

                                            {{ $p->nip }}
                                            -
                                            {{ $p->nama }}

                                        </option>

                                    @endforeach

                                </select>

                                @error('pegawai_id')

                                    <p class="text-red-600 text-sm mt-1">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                            {{-- Golongan --}}
                            <div>

                                <label class="block font-semibold mb-2">
                                    Golongan
                                </label>

                                <select
                                    name="golongan_id"
                                    class="w-full rounded-lg border px-4 py-2
                                    @error('golongan_id')
                                        border-red-500
                                    @else
                                        border-gray-300
                                    @enderror">

                                    <option value="">
                                        -- Pilih Golongan --
                                    </option>

                                    @foreach($golongan as $g)

                                        <option
                                            value="{{ $g->id }}"
                                            {{ old('golongan_id',$data->golongan_id)==$g->id ? 'selected' : '' }}>

                                            {{ $g->kode_golongan }}
                                            -
                                            {{ $g->nama_golongan }}

                                        </option>

                                    @endforeach

                                </select>

                                @error('golongan_id')

                                    <p class="text-red-600 text-sm mt-1">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                            {{-- TMT --}}
                            <div>

                                <label class="block font-semibold mb-2">
                                    TMT Pangkat
                                </label>

                                <input
                                    type="date"
                                    name="tmt"
                                    value="{{ old('tmt', \Carbon\Carbon::parse($data->tmt)->format('Y-m-d')) }}"
                                    class="w-full rounded-lg border px-4 py-2
                                    @error('tmt')
                                        border-red-500
                                    @else
                                        border-gray-300
                                    @enderror">

                                @error('tmt')

                                    <p class="text-red-600 text-sm mt-1">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                            {{-- Nomor SK --}}
                            <div>

                                <label class="block font-semibold mb-2">
                                    Nomor SK
                                </label>

                                <input
                                    type="text"
                                    name="nomor_sk"
                                    value="{{ old('nomor_sk',$data->nomor_sk) }}"
                                    placeholder="Masukkan Nomor SK..."
                                    class="w-full rounded-lg border px-4 py-2
                                    @error('nomor_sk')
                                        border-red-500
                                    @else
                                        border-gray-300
                                    @enderror">

                                @error('nomor_sk')

                                    <p class="text-red-600 text-sm mt-1">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                        </div>

                        {{-- ================================================= --}}
                        {{-- KOLOM KANAN --}}
                        {{-- ================================================= --}}

                        <div class="space-y-5">

                            {{-- File SK Lama --}}
                            <div>

                                <label class="block font-semibold mb-2">
                                    File SK Saat Ini
                                </label>

                                @if($data->file_sk)

                                    <div class="border rounded-lg bg-blue-50 border-blue-200 p-4">

                                        <div class="flex items-center justify-between">

                                            <div>

                                                <div class="font-semibold text-blue-700">
                                                    📄 File SK tersedia
                                                </div>

                                                <div class="text-sm text-gray-600 mt-1">
                                                    Klik tombol di samping untuk melihat file.
                                                </div>

                                            </div>

                                            <a
                                                href="{{ asset('storage/'.$data->file_sk) }}"
                                                target="_blank"
                                                class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition">
                                                Lihat SK
                                            </a>

                                        </div>

                                    </div>

                                @else

                                    <div class="border rounded-lg bg-gray-50 border-gray-200 p-4 text-gray-500">
                                        Belum ada file SK yang diupload.
                                    </div>

                                @endif

                            </div>

                            {{-- Upload File Baru --}}
                            <div>

                                <label class="block font-semibold mb-2">
                                    Upload File SK Baru
                                </label>

                                <input
                                    type="file"
                                    id="file_sk"
                                    name="file_sk"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full rounded-lg border px-4 py-2">

                                <small class="text-gray-500">
                                    Format: PDF, JPG, JPEG, PNG (Maksimal 2 MB)
                                </small>

                                @error('file_sk')

                                    <p class="text-red-600 text-sm mt-1">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                            {{-- Preview File Baru --}}
                            <div
                                id="previewArea"
                                class="hidden border rounded-xl p-4 bg-gray-50">

                                <div
                                    id="previewInfo"
                                    class="text-sm text-gray-600 mb-4">
                                </div>

                                <iframe
                                    id="pdfPreview"
                                    class="hidden w-full h-80 rounded border">
                                </iframe>

                                <img
                                    id="imagePreview"
                                    class="hidden rounded-lg border mx-auto max-h-80">

                            </div>

                            {{-- Keterangan --}}
                            <div>

                                <label class="block font-semibold mb-2">
                                    Keterangan
                                </label>

                                <textarea
                                    name="keterangan"
                                    rows="4"
                                    placeholder="Tambahkan keterangan apabila diperlukan..."
                                    class="w-full rounded-lg border px-4 py-3
                                    @error('keterangan')
                                        border-red-500
                                    @else
                                        border-gray-300
                                    @enderror">{{ old('keterangan',$data->keterangan) }}</textarea>

                                @error('keterangan')

                                    <p class="text-red-600 text-sm mt-1">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                        </div>

                    </div>

                    {{-- ===================================================== --}}
                    {{-- FOOTER --}}
                    {{-- ===================================================== --}}

                    <div class="border-t bg-gray-50 px-6 py-5">

                        <div class="flex flex-col sm:flex-row justify-end gap-3">

                            {{-- Tombol Kembali --}}
                            <a
                                href="{{ route('riwayat-pangkat.index') }}"
                                class="inline-flex justify-center items-center px-6 py-3 rounded-lg bg-gray-600 hover:bg-gray-700 text-white font-semibold transition"
                                style="background-color: #4b5563; color: #ffffff;">
                                ← Kembali
                            </a>

                            {{-- Tombol Update Data (Diperbaiki dengan Hijau Emerald Berkontras Tinggi) --}}
                            <button
                                id="btnSubmit"
                                type="submit"
                                class="inline-flex justify-center items-center px-6 py-3 rounded-lg font-semibold transition"
                                style="background-color: #059669; color: #ffffff; border: none; cursor: pointer;">

                                <svg
                                    id="loadingSpinner"
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

                                <span id="btnText" style="color: #ffffff !important;">
                                    💾 Update Data
                                </span>

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('file_sk');
    const previewArea = document.getElementById('previewArea');
    const previewInfo = document.getElementById('previewInfo');
    const pdfPreview = document.getElementById('pdfPreview');
    const imagePreview = document.getElementById('imagePreview');

    input.addEventListener('change', function () {

        const file = this.files[0];

        if (!file) {
            previewArea.classList.add('hidden');
            pdfPreview.classList.add('hidden');
            imagePreview.classList.add('hidden');
            return;
        }

        previewArea.classList.remove('hidden');

        const size = (file.size / 1024).toFixed(2);

        previewInfo.innerHTML = `
            <div class="space-y-1">
                <div><strong>Nama File :</strong> ${file.name}</div>
                <div><strong>Ukuran :</strong> ${size} KB</div>
                <div><strong>Tipe :</strong> ${file.type}</div>
            </div>
        `;

        pdfPreview.classList.add('hidden');
        imagePreview.classList.add('hidden');

        const url = URL.createObjectURL(file);

        if (file.type === 'application/pdf') {
            pdfPreview.src = url;
            pdfPreview.classList.remove('hidden');
        }
        else if (file.type.startsWith('image/')) {
            imagePreview.src = url;
            imagePreview.classList.remove('hidden');
        }

    });

});

document.getElementById('formPangkat').addEventListener('submit', function () {

    document.getElementById('loadingSpinner').classList.remove('hidden');
    document.getElementById('btnText').innerHTML = 'Menyimpan...';
    document.getElementById('btnSubmit').disabled = true;
    
    // Tetap pertahankan warna tombol saat proses loading
    document.getElementById('btnSubmit').style.backgroundColor = '#047857'; 

});

</script>

</x-app-layout>