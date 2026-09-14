<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Mutasi Pegawai
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-4xl mx-auto">

            <div class="bg-white shadow rounded p-6">

                <form action="{{ route('mutasi-pegawai.update',$mutasi->id) }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label>Pegawai</label>

                        <select name="pegawai_id"
                                class="w-full border rounded p-2"
                                required>

                            @foreach($pegawai as $p)

                                <option value="{{ $p->id }}"
                                    {{ $mutasi->pegawai_id == $p->id ? 'selected' : '' }}>

                                    {{ $p->nip }} - {{ $p->nama_lengkap ?? $p->nama }}

                                </option>

                            @endforeach

                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Unit Kerja Lama</label>

                        <select name="unit_lama_id"
                                class="w-full border rounded p-2">

                            @foreach($unitKerja as $u)

                                <option value="{{ $u->id }}"
                                    {{ $mutasi->unit_lama_id == $u->id ? 'selected' : '' }}>

                                    {{ $u->nama_unit }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="mb-4">
                        <label>Unit Kerja Baru</label>

                        <select name="unit_baru_id"
                                class="w-full border rounded p-2">

                            @foreach($unitKerja as $u)

                                <option value="{{ $u->id }}"
                                    {{ $mutasi->unit_baru_id == $u->id ? 'selected' : '' }}>

                                    {{ $u->nama_unit }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="mb-4">
                        <label>Jabatan Lama</label>

                        <select name="jabatan_lama_id"
                                class="w-full border rounded p-2">

                            @foreach($jabatan as $j)

                                <option value="{{ $j->id }}"
                                    {{ $mutasi->jabatan_lama_id == $j->id ? 'selected' : '' }}>

                                    {{ $j->nama_jabatan }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="mb-4">
                        <label>Jabatan Baru</label>

                        <select name="jabatan_baru_id"
                                class="w-full border rounded p-2">

                            @foreach($jabatan as $j)

                                <option value="{{ $j->id }}"
                                    {{ $mutasi->jabatan_baru_id == $j->id ? 'selected' : '' }}>

                                    {{ $j->nama_jabatan }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="mb-4">
                        <label>TMT Mutasi</label>

                        <input type="date"
                               name="tmt"
                               value="{{ $mutasi->tmt }}"
                               class="w-full border rounded p-2">
                    </div>

                    <div class="mb-4">
                        <label>Nomor SK</label>

                        <input type="text"
                               name="nomor_sk"
                               value="{{ $mutasi->nomor_sk }}"
                               class="w-full border rounded p-2">
                    </div>

                    <div class="mb-4">

                        <label>File SK</label>

                        @if($mutasi->file_sk)

                            <div class="mb-2">
                                <a href="{{ route('document.preview', ['path' => $mutasi->file_sk]) }}"
                                   target="_blank"
                                   class="text-blue-600">
                                    Lihat File SK
                                </a>
                            </div>

                        @endif

                        <input type="file"
                               name="file_sk"
                               class="w-full border rounded p-2">

                    </div>

                    <div class="mb-4">

                        <label>Keterangan</label>

                        <textarea name="keterangan"
                                  rows="3"
                                  class="w-full border rounded p-2">{{ $mutasi->keterangan }}</textarea>

                    </div>

                    <div class="flex gap-2">

                        <button type="submit"
                                class="bg-green-600 text-white px-4 py-2 rounded">
                            Update
                        </button>

                        <a href="{{ route('mutasi-pegawai.index') }}"
                           class="bg-gray-500 text-white px-4 py-2 rounded">
                            Kembali
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>