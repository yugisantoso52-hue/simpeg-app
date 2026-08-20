<div class="mt-6 border-t pt-6 space-y-4">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Riwayat Pangkat/Golongan</h3>
        <button type="button" id="add-pangkat-btn" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition">
            + Tambah Riwayat Pangkat
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm" id="pangkat-table">
            <thead class="bg-gray-50 text-gray-700 font-medium">
                <tr>
                    <th class="px-3 py-2 text-left w-1/4">Golongan / Pangkat</th>
                    <th class="px-3 py-2 text-center w-36">TMT Pangkat</th>
                    <th class="px-3 py-2 text-left">Nomor SK</th>
                    <th class="px-3 py-2 text-center w-36">Tanggal SK</th>
                    <th class="px-3 py-2 text-center w-28">Status</th>
                    <th class="px-3 py-2 text-left">File SK</th>
                    <th class="px-3 py-2 text-center w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white" id="pangkat-tbody">
                @php
                    $pangkats = isset($pegawai) ? $pegawai->riwayatPangkat : collect();
                @endphp
                @forelse($pangkats as $index => $rp)
                    <tr class="pangkat-row" data-index="{{ $index }}">
                        <input type="hidden" name="riwayat_pangkat[{{ $index }}][id]" value="{{ $rp->id }}">
                        <td class="px-3 py-2">
                            <select name="riwayat_pangkat[{{ $index }}][golongan_id]" class="w-full border rounded px-2 py-1 text-xs" required>
                                <option value="">-- Pilih Golongan --</option>
                                @foreach($golongan as $g)
                                    <option value="{{ $g->id }}" @selected($rp->golongan_id == $g->id)>{{ $g->nama_golongan }} - {{ $g->nama_pangkat }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_pangkat[{{ $index }}][tmt]" value="{{ $rp->tmt ? \Carbon\Carbon::parse($rp->tmt)->format('Y-m-d') : '' }}" class="w-full border rounded px-2 py-1 text-xs text-center" required>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_pangkat[{{ $index }}][nomor_sk]" value="{{ $rp->nomor_sk }}" class="w-full border rounded px-2 py-1 text-xs">
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_pangkat[{{ $index }}][tanggal_sk]" value="{{ $rp->tanggal_sk ? \Carbon\Carbon::parse($rp->tanggal_sk)->format('Y-m-d') : '' }}" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_pangkat[{{ $index }}][status]" class="w-full border rounded px-2 py-1 text-xs" required>
                                <option value="aktif" @selected(strtolower($rp->status) == 'aktif')>Aktif</option>
                                <option value="riwayat" @selected(strtolower($rp->status) == 'riwayat')>Riwayat</option>
                            </select>
                        </td>
                        <td class="px-3 py-2 text-xs">
                            @if($rp->file_sk)
                                <div class="mb-1 text-slate-500 truncate max-w-xs">
                                    <a href="{{ route('document.preview', ['path' => $rp->file_sk]) }}" target="_blank" class="text-blue-600 underline">Lihat SK</a>
                                </div>
                            @endif
                            <input type="file" name="riwayat_pangkat[{{ $index }}][file_sk]" class="w-full text-xs">
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-pangkat-btn">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr class="pangkat-row" data-index="0">
                        <td class="px-3 py-2">
                            <select name="riwayat_pangkat[0][golongan_id]" class="w-full border rounded px-2 py-1 text-xs" required>
                                <option value="">-- Pilih Golongan --</option>
                                @foreach($golongan as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama_golongan }} - {{ $g->nama_pangkat }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_pangkat[0][tmt]" class="w-full border rounded px-2 py-1 text-xs text-center" required>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_pangkat[0][nomor_sk]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nomor SK">
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="riwayat_pangkat[0][tanggal_sk]" class="w-full border rounded px-2 py-1 text-xs text-center">
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_pangkat[0][status]" class="w-full border rounded px-2 py-1 text-xs" required>
                                <option value="aktif">Aktif</option>
                                <option value="riwayat">Riwayat</option>
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="file" name="riwayat_pangkat[0][file_sk]" class="w-full text-xs">
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-pangkat-btn">Hapus</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('pangkat-tbody');
    const addBtn = document.getElementById('add-pangkat-btn');
    const golonganOptions = `@foreach($golongan as $g)<option value="{{ $g->id }}">{{ $g->nama_golongan }} - {{ $g->nama_pangkat }}</option>@endforeach`;

    if (addBtn && tbody) {
        addBtn.addEventListener('click', function () {
            let maxIndex = -1;
            tbody.querySelectorAll('.pangkat-row').forEach(row => {
                let idx = parseInt(row.getAttribute('data-index'));
                if (idx > maxIndex) {
                    maxIndex = idx;
                }
            });
            const newIndex = maxIndex + 1;

            const tr = document.createElement('tr');
            tr.className = 'pangkat-row';
            tr.setAttribute('data-index', newIndex);
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <select name="riwayat_pangkat[\${newIndex}][golongan_id]" class="w-full border rounded px-2 py-1 text-xs" required>
                        <option value="">-- Pilih Golongan --</option>
                        ${golonganOptions}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="date" name="riwayat_pangkat[\${newIndex}][tmt]" class="w-full border rounded px-2 py-1 text-xs text-center" required>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_pangkat[\${newIndex}][nomor_sk]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nomor SK">
                </td>
                <td class="px-3 py-2">
                    <input type="date" name="riwayat_pangkat[\${newIndex}][tanggal_sk]" class="w-full border rounded px-2 py-1 text-xs text-center">
                </td>
                <td class="px-3 py-2">
                    <select name="riwayat_pangkat[\${newIndex}][status]" class="w-full border rounded px-2 py-1 text-xs" required>
                        <option value="aktif">Aktif</option>
                        <option value="riwayat">Riwayat</option>
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="file" name="riwayat_pangkat[\${newIndex}][file_sk]" class="w-full text-xs">
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-pangkat-btn">Hapus</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        tbody.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-pangkat-btn')) {
                const row = e.target.closest('.pangkat-row');
                if (tbody.querySelectorAll('.pangkat-row').length > 1) {
                    row.remove();
                } else {
                    alert('Minimal harus menyertakan satu baris riwayat.');
                }
            }
        });
    }
});
</script>
