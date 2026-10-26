@props([
    'pegawai',
])

{{-- Pengaturan KGB --}}
<div class="rounded-xl bg-amber-50/70 p-4 border border-amber-100 space-y-3">
    <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
        <span>💰</span> Rencana Terhitung Mulai Tanggal (TMT) KGB Baru
    </h4>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">TMT KGB Baru <span class="text-red-500">*</span></label>
            <input type="date" name="tmt_baru" required value="{{ old('tmt_baru', $pegawai->kgb_berikutnya ? \Carbon\Carbon::parse($pegawai->kgb_berikutnya)->format('Y-m-d') : date('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 text-xs focus:ring-amber-500 focus:border-amber-500">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Masa Kerja Golongan (MKG Saat Ini)</label>
            <div class="text-xs font-semibold text-slate-700 pt-2">
                {{ $pegawai->mkg_tahun ?? 0 }} Tahun {{ $pegawai->mkg_bulan ?? 0 }} Bulan
            </div>
        </div>
    </div>
</div>

{{-- PERSYARATAN ADMINISTRASI KGB --}}
<div class="space-y-4">
    <div class="border-b pb-2 flex items-center justify-between">
        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
            <span>📎</span> Unggah Dokumen Kelengkapan Persyaratan KGB (PDF / JPG Maks 4MB)
        </h4>
        <span class="text-[11px] text-slate-500 font-medium">Diperlukan untuk verifikasi tata naskah &amp; paraf pimpinan</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                1. SK Pangkat Terakhir <span class="text-red-500">*</span>
            </label>
            <input type="file" name="file_sk_pangkat_terakhir" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200">
            <p class="text-[10px] text-slate-400 mt-0.5">Salinan SK Pangkat terakhir / SK Pengangkatan</p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                2. SK / Pemberitahuan KGB Terakhir <span class="text-red-500">*</span>
            </label>
            <input type="file" name="file_sk_kgb_terakhir" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200">
            <p class="text-[10px] text-slate-400 mt-0.5">Surat pemberitahuan kenaikan gaji berkala sebelumnya</p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                3. Penilaian Kinerja (SKP) Tahun Terakhir (N-1) <span class="text-red-500">*</span>
            </label>
            <input type="file" name="file_skp_1" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
            <p class="text-[10px] text-slate-400 mt-0.5">Predikat kinerja minimal bernilai "Baik"</p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                4. Penilaian Kinerja (SKP) 2 Tahun Lalu (N-2) <span class="text-red-500">*</span>
            </label>
            <input type="file" name="file_skp_2" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
            <p class="text-[10px] text-slate-400 mt-0.5">Predikat kinerja minimal bernilai "Baik"</p>
        </div>

        <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                Dokumen Pendukung Lainnya (Opsional)
            </label>
            <input type="file" name="file_pendukung" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        </div>
    </div>
</div>
