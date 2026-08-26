<div class="mt-6 border-t pt-6 space-y-4">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Riwayat Pendidikan</h3>
        <button type="button" id="add-pendidikan-btn" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition">
            + Tambah Riwayat Pendidikan
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm" id="pendidikan-table">
            <thead class="bg-gray-50 text-gray-700 font-medium">
                <tr>
                    <th class="px-3 py-2 text-left w-1/6">Jenjang</th>
                    <th class="px-3 py-2 text-left w-1/4">Institusi/Sekolah</th>
                    <th class="px-3 py-2 text-left">Fakultas</th>
                    <th class="px-3 py-2 text-left">Jurusan</th>
                    <th class="px-3 py-2 text-center w-24">Tahun Lulus</th>
                    <th class="px-3 py-2 text-left">File Ijazah</th>
                    <th class="px-3 py-2 text-center w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white" id="pendidikan-tbody">
                @php
                    $pendidikans = isset($pegawai) ? $pegawai->riwayatPendidikan : collect();
                @endphp
                @forelse($pendidikans as $index => $rp)
                    <tr class="pendidikan-row" data-index="{{ $index }}">
                        <input type="hidden" name="riwayat_pendidikan[{{ $index }}][id]" value="{{ $rp->id }}">
                        <td class="px-3 py-2">
                            <select name="riwayat_pendidikan[{{ $index }}][jenjang]" class="w-full border rounded px-2 py-1 text-xs">
                                <option value="">-- Pilih --</option>
                                @foreach(['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3', 'Profesi'] as $j)
                                    <option value="{{ $j }}" @selected($rp->jenjang == $j)>{{ $j }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_pendidikan[{{ $index }}][institusi]" value="{{ $rp->institusi }}" class="w-full border rounded px-2 py-1 text-xs">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_pendidikan[{{ $index }}][fakultas]" value="{{ $rp->fakultas }}" class="w-full border rounded px-2 py-1 text-xs">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_pendidikan[{{ $index }}][jurusan]" value="{{ $rp->jurusan }}" class="w-full border rounded px-2 py-1 text-xs">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="riwayat_pendidikan[{{ $index }}][tahun_lulus]" value="{{ $rp->tahun_lulus }}" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2 text-xs">
                            @if($rp->ijazah)
                                <div class="mb-1 text-slate-500 truncate max-w-xs">
                                    <a href="{{ route('document.preview', ['path' => $rp->ijazah]) }}" target="_blank" class="text-blue-600 underline">Lihat Ijazah</a>
                                </div>
                            @endif
                            <input type="file" name="riwayat_pendidikan[{{ $index }}][ijazah]" class="w-full text-xs">
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-pendidikan-btn">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr class="pendidikan-row" data-index="0">
                        <td class="px-3 py-2">
                            <select name="riwayat_pendidikan[0][jenjang]" class="w-full border rounded px-2 py-1 text-xs">
                                <option value="">-- Pilih --</option>
                                @foreach(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Profesor'] as $j)
                                    <option value="{{ $j }}">{{ $j }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_pendidikan[0][institusi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Sekolah/Univ">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_pendidikan[0][fakultas]" class="w-full border rounded px-2 py-1 text-xs">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_pendidikan[0][jurusan]" class="w-full border rounded px-2 py-1 text-xs">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="riwayat_pendidikan[0][tahun_lulus]" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2">
                            <input type="file" name="riwayat_pendidikan[0][ijazah]" class="w-full text-xs">
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-pendidikan-btn">Hapus</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('pendidikan-tbody');
    const addBtn = document.getElementById('add-pendidikan-btn');

    if (addBtn && tbody) {
        addBtn.addEventListener('click', function () {
            let maxIndex = -1;
            tbody.querySelectorAll('.pendidikan-row').forEach(row => {
                let idx = parseInt(row.getAttribute('data-index'));
                if (idx > maxIndex) {
                    maxIndex = idx;
                }
            });
            const newIndex = maxIndex + 1;

            const tr = document.createElement('tr');
            tr.className = 'pendidikan-row';
            tr.setAttribute('data-index', newIndex);
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <select name="riwayat_pendidikan[\${newIndex}][jenjang]" class="w-full border rounded px-2 py-1 text-xs">
                        <option value="">-- Pilih --</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                        <option value="D1">D1</option>
                        <option value="D2">D2</option>
                        <option value="D3">D3</option>
                        <option value="D4">D4</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                        <option value="Profesi">Profesi</option>
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_pendidikan[\${newIndex}][institusi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Sekolah/Univ">
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_pendidikan[\${newIndex}][fakultas]" class="w-full border rounded px-2 py-1 text-xs">
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_pendidikan[\${newIndex}][jurusan]" class="w-full border rounded px-2 py-1 text-xs">
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="riwayat_pendidikan[\${newIndex}][tahun_lulus]" class="w-full border rounded px-2 py-1 text-xs text-center">
                </td>
                <td class="px-3 py-2">
                    <input type="file" name="riwayat_pendidikan[\${newIndex}][ijazah]" class="w-full text-xs">
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-pendidikan-btn">Hapus</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        tbody.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-pendidikan-btn')) {
                const row = e.target.closest('.pendidikan-row');
                if (row) {
                    row.remove();
                }
            }
        });
    }
});
</script>