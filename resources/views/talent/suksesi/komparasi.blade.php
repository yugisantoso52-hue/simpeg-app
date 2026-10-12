<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manajemen-talenta.suksesi.jabatan', ['jabatan' => $jabatan->id, 'tahun' => $tahun]) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                        &larr; Kembali ke Rencana Suksesi Jabatan
                    </a>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>⚔️</span> Komparasi Kandidat Suksesi (Head-to-Head)
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Perbandingan Multi-Dimensi Antar Kandidat untuk Jabatan: <b class="text-gray-900">{{ $jabatan->nama_jabatan }}</b> (Kelas {{ $jabatan->kelas_jabatan ?? '-' }})
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('manajemen-talenta.suksesi.jabatan', ['jabatan' => $jabatan->id, 'tahun' => $tahun]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                    <span>📋</span> Daftar Pool Kandidat
                </a>
                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition">
                    <span>🖨️</span> Cetak Komparasi
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Selector Filter Kandidat yang Ingin Dibandingkan --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <form method="GET" action="{{ route('manajemen-talenta.suksesi.komparasi', $jabatan) }}" class="space-y-3">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <span class="text-xs font-black uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                        <span>👥</span> Pilih Kandidat yang Ingin Dikomparasikan (Maksimal 3 Orang):
                    </span>
                    <button type="submit" class="px-3.5 py-1.5 text-xs font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-xs transition">
                        Terapkan Perbandingan
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 pt-1">
                    @foreach($allPool as $cand)
                        @php
                            $isSelected = $selectedCandidates->contains(fn($c) => $c->pegawai->id === $cand->pegawai->id);
                        @endphp
                        <label class="flex items-center gap-2.5 p-2 rounded-xl border text-xs cursor-pointer transition {{ $isSelected ? 'bg-indigo-50/80 border-indigo-300 font-bold text-indigo-950' : 'bg-slate-50 border-gray-200 text-gray-700 hover:bg-gray-100' }}">
                            <input type="checkbox" name="candidate_ids[]" value="{{ $cand->pegawai->id }}" {{ $isSelected ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="truncate">{{ $cand->pegawai->nama }} (Kotak {{ $cand->kuadran_box }})</span>
                        </label>
                    @endforeach
                </div>
            </form>
        </div>

        {{-- Matriks Perbandingan Head-to-Head --}}
        @if($selectedCandidates->isEmpty())
            <div class="bg-white rounded-2xl p-12 text-center border border-gray-200 text-gray-500">
                <span class="text-3xl block mb-2">🔍</span>
                <p class="font-bold text-gray-700">Belum ada kandidat suksesi yang terpilih untuk dikomparasi.</p>
                <p class="text-xs text-gray-500 mt-1">Silakan centang kandidat dari pool talenta di atas.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-{{ count($selectedCandidates) }} gap-6">
                @foreach($selectedCandidates as $idx => $candidate)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                        
                        {{-- Header Profil Kandidat --}}
                        <div class="p-5 bg-gradient-to-br from-slate-50 to-indigo-50/40 border-b border-gray-200 text-center relative">
                            <span class="absolute top-3 left-3 text-[10px] font-mono font-black text-indigo-700 bg-white px-2 py-0.5 rounded-full border border-indigo-200">
                                Kandidat #{{ $idx + 1 }}
                            </span>

                            <div class="w-16 h-16 mx-auto rounded-full bg-indigo-600 text-white flex items-center justify-center font-black text-xl shadow-md border-2 border-white mb-2">
                                {{ strtoupper(substr($candidate->pegawai->nama, 0, 2)) }}
                            </div>
                            <h3 class="font-black text-gray-900 text-sm leading-snug">{{ $candidate->pegawai->nama }}</h3>
                            <p class="text-[11px] font-mono text-gray-500 mt-0.5">NIP. {{ $candidate->pegawai->nip }}</p>
                            <div class="text-[11px] text-gray-600 mt-1">
                                Gol. <b>{{ $candidate->pegawai->golongan->kode ?? '-' }}</b> &bull; {{ $candidate->pegawai->jabatan->nama_jabatan ?? '-' }}
                            </div>
                        </div>

                        <div class="p-5 space-y-5 text-xs divide-y divide-gray-100 flex-1">
                            
                            {{-- 1. Match Score & Status Kesiapan --}}
                            <div class="pt-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-black uppercase text-gray-500">Job Match Index</span>
                                    <span class="text-lg font-black text-indigo-600">{{ number_format($candidate->match_score, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all" style="width: {{ min(100, $candidate->match_score) }}%"></div>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-[11px] text-gray-500">Status Kesiapan:</span>
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $candidate->status_kesiapan === 'Siap Sekarang' ? 'bg-emerald-100 text-emerald-800' : ($candidate->status_kesiapan === 'Siap 1-2 Tahun' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $candidate->status_kesiapan }}
                                    </span>
                                </div>
                            </div>

                            {{-- 2. Posisi 9-Box Grid --}}
                            <div class="pt-4">
                                <span class="text-[10px] font-black uppercase text-gray-500 block mb-2">Matriks 9-Kotak Talenta</span>
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                                    <div>
                                        <span class="font-black text-gray-900 block text-xs">{{ $candidate->mapping->box_name }}</span>
                                        <span class="text-[10px] text-gray-500">Kinerja: <b>{{ $candidate->mapping->sumbu_kinerja_nilai }}</b> &bull; Potensi: <b>{{ $candidate->mapping->sumbu_potensi_nilai }}</b></span>
                                    </div>
                                    <span class="text-sm font-black px-2.5 py-1 rounded-lg {{ $candidate->kuadran_box >= 8 ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white' }}">
                                        Kotak {{ $candidate->kuadran_box }}
                                    </span>
                                </div>
                            </div>

                            {{-- 3. Asesmen Center BKN --}}
                            <div class="pt-4">
                                <span class="text-[10px] font-black uppercase text-gray-500 block mb-2">Asesmen Kompetensi (BKN)</span>
                                @if($candidate->latest_assessment)
                                    <div class="space-y-1.5 text-[11px]">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Manajerial:</span>
                                            <span class="font-bold text-gray-800">{{ $candidate->latest_assessment->skor_manajerial ?? '-' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Sosio Kultural:</span>
                                            <span class="font-bold text-gray-800">{{ $candidate->latest_assessment->skor_sosio_kultural ?? '-' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Teknis:</span>
                                            <span class="font-bold text-gray-800">{{ $candidate->latest_assessment->skor_teknis ?? '-' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Potensi:</span>
                                            <span class="font-bold text-gray-800">{{ $candidate->latest_assessment->skor_potensi ?? '-' }}</span>
                                        </div>
                                        <div class="flex justify-between pt-1 border-t border-gray-100 font-bold">
                                            <span>Skor Total Komposit:</span>
                                            <span class="text-indigo-600">{{ $candidate->latest_assessment->skor_total }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[11px] text-gray-400 italic">Belum ada rekam jejak asesmen BKN</span>
                                @endif
                            </div>

                            {{-- 4. Kinerja SKP 2 Tahun Terakhir --}}
                            <div class="pt-4">
                                <span class="text-[10px] font-black uppercase text-gray-500 block mb-2">Kinerja SKP Tahunan</span>
                                <div class="space-y-1 text-[11px]">
                                    @forelse($candidate->pegawai->riwayatSkp as $skp)
                                        <div class="flex justify-between p-1.5 bg-slate-50 rounded">
                                            <span>SKP {{ $skp->tahun }}:</span>
                                            <span class="font-bold text-emerald-700">{{ $skp->nilai ?? $skp->predikat_kinerja ?? 'Baik' }}</span>
                                        </div>
                                    @empty
                                        <span class="text-[11px] text-gray-400 italic">Belum ada data riwayat SKP</span>
                                    @endforelse
                                </div>
                            </div>

                            {{-- 5. Jam Pelatihan Diklat (20 JP) --}}
                            <div class="pt-4">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[10px] font-black uppercase text-gray-500">Hak 20 JP Pelatihan</span>
                                    <span class="font-bold {{ $candidate->total_jp >= 20 ? 'text-emerald-700' : 'text-amber-700' }}">{{ $candidate->total_jp }} / 20 JP</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $candidate->total_jp >= 20 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min(100, ($candidate->total_jp / 20) * 100) }}%"></div>
                                </div>
                            </div>

                            {{-- 6. Gap Analysis --}}
                            <div class="pt-4">
                                <span class="text-[10px] font-black uppercase text-gray-500 block mb-2">Analisis Kesenjangan (Gap)</span>
                                <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                                    @foreach($candidate->gap_analysis as $gap)
                                        <div class="p-1.5 rounded text-[10px] {{ $gap['is_pass'] ? 'bg-emerald-50 text-emerald-900 border border-emerald-100' : 'bg-rose-50 text-rose-900 border border-rose-100' }}">
                                            <span class="font-bold block">{{ $gap['is_pass'] ? '✅' : '⚠️' }} {{ $gap['aspek'] }}</span>
                                            <span class="text-[9px] text-gray-600 block mt-0.5">{{ $gap['detail'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                        {{-- Footer Aksi Nominasi Suksesi --}}
                        <div class="p-4 bg-gray-50 border-t border-gray-200">
                            @if(Auth::user()->canManageTalentManagement())
                                <form method="POST" action="{{ route('manajemen-talenta.suksesi.nominasi.store') }}">
                                    @csrf
                                    <input type="hidden" name="jabatan_target_id" value="{{ $jabatan->id }}">
                                    <input type="hidden" name="pegawai_id" value="{{ $candidate->pegawai->id }}">
                                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                                    <input type="hidden" name="peringkat_prioritas" value="{{ $idx + 1 }}">
                                    <input type="hidden" name="status_kesiapan" value="{{ $candidate->status_kesiapan }}">
                                    <input type="hidden" name="status_nominasi" value="Kandidat Terpilih">
                                    <input type="hidden" name="catatan_komite" value="Ditetapkan melalui simulasi perbandingan komparatif talenta.">
                                    
                                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 shadow-sm transition flex items-center justify-center gap-1.5">
                                        <span>👑</span> Tetapkan ke Rencana Suksesi
                                    </button>
                                </form>
                            @else
                                <span class="text-center block text-[11px] text-gray-400">Hanya Tim Komite yang dapat menetapkan</span>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-app-layout>
