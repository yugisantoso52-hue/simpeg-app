<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Tambah Riwayat Pendidikan
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded p-6">

            <form action="{{ route('riwayat-pendidikan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block mb-1 font-medium">Pegawai</label>
                    <select name="pegawai_id" class="w-full border rounded px-3 py-2" required>
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($pegawai as $p)
                            <option value="{{ $p->id }}" {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nip }} - {{ $p->nama_lengkap ?? $p->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">Jenjang</label>
                    <select name="jenjang" class="w-full border rounded px-3 py-2" required>
                        @foreach(['SMA', 'D3', 'S1', 'S2', 'S3'] as $j)
                            <option value="{{ $j }}" {{ old('jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">Institusi</label>
                    <input type="text" name="institusi" value="{{ old('institusi') }}" class="w-full border rounded px-3 py-2" required>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">Fakultas</label>
                    <input type="text" name="fakultas" value="{{ old('fakultas') }}" class="w-full border rounded px-3 py-2" placeholder="Isi jika tersedia">
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">Jurusan</label>
                    <input type="text" name="jurusan" value="{{ old('jurusan') }}" class="w-full border rounded px-3 py-2" placeholder="Isi jika tersedia">
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">Tahun Lulus</label>
                    <input type="number" name="tahun_lulus" value="{{ old('tahun_lulus') }}" class="w-full border rounded px-3 py-2" placeholder="Isi jika tersedia">
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">File Ijazah (PDF/Gambar)</label>
                    <input type="file" name="ijazah" class="w-full border rounded px-3 py-2">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Simpan
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