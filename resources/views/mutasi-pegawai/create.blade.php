<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Mutasi Pegawai
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-4xl mx-auto">

            <div class="bg-white shadow rounded p-6">

                <form action="{{ route('mutasi-pegawai.store') }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf

                    {{-- Pegawai --}}
                    <div class="mb-4">
                        <label>Pegawai</label>

                        <select id="pegawai_id"
                                name="pegawai_id"
                                class="w-full border rounded p-2"
                                required>

                            <option value="">
                                Pilih Pegawai
                            </option>

                            @foreach($pegawai as $p)

                                <option value="{{ $p->id }}">
                                    {{ $p->nip }} - {{ $p->nama }}
                                </option>

                            @endforeach

                        </select>
                    </div>

                    {{-- Unit Lama --}}
                    <div class="mb-4">

                        <label>Unit Kerja Lama</label>

                        <input type="text"
                               id="unit_lama_nama"
                               class="w-full border rounded p-2 bg-gray-100"
                               readonly>

                        <input type="hidden"
                               id="unit_lama_id"
                               name="unit_lama_id">

                    </div>

                    {{-- Unit Baru --}}
                    <div class="mb-4">

                        <label>Unit Kerja Baru</label>

                        <select name="unit_baru_id"
                                class="w-full border rounded p-2"
                                required>

                            <option value="">
                                Pilih Unit Kerja Baru
                            </option>

                            @foreach($unitKerja as $u)

                                <option value="{{ $u->id }}">
                                    {{ $u->nama_unit }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- Jabatan Lama --}}
                    <div class="mb-4">

                        <label>Jabatan Lama</label>

                        <input type="text"
                               id="jabatan_lama_nama"
                               class="w-full border rounded p-2 bg-gray-100"
                               readonly>

                        <input type="hidden"
                               id="jabatan_lama_id"
                               name="jabatan_lama_id">

                    </div>

                    {{-- Jabatan Baru --}}
                    <div class="mb-4">

                        <label>Jabatan Baru</label>

                        <select name="jabatan_baru_id"
                                class="w-full border rounded p-2"
                                required>

                            <option value="">
                                Pilih Jabatan Baru
                            </option>

                            @foreach($jabatan as $j)

                                <option value="{{ $j->id }}">
                                    {{ $j->nama_jabatan }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- TMT --}}
                    <div class="mb-4">

                        <label>TMT Mutasi</label>

                        <input type="date"
                               name="tmt"
                               class="w-full border rounded p-2"
                               required>

                    </div>

                    {{-- Nomor SK --}}
                    <div class="mb-4">

                        <label>Nomor SK</label>

                        <input type="text"
                               name="nomor_sk"
                               class="w-full border rounded p-2">

                    </div>

                    {{-- File SK --}}
                    <div class="mb-4">

                        <label>Upload File SK</label>

                        <input type="file"
                               name="file_sk"
                               class="w-full border rounded p-2">

                    </div>

                    {{-- Keterangan --}}
                    <div class="mb-4">

                        <label>Keterangan</label>

                        <textarea name="keterangan"
                                  rows="3"
                                  class="w-full border rounded p-2"></textarea>

                    </div>

                    <div class="flex gap-2">

                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded">
                            Simpan
                        </button>

                        <a href="{{ route('mutasi-pegawai.index') }}"
                           class="px-4 py-2 bg-gray-500 text-white rounded">
                            Kembali
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

<script>

document.addEventListener('DOMContentLoaded', function(){

    const pegawai = document.getElementById('pegawai_id');

    pegawai.addEventListener('change', function(){

        if(!this.value) return;

        fetch('/pegawai-mutasi/' + this.value)

        .then(response => response.json())

        .then(data => {

            document.getElementById('unit_lama_id').value =
                data.unit_id;

            document.getElementById('unit_lama_nama').value =
                data.unit;

            document.getElementById('jabatan_lama_id').value =
                data.jabatan_id;

            document.getElementById('jabatan_lama_nama').value =
                data.jabatan;

        });

    });

});

</script>

</x-app-layout>