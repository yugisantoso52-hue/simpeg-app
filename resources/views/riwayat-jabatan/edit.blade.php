<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Riwayat Jabatan
    </h2>
</x-slot>

<div class="py-6">

    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white shadow-xl rounded-xl overflow-hidden">

            {{-- Header Card --}}

            <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4">

                <h3 class="text-xl font-bold text-white">

                    Edit Riwayat Jabatan

                </h3>

                <p class="text-orange-100 text-sm mt-1">

                    Perbarui data Riwayat Jabatan Pegawai.

                </p>

            </div>

            <div class="p-6">

                {{-- Error Validation --}}

                @if ($errors->any())

                    <div class="mb-6 rounded-lg bg-red-100 border border-red-300 p-4">

                        <div class="font-semibold text-red-700 mb-2">

                            Terjadi kesalahan :

                        </div>

                        <ul class="list-disc ml-5 text-red-700 text-sm">

                            @foreach ($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif

                <form
                    action="{{ route('riwayat-jabatan.update',$data->id) }}"
                    method="POST"
                    enctype="multipart/form-data">

                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Pegawai --}}

                        <div>

                            <label class="block mb-2 font-semibold">

                                Pegawai
                                <span class="text-red-600">*</span>

                            </label>

                            <select
                                name="pegawai_id"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                                @foreach($pegawai as $p)

                                    <option
                                        value="{{ $p->id }}"
                                        {{ old('pegawai_id',$data->pegawai_id)==$p->id ? 'selected':'' }}>

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

                        {{-- Jabatan --}}

                        <div>

                            <label class="block mb-2 font-semibold">

                                Jabatan
                                <span class="text-red-600">*</span>

                            </label>

                            <select
                                name="jabatan_id"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                                @foreach($jabatan as $j)

                                    <option
                                        value="{{ $j->id }}"
                                        {{ old('jabatan_id',$data->jabatan_id)==$j->id ? 'selected':'' }}>

                                        {{ $j->nama_jabatan }}

                                    </option>

                                @endforeach

                            </select>

                            @error('jabatan_id')

                                <p class="text-red-600 text-sm mt-1">

                                    {{ $message }}

                                </p>

                            @enderror

                        </div>

                        {{-- Unit Kerja --}}

                        <div>

                            <label class="block mb-2 font-semibold">

                                Unit Kerja
                                <span class="text-red-600">*</span>

                            </label>

                            <select
                                name="unit_kerja_id"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                                @foreach($unit_kerja as $u)

                                    <option
                                        value="{{ $u->id }}"
                                        {{ old('unit_kerja_id',$data->unit_kerja_id)==$u->id ? 'selected':'' }}>

                                        {{ $u->nama_unit }}

                                    </option>

                                @endforeach

                            </select>

                            @error('unit_kerja_id')

                                <p class="text-red-600 text-sm mt-1">

                                    {{ $message }}

                                </p>

                            @enderror

                        </div>

                        {{-- Status --}}

                        <div>

                            <label class="block mb-2 font-semibold">

                                Status

                            </label>

                            <select
                                name="status"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                                <option
                                    value="Aktif"
                                    {{ old('status',$data->status)=='Aktif' ? 'selected':'' }}>

                                    Aktif

                                </option>

                                <option
                                    value="Tidak Aktif"
                                    {{ old('status',$data->status)=='Tidak Aktif' ? 'selected':'' }}>

                                    Tidak Aktif

                                </option>

                            </select>

                        </div>

                        {{-- Nomor SK --}}

                        <div>

                            <label class="block mb-2 font-semibold">

                                Nomor SK
                                <span class="text-red-600">*</span>

                            </label>

                            <input
                                type="text"
                                name="nomor_sk"
                                value="{{ old('nomor_sk',$data->nomor_sk) }}"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                            @error('nomor_sk')

                                <p class="text-red-600 text-sm mt-1">

                                    {{ $message }}

                                </p>

                            @enderror

                        </div>

                        {{-- Tanggal SK --}}

                        <div>

                            <label class="block mb-2 font-semibold">

                                Tanggal SK
                                <span class="text-red-600">*</span>

                            </label>

                            <input
                                type="date"
                                name="tanggal_sk"
                                value="{{ old('tanggal_sk',$data->tanggal_sk) }}"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                        </div>

                        {{-- TMT Jabatan --}}

                        <div>

                            <label class="block mb-2 font-semibold">

                                TMT Jabatan
                                <span class="text-red-600">*</span>

                            </label>

                            <input
                                type="date"
                                name="tmt_jabatan"
                                value="{{ old('tmt_jabatan',$data->tmt_jabatan) }}"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                        </div>

                                            {{-- Upload File SK --}}

                        <div class="md:col-span-2">

                            <label class="block mb-2 font-semibold">

                                File SK

                            </label>

                            @if($data->file_sk)

                                <div
                                    class="mb-4 p-4 rounded-lg bg-blue-50 border border-blue-200">

                                    <div class="flex items-center justify-between">

                                        <div>

                                            <p class="text-sm font-semibold text-blue-700">

                                                File SK Saat Ini

                                            </p>

                                            <p class="text-xs text-gray-500 mt-1">

                                                Klik tombol di samping untuk melihat file.

                                            </p>

                                        </div>

                                        <a
                                            href="{{ route('document.preview', ['path' => $data->file_sk]) }}"
                                            target="_blank"
                                            class="inline-flex items-center
                                                   px-4 py-2
                                                   rounded-lg
                                                   bg-blue-600
                                                   hover:bg-blue-700
                                                   text-white
                                                   text-sm
                                                   font-semibold">

                                            📄 Lihat File SK

                                        </a>

                                    </div>

                                </div>

                            @endif

                            <input
                                id="file_sk"
                                type="file"
                                name="file_sk"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="block w-full
                                       rounded-lg
                                       border-gray-300
                                       focus:border-blue-500
                                       focus:ring-blue-500">

                            @error('file_sk')

                                <p class="text-red-600 text-sm mt-1">

                                    {{ $message }}

                                </p>

                            @enderror

                            <div
                                id="previewFile"
                                class="hidden mt-4 rounded-lg border border-green-200 bg-green-50 p-4">

                                <div class="font-semibold text-green-700">

                                    File Baru

                                </div>

                                <div
                                    id="previewNama"
                                    class="text-sm text-gray-700 mt-1">

                                </div>

                            </div>

                            <p class="text-xs text-gray-500 mt-2">

                                Kosongkan jika tidak ingin mengganti File SK.

                            </p>

                        </div>

                    </div>

                    <div
                        class="mt-8
                               border-t
                               pt-6
                               flex
                               justify-end
                               gap-3">

                        <a
    href="{{ route('riwayat-jabatan.index') }}"
    class="inline-flex items-center justify-center
           px-6 py-3
           rounded-lg
           bg-gray-500
           hover:bg-gray-600
           text-white
           font-semibold
           shadow-md
           transition
           duration-200">

    ← Kembali

</a>

                        <button
    id="btnUpdate"
    type="submit"
    class="inline-flex items-center justify-center
           px-6 py-3
           rounded-lg
           bg-blue-600
           hover:bg-blue-700
           text-white
           font-semibold
           shadow-md
           transition
           duration-200">

    💾 Update Data

</button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<script>

const inputFile=document.getElementById('file_sk');

const preview=document.getElementById('previewFile');

const previewNama=document.getElementById('previewNama');

inputFile.addEventListener('change',function(){

    if(this.files.length){

        preview.classList.remove('hidden');

        previewNama.innerHTML='📎 '+this.files[0].name;

    }else{

        preview.classList.add('hidden');

        previewNama.innerHTML='';

    }

});

document.getElementById('btnUpdate').addEventListener('click',function(){

    this.disabled=true;

    this.innerHTML='⏳ Menyimpan...';

    this.form.submit();

});

</script>

</x-app-layout>