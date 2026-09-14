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

                {{-- INFO PERSETUJUAN JIKA SUDAH DIPROSES --}}
                @if($cuti->status !== 'Menunggu Persetujuan')
                    <div class="mt-6 pt-5 border-t border-gray-100 bg-gray-50 p-4 rounded-lg">
                        <div class="text-xs font-bold uppercase text-gray-500 tracking-wider mb-2">Riwayat Keputusan Pejabat yang Berwenang</div>
                        <div class="text-sm text-gray-800">
                            <strong>Diverifikasi oleh:</strong> {{ $cuti->approver->name ?? 'Pimpinan Fakultas' }}
                            @if($cuti->approved_at)
                                <span class="text-xs text-gray-500">({{ $cuti->approved_at->translatedFormat('d F Y H:i') }})</span>
                            @endif
                        </div>
                        @if($cuti->catatan_pimpinan)
                            <div class="text-xs text-gray-700 mt-1 italic">
                                "Catatan: {{ $cuti->catatan_pimpinan }}"
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- FORM VERIFIKASI & PERSETUJUAN (KHUSUS ADMIN & PIMPINAN) --}}
            @if(Auth::user()->hasRole(['admin', 'pimpinan']))
                <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span>✍️</span> Keputusan Pejabat yang Berwenang Memberikan Cuti
                    </h3>

                    <form action="{{ route('pengajuan-cuti.approve', $cuti->id) }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="status" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                    Keputusan Persetujuan <span class="text-red-500">*</span>
                                </label>
                                <select name="status" id="status" required
                                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="Disetujui" @selected(old('status', $cuti->status) == 'Disetujui')>✅ Disetujui (Cuti Diberikan)</option>
                                    <option value="Ditolak" @selected(old('status', $cuti->status) == 'Ditolak')>❌ Ditolak (Tidak Disetujui)</option>
                                </select>
                            </div>

                            <div>
                                <label for="nomor_surat" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                    Nomor Surat Izin Cuti (Opsional)
                                </label>
                                <input type="text" name="nomor_surat" id="nomor_surat" value="{{ old('nomor_surat', $cuti->nomor_surat) }}"
                                       placeholder="Contoh: 123/UN19.5.1/KP/2026"
                                       class="w-full rounded-lg border-gray-300 font-mono text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label for="catatan_pimpinan" class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                Catatan / Pertimbangan Pimpinan
                            </label>
                            <textarea name="catatan_pimpinan" id="catatan_pimpinan" rows="2"
                                      placeholder="Tambahkan catatan jika diperlukan..."
                                      class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">{{ old('catatan_pimpinan', $cuti->catatan_pimpinan) }}</textarea>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                    class="px-6 py-2.5 bg-slate-900 hover:bg-black text-white font-semibold rounded-lg text-sm shadow transition">
                                💾 Simpan Keputusan Persetujuan
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
