@php
    $tahunLalu = (int)date('Y') - 1;
    $duaTahunLalu = (int)date('Y') - 2;

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
@endphp

<x-enterprise.forms.section
    id="section-penghargaan-skp"
    title="Penilaian Kinerja (SKP 2 Tahun Lalu) & Piagam Satyalancana"
    description="Basis data penilaian SKP 2 tahun terakhir dan berkas tanda kehormatan Satyalancana terdahulu">

    <div class="mb-4 text-xs text-blue-800 bg-blue-50/80 p-3.5 rounded-xl border border-blue-200 leading-relaxed">
        <span class="font-bold block mb-1">📌 Regulasi Berkas Satyalancana Karya Satya & Layanan Kepegawaian (KP/KGB):</span>
        <ul class="list-disc list-inside space-y-0.5 text-blue-900">
            <li><strong>SKP 2 Tahun Lalu:</strong> Penilaian prestasi kerja sekurang-kurangnya bernilai <em>"Baik"</em> berturut-turut dalam 2 tahun terakhir tanpa catatan kinerja buruk.</li>
            <li><strong>Piagam Satyalancana Sebelumnya:</strong> Diperlukan bagi ASN yang mengusulkan Satyalancana 20 Tahun (wajib scan Piagam 10 Tahun) atau usulan 30 Tahun (wajib scan Piagam 20 Tahun). Bagi usulan perdana (10 Tahun), biarkan opsi <em>"Tidak Ada / Usulan Perdana"</em>.</li>
        </ul>
    </div>

    {{-- ========================================================================= --}}
    {{-- 1. PENILAIAN SKP TAHUN N-1 (1 TAHUN LALU)                                 --}}
    {{-- ========================================================================= --}}
    <div class="p-4 bg-slate-50/70 rounded-xl border border-slate-200 mb-4 space-y-3">
        <div class="flex items-center justify-between">
            <span class="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                Penilaian Kinerja / SKP Tahun {{ $skp1->tahun ?? $tahunLalu }} (1 Tahun Lalu)
            </span>
            <span class="text-[11px] text-slate-500 font-medium">Tahun Evaluasi: {{ $skp1->tahun ?? $tahunLalu }}</span>
        </div>

        <input type="hidden" name="riwayat_skp[0][id]" value="{{ $skp1->id ?? '' }}">
        <input type="hidden" name="riwayat_skp[0][tahun]" value="{{ $skp1->tahun ?? $tahunLalu }}">

        <x-enterprise.forms.row cols="3">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Predikat Kinerja SKP (Tahun {{ $skp1->tahun ?? $tahunLalu }})">
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

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Upload Berkas SKP {{ $skp1->tahun ?? $tahunLalu }} (PDF/Gambar)">
                    <x-enterprise.file-upload name="riwayat_skp[0][file_evaluasi_skp]" accept=".pdf,.jpg,.jpeg,.png"/>
                    @if(isset($skp1) && $skp1->file_evaluasi_skp)
                        <p class="text-xs text-emerald-700 mt-1 flex items-center gap-1 font-semibold">
                            <span>📄 File saat ini:</span>
                            <a href="{{ route('document.preview', ['path' => $skp1->file_evaluasi_skp]) }}" target="_blank" class="underline hover:text-emerald-900">Lihat Berkas SKP {{ $skp1->tahun }}</a>
                        </p>
                    @endif
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. PENILAIAN SKP TAHUN N-2 (2 TAHUN LALU)                                 --}}
    {{-- ========================================================================= --}}
    <div class="p-4 bg-slate-50/70 rounded-xl border border-slate-200 mb-4 space-y-3">
        <div class="flex items-center justify-between">
            <span class="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                Penilaian Kinerja / SKP Tahun {{ $skp2->tahun ?? $duaTahunLalu }} (2 Tahun Lalu)
            </span>
            <span class="text-[11px] text-slate-500 font-medium">Tahun Evaluasi: {{ $skp2->tahun ?? $duaTahunLalu }}</span>
        </div>

        <input type="hidden" name="riwayat_skp[1][id]" value="{{ $skp2->id ?? '' }}">
        <input type="hidden" name="riwayat_skp[1][tahun]" value="{{ $skp2->tahun ?? $duaTahunLalu }}">

        <x-enterprise.forms.row cols="3">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Predikat Kinerja SKP (Tahun {{ $skp2->tahun ?? $duaTahunLalu }})">
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

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Upload Berkas SKP {{ $skp2->tahun ?? $duaTahunLalu }} (PDF/Gambar)">
                    <x-enterprise.file-upload name="riwayat_skp[1][file_evaluasi_skp]" accept=".pdf,.jpg,.jpeg,.png"/>
                    @if(isset($skp2) && $skp2->file_evaluasi_skp)
                        <p class="text-xs text-emerald-700 mt-1 flex items-center gap-1 font-semibold">
                            <span>📄 File saat ini:</span>
                            <a href="{{ route('document.preview', ['path' => $skp2->file_evaluasi_skp]) }}" target="_blank" class="underline hover:text-emerald-900">Lihat Berkas SKP {{ $skp2->tahun }}</a>
                        </p>
                    @endif
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. PIAGAM TANDA KEHORMATAN SATYALANCANA KARYA SATYA SEBELUMNYA            --}}
    {{-- ========================================================================= --}}
    <div class="p-4 bg-amber-50/60 rounded-xl border border-amber-200 space-y-3">
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

</x-enterprise.forms.section>
