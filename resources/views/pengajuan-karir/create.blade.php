<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Formulir Pengajuan {{ $jenis === 'KP' ? 'Kenaikan Pangkat (PNS)' : 'Kenaikan Gaji Berkala (KGB)' }}"
            subtitle="Pastikan data dan dokumen pendukung sesuai dengan peraturan BKN dan KemenPAN-RB"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">

            {{-- Kartu Identitas Pegawai Pengusul --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <span>👤</span> Data Identitas Pegawai Pengusul
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $jenis === 'KP' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        Usulan {{ $jenis }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Nama Lengkap:</span>
                        <strong class="text-slate-900 text-sm">{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">NIP / NI PPPK:</span>
                        <strong class="text-slate-900 font-mono">{{ $pegawai->nip }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Jabatan & Unit Kerja:</span>
                        <span class="text-slate-800">{{ $pegawai->jabatan->nama_jabatan ?? '-' }} / {{ $pegawai->unitKerja->nama_unit ?? 'Fakultas Keperawatan' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Pangkat / Golongan Saat Ini:</span>
                        <span class="text-slate-800 font-semibold">{{ $pegawai->golongan->nama_pangkat ?? '-' }} ({{ $pegawai->golongan->nama_golongan ?? '-' }})</span>
                    </div>
                </div>
            </div>

            {{-- FORM SUBMIT --}}
            <form action="{{ route('pengajuan-karir.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="jenis_pengajuan" value="{{ $jenis }}">

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">

                    @if($jenis === 'KP')
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
                                            <option value="{{ $key }}" {{ date('n') <= 2 && $key === 'Februari' ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Pengusulan <span class="text-red-500">*</span></label>
                                    <input type="number" name="tahun_periode" required value="{{ date('Y') }}" class="w-full rounded-lg border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Golongan Ruang yang Diusulkan <span class="text-red-500">*</span></label>
                                <select name="golongan_tujuan_id" required class="w-full rounded-lg border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">-- Pilih Golongan Tingkat Berikutnya --</option>
                                    @foreach($golongans as $g)
                                        <option value="{{ $g->id }}" {{ ($pegawai->golongan_id + 1) == $g->id ? 'selected' : '' }}>
                                            {{ $g->nama_golongan }} - {{ $g->nama_pangkat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    @else
                        {{-- Pengaturan KGB --}}
                        <div class="rounded-xl bg-amber-50/70 p-4 border border-amber-100 space-y-3">
                            <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                                <span>💰</span> Rencana Terhitung Mulai Tanggal (TMT) KGB Baru
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">TMT KGB Baru <span class="text-red-500">*</span></label>
                                    <input type="date" name="tmt_baru" required value="{{ $pegawai->kgb_berikutnya ? \Carbon\Carbon::parse($pegawai->kgb_berikutnya)->format('Y-m-d') : date('Y-m-d') }}" class="w-full rounded-lg border-slate-300 text-xs focus:ring-amber-500 focus:border-amber-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Masa Kerja Golongan (MKG Saat Ini)</label>
                                    <div class="text-xs font-semibold text-slate-700 pt-2">
                                        {{ $pegawai->mkg_tahun ?? 0 }} Tahun {{ $pegawai->mkg_bulan ?? 0 }} Bulan
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Lampiran Berkas Persyaratan --}}
                    <div class="space-y-4">
                        <div class="border-b pb-2 flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                <span>📎</span> Unggah Dokumen Kelengkapan Persyaratan (PDF / JPG Maks 4MB)
                            </h4>
                            <span class="text-[11px] text-slate-500 font-medium">Diperlukan untuk verifikasi tata naskah & paraf pimpinan</span>
                        </div>

                        @if($jenis === 'KGB')
                            {{-- PERSYARATAN ADMINISTRASI KGB --}}
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

                        @else
                            {{-- PERSYARATAN ADMINISTRASI KP --}}
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
                        @endif
                    </div>

                    {{-- Catatan Pegawai --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan Pengusul</label>
                        <textarea name="catatan_pegawai" rows="3" placeholder="Tuliskan keterangan bila ada (misal: telah memenuhi masa kerja 2 tahun untuk KGB, atau kelengkapan PAK telah diverifikasi di Sister/SIASN)" class="w-full rounded-lg border-slate-300 text-xs focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    {{-- Alur Paraf & Penandatanganan Informasi --}}
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-200 text-xs text-slate-600 space-y-1.5">
                        <div class="font-bold text-slate-800">Alur Verifikasi Administratif Surat Resmi:</div>
                        <ol class="list-decimal list-inside space-y-1 text-[11px]">
                            <li><strong>Pegawai</strong> submit formulir dan berkas pengajuan secara mandiri.</li>
                            <li><strong>Kepala Bagian Umum</strong> memverifikasi kelengkapan berkas fisik & membubuhkan paraf administrasi.</li>
                            <li><strong>Wakil Dekan Bidang Keuangan dan Umum (WD II)</strong> memverifikasi dan membubuhkan paraf pimpinan.</li>
                            <li><strong>Dekan</strong> menandatangani Surat Keputusan / Surat Usulan resmi ke Rektor UNRI.</li>
                        </ol>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t">
                        <a href="{{ route('pengajuan-karir.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-md transition {{ $jenis === 'KP' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-amber-600 hover:bg-amber-700' }}">
                            🚀 Kirim Usulan {{ $jenis }}
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</x-app-layout>
