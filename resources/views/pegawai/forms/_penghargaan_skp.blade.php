@php
    $tahunIni = (int)date('Y');
    $tahunLalu = $tahunIni - 1;
    $duaTahunLalu = $tahunIni - 2;

    $skp1 = isset($pegawai) ? $pegawai->riwayatSkp->firstWhere('tahun', $tahunLalu) : null;
    $skp2 = isset($pegawai) ? $pegawai->riwayatSkp->firstWhere('tahun', $duaTahunLalu) : null;

    // Fallback jika ada data riwayat SKP terbaru lainnya
    if (!$skp1 && isset($pegawai) && $pegawai->riwayatSkp->isNotEmpty()) {
        $skp1 = $pegawai->riwayatSkp->get(0);
        $skp2 = $pegawai->riwayatSkp->get(1);
    }

    $piagamTerakhir = isset($pegawai) 
        ? $pegawai->riwayatPenghargaan->where('jenis_penghargaan', 'Satyalancana')->first() 
        : null;

    $penghargaanLainnya = isset($pegawai)
        ? $pegawai->riwayatPenghargaan->filter(fn($p) => !$piagamTerakhir || $p->id !== $piagamTerakhir->id)->values()
        : collect();
@endphp

<x-enterprise.forms.section
    id="section-penghargaan-skp"
    title="Pengarsipan SKP (2 Tahun) & Riwayat Penghargaan / Satyalancana"
    description="Basis data penilaian SKP 2 tahun terakhir, Piagam Satyalancana, dan penghargaan/tanda jasa lainnya">

    <div class="mb-4 text-xs text-blue-800 bg-blue-50/80 p-3.5 rounded-xl border border-blue-200 leading-relaxed">
        <span class="font-bold block mb-1">📌 Regulasi Berkas Satyalancana Karya Satya & Layanan Kepegawaian (KP/KGB):</span>
        <ul class="list-disc list-inside space-y-0.5 text-blue-900">
            <li><strong>SKP 2 Tahun:</strong> Penilaian prestasi kerja sekurang-kurangnya bernilai <em>"Baik"</em> berturut-turut dalam 2 tahun terakhir tanpa catatan kinerja buruk.</li>
            <li><strong>Piagam Satyalancana Sebelumnya:</strong> Diperlukan bagi ASN yang mengusulkan Satyalancana 20 Tahun (wajib scan Piagam 10 Tahun) atau usulan 30 Tahun (wajib scan Piagam 20 Tahun). Bagi usulan perdana (10 Tahun), biarkan opsi <em>"Tidak Ada / Usulan Perdana"</em>.</li>
        </ul>
    </div>

    {{-- ========================================================================= --}}
    {{-- 1. PENILAIAN SKP PERIODE I (TAHUN TERBARU / N-1)                          --}}
    {{-- ========================================================================= --}}
    <div class="p-4 bg-slate-50/70 rounded-xl border border-slate-200 mb-4 space-y-3">
        <div class="flex items-center justify-between">
            <span class="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                Penilaian Kinerja / SKP Periode I (Tahun {{ $skp1->tahun ?? $tahunLalu }})
            </span>
            <span class="text-[11px] text-slate-500 font-medium">Tahun Evaluasi: {{ $skp1->tahun ?? $tahunLalu }}</span>
        </div>

        <input type="hidden" name="riwayat_skp[0][id]" value="{{ $skp1->id ?? '' }}">

        <x-enterprise.forms.row cols="3">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Tahun SKP">
                    <x-enterprise.input
                        type="number"
                        name="riwayat_skp[0][tahun]"
                        :value="old('riwayat_skp.0.tahun', $skp1->tahun ?? $tahunLalu)"
                        placeholder="{{ $tahunLalu }}"
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Predikat Kinerja SKP">
                    <x-enterprise.select name="riwayat_skp[0][predikat_kinerja]">
                        <option value="">-- Pilih Predikat Kinerja --</option>
                        @foreach(['Sangat Baik', 'Baik', 'Butuh Perbaikan', 'Kurang', 'Sangat Kurang'] as $predikat)
                            <option value="{{ $predikat }}" @selected(old('riwayat_skp.0.predikat_kinerja', $skp1->predikat_kinerja ?? '') == $predikat)>
                                {{ $predikat }}
                            </option>
                        @endforeach
                    </x-enterprise.select>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Pejabat Penilai Kinerja">
                    <x-enterprise.input 
                        name="riwayat_skp[0][pejabat_penilai]" 
                        :value="old('riwayat_skp.0.pejabat_penilai', $skp1->pejabat_penilai ?? '')" 
                        placeholder="Nama / NIP Pejabat Penilai..." 
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>

        <x-enterprise.forms.row cols="2">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Upload 1. Rencana SKP (PDF/Gambar)">
                    <x-enterprise.file-upload name="riwayat_skp[0][file_rencana_skp]" accept=".pdf,.jpg,.jpeg,.png"/>
                    @if(isset($skp1) && $skp1->file_rencana_skp)
                        <p class="text-xs text-blue-700 mt-1 flex items-center gap-1 font-semibold">
                            <span>📄 Rencana SKP saat ini:</span>
                            <a href="{{ route('document.preview', ['path' => $skp1->file_rencana_skp]) }}" target="_blank" class="underline hover:text-blue-900">Lihat Rencana SKP {{ $skp1->tahun }}</a>
                        </p>
                    @endif
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Upload 2. Evaluasi SKP (PDF/Gambar)">
                    <x-enterprise.file-upload name="riwayat_skp[0][file_evaluasi_skp]" accept=".pdf,.jpg,.jpeg,.png"/>
                    @if(isset($skp1) && $skp1->file_evaluasi_skp)
                        <p class="text-xs text-emerald-700 mt-1 flex items-center gap-1 font-semibold">
                            <span>📑 Evaluasi SKP saat ini:</span>
                            <a href="{{ route('document.preview', ['path' => $skp1->file_evaluasi_skp]) }}" target="_blank" class="underline hover:text-emerald-900">Lihat Evaluasi SKP {{ $skp1->tahun }}</a>
                        </p>
                    @endif
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. PENILAIAN SKP PERIODE II (TAHUN SEBELUMNYA / N-2)                      --}}
    {{-- ========================================================================= --}}
    <div class="p-4 bg-slate-50/70 rounded-xl border border-slate-200 mb-4 space-y-3">
        <div class="flex items-center justify-between">
            <span class="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                Penilaian Kinerja / SKP Periode II (Tahun {{ $skp2->tahun ?? $duaTahunLalu }})
            </span>
            <span class="text-[11px] text-slate-500 font-medium">Tahun Evaluasi: {{ $skp2->tahun ?? $duaTahunLalu }}</span>
        </div>

        <input type="hidden" name="riwayat_skp[1][id]" value="{{ $skp2->id ?? '' }}">

        <x-enterprise.forms.row cols="3">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Tahun SKP">
                    <x-enterprise.input
                        type="number"
                        name="riwayat_skp[1][tahun]"
                        :value="old('riwayat_skp.1.tahun', $skp2->tahun ?? $duaTahunLalu)"
                        placeholder="{{ $duaTahunLalu }}"
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Predikat Kinerja SKP">
                    <x-enterprise.select name="riwayat_skp[1][predikat_kinerja]">
                        <option value="">-- Pilih Predikat Kinerja --</option>
                        @foreach(['Sangat Baik', 'Baik', 'Butuh Perbaikan', 'Kurang', 'Sangat Kurang'] as $predikat)
                            <option value="{{ $predikat }}" @selected(old('riwayat_skp.1.predikat_kinerja', $skp2->predikat_kinerja ?? '') == $predikat)>
                                {{ $predikat }}
                            </option>
                        @endforeach
                    </x-enterprise.select>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Pejabat Penilai Kinerja">
                    <x-enterprise.input 
                        name="riwayat_skp[1][pejabat_penilai]" 
                        :value="old('riwayat_skp.1.pejabat_penilai', $skp2->pejabat_penilai ?? '')" 
                        placeholder="Nama / NIP Pejabat Penilai..." 
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>

        <x-enterprise.forms.row cols="2">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Upload 1. Rencana SKP (PDF/Gambar)">
                    <x-enterprise.file-upload name="riwayat_skp[1][file_rencana_skp]" accept=".pdf,.jpg,.jpeg,.png"/>
                    @if(isset($skp2) && $skp2->file_rencana_skp)
                        <p class="text-xs text-blue-700 mt-1 flex items-center gap-1 font-semibold">
                            <span>📄 Rencana SKP saat ini:</span>
                            <a href="{{ route('document.preview', ['path' => $skp2->file_rencana_skp]) }}" target="_blank" class="underline hover:text-blue-900">Lihat Rencana SKP {{ $skp2->tahun }}</a>
                        </p>
                    @endif
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Upload 2. Evaluasi SKP (PDF/Gambar)">
                    <x-enterprise.file-upload name="riwayat_skp[1][file_evaluasi_skp]" accept=".pdf,.jpg,.jpeg,.png"/>
                    @if(isset($skp2) && $skp2->file_evaluasi_skp)
                        <p class="text-xs text-emerald-700 mt-1 flex items-center gap-1 font-semibold">
                            <span>📑 Evaluasi SKP saat ini:</span>
                            <a href="{{ route('document.preview', ['path' => $skp2->file_evaluasi_skp]) }}" target="_blank" class="underline hover:text-emerald-900">Lihat Evaluasi SKP {{ $skp2->tahun }}</a>
                        </p>
                    @endif
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. PIAGAM TANDA KEHORMATAN SATYALANCANA KARYA SATYA SEBELUMNYA            --}}
    {{-- ========================================================================= --}}
    <div class="p-4 bg-amber-50/60 rounded-xl border border-amber-200 space-y-3 mb-4">
        <div class="flex items-center justify-between">
            <span class="font-bold text-amber-950 text-xs uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-amber-600 text-white flex items-center justify-center text-[10px] font-bold">🎖️</span>
                Piagam Satyalancana Karya Satya Terdahulu (Jika Pernah Memperoleh)
            </span>
            <span class="text-[11px] text-amber-800 font-normal">*Syarat Wajib Usulan Satyalancana 20 & 30 Tahun</span>
        </div>

        <input type="hidden" name="piagam_satyalancana[id]" value="{{ $piagamTerakhir->id ?? '' }}">

        <x-enterprise.forms.row cols="3">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Tanda Kehormatan Sebelumnya">
                    @php
                        $currentPenghargaan = old('piagam_satyalancana.nama_penghargaan', $piagamTerakhir->nama_penghargaan ?? ($pegawai->satyalancana_terakhir ?? ''));
                    @endphp
                    <x-enterprise.select name="piagam_satyalancana[nama_penghargaan]">
                        <option value="">-- Belum Ada / Usulan Perdana (10 Tahun) --</option>
                        <option value="Satyalancana Karya Satya 10 Tahun" @selected(str_contains($currentPenghargaan, '10') || $currentPenghargaan == 'Perunggu')>
                            Satyalancana Karya Satya 10 Tahun (Perunggu)
                        </option>
                        <option value="Satyalancana Karya Satya 20 Tahun" @selected(str_contains($currentPenghargaan, '20') || $currentPenghargaan == 'Perak')>
                            Satyalancana Karya Satya 20 Tahun (Perak)
                        </option>
                        <option value="Satyalancana Karya Satya 30 Tahun" @selected(str_contains($currentPenghargaan, '30') || $currentPenghargaan == 'Emas')>
                            Satyalancana Karya Satya 30 Tahun (Emas)
                        </option>
                    </x-enterprise.select>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Nomor SK / Keppres Piagam">
                    <x-enterprise.input 
                        name="piagam_satyalancana[nomor_sk]" 
                        :value="old('piagam_satyalancana.nomor_sk', $piagamTerakhir->nomor_sk ?? '')" 
                        placeholder="Contoh: Keppres No. 12/TK/2015..." 
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Tanggal SK / Piagam">
                    <x-enterprise.date-picker 
                        name="piagam_satyalancana[tanggal_sk]" 
                        :value="old('piagam_satyalancana.tanggal_sk', (isset($piagamTerakhir->tanggal_terima) && $piagamTerakhir->tanggal_terima) ? \Carbon\Carbon::parse($piagamTerakhir->tanggal_terima)->format('Y-m-d') : '')" 
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>

        <x-enterprise.forms.row cols="2">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Instansi Pemberi">
                    <x-enterprise.input 
                        name="piagam_satyalancana[instansi_pemberi]" 
                        :value="old('piagam_satyalancana.instansi_pemberi', $piagamTerakhir->instansi_pemberi ?? 'Presiden Republik Indonesia')" 
                        placeholder="Presiden Republik Indonesia" 
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Upload Scan Piagam Satyalancana (PDF/Gambar)">
                    <x-enterprise.file-upload name="piagam_satyalancana[file_sk]" accept=".pdf,.jpg,.jpeg,.png"/>
                    @if(isset($piagamTerakhir) && $piagamTerakhir->file_sk)
                        <p class="text-xs text-amber-900 mt-1 flex items-center gap-1 font-semibold">
                            <span>📜 File piagam saat ini:</span>
                            <a href="{{ route('document.preview', ['path' => $piagamTerakhir->file_sk]) }}" target="_blank" class="underline hover:text-amber-950">Lihat Berkas Piagam</a>
                        </p>
                    @endif
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. RIWAYAT PENGHARGAAN & TANDA JASA LAINNYA                               --}}
    {{-- ========================================================================= --}}
    <div class="mt-4 pt-4 border-t border-gray-200 space-y-3">
        <div class="flex justify-between items-center">
            <div>
                <h4 class="text-sm font-bold text-gray-800">Riwayat Penghargaan & Tanda Jasa Lainnya</h4>
                <p class="text-xs text-gray-500">Penghargaan prestasi akademik, inovasi, pengabdian, atau tanda jasa lainnya</p>
            </div>
            <button type="button" id="add-penghargaan-btn" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition">
                + Tambah Penghargaan
            </button>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm" id="penghargaan-table">
                <thead class="bg-gray-50 text-gray-700 font-medium">
                    <tr>
                        <th class="px-3 py-2 text-left w-1/4">Nama Penghargaan</th>
                        <th class="px-3 py-2 text-left w-36">Jenis</th>
                        <th class="px-3 py-2 text-left">Instansi Pemberi</th>
                        <th class="px-3 py-2 text-left w-36">Nomor SK</th>
                        <th class="px-3 py-2 text-center w-36">Tanggal Terima</th>
                        <th class="px-3 py-2 text-left">Berkas SK/Piagam</th>
                        <th class="px-3 py-2 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white" id="penghargaan-tbody">
                    @forelse($penghargaanLainnya as $index => $p)
                        <tr class="penghargaan-row" data-index="{{ $index }}">
                            <input type="hidden" name="riwayat_penghargaan[{{ $index }}][id]" value="{{ $p->id }}">
                            <td class="px-3 py-2">
                                <input type="text" name="riwayat_penghargaan[{{ $index }}][nama_penghargaan]" value="{{ $p->nama_penghargaan }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Penghargaan...">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="riwayat_penghargaan[{{ $index }}][jenis_penghargaan]" value="{{ $p->jenis_penghargaan }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Prestasi / Inovasi">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="riwayat_penghargaan[{{ $index }}][instansi_pemberi]" value="{{ $p->instansi_pemberi }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Universitas Riau / Kemendikbud">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="riwayat_penghargaan[{{ $index }}][nomor_sk]" value="{{ $p->nomor_sk }}" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nomor SK/Piagam">
                            </td>
                            <td class="px-3 py-2">
                                <input type="date" name="riwayat_penghargaan[{{ $index }}][tanggal_terima]" value="{{ $p->tanggal_terima ? \Carbon\Carbon::parse($p->tanggal_terima)->format('Y-m-d') : '' }}" class="w-full border rounded px-2 py-1 text-xs text-center">
                            </td>
                            <td class="px-3 py-2 text-xs">
                                @if($p->file_sk)
                                    <div class="mb-1 text-slate-500 truncate max-w-xs">
                                        <a href="{{ route('document.preview', ['path' => $p->file_sk]) }}" target="_blank" class="text-blue-600 underline">Lihat SK</a>
                                    </div>
                                @endif
                                <input type="file" name="riwayat_penghargaan[{{ $index }}][file_sk]" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-penghargaan-btn">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr class="penghargaan-row" data-index="0">
                            <td class="px-3 py-2">
                                <input type="text" name="riwayat_penghargaan[0][nama_penghargaan]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Penghargaan...">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="riwayat_penghargaan[0][jenis_penghargaan]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Prestasi / Inovasi">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="riwayat_penghargaan[0][instansi_pemberi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Universitas Riau / Kemendikbud">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="riwayat_penghargaan[0][nomor_sk]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nomor SK/Piagam">
                            </td>
                            <td class="px-3 py-2">
                                <input type="date" name="riwayat_penghargaan[0][tanggal_terima]" class="w-full border rounded px-2 py-1 text-xs text-center">
                            </td>
                            <td class="px-3 py-2">
                                <input type="file" name="riwayat_penghargaan[0][file_sk]" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-penghargaan-btn">Hapus</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-enterprise.forms.section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('penghargaan-tbody');
    const addBtn = document.getElementById('add-penghargaan-btn');

    if (addBtn && tbody) {
        addBtn.addEventListener('click', function () {
            let maxIndex = -1;
            tbody.querySelectorAll('.penghargaan-row').forEach(row => {
                let idx = parseInt(row.getAttribute('data-index'));
                if (idx > maxIndex) {
                    maxIndex = idx;
                }
            });
            const newIndex = maxIndex + 1;

            const tr = document.createElement('tr');
            tr.className = 'penghargaan-row';
            tr.setAttribute('data-index', newIndex);
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_penghargaan[${newIndex}][nama_penghargaan]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nama Penghargaan...">
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_penghargaan[${newIndex}][jenis_penghargaan]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Prestasi / Inovasi">
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_penghargaan[${newIndex}][instansi_pemberi]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Universitas Riau / Kemendikbud">
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="riwayat_penghargaan[${newIndex}][nomor_sk]" class="w-full border rounded px-2 py-1 text-xs" placeholder="Nomor SK/Piagam">
                </td>
                <td class="px-3 py-2">
                    <input type="date" name="riwayat_penghargaan[${newIndex}][tanggal_terima]" class="w-full border rounded px-2 py-1 text-xs text-center">
                </td>
                <td class="px-3 py-2">
                    <input type="file" name="riwayat_penghargaan[${newIndex}][file_sk]" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs">
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 text-xs remove-penghargaan-btn">Hapus</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        tbody.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-penghargaan-btn')) {
                const row = e.target.closest('.penghargaan-row');
                if (row) {
                    row.remove();
                }
            }
        });
    }
});
</script>
