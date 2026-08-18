<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Riwayat Pangkat
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b bg-gradient-to-r from-indigo-600 to-blue-600">
                    <h3 class="text-lg font-semibold text-white">
                        Form Tambah Riwayat Pangkat
                    </h3>
                    <p class="text-blue-100 text-sm">
                        Lengkapi seluruh data riwayat pangkat pegawai.
                    </p>
                </div>

                <form
                    action="{{ route('riwayat-pangkat.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="formPangkat">

                    @csrf

                    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">

                        {{-- ================= LEFT ================= --}}
                        <div class="space-y-5">

                            {{-- Pegawai --}}
                            <div>
                                <label class="block font-semibold mb-2">
                                    Pegawai <span class="text-red-500">*</span>
                                </label>
                                <select
                                    name="pegawai_id"
                                    class="w-full rounded-lg border px-4 py-2 @error('pegawai_id') border-red-500 ring-red-300 @else border-gray-300 @enderror">
                                    <option value="">-- Pilih Pegawai --</option>
                                    @foreach($pegawai as $p)
                                        <option
                                            value="{{ $p->id }}"
                                            {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->nip }} - {{ $p->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pegawai_id')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Golongan --}}
                            <div>
                                <label class="block font-semibold mb-2">
                                    Golongan <span class="text-red-500">*</span>
                                </label>
                                <select
                                    name="golongan_id"
                                    class="w-full rounded-lg border px-4 py-2 @error('golongan_id') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">-- Pilih Golongan --</option>
                                    @foreach($golongan as $g)
                                        <option
                                            value="{{ $g->id }}"
                                            {{ old('golongan_id') == $g->id ? 'selected' : '' }}>
                                            {{ $g->kode_golongan }} - {{ $g->nama_golongan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('golongan_id')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- TMT Pangkat --}}
                            <div>
                                <label class="block font-semibold mb-2">
                                    TMT Pangkat
                                </label>
                                <input
                                    type="date"
                                    name="tmt"
                                    value="{{ old('tmt') }}"
                                    class="w-full rounded-lg border px-4 py-2 @error('tmt') border-red-500 @else border-gray-300 @enderror">
                                @error('tmt')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
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
                                    value="{{ old('nomor_sk') }}"
                                    placeholder="Masukkan Nomor SK..."
                                    class="w-full rounded-lg border px-4 py-2 @error('nomor_sk') border-red-500 @else border-gray-300 @enderror">
                                @error('nomor_sk')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- ================= RIGHT ================= --}}
                        <div class="space-y-5">

                            {{-- Upload File --}}
                            <div>
                                <label class="block font-semibold mb-2">
                                    Upload File SK
                                </label>
                                <input
                                    type="file"
                                    id="file_sk"
                                    name="file_sk"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full rounded-lg border px-4 py-2 border-gray-300 @error('file_sk') border-red-500 @enderror">
                                <small class="text-gray-500 block mt-1">
                                    Format: PDF, JPG, PNG (Max 2 MB)
                                </small>
                                @error('file_sk')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Preview Area --}}
                            <div
                                id="previewArea"
                                class="hidden border rounded-lg p-4 bg-gray-50">
                                <div
                                    id="previewInfo"
                                    class="mb-3 text-sm text-gray-600">
                                </div>
                                <iframe
                                    id="pdfPreview"
                                    class="hidden w-full h-80 rounded border">
                                </iframe>
                                <img
                                    id="imagePreview"
                                    class="hidden rounded border max-h-80 mx-auto">
                            </div>

                            {{-- Keterangan --}}
                            <div>
                                <label class="block font-semibold mb-2">
                                    Keterangan
                                </label>
                                <textarea
                                    name="keterangan"
                                    rows="4"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2"
                                    placeholder="Opsional...">{{ old('keterangan') }}</textarea>
                            </div>

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div class="border-t bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <a
                            href="{{ route('riwayat-pangkat.index') }}"
                            class="px-5 py-2 rounded-lg bg-gray-600 text-white hover:bg-gray-700 transition">
                            ← Kembali
                        </a>
                        <button
                            type="submit"
                            id="btnSubmit"
                            class="px-6 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 flex items-center gap-2 transition">
                            <svg
                                id="spinner"
                                class="hidden animate-spin h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24">
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4l3-3-3-3v4A10 10 0 002 12h2z"></path>
                            </svg>
                            <span id="btnText">💾 Simpan Data</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('file_sk');
        const previewArea = document.getElementById('previewArea');
        const previewInfo = document.getElementById('previewInfo');
        const pdf = document.getElementById('pdfPreview');
        const img = document.getElementById('imagePreview');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) {
                previewArea.classList.add('hidden');
                return;
            }

            previewArea.classList.remove('hidden');
            previewInfo.innerHTML = "<b>Nama :</b> " + file.name + "<br><b>Ukuran :</b> " + (file.size / 1024 / 1024).toFixed(2) + " MB";

            let url = URL.createObjectURL(file);

            if (file.type === "application/pdf") {
                pdf.src = url;
                pdf.classList.remove('hidden');
                img.classList.add('hidden');
            } else {
                img.src = url;
                img.classList.remove('hidden');
                pdf.classList.add('hidden');
            }
        });

        document.getElementById('formPangkat').addEventListener('submit', function() {
            document.getElementById('spinner').classList.remove('hidden');
            document.getElementById('btnText').innerText = 'Menyimpan...';
            document.getElementById('btnSubmit').disabled = true;
        });
    </script>

</x-app-layout>