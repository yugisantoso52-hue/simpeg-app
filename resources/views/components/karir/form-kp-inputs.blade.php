@props([
    'pegawai',
    'golongans'     => collect(),
    'periodeKpList' => [],
])

{{-- Pengaturan Periode KP BKN --}}
<div class="rounded-xl bg-indigo-50/70 p-4 border border-indigo-100 space-y-3">
    <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider flex items-center gap-1.5">
        <span>📅</span> Penentuan Periode Kenaikan Pangkat (Peraturan BKN No. 4/2023)
    </h4>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Periode KP <span class="text-red-500">*</span></label>
            <select name="periode_kp" required class="w-full rounded-lg border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                @foreach($periodeKpList as $key => $label)
                    <option value="{{ $key }}" @selected(old('periode_kp', date('n') <= 2 ? 'Februari' : '') === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Pengusulan <span class="text-red-500">*</span></label>
            <input type="number" name="tahun_periode" required value="{{ old('tahun_periode', date('Y')) }}" class="w-full rounded-lg border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
        </div>
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Golongan Ruang yang Diusulkan <span class="text-red-500">*</span></label>
        <select name="golongan_tujuan_id" required class="w-full rounded-lg border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
            <option value="">-- Pilih Golongan Tingkat Berikutnya --</option>
            @foreach($golongans as $g)
                <option value="{{ $g->id }}" @selected(old('golongan_tujuan_id', ($pegawai->golongan_id ?? 0) + 1) == $g->id)>
                    {{ $g->nama_golongan }} - {{ $g->nama_pangkat }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- PERSYARATAN ADMINISTRASI KP --}}
<div class="space-y-4">
    <div class="border-b pb-2 flex items-center justify-between">
        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
            <span>📎</span> Unggah Dokumen Kelengkapan Persyaratan KP (PDF / JPG Maks 4MB)
        </h4>
        <span class="text-[11px] text-slate-500 font-medium">Diperlukan untuk verifikasi tata naskah &amp; paraf pimpinan</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                1. SK Pangkat Terakhir <span class="text-red-500">*</span>
            </label>
            <input type="file" name="file_sk_pangkat_terakhir" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-100 file:text-indigo-800 hover:file:bg-indigo-200">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                2. SKP Tahun Terakhir (N-1) Predikat Minimal "Baik" <span class="text-red-500">*</span>
            </label>
            <input type="file" name="file_skp_1" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                3. SKP 2 Tahun Lalu (N-2) Predikat Minimal "Baik" <span class="text-red-500">*</span>
            </label>
            <input type="file" name="file_skp_2" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                4. Fotocopy KARPEG / Identitas ASN <span class="text-red-500">*</span>
            </label>
            <input type="file" name="file_karpeg" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        </div>

        @if($pegawai->isDosen() || $pegawai->isPlp())
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    5. Dokumen PAK (Penetapan Angka Kredit) / Konversi Predikat Kinerja <span class="text-red-500">*</span>
                </label>
                <input type="file" name="file_pak" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
            </div>
        @endif

        <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                Dokumen Pendukung Lainnya (Opsional - Ijazah/Sertifikat/SK Tambahan)
            </label>
            <input type="file" name="file_pendukung" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        </div>
    </div>
</div>
