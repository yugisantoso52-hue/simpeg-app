@php
    $oldPublikasi = old('riwayat_publikasi', (isset($pegawai) && $pegawai->riwayatPublikasi instanceof \Illuminate\Support\Collection) ? $pegawai->riwayatPublikasi->all() : []);
    $publikasiList = count($oldPublikasi) > 0 ? $oldPublikasi : [null];
    $jenisPublikasiOptions = ['Jurnal', 'Prosiding', 'Buku', 'Book Chapter', 'Paten', 'HKI', 'Lainnya'];
    $indeksasiOptions = ['Scopus', 'WoS', 'SINTA 1', 'SINTA 2', 'SINTA 3', 'SINTA 4', 'SINTA 5', 'SINTA 6', 'Nasional Terakreditasi', 'Nasional Tidak Terakreditasi', 'Lainnya'];
@endphp

<div class="mt-6 border-t pt-6 space-y-4">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Riwayat Publikasi Ilmiah & Karya</h3>
            <p class="text-xs text-gray-500">Jurnal nasional/internasional, prosiding, buku ajar, monograf, HKI, atau karya ilmiah</p>
        </div>
        <button type="button" id="add-publikasi-btn" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition">
            + Tambah Publikasi
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm" id="publikasi-table">
            <thead class="bg-gray-50 text-gray-700 font-medium">
                <tr>
                    <th class="px-3 py-2 text-left w-1/4">Judul Publikasi / Karya</th>
                    <th class="px-3 py-2 text-left w-36">Jenis</th>
                    <th class="px-3 py-2 text-left w-36">Indeksasi</th>
                    <th class="px-3 py-2 text-left">Jurnal / Penerbit</th>
                    <th class="px-3 py-2 text-center w-24">Tahun</th>
                    <th class="px-3 py-2 text-left w-36">URL / DOI</th>
                    <th class="px-3 py-2 text-left">Berkas PDF</th>
                    <th class="px-3 py-2 text-center w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white" id="publikasi-tbody">
                @foreach($publikasiList as $index => $pub)
                    @php
                        $idVal = is_array($pub) ? ($pub['id'] ?? '') : ($pub->id ?? '');
                        $judulVal = is_array($pub) ? ($pub['judul_publikasi'] ?? '') : ($pub->judul_publikasi ?? '');
                        $jenisVal = is_array($pub) ? ($pub['jenis_publikasi'] ?? 'Jurnal') : ($pub->jenis_publikasi ?? 'Jurnal');
                        $indeksVal = is_array($pub) ? ($pub['indeksasi'] ?? '') : ($pub->indeksasi ?? '');
                        $jurnalVal = is_array($pub) ? ($pub['nama_jurnal'] ?? '') : ($pub->nama_jurnal ?? $pub->penerbit ?? '');
                        $tahunVal = is_array($pub) ? ($pub['tahun_terbit'] ?? '') : ($pub->tahun_terbit ?? '');
                        $doiVal = is_array($pub) ? ($pub['url_doi'] ?? '') : ($pub->url_doi ?? '');
                        $fileVal = is_object($pub) ? ($pub->file_publikasi ?? null) : null;
                    @endphp
                    <tr class="publikasi-row" data-index="{{ $index }}">
                        @if($idVal)
                            <input type="hidden" name="riwayat_publikasi[{{ $index }}][id]" value="{{ $idVal }}">
                        @endif
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_publikasi[{{ $index }}][judul_publikasi]" value="{{ $judulVal }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Judul artikel / buku...">
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_publikasi[{{ $index }}][jenis_publikasi]" class="w-full border rounded px-2 py-1 text-xs">
                                @foreach($jenisPublikasiOptions as $jp)
                                    <option value="{{ $jp }}" @selected($jenisVal === $jp)>{{ $jp }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <select name="riwayat_publikasi[{{ $index }}][indeksasi]" class="w-full border rounded px-2 py-1 text-xs">
                                <option value="">-- Pilih Indeksasi --</option>
                                @foreach($indeksasiOptions as $idxOpt)
                                    <option value="{{ $idxOpt }}" @selected($indeksVal === $idxOpt)>{{ $idxOpt }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_publikasi[{{ $index }}][nama_jurnal]" value="{{ $jurnalVal }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Jurnal / Penerbit">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="riwayat_publikasi[{{ $index }}][tahun_terbit]" value="{{ $tahunVal }}" class="w-full border rounded px-2 py-1 text-xs text-center" placeholder="2025">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="riwayat_publikasi[{{ $index }}][url_doi]" value="{{ $doiVal }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="https://doi.org/...">
                        </td>
                        <td class="px-3 py-2 text-xs">
                            @if($fileVal)
                                <div class="mb-1 text-slate-500 truncate max-w-xs">
                                    <a href="{{ route('document.preview', ['path' => $fileVal]) }}" target="_blank" class="text-emerald-600 underline">Lihat PDF</a>
                                </div>
                            @endif
                            <input type="file" name="riwayat_publikasi[{{ $index }}][file_publikasi]" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs">
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-publikasi-btn">Hapus</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('publikasi-tbody');
    const addBtn = document.getElementById('add-publikasi-btn');

    if (!tbody || !addBtn) return;

    function getNextPubIndex() {
        const rows = tbody.querySelectorAll('.publikasi-row');
        let max = -1;
        rows.forEach(r => {
            const idx = parseInt(r.getAttribute('data-index'), 10);
            if (!isNaN(idx) && idx > max) max = idx;
        });
        return max + 1;
    }

    addBtn.addEventListener('click', function () {
        const idx = getNextPubIndex();
        const tr = document.createElement('tr');
        tr.className = 'publikasi-row';
        tr.setAttribute('data-index', idx);

        tr.innerHTML = `
            <td class="px-3 py-2">
                <input type="text" name="riwayat_publikasi[${idx}][judul_publikasi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Judul artikel / buku...">
            </td>
            <td class="px-3 py-2">
                <select name="riwayat_publikasi[${idx}][jenis_publikasi]" class="w-full border rounded px-2 py-1 text-xs">
                    <option value="Jurnal">Jurnal</option>
                    <option value="Prosiding">Prosiding</option>
                    <option value="Buku">Buku</option>
                    <option value="Book Chapter">Book Chapter</option>
                    <option value="Paten">Paten</option>
                    <option value="HKI">HKI</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </td>
            <td class="px-3 py-2">
                <select name="riwayat_publikasi[${idx}][indeksasi]" class="w-full border rounded px-2 py-1 text-xs">
                    <option value="">-- Pilih Indeksasi --</option>
                    <option value="Scopus">Scopus</option>
                    <option value="WoS">WoS</option>
                    <option value="SINTA 1">SINTA 1</option>
                    <option value="SINTA 2">SINTA 2</option>
                    <option value="SINTA 3">SINTA 3</option>
                    <option value="SINTA 4">SINTA 4</option>
                    <option value="SINTA 5">SINTA 5</option>
                    <option value="SINTA 6">SINTA 6</option>
                    <option value="Nasional Terakreditasi">Nasional Terakreditasi</option>
                    <option value="Nasional Tidak Terakreditasi">Nasional Tidak Terakreditasi</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </td>
            <td class="px-3 py-2">
                <input type="text" name="riwayat_publikasi[${idx}][nama_jurnal]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Jurnal / Penerbit">
            </td>
            <td class="px-3 py-2">
                <input type="number" name="riwayat_publikasi[${idx}][tahun_terbit]" class="w-full border rounded px-2 py-1 text-xs text-center" placeholder="2025">
            </td>
            <td class="px-3 py-2">
                <input type="text" name="riwayat_publikasi[${idx}][url_doi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="https://doi.org/...">
            </td>
            <td class="px-3 py-2">
                <input type="file" name="riwayat_publikasi[${idx}][file_publikasi]" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs">
            </td>
            <td class="px-3 py-2 text-center">
                <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-publikasi-btn">Hapus</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    tbody.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-publikasi-btn')) {
            const rows = tbody.querySelectorAll('.publikasi-row');
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
