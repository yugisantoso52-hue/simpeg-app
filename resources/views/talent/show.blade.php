<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manajemen-talenta.index', ['tahun' => $tahun]) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                        &larr; Kembali ke Matriks 9-Kotak
                    </a>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>👤</span> Profil &amp; Evaluasi Talenta ASN
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Breakdown Nilai Sumbu Kinerja, Sumbu Potensi, dan Rekomendasi Rencana Suksesi Jabatan
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('manajemen-talenta.rekap', ['tahun' => $tahun]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                    <span>📋</span> Rekap Semua Pegawai
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Profil Singkat Pegawai & Posisi Kuadran Saat Ini --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-2xl font-black text-slate-700 shrink-0 overflow-hidden shadow-2xs">
                        @if($pegawai->foto)
                            <img src="{{ route('pegawai.foto', $pegawai->id) }}" alt="{{ $pegawai->nama }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($pegawai->nama, 0, 2)) }}
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-black text-gray-900">{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</h2>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $pegawai->status_asn ?? 'ASN' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 font-mono mt-0.5">
                            NIP. {{ $pegawai->nip }} &bull; Golongan: {{ $pegawai->golongan->nama_pangkat ?? '-' }} ({{ $pegawai->golongan->kode ?? '-' }})
                        </p>
                        <p class="text-xs font-semibold text-slate-700 mt-1">
                            {{ $pegawai->jabatan->nama_jabatan ?? '-' }} &bull; <span class="text-slate-500 font-normal">{{ $pegawai->unitKerja->nama_unit ?? '-' }}</span>
                        </p>
                    </div>
                </div>

                {{-- Card Posisi Kuadran --}}
                <div class="flex items-center gap-4 bg-slate-50 border border-slate-200 p-4 rounded-2xl shrink-0">
                    <div>
                        <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Penetapan Tahun {{ $tahun }}</span>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-sm font-black shadow-xs {{ $mapping->box_badge_color }}">
                                {{ $mapping->box_name }}
                            </span>
                        </div>
                        <span class="block text-xs font-bold text-slate-700 mt-1">
                            {{ $mapping->status_talenta }}
                        </span>
                    </div>
                    @if($mapping->is_suksesi_eligible)
                        <div class="px-3 py-2 bg-emerald-100 border border-emerald-300 rounded-xl text-center">
                            <span class="text-lg block">⭐</span>
                            <span class="text-[9px] font-black uppercase text-emerald-800">Talent Pool</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Rekomendasi Resmi --}}
            <div class="mt-4 pt-4 border-t border-gray-100 bg-slate-50/70 p-4 rounded-xl border border-slate-200">
                <span class="text-xs font-bold text-slate-800 block mb-1">💡 Rekomendasi Tindak Lanjut &amp; Pola Karier (PermenPAN-RB No. 3/2020):</span>
                <p class="text-xs text-slate-600 leading-relaxed">
                    {{ $mapping->rekomendasi_kebijakan }}
                </p>
            </div>
        </div>

        {{-- GRID BREAKDOWN DUA SUMBU PENILAIAN --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ======================================================== --}}
            {{-- KOLOM KIRI: SUMBU KINERJA (X)                           --}}
            {{-- ======================================================== --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Sumbu X (Horizontal)</span>
                        <h3 class="text-base font-black text-gray-900">Penilaian Kinerja Pegawai</h3>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-black text-gray-900 font-mono">{{ $mapping->sumbu_kinerja_nilai }}</span>
                        <span class="block text-[10px] font-bold px-2 py-0.5 rounded-full {{ $mapping->sumbu_kinerja_kategori === 'Tinggi' ? 'bg-emerald-100 text-emerald-800' : ($mapping->sumbu_kinerja_kategori === 'Sedang' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                            Kategori {{ $mapping->sumbu_kinerja_kategori }}
                        </span>
                    </div>
                </div>

                {{-- Detail Butir Sumbu Kinerja --}}
                <div class="space-y-3 text-xs">
                    
                    {{-- 1. SKP Tahun N --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">SKP Tahun Berjalan ({{ $tahun }})</span>
                            <span class="text-[11px] text-gray-500">Predikat: <b>{{ $mapping->predikat_skp_n ?? '-' }}</b> (Bobot 60%)</span>
                        </div>
                        <span class="font-mono font-bold text-slate-900 text-sm">
                            {{ $mapping->skor_skp_n !== null ? number_format($mapping->skor_skp_n, 1) : '-' }}
                        </span>
                    </div>

                    {{-- 2. SKP Tahun N-1 --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">SKP Tahun Sebelumnya ({{ $tahun - 1 }})</span>
                            <span class="text-[11px] text-gray-500">Predikat: <b>{{ $mapping->predikat_skp_n_minus_1 ?? '-' }}</b> (Bobot 40%)</span>
                        </div>
                        <span class="font-mono font-bold text-slate-900 text-sm">
                            {{ $mapping->skor_skp_n_minus_1 !== null ? number_format($mapping->skor_skp_n_minus_1, 1) : '-' }}
                        </span>
                    </div>

                    {{-- 3. Disiplin Presensi --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Disiplin Kehadiran (Presensi GPS)</span>
                            <span class="text-[11px] text-gray-500">Tingkat kehadiran tepat waktu tanpa pelanggaran</span>
                        </div>
                        <span class="font-mono font-bold text-slate-900 text-sm">
                            {{ $mapping->skor_disiplin_kehadiran !== null ? number_format($mapping->skor_disiplin_kehadiran, 1) . '%' : 'N/A' }}
                        </span>
                    </div>

                    {{-- 4. Keaktifan Logbook --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Aktivitas Kinerja Harian (Logbook)</span>
                            <span class="text-[11px] text-gray-500">Persentase laporan kerja harian diverifikasi atasan</span>
                        </div>
                        <span class="font-mono font-bold text-slate-900 text-sm">
                            {{ $mapping->skor_aktivitas_logbook !== null ? number_format($mapping->skor_aktivitas_logbook, 1) . '%' : 'N/A' }}
                        </span>
                    </div>

                </div>

                {{-- Catatan Ambang Batas Kinerja --}}
                <div class="p-3 rounded-xl bg-slate-50 text-[10px] text-slate-500 leading-relaxed border border-slate-100">
                    <b>Standar PermenPAN-RB No. 3/2020:</b><br>
                    &bull; Kinerja Tinggi: Skor &ge; 90.00 (Di Atas Ekspektasi / Sangat Baik)<br>
                    &bull; Kinerja Sedang: Skor 75.00 – 89.99 (Sesuai Ekspektasi / Baik)<br>
                    &bull; Kinerja Rendah: Skor &lt; 75.00 (Di Bawah Ekspektasi / Butuh Perbaikan)
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- KOLOM KANAN: SUMBU POTENSI (Y)                          --}}
            {{-- ======================================================== --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Sumbu Y (Vertikal)</span>
                        <h3 class="text-base font-black text-gray-900">Penilaian Potensi &amp; Kualifikasi</h3>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-black text-gray-900 font-mono">{{ $mapping->sumbu_potensi_nilai }}</span>
                        <span class="block text-[10px] font-bold px-2 py-0.5 rounded-full {{ $mapping->sumbu_potensi_kategori === 'Tinggi' ? 'bg-indigo-100 text-indigo-800' : ($mapping->sumbu_potensi_kategori === 'Sedang' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                            Kategori {{ $mapping->sumbu_potensi_kategori }}
                        </span>
                    </div>
                </div>

                {{-- Detail Butir Sumbu Potensi --}}
                <div class="space-y-3 text-xs">
                    
                    {{-- 1. Kualifikasi Pendidikan --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Kualifikasi Pendidikan Formal</span>
                            <span class="text-[11px] text-gray-500">Pendidikan Terakhir: <b>{{ $pegawai->pendidikan_terakhir ?? '-' }}</b> (Standar IP ASN)</span>
                        </div>
                        <span class="font-mono font-bold text-slate-900 text-sm">
                            {{ number_format($mapping->skor_kualifikasi_pendidikan, 1) }}
                        </span>
                    </div>

                    {{-- 2. Hak 20 JP Diklat --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Pengembangan Kompetensi (&ge;20 JP)</span>
                            <span class="text-[11px] text-gray-500">Target minimal 20 JP per tahun (PP 11/2017 &amp; PP 17/2020)</span>
                        </div>
                        <span class="font-mono font-bold text-slate-900 text-sm">
                            {{ number_format($mapping->skor_pengembangan_kompetensi, 1) }}
                        </span>
                    </div>

                    {{-- 3. Rekam Jejak Kepangkatan & Penghargaan --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Rekam Jejak, Masa Kerja &amp; Penghargaan</span>
                            <span class="text-[11px] text-gray-500">MKG: <b>{{ $pegawai->mkg_tahun ?? 0 }} Thn</b> &bull; Penghargaan: <b>{{ $pegawai->riwayatPenghargaan->count() }} Piagam</b></span>
                        </div>
                        <span class="font-mono font-bold text-slate-900 text-sm">
                            {{ number_format($mapping->skor_rekam_jejak, 1) }}
                        </span>
                    </div>

                    {{-- 4. Asesmen Center --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Uji Asesmen Kompetensi (BKN / Mandiri)</span>
                            <span class="text-[11px] text-gray-500">
                                @if($mapping->skor_asesmen_kompetensi !== null)
                                    Hasil Assessment Center resmi terdaftar
                                @else
                                    Belum mengikuti uji asesmen resmi (Komposit Kualifikasi+Diklat)
                                @endif
                            </span>
                        </div>
                        <span class="font-mono font-bold text-slate-900 text-sm">
                            {{ $mapping->skor_asesmen_kompetensi !== null ? number_format($mapping->skor_asesmen_kompetensi, 1) : '-' }}
                        </span>
                    </div>

                </div>

                {{-- Catatan Ambang Batas Potensi --}}
                <div class="p-3 rounded-xl bg-slate-50 text-[10px] text-slate-500 leading-relaxed border border-slate-100">
                    <b>Standar PermenPAN-RB No. 3/2020:</b><br>
                    &bull; Potensi Tinggi: Skor &ge; 90.00 (Kualifikasi S3, Diklat &ge;20 JP, Asesmen Memenuhi Syarat)<br>
                    &bull; Potensi Sedang: Skor 75.00 – 89.99 (Kualifikasi S2/S1 Senior, Diklat 10–19 JP)<br>
                    &bull; Potensi Rendah: Skor &lt; 75.00 (Perlu penguatan kualifikasi &amp; uji kompetensi lanjutan)
                </div>
            </div>

        </div>

        {{-- FORM VALIDASI KOMITE TALENTA (KHUSUS PIMPINAN & TIM KEPEGAWAIAN) --}}
        @if(Auth::user()->canManageTalentManagement())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="border-b border-gray-100 pb-3 mb-4">
                    <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
                        <span>🛡️</span> Sidang Komite Talenta &amp; Penetapan Suksesi
                    </h3>
                    <p class="text-xs text-gray-500">
                        Validasi hasil kalkulasi otomatis oleh Komite Talenta Instansi sesuai amanat PermenPAN-RB No. 3 Tahun 2020
                    </p>
                </div>

                <form action="{{ route('manajemen-talenta.validate', $pegawai->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $tahun }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="status_validasi" class="block text-xs font-bold text-gray-700 mb-1">Status Validasi</label>
                            <select name="status_validasi" id="status_validasi" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                                <option value="Draft" {{ $mapping->status_validasi === 'Draft' ? 'selected' : '' }}>Draft (Kalkulasi Sistem)</option>
                                <option value="Ditinjau Komite" {{ $mapping->status_validasi === 'Ditinjau Komite' ? 'selected' : '' }}>Ditinjau Komite Talenta</option>
                                <option value="Ditetapkan PPK" {{ $mapping->status_validasi === 'Ditetapkan PPK' ? 'selected' : '' }}>Ditetapkan Pejabat Pembina Kepegawaian (PPK)</option>
                            </select>
                        </div>

                        <div>
                            <label for="is_suksesi_eligible" class="block text-xs font-bold text-gray-700 mb-1">Kelayakan Talent Pool Suksesi</label>
                            <select name="is_suksesi_eligible" id="is_suksesi_eligible" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                                <option value="1" {{ $mapping->is_suksesi_eligible ? 'selected' : '' }}>Ya - Masuk Kelompok Rencana Suksesi (Talent Pool)</option>
                                <option value="0" {{ !$mapping->is_suksesi_eligible ? 'selected' : '' }}>Tidak - Belum Memenuhi Kriteria Suksesi</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="catatan_komite" class="block text-xs font-bold text-gray-700 mb-1">Catatan &amp; Arahan Komite Talenta</label>
                        <textarea name="catatan_komite" id="catatan_komite" rows="3" placeholder="Masukkan arahan penugasan khusus, rekomendasi diklat, atau catatan kelayakan suksesi..." class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 p-2.5">{{ $mapping->catatan_komite }}</textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <div class="text-[11px] text-gray-500">
                            @if($mapping->validated_at)
                                Terakhir divalidasi pada {{ $mapping->validated_at->translatedFormat('d F Y H:i') }}
                                @if($mapping->validator)
                                    oleh <b>{{ $mapping->validator->name }}</b>
                                @endif
                            @endif
                        </div>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 shadow-xs transition">
                            💾 Simpan Penetapan Komite
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- RIWAYAT ASESMEN KOMPETENSI PEGAWAI --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <div>
                    <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
                        <span>📝</span> Rekam Jejak Uji Kompetensi &amp; Asesmen
                    </h3>
                    <p class="text-xs text-gray-500">
                        Hasil uji Assessment Center BKN, LPPM, atau Puslatbang LAN
                    </p>
                </div>
                @if(Auth::user()->canManageTalentManagement())
                    <a href="{{ route('manajemen-talenta.asesmen.create', ['pegawai_id' => $pegawai->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-xs transition">
                        <span>➕</span> Tambah Hasil Asesmen
                    </a>
                @endif
            </div>

            @if($assessments->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($assessments as $ast)
                        <div class="py-3 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                            <div>
                                <span class="font-bold text-gray-900 block text-sm">{{ $ast->metode_asesmen }}</span>
                                <span class="text-gray-500 text-[11px]">
                                    Penyelenggara: <b>{{ $ast->lembaga_penyelenggara }}</b> &bull; Tgl: {{ $ast->tanggal_asesmen->translatedFormat('d M Y') }}
                                </span>
                                @if($ast->nomor_surat)
                                    <span class="text-gray-400 font-mono text-[10px] block">No. Surat: {{ $ast->nomor_surat }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <span class="font-mono font-black text-base text-gray-900">{{ $ast->skor_total }}</span>
                                    <span class="block text-[10px] font-bold text-indigo-700">{{ $ast->kategori_kelayakan }}</span>
                                </div>
                                @if($ast->file_laporan_url)
                                    <a href="{{ $ast->file_laporan_url }}" target="_blank" class="px-2.5 py-1 text-[11px] font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                                        📄 Berkas
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-500 italic py-4 text-center">
                    Belum ada data uji kompetensi resmi yang tercatat untuk pegawai ini.
                </p>
            @endif
        </div>

        {{-- RIWAYAT TRAJEKTORI TALENTA TAHUN KE TAHUN --}}
        @if($historyMappings->count() > 1)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="pb-3 border-b border-gray-100 mb-4">
                    <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
                        <span>📈</span> Trajektori Kuadran Multi-Tahun
                    </h3>
                    <p class="text-xs text-gray-500">
                        Perkembangan pergerakan posisi talenta ASN antar tahun evaluasi
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-200 text-slate-700 font-bold uppercase text-[10px]">
                                <th class="py-2.5 px-3">Tahun</th>
                                <th class="py-2.5 px-3 text-center">Skor Kinerja (X)</th>
                                <th class="py-2.5 px-3 text-center">Skor Potensi (Y)</th>
                                <th class="py-2.5 px-3 text-center">Kuadran</th>
                                <th class="py-2.5 px-3">Status Talenta</th>
                                <th class="py-2.5 px-3 text-center">Talent Pool</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($historyMappings as $hm)
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-gray-900">{{ $hm->tahun }}</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $hm->sumbu_kinerja_nilai }} ({{ $hm->sumbu_kinerja_kategori }})</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $hm->sumbu_potensi_nilai }} ({{ $hm->sumbu_potensi_kategori }})</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $hm->box_badge_color }}">
                                            Kotak {{ $hm->kuadran_box }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-gray-700">{{ $hm->status_talenta }}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        {!! $hm->is_suksesi_eligible ? '<span class="text-emerald-600 font-bold">⭐ Ya</span>' : '<span class="text-gray-400">-</span>' !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
