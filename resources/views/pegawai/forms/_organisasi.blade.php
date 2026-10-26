<div class="mt-6 border-t pt-6 space-y-4">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Riwayat Keanggotaan Organisasi</h3>
            <p class="text-xs text-gray-500">Organisasi profesi (PPNI, AIPNI, dll.), kemasyarakatan, atau ilmiah yang diikuti pegawai</p>
        </div>
        <button type="button" id="add-organisasi-btn" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition">
            + Tambah Riwayat Organisasi
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm" id="organisasi-table">
            <thead class="bg-gray-50 text-gray-700 font-medium">
                <tr>
                    <th class="px-3 py-2 text-left w-1/3">Nama Organisasi</th>
                    <th class="px-3 py-2 text-left w-1/4">Jabatan / Peran</th>
                    <th class="px-3 py-2 text-center w-28">Tahun Mulai</th>
                    <th class="px-3 py-2 text-center w-28">Tahun Selesai</th>
                    <th class="px-3 py-2 text-center w-28">Status</th>
                    <th class="px-3 py-2 text-center w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white" id="organisasi-tbody">
                @php
                    $organisasis = isset($pegawai) ? $pegawai->riwayatOrganisasi : collect();
                @endphp
                @forelse($organisasis as $index => $org)
                    <tr class="organisasi-row" data-index="{{ $index }}">
                        <input type="hidden" name="riwayat_organisasi[{{ $index }}][id]" value="{{ $org->id }}">
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_organisasi[{{ $index }}][nama_organisasi]" value="{{ $org->nama_organisasi }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Contoh: PPNI Provinsi Riau">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_organisasi[{{ $index }}][jabatan_organisasi]" value="{{ $org->jabatan_organisasi }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Contoh: Anggota / Pengurus">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="riwayat_organisasi[{{ $index }}][tahun_mulai]" value="{{ $org->tahun_mulai }}" class="w-full border rounded px-2 py-1 text-xs text-center" placeholder="YYYY">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="riwayat_organisasi[{{ $index }}][tahun_selesai]" value="{{ $org->tahun_selesai }}" class="w-full border rounded px-2 py-1 text-xs text-center" placeholder="YYYY">
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_organisasi[{{ $index }}][masih_aktif]" class="w-full border rounded px-2 py-1 text-xs">
                                <option value="1" @selected($org->masih_aktif)>Aktif</option>
                                <option value="0" @selected(!$org->masih_aktif)>Selesai</option>
                            </select>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-organisasi-btn">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr class="organisasi-row" data-index="0">
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_organisasi[0][nama_organisasi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Contoh: PPNI Provinsi Riau">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_organisasi[0][jabatan_organisasi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Contoh: Anggota / Pengurus">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="riwayat_organisasi[0][tahun_mulai]" class="w-full border rounded px-2 py-1 text-xs text-center" placeholder="YYYY">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="riwayat_organisasi[0][tahun_selesai]" class="w-full border rounded px-2 py-1 text-xs text-center" placeholder="Kosong jika aktif">
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_organisasi[0][masih_aktif]" class="w-full border rounded px-2 py-1 text-xs">
                                <option value="1">Aktif</option>
                                <option value="0">Selesai</option>
                            </select>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-organisasi-btn">Hapus</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('organisasi-tbody');
    const addBtn = document.getElementById('add-organisasi-btn');

    if (addBtn && tbody) {
        addBtn.addEventListener('click', function () {
            let maxIndex = -1;
            tbody.querySelectorAll('.organisasi-row').forEach(row => {
                let idx = parseInt(row.getAttribute('data-index'));
                if (idx > maxIndex) {
                    maxIndex = idx;
                }
            });
            const newIndex = maxIndex + 1;

            const tr = document.createElement('tr');
            tr.className = 'organisasi-row';
            tr.setAttribute('data-index', newIndex);
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_organisasi[${newIndex}][nama_organisasi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Contoh: PPNI Provinsi Riau">
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_organisasi[${newIndex}][jabatan_organisasi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Contoh: Anggota / Pengurus">
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="riwayat_organisasi[${newIndex}][tahun_mulai]" class="w-full border rounded px-2 py-1 text-xs text-center" placeholder="YYYY">
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="riwayat_organisasi[${newIndex}][tahun_selesai]" class="w-full border rounded px-2 py-1 text-xs text-center" placeholder="YYYY">
                </td>
                <td class="px-3 py-2">
                    <select name="riwayat_organisasi[${newIndex}][masih_aktif]" class="w-full border rounded px-2 py-1 text-xs">
                        <option value="1">Aktif</option>
                        <option value="0">Selesai</option>
                    </select>
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-organisasi-btn">Hapus</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        tbody.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-organisasi-btn')) {
                const row = e.target.closest('.organisasi-row');
                if (row) {
                    row.remove();
                }
            }
        });
    }
});
</script>
