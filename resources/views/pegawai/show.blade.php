<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Lengkap Profil Pegawai') }}
            </h2>
            <div class="flex items-center gap-2">
                {{-- Tombol Kembali (Hanya Tampil untuk Admin & Pimpinan) --}}
                @if(Auth::user()->hasRole(['admin', 'pimpinan']))
                    <a href="{{ route('pegawai.index') }}"
                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                        ← Kembali
                    </a>
                @endif

                {{-- Tombol Edit Data (Tampil jika Admin ATAU Pegawai pemilik akun) --}}
                @can('update', $pegawai)
                    <a href="{{ route('pegawai.edit', $pegawai->id) }}"
                       class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 transition">
                        ✏️ Edit Data
                    </a>
                @endcan

                <a href="{{ route('pegawai.download-pdf', $pegawai->id) }}" target="_blank"
                   class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    {{-- Side Profil (Foto & Ringkasan Utama) --}}
                    <div class="flex flex-col items-center md:border-r border-gray-100 pb-6 md:pb-0 md:pr-6">
                        <img src="{{ $pegawai->foto_url ?? asset('images/default-avatar.png') }}" 
                             alt="Foto Profil"
                             class="w-full max-w-[200px] aspect-[3/4] object-cover rounded-lg shadow-md border border-gray-200">
                        
                        <div class="mt-4 text-center w-full">
                            <h3 class="text-lg font-bold text-gray-900 leading-snug">
                                {{ $pegawai->nama_lengkap }}
                            </h3>
                            <p class="text-sm text-gray-500 font-mono mt-1">NIP: {{ $pegawai->nip ?? '-' }}</p>

                            <div class="mt-3 flex items-center justify-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($pegawai->status_pegawai ?? 'Aktif') == 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $pegawai->status_pegawai ?? 'Aktif' }}
                                </span>
                                @if(!empty($pegawai->jenis_pegawai))
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        {{ $pegawai->jenis_pegawai }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Data Detail Terstruktur --}}
                    <div class="md:col-span-2 space-y-6">

                        {{-- ========================================================================= --}}
                        {{-- 1. DATA PRIBADI                                                           --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">1. Data Pribadi</h4>
                            <table class="w-full text-sm text-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500 w-48">NIP / NIK</td>
                                        <td class="py-1.5 text-gray-900 font-mono">{{ $pegawai->nip ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">KARPEG / KARIS / KARSU</td>
                                        <td class="py-1.5 text-gray-900 font-mono">
                                            {{ $pegawai->karpeg_karis_karsu ?? '-' }}
                                            @if($pegawai->file_karpeg)
                                                <a href="{{ route('document.preview', ['path' => $pegawai->file_karpeg]) }}" target="_blank" class="ml-2 inline-flex items-center text-xs text-blue-600 hover:underline font-sans font-semibold">
                                                    📄 Lihat Berkas KARPEG
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">NIDN / NUPTK</td>
                                        <td class="py-1.5 text-gray-900 font-mono">{{ $pegawai->nidn_nuptk ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Nama Lengkap</td>
                                        <td class="py-1.5 text-gray-900 font-semibold">{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</td>
                                    </tr>
                                    @if($pegawai->gelar_depan || $pegawai->gelar_belakang)
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Gelar Depan / Belakang</td>
                                        <td class="py-1.5 text-gray-900">
                                            {{ $pegawai->gelar_depan ?? '-' }} / {{ $pegawai->gelar_belakang ?? '-' }}
                                        </td>
                                    </tr>
                                    @endif
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Tempat, Tanggal Lahir</td>
                                        <td class="py-1.5 text-gray-900">
                                            {{ $pegawai->tempat_lahir ?? '-' }}, 
                                            {{ $pegawai->tanggal_lahir ? (is_string($pegawai->tanggal_lahir) ? \Carbon\Carbon::parse($pegawai->tanggal_lahir)->translatedFormat('d F Y') : $pegawai->tanggal_lahir->translatedFormat('d F Y')) : '-' }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Jenis Kelamin</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->jenis_kelamin == 'L' ? 'Laki-laki' : ($pegawai->jenis_kelamin == 'P' ? 'Perempuan' : ($pegawai->jenis_kelamin ?? '-')) }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Agama</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->agama ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 2. INFORMASI KONTAK & DOMISILI                                            --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">2. Informasi Kontak & Domisili</h4>
                            <table class="w-full text-sm text-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500 w-48">Email</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->email ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Nomor HP / WhatsApp</td>
                                        <td class="py-1.5 text-gray-900 font-mono">{{ $pegawai->no_hp ?? $pegawai->telepon ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Nama Kontak Darurat</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->nama_kontak_darurat ?? $pegawai->kontak_darurat_nama ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Hubungan Kontak Darurat</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->hubungan_kontak_darurat ?? $pegawai->kontak_darurat_hubungan ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Nomor HP Darurat</td>
                                        <td class="py-1.5 text-gray-900 font-mono">{{ $pegawai->no_hp_darurat ?? $pegawai->kontak_darurat_hp ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Alamat Sesuai KTP / Asal</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->alamat ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Alamat Domisili Saat Ini</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->alamat_domisili ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Kota / Kabupaten Domisili</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->kota_domisili ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Provinsi Domisili</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->provinsi ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Kode Pos</td>
                                        <td class="py-1.5 text-gray-900 font-mono">{{ $pegawai->kode_pos ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 3. DATA KELUARGA                                                          --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">3. Data Keluarga</h4>
                            <table class="w-full text-sm text-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500 w-48">Status Pernikahan</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->status_pernikahan ?? $pegawai->status_kawin ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Nama Pasangan</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->nama_pasangan ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Jumlah Anak</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->jumlah_anak ?? 0 }} Orang</td>
                                    </tr>                                          
                                </tbody>
                            </table>
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 4. DATA KEPEGAWAIAN & JABATAN                                             --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">4. Data Kepegawaian & Jabatan</h4>
                            <table class="w-full text-sm text-gray-600">
                                 <tbody>
                                     <tr class="border-b border-gray-50">
                                         <td class="py-1.5 font-medium text-gray-500 w-48">Unit Kerja</td>
                                         <td class="py-1.5 text-gray-900 font-semibold">{{ $pegawai->unitKerja->nama_unit ?? $pegawai->unitKerja->nama_unit_kerja ?? $pegawai->unit_kerja ?? '-' }}</td>
                                     </tr>
                                     <tr class="border-b border-gray-50">
                                         <td class="py-1.5 font-medium text-gray-500">Jabatan</td>
                                         <td class="py-1.5 text-gray-900 font-semibold">
                                             {{ $pegawai->jabatan->nama_jabatan ?? $pegawai->jabatan->nama ?? $pegawai->jabatan ?? '-' }}
                                         </td>
                                     </tr>
                                     <tr class="border-b border-gray-50">
                                         <td class="py-1.5 font-medium text-gray-500">Golongan / Pangkat</td>
                                         <td class="py-1.5 text-gray-900">
                                             {{ $pegawai->golongan->nama_golongan ?? $pegawai->golongan->nama ?? $pegawai->golongan ?? '-' }}
                                             @if(!empty($pegawai->golongan->nama_pangkat))
                                                 ({{ $pegawai->golongan->nama_pangkat }})
                                             @endif
                                         </td>
                                     </tr>
                                     <tr class="border-b border-gray-50">
                                         <td class="py-1.5 font-medium text-gray-500">Jenis Jabatan</td>
                                         <td class="py-1.5 text-gray-900">
                                             @if($pegawai->jenis_jabatan)
                                                 <span class="inline-flex items-center text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
                                                     {{ $pegawai->jenis_jabatan }}
                                                 </span>
                                             @else
                                                 -
                                             @endif
                                         </td>
                                     </tr>
                                     <tr class="border-b border-gray-50">
                                         <td class="py-1.5 font-medium text-gray-500">Penilaian Angka Kredit (PAK)</td>
                                         <td class="py-1.5 text-gray-900">
                                             <span class="font-mono font-semibold">{{ number_format($pegawai->angka_kredit ?? 0, 2, ',', '.') }}</span>
                                             @if($pegawai->nomor_pak)
                                                 <span class="text-xs text-slate-500 ml-2 font-mono">(No: {{ $pegawai->nomor_pak }})</span>
                                             @endif
                                             @if($pegawai->file_pak)
                                                 <a href="{{ route('document.preview', ['path' => $pegawai->file_pak]) }}" target="_blank" class="ml-2 inline-flex items-center text-xs text-emerald-600 hover:underline font-sans font-semibold">
                                                     📄 Lihat Berkas PAK
                                                 </a>
                                             @endif
                                         </td>
                                     </tr>
                                     <tr class="border-b border-gray-50">
                                         <td class="py-1.5 font-medium text-gray-500">Jenis Pegawai</td>
                                         <td class="py-1.5 text-gray-900 font-semibold">{{ $pegawai->jenis_pegawai ?? '-' }}</td>
                                     </tr>
                                     <tr class="border-b border-gray-50">
                                         <td class="py-1.5 font-medium text-gray-500">Status ASN</td>
                                         <td class="py-1.5 text-gray-900">{{ $pegawai->status_asn ?? '-' }}</td>
                                     </tr>
                                     <tr class="border-b border-gray-50">
                                         <td class="py-1.5 font-medium text-gray-500">Pendidikan Terakhir</td>
                                         <td class="py-1.5 text-gray-900">{{ $pegawai->pendidikan_terakhir ?? '-' }}</td>
                                     </tr>
                                     <tr class="border-b border-gray-50">
                                         <td class="py-1.5 font-medium text-gray-500">MKG (Masa Kerja Golongan) Tahun</td>
                                         <td class="py-1.5 text-gray-900 font-mono">{{ $pegawai->mkg_tahun ?? 0 }} Tahun</td>
                                     </tr>
                                     <tr class="border-b border-gray-50">
                                         <td class="py-1.5 font-medium text-gray-500">MKG (Masa Kerja Golongan) Bulan</td>
                                         <td class="py-1.5 text-gray-900 font-mono">{{ $pegawai->mkg_bulan ?? 0 }} Bulan</td>
                                     </tr>
                                 </tbody>
                            </table>
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 5. ADMINISTRASI KEPEGAWAIAN & LEGALITAS                                   --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">5. Administrasi Kepegawaian & Legalitas</h4>
                            <table class="w-full text-sm text-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500 w-48">Tanggal Masuk / TMT Awal</td>
                                        <td class="py-1.5 text-gray-900">
                                            {{ $pegawai->tanggal_masuk ? (is_string($pegawai->tanggal_masuk) ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->translatedFormat('d F Y') : $pegawai->tanggal_masuk->translatedFormat('d F Y')) : '-' }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Status Pegawai</td>
                                        <td class="py-1.5 text-gray-900">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ ($pegawai->status_pegawai ?? 'Aktif') === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $pegawai->status_pegawai ?? 'Aktif' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">TMT SK Pertama</td>
                                        <td class="py-1.5 text-gray-900">
                                            <div>{{ $pegawai->tmt_sk_pertama ? (is_string($pegawai->tmt_sk_pertama) ? \Carbon\Carbon::parse($pegawai->tmt_sk_pertama)->translatedFormat('d F Y') : $pegawai->tmt_sk_pertama->translatedFormat('d F Y')) : '-' }}</div>
                                            @if($pegawai->nomor_sk_pertama || $pegawai->tanggal_sk_pertama || $pegawai->file_sk_pertama)
                                                <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-2 flex-wrap">
                                                    @if($pegawai->nomor_sk_pertama)
                                                        <span>No. SK: <strong class="text-gray-700 font-mono">{{ $pegawai->nomor_sk_pertama }}</strong></span>
                                                    @endif
                                                    @if($pegawai->tanggal_sk_pertama)
                                                        <span>(Tgl: {{ is_string($pegawai->tanggal_sk_pertama) ? \Carbon\Carbon::parse($pegawai->tanggal_sk_pertama)->translatedFormat('d/m/Y') : $pegawai->tanggal_sk_pertama->translatedFormat('d/m/Y') }})</span>
                                                    @endif
                                                    @if($pegawai->file_sk_pertama)
                                                        <a href="{{ route('document.preview', ['path' => $pegawai->file_sk_pertama]) }}" target="_blank" class="text-blue-600 hover:underline font-semibold text-[11px]">📄 Berkas SK Pertama</a>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">TMT Pangkat Terakhir</td>
                                        <td class="py-1.5 text-gray-900">
                                            <div>{{ $pegawai->tmt_pangkat_terakhir ? (is_string($pegawai->tmt_pangkat_terakhir) ? \Carbon\Carbon::parse($pegawai->tmt_pangkat_terakhir)->translatedFormat('d F Y') : $pegawai->tmt_pangkat_terakhir->translatedFormat('d F Y')) : '-' }}</div>
                                            @if($pegawai->nomor_sk_pangkat_terakhir || $pegawai->tanggal_sk_pangkat_terakhir || $pegawai->file_sk_pangkat_terakhir)
                                                <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-2 flex-wrap">
                                                    @if($pegawai->nomor_sk_pangkat_terakhir)
                                                        <span>No. SK: <strong class="text-gray-700 font-mono">{{ $pegawai->nomor_sk_pangkat_terakhir }}</strong></span>
                                                    @endif
                                                    @if($pegawai->tanggal_sk_pangkat_terakhir)
                                                        <span>(Tgl: {{ is_string($pegawai->tanggal_sk_pangkat_terakhir) ? \Carbon\Carbon::parse($pegawai->tanggal_sk_pangkat_terakhir)->translatedFormat('d/m/Y') : $pegawai->tanggal_sk_pangkat_terakhir->translatedFormat('d/m/Y') }})</span>
                                                    @endif
                                                    @if($pegawai->file_sk_pangkat_terakhir)
                                                        <a href="{{ route('document.preview', ['path' => $pegawai->file_sk_pangkat_terakhir]) }}" target="_blank" class="text-blue-600 hover:underline font-semibold text-[11px]">📄 Berkas SK Pangkat</a>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">TMT KGB Terakhir</td>
                                        <td class="py-1.5 text-gray-900">
                                            <div class="flex items-center gap-3">
                                                <span>{{ $pegawai->tmt_kgb_terakhir ? (is_string($pegawai->tmt_kgb_terakhir) ? \Carbon\Carbon::parse($pegawai->tmt_kgb_terakhir)->translatedFormat('d F Y') : $pegawai->tmt_kgb_terakhir->translatedFormat('d F Y')) : '-' }}</span>
                                                @if($pegawai->file_sk_kgb_terakhir)
                                                    <a href="{{ route('document.preview', ['path' => $pegawai->file_sk_kgb_terakhir]) }}" target="_blank" class="text-blue-600 hover:underline font-semibold text-[11px]">📄 Berkas SK KGB</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Batas Usia Pensiun (BUP)</td>
                                        <td class="py-1.5 text-gray-900">
                                            {{ $pegawai->batas_usia_pensiun ? $pegawai->batas_usia_pensiun . ' Tahun' : '-' }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Tanggal Pensiun</td>
                                        <td class="py-1.5 text-gray-900 font-semibold text-amber-800">
                                            {{ $pegawai->tanggal_pensiun ? (is_string($pegawai->tanggal_pensiun) ? \Carbon\Carbon::parse($pegawai->tanggal_pensiun)->translatedFormat('d F Y') : $pegawai->tanggal_pensiun->translatedFormat('d F Y')) : '-' }}
                                        </td>
                                    </tr>
                                    @if($pegawai->jenis_pegawai === 'PHL' || $pegawai->jenis_kontrak || $pegawai->tanggal_kontrak_mulai)
                                    <tr class="border-b border-gray-50 bg-amber-50/40">
                                        <td class="py-1.5 font-medium text-amber-800">Jenis Kontrak (PHL)</td>
                                        <td class="py-1.5 text-amber-900 font-semibold">{{ $pegawai->jenis_kontrak ?? 'Kontrak Kerja' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50 bg-amber-50/40">
                                        <td class="py-1.5 font-medium text-amber-800">Masa Kontrak Kerja</td>
                                        <td class="py-1.5 text-amber-900">
                                            {{ $pegawai->tanggal_kontrak_mulai ? (is_string($pegawai->tanggal_kontrak_mulai) ? \Carbon\Carbon::parse($pegawai->tanggal_kontrak_mulai)->format('d/m/Y') : $pegawai->tanggal_kontrak_mulai->format('d/m/Y')) : '?' }} 
                                            s.d. 
                                            {{ $pegawai->tanggal_kontrak_selesai ? (is_string($pegawai->tanggal_kontrak_selesai) ? \Carbon\Carbon::parse($pegawai->tanggal_kontrak_selesai)->format('d/m/Y') : $pegawai->tanggal_kontrak_selesai->format('d/m/Y')) : '?' }}
                                            @if($pegawai->status_kontrak)
                                                <span class="ml-1 text-[11px] font-bold px-2 py-0.5 rounded-full {{ $pegawai->status_kontrak === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $pegawai->status_kontrak }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 6. DATA FISIK & KESEHATAN                                                 --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">6. Data Fisik & Kesehatan</h4>
                            <table class="w-full text-sm text-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500 w-48">Tinggi Badan</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->tinggi_badan ? $pegawai->tinggi_badan . ' cm' : '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Berat Badan</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->berat_badan ? $pegawai->berat_badan . ' kg' : '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Golongan Darah</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->golongan_darah ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Ciri-ciri Fisik / Khas</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->ciri_khas ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 7. RIWAYAT PANGKAT / GOLONGAN                                             --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">7. Riwayat Pangkat / Golongan</h4>
                                @can('update', $pegawai)
                                    <a href="{{ route('riwayat-pangkat.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah Pangkat
                                    </a>
                                @endcan
                            </div>
                            @if($pegawai->riwayatPangkat && $pegawai->riwayatPangkat->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-gray-600 border border-gray-100 rounded">
                                        <thead>
                                            <tr class="bg-gray-50 text-left border-b border-gray-100 text-xs font-semibold text-gray-500">
                                                <th class="py-2 px-3">Golongan / Pangkat</th>
                                                <th class="py-2 px-3 text-center">TMT Pangkat</th>
                                                <th class="py-2 px-3">Nomor SK</th>
                                                <th class="py-2 px-3 text-center">Tanggal SK</th>
                                                <th class="py-2 px-3 text-center">Status</th>
                                                <th class="py-2 px-3 text-center">Berkas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->riwayatPangkat as $rp)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                    <td class="py-1.5 px-3 font-semibold text-gray-800">
                                                        {{ $rp->golongan->nama_golongan ?? '-' }} ({{ $rp->golongan->nama_pangkat ?? '-' }})
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center font-mono text-xs">{{ $rp->tmt ? \Carbon\Carbon::parse($rp->tmt)->format('d/m/Y') : '-' }}</td>
                                                    <td class="py-1.5 px-3 font-mono text-xs">{{ $rp->nomor_sk ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center font-mono text-xs">{{ $rp->tanggal_sk ? \Carbon\Carbon::parse($rp->tanggal_sk)->format('d/m/Y') : '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ strtolower($rp->status ?? '') == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                                            {{ ucfirst($rp->status ?? 'Riwayat') }}
                                                        </span>
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        @if($rp->file_sk)
                                                            <a href="{{ route('document.preview', ['path' => $rp->file_sk]) }}" target="_blank" class="text-blue-600 font-semibold text-xs hover:underline">Lihat SK</a>
                                                        @else
                                                            <span class="text-gray-400 text-xs">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic py-1">Belum ada data riwayat pangkat tercatat.</p>
                            @endif
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 8. RIWAYAT JABATAN                                                        --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">8. Riwayat Jabatan</h4>
                                @can('update', $pegawai)
                                    <a href="{{ route('riwayat-jabatan.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah Jabatan
                                    </a>
                                @endcan
                            </div>
                            @if($pegawai->riwayatJabatan && $pegawai->riwayatJabatan->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-gray-600 border border-gray-100 rounded">
                                        <thead>
                                            <tr class="bg-gray-50 text-left border-b border-gray-100 text-xs font-semibold text-gray-500">
                                                <th class="py-2 px-3">Nama Jabatan</th>
                                                <th class="py-2 px-3">Unit Kerja</th>
                                                <th class="py-2 px-3 text-center">TMT Jabatan</th>
                                                <th class="py-2 px-3">Nomor SK</th>
                                                <th class="py-2 px-3 text-center">Status</th>
                                                <th class="py-2 px-3 text-center">Berkas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->riwayatJabatan as $rj)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                    <td class="py-1.5 px-3 font-semibold text-gray-800">{{ $rj->jabatan->nama_jabatan ?? $rj->nama_jabatan ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-xs">{{ $rj->unitKerja->nama_unit ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center font-mono text-xs">{{ $rj->tmt_jabatan ? \Carbon\Carbon::parse($rj->tmt_jabatan)->format('d/m/Y') : '-' }}</td>
                                                    <td class="py-1.5 px-3 font-mono text-xs">{{ $rj->nomor_sk ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ strtolower($rj->status ?? '') == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                                            {{ ucfirst($rj->status ?? 'Riwayat') }}
                                                        </span>
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        @if($rj->file_sk)
                                                            <a href="{{ route('document.preview', ['path' => $rj->file_sk]) }}" target="_blank" class="text-blue-600 font-semibold text-xs hover:underline">Lihat SK</a>
                                                        @else
                                                            <span class="text-gray-400 text-xs">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic py-1">Belum ada data riwayat jabatan tercatat.</p>
                            @endif
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 9. RIWAYAT PENDIDIKAN                                                     --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">9. Riwayat Pendidikan</h4>
                                @can('update', $pegawai)
                                    <a href="{{ route('riwayat-pendidikan.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah Pendidikan
                                    </a>
                                @endcan
                            </div>
                            @if($pegawai->riwayatPendidikan && $pegawai->riwayatPendidikan->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-gray-600 border border-gray-100 rounded">
                                        <thead>
                                            <tr class="bg-gray-50 text-left border-b border-gray-100 text-xs font-semibold text-gray-500">
                                                <th class="py-2 px-3">Tingkat</th>
                                                <th class="py-2 px-3">Nama Institusi / Sekolah</th>
                                                <th class="py-2 px-3">Jurusan</th>
                                                <th class="py-2 px-3 text-center">Tahun Lulus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->riwayatPendidikan as $pendidikan)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                    <td class="py-1.5 px-3 font-semibold text-gray-800">{{ $pendidikan->tingkat_pendidikan ?? $pendidikan->jenjang ?? '-' }}</td>
                                                    <td class="py-1.5 px-3">{{ $pendidikan->nama_institusi ?? $pendidikan->institusi ?? '-' }}</td>
                                                    <td class="py-1.5 px-3">{{ $pendidikan->jurusan ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center font-mono">{{ $pendidikan->tahun_lulus ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic py-1">Belum ada data riwayat pendidikan tercatat.</p>
                            @endif
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 10. RIWAYAT DIKLAT / PELATIHAN                                            --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">10. Riwayat Diklat / Pelatihan</h4>
                                @can('update', $pegawai)
                                    <a href="{{ route('riwayat-diklat.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah Diklat
                                    </a>
                                @endcan
                            </div>
                            @if($pegawai->riwayatDiklat && $pegawai->riwayatDiklat->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-gray-600 border border-gray-100 rounded">
                                        <thead>
                                            <tr class="bg-gray-50 text-left border-b border-gray-100 text-xs font-semibold text-gray-500">
                                                <th class="py-2 px-3">Nama Diklat</th>
                                                <th class="py-2 px-3">Penyelenggara</th>
                                                <th class="py-2 px-3 text-center">Tahun</th>
                                                <th class="py-2 px-3 text-center">Jumlah Jam</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->riwayatDiklat as $diklat)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                    <td class="py-1.5 px-3 font-semibold text-gray-800">{{ $diklat->nama_diklat ?? '-' }}</td>
                                                    <td class="py-1.5 px-3">{{ $diklat->penyelenggara ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center font-mono">
                                                        {{ $diklat->tahun ?? ($diklat->tanggal_mulai ? \Carbon\Carbon::parse($diklat->tanggal_mulai)->year : '-') }}
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center font-mono">
                                                        {{ $diklat->jumlah_jam ? $diklat->jumlah_jam . ' JP' : '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic py-1">Belum ada data riwayat diklat tercatat.</p>
                            @endif
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 11. LEGALITAS PROFESI (STR & SIP / SIKP)                                  --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">11. Legalitas Profesi (STR & SIP)</h4>
                                @can('update', $pegawai)
                                    <a href="{{ route('riwayat-str-sip.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah STR/SIP
                                    </a>
                                @endcan
                            </div>
                            @if($pegawai->riwayatStrSip && $pegawai->riwayatStrSip->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-gray-600 border border-gray-100 rounded">
                                        <thead>
                                            <tr class="bg-gray-50 text-left border-b border-gray-100 text-xs font-semibold text-gray-500">
                                                <th class="py-2 px-3">Jenis</th>
                                                <th class="py-2 px-3">Nomor Registrasi</th>
                                                <th class="py-2 px-3">Kualifikasi / Penerbit</th>
                                                <th class="py-2 px-3 text-center">Masa Berlaku</th>
                                                <th class="py-2 px-3 text-center">Status</th>
                                                <th class="py-2 px-3 text-center">Berkas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->riwayatStrSip as $doc)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                    <td class="py-1.5 px-3 font-semibold text-gray-800">{{ $doc->jenis_dokumen }}</td>
                                                    <td class="py-1.5 px-3 font-mono font-medium">{{ $doc->nomor_registrasi }}</td>
                                                    <td class="py-1.5 px-3">
                                                        <div class="text-gray-800">{{ $doc->nama_dokumen ?? '-' }}</div>
                                                        <div class="text-[11px] text-gray-500">{{ $doc->instansi_penerbit ?? '-' }}</div>
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        @if($doc->is_seumur_hidup)
                                                            <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Seumur Hidup</span>
                                                        @else
                                                            <div class="text-xs">{{ $doc->tanggal_berakhir ? $doc->tanggal_berakhir->format('d/m/Y') : '-' }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $doc->is_seumur_hidup ? 'bg-emerald-100 text-emerald-800' : ($doc->sisa_hari !== null && $doc->sisa_hari <= 180 ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800') }}">
                                                            {{ $doc->status_label }}
                                                        </span>
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        @if($doc->file_dokumen_url)
                                                            <a href="{{ $doc->file_dokumen_url }}" target="_blank" class="text-blue-600 hover:underline text-xs font-semibold">
                                                                Lihat
                                                            </a>
                                                        @else
                                                            <span class="text-gray-400 italic text-xs">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic py-1">Belum ada data STR / SIP tercatat.</p>
                            @endif
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 12. PENGARSIPAN SKP (SASARAN KINERJA PEGAWAI - 2 TAHUN)                   --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-3">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">12. Pengarsipan SKP (Sasaran Kinerja Pegawai - 2 Tahun)</h4>
                                @can('update', $pegawai)
                                    <a href="{{ route('riwayat-skp.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah Arsip SKP
                                    </a>
                                @endcan
                            </div>

                            @php
                                $skpN = $pegawai->getSkpTahun(now()->year);
                                $skpN1 = $pegawai->getSkpTahun(now()->year - 1);
                                $skpLainnya = $pegawai->riwayatSkp->filter(fn($s) => !in_array($s->tahun, [now()->year, now()->year - 1]));
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                {{-- KARTU TAHUN INI (N) --}}
                                <div class="rounded-xl border {{ $skpN ? 'border-blue-200 bg-blue-50/30' : 'border-dashed border-gray-300 bg-gray-50/50' }} p-4">
                                    <div class="flex items-center justify-between border-b border-gray-200/70 pb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base font-extrabold text-gray-900">Tahun {{ now()->year }} (N)</span>
                                            <span class="text-[11px] font-semibold text-blue-700 bg-blue-100 px-2 py-0.5 rounded">Tahun Berjalan</span>
                                        </div>
                                        @can('update', $pegawai)
                                            @if($skpN)
                                                <a href="{{ route('riwayat-skp.edit', $skpN->id) }}" class="text-xs text-yellow-700 hover:underline font-semibold">Edit</a>
                                            @else
                                                <a href="{{ route('riwayat-skp.create', ['pegawai_id' => $pegawai->id, 'tahun' => now()->year]) }}" class="text-xs text-blue-600 font-semibold hover:underline">+ Unggah SKP {{ now()->year }}</a>
                                            @endif
                                        @endcan
                                    </div>

                                    @if($skpN)
                                        <div class="mt-3 space-y-2 text-xs">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-500 font-semibold">Predikat Kinerja:</span>
                                                @if($skpN->predikat_kinerja)
                                                    <span class="font-bold px-2 py-0.5 rounded-full border {{ $skpN->predikat_badge_class }}">
                                                        {{ $skpN->predikat_kinerja }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 italic">Sedang Berjalan</span>
                                                @endif
                                            </div>

                                            <div class="flex justify-between items-center pt-1 border-t border-gray-100">
                                                <span class="text-gray-600">1. Rencana SKP:</span>
                                                @if($skpN->file_rencana_skp_url)
                                                    <a href="{{ $skpN->file_rencana_skp_url }}" target="_blank" class="font-bold text-blue-600 hover:underline">📄 Lihat File</a>
                                                @else
                                                    <span class="text-amber-600 italic">Belum diunggah</span>
                                                @endif
                                            </div>

                                            <div class="flex justify-between items-center pt-1 border-t border-gray-100">
                                                <span class="text-gray-600">2. Evaluasi SKP:</span>
                                                @if($skpN->file_evaluasi_skp_url)
                                                    <a href="{{ $skpN->file_evaluasi_skp_url }}" target="_blank" class="font-bold text-emerald-600 hover:underline">📑 Lihat File</a>
                                                @else
                                                    <span class="text-amber-600 italic">Belum diunggah</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="py-4 text-center text-xs text-gray-400 italic">
                                            Belum ada arsip SKP Tahun {{ now()->year }}.
                                        </div>
                                    @endif
                                </div>

                                {{-- KARTU TAHUN SEBELUMNYA (N-1) --}}
                                <div class="rounded-xl border {{ $skpN1 ? 'border-indigo-200 bg-indigo-50/30' : 'border-dashed border-gray-300 bg-gray-50/50' }} p-4">
                                    <div class="flex items-center justify-between border-b border-gray-200/70 pb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base font-extrabold text-gray-900">Tahun {{ now()->year - 1 }} (N-1)</span>
                                            <span class="text-[11px] font-semibold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded">Tahun Sebelumnya</span>
                                        </div>
                                        @can('update', $pegawai)
                                            @if($skpN1)
                                                <a href="{{ route('riwayat-skp.edit', $skpN1->id) }}" class="text-xs text-yellow-700 hover:underline font-semibold">Edit</a>
                                            @else
                                                <a href="{{ route('riwayat-skp.create', ['pegawai_id' => $pegawai->id, 'tahun' => now()->year - 1]) }}" class="text-xs text-indigo-600 font-semibold hover:underline">+ Unggah SKP {{ now()->year - 1 }}</a>
                                            @endif
                                        @endcan
                                    </div>

                                    @if($skpN1)
                                        <div class="mt-3 space-y-2 text-xs">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-500 font-semibold">Predikat Kinerja:</span>
                                                @if($skpN1->predikat_kinerja)
                                                    <span class="font-bold px-2 py-0.5 rounded-full border {{ $skpN1->predikat_badge_class }}">
                                                        {{ $skpN1->predikat_kinerja }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 italic">Belum Ada</span>
                                                @endif
                                            </div>

                                            <div class="flex justify-between items-center pt-1 border-t border-gray-100">
                                                <span class="text-gray-600">1. Rencana SKP:</span>
                                                @if($skpN1->file_rencana_skp_url)
                                                    <a href="{{ $skpN1->file_rencana_skp_url }}" target="_blank" class="font-bold text-blue-600 hover:underline">📄 Lihat File</a>
                                                @else
                                                    <span class="text-amber-600 italic">Belum diunggah</span>
                                                @endif
                                            </div>

                                            <div class="flex justify-between items-center pt-1 border-t border-gray-100">
                                                <span class="text-gray-600">2. Evaluasi SKP:</span>
                                                @if($skpN1->file_evaluasi_skp_url)
                                                    <a href="{{ $skpN1->file_evaluasi_skp_url }}" target="_blank" class="font-bold text-emerald-600 hover:underline">📑 Lihat File</a>
                                                @else
                                                    <span class="text-amber-600 italic">Belum diunggah</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="py-4 text-center text-xs text-gray-400 italic">
                                            Belum ada arsip SKP Tahun {{ now()->year - 1 }}.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($skpLainnya->count() > 0)
                                <div class="mt-3">
                                    <div class="text-[11px] font-bold uppercase text-gray-500 mb-1.5">Arsip SKP Tahun Terdahulu:</div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs text-gray-600 border border-gray-100 rounded">
                                            <thead>
                                                <tr class="bg-gray-50 text-left border-b border-gray-100 font-semibold text-gray-500">
                                                    <th class="py-1.5 px-3 text-center">Tahun</th>
                                                    <th class="py-1.5 px-3 text-center">Predikat</th>
                                                    <th class="py-1.5 px-3">Pejabat Penilai</th>
                                                    <th class="py-1.5 px-3 text-center">Rencana SKP</th>
                                                    <th class="py-1.5 px-3 text-center">Evaluasi SKP</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($skpLainnya as $skpOld)
                                                    <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                        <td class="py-1.5 px-3 text-center font-bold font-mono">{{ $skpOld->tahun }}</td>
                                                        <td class="py-1.5 px-3 text-center">
                                                            <span class="px-2 py-0.5 rounded-full {{ $skpOld->predikat_badge_class }}">
                                                                {{ $skpOld->predikat_kinerja ?: '-' }}
                                                            </span>
                                                        </td>
                                                        <td class="py-1.5 px-3">{{ $skpOld->pejabat_penilai ?: '-' }}</td>
                                                        <td class="py-1.5 px-3 text-center">
                                                            @if($skpOld->file_rencana_skp_url)
                                                                <a href="{{ $skpOld->file_rencana_skp_url }}" target="_blank" class="text-blue-600 hover:underline font-semibold">📄 File</a>
                                                            @else
                                                                <span class="text-gray-400">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="py-1.5 px-3 text-center">
                                                            @if($skpOld->file_evaluasi_skp_url)
                                                                <a href="{{ $skpOld->file_evaluasi_skp_url }}" target="_blank" class="text-emerald-600 hover:underline font-semibold">📑 File</a>
                                                            @else
                                                                <span class="text-gray-400">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 13. RIWAYAT PENGHARGAAN & TANDA JASA                                      --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">13. Riwayat Penghargaan & Tanda Jasa</h4>
                                @can('update', $pegawai)
                                    <a href="{{ route('riwayat-penghargaan.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah Penghargaan
                                    </a>
                                @endcan
                            </div>
                            @if($pegawai->riwayatPenghargaan && $pegawai->riwayatPenghargaan->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-gray-600 border border-gray-100 rounded">
                                        <thead>
                                            <tr class="bg-gray-50 text-left border-b border-gray-100 text-xs font-semibold text-gray-500">
                                                <th class="py-2 px-3">Nama Penghargaan</th>
                                                <th class="py-2 px-3">Jenis</th>
                                                <th class="py-2 px-3">Pemberi</th>
                                                <th class="py-2 px-3 text-center">Tanggal Terima</th>
                                                <th class="py-2 px-3 text-center">Berkas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->riwayatPenghargaan as $p)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                    <td class="py-1.5 px-3 font-semibold text-gray-800">{{ $p->nama_penghargaan }}</td>
                                                    <td class="py-1.5 px-3 text-xs text-gray-600">{{ $p->jenis_penghargaan ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-xs text-gray-600">{{ $p->instansi_pemberi ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center font-mono text-xs">{{ $p->tanggal_terima ? $p->tanggal_terima->format('d/m/Y') : '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        @if($p->file_sk)
                                                            <a href="{{ route('document.preview', ['path' => $p->file_sk]) }}" target="_blank" class="text-blue-600 font-semibold text-xs hover:underline">Lihat SK</a>
                                                        @else
                                                            <span class="text-gray-400 text-xs">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic py-1">Belum ada riwayat penghargaan / tanda jasa tercatat.</p>
                            @endif
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 14. RIWAYAT KEANGGOTAAN ORGANISASI                                        --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">14. Riwayat Keanggotaan Organisasi</h4>
                                @can('update', $pegawai)
                                    <a href="{{ route('riwayat-organisasi.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah Organisasi
                                    </a>
                                @endcan
                            </div>
                            @if($pegawai->riwayatOrganisasi && $pegawai->riwayatOrganisasi->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-gray-600 border border-gray-100 rounded">
                                        <thead>
                                            <tr class="bg-gray-50 text-left border-b border-gray-100 text-xs font-semibold text-gray-500">
                                                <th class="py-2 px-3">Nama Organisasi</th>
                                                <th class="py-2 px-3">Jabatan / Peran</th>
                                                <th class="py-2 px-3 text-center">Periode</th>
                                                <th class="py-2 px-3 text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->riwayatOrganisasi as $org)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                    <td class="py-1.5 px-3 font-semibold text-gray-800">{{ $org->nama_organisasi }}</td>
                                                    <td class="py-1.5 px-3 text-xs text-gray-600">{{ $org->jabatan_organisasi ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center text-xs font-mono">{{ $org->tahun_mulai ?? '?' }} - {{ $org->tahun_selesai ?? 'sekarang' }}</td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $org->masih_aktif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                                            {{ $org->masih_aktif ? 'Aktif' : 'Selesai' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic py-1">Belum ada riwayat keanggotaan organisasi tercatat.</p>
                            @endif
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 15. RIWAYAT PUBLIKASI ILMIAH & KARYA                                      --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">15. Riwayat Publikasi Ilmiah & Karya</h4>
                                @can('update', $pegawai)
                                    <a href="{{ route('riwayat-publikasi.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah Publikasi
                                    </a>
                                @endcan
                            </div>
                            @if($pegawai->riwayatPublikasi && $pegawai->riwayatPublikasi->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-gray-600 border border-gray-100 rounded">
                                        <thead>
                                            <tr class="bg-gray-50 text-left border-b border-gray-100 text-xs font-semibold text-gray-500">
                                                <th class="py-2 px-3">Judul Publikasi</th>
                                                <th class="py-2 px-3">Jenis & Indeksasi</th>
                                                <th class="py-2 px-3">Jurnal / Penerbit</th>
                                                <th class="py-2 px-3 text-center">Tahun</th>
                                                <th class="py-2 px-3 text-center">Berkas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->riwayatPublikasi as $pub)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                    <td class="py-1.5 px-3 font-semibold text-gray-800 max-w-xs">
                                                        <div class="line-clamp-2">{{ $pub->judul_publikasi }}</div>
                                                        @if($pub->url_doi)
                                                            <a href="{{ $pub->url_doi }}" target="_blank" class="text-[11px] text-blue-500 hover:underline">Link DOI</a>
                                                        @endif
                                                    </td>
                                                    <td class="py-1.5 px-3 text-xs">
                                                        <span class="font-medium text-gray-800">{{ $pub->jenis_publikasi }}</span>
                                                        @if($pub->indeksasi)
                                                            <span class="block text-[10px] text-indigo-700 font-semibold mt-0.5">{{ $pub->indeksasi }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-1.5 px-3 text-xs text-gray-600">
                                                        {{ $pub->nama_jurnal ?? $pub->penerbit ?? '-' }}
                                                        @if($pub->volume_nomor)
                                                            <span class="block text-[10px] text-gray-400">{{ $pub->volume_nomor }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center font-mono text-xs">{{ $pub->tahun_terbit ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        @if($pub->file_publikasi)
                                                            <a href="{{ route('document.preview', ['path' => $pub->file_publikasi]) }}" target="_blank" class="text-emerald-600 font-semibold text-xs hover:underline">PDF</a>
                                                        @else
                                                            <span class="text-gray-400 text-xs">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic py-1">Belum ada riwayat publikasi ilmiah tercatat.</p>
                            @endif
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 16. TUGAS BELAJAR & IZIN BELAJAR                                          --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">16. Tugas Belajar & Izin Belajar (Studi Lanjut)</h4>
                                @can('update', $pegawai)
                                    <a href="{{ route('tugas-belajar.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah Tubel/Ibel
                                    </a>
                                @endcan
                            </div>
                            @if($pegawai->tugasBelajar && $pegawai->tugasBelajar->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-gray-600 border border-gray-100 rounded">
                                        <thead>
                                            <tr class="bg-gray-50 text-left border-b border-gray-100 text-xs font-semibold text-gray-500">
                                                <th class="py-2 px-3">Jenis & Jenjang</th>
                                                <th class="py-2 px-3">Universitas & Prodi</th>
                                                <th class="py-2 px-3">Beasiswa / SK</th>
                                                <th class="py-2 px-3 text-center">Semester & Masa Studi</th>
                                                <th class="py-2 px-3 text-center">Status</th>
                                                <th class="py-2 px-3 text-center">Berkas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->tugasBelajar as $tb)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                    <td class="py-1.5 px-3 font-semibold text-gray-800">
                                                        <span class="inline-block px-1.5 py-0.5 rounded text-[11px] font-bold {{ $tb->jenis_pengembangan === 'Tugas Belajar' ? 'bg-indigo-100 text-indigo-800' : 'bg-teal-100 text-teal-800' }}">
                                                            {{ $tb->jenis_pengembangan }}
                                                        </span>
                                                        <div class="font-bold text-xs mt-0.5">{{ $tb->jenjang_studi }}</div>
                                                    </td>
                                                    <td class="py-1.5 px-3">
                                                        <div class="text-gray-900 font-semibold text-xs">{{ $tb->program_studi }}</div>
                                                        <div class="text-[11px] text-gray-500">{{ $tb->perguruan_tinggi }} ({{ $tb->negara }})</div>
                                                    </td>
                                                    <td class="py-1.5 px-3">
                                                        <div class="text-xs text-gray-800">{{ $tb->sumber_pembiayaan }}</div>
                                                        <div class="text-[11px] text-gray-500 font-mono">SK: {{ $tb->nomor_sk }}</div>
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        <div class="text-xs font-bold text-gray-800">Semester {{ $tb->semester_berjalan }}</div>
                                                        <div class="text-[11px] text-gray-500">
                                                            {{ $tb->tanggal_mulai ? $tb->tanggal_mulai->format('d/m/Y') : '-' }} s.d. {{ $tb->tanggal_selesai ? $tb->tanggal_selesai->format('d/m/Y') : '-' }}
                                                        </div>
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full border {{ $tb->status_badge_class }}">
                                                            {{ $tb->status_studi }}
                                                        </span>
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        <div class="flex flex-col items-center gap-1">
                                                            @if($tb->file_sk_url)
                                                                <a href="{{ $tb->file_sk_url }}" target="_blank" class="text-indigo-600 hover:underline text-xs font-semibold">
                                                                    SK
                                                                </a>
                                                            @endif
                                                            @if($tb->file_laporan_progress_url)
                                                                <a href="{{ $tb->file_laporan_progress_url }}" target="_blank" class="text-teal-600 hover:underline text-xs font-semibold">
                                                                    KHS
                                                                </a>
                                                            @endif
                                                            @if(!$tb->file_sk_url && !$tb->file_laporan_progress_url)
                                                                <span class="text-gray-400 italic text-xs">-</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic py-1">Belum ada riwayat tugas belajar / izin belajar tercatat.</p>
                            @endif
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 17. RIWAYAT MUTASI PEGAWAI                                                --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">17. Riwayat Mutasi Pegawai</h4>
                                @can('update', $pegawai)
                                    <a href="{{ route('mutasi-pegawai.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah Mutasi
                                    </a>
                                @endcan
                            </div>
                            @if($pegawai->mutasi && $pegawai->mutasi->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-gray-600 border border-gray-100 rounded">
                                        <thead>
                                            <tr class="bg-gray-50 text-left border-b border-gray-100 text-xs font-semibold text-gray-500">
                                                <th class="py-2 px-3">Jenis Mutasi</th>
                                                <th class="py-2 px-3">Asal</th>
                                                <th class="py-2 px-3">Tujuan</th>
                                                <th class="py-2 px-3 text-center">TMT Mutasi</th>
                                                <th class="py-2 px-3 text-center">No. SK</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->mutasi as $m)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                    <td class="py-1.5 px-3 font-semibold text-gray-800">{{ $m->jenis_mutasi ?? 'Mutasi Internal' }}</td>
                                                    <td class="py-1.5 px-3 text-xs">{{ $m->unitKerjaAsal->nama_unit ?? $m->instansi_asal ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-xs font-semibold text-blue-700">{{ $m->unitKerjaTujuan->nama_unit ?? $m->instansi_tujuan ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center font-mono text-xs">{{ $m->tmt_mutasi ? \Carbon\Carbon::parse($m->tmt_mutasi)->format('d/m/Y') : '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center font-mono text-xs">{{ $m->nomor_sk ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic py-1">Belum ada data riwayat mutasi tercatat.</p>
                            @endif
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- 18. RIWAYAT CUTI PEGAWAI                                                  --}}
                        {{-- ========================================================================= --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">18. Riwayat Cuti Pegawai</h4>
                                <a href="{{ route('pengajuan-cuti.create') }}"
                                   class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                    + Ajukan Cuti
                                </a>
                            </div>
                            @if($pegawai->pengajuanCuti && $pegawai->pengajuanCuti->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-gray-600 border border-gray-100 rounded">
                                        <thead>
                                            <tr class="bg-gray-50 text-left border-b border-gray-100 text-xs font-semibold text-gray-500">
                                                <th class="py-2 px-3">Jenis Cuti</th>
                                                <th class="py-2 px-3 text-center">Periode Cuti</th>
                                                <th class="py-2 px-3 text-center">Jumlah Hari</th>
                                                <th class="py-2 px-3">Alasan</th>
                                                <th class="py-2 px-3 text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->pengajuanCuti as $cuti)
                                                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                                    <td class="py-1.5 px-3 font-semibold text-gray-800">{{ $cuti->jenis_cuti }}</td>
                                                    <td class="py-1.5 px-3 text-center text-xs font-mono">
                                                        {{ $cuti->tanggal_mulai ? \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d/m/Y') : '-' }} s.d. {{ $cuti->tanggal_selesai ? \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d/m/Y') : '-' }}
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center font-bold">{{ $cuti->jumlah_hari }} Hari</td>
                                                    <td class="py-1.5 px-3 text-xs text-gray-600">{{ $cuti->alasan ?? '-' }}</td>
                                                    <td class="py-1.5 px-3 text-center">
                                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $cuti->status === 'Disetujui' ? 'bg-green-100 text-green-800' : ($cuti->status === 'Ditolak' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                                            {{ $cuti->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic py-1">Belum ada riwayat cuti pegawai tercatat.</p>
                            @endif
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>