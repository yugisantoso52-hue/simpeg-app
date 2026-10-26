@php
    $oldTubel = old('tugas_belajar', (isset($pegawai) && $pegawai->tugasBelajar instanceof \Illuminate\Support\Collection) ? $pegawai->tugasBelajar->all() : []);
    $tubelList = count($oldTubel) > 0 ? $oldTubel : [null];
@endphp

<div class="mt-6 border-t pt-6 space-y-4">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Tugas Belajar & Izin Belajar (Studi Lanjut)</h3>
            <p class="text-xs text-gray-500">Data pengembangan kompetensi akademik melalui Tugas Belajar atau Izin Belajar</p>
        </div>
        <button type="button" id="add-tubel-btn" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition">
            + Tambah Tubel/Ibel
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm" id="tubel-table">
            <thead class="bg-gray-50 text-gray-700 font-medium">
                <tr>
                    <th class="px-3 py-2 text-left w-36">Jenis & Jenjang</th>
                    <th class="px-3 py-2 text-left">Universitas, Prodi & Negara</th>
                    <th class="px-3 py-2 text-left">Beasiswa & Nomor SK</th>
                    <th class="px-3 py-2 text-center w-44">Masa Studi & Semester</th>
                    <th class="px-3 py-2 text-center w-36">Status Studi</th>
                    <th class="px-3 py-2 text-left">Berkas SK</th>
                    <th class="px-3 py-2 text-center w-16">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white" id="tubel-tbody">
                @foreach($tubelList as $index => $tb)
                    @php
                        $idVal = is_array($tb) ? ($tb['id'] ?? '') : ($tb->id ?? '');
                        $jenisVal = is_array($tb) ? ($tb['jenis_pengembangan'] ?? 'Tugas Belajar') : ($tb->jenis_pengembangan ?? 'Tugas Belajar');
                        $jenjangVal = is_array($tb) ? ($tb['jenjang_studi'] ?? 'S3') : ($tb->jenjang_studi ?? 'S3');
                        $univVal = is_array($tb) ? ($tb['perguruan_tinggi'] ?? '') : ($tb->perguruan_tinggi ?? '');
                        $prodiVal = is_array($tb) ? ($tb['program_studi'] ?? '') : ($tb->program_studi ?? '');
                        $negaraVal = is_array($tb) ? ($tb['negara'] ?? 'Indonesia') : ($tb->negara ?? 'Indonesia');
                        $biayaVal = is_array($tb) ? ($tb['sumber_pembiayaan'] ?? '') : ($tb->sumber_pembiayaan ?? '');
                        $skVal = is_array($tb) ? ($tb['nomor_sk'] ?? '') : ($tb->nomor_sk ?? '');
                        $mulaiVal = is_array($tb) ? ($tb['tanggal_mulai'] ?? '') : (isset($tb->tanggal_mulai) && $tb->tanggal_mulai ? \Carbon\Carbon::parse($tb->tanggal_mulai)->format('Y-m-d') : '');
                        $selesaiVal = is_array($tb) ? ($tb['tanggal_selesai'] ?? '') : (isset($tb->tanggal_selesai) && $tb->tanggal_selesai ? \Carbon\Carbon::parse($tb->tanggal_selesai)->format('Y-m-d') : '');
                        $semVal = is_array($tb) ? ($tb['semester_berjalan'] ?? '') : ($tb->semester_berjalan ?? '');
                        $statusVal = is_array($tb) ? ($tb['status_studi'] ?? 'Sedang Studi') : ($tb->status_studi ?? 'Sedang Studi');
                        $fileVal = is_object($tb) ? ($tb->file_sk ?? null) : null;
                    @endphp
                    <tr class="tubel-row" data-index="{{ $index }}">
                        @if($idVal)
                            <input type="hidden" name="tugas_belajar[{{ $index }}][id]" value="{{ $idVal }}">
                        @endif
                        <td class="px-3 py-2 space-y-1">
                            <select name="tugas_belajar[{{ $index }}][jenis_pengembangan]" class="w-full border rounded px-2 py-1 text-xs">
                                @foreach(['Tugas Belajar', 'Izin Belajar'] as $jp)
                                    <option value="{{ $jp }}" @selected($jenisVal === $jp)>{{ $jp }}</option>
                                @endforeach
                            </select>
                            <select name="tugas_belajar[{{ $index }}][jenjang_studi]" class="w-full border rounded px-2 py-1 text-xs">
                                @foreach(['S2', 'S3', 'Spesialis', 'Subspesialis', 'Post Doctoral'] as $js)
                                    <option value="{{ $js }}" @selected($jenjangVal === $js)>{{ $js }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2 space-y-1">
                            <input type="text" name="tugas_belajar[{{ $index }}][perguruan_tinggi]" value="{{ $univVal }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Universitas...">
                            <div class="grid grid-cols-2 gap-1">
                                <input type="text" name="tugas_belajar[{{ $index }}][program_studi]" value="{{ $prodiVal }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Program Studi">
                                <input type="text" name="tugas_belajar[{{ $index }}][negara]" value="{{ $negaraVal }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Negara">
                            </div>
                        </td>
                        <td class="px-3 py-2 space-y-1">
                            <input type="text" name="tugas_belajar[{{ $index }}][sumber_pembiayaan]" value="{{ $biayaVal }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Beasiswa BPI / LPDP / Mandiri">
                            <input type="text" name="tugas_belajar[{{ $index }}][nomor_sk]" value="{{ $skVal }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nomor SK Tubel/Ibel">
                        </td>
                        <td class="px-3 py-2 space-y-1">
                            <div class="grid grid-cols-2 gap-1">
                                <input type="date" name="tugas_belajar[{{ $index }}][tanggal_mulai]" value="{{ $mulaiVal }}" class="w-full border rounded px-1.5 py-1 text-[11px]" title="Tanggal Mulai">
                                <input type="date" name="tugas_belajar[{{ $index }}][tanggal_selesai]" value="{{ $selesaiVal }}" class="w-full border rounded px-1.5 py-1 text-[11px]" title="Tanggal Selesai">
                            </div>
                            <input type="number" name="tugas_belajar[{{ $index }}][semester_berjalan]" value="{{ $semVal }}" min="1" max="16" class="w-full border rounded px-2 py-1 text-xs text-center" placeholder="Semester Berjalan (mis: 3)">
                        </td>
                        <td class="px-3 py-2">
                            <select name="tugas_belajar[{{ $index }}][status_studi]" class="w-full border rounded px-2 py-1 text-xs">
                                @foreach(['Sedang Studi', 'Perpanjangan', 'Lulus', 'Dibatalkan / DO'] as $st)
                                    <option value="{{ $st }}" @selected($statusVal === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2 text-xs">
                            @if($fileVal)
                                <div class="mb-1 text-slate-500 truncate max-w-xs">
                                    <a href="{{ route('document.preview', ['path' => $fileVal]) }}" target="_blank" class="text-blue-600 underline">Lihat SK</a>
                                </div>
                            @endif
                            <input type="file" name="tugas_belajar[{{ $index }}][file_sk]" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs">
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-tubel-btn">Hapus</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tubel-tbody');
    const addBtn = document.getElementById('add-tubel-btn');

    if (!tbody || !addBtn) return;

    function getNextTubelIndex() {
        const rows = tbody.querySelectorAll('.tubel-row');
        let max = -1;
        rows.forEach(r => {
            const idx = parseInt(r.getAttribute('data-index'), 10);
            if (!isNaN(idx) && idx > max) max = idx;
        });
        return max + 1;
    }

    addBtn.addEventListener('click', function () {
        const idx = getNextTubelIndex();
        const tr = document.createElement('tr');
        tr.className = 'tubel-row';
        tr.setAttribute('data-index', idx);

        tr.innerHTML = `
            <td class="px-3 py-2 space-y-1">
                <select name="tugas_belajar[${idx}][jenis_pengembangan]" class="w-full border rounded px-2 py-1 text-xs">
                    <option value="Tugas Belajar">Tugas Belajar</option>
                    <option value="Izin Belajar">Izin Belajar</option>
                </select>
                <select name="tugas_belajar[${idx}][jenjang_studi]" class="w-full border rounded px-2 py-1 text-xs">
                    <option value="S2">S2</option>
                    <option value="S3">S3</option>
                    <option value="Spesialis">Spesialis</option>
                    <option value="Subspesialis">Subspesialis</option>
                    <option value="Post Doctoral">Post Doctoral</option>
                </select>
            </td>
            <td class="px-3 py-2 space-y-1">
                <input type="text" name="tugas_belajar[${idx}][perguruan_tinggi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Universitas...">
                <div class="grid grid-cols-2 gap-1">
                    <input type="text" name="tugas_belajar[${idx}][program_studi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Program Studi">
                    <input type="text" name="tugas_belajar[${idx}][negara]" value="Indonesia" class="w-full border rounded px-2 py-1 text-xs" placeholder="Negara">
                </div>
            </td>
            <td class="px-3 py-2 space-y-1">
                <input type="text" name="tugas_belajar[${idx}][sumber_pembiayaan]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Beasiswa BPI / LPDP / Mandiri">
                <input type="text" name="tugas_belajar[${idx}][nomor_sk]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nomor SK Tubel/Ibel">
            </td>
            <td class="px-3 py-2 space-y-1">
                <div class="grid grid-cols-2 gap-1">
                    <input type="date" name="tugas_belajar[${idx}][tanggal_mulai]" class="w-full border rounded px-1.5 py-1 text-[11px]" title="Tanggal Mulai">
                    <input type="date" name="tugas_belajar[${idx}][tanggal_selesai]" class="w-full border rounded px-1.5 py-1 text-[11px]" title="Tanggal Selesai">
                </div>
                <input type="number" name="tugas_belajar[${idx}][semester_berjalan]" min="1" max="16" class="w-full border rounded px-2 py-1 text-xs text-center" placeholder="Semester Berjalan">
            </td>
            <td class="px-3 py-2">
                <select name="tugas_belajar[${idx}][status_studi]" class="w-full border rounded px-2 py-1 text-xs">
                    <option value="Sedang Studi">Sedang Studi</option>
                    <option value="Perpanjangan">Perpanjangan</option>
                    <option value="Lulus">Lulus</option>
                    <option value="Dibatalkan / DO">Dibatalkan / DO</option>
                </select>
            </td>
            <td class="px-3 py-2">
                <input type="file" name="tugas_belajar[${idx}][file_sk]" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs">
            </td>
            <td class="px-3 py-2 text-center">
                <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-tubel-btn">Hapus</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    tbody.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-tubel-btn')) {
            const rows = tbody.querySelectorAll('.tubel-row');
            const tr = e.target.closest('tr');
            if (rows.length > 1) {
                tr.remove();
            } else {
                tr.querySelectorAll('input').forEach(el => {
                    if (el.type !== 'hidden') el.value = '';
                });
            }
        }
    });
});
</script>
