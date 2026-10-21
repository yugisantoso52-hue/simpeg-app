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
            <div class="flex items-center gap-2" x-data="{ openModalCetak: false, nomorInput: '{{ $cuti->nomor_surat ? explode('/', $cuti->nomor_surat)[0] : '' }}' }">
                <button type="button" @click="openModalCetak = true"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    <span>📄</span> Cetak Formulir Cuti (PDF)
                </button>
                <a href="{{ route('pengajuan-cuti.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                    ← Kembali
                </a>

                {{-- MODAL CETAK FORMULIR CUTI DENGAN INPUT NOMOR SURAT MANUAL --}}
                <div x-show="openModalCetak" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                     @keydown.escape.window="openModalCetak = false">
                    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 text-left border border-slate-200"
                         @click.away="openModalCetak = false">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                <span>📄</span> Cetak Formulir Permintaan & Pemberian Cuti
                            </h3>
                            <button type="button" @click="openModalCetak = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                        </div>
                        
                        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                            Pastikan nomor surat dinas sesuai format resmi institusi Fakultas Keperawatan Universitas Riau sebelum mencetak dokumen.
                        </p>

                        @php
                            $userIsAdmin = Auth::user()->hasRole('admin') || Auth::user()->canAccessExecutiveKepegawaianMenus();
                        @endphp
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    Nomor Surat @if($userIsAdmin) (Diisi Manual oleh Admin) @else (Ditetapkan oleh Admin) @endif
                                </label>
                                @if($userIsAdmin)
                                    <div class="flex items-center">
                                        <input type="text" x-model="nomorInput"
                                               placeholder="Isi nomor manual..."
                                               class="w-full rounded-l-lg border-slate-300 font-mono text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                        <span class="inline-flex items-center px-3 py-2 rounded-r-lg border border-l-0 border-slate-300 bg-slate-100 text-slate-700 text-xs font-mono font-bold whitespace-nowrap">
                                            /UN19.5.1.1.10/KP/{{ date('Y') }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-slate-500 mt-1 block">
                                        Format Surat: <span class="font-mono text-emerald-700 font-bold" x-text="(nomorInput ? nomorInput : '......') + '/UN19.5.1.1.10/KP/{{ date('Y') }}'"></span>
                                    </span>
                                @else
                                    <div class="flex items-center">
                                        <input type="text" :value="nomorInput ? nomorInput : '[Belum Diterbitkan Admin]'" disabled
                                               class="w-full rounded-l-lg border-slate-300 bg-slate-100 text-slate-500 font-mono text-sm cursor-not-allowed">
                                        <span class="inline-flex items-center px-3 py-2 rounded-r-lg border border-l-0 border-slate-300 bg-slate-100 text-slate-400 text-xs font-mono font-bold whitespace-nowrap">
                                            /UN19.5.1.1.10/KP/{{ date('Y') }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-amber-600 mt-1 block font-medium">
                                        ℹ️ Sesuai Tata Naskah Dinas, pengisian nomor surat dinas resmi dilakukan oleh Admin Kepegawaian.
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                                <button type="button" @click="openModalCetak = false"
                                        class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                    Batal
                                </button>
                                <a :href="'{{ route('pengajuan-cuti.cetak-pdf', $cuti->id) }}' + (nomorInput ? '?nomor_surat_input=' + encodeURIComponent(nomorInput) : '')"
                                   target="_blank"
                                   @click="openModalCetak = false"
                                   class="px-4 py-2 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow transition flex items-center gap-1.5">
                                    <span>🖨️</span> Buka & Cetak PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
                                {{ $cuti->status_label ?? $cuti->status }}
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

                {{-- STANDARISASI ALUR VERIFIKASI BERJENJANG (STEPPER PARAF DIGITAL) --}}
                <div class="mt-4 rounded-xl bg-slate-50 p-4 border border-slate-200">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                        Status Alur Verifikasi & Penandatanganan Resmi:
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        {{-- 1. Admin Kepegawaian --}}
                        <div class="p-3 rounded-lg border {{ $cuti->verifikasi_admin_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">1. Admin Kepegawaian</span>
                                <span class="text-xs font-bold">{{ $cuti->verifikasi_admin_at ? '✓ Selesai' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Screening Lampiran & Kuota</div>
                            @if($cuti->verifikasi_admin_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $cuti->verifikasi_admin_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- 2. Ka Pokja Keu-Kepeg --}}
                        <div class="p-3 rounded-lg border {{ $cuti->paraf_kapokja_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">2. Ka Pokja Keu-Kepeg</span>
                                <span class="text-xs font-bold">{{ $cuti->paraf_kapokja_at ? '✓ Paraf' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Dolli Vita Zenitha, SE</div>
                            @if($cuti->paraf_kapokja_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $cuti->paraf_kapokja_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- 3. Kabag Umum --}}
                        <div class="p-3 rounded-lg border {{ $cuti->paraf_kabag_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">3. Kabag Umum</span>
                                <span class="text-xs font-bold">{{ $cuti->paraf_kabag_at ? '✓ Paraf' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">Bakhtiar, S.Sos., M.Si</div>
                            @if($cuti->paraf_kabag_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $cuti->paraf_kabag_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- 4. Wadek II / PYBMC (Pejabat Penandatangan Resmi) --}}
                        <div class="p-3 rounded-lg border {{ in_array($cuti->status, ['Disetujui', 'Ditolak']) ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold">4. TTE PYBMC</span>
                                <span class="text-xs font-bold">{{ in_array($cuti->status, ['Disetujui', 'Ditolak']) ? '✓ Sah (TTE)' : 'Menunggu' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-600">{{ $pejabatInfo['pybmc_nama'] ?? 'Wakil Dekan II' }}</div>
                            @if($cuti->approved_at)
                                <div class="text-[10px] text-emerald-700 font-mono mt-1">
                                    {{ $cuti->approved_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if(Auth::user()->hasRole('admin'))
                    {{-- PANEL KHUSUS ADMIN: INPUT / UPDATE NOMOR SURAT RESMI INSTANSI --}}
                    <div class="mt-4 p-4 rounded-xl border border-emerald-200 bg-emerald-50/40">
                        <div class="flex items-center justify-between pb-2 border-b border-emerald-200/60 mb-2.5">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-950 flex items-center gap-1.5">
                                <span>🏛️</span> Penomoran Surat Izin Cuti (Khusus Admin Kepegawaian)
                            </h4>
                            @if($cuti->nomor_surat)
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    {{ $cuti->nomor_surat }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    Belum Bernomor Resmi
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-emerald-900/80 mb-3 leading-relaxed">
                            Berdasarkan SOP Tata Naskah Dinas, penetapan dan pencatatan nomor agenda surat izin cuti dilakukan oleh Admin Kepegawaian.
                        </p>
                        <form action="{{ route('pengajuan-cuti.update-nomor', $cuti->id) }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            @csrf
                            <div class="flex flex-1 rounded-lg shadow-xs">
                                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-emerald-300 bg-emerald-100/70 text-emerald-800 font-mono text-xs font-semibold">
                                    Nomor:
                                </span>
                                <input type="text" name="nomor_surat_input" 
                                       value="{{ $cuti->nomor_surat ? explode('/UN19', $cuti->nomor_surat)[0] : '' }}"
                                       placeholder="Ketik nomor urut (contoh: 045)..."
                                       class="flex-1 min-w-0 block w-full px-3 py-1.5 border border-emerald-300 text-sm font-mono font-bold text-gray-900 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-emerald-300 bg-emerald-100/70 text-emerald-800 font-mono text-xs font-semibold">
                                    /UN19.5.1.1.10/KP/{{ $cuti->created_at ? $cuti->created_at->format('Y') : date('Y') }}
                                </span>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center justify-center gap-1.5 whitespace-nowrap">
                                <span>💾</span> Simpan Nomor Surat
                            </button>
                        </form>
                    </div>
                @elseif($cuti->nomor_surat)
                    <div class="mt-4 p-3 rounded-xl border border-emerald-200 bg-emerald-50/30 flex items-center justify-between">
                        <span class="text-xs font-semibold text-emerald-900 flex items-center gap-1.5">
                            <span>🏛️</span> Nomor Surat Izin Cuti Resmi:
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            {{ $cuti->nomor_surat }}
                        </span>
                    </div>
                @endif

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
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-1 flex items-center gap-2">
                    <span>📋</span> Alur Persetujuan 2 Tahap (PerBKN No. 7/2022)
                </h3>

                {{-- Kategori Pegawai Badge --}}
                @if(isset($pejabatInfo))
                    @php
                        $kategori = $pejabatInfo['kategori'] ?? ($pejabatInfo['is_dosen'] ? 'dosen' : 'tendik');
                        $kategoriBadge = match($kategori) {
                            'dosen'               => ['icon' => '👨‍🏫', 'label' => 'Kategori Dosen', 'color' => 'bg-blue-100 text-blue-700 border-blue-200'],
                            'tendik'              => ['icon' => '🏢', 'label' => 'Kategori Tendik / Staff / Laboran', 'color' => 'bg-purple-100 text-purple-700 border-purple-200'],
                            'pimpinan_struktural' => ['icon' => '🏛️', 'label' => 'Pimpinan Struktural (Wadek / Kajur / Kabag)', 'color' => 'bg-orange-100 text-orange-700 border-orange-200'],
                            'wadek2'              => ['icon' => '⚖️', 'label' => 'Wakil Dekan II', 'color' => 'bg-teal-100 text-teal-700 border-teal-200'],
                            'dekan'               => ['icon' => '🎓', 'label' => 'Dekan (via Rektor)', 'color' => 'bg-rose-100 text-rose-700 border-rose-200'],
                            default               => ['icon' => '👤', 'label' => 'Pegawai', 'color' => 'bg-gray-100 text-gray-700 border-gray-200'],
                        };
                    @endphp
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $kategoriBadge['color'] }}">
                            {{ $kategoriBadge['icon'] }} {{ $kategoriBadge['label'] }}
                        </span>
                        <span class="text-xs text-gray-400">Pejabat penandatangan disesuaikan dengan kategori pegawai.</span>
                    </div>
                @endif

                <div class="flex flex-col md:flex-row gap-4">
                    {{-- TAHAP 1: VI. Pertimbangan Atasan Langsung --}}
                    @php
                        $tahap1Color = $cuti->pertimbangan_atasan === 'Disetujui'
                            ? 'border-emerald-300 bg-emerald-50'
                            : ($cuti->pertimbangan_atasan === 'Ditolak' ? 'border-rose-300 bg-rose-50' : 'border-amber-200 bg-amber-50');
                        $tahap1Badge = $cuti->pertimbangan_atasan === 'Disetujui'
                            ? 'bg-emerald-600 text-white'
                            : ($cuti->pertimbangan_atasan === 'Ditolak' ? 'bg-rose-600 text-white' : 'bg-amber-400 text-white');
                    @endphp
                    <div class="flex-1 rounded-lg border {{ $tahap1Color }} p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold {{ $tahap1Badge }}">1</div>
                            <span class="text-xs font-bold uppercase tracking-wide text-gray-600">VI. Pertimbangan Atasan Langsung</span>
                        </div>
                        <div class="text-sm">
                            {{-- Jabatan yang seharusnya (dari hierarchy service) --}}
                            @if(isset($pejabatInfo))
                                <div class="font-bold text-gray-900">{{ $pejabatInfo['atasan_jabatan'] }}</div>
                                @if($pejabatInfo['atasan_nama'])
                                    <div class="font-semibold text-gray-700 mt-0.5">{{ $pejabatInfo['atasan_nama'] }}</div>
                                @endif
                                @if($pejabatInfo['atasan_nip'])
                                    <div class="text-xs text-gray-400 font-mono">NIP. {{ $pejabatInfo['atasan_nip'] }}</div>
                                @endif
                            @elseif($cuti->atasanLangsung)
                                <div class="font-semibold text-gray-800">{{ $cuti->atasanLangsung->name ?? '-' }}</div>
                            @else
                                <div class="text-gray-400 italic text-xs">Atasan belum terdeteksi di sistem</div>
                            @endif

                            @if($cuti->pertimbangan_atasan)
                                <div class="mt-2 pt-2 border-t border-gray-200">
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
                                <div class="mt-2 pt-2 border-t border-amber-200 text-xs text-amber-700 font-semibold">⏳ Menunggu Pertimbangan</div>
                            @endif
                        </div>
                    </div>

                    {{-- Panah --}}
                    <div class="hidden md:flex items-center text-gray-400 text-2xl self-center">→</div>

                    {{-- TAHAP 2: VII. Keputusan Pejabat Yang Berwenang (PYBMC) --}}
                    @php
                        $tahap2Color = $cuti->status === 'Disetujui'
                            ? 'border-emerald-300 bg-emerald-50'
                            : ($cuti->status === 'Ditolak' ? 'border-rose-300 bg-rose-50' : 'border-blue-200 bg-blue-50');
                        $tahap2Badge = $cuti->status === 'Disetujui'
                            ? 'bg-emerald-600 text-white'
                            : ($cuti->status === 'Ditolak' ? 'bg-rose-600 text-white' : 'bg-blue-500 text-white');
                    @endphp
                    <div class="flex-1 rounded-lg border {{ $tahap2Color }} p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold {{ $tahap2Badge }}">2</div>
                            <span class="text-xs font-bold uppercase tracking-wide text-gray-600">VII. Keputusan Pejabat Yang Berwenang</span>
                        </div>
                        <div class="text-sm">
                            {{-- PYBMC selalu Wadek II (Bid. Keuangan dan Umum) --}}
                            @if(isset($pejabatInfo))
                                <div class="font-bold text-gray-900">{{ $pejabatInfo['pybmc_jabatan'] }}</div>
                                @if($pejabatInfo['pybmc_nama'])
                                    <div class="font-semibold text-gray-700 mt-0.5">{{ $pejabatInfo['pybmc_nama'] }}</div>
                                @endif
                                @if($pejabatInfo['pybmc_nip'])
                                    <div class="text-xs text-gray-400 font-mono">NIP. {{ $pejabatInfo['pybmc_nip'] }}</div>
                                @endif
                            @elseif($cuti->pybmc)
                                <div class="font-semibold text-gray-800">{{ $cuti->pybmc->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">Pejabat Berwenang Memberikan Cuti</div>
                            @else
                                <div class="text-gray-400 italic text-xs">Pejabat berwenang belum terdeteksi</div>
                            @endif

                            @if($cuti->approved_by && in_array($cuti->status, ['Disetujui', 'Ditolak']))
                                <div class="mt-2 pt-2 border-t border-gray-200">
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
                                <div class="mt-2 pt-2 border-t border-blue-200 text-xs text-blue-700 font-semibold">⏳ Menunggu Keputusan Pejabat Berwenang</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== FORM PERSETUJUAN (ADMIN/PIMPINAN/ATASAN) ===== --}}
            @php
                $authUser         = Auth::user();
                $isAdmin          = $authUser->hasRole('admin');
                $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);

                // Cek Atasan Langsung yang sah:
                $isAtasanLgs = $isAdmin || (
                    $authUser->pegawai && $cuti->pegawai &&
                    $hierarchyService->isAtasanLangsungCuti($authUser->pegawai, $cuti->pegawai)
                ) || ($cuti->atasan_langsung_id && (int)$cuti->atasan_langsung_id === (int)$authUser->id);

                // Cek PYBMC yang sah:
                $isPybmc = $isAdmin || (
                    $authUser->pegawai && $cuti->pegawai &&
                    $hierarchyService->isPybmcCuti($authUser->pegawai, $cuti->pegawai)
                ) || ($cuti->pybmc_id && (int)$cuti->pybmc_id === (int)$authUser->id);

                $pertimbanganSudahAda = !empty($cuti->pertimbangan_atasan);

                // TAHAP 1: Atasan Langsung memberikan pertimbangan
                $canGivePertimbangan = !in_array($cuti->status, ['Disetujui', 'Ditolak', 'Dibatalkan'])
                    && !$pertimbanganSudahAda
                    && $isAtasanLgs;

                // TAHAP 2: PYBMC memberikan keputusan final
                $canGiveKeputusan = !in_array($cuti->status, ['Disetujui', 'Ditolak', 'Dibatalkan'])
                    && $isPybmc
                    && ($pertimbanganSudahAda || $isAdmin);

                // Kondisi di mana PYBMC membuka cuti yang masih menunggu Atasan Langsung
                $isPybmcWaitingAtasan = !in_array($cuti->status, ['Disetujui', 'Ditolak', 'Dibatalkan'])
                    && $isPybmc
                    && !$pertimbanganSudahAda
                    && !$isAdmin;
            @endphp

            {{-- PANEL STANDARISASI: VERIFIKASI ADMIN & PARAF KOORDINASI (POKJA & KABAG) --}}
            @if($isAdmin || ($isKaPokja ?? false) || ($isKabag ?? false) || ($isExecutive ?? false))
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-slate-100 gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <span>🛡️</span> Tindakan Verifikasi & Paraf Koordinasi Tata Naskah
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Standar pemeriksaan syarat berkas fisik/lampiran oleh Admin, paraf teknis Ka Pokja, dan paraf tata naskah Kabag Umum.
                            </p>
                        </div>
                    </div>

                    @if(!in_array($cuti->status, ['Disetujui', 'Ditolak', 'Dibatalkan']))
                        <form action="{{ route('pengajuan-cuti.verifikasi', $cuti->id) }}" method="POST">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        Catatan / Lembar Telaah Verifikator (Wajib jika menolak):
                                    </label>
                                    <textarea name="catatan" rows="2" placeholder="Tuliskan catatan kelayakan berkas, kepatuhan kuota cuti, atau catatan revisi penolakan..."
                                              class="w-full text-xs rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">{{ old('catatan', $cuti->catatan_verifikator) }}</textarea>
                                </div>

                                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                                    {{-- 1. Tombol Verifikasi Admin --}}
                                    @if($cuti->verifikasi_admin_at)
                                        <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5" title="Selesai pada {{ $cuti->verifikasi_admin_at->format('d/m/Y H:i') }}">
                                            ✓ 1. Admin Screening: Selesai
                                        </span>
                                    @elseif($isAdmin)
                                        <button type="submit" name="tahap" value="verifikasi_admin" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-xs transition flex items-center gap-1.5">
                                            ✓ 1. Admin Screening Berkas
                                        </button>
                                    @else
                                        <span class="px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-slate-200 select-none">
                                            ⏳ 1. Menunggu Admin Screening Berkas
                                        </span>
                                    @endif

                                    {{-- 2. Tombol Paraf Ka Pokja Keu-Kepeg --}}
                                    @if($cuti->paraf_kapokja_at)
                                        <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5" title="Selesai pada {{ $cuti->paraf_kapokja_at->format('d/m/Y H:i') }}">
                                            ✓ 2. Paraf Ka Pokja: Selesai
                                        </span>
                                    @elseif(!$cuti->verifikasi_admin_at)
                                        <span class="px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 select-none opacity-60" title="🔒 Terkunci: Menunggu Admin Screening Berkas">
                                            🔒 2. Paraf Pokja (Terkunci)
                                        </span>
                                    @elseif(($isKaPokja ?? false) || $isAdmin)
                                        <button type="submit" name="tahap" value="paraf_kapokja" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-teal-600 hover:bg-teal-700 text-white shadow-xs transition flex items-center gap-1.5">
                                            ✒ 2. Periksa & Paraf Ka Pokja
                                        </button>
                                    @else
                                        <span class="px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-slate-200 select-none">
                                            ⏳ 2. Menunggu Paraf Ka Pokja
                                        </span>
                                    @endif

                                    {{-- 3. Tombol Paraf Kabag Umum --}}
                                    @if($cuti->paraf_kabag_at)
                                        <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5" title="Selesai pada {{ $cuti->paraf_kabag_at->format('d/m/Y H:i') }}">
                                            ✓ 3. Paraf Kabag: Selesai
                                        </span>
                                    @elseif(!$cuti->paraf_kapokja_at)
                                        <span class="px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 select-none opacity-60" title="🔒 Terkunci: Menunggu Paraf Ka Pokja">
                                            🔒 3. Paraf Kabag (Terkunci)
                                        </span>
                                    @elseif(($isKabag ?? false) || $isAdmin)
                                        <button type="submit" name="tahap" value="paraf_kabag" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition flex items-center gap-1.5">
                                            ✒ 3. Telaah Naskah & Paraf Kabag
                                        </button>
                                    @else
                                        <span class="px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 border border-slate-200 select-none">
                                            ⏳ 3. Menunggu Paraf Kabag
                                        </span>
                                    @endif

                                    {{-- Tombol Tolak / Kembalikan --}}
                                    <button type="submit" name="tahap" value="tolak" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition"
                                            onclick="return confirm('Apakah Anda yakin ingin menolak atau mengembalikan berkas cuti ini? Pastikan kolom catatan telaah telah diisi.');">
                                        ✕ Kembalikan / Tolak
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="text-xs text-slate-600 italic">
                            Permohonan cuti ini telah berstatus final ({{ $cuti->status }}).
                            @if($cuti->catatan_verifikator)
                                <div class="mt-1 font-normal text-slate-700">Catatan telaah: "{{ $cuti->catatan_verifikator }}"</div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            {{-- FORM TAHAP 1: Pertimbangan Atasan Langsung --}}
            @if($canGivePertimbangan)
                <div class="bg-white rounded-xl shadow-sm border border-amber-300 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span>✍️</span> VI. Form Pertimbangan Atasan Langsung
                        <span class="text-xs font-normal text-gray-500">({{ $pejabatInfo['atasan_jabatan'] ?? 'Atasan Langsung' }})</span>
                    </h3>
                    <p class="text-xs text-amber-800 mb-4 bg-amber-50 border border-amber-200 rounded px-3.5 py-2.5 leading-relaxed">
                        Silakan berikan catatan <strong>PERTIMBANGAN ATASAN LANGSUNG</strong>. Setelah Anda simpan, permohonan akan secara otomatis diteruskan ke <strong>{{ $pejabatInfo['pybmc_jabatan'] ?? 'Pejabat Berwenang' }}</strong> untuk persetujuan / keputusan akhir.
                    </p>
                    <form action="{{ route('pengajuan-cuti.approve', $cuti->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                Pertimbangan Atasan <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-amber-500 focus:border-amber-500 font-medium">
                                <option value="Disetujui">✅ Setuju (Diteruskan untuk Keputusan Akhir)</option>
                                <option value="Ditolak">❌ Tidak Disetujui (Ditolak Langsung)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                Catatan Pertimbangan Atasan Langsung
                            </label>
                            <textarea name="catatan_atasan_langsung" rows="3" required
                                      placeholder="Tuliskan catatan pertimbangan Anda di sini..."
                                      class="w-full rounded-lg border-gray-300 text-sm focus:ring-amber-500 focus:border-amber-500">{{ old('catatan_atasan_langsung', $cuti->catatan_atasan_langsung) }}</textarea>
                        </div>
                        <div class="flex justify-end pt-1">
                            <button type="submit"
                                    class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm shadow transition flex items-center gap-2">
                                <span>💾</span> Simpan Pertimbangan Atasan Langsung
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- NOTIFIKASI INFO: Menunggu Pertimbangan Atasan Langsung --}}
            @if($isPybmcWaitingAtasan)
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 text-blue-800 flex items-start gap-3 shadow-sm">
                    <span class="text-2xl">⏳</span>
                    <div class="text-sm">
                        <div class="font-bold text-blue-900 mb-0.5">Menunggu Pertimbangan Atasan Langsung</div>
                        <div class="text-xs leading-relaxed text-blue-700">
                            Anda adalah <strong>{{ $pejabatInfo['pybmc_jabatan'] ?? 'Pejabat Berwenang' }}</strong> untuk permohonan ini.
                            Permohonan saat ini sedang menunggu catatan pertimbangan dari Atasan Langsung: <strong>{{ $pejabatInfo['atasan_jabatan'] ?? 'Atasan Langsung' }} ({{ $pejabatInfo['atasan_nama'] ?? '—' }})</strong>.
                            Form Keputusan Final akan otomatis aktif setelah Atasan Langsung memberikan pertimbangan.
                        </div>
                    </div>
                </div>
            @endif

            {{-- FORM TAHAP 2: Keputusan Pejabat Berwenang --}}
            @if($canGiveKeputusan)
                <div class="bg-white rounded-xl shadow-sm border border-blue-300 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span>🏛️</span> VII. Form Keputusan Pejabat Berwenang
                        <span class="text-xs font-normal text-gray-500">({{ $pejabatInfo['pybmc_jabatan'] ?? 'Pejabat Berwenang' }})</span>
                    </h3>
                    <p class="text-xs text-blue-800 mb-4 bg-blue-50 border border-blue-200 rounded px-3.5 py-2.5 leading-relaxed">
                        Pertimbangan Atasan Langsung telah diterima: <strong>{{ $cuti->pertimbangan_atasan ?? '-' }}</strong>.
                        @if($cuti->catatan_atasan_langsung)
                            <em>("{{ $cuti->catatan_atasan_langsung }}")</em>.
                        @endif
                        Silakan tentukan Keputusan Final (Disetujui / Ditolak). Keputusan ini akan langsung tercatat resmi dan masuk sebagai arsip ke Dekan serta Admin.
                    </p>
                    <form action="{{ route('pengajuan-cuti.approve', $cuti->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="status" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                    Keputusan Akhir Pejabat Berwenang <span class="text-red-500">*</span>
                                </label>
                                <select name="status" id="status" required
                                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500 font-medium">
                                    <option value="Disetujui" @selected(old('status') == 'Disetujui')>✅ Disetujui — Cuti Diberikan</option>
                                    <option value="Ditolak" @selected(old('status') == 'Ditolak')>❌ Ditolak — Tidak Disetujui</option>
                                </select>
                            </div>
                            <div>
                                <label for="nomor_surat_input" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                    Nomor Surat Izin Cuti (Opsional)
                                </label>
                                @php
                                    $currentNomor = old('nomor_surat_input', old('nomor_surat', $cuti->nomor_surat ?? ''));
                                    $prefixVal = $currentNomor;
                                    if (str_contains($currentNomor, '/UN19')) {
                                        $prefixVal = explode('/UN19', $currentNomor)[0];
                                    }
                                @endphp
                                <div class="flex rounded-lg shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 font-mono text-xs">
                                        Nomor:
                                    </span>
                                    <input type="text" name="nomor_surat_input" id="nomor_surat_input"
                                           value="{{ $prefixVal }}"
                                           placeholder="123"
                                           class="flex-1 min-w-0 block w-full px-3 py-2 border border-gray-300 text-sm font-mono font-bold text-gray-900 focus:ring-emerald-500 focus:border-emerald-500">
                                    <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 bg-slate-100 text-slate-700 font-mono text-xs font-semibold">
                                        /UN19.5.1.1.10/KP/{{ date('Y') }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-1">Isi nomor manual urut surat (misal: 125). Format instansi otomatis disematkan.</p>
                            </div>
                        </div>
                        <div>
                            <label for="catatan_pimpinan" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                Catatan Keputusan Pejabat Berwenang
                            </label>
                            <textarea name="catatan_pimpinan" id="catatan_pimpinan" rows="2"
                                      placeholder="Tambahkan catatan keputusan jika diperlukan..."
                                      class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">{{ old('catatan_pimpinan', $cuti->catatan_pimpinan) }}</textarea>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                    class="px-6 py-2.5 bg-slate-900 hover:bg-black text-white font-semibold rounded-lg text-sm shadow transition flex items-center gap-2">
                                <span>💾</span> Simpan Keputusan Final
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
