<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🏖️</span> Detail Permohonan Cuti
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Informasi lengkap permohonan cuti dan riwayat persetujuan pimpinan
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('pengajuan-cuti.cetak-pdf', $cuti->id) }}" target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    📄 Cetak Formulir BKN (PDF)
                </a>
                <a href="{{ route('pengajuan-cuti.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                    ← Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-lg border border-green-300 bg-green-50 px-5 py-4 text-green-700 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="text-green-700 font-bold hover:text-green-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- KARTU STATUS PERMOHONAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Permohonan Cuti</span>
                        <div class="mt-1 flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-sm font-bold border {{ $cuti->status_badge_class }}">
                                {{ $cuti->status }}
                            </span>
                            @if($cuti->nomor_surat)
                                <span class="text-xs text-gray-600 font-mono bg-gray-100 px-2 py-1 rounded">
                                    No. Surat: {{ $cuti->nomor_surat }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Tombol Batal untuk Pegawai jika status masih Menunggu Persetujuan --}}
                    @if($cuti->status === 'Menunggu Persetujuan' && Auth::user()->pegawai_id === $cuti->pegawai_id)
                        <form action="{{ route('pengajuan-cuti.cancel', $cuti->id) }}" method="POST"
                              onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan cuti ini?');">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-semibold transition">
                                ❌ Batalkan Permohonan Cuti
                            </button>
                        </form>
                    @endif
                </div>

                {{-- RINCIAN DATA PERMOHONAN --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-5 text-sm">
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs text-gray-500 font-semibold uppercase">Nama Pegawai / Pemohon</div>
                            <div class="font-bold text-gray-900 text-base mt-0.5">
                                <a href="{{ route('pegawai.show', $cuti->pegawai_id) }}" class="hover:text-emerald-600">
                                    {{ $cuti->pegawai->nama_lengkap ?? $cuti->pegawai->nama }}
                                </a>
                            </div>
                            <div class="text-xs text-gray-500 font-mono">NIP. {{ $cuti->pegawai->nip ?? '-' }}</div>
                            <div class="text-xs text-gray-600 mt-0.5">
                                {{ $cuti->pegawai->jabatan->nama_jabatan ?? '-' }} - {{ $cuti->pegawai->unitKerja->nama_unit ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 font-semibold uppercase">Jenis Cuti yang Diajukan</div>
                            <div class="font-bold text-gray-900 mt-0.5">{{ $cuti->jenis_cuti }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 font-semibold uppercase">Durasi & Waktu Pelaksanaan</div>
                            <div class="font-semibold text-gray-900 mt-0.5">
                                {{ $cuti->jumlah_hari }} Hari Kerja
                            </div>
                            <div class="text-xs text-gray-600 mt-0.5">
                                {{ $cuti->tanggal_mulai ? $cuti->tanggal_mulai->translatedFormat('d F Y') : '-' }} s.d.
                                {{ $cuti->tanggal_selesai ? $cuti->tanggal_selesai->translatedFormat('d F Y') : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="text-xs text-gray-500 font-semibold uppercase">Alasan Permohonan Cuti</div>
                            <div class="text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-200 mt-0.5 whitespace-pre-line">
                                {{ $cuti->alasan }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 font-semibold uppercase">Alamat Selama Cuti & Kontak</div>
                            <div class="text-gray-800 mt-0.5">{{ $cuti->alamat_selama_cuti ?: '-' }}</div>
                            <div class="text-xs text-gray-600 font-mono mt-0.5">Telp/WA: {{ $cuti->nomor_telepon ?: '-' }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 font-semibold uppercase">Berkas Lampiran Pendukung</div>
                            @if($cuti->file_lampiran_url)
                                <a href="{{ $cuti->file_lampiran_url }}" target="_blank"
                                   class="inline-flex items-center text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 font-semibold px-3 py-1.5 rounded-lg mt-1 gap-1.5 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Buka Berkas Lampiran
                                </a>
                            @else
                                <span class="text-xs text-gray-400 italic">Tidak ada lampiran.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== ALUR PERSETUJUAN 2-TAHAP (PerBKN No. 7 Tahun 2022) ===== --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span>📋</span> Alur Persetujuan 2 Tahap (PerBKN No. 7/2022)
                </h3>

                <div class="flex flex-col md:flex-row gap-4">
                    {{-- TAHAP 1: Pertimbangan Atasan Langsung --}}
                    @php
                        $tahap1Color = $cuti->pertimbangan_atasan === 'Disetujui'
                            ? 'border-emerald-300 bg-emerald-50'
                            : ($cuti->pertimbangan_atasan === 'Ditolak' ? 'border-rose-300 bg-rose-50' : 'border-amber-200 bg-amber-50');
                        $tahap1Badge = $cuti->pertimbangan_atasan === 'Disetujui'
                            ? 'bg-emerald-600 text-white'
                            : ($cuti->pertimbangan_atasan === 'Ditolak' ? 'bg-rose-600 text-white' : 'bg-amber-400 text-white');
                    @endphp
                    <div class="flex-1 rounded-lg border {{ $tahap1Color }} p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold {{ $tahap1Badge }}">1</div>
                            <span class="text-xs font-bold uppercase tracking-wide text-gray-600">Pertimbangan Atasan Langsung</span>
                        </div>
                        <div class="text-sm">
                            @if($cuti->atasanLangsung)
                                <div class="font-semibold text-gray-800">{{ $cuti->atasanLangsung->name ?? '-' }}</div>
                            @else
                                <div class="text-gray-400 italic text-xs">Atasan belum terdeteksi di sistem</div>
                            @endif
                            @if($cuti->pertimbangan_atasan)
                                <div class="mt-1">
                                    <span class="px-2 py-0.5 rounded text-xs font-bold {{ $cuti->pertimbangan_atasan === 'Disetujui' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $cuti->pertimbangan_atasan }}
                                    </span>
                                    @if($cuti->pertimbangan_atasan_at)
                                        <div class="text-xs text-gray-500 mt-1">{{ $cuti->pertimbangan_atasan_at->translatedFormat('d F Y H:i') }}</div>
                                    @endif
                                    @if($cuti->catatan_atasan_langsung)
                                        <div class="text-xs text-gray-700 mt-1 italic">"{{ $cuti->catatan_atasan_langsung }}"</div>
                                    @endif
                                </div>
                            @else
                                <div class="mt-1 text-xs text-amber-700 font-semibold">⏳ Menunggu Pertimbangan Atasan</div>
                            @endif
                        </div>
                    </div>

                    {{-- Panah --}}
                    <div class="hidden md:flex items-center text-gray-400 text-2xl self-center">→</div>

                    {{-- TAHAP 2: Keputusan PYBMC --}}
                    @php
                        $tahap2Color = $cuti->status === 'Disetujui'
                            ? 'border-emerald-300 bg-emerald-50'
                            : ($cuti->status === 'Ditolak' ? 'border-rose-300 bg-rose-50' : 'border-blue-200 bg-blue-50');
                        $tahap2Badge = $cuti->status === 'Disetujui'
                            ? 'bg-emerald-600 text-white'
                            : ($cuti->status === 'Ditolak' ? 'bg-rose-600 text-white' : 'bg-blue-500 text-white');
                    @endphp
                    <div class="flex-1 rounded-lg border {{ $tahap2Color }} p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold {{ $tahap2Badge }}">2</div>
                            <span class="text-xs font-bold uppercase tracking-wide text-gray-600">Keputusan PYBMC</span>
                        </div>
                        <div class="text-sm">
                            @if($cuti->pybmc)
                                <div class="font-semibold text-gray-800">{{ $cuti->pybmc->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">Pejabat Berwenang Memberikan Cuti</div>
                            @else
                                <div class="text-gray-400 italic text-xs">Pejabat berwenang belum terdeteksi</div>
                            @endif
                            @if($cuti->approved_by && in_array($cuti->status, ['Disetujui', 'Ditolak']))
                                <div class="mt-1">
                                    <span class="px-2 py-0.5 rounded text-xs font-bold {{ $cuti->status === 'Disetujui' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $cuti->status }}
                                    </span>
                                    @if($cuti->approved_at)
                                        <div class="text-xs text-gray-500 mt-1">{{ $cuti->approved_at->translatedFormat('d F Y H:i') }}</div>
                                    @endif
                                    @if($cuti->catatan_pimpinan)
                                        <div class="text-xs text-gray-700 mt-1 italic">"{{ $cuti->catatan_pimpinan }}"</div>
                                    @endif
                                </div>
                            @else
                                <div class="mt-1 text-xs text-blue-700 font-semibold">⏳ Menunggu Keputusan PYBMC</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== FORM PERSETUJUAN (ADMIN/PIMPINAN/ATASAN) ===== --}}
            @php
                $authUser    = Auth::user();
                $isAdmin     = $authUser->hasRole('admin');
                $isPybmc     = $cuti->pybmc_id && (int)$cuti->pybmc_id === (int)$authUser->id;
                $isAtasanLgs = $cuti->atasan_langsung_id && (int)$cuti->atasan_langsung_id === (int)$authUser->id;

                // Tahap 1: Atasan langsung bisa pertimbangan (atau admin jika belum ada pertimbangan)
                $canGivePertimbangan = !in_array($cuti->status, ['Disetujui', 'Ditolak', 'Dibatalkan'])
                    && empty($cuti->pertimbangan_atasan)
                    && ($isAtasanLgs || $isAdmin);

                // Tahap 2: PYBMC atau admin bisa berikan keputusan final
                $canGiveKeputusan = !in_array($cuti->status, ['Disetujui', 'Ditolak', 'Dibatalkan'])
                    && ($isPybmc || $isAdmin || $authUser->hasRole('pimpinan'));

                // Admin bisa lakukan keduanya
                if ($isAdmin) {
                    $canGivePertimbangan = !in_array($cuti->status, ['Disetujui', 'Ditolak', 'Dibatalkan']) && empty($cuti->pertimbangan_atasan);
                }
            @endphp

            {{-- FORM TAHAP 1: Pertimbangan Atasan Langsung --}}
            @if($canGivePertimbangan && !$canGiveKeputusan)
                <div class="bg-white rounded-xl shadow-sm border border-amber-300 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span>✍️</span> Pertimbangan Atasan Langsung (Tahap 1)
                    </h3>
                    <p class="text-xs text-amber-700 mb-4 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                        Berikan pertimbangan Anda sebagai Atasan Langsung. Setelah ini, permohonan akan diteruskan ke Pejabat Yang Berwenang Memberikan Cuti (PYBMC) untuk keputusan akhir.
                    </p>
                    <form action="{{ route('pengajuan-cuti.approve', $cuti->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                Pertimbangan <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-amber-500 focus:border-amber-500">
                                <option value="Disetujui">✅ Setuju (Diteruskan ke PYBMC)</option>
                                <option value="Ditolak">❌ Ditolak</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Catatan Pertimbangan</label>
                            <textarea name="catatan_pimpinan" rows="2"
                                      placeholder="Tambahkan catatan jika diperlukan..."
                                      class="w-full rounded-lg border-gray-300 text-sm focus:ring-amber-500 focus:border-amber-500"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm shadow transition">
                                💾 Kirim Pertimbangan Saya
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- FORM TAHAP 2: Keputusan PYBMC --}}
            @if($canGiveKeputusan)
                <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span>🏛️</span>
                        @if($isAdmin)
                            Keputusan Pejabat Berwenang / Admin (PYBMC) — Tahap 2
                        @else
                            Keputusan Pejabat Yang Berwenang Memberikan Cuti (PYBMC) — Tahap 2
                        @endif
                    </h3>
                    <p class="text-xs text-blue-700 mb-4 bg-blue-50 border border-blue-200 rounded px-3 py-2">
                        Keputusan final permohonan cuti sesuai PerBKN No. 7 Tahun 2022. Keputusan ini bersifat final dan akan langsung diterima oleh pegawai pemohon.
                    </p>
                    <form action="{{ route('pengajuan-cuti.approve', $cuti->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="status" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                    Keputusan Akhir <span class="text-red-500">*</span>
                                </label>
                                <select name="status" id="status" required
                                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="Disetujui" @selected(old('status') == 'Disetujui')>✅ Disetujui — Cuti Diberikan</option>
                                    <option value="Ditolak" @selected(old('status') == 'Ditolak')>❌ Ditolak — Tidak Disetujui</option>
                                </select>
                            </div>
                            <div>
                                <label for="nomor_surat" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                    Nomor Surat Izin Cuti (Opsional)
                                </label>
                                <input type="text" name="nomor_surat" id="nomor_surat"
                                       value="{{ old('nomor_surat', $cuti->nomor_surat) }}"
                                       placeholder="Contoh: 123/UN19.5.1/KP/2026"
                                       class="w-full rounded-lg border-gray-300 font-mono text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>
                        <div>
                            <label for="catatan_pimpinan" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                Catatan / Pertimbangan PYBMC
                            </label>
                            <textarea name="catatan_pimpinan" id="catatan_pimpinan" rows="2"
                                      placeholder="Tambahkan catatan keputusan jika diperlukan..."
                                      class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">{{ old('catatan_pimpinan', $cuti->catatan_pimpinan) }}</textarea>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                    class="px-6 py-2.5 bg-slate-900 hover:bg-black text-white font-semibold rounded-lg text-sm shadow transition">
                                💾 Simpan Keputusan Final
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
