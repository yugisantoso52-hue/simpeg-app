<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>➕</span> Catat Aktivitas Kinerja Harian
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Formulir Pengisian Logbook Kinerja Harian Pegawai FKP UNRI
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

                {{-- INFORMASI PEGAWAI --}}
                <div class="mb-6 p-4 rounded-xl bg-blue-50/70 border border-blue-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <div class="text-xs text-blue-600 font-semibold uppercase tracking-wider">Identitas Pegawai</div>
                        <div class="text-base font-bold text-gray-900 mt-0.5">{{ $pegawai->nama }}</div>
                        <div class="text-xs text-gray-500">NIP: {{ $pegawai->nip ?? '-' }} • {{ $pegawai->unitKerja?->nama_unit ?? 'FKP UNRI' }}</div>
                    </div>
                    <div class="text-xs text-blue-700 bg-white px-3 py-1.5 rounded-lg border border-blue-200 font-medium">
                        {{ $pegawai->jabatan?->nama_jabatan ?? 'Pegawai' }}
                    </div>
                </div>

                {{-- BANNER INDIKATOR JARINGAN PWA OFFLINE / ONLINE --}}
                <div id="pwa-network-banner" class="hidden mb-6 p-4 rounded-xl text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all">
                    <div class="flex items-center gap-2.5">
                        <span id="network-icon" class="text-xl">📴</span>
                        <div>
                            <strong id="network-title" class="block font-bold">Mode Offline Terdeteksi</strong>
                            <span id="network-desc" class="text-[11px] opacity-90">Koneksi internet terputus. Anda tetap bisa mencatat logbook dan menyimpannya di memori HP/laptop ini.</span>
                        </div>
                    </div>
                    <button type="button" onclick="saveToOfflineStorage()" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs shrink-0 shadow-xs self-start sm:self-auto">
                        💾 Simpan Draf di HP
                    </button>
                </div>

                {{-- PANEL DRAF TERSIMPAN SECARA OFFLINE DI PERANGKAT --}}
                <div id="offline-drafts-panel" class="hidden mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <span>📦</span> Draf Logbook Tersimpan di Perangkat Ini (<span id="offline-count">0</span>)
                        </span>
                        <span class="text-[10px] text-slate-400">Offline Local Storage</span>
                    </div>
                    <div id="offline-drafts-list" class="space-y-2 mt-2">
                        <!-- Diisi oleh JS -->
                    </div>
                </div>

                {{-- FORM ENTRI --}}
                <form id="logbook-form" method="POST" action="{{ route('logbook.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        {{-- TANGGAL --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Tanggal Aktivitas <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal" value="{{ old('tanggal', $defaultDate) }}" required
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
                            <input type="time" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai', '08:00') }}" required
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
                            <input type="time" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai', '09:30') }}" required
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
                            1 Jam 30 Menit (90 Menit)
                        </span>
                    </div>

                    {{-- KATEGORI KEGIATAN --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Kategori Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <select name="kategori_kegiatan" required
                                class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 @error('kategori_kegiatan') border-rose-500 @enderror">
                            <option value="">-- Pilih Kategori Aktivitas --</option>
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ old('kategori_kegiatan') == $kat ? 'selected' : '' }}>
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
                        <input type="text" name="aktivitas" value="{{ old('aktivitas') }}" required
                               placeholder="Contoh: Menginput nilai ujian mahasiswa blok Keperawatan Medikal Bedah"
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
                                  placeholder="Jelaskan secara rinci tahapan dan hasil dari pekerjaan yang Anda selesaikan..."
                                  class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 @error('deskripsi_kegiatan') border-rose-500 @enderror">{{ old('deskripsi_kegiatan') }}</textarea>
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
                            <input type="number" name="jumlah_output" value="{{ old('jumlah_output', 1) }}" min="1" required
                                   class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="sm:col-span-4">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Satuan Output <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="satuan_output" list="satuan_list" value="{{ old('satuan_output', 'Kegiatan') }}" required
                                   placeholder="Kegiatan, Dokumen, Berkas..."
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
                            <input type="text" name="output_kegiatan" value="{{ old('output_kegiatan') }}"
                                   placeholder="Misal: Draft RPS Blok A, Berita Acara Rapat"
                                   class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- UNGGAH BERKAS BUKTI LAMPIRAN --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Bukti Dokumen / Foto Dokumentasi (Opsional)
                        </label>
                        <input type="file" name="file_lampiran"
                               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                               class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-300 rounded-xl p-1">
                        <p class="text-[11px] text-gray-400 mt-1">
                            Format yang didukung: PDF, PNG, JPG, DOCX, XLSX (Maksimal 10 MB).
                        </p>
                        @error('file_lampiran')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- TOMBOL AKSI --}}
                    <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row justify-end items-center gap-3">
                        <a href="{{ route('logbook.index') }}"
                           class="w-full sm:w-auto px-5 py-2.5 text-center text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                            Batal
                        </a>

                        <button type="button" onclick="saveToOfflineStorage()"
                                class="w-full sm:w-auto px-4 py-2.5 text-center text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 border border-amber-300 rounded-xl transition flex items-center justify-center gap-1.5 shadow-2xs">
                            <span>📱</span> Simpan Draf Lokal (HP)
                        </button>

                        <button type="submit" name="action" value="draft"
                                class="w-full sm:w-auto px-5 py-2.5 text-center text-xs font-bold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-xl transition">
                            💾 Simpan sebagai Draft
                        </button>

                        <button type="submit" name="action" value="diajukan"
                                class="w-full sm:w-auto px-6 py-2.5 text-center text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                            🚀 Langsung Ajukan ke Atasan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- JAVASCRIPT: PERHITUNGAN DURASI & FITUR PWA OFFLINE STORAGE --}}
    <script>
        const STORAGE_KEY = 'sikap_logbook_offline_drafts';

        // 1. Simpan Draf Form ke Local Storage
        function saveToOfflineStorage() {
            const form = document.getElementById('logbook-form');
            const tanggal = form.querySelector('[name="tanggal"]').value;
            const jamMulai = form.querySelector('[name="jam_mulai"]').value;
            const jamSelesai = form.querySelector('[name="jam_selesai"]').value;
            const kategori = form.querySelector('[name="kategori_kegiatan"]').value;
            const deskripsi = form.querySelector('[name="deskripsi_aktivitas"]').value;
            const output = form.querySelector('[name="output_hasil"]').value;
            const volume = form.querySelector('[name="volume_capaian"]').value;
            const satuan = form.querySelector('[name="satuan_output"]').value;

            if (!deskripsi && !kategori) {
                alert('Silakan isi minimal kategori kegiatan atau uraian aktivitas terlebih dahulu.');
                return;
            }

            const draft = {
                id: Date.now(),
                tanggal: tanggal,
                jam_mulai: jamMulai,
                jam_selesai: jamSelesai,
                kategori_kegiatan: kategori,
                deskripsi_aktivitas: deskripsi,
                output_hasil: output,
                volume_capaian: volume,
                satuan_output: satuan,
                saved_at: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            };

            let drafts = getOfflineDrafts();
            drafts.unshift(draft);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(drafts));

            renderOfflineDrafts();
            alert('✓ Berhasil! Draf logbook telah disimpan di memori HP/laptop Anda.');
        }

        function getOfflineDrafts() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            } catch (e) {
                return [];
            }
        }

        function deleteOfflineDraft(id) {
            let drafts = getOfflineDrafts().filter(d => d.id !== id);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(drafts));
            renderOfflineDrafts();
        }

        function restoreOfflineDraft(id) {
            const draft = getOfflineDrafts().find(d => d.id === id);
            if (!draft) return;

            const form = document.getElementById('logbook-form');
            if (draft.tanggal) form.querySelector('[name="tanggal"]').value = draft.tanggal;
            if (draft.jam_mulai) form.querySelector('[name="jam_mulai"]').value = draft.jam_mulai;
            if (draft.jam_selesai) form.querySelector('[name="jam_selesai"]').value = draft.jam_selesai;
            if (draft.kategori_kegiatan) form.querySelector('[name="kategori_kegiatan"]').value = draft.kategori_kegiatan;
            if (draft.deskripsi_aktivitas) form.querySelector('[name="deskripsi_aktivitas"]').value = draft.deskripsi_aktivitas;
            if (draft.output_hasil) form.querySelector('[name="output_hasil"]').value = draft.output_hasil;
            if (draft.volume_capaian) form.querySelector('[name="volume_capaian"]').value = draft.volume_capaian;
            if (draft.satuan_output) form.querySelector('[name="satuan_output"]').value = draft.satuan_output;

            const startInput = document.getElementById('jam_mulai');
            startInput.dispatchEvent(new Event('input'));

            alert('✓ Draf berhasil dipulihkan ke dalam formulir!');
            window.scrollTo({ top: form.offsetTop - 50, behavior: 'smooth' });
        }

        function renderOfflineDrafts() {
            const panel = document.getElementById('offline-drafts-panel');
            const list = document.getElementById('offline-drafts-list');
            const countBadge = document.getElementById('offline-count');
            const drafts = getOfflineDrafts();

            if (drafts.length === 0) {
                panel.classList.add('hidden');
                return;
            }

            panel.classList.remove('hidden');
            countBadge.textContent = drafts.length;

            list.innerHTML = '';
            drafts.forEach((d) => {
                const item = document.createElement('div');
                item.className = 'p-3 bg-white rounded-xl border border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-xs shadow-2xs';
                item.innerHTML = `
                    <div>
                        <strong class="text-slate-800">${d.kategori_kegiatan || 'Aktivitas Umum'}</strong>
                        <span class="text-slate-400 font-mono text-[11px] ml-1.5">(${d.tanggal || '-'} • ${d.jam_mulai}-${d.jam_selesai})</span>
                        <p class="text-slate-600 line-clamp-1 text-[11px] mt-0.5">${d.deskripsi_aktivitas || '-'}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" onclick="restoreOfflineDraft(${d.id})" class="px-2.5 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg font-bold">
                            ✏️ Gunakan
                        </button>
                        <button type="button" onclick="deleteOfflineDraft(${d.id})" class="px-2 py-1 text-rose-600 hover:bg-rose-50 rounded-lg font-bold">
                            ✕ Hapus
                        </button>
                    </div>
                `;
                list.appendChild(item);
            });
        }

        // 2. Monitoring Status Jaringan Online/Offline
        function updateNetworkStatus() {
            const banner = document.getElementById('pwa-network-banner');
            const icon = document.getElementById('network-icon');
            const title = document.getElementById('network-title');
            const desc = document.getElementById('network-desc');

            if (!navigator.onLine) {
                banner.className = 'mb-6 p-4 rounded-xl text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all bg-amber-100 border border-amber-300 text-amber-900';
                icon.textContent = '📴';
                title.textContent = 'Mode Offline (Tanpa Sinyal Internet)';
                desc.textContent = 'Anda dapat tetap menulis logbook. Gunakan tombol "Simpan Draf di HP" agar tulisan Anda tidak hilang.';
                banner.classList.remove('hidden');
            } else {
                const drafts = getOfflineDrafts();
                if (drafts.length > 0) {
                    banner.className = 'mb-6 p-4 rounded-xl text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all bg-emerald-100 border border-emerald-300 text-emerald-900';
                    icon.textContent = '🌐';
                    title.textContent = 'Koneksi Internet Terhubung';
                    desc.textContent = `Ada ${drafts.length} draf offline tersimpan di perangkat ini yang siap Anda ajukan ke server.`;
                    banner.classList.remove('hidden');
                } else {
                    banner.classList.add('hidden');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Durasi otomatis
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
            updateDuration();

            // PWA Offline logic
            updateNetworkStatus();
            renderOfflineDrafts();

            window.addEventListener('online', updateNetworkStatus);
            window.addEventListener('offline', updateNetworkStatus);
        });
    </script>
</x-app-layout>
