<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manajemen-talenta.suksesi.index', ['tahun' => $tahun]) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                        &larr; Kembali ke Rencana Suksesi
                    </a>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>🎯</span> Job Matching &amp; Gap Analysis Suksesi Jabatan
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Target Jabatan: <b class="text-gray-900">{{ $jabatan->nama_jabatan }}</b> &bull; {{ $jabatan->unitKerja->nama_unit ?? '-' }} (Kelas {{ $jabatan->kelas_jabatan ?? '-' }})
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($anjab)
                    <a href="{{ route('anjab.show', $anjab->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                        <span>📑</span> Dokumen Anjab
                    </a>
                @endif
                <a href="{{ route('manajemen-talenta.index', ['tahun' => $tahun]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 shadow-sm transition">
                    <span>📊</span> Matriks 9-Kotak
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- KOLOM KIRI: STANDAR KOMPETENSI JABATAN (SKJ DARI ANJAB) --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- Card Profil Jabatan Target --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="pb-3 border-b border-gray-100">
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600">Jabatan Target Suksesi</span>
                        <h2 class="text-lg font-black text-gray-900">{{ $jabatan->nama_jabatan }}</h2>
                        <p class="text-xs text-gray-500 font-mono mt-0.5">Kode: {{ $jabatan->kode_jabatan ?? '-' }}</p>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="font-bold text-gray-500 block text-[10px] uppercase">Unit Kerja</span>
                            <span class="font-semibold text-gray-900">{{ $jabatan->unitKerja->nama_unit ?? '-' }}</span>
                        </div>

                        <div>
                            <span class="font-bold text-gray-500 block text-[10px] uppercase">Kelas Jabatan</span>
                            <span class="font-mono font-bold text-indigo-700 text-sm">Kelas {{ $jabatan->kelas_jabatan ?? '-' }}</span>
                        </div>

                        <div>
                            <span class="font-bold text-gray-500 block text-[10px] uppercase">Pejabat Saat Ini (Inkumben)</span>
                            @forelse($incumbents as $inc)
                                <div class="p-2 rounded-lg bg-slate-50 border border-slate-200 mt-1">
                                    <span class="font-bold text-gray-900 block">{{ $inc->nama_lengkap ?? $inc->nama }}</span>
                                    <span class="text-[10px] font-mono text-gray-500">NIP. {{ $inc->nip }} &bull; Gol. {{ $inc->golongan->kode ?? '-' }}</span>
                                </div>
                            @empty
                                <span class="inline-block mt-1 px-2.5 py-1 rounded bg-rose-100 text-rose-800 font-bold text-[11px]">
                                    Posisi Sedang Lowong (Kosong)
                                </span>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Card Standar Syarat Kualifikasi Anjab --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="pb-2 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase text-gray-800 tracking-wider">Syarat Kualifikasi (Anjab)</h3>
                        <span class="text-xs">📜</span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="font-bold text-gray-700 block mb-0.5">🎓 Kualifikasi Pendidikan</span>
                            <p class="text-gray-600 leading-relaxed text-[11px]">
                                {{ $anjab->kualifikasi_pendidikan ?? 'Sesuai rumpun ilmu keperawatan / kesehatan / manajemen' }}
                            </p>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="font-bold text-gray-700 block mb-0.5">📜 Kualifikasi Pelatihan / Diklat</span>
                            <p class="text-gray-600 leading-relaxed text-[11px]">
                                {{ $anjab->kualifikasi_pelatihan ?? 'Pelatihan Kepemimpinan Struktural / Pelatihan Teknis Terkait' }}
                            </p>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="font-bold text-gray-700 block mb-0.5">💼 Kualifikasi Pengalaman</span>
                            <p class="text-gray-600 leading-relaxed text-[11px]">
                                {{ $anjab->kualifikasi_pengalaman ?? 'Memiliki pengalaman di bidang tugas terkait minimal 2-3 tahun' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN: DAFTAR NOMINASI & REKOMENDASI KANDIDAT TALENT POOL --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Bagian 1: Kandidat yang Sudah Resmi Dinominasikan --}}
                @if($existingPlans->count() > 0)
                    <div class="bg-white rounded-2xl shadow-sm border-2 border-indigo-200 p-6">
                        <div class="pb-3 border-b border-gray-100 flex items-center justify-between mb-4">
                            <div>
                                <span class="px-2 py-0.5 text-[10px] font-black rounded bg-indigo-100 text-indigo-800 uppercase">Resmi Terdaftar</span>
                                <h3 class="text-sm font-black text-gray-900 mt-1">Kandidat Suksesor yang Telah Ditetapkan (Tahun {{ $tahun }})</h3>
                            </div>
                            <span class="text-xs font-mono font-bold text-indigo-700">{{ $existingPlans->count() }} Orang</span>
                        </div>

                        <div class="space-y-3">
                            @foreach($existingPlans as $plan)
                                <div class="p-4 rounded-xl bg-indigo-50/50 border border-indigo-200 flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-black flex items-center justify-center text-sm shrink-0 shadow-xs">
                                            #{{ $plan->peringkat_prioritas }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-gray-900 text-sm">{{ $plan->pegawai->nama_lengkap ?? $plan->pegawai->nama }}</span>
                                                <span class="px-2 py-0.2 rounded-full text-[10px] font-bold {{ $plan->kesiapan_badge_class }}">
                                                    {{ $plan->status_kesiapan }}
                                                </span>
                                            </div>
                                            <span class="text-gray-500 font-mono text-[11px]">
                                                NIP. {{ $plan->pegawai->nip }} &bull; Jabatan Saat Ini: {{ $plan->pegawai->jabatan->nama_jabatan ?? '-' }}
                                            </span>
                                            @if($plan->catatan_komite)
                                                <p class="text-[11px] text-indigo-900 mt-1 italic">
                                                    "{{ $plan->catatan_komite }}"
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        <div class="text-right">
                                            <span class="font-mono font-black text-sm text-indigo-900">{{ $plan->match_score }}%</span>
                                            <span class="block text-[10px] text-gray-500">Match Index</span>
                                        </div>
                                        @if(Auth::user()->canManageTalentManagement())
                                            <form action="{{ route('manajemen-talenta.suksesi.nominasi.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Hapus nominasi suksesi pegawai ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition" title="Batalkan Nominasi">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Bagian 2: Hasil Matching & Pemeringkatan Kandidat dari Talent Pool --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="pb-3 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
                                <span>🔍</span> Hasil Job Matching &amp; Ranking Kandidat Talent Pool
                            </h3>
                            <p class="text-xs text-gray-500">
                                Diurutkan berdasarkan Indeks Kecocokan (Fit Index) terhadap Standar Anjab
                            </p>
                        </div>
                        <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                            {{ $candidates->count() }} Kandidat Teridentifikasi
                        </span>
                    </div>

                    <div class="space-y-4">
                        @forelse($candidates as $idx => $cand)
                            <div class="p-5 rounded-2xl border {{ $cand->is_current_plan ? 'border-indigo-300 bg-indigo-50/20' : 'border-gray-200 bg-white hover:border-emerald-300' }} shadow-xs transition" x-data="{ showGap: false }">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-start gap-3.5">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-lg font-black text-slate-700 shrink-0 overflow-hidden shadow-2xs">
                                            @if($cand->pegawai->foto)
                                                <img src="{{ route('pegawai.foto', $cand->pegawai->id) }}" alt="{{ $cand->pegawai->nama }}" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($cand->pegawai->nama, 0, 2)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-black text-gray-900 text-sm">
                                                    {{ $cand->pegawai->nama_lengkap ?? $cand->pegawai->nama }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.2 rounded-full text-[10px] font-bold shadow-2xs {{ $cand->mapping->box_badge_color }}">
                                                    Kotak {{ $cand->kuadran_box }}
                                                </span>
                                                @if($cand->is_current_plan)
                                                    <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                        Sudah Dinominasikan
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-gray-500 font-mono mt-0.5">
                                                NIP. {{ $cand->pegawai->nip }} &bull; Gol. {{ $cand->pegawai->golongan->kode ?? '-' }} &bull; {{ $cand->pegawai->pendidikan_terakhir }}
                                            </p>
                                            <p class="text-[11px] text-slate-700 font-medium mt-0.5">
                                                Posisi Saat Ini: {{ $cand->pegawai->jabatan->nama_jabatan ?? '-' }} ({{ $cand->pegawai->unitKerja->nama_unit ?? '-' }})
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Skor Match & Tombol Aksi --}}
                                    <div class="flex items-center gap-4 shrink-0">
                                        <div class="text-right">
                                            <div class="flex items-center gap-1.5 justify-end">
                                                <span class="font-mono font-black text-lg text-slate-900">{{ $cand->match_score }}%</span>
                                            </div>
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $cand->status_kesiapan === 'Siap Sekarang' ? 'bg-emerald-100 text-emerald-800' : ($cand->status_kesiapan === 'Siap 1-2 Tahun' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                                {{ $cand->status_kesiapan }}
                                            </span>
                                        </div>

                                        <button @click="showGap = !showGap" type="button" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition" title="Lihat Gap Analysis">
                                            <span x-text="showGap ? 'Tutup Rincian' : 'Rincian Gap'"></span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Progress Bar Match Index --}}
                                <div class="mt-3 w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $cand->match_score >= 85 ? 'bg-emerald-500' : ($cand->match_score >= 70 ? 'bg-blue-500' : 'bg-amber-500') }}" style="width: {{ $cand->match_score }}%"></div>
                                </div>

                                {{-- Panel Rincian Gap Analysis & Form Nominasi (Toggle Alpine.js) --}}
                                <div x-show="showGap" x-cloak class="mt-4 pt-4 border-t border-gray-100 space-y-4">
                                    
                                    {{-- Gap Items --}}
                                    <div>
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-2">Analisis Kesenjangan (Gap Analysis) terhadap Standar Jabatan:</span>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                                            @foreach($cand->gap_analysis as $gap)
                                                <div class="p-2.5 rounded-xl border {{ $gap['is_pass'] ? 'bg-emerald-50/50 border-emerald-200' : 'bg-amber-50/50 border-amber-200' }}">
                                                    <div class="flex items-center justify-between font-bold">
                                                        <span class="{{ $gap['is_pass'] ? 'text-emerald-900' : 'text-amber-900' }}">{{ $gap['aspek'] }}</span>
                                                        <span class="text-[10px] px-1.5 py-0.2 rounded font-semibold {{ $gap['is_pass'] ? 'bg-emerald-200 text-emerald-900' : 'bg-amber-200 text-amber-900' }}">{{ $gap['status'] }}</span>
                                                    </div>
                                                    <p class="text-[11px] text-gray-600 mt-1 leading-snug">
                                                        {{ $gap['detail'] }}
                                                    </p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Form Nominasi untuk Komite Talenta --}}
                                    @if(Auth::user()->canManageTalentManagement())
                                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                                            <span class="text-xs font-bold text-slate-800 block mb-2">👑 Tetapkan / Perbarui Nominasi Suksesor:</span>
                                            <form action="{{ route('manajemen-talenta.suksesi.nominasi.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end text-xs">
                                                @csrf
                                                <input type="hidden" name="jabatan_target_id" value="{{ $jabatan->id }}">
                                                <input type="hidden" name="pegawai_id" value="{{ $cand->pegawai->id }}">
                                                <input type="hidden" name="tahun" value="{{ $tahun }}">

                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-600 mb-1">Prioritas</label>
                                                    <select name="peringkat_prioritas" class="w-full text-xs rounded-lg border-gray-300 py-1.5">
                                                        <option value="1">Prioritas 1 (Kandidat Utama)</option>
                                                        <option value="2">Prioritas 2 (Kandidat Cadangan 1)</option>
                                                        <option value="3">Prioritas 3 (Kandidat Cadangan 2)</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-600 mb-1">Status Kesiapan</label>
                                                    <select name="status_kesiapan" class="w-full text-xs rounded-lg border-gray-300 py-1.5">
                                                        <option value="Siap Sekarang" {{ $cand->status_kesiapan === 'Siap Sekarang' ? 'selected' : '' }}>Siap Sekarang (&lt; 6 Bln)</option>
                                                        <option value="Siap 1-2 Tahun" {{ $cand->status_kesiapan === 'Siap 1-2 Tahun' ? 'selected' : '' }}>Siap 1-2 Tahun</option>
                                                        <option value="Potensial Jangka Panjang" {{ $cand->status_kesiapan === 'Potensial Jangka Panjang' ? 'selected' : '' }}>Potensial Jangka Panjang</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-600 mb-1">Status Nominasi</label>
                                                    <select name="status_nominasi" class="w-full text-xs rounded-lg border-gray-300 py-1.5">
                                                        <option value="Kandidat Terpilih">Kandidat Terpilih</option>
                                                        <option value="Dalam Seleksi">Dalam Seleksi</option>
                                                        <option value="Ditetapkan PPK">Ditetapkan PPK</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <button type="submit" class="w-full px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-xs transition">
                                                        💾 Simpan Nominasi
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 italic py-6 text-center">
                                Tidak ada kandidat ASN di Talent Pool yang memenuhi kriteria awal suksesi jabatan ini.
                            </p>
                        @endforelse
                    </div>

                </div>

            </div>

        </div>

    </div>
</x-app-layout>
