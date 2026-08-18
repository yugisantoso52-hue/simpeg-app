<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Riwayat Pendidikan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white shadow rounded p-6">

                <form action="{{ route('riwayat-pendidikan.update', $data->id) }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf
                    @method('PUT')

                    {{-- Pegawai --}}
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Pegawai</label>
                        <select name="pegawai_id" class="w-full border rounded px-3 py-2" required>
                            @foreach($pegawai as $p)
                                <option value="{{ $p->id }}" {{ $data->pegawai_id == $p->id ? 'selected' : '' }}>
                                    {{ $p->nip }} - {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jenjang --}}
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Jenjang</label>
                        <select name="jenjang" class="w-full border rounded px-3 py-2" required>
                            @foreach(['SMA', 'D3', 'S1', 'S2', 'S3'] as $j)
                                <option value="{{ $j }}" {{ $data->jenjang == $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Institusi --}}
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Institusi</label>
                        <input type="text" name="institusi" value="{{ $data->institusi }}" class="w-full border rounded px-3 py-2" required>
                    </div>

                    {{-- Fakultas --}}
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Fakultas</label>
                        <input type="text" name="fakultas" value="{{ $data->fakultas }}" class="w-full border rounded px-3 py-2" placeholder="Isi jika tersedia">
                    </div>

                    {{-- Jurusan --}}
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Jurusan</label>
                        <input type="text" name="jurusan" value="{{ $data->jurusan }}" class="w-full border rounded px-3 py-2" placeholder="Isi jika tersedia">
                    </div>

                    {{-- Tahun Lulus --}}
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Tahun Lulus</label>
                        <input type="number" name="tahun_lulus" value="{{ $data->tahun_lulus }}" class="w-full border rounded px-3 py-2" placeholder="Isi jika tersedia">
                    </div>

                    {{-- File Ijazah --}}
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">File Ijazah (Kosongkan jika tidak diganti)</label>
                        @if($data->ijazah)
                            <div class="mb-2">
                                <a href="{{ asset('storage/'.$data->ijazah) }}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1 font-medium">
                                    📂 Lihat File Saat Ini
                                </a>
                            </div>
                        @endif
                        <input type="file" name="ijazah" class="w-full border rounded px-3 py-2">
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex gap-2 mt-6">
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                            Update
                        </button>
                        <a href="{{ route('riwayat-pendidikan.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                            Kembali
                        </a>
                    </div>

                </form>

            </div>
        </div>
    </div>

</x-app-layout>