<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>✏️</span> Edit Aktivitas Logbook
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Perbarui data catatan kinerja atau tindak lanjuti catatan revisi dari atasan
                </p>
            </div>
            <a href="{{ route('logbook.index') }}"
               class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 px-3.5 py-2 rounded-lg transition">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">

                {{-- ALERT CATATAN REVISI JIKA ADA --}}
                @if($logbook->catatan_atasan)
                    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                        <div class="flex items-center gap-2 font-bold text-sm">
                            <span>⚠️</span> Catatan Koreksi dari Atasan / Verifikator:
                        </div>
                        <p class="mt-1.5 text-xs text-amber-800 bg-white/70 p-3 rounded-lg border border-amber-200/50">
                            {{ $logbook->catatan_atasan }}
                        </p>
                        <p class="text-[11px] text-amber-700 mt-1">
                            Silakan sesuaikan data sesuai catatan di atas, kemudian klik <strong>"Simpan & Ajukan Ulang"</strong>.
                        </p>
                    </div>
                @endif

                {{-- FORM EDIT --}}
                <form method="POST" action="{{ route('logbook.update', $logbook->id) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        {{-- TANGGAL --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Tanggal Aktivitas <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal" value="{{ old('tanggal', \Carbon\Carbon::parse($logbook->tanggal)->toDateString()) }}" required
                                   class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 @error('tanggal') border-rose-500 @enderror">
                            @error('tanggal')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- JAM MULAI --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Jam Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai', substr($logbook->jam_mulai, 0, 5)) }}" required
                                   class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 @error('jam_mulai') border-rose-500 @enderror">
                            @error('jam_mulai')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- JAM SELESAI --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Jam Selesai <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai', substr($logbook->jam_selesai, 0, 5)) }}" required
                                   class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 @error('jam_selesai') border-rose-500 @enderror">
                            @error('jam_selesai')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- LIVE DURATION BADGE --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                        <span class="text-gray-600 font-medium">Estimasi Durasi Pengerjaan:</span>
                        <span id="durasi_badge" class="font-bold px-2.5 py-1 rounded-lg bg-blue-100 text-blue-800">
                            {{ $logbook->durasi_formatted }} ({{ $logbook->durasi_menit }} Menit)
                        </span>
                    </div>

                    {{-- KATEGORI KEGIATAN --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Kategori Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <select name="kategori_kegiatan" required
                                class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 @error('kategori_kegiatan') border-rose-500 @enderror">
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ old('kategori_kegiatan', $logbook->kategori_kegiatan) == $kat ? 'selected' : '' }}>
                                    {{ $kat }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_kegiatan')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- RINGKASAN AKTIVITAS --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Ringkasan / Judul Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="aktivitas" value="{{ old('aktivitas', $logbook->aktivitas) }}" required
                               class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 @error('aktivitas') border-rose-500 @enderror">
                        @error('aktivitas')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- RINCIAN DESKRIPSI KEGIATAN --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Deskripsi / Rincian Pekerjaan yang Dilakukan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="deskripsi_kegiatan" rows="4" required
                                  class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 @error('deskripsi_kegiatan') border-rose-500 @enderror">{{ old('deskripsi_kegiatan', $logbook->deskripsi_kegiatan) }}</textarea>
                        @error('deskripsi_kegiatan')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- OUTPUT KEGIATAN --}}
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Jumlah Output <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="jumlah_output" value="{{ old('jumlah_output', $logbook->jumlah_output) }}" min="1" required
                                   class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="sm:col-span-4">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Satuan Output <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="satuan_output" list="satuan_list" value="{{ old('satuan_output', $logbook->satuan_output) }}" required
                                   class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <datalist id="satuan_list">
                                <option value="Kegiatan">
                                <option value="Dokumen">
                                <option value="Berkas">
                                <option value="Laporan">
                                <option value="Mahasiswa">
                                <option value="Peserta">
                                <option value="Modul">
                                <option value="Surat">
                            </datalist>
                        </div>

                        <div class="sm:col-span-5">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Nama / Bentuk Output (Opsional)
                            </label>
                            <input type="text" name="output_kegiatan" value="{{ old('output_kegiatan', $logbook->output_kegiatan) }}"
                                   class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- UNGGAH BERKAS BUKTI LAMPIRAN --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Bukti Dokumen / Foto Dokumentasi
                        </label>
                        @if($logbook->file_lampiran)
                            <div class="mb-2 flex items-center gap-2 p-2.5 rounded-lg bg-blue-50 border border-blue-200 text-xs text-blue-700">
                                <span>📎 Berkas saat ini telah tersimpan:</span>
                                <a href="{{ route('document.preview', $logbook->file_lampiran) }}" target="_blank" class="font-bold underline hover:text-blue-900">
                                    Lihat Berkas Lampiran
                                </a>
                            </div>
                        @endif
                        <input type="file" name="file_lampiran"
                               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                               class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-300 rounded-xl p-1">
                        <p class="text-[11px] text-gray-400 mt-1">
                            Pilih file baru jika ingin mengganti lampiran sebelumnya (PDF/JPG/PNG/DOCX/XLSX, maks 10 MB).
                        </p>
                    </div>

                    {{-- TOMBOL AKSI --}}
                    <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row justify-end items-center gap-3">
                        <a href="{{ route('logbook.index') }}"
                           class="w-full sm:w-auto px-5 py-2.5 text-center text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                            Batal
                        </a>

                        <button type="submit" name="action" value="draft"
                                class="w-full sm:w-auto px-5 py-2.5 text-center text-xs font-bold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-xl transition">
                            💾 Simpan Perubahan
                        </button>

                        <button type="submit" name="action" value="diajukan"
                                class="w-full sm:w-auto px-6 py-2.5 text-center text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                            🚀 Simpan & Ajukan Ulang ke Atasan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- JAVASCRIPT PERHITUNGAN DURASI OTOMATIS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startInput = document.getElementById('jam_mulai');
            const endInput = document.getElementById('jam_selesai');
            const badge = document.getElementById('durasi_badge');

            function updateDuration() {
                const start = startInput.value;
                const end = endInput.value;

                if (!start || !end) {
                    badge.textContent = '-';
                    return;
                }

                const [sh, sm] = start.split(':').map(Number);
                const [eh, em] = end.split(':').map(Number);

                const startMins = sh * 60 + sm;
                const endMins = eh * 60 + em;

                const diff = endMins - startMins;

                if (diff <= 0) {
                    badge.textContent = 'Jam selesai harus lebih besar dari jam mulai!';
                    badge.className = 'font-bold px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800';
                } else {
                    const hours = Math.floor(diff / 60);
                    const mins = diff % 60;
                    let text = '';
                    if (hours > 0 && mins > 0) {
                        text = `${hours} Jam ${mins} Menit (${diff} Menit)`;
                    } else if (hours > 0) {
                        text = `${hours} Jam (${diff} Menit)`;
                    } else {
                        text = `${mins} Menit`;
                    }
                    badge.textContent = text;
                    badge.className = 'font-bold px-2.5 py-1 rounded-lg bg-blue-100 text-blue-800';
                }
            }

            startInput.addEventListener('input', updateDuration);
            endInput.addEventListener('input', updateDuration);
        });
    </script>
</x-app-layout>
