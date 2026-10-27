<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manajemen-talenta.idp', ['tahun' => $tahun]) }}" class="text-xs font-semibold text-teal-600 hover:text-teal-700 flex items-center gap-1">
                        &larr; Kembali ke Daftar IDP
                    </a>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>📑</span> Roadmap Individual Development Plan (IDP)
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Rencana Pengembangan Kompetensi Mandiri Pegawai ASN Berbasis Meritokrasi (Tahun Evaluasi: {{ $tahun }})
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-teal-600 rounded-lg hover:bg-teal-700 shadow-sm transition">
                    <span>🖨️</span> Cetak Lembar IDP
                </button>
                <a href="{{ route('pegawai.show', $pegawai->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                    <span>👤</span> Profil Pegawai
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10 space-y-6">

        {{-- Profil Singkat Pegawai --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-teal-600 text-white flex items-center justify-center font-black text-2xl shadow-sm shrink-0">
                    {{ strtoupper(substr($pegawai->nama, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-lg font-black text-gray-900 leading-snug">{{ $pegawai->nama }}</h2>
                    <p class="text-xs text-gray-500 font-mono mt-0.5">NIP. {{ $pegawai->nip }} &bull; Gol. <b>{{ $pegawai->golongan->kode ?? '-' }}</b> ({{ $pegawai->golongan->nama_golongan ?? '-' }})</p>
                    <div class="text-xs text-gray-700 mt-1">
                        Jabatan: <b>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</b> &bull; Unit: <b>{{ $pegawai->unitKerja->nama_unit ?? 'Fakultas Keperawatan' }}</b>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-teal-50/60 p-4 rounded-2xl border border-teal-200 shrink-0">
                <div class="text-center">
                    <span class="text-[10px] font-bold text-gray-500 block uppercase">Kuadran Talenta</span>
                    <span class="text-lg font-black text-teal-800">Kotak {{ $mapping?->kuadran_box ?? '-' }}</span>
                    <span class="text-[10px] text-teal-600 block">{{ $mapping?->box_name ?? 'Normal' }}</span>
                </div>
                <div class="h-10 w-px bg-teal-200"></div>
                <div class="text-center">
                    <span class="text-[10px] font-bold text-gray-500 block uppercase">Status 20 JP</span>
                    <span class="text-lg font-black {{ $totalJp >= 20 ? 'text-emerald-700' : 'text-rose-600' }}">{{ $totalJp }} / 20 JP</span>
                    <span class="text-[10px] {{ $totalJp >= 20 ? 'text-emerald-600' : 'text-rose-500' }} block">
                        {{ $totalJp >= 20 ? 'Terpenuhi' : 'Kurang ' . $sisaJp . ' JP' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Progress Bar Pemenuhan Target 20 JP --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-700">Akumulasi Jam Pelajaran (JP) Tahun {{ $tahun }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Standar mandatori minimal 20 JP per tahun kalender kerja.</p>
                </div>
                <span class="text-lg font-black {{ $totalJp >= 20 ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $persen }}% Terpenuhi
                </span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                <div class="h-3 rounded-full transition-all {{ $totalJp >= 20 ? 'bg-emerald-500' : 'bg-teal-600' }}" style="width: {{ $persen }}%"></div>
            </div>
            <div class="flex justify-between text-[11px] text-gray-500 pt-1">
                <span>Realisasi: <b>{{ $totalJp }} JP</b></span>
                <span>Target Mandatori: <b>20 JP</b></span>
            </div>
        </div>

        {{-- Tabel Riwayat Diklat yang Pernah Diikuti --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-800">Rekam Jejak Diklat &amp; Pelatihan Terdaftar</h3>
                <span class="text-xs font-mono text-gray-500">{{ $pegawai->riwayatDiklat->count() }} Kegiatan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-gray-200">
                    <thead class="bg-slate-50 text-gray-600 font-black uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">No</th>
                            <th class="px-4 py-3">Nama Diklat / Seminar / Workshop</th>
                            <th class="px-3 py-3">Penyelenggara</th>
                            <th class="px-3 py-3 text-center">Tahun / Tanggal</th>
                            <th class="px-3 py-3 text-center">Jam (JP)</th>
                            <th class="px-3 py-3 text-center">Sertifikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium text-gray-800">
                        @forelse($pegawai->riwayatDiklat as $idx => $d)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900">{{ $d->nama_diklat }}</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">{{ $d->jenis_diklat ?? 'Diklat Teknis/Fungsional' }}</div>
                                </td>
                                <td class="px-3 py-3 text-gray-600">{{ $d->penyelenggara ?? '-' }}</td>
                                <td class="px-3 py-3 text-center text-gray-600 font-mono">
                                    {{ $d->tanggal_selesai ? \Carbon\Carbon::parse($d->tanggal_selesai)->format('d/m/Y') : ($d->tahun ?? '-') }}
                                </td>
                                <td class="px-3 py-3 text-center font-bold font-mono text-teal-700">
                                    {{ $d->jumlah_jam ?? 0 }} JP
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($d->file_sertifikat)
                                        <span class="text-emerald-600 font-bold">Ada (PDF)</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-6 text-center text-gray-500 text-xs">
                                    Belum ada catatan riwayat pelatihan/diklat yang diinputkan untuk pegawai ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Roadmap Rencana Aksi Pengembangan Kompetensi (Kurikulum IDP 1 Tahun) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div class="pb-2 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-800">Rekomendasi Roadmap Pembelajaran Individu (IDP)</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Disusun berdasarkan analisis kesenjangan asesmen &amp; kuadran penempatan talenta ASN.</p>
                </div>
                <span class="text-xs font-bold text-teal-700 bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-200">
                    Target: 20 JP Terpenuhi
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                {{-- Modul 1: Manajerial --}}
                <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/30 space-y-2">
                    <div class="flex items-center gap-1.5 font-bold text-indigo-900">
                        <span>👔</span> Kompetensi Manajerial
                    </div>
                    <p class="text-[11px] text-gray-600 leading-relaxed">
                        @if(($mapping?->kuadran_box ?? 0) >= 7)
                            Pelatihan Kepemimpinan Administrator (PKA) / Pelatihan Kepemimpinan Pengawas (PKP) atau Executive Leadership Coaching.
                        @else
                            Manajemen Waktu, Komunikasi Efektif di Tempat Kerja, dan Manajemen Konflik Internal.
                        @endif
                    </p>
                    <span class="inline-block text-[10px] font-mono font-bold text-indigo-700 bg-white px-2 py-0.5 rounded border border-indigo-200">
                        Alokasi Target: 8 JP
                    </span>
                </div>

                {{-- Modul 2: Teknis --}}
                <div class="p-4 rounded-xl border border-teal-200 bg-teal-50/30 space-y-2">
                    <div class="flex items-center gap-1.5 font-bold text-teal-900">
                        <span>🔬</span> Kompetensi Teknis Bidang
                    </div>
                    <p class="text-[11px] text-gray-600 leading-relaxed">
                        Pengembangan Kurikulum Keperawatan Berbasis Outcome-Based Education (OBE), Metodologi Penelitian Klinis, atau Pelatihan Laboratorium Simulasi OSCE.
                    </p>
                    <span class="inline-block text-[10px] font-mono font-bold text-teal-700 bg-white px-2 py-0.5 rounded border border-teal-200">
                        Alokasi Target: 8 JP
                    </span>
                </div>

                {{-- Modul 3: Sosio-Kultural --}}
                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/30 space-y-2">
                    <div class="flex items-center gap-1.5 font-bold text-amber-900">
                        <span>🤝</span> Kompetensi Sosio-Kultural
                    </div>
                    <p class="text-[11px] text-gray-600 leading-relaxed">
                        Etika Pelayanan Publik ASN BerAKHLAK, Keberagaman Budaya dalam Pelayanan Kesehatan, dan Integritas Anti-Korupsi.
                    </p>
                    <span class="inline-block text-[10px] font-mono font-bold text-amber-700 bg-white px-2 py-0.5 rounded border border-amber-200">
                        Alokasi Target: 4 JP
                    </span>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
