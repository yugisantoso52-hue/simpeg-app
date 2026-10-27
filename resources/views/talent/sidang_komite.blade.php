<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manajemen-talenta.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1">
                        &larr; Kembali ke Manajemen Talenta
                    </a>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>⚖️</span> Sidang Komite Talenta &amp; Penetapan Talent Pool
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Dewan Pertimbangan Karier &amp; Komite Suksesi ASN Fakultas Keperawatan Universitas Riau (Tahun {{ $tahun }})
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Tombol SOP Sidang Komite Talenta --}}
                <x-sop-modal title="SOP Sidang Pleno Komite Talenta & Berita Acara Penetapan" 
                             buttonLabel="SOP Sidang Komite" 
                             badge="Dewan Komite ASN" 
                             color="indigo">
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-200">
                            <h4 class="font-bold text-indigo-950 text-xs mb-1 flex items-center gap-1.5">
                                <x-icon name="scale" class="w-4 h-4 text-indigo-700" />
                                Wewenang &amp; Tugas Dewan Komite Talenta
                            </h4>
                            <p class="text-indigo-900 text-[11px] leading-relaxed">
                                Komite Manajemen Talenta ASN bertindak sebagai dewan pertimbangan karir institusi yang berwenang menelaah, memvalidasi objektivitas kuadran talenta, dan menetapkan anggota <em>Talent Pool</em> serta nominasi suksesi jabatan tingkat fakultas.
                            </p>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px]">1</span>
                                Mekanisme Sidang Pleno Komite
                            </h4>
                            <div class="space-y-2 ps-2 border-l-2 border-indigo-200 text-[11px] text-slate-600">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">1. Pra-Sidang (Penyusunan Draf)</strong>
                                    Sekretariat (Ka Pokja Kepegawaian) menyajikan draf hasil scoring kuadran 9-kotak dan rekap data asesmen BKN.
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">2. Sidang Pembahasan (Deliberasi)</strong>
                                    Dekan bersama para Wakil Dekan meninjau integritas, catatan disiplin (PP 94/2021), dan rekam jejak kepemimpinan calon suksesor.
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">3. Penetapan &amp; Penandatanganan Berita Acara</strong>
                                    Status diubah menjadi <strong>Ditetapkan PPK</strong> dan Berita Acara resmi ditandatangani secara pleno sebagai dokumen legal suksesi.
                                </div>
                            </div>
                        </div>
                    </div>
                </x-sop-modal>

                <a href="{{ route('manajemen-talenta.sidang-komite.cetak', ['tahun' => $tahun]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 rounded-xl shadow-2xs hover:bg-emerald-700 transition">
                    <span>🖨️</span> Cetak Berita Acara (PDF)
                </a>
                <a href="{{ route('manajemen-talenta.suksesi.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 shadow-2xs transition">
                    <span>👑</span> Rencana Suksesi
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

        {{-- 4 Kartu Status Validasi Komite --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kandidat Talent Pool (Kotak 7,8,9)</div>
                <div class="text-3xl font-black text-gray-900 mt-1">{{ $totalKandidat }} <span class="text-sm font-normal text-gray-500">Pegawai</span></div>
                <div class="text-[10px] text-gray-400 mt-1">Eligible Masuk Talent Pool</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-emerald-200 bg-emerald-50/30 shadow-sm">
                <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Ditetapkan PPK / Dekan</div>
                <div class="text-3xl font-black text-emerald-600 mt-1">{{ $totalDitetapkan }} <span class="text-sm font-normal text-emerald-400">Pegawai</span></div>
                <div class="text-[10px] text-emerald-700 mt-1">Sah Melalui Berita Acara Sidang</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-blue-200 bg-blue-50/30 shadow-sm">
                <div class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Sedang Ditinjau Komite</div>
                <div class="text-3xl font-black text-blue-600 mt-1">{{ $totalDitinjau }} <span class="text-sm font-normal text-blue-400">Pegawai</span></div>
                <div class="text-[10px] text-blue-700 mt-1">Dalam Tahap Verifikasi Dokumen</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-amber-200 bg-amber-50/30 shadow-sm">
                <div class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Status Draft Awal</div>
                <div class="text-3xl font-black text-amber-600 mt-1">{{ $totalDraft }} <span class="text-sm font-normal text-amber-400">Pegawai</span></div>
                <div class="text-[10px] text-amber-700 mt-1">Hasil Perhitungan Algoritma SIKAP</div>
            </div>
        </div>

        {{-- Susunan Dewan Komite Talenta --}}
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 shadow-md space-y-3">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-400">Dewan Pertimbangan &amp; Otoritas</span>
                    <h3 class="text-base font-black">Susunan Komite Manajemen Talenta ASN Fakultas Keperawatan UNRI</h3>
                </div>
                <span class="text-xs font-mono bg-white/10 text-indigo-200 px-3 py-1 rounded-full border border-white/20">
                    SK Dekan UNRI
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs pt-1">
                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-[10px] text-indigo-300 block font-bold uppercase">Ketua Komite:</span>
                    <span class="font-bold text-white block mt-0.5">Dekan Fakultas Keperawatan</span>
                    <span class="text-[10px] text-gray-400">Pejabat Pembina / Otoritas Utama</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-[10px] text-indigo-300 block font-bold uppercase">Sekretaris Komite:</span>
                    <span class="font-bold text-white block mt-0.5">Wakil Dekan II (Keu &amp; Kepeg)</span>
                    <span class="text-[10px] text-gray-400">Pengelola Sumber Daya</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-[10px] text-indigo-300 block font-bold uppercase">Anggota Komite:</span>
                    <span class="font-bold text-white block mt-0.5">Wakil Dekan I &amp; III</span>
                    <span class="text-[10px] text-gray-400">Bidang Akademik &amp; Kemahasiswaan</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-[10px] text-indigo-300 block font-bold uppercase">Sekretariat Pelaksana:</span>
                    <span class="font-bold text-white block mt-0.5">Ka Pokja Keuangan &amp; Kepeg</span>
                    <span class="text-[10px] text-gray-400">Verifikator Data &amp; Dokumen</span>
                </div>
            </div>
        </div>

        {{-- Form Penetapan Keputusan Sidang Komite Talenta --}}
        <form method="POST" action="{{ route('manajemen-talenta.sidang-komite.tetapkan') }}" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider">Daftar Kandidat Talent Pool untuk Penetapan Sidang</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Pilih pegawai untuk pengesahan status validasi resmi oleh Komite Talenta.</p>
                    </div>

                    @if(Auth::user()->canManageTalentManagement())
                        <div class="flex items-center gap-2 flex-wrap">
                            <select name="status_validasi" class="text-xs rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="Ditetapkan PPK">Ditetapkan PPK (Sah)</option>
                                <option value="Ditinjau Komite">Ditinjau Komite</option>
                                <option value="Draft">Kembalikan ke Draft</option>
                            </select>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-black text-xs hover:bg-indigo-700 shadow-xs transition">
                                ✍️ Tetapkan Status Terpilih
                            </button>
                        </div>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs divide-y divide-gray-200">
                        <thead class="bg-slate-50 text-gray-600 font-black uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3.5 text-center w-10">
                                    <input type="checkbox" onclick="document.querySelectorAll('.mapping-chk').forEach(c => c.checked = this.checked)" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-4 py-3.5">Nama Pegawai &amp; Unit</th>
                                <th class="px-3 py-3.5 text-center">Kuadran</th>
                                <th class="px-3 py-3.5 text-center">Nilai Kinerja</th>
                                <th class="px-3 py-3.5 text-center">Nilai Potensi</th>
                                <th class="px-3 py-3.5 text-center">Status Validasi</th>
                                <th class="px-4 py-3.5">Catatan Pertimbangan Komite</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium text-gray-800">
                            @forelse($talentPoolCandidates as $cand)
                                <tr class="hover:bg-indigo-50/20 transition">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" name="mapping_ids[]" value="{{ $cand->id }}" class="mapping-chk rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900 text-xs">{{ $cand->pegawai?->nama_lengkap ?? $cand->pegawai?->nama ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">
                                            NIP. {{ $cand->pegawai?->nip ?? '-' }} &bull; Gol. <b>{{ $cand->pegawai?->golongan?->kode ?? $cand->pegawai?->golongan?->nama_golongan ?? '-' }}</b>
                                        </div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            {{ $cand->pegawai?->jabatan?->nama_jabatan ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full font-black text-[10px] {{ $cand->kuadran_box === 9 ? 'bg-purple-100 text-purple-900 border border-purple-200' : ($cand->kuadran_box === 8 ? 'bg-emerald-100 text-emerald-900' : 'bg-blue-100 text-blue-900') }}">
                                            Kotak {{ $cand->kuadran_box }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center font-bold text-emerald-700">
                                        {{ $cand->sumbu_kinerja_nilai }}
                                    </td>
                                    <td class="px-3 py-3 text-center font-bold text-indigo-700">
                                        {{ $cand->sumbu_potensi_nilai }}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] {{ $cand->status_validasi === 'Ditetapkan PPK' ? 'bg-emerald-100 text-emerald-800' : ($cand->status_validasi === 'Ditinjau Komite' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ $cand->status_validasi }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-[11px] text-gray-600">
                                        {{ $cand->catatan_komite ?? 'Memenuhi syarat administratif & nilai asesmen kompetensi untuk suksesi jabatan.' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-xs">
                                        Belum ada pegawai yang masuk ke dalam kriteria Talent Pool (Kotak 7, 8, 9).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Input Catatan Komite Tambahan --}}
                <div class="p-6 bg-gray-50 border-t border-gray-200 space-y-2">
                    <label class="font-bold text-gray-800 text-xs block">Catatan Tambahan Dewan Komite Talenta (Akan Disimpan pada Riwayat Penetapan):</label>
                    <textarea name="catatan_komite" rows="2" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Masukkan catatan pertimbangan khusus hasil musyawarah sidang komite..."></textarea>
                </div>
            </div>
        </form>

    </div>
</x-app-layout>
