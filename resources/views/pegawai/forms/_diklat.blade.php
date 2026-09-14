<div class="mt-6 border-t pt-6 space-y-4">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Riwayat Pendidikan dan Pelatihan (Diklat)</h3>
        <button type="button" id="add-diklat-btn" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition">
            + Tambah Riwayat Diklat
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm" id="diklat-table">
            <thead class="bg-gray-50 text-gray-700 font-medium">
                <tr>
                    <th class="px-3 py-2 text-left w-1/4">Nama Diklat</th>
                    <th class="px-3 py-2 text-left">Penyelenggara</th>
                    <th class="px-3 py-2 text-left w-32">Jenis/Kategori</th>
                    <th class="px-3 py-2 text-center w-36">Tgl Mulai</th>
                    <th class="px-3 py-2 text-center w-36">Tgl Selesai</th>
                    <th class="px-3 py-2 text-center w-20">JP</th>
                    <th class="px-3 py-2 text-center w-28">Status</th>
                    <th class="px-3 py-2 text-left">Sertifikat</th>
                    <th class="px-3 py-2 text-center w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white" id="diklat-tbody">
                @php
                    $diklats = isset($pegawai) ? $pegawai->riwayatDiklat : collect();
                @endphp
                @forelse($diklats as $index => $rd)
                    <tr class="diklat-row" data-index="{{ $index }}">
                        <input type="hidden" name="riwayat_diklat[{{ $index }}][id]" value="{{ $rd->id }}">
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_diklat[{{ $index }}][nama_diklat]" value="{{ $rd->nama_diklat }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Diklat/Pelatihan">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_diklat[{{ $index }}][penyelenggara]" value="{{ $rd->penyelenggara }}" class="w-full border rounded px-2 py-1 text-xs">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_diklat[{{ $index }}][jenis_diklat]" value="{{ $rd->jenis_diklat }}" class="w-full border rounded px-2 py-1 text-xs">
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_diklat[{{ $index }}][tanggal_mulai]" value="{{ $rd->tanggal_mulai ? \Carbon\Carbon::parse($rd->tanggal_mulai)->format('Y-m-d') : '' }}" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_diklat[{{ $index }}][tanggal_selesai]" value="{{ $rd->tanggal_selesai ? \Carbon\Carbon::parse($rd->tanggal_selesai)->format('Y-m-d') : '' }}" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="riwayat_diklat[{{ $index }}][jumlah_jam]" value="{{ $rd->jumlah_jam }}" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_diklat[{{ $index }}][status]" class="w-full border rounded px-2 py-1 text-xs">
                                <option value="Aktif" @selected($rd->status == 'Aktif')>Aktif</option>
                                <option value="Tidak Aktif" @selected($rd->status == 'Tidak Aktif')>Tidak Aktif</option>
                            </select>
                        </td>
                        <td class="px-3 py-2 text-xs">
                            @if($rd->file_sertifikat)
                                <div class="mb-1 text-slate-500 truncate max-w-xs">
                                    <a href="{{ route('document.preview', ['path' => $rd->file_sertifikat]) }}" target="_blank" class="text-blue-600 underline">Lihat Sertifikat</a>
                                </div>
                            @endif
                            <input type="file" name="riwayat_diklat[{{ $index }}][file_sertifikat]" class="w-full text-xs">
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-diklat-btn">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr class="diklat-row" data-index="0">
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_diklat[0][nama_diklat]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Diklat/Pelatihan">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_diklat[0][penyelenggara]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Penyelenggara">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_diklat[0][jenis_diklat]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Jenis">
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_diklat[0][tanggal_mulai]" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_diklat[0][tanggal_selesai]" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="riwayat_diklat[0][jumlah_jam]" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_diklat[0][status]" class="w-full border rounded px-2 py-1 text-xs">
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="file" name="riwayat_diklat[0][file_sertifikat]" class="w-full text-xs">
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-diklat-btn">Hapus</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('diklat-tbody');
    const addBtn = document.getElementById('add-diklat-btn');

    if (addBtn && tbody) {
        addBtn.addEventListener('click', function () {
            let maxIndex = -1;
            tbody.querySelectorAll('.diklat-row').forEach(row => {
                let idx = parseInt(row.getAttribute('data-index'));
                if (idx > maxIndex) {
                    maxIndex = idx;
                }
            });
            const newIndex = maxIndex + 1;

            const tr = document.createElement('tr');
            tr.className = 'diklat-row';
            tr.setAttribute('data-index', newIndex);
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_diklat[\${newIndex}][nama_diklat]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Diklat/Pelatihan">
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_diklat[\${newIndex}][penyelenggara]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Penyelenggara">
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_diklat[\${newIndex}][jenis_diklat]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Jenis">
                </td>
                <td class="px-3 py-2">
                    <input type="date" name="riwayat_diklat[\${newIndex}][tanggal_mulai]" class="w-full border rounded px-2 py-1 text-xs text-center">
                </td>
                <td class="px-3 py-2">
                    <input type="date" name="riwayat_diklat[\${newIndex}][tanggal_selesai]" class="w-full border rounded px-2 py-1 text-xs text-center">
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="riwayat_diklat[\${newIndex}][jumlah_jam]" class="w-full border rounded px-2 py-1 text-xs text-center">
                </td>
                <td class="px-3 py-2">
                    <select name="riwayat_diklat[\${newIndex}][status]" class="w-full border rounded px-2 py-1 text-xs">
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="file" name="riwayat_diklat[\${newIndex}][file_sertifikat]" class="w-full text-xs">
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-diklat-btn">Hapus</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        tbody.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-diklat-btn')) {
                const row = e.target.closest('.diklat-row');
                if (row) {
                    row.remove();
                }
            }
        });
    }
});
</script>