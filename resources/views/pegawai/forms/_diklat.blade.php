<div class="space-y-6 mt-6 border-t pt-6">
    <h3 class="text-lg font-semibold text-gray-800">Riwayat Diklat</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 text-sm font-medium">Nama Diklat</label>
            <input type="text" name="diklat[nama_diklat]" value="{{ old('diklat.nama_diklat') }}" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Contoh: Pelatihan Kepemimpinan">
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">Jenis Diklat</label>
            <input type="text" name="diklat[jenis_diklat]" value="{{ old('diklat.jenis_diklat') }}" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Struktural / Teknis / Fungsional">
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">Penyelenggara</label>
            <input type="text" name="diklat[penyelenggara]" value="{{ old('diklat.penyelenggara') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">Nomor Sertifikat</label>
            <input type="text" name="diklat[nomor_sertifikat]" value="{{ old('diklat.nomor_sertifikat') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">Tanggal Mulai</label>
            <input type="date" name="diklat[tanggal_mulai]" value="{{ old('diklat.tanggal_mulai') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">Tanggal Selesai</label>
            <input type="date" name="diklat[tanggal_selesai]" value="{{ old('diklat.tanggal_selesai') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">Status</label>
            <select name="diklat[status]" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="Aktif" {{ old('diklat.status', 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="Tidak Aktif" {{ old('diklat.status') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
        </div>

        <div>
            <label class="block mb-1 text-sm font-medium">Upload Sertifikat</label>
            <input type="file" name="diklat_sertifikat" accept=".pdf,.jpg,.jpeg,.png" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
    </div>

    <div class="mt-4">
        <label class="block mb-1 text-sm font-medium">Keterangan Diklat</label>
        <textarea name="diklat[keterangan]" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Keterangan tambahan...">{{ old('diklat.keterangan') }}</textarea>
    </div>
</div>