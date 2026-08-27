<x-app-layout>

<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Riwayat Pendidikan
        </h2>
        <a href="{{ auth()->user()->hasRole('pegawai') ? route('pegawai.show', auth()->user()->pegawai_id) : (request('pegawai_id') ? route('pegawai.show', request('pegawai_id')) : route('riwayat-pendidikan.index')) }}"
           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
            ← Kembali
        </a>
    </div>
</x-slot>

<div class="py-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded p-6">

            <form action="{{ route('riwayat-pendidikan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @if(auth()->user()->hasRole('pegawai') && auth()->user()->pegawai_id)
                    <input type="hidden" name="pegawai_id" value="{{ auth()->user()->pegawai_id }}">
                @else
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Pegawai</label>
                        <select name="pegawai_id" class="w-full border rounded px-3 py-2" required>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($pegawai as $p)
                                <option value="{{ $p->id }}" {{ old('pegawai_id', request('pegawai_id')) == $p->id ? 'selected' : '' }}>
                                    {{ $p->nip }} - {{ $p->nama_lengkap ?? $p->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

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