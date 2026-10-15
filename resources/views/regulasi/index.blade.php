<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">Beranda</a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">Regulasi & Tata Naskah Dinas</span>
                </nav>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span class="p-2 bg-blue-100 text-blue-700 rounded-xl text-lg">⚖️</span>
                    Landasan Regulasi Tata Naskah & Edukasi Digital
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Dasar hukum formal penandatanganan mandat, hierarki paraf digital koordinasi, dan otentikasi naskah dinas di lingkungan Fakultas Keperawatan Universitas Riau.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('regulasi-tata-naskah.pdf') }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh Buku Panduan Resmi (PDF)
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- 1. HERO BANNER EDUKASI --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white p-6 sm:p-8 shadow-xl border border-blue-900/50">
            <div class="relative z-10 max-w-3xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-400/30 mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Kepastian Hukum & Tata Kelola Naskah Dinas Elektronik
                </span>
                <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight mb-2">
                    Edukasi Akuntabilitas Penandatanganan & Paraf Koordinasi SIKAP
                </h3>
                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    Setiap naskah dinas dan surat elektronik yang diterbitkan melalui SIKAP FKp UNRI dirancang memenuhi prinsip kepatuhan hukum (*legal compliance*), menjamin tanggung jawab penandatanganan tidak melampaui kewenangan, serta memberikan jaminan kepastian hukum bagi seluruh aparatur sipil negara di lingkungan Fakultas Keperawatan.
                </p>
            </div>
            <div class="absolute -right-6 -bottom-8 opacity-10 text-white text-9xl pointer-events-none font-serif select-none">
                §
            </div>
        </div>

        {{-- 2. GRID 3 PILAR HUKUM UTAMA --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Pilar 1: Administrasi Pemerintahan --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-blue-300 transition">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg mb-3">
                    🏛️
                </div>
                <h4 class="text-sm font-bold text-slate-800 mb-1">UU No. 30 Tahun 2014</h4>
                <div class="text-xs font-semibold text-blue-600 mb-2">Administrasi Pemerintahan</div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Menjadi dasar hukum pengalihan wewenang penandatanganan. Pejabat yang bertindak atas pelimpahan kewenangan pimpinan wajib menggunakan formula mandat resmi: <strong>a.n. (Atas Nama)</strong> untuk menjamin pertanggungjawaban yuridis.
                </p>
            </div>

            {{-- Pilar 2: Tata Naskah Dinas --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-emerald-300 transition">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg mb-3">
                    📑
                </div>
                <h4 class="text-sm font-bold text-slate-800 mb-1">PermenPAN-RB No. 1 Tahun 2023</h4>
                <div class="text-xs font-semibold text-emerald-600 mb-2">Tata Naskah Dinas Instansi Pemerintah</div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Mengatur prinsip, asas, serta hierarki naskah dinas. Menetapkan kewajiban <strong>Paraf Koordinasi Hierarkis</strong> berjenjang oleh pejabat pemeriksa sebelum dokumen ditandatangani oleh pimpinan yang berwenang.
                </p>
            </div>

            {{-- Pilar 3: ITE & Pembuktian Elektronik --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs hover:border-purple-300 transition">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg mb-3">
                    🔐
                </div>
                <h4 class="text-sm font-bold text-slate-800 mb-1">UU ITE & PP No. 71 Tahun 2019</h4>
                <div class="text-xs font-semibold text-purple-600 mb-2">Penyelenggaraan Sistem & Transaksi Elektronik</div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Menetapkan bahwa dokumen elektronik ber-QR Code dengan verifikasi kriptografis dan catatan audit *timestamp* memiliki kekuatan pembuktian hukum yang sah (*admissible evidence*) di hadapan hukum.
                </p>
            </div>
        </div>

        {{-- 3. MATRIKS STANDAR KEWENANGAN PENANDATANGANAN DOKUMEN --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>📋</span> Matriks Penerapan Kewenangan Penandatanganan Dokumen di SIKAP
                    </h3>
                    <p class="text-xs text-slate-500">Standar naskah dinas dan delegasi mandat yang telah disesuaikan pada sistem SIKAP FKp UNRI.</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 self-start sm:self-auto">
                    ✓ Telah Disesuaikan 100%
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Jenis Dokumen / Surat</th>
                            <th class="px-4 py-3">Kewenangan Asal</th>
                            <th class="px-4 py-3">Pejabat Penandatangan</th>
                            <th class="px-4 py-3">Rumusan Penandatanganan Resmi</th>
                            <th class="px-4 py-3">Hierarki Paraf Koordinasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        {{-- 1. Surat Keterangan Aktif --}}
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                Surat Keterangan Aktif Bekerja
                            </td>
                            <td class="px-4 py-3">Dekan (Atribusi)</td>
                            <td class="px-4 py-3">Dekan Fakultas Keperawatan</td>
                            <td class="px-4 py-3 font-mono text-[11px] bg-slate-50">
                                <strong>Dekan Fakultas Keperawatan,</strong><br>
                                {{ $pejabatDekan->nama_lengkap ?? 'Prof. Dr. Wan Nishfa Dewi, S.Kp., M.Ng.' }}
                            </td>
                            <td class="px-4 py-3">
                                1. Ka Pokja Keu-Kepeg<br>
                                2. Kepala Bagian Umum
                            </td>
                        </tr>

                        {{-- 2. Surat Pemberitahuan KGB --}}
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                Surat Pemberitahuan KGB (Kenaikan Gaji Berkala)
                            </td>
                            <td class="px-4 py-3">Rektor $\rightarrow$ Didelegasikan ke Dekan</td>
                            <td class="px-4 py-3">Wakil Dekan Bidang Keuangan dan Umum</td>
                            <td class="px-4 py-3 font-mono text-[11px] bg-slate-50">
                                <span class="text-blue-700 font-bold">a.n. Dekan Fakultas Keperawatan</span><br>
                                <strong>Wakil Dekan Bidang Keuangan dan Umum,</strong><br>
                                {{ $pejabatWd2->nama_lengkap ?? 'Dr. Safri, M.Kep., Sp.Kep.M.B' }}
                            </td>
                            <td class="px-4 py-3">
                                1. Ka Pokja Keu-Kepeg<br>
                                2. Kepala Bagian Umum
                            </td>
                        </tr>

                        {{-- 3. Surat Usulan KP --}}
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                Surat Usulan Kenaikan Pangkat (ke Rektor)
                            </td>
                            <td class="px-4 py-3">Dekan (Naskah Keluar ke Atasan)</td>
                            <td class="px-4 py-3">Wakil Dekan Bidang Keuangan dan Umum</td>
                            <td class="px-4 py-3 font-mono text-[11px] bg-slate-50">
                                <span class="text-blue-700 font-bold">a.n. Dekan Fakultas Keperawatan</span><br>
                                <strong>Wakil Dekan Bidang Keuangan dan Umum,</strong><br>
                                {{ $pejabatWd2->nama_lengkap ?? 'Dr. Safri, M.Kep., Sp.Kep.M.B' }}
                            </td>
                            <td class="px-4 py-3">
                                1. Ka Pokja Keu-Kepeg<br>
                                2. Kepala Bagian Umum
                            </td>
                        </tr>

                        {{-- 4. Formulir Cuti PNS --}}
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                Formulir Permintaan & Pemberian Cuti PNS
                            </td>
                            <td class="px-4 py-3">Rektor $\rightarrow$ Pimpinan Unit Kerja (PyBMC)</td>
                            <td class="px-4 py-3">
                                Kolom VI: Atasan Langsung<br>
                                Kolom VII: PyBMC (Wadek II / Dekan)
                            </td>
                            <td class="px-4 py-3 font-mono text-[11px] bg-slate-50">
                                Kolom VII: <span class="text-blue-700 font-bold">a.n. Dekan Fakultas Keperawatan</span><br>
                                <strong>Wakil Dekan Bidang Keuangan dan Umum</strong>
                            </td>
                            <td class="px-4 py-3">
                                Kolom VI: Pertimbangan Atasan Langsung masing-masing
                            </td>
                        </tr>

                        {{-- 5. Satyalancana --}}
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                Surat Pengantar Usulan Satyalancana Karya Satya
                            </td>
                            <td class="px-4 py-3">Dekan (Pengusul Kolektif ke Rektor)</td>
                            <td class="px-4 py-3">Wakil Dekan Bidang Keuangan dan Umum</td>
                            <td class="px-4 py-3 font-mono text-[11px] bg-slate-50">
                                <span class="text-blue-700 font-bold">a.n. Dekan Fakultas Keperawatan</span><br>
                                <strong>Wakil Dekan Bidang Keuangan dan Umum,</strong><br>
                                {{ $pejabatWd2->nama_lengkap ?? 'Dr. Safri, M.Kep., Sp.Kep.M.B' }}
                            </td>
                            <td class="px-4 py-3">
                                1. Ka Pokja Keu-Kepeg<br>
                                2. Kepala Bagian Umum
                            </td>
                        </tr>

                        {{-- 6. Dokumen Anjab & ABK --}}
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                Dokumen Analisis Jabatan & ABK
                            </td>
                            <td class="px-4 py-3">Pimpinan Unit Kerja / Instansi</td>
                            <td class="px-4 py-3">Dekan Fakultas Keperawatan & Tim Penyusun</td>
                            <td class="px-4 py-3 font-mono text-[11px] bg-slate-50">
                                Mengetahui / Mengesahkan:<br>
                                <strong>Dekan Fakultas Keperawatan</strong>
                            </td>
                            <td class="px-4 py-3">
                                Ka Pokja Keuangan & Kepegawaian (Penyusun)
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 4. STANDAR OPERASIONAL PROSEDUR (SOP) PARAF DIGITAL KOORDINASI TATA NASKAH --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
            <h3 class="text-base font-bold text-slate-800 mb-2 flex items-center gap-2">
                <span>🔄</span> Standar Operasional Prosedur (SOP) Alur Pemarafan Koordinasi Naskah
            </h3>
            <p class="text-xs text-slate-500 mb-6">
                Mekanisme koordinasi tata naskah berjenjang sebelum suatu dokumen dinas kepegawaian disahkan dan ditandatangani.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Langkah 1 --}}
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 relative">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">Langkah 1</span>
                        <span class="text-xs text-slate-400 font-mono">Pemeriksaan Teknis</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 mb-1">Ka Pokja Keu-Kepeg</h4>
                    <p class="text-xs text-slate-600 mb-2">
                        Memeriksa kebenaran berkas materiil, kelengkapan syarat administratif kepegawaian (SKP, PAK, masa kerja, dll).
                    </p>
                    <div class="text-[11px] text-emerald-700 font-semibold bg-emerald-50 p-2 rounded-lg border border-emerald-200 flex items-center gap-1.5">
                        <span>✓</span> Paraf Digital Tingkat 1 Terekam Sistem
                    </div>
                </div>

                {{-- Langkah 2 --}}
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 relative">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-100 px-2 py-0.5 rounded-full">Langkah 2</span>
                        <span class="text-xs text-slate-400 font-mono">Telaah Tata Naskah</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 mb-1">Kepala Bagian Umum</h4>
                    <p class="text-xs text-slate-600 mb-2">
                        Menelaah tata naskah dinas, nomor surat, format kop instansi, kepatuhan bahasa dinas, dan kewenangan penandatangan.
                    </p>
                    <div class="text-[11px] text-emerald-700 font-semibold bg-emerald-50 p-2 rounded-lg border border-emerald-200 flex items-center gap-1.5">
                        <span>✓</span> Paraf Digital Tingkat 2 Terekam Sistem
                    </div>
                </div>

                {{-- Langkah 3 --}}
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 relative">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">Langkah 3</span>
                        <span class="text-xs text-slate-400 font-mono">Pengesahan / TTE</span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 mb-1">Dekan / Wakil Dekan II</h4>
                    <p class="text-xs text-slate-600 mb-2">
                        Melakukan penandatanganan dinas resmi (TTE atau fisik basah) berdasarkan paraf koordinasi yang telah diverifikasi lengkap.
                    </p>
                    <div class="text-[11px] text-blue-700 font-semibold bg-blue-50 p-2 rounded-lg border border-blue-200 flex items-center gap-1.5">
                        <span>★</span> Naskah Dinas Sah & QR Otentikasi Aktif
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. EDUKASI PRAKTIS UNTUK PEJABAT & PENGGUNA --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Edukasi Pejabat --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-3">
                    <span class="p-2 bg-amber-100 text-amber-800 rounded-lg text-sm">💡</span>
                    <h4 class="text-sm font-bold text-slate-800">Edukasi Bagi Pejabat Penandatangan & Pemeriksa Paraf</h4>
                </div>
                <ul class="text-xs text-slate-600 space-y-2.5 list-disc list-inside">
                    <li>
                        <strong>Perlindungan Yuridis Formula `a.n.`:</strong> Penggunaan formula `a.n. Dekan` oleh Wakil Dekan menegaskan bahwa tindakan administrasi dilaksanakan atas dasar penugasan sah pimpinan instansi dan bukan tindakan sepihak (*ultra vires*).
                    </li>
                    <li>
                        <strong>Tanggung Jawab Paraf Digital:</strong> Pemarafan digital bukanlah formalitas, melainkan bukti otentik pertanggungjawaban verifikasi berkas oleh Ka Pokja dan Kabag Umum sebelum dokumen diterbitkan.
                    </li>
                    <li>
                        <strong>Jejak Audit Tersertifikasi:</strong> Setiap penandatanganan dan paraf mencatat ID pengguna, alamat IP, waktu server (*timestamp*), dan token kriptografis unik yang tidak dapat diubah atau disangkal.
                    </li>
                </ul>
            </div>

            {{-- Edukasi Pegawai Pemohon --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-3">
                    <span class="p-2 bg-blue-100 text-blue-800 rounded-lg text-sm">📱</span>
                    <h4 class="text-sm font-bold text-slate-800">Edukasi Bagi Pegawai Pemohon (Dosen & Tendik)</h4>
                </div>
                <ul class="text-xs text-slate-600 space-y-2.5 list-disc list-inside">
                    <li>
                        <strong>Verifikasi Mandiri Secara Daring:</strong> Dokumen yang diunduh dapat langsung diverifikasi keasliannya oleh instansi luar (perbankan, kedutaan, BKN) dengan memindai QR Code pada kotak otentikasi.
                    </li>
                    <li>
                        <strong>Keabsahan Salinan Elektronik:</strong> Sesuai UU ITE Pasal 5, dokumen PDF ber-QR Code resmi SIKAP memiliki kekuatan pembuktian yang sama dengan dokumen bertanda tangan basah dan stempel fisik.
                    </li>
                    <li>
                        <strong>Pemeriksaan Format Nomor Surat:</strong> Pastikan nomor surat yang tercantum pada dokumen telah sesuai dengan penomoran institusi resmi Fakultas Keperawatan Universitas Riau.
                    </li>
                </ul>
            </div>
        </div>

    </div>
</x-app-layout>
