<div class="space-y-6 mt-6 border-t pt-6">
    <h3 class="text-lg font-semibold text-gray-800">Riwayat Pendidikan</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 text-sm font-medium">Jenjang</label>
            <select name="pendidikan[jenjang]" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">-- Pilih Jenjang --</option>
                @foreach(['SMA', 'D3', 'S1', 'S2', 'S3'] as $j)
                    <option value="{{ $j }}" {{ old('pendidikan.jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">Institusi</label>
            <input type="text" name="pendidikan[institusi]" value="{{ old('pendidikan.institusi') }}" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Nama Sekolah/Universitas">
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">Fakultas</label>
            <input type="text" name="pendidikan[fakultas]" value="{{ old('pendidikan.fakultas') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">Jurusan</label>
            <input type="text" name="pendidikan[jurusan]" value="{{ old('pendidikan.jurusan') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">Tahun Lulus</label>
            <input type="number" name="pendidikan[tahun_lulus]" value="{{ old('pendidikan.tahun_lulus') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">File Ijazah (PDF/Gambar)</label>
            <input type="file" name="pendidikan_ijazah" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
    </div>
</div>