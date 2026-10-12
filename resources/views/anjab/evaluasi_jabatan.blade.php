<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('anjab.show', $anjab) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                        &larr; Kembali ke Dokumen Anjab
                    </a>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>⚖️</span> Evaluasi Jabatan &amp; Penetapan Kelas Jabatan (Job Grading)
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Metode Factor Evaluation System (FES) 9 Faktor Standar BKN &amp; PermenPAN-RB No. 34/2011
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('anjab.show', $anjab) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                    <span>📑</span> Lihat Dokumen
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{
            f1: 550,
            f2: 275,
            f3: 275,
            f4: 150,
            f5: 150,
            f6: 60,
            f7: 50,
            f8: 20,
            f9: 20,
            get totalNilai() {
                return Number(this.f1) + Number(this.f2) + Number(this.f3) + Number(this.f4) + Number(this.f5) + Number(this.f6) + Number(this.f7) + Number(this.f8) + Number(this.f9);
            },
            get calculatedGrade() {
                const n = this.totalNilai;
                if (n <= 240) return 1;
                if (n <= 300) return 2;
                if (n <= 370) return 3;
                if (n <= 450) return 4;
                if (n <= 650) return 5;
                if (n <= 850) return 6;
                if (n <= 1100) return 7;
                if (n <= 1350) return 8;
                if (n <= 1600) return 9;
                if (n <= 1850) return 10;
                if (n <= 2100) return 11;
                if (n <= 2350) return 12;
                if (n <= 2750) return 13;
                if (n <= 3150) return 14;
                if (n <= 3600) return 15;
                if (n <= 4050) return 16;
                return 17;
            }
         }">

        {{-- Profil Jabatan yang Dievaluasi --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                    Jabatan Sasaran Evaluasi
                </span>
                <h2 class="text-lg font-black text-gray-900 mt-1">{{ $anjab->jabatan->nama_jabatan ?? '-' }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Unit Kerja: <b>{{ $anjab->unitKerja->nama_unit ?? 'Fakultas Keperawatan UNRI' }}</b> &bull; Kode Anjab: <span class="font-mono">{{ $anjab->kode_anjab ?? '-' }}</span>
                </p>
            </div>
            <div class="flex items-center gap-4 bg-amber-50/60 p-3 rounded-xl border border-amber-200 shrink-0">
                <div class="text-center">
                    <span class="text-[10px] font-bold text-gray-500 block uppercase">Kelas Eksisting</span>
                    <span class="text-xl font-black text-gray-700">Grade {{ $anjab->kelas_jabatan ?? $anjab->jabatan->kelas_jabatan ?? '-' }}</span>
                </div>
                <div class="text-gray-300 font-light text-2xl">➔</div>
                <div class="text-center">
                    <span class="text-[10px] font-bold text-amber-800 block uppercase">Hasil Kalkulasi FES</span>
                    <span class="text-xl font-black text-amber-800" x-text="'Grade ' + calculatedGrade">Grade -</span>
                </div>
            </div>
        </div>

        {{-- Live Score Card --}}
        <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 rounded-2xl p-6 text-white shadow-md flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-amber-100">Simulasi Real-Time Poin Evaluasi</span>
                <div class="text-4xl font-black mt-1" x-text="totalNilai.toLocaleString('id-ID') + ' Poin'">1.550 Poin</div>
                <p class="text-xs text-amber-100 mt-1">Total akumulasi 9 faktor penimbang tuntutan jabatan</p>
            </div>
            <div class="bg-white/20 backdrop-blur-sm px-6 py-4 rounded-xl border border-white/30 text-center">
                <span class="text-xs font-bold text-white uppercase tracking-wider">Rekomendasi Kelas Jabatan</span>
                <div class="text-3xl font-black text-white mt-0.5" x-text="'Kelas ' + calculatedGrade">Kelas 9</div>
                <span class="text-[10px] text-amber-100">Standar BKN RI</span>
            </div>
        </div>

        {{-- Formulir Evaluasi 9 Faktor FES --}}
        <form method="POST" action="{{ route('anjab.evaluasi-jabatan.update', $anjab) }}" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden divide-y divide-gray-100">
                <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-800">9 Faktor Penimbang Evaluasi Jabatan (FES)</h3>
                    <span class="text-[11px] text-gray-500">Pilih tingkat faktor yang paling mencerminkan tuntutan jabatan</span>
                </div>

                {{-- Faktor 1 --}}
                <div class="p-6 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-gray-800 text-sm">Faktor 1: Pengetahuan yang Dibutuhkan Jabatan</label>
                        <span class="text-xs font-mono font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded" x-text="f1 + ' Poin'"></span>
                    </div>
                    <p class="text-xs text-gray-500">Sifat dan tingkat keahlian pengetahuan yang dipersyaratkan untuk melaksanakan pekerjaan.</p>
                    <select name="faktor_1" x-model.number="f1" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="50">Tingkat 1-1 (50 Poin) - Pengetahuan keterampilan sederhana / hafalan</option>
                        <option value="190">Tingkat 1-2 (190 Poin) - Pengetahuan dasar operasional / pelatihan singkat</option>
                        <option value="350">Tingkat 1-3 (350 Poin) - Pengetahuan teknis dasar / standar prosedur umum</option>
                        <option value="550">Tingkat 1-4 (550 Poin) - Pengetahuan mendalam bidang teknis / sarjana muda / D3</option>
                        <option value="750">Tingkat 1-5 (750 Poin) - Pengetahuan dasar profesi / konsep teori Strata 1 (S1)</option>
                        <option value="950">Tingkat 1-6 (950 Poin) - Pengetahuan profesi ahli / Magister (S2) / analisis mendalam</option>
                        <option value="1250">Tingkat 1-7 (1.250 Poin) - Penguasaan bidang ilmu luas / kepakaran / Doktoral (S3)</option>
                        <option value="1550">Tingkat 1-8 (1.550 Poin) - Penguasaan otoritatif nasional / kepakaran puncak</option>
                    </select>
                </div>

                {{-- Faktor 2 --}}
                <div class="p-6 space-y-2 bg-gray-50/40">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-gray-800 text-sm">Faktor 2: Pengawasan Penyelia</label>
                        <span class="text-xs font-mono font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded" x-text="f2 + ' Poin'"></span>
                    </div>
                    <p class="text-xs text-gray-500">Sejauh mana penyelia memberikan instruksi, pengawasan berkelanjutan, dan mengkaji hasil kerja.</p>
                    <select name="faktor_2" x-model.number="f2" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="25">Tingkat 2-1 (25 Poin) - Tugas spesifik terperinci, pengawasan ketat langsung</option>
                        <option value="125">Tingkat 2-2 (125 Poin) - Instruksi umum untuk tugas rutin, pengawasan berkala</option>
                        <option value="275">Tingkat 2-3 (275 Poin) - Menentukan langkah sendiri sesuai tenggat waktu, pengawasan hasil akhir</option>
                        <option value="450">Tingkat 2-4 (450 Poin) - Tanggung jawab perencanaan proyek mandiri, konsultasi hanya masalah kebijakan</option>
                        <option value="650">Tingkat 2-5 (650 Poin) - Kemandirian penuh pimpinan puncak, evaluasi hanya pencapaian visi institusi</option>
                    </select>
                </div>

                {{-- Faktor 3 --}}
                <div class="p-6 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-gray-800 text-sm">Faktor 3: Pedoman Kerja</label>
                        <span class="text-xs font-mono font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded" x-text="f3 + ' Poin'"></span>
                    </div>
                    <p class="text-xs text-gray-500">Ketersediaan petunjuk, SOP, peraturan perundang-undangan dan kebutuhan penilaian dalam menerapkannya.</p>
                    <select name="faktor_3" x-model.number="f3" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="25">Tingkat 3-1 (25 Poin) - Pedoman sangat rinci dan jelas, tidak memerlukan penyesuaian</option>
                        <option value="125">Tingkat 3-2 (125 Poin) - Prosedur tersedia tetapi memerlukan pemilihan pedoman yang tepat</option>
                        <option value="275">Tingkat 3-3 (275 Poin) - Pedoman berupa garis besar umum, pegawai menginterpretasikan aturan</option>
                        <option value="450">Tingkat 3-4 (450 Poin) - Pedoman terbatas atau ambigu, dituntut merumuskan pedoman/kebijakan baru</option>
                        <option value="650">Tingkat 3-5 (650 Poin) - Kebijakan nasional dasar, dituntut merumuskan regulasi strategis</option>
                    </select>
                </div>

                {{-- Faktor 4 --}}
                <div class="p-6 space-y-2 bg-gray-50/40">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-gray-800 text-sm">Faktor 4: Kompleksitas Tugas</label>
                        <span class="text-xs font-mono font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded" x-text="f4 + ' Poin'"></span>
                    </div>
                    <p class="text-xs text-gray-500">Sifat pekerjaan, variasi tugas, kesulitan identifikasi masalah, dan orisinalitas pemecahan.</p>
                    <select name="faktor_4" x-model.number="f4" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="25">Tingkat 4-1 (25 Poin) - Tugas jelas, serupa, dan berulang</option>
                        <option value="75">Tingkat 4-2 (75 Poin) - Tugas terdiri dari beberapa langkah berurutan</option>
                        <option value="150">Tingkat 4-3 (150 Poin) - Analisis beragam faktor teknis, pemecahan masalah non-standar</option>
                        <option value="225">Tingkat 4-4 (225 Poin) - Kompleksitas tinggi, evaluasi data tidak lengkap, proses multi-disiplin</option>
                        <option value="325">Tingkat 4-5 (325 Poin) - Merumuskan teori/metode baru pada bidang profesi dinamis</option>
                        <option value="450">Tingkat 4-6 (450 Poin) - Kompleksitas strategis kepemimpinan institusi berskala makro</option>
                    </select>
                </div>

                {{-- Faktor 5 --}}
                <div class="p-6 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-gray-800 text-sm">Faktor 5: Ruang Lingkup dan Dampak</label>
                        <span class="text-xs font-mono font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded" x-text="f5 + ' Poin'"></span>
                    </div>
                    <p class="text-xs text-gray-500">Jangkauan hasil kerja jabatan dan efek langsungnya terhadap unit internal maupun publik.</p>
                    <select name="faktor_5" x-model.number="f5" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="25">Tingkat 5-1 (25 Poin) - Memfasilitasi pekerjaan individu lain dalam unit kecil</option>
                        <option value="75">Tingkat 5-2 (75 Poin) - Mempengaruhi kelancaran operasional unit bagian</option>
                        <option value="150">Tingkat 5-3 (150 Poin) - Mempengaruhi efisiensi seluruh fakultas atau program studi</option>
                        <option value="225">Tingkat 5-4 (225 Poin) - Mempengaruhi misi strategis universitas / pelayanan eksternal luas</option>
                        <option value="325">Tingkat 5-5 (325 Poin) - Dampak jangka panjang pada kebijakan pendidikan tinggi nasional</option>
                        <option value="450">Tingkat 5-6 (450 Poin) - Kebijakan nasional berdampak pada hajat publik luas</option>
                    </select>
                </div>

                {{-- Faktor 6 & 7 --}}
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/40">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-gray-800 text-sm">Faktor 6: Hubungan Personal</label>
                            <span class="text-xs font-mono font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded" x-text="f6 + ' Poin'"></span>
                        </div>
                        <select name="faktor_6" x-model.number="f6" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="10">Tingkat 6-1 (10 Poin) - Pegawai di unit yang sama</option>
                            <option value="25">Tingkat 6-2 (25 Poin) - Pegawai lintas unit dalam fakultas</option>
                            <option value="60">Tingkat 6-3 (60 Poin) - Pihak luar, dosen, mahasiswa, stakeholder eksternal</option>
                            <option value="110">Tingkat 6-4 (110 Poin) - Pejabat tinggi instansi lain, media massa, asosiasi profesi</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-gray-800 text-sm">Faktor 7: Tujuan Hubungan</label>
                            <span class="text-xs font-mono font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded" x-text="f7 + ' Poin'"></span>
                        </div>
                        <select name="faktor_7" x-model.number="f7" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="20">Tingkat 7-1 (20 Poin) - Memberi / menerima informasi faktual</option>
                            <option value="50">Tingkat 7-2 (50 Poin) - Mengkoordinasikan pekerjaan &amp; memecahkan kendala kerja</option>
                            <option value="120">Tingkat 7-3 (120 Poin) - Mempengaruhi, memotivasi, dan meyakinkan pihak lain</option>
                            <option value="220">Tingkat 7-4 (220 Poin) - Negosiasi kesepakatan strategis &amp; komitmen institusi</option>
                        </select>
                    </div>
                </div>

                {{-- Faktor 8 & 9 --}}
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-gray-800 text-sm">Faktor 8: Persyaratan Fisik</label>
                            <span class="text-xs font-mono font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded" x-text="f8 + ' Poin'"></span>
                        </div>
                        <select name="faktor_8" x-model.number="f8" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="5">Tingkat 8-1 (5 Poin) - Duduk nyaman, berjalan ringan, mengetik</option>
                            <option value="20">Tingkat 8-2 (20 Poin) - Berdiri lama, mengangkat beban sedang, aktif lapangan</option>
                            <option value="50">Tingkat 8-3 (50 Poin) - Ketahanan fisik tinggi, mengangkat beban berat</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-gray-800 text-sm">Faktor 9: Lingkungan Pekerjaan</label>
                            <span class="text-xs font-mono font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded" x-text="f9 + ' Poin'"></span>
                        </div>
                        <select name="faktor_9" x-model.number="f9" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="5">Tingkat 9-1 (5 Poin) - Ruangan ber-AC aman, risiko bahaya minimal</option>
                            <option value="20">Tingkat 9-2 (20 Poin) - Lingkungan laboratorium, cairan kimia/biologis, alat pelindung diri</option>
                            <option value="50">Tingkat 9-3 (50 Poin) - Paparan zat berbahaya tinggi / risiko penularan infeksi klinis</option>
                        </select>
                    </div>
                </div>

                {{-- Catatan Evaluator --}}
                <div class="p-6 space-y-2 bg-gray-50/40">
                    <label class="font-bold text-gray-800 text-sm block">Catatan Tim Evaluator / Komite Jabatan</label>
                    <textarea name="catatan_evaluasi" rows="3" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Tambahkan catatan pertimbangan teknis atau justifikasi kelas jabatan..."></textarea>
                </div>
            </div>

            {{-- Action Submit --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('anjab.show', $anjab) }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-600 text-white text-xs font-black shadow-md hover:bg-amber-700 transition flex items-center gap-2">
                    <span>💾</span> Simpan Hasil Evaluasi &amp; Tetapkan Kelas Jabatan
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
