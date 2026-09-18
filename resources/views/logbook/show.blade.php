<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>📄</span> Rincian Aktivitas Logbook
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Detail Catatan Kinerja Harian Pegawai FKP UNRI
                </p>
            </div>
            <a href="{{ url()->previous() ?: route('logbook.index') }}"
               class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 px-3.5 py-2 rounded-lg transition">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- KARTU STATUS & EVALUASI --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Status Verifikasi</div>
                        <div class="mt-1 flex items-center gap-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $logbook->status_badge_class }}">
                                {{ $logbook->status_label }}
                            </span>
                            @if($logbook->diverifikasi_pada)
                                <span class="text-xs text-gray-500">
                                    Diverifikasi pada {{ \Carbon\Carbon::parse($logbook->diverifikasi_pada)->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB
                                    oleh <strong>{{ $logbook->verifikator?->name ?? 'Pimpinan' }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- TOMBOL AKSI CEPAT JIKA BERHAK EDIT --}}
                    <div class="flex items-center gap-2">
                        @if($logbook->canEditBy(auth()->user()))
                            <a href="{{ route('logbook.edit', $logbook->id) }}"
                               class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white transition shadow-sm">
                                ✏️ Edit Catatan
                            </a>
                        @endif

                        @if(in_array($logbook->status, ['draft', 'perlu_revisi']) && $logbook->user_id === auth()->id())
                            <form method="POST" action="{{ route('logbook.submit', $logbook->id) }}" onsubmit="return confirm('Ajukan aktivitas ini ke atasan?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm">
                                    🚀 Ajukan ke Atasan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($logbook->catatan_atasan)
                    <div class="mt-4 p-4 rounded-xl bg-amber-50/80 border border-amber-200 text-amber-900 text-xs">
                        <div class="font-bold flex items-center gap-1.5 mb-1">
                            <span>💬</span> Catatan / Evaluasi dari Atasan:
                        </div>
                        <p class="text-gray-800 bg-white p-3 rounded-lg border border-amber-100 font-medium">
                            {{ $logbook->catatan_atasan }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- KARTU DETAIL AKTIVITAS --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-600 bg-blue-50 border border-blue-200 px-3 py-1 rounded-full">
                            {{ $logbook->kategori_kegiatan }}
                        </span>
                        <div class="text-xs text-gray-500 font-medium">
                            {{ $logbook->tanggal_formatted }}
                        </div>
                    </div>
                    <h3 class="text-xl font-extrabold text-gray-900 mt-3">
                        {{ $logbook->aktivitas }}
                    </h3>
                </div>

                <div class="p-6 sm:p-8 space-y-6 text-sm">
                    {{-- INFO METRIK --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-gray-50 border border-gray-200 text-center sm:text-left">
                        <div>
                            <div class="text-xs text-gray-500 font-semibold uppercase">Waktu Pelaksanaan</div>
                            <div class="text-sm font-bold text-gray-900 mt-0.5">
                                {{ $logbook->jam_kerja_formatted }} WIB
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-semibold uppercase">Durasi Pengerjaan</div>
                            <div class="text-sm font-bold text-blue-700 mt-0.5">
                                ⏱️ {{ $logbook->durasi_formatted }} ({{ $logbook->durasi_menit }} Menit)
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-semibold uppercase">Capaian / Output</div>
                            <div class="text-sm font-bold text-gray-900 mt-0.5">
                                {{ $logbook->jumlah_output }} {{ $logbook->satuan_output }}
                                @if($logbook->output_kegiatan)
                                    <span class="text-xs font-normal text-gray-500 block">({{ $logbook->output_kegiatan }})</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- RINCIAN DESKRIPSI --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            Uraian / Deskripsi Pekerjaan:
                        </h4>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-gray-800 leading-relaxed whitespace-pre-line text-sm">
                            {{ $logbook->deskripsi_kegiatan }}
                        </div>
                    </div>

                    {{-- BUKTI BERKAS / LAMPIRAN --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            Bukti Dokumen / Foto Dokumentasi:
                        </h4>
                        @if($logbook->file_lampiran)
                            <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <span class="p-2.5 rounded-xl bg-blue-100 text-blue-700">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </span>
                                    <div>
                                        <div class="text-xs font-bold text-gray-900">Berkas Lampiran Kegiatan</div>
                                        <div class="text-[11px] text-gray-500">{{ basename($logbook->file_lampiran) }}</div>
                                    </div>
                                </div>
                                <a href="{{ route('document.preview', $logbook->file_lampiran) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Buka / Lihat Berkas
                                </a>
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Tidak ada berkas lampiran yang diunggah untuk aktivitas ini.</p>
                        @endif
                    </div>

                    {{-- IDENTITAS PEGAWAI --}}
                    <div class="pt-6 border-t border-gray-200">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Informasi Pegawai</div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-base">
                                {{ strtoupper(substr($logbook->pegawai?->nama ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">{{ $logbook->pegawai?->nama }}</div>
                                <div class="text-xs text-gray-500">
                                    NIP: {{ $logbook->pegawai?->nip ?? '-' }} • Unit: {{ $logbook->pegawai?->unitKerja?->nama_unit ?? 'FKP UNRI' }} • Jabatan: {{ $logbook->pegawai?->jabatan?->nama_jabatan ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL / FORM VERIFIKASI UNTUK PIMPINAN / ADMIN --}}
                    @if(auth()->user()->hasRole(['admin', 'pimpinan']))
                        <div class="mt-8 pt-6 border-t border-gray-200 bg-slate-50 p-6 rounded-2xl border">
                            <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <span>⚖️</span> Verifikasi Aktivitas (Khusus Pimpinan & Admin)
                            </h4>
                            <form method="POST" action="{{ route('admin.logbook.verify', $logbook->id) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Keputusan Verifikasi</label>
                                    <select name="status" required class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="disetujui" {{ $logbook->status === 'disetujui' ? 'selected' : '' }}>✅ Disetujui (Valid & Sesuai)</option>
                                        <option value="perlu_revisi" {{ $logbook->status === 'perlu_revisi' ? 'selected' : '' }}>⚠️ Perlu Revisi (Kembalikan ke Pegawai)</option>
                                        <option value="ditolak" {{ $logbook->status === 'ditolak' ? 'selected' : '' }}>❌ Ditolak (Tidak Memenuhi Syarat)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Catatan / Arahan Atasan (Opsional/Wajib untuk Revisi/Tolak)</label>
                                    <textarea name="catatan_atasan" rows="3" placeholder="Tuliskan catatan evaluasi atau instruksi revisi untuk pegawai..."
                                              class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('catatan_atasan', $logbook->catatan_atasan) }}</textarea>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs shadow-md transition">
                                        Simpan Hasil Verifikasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
