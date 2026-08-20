<div class="mt-6 border-t pt-6 space-y-4">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Riwayat Jabatan</h3>
        <button type="button" id="add-jabatan-btn" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition">
            + Tambah Riwayat Jabatan
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm" id="jabatan-table">
            <thead class="bg-gray-50 text-gray-700 font-medium">
                <tr>
                    <th class="px-3 py-2 text-left w-1/4">Jabatan</th>
                    <th class="px-3 py-2 text-left w-1/4">Unit Kerja</th>
                    <th class="px-3 py-2 text-center w-36">TMT Jabatan</th>
                    <th class="px-3 py-2 text-left">Nomor SK</th>
                    <th class="px-3 py-2 text-center w-36">Tanggal SK</th>
                    <th class="px-3 py-2 text-center w-28">Status</th>
                    <th class="px-3 py-2 text-left">File SK</th>
                    <th class="px-3 py-2 text-center w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white" id="jabatan-tbody">
                @php
                    $jabatans = isset($pegawai) ? $pegawai->riwayatJabatan : collect();
                @endphp
                @forelse($jabatans as $index => $rj)
                    <tr class="jabatan-row" data-index="{{ $index }}">
                        <input type="hidden" name="riwayat_jabatan[{{ $index }}][id]" value="{{ $rj->id }}">
                        <td class="px-3 py-2">
                            <select name="riwayat_jabatan[{{ $index }}][jabatan_id]" class="w-full border rounded px-2 py-1 text-xs" required>
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($jabatan as $j)
                                    <option value="{{ $j->id }}" @selected($rj->jabatan_id == $j->id)>{{ $j->nama_jabatan }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_jabatan[{{ $index }}][unit_kerja_id]" class="w-full border rounded px-2 py-1 text-xs" required>
                                <option value="">-- Pilih Unit Kerja --</option>
                                @foreach($unitKerja as $u)
                                    <option value="{{ $u->id }}" @selected($rj->unit_kerja_id == $u->id)>{{ $u->nama_unit }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_jabatan[{{ $index }}][tmt_jabatan]" value="{{ $rj->tmt_jabatan ? \Carbon\Carbon::parse($rj->tmt_jabatan)->format('Y-m-d') : '' }}" class="w-full border rounded px-2 py-1 text-xs text-center" required>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_jabatan[{{ $index }}][nomor_sk]" value="{{ $rj->nomor_sk }}" class="w-full border rounded px-2 py-1 text-xs">
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_jabatan[{{ $index }}][tanggal_sk]" value="{{ $rj->tanggal_sk ? \Carbon\Carbon::parse($rj->tanggal_sk)->format('Y-m-d') : '' }}" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_jabatan[{{ $index }}][status]" class="w-full border rounded px-2 py-1 text-xs" required>
                                <option value="aktif" @selected(strtolower($rj->status) == 'aktif')>Aktif</option>
                                <option value="riwayat" @selected(strtolower($rj->status) == 'riwayat')>Riwayat</option>
                            </select>
                        </td>
                        <td class="px-3 py-2 text-xs">
                            @if($rj->file_sk)
                                <div class="mb-1 text-slate-500 truncate max-w-xs">
                                    <a href="{{ route('document.preview', ['path' => $rj->file_sk]) }}" target="_blank" class="text-blue-600 underline">Lihat SK</a>
                                </div>
                            @endif
                            <input type="file" name="riwayat_jabatan[{{ $index }}][file_sk]" class="w-full text-xs">
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-jabatan-btn">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr class="jabatan-row" data-index="0">
                        <td class="px-3 py-2">
                            <select name="riwayat_jabatan[0][jabatan_id]" class="w-full border rounded px-2 py-1 text-xs" required>
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($jabatan as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama_jabatan }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_jabatan[0][unit_kerja_id]" class="w-full border rounded px-2 py-1 text-xs" required>
                                <option value="">-- Pilih Unit Kerja --</option>
                                @foreach($unitKerja as $u)
                                    <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_jabatan[0][tmt_jabatan]" class="w-full border rounded px-2 py-1 text-xs text-center" required>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_jabatan[0][nomor_sk]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nomor SK">
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_jabatan[0][tanggal_sk]" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_jabatan[0][status]" class="w-full border rounded px-2 py-1 text-xs" required>
                                <option value="aktif">Aktif</option>
                                <option value="riwayat">Riwayat</option>
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="file" name="riwayat_jabatan[0][file_sk]" class="w-full text-xs">
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-jabatan-btn">Hapus</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('jabatan-tbody');
    const addBtn = document.getElementById('add-jabatan-btn');
    const jabatanOptions = `@foreach($jabatan as $j)<option value="{{ $j->id }}">{{ $j->nama_jabatan }}</option>@endforeach`;
    const unitOptions = `@foreach($unitKerja as $u)<option value="{{ $u->id }}">{{ $u->nama_unit }}</option>@endforeach`;

    if (addBtn && tbody) {
        addBtn.addEventListener('click', function () {
            let maxIndex = -1;
            tbody.querySelectorAll('.jabatan-row').forEach(row => {
                let idx = parseInt(row.getAttribute('data-index'));
                if (idx > maxIndex) {
                    maxIndex = idx;
                }
            });
            const newIndex = maxIndex + 1;

            const tr = document.createElement('tr');
            tr.className = 'jabatan-row';
            tr.setAttribute('data-index', newIndex);
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <select name="riwayat_jabatan[\${newIndex}][jabatan_id]" class="w-full border rounded px-2 py-1 text-xs" required>
                        <option value="">-- Pilih Jabatan --</option>
                        \${jabatanOptions}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <select name="riwayat_jabatan[\${newIndex}][unit_kerja_id]" class="w-full border rounded px-2 py-1 text-xs" required>
                        <option value="">-- Pilih Unit Kerja --</option>
                        \${unitOptions}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="date" name="riwayat_jabatan[\${newIndex}][tmt_jabatan]" class="w-full border rounded px-2 py-1 text-xs text-center" required>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_jabatan[\${newIndex}][nomor_sk]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nomor SK">
                </td>
                <td class="px-3 py-2">
                    <input type="date" name="riwayat_jabatan[\${newIndex}][tanggal_sk]" class="w-full border rounded px-2 py-1 text-xs text-center">
                </td>
                <td class="px-3 py-2">
                    <select name="riwayat_jabatan[\${newIndex}][status]" class="w-full border rounded px-2 py-1 text-xs" required>
                        <option value="aktif">Aktif</option>
                        <option value="riwayat">Riwayat</option>
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="file" name="riwayat_jabatan[\${newIndex}][file_sk]" class="w-full text-xs">
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-jabatan-btn">Hapus</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        tbody.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-jabatan-btn')) {
                const row = e.target.closest('.jabatan-row');
                if (tbody.querySelectorAll('.jabatan-row').length > 1) {
                    row.remove();
                } else {
                    alert('Minimal harus menyertakan satu baris riwayat.');
                }
            }
        });
    }
});
</script>
