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
                        
                        {{-- 1. DATA PRIBADI --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">1. Data Pribadi</h4>
                            <table class="w-full text-sm text-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500 w-44">NIP</td>
                                        <td class="py-1.5 text-gray-900 font-mono">{{ $pegawai->nip ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">KARPEG / KARIS / KARSU</td>
                                        <td class="py-1.5 text-gray-900 font-mono">{{ $pegawai->karpeg_karis_karsu ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">NIDN / NUPTK</td>
                                        <td class="py-1.5 text-gray-900 font-mono">{{ $pegawai->nidn_nuptk ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Jenis Kelamin</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->jenis_kelamin == 'L' ? 'Laki-laki' : ($pegawai->jenis_kelamin == 'P' ? 'Perempuan' : ($pegawai->jenis_kelamin ?? '-')) }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Tempat, Tanggal Lahir</td>
                                        <td class="py-1.5 text-gray-900">
                                            {{ $pegawai->tempat_lahir ?? '-' }}, 
                                            {{ $pegawai->tanggal_lahir ? (is_string($pegawai->tanggal_lahir) ? \Carbon\Carbon::parse($pegawai->tanggal_lahir)->translatedFormat('d F Y') : $pegawai->tanggal_lahir->translatedFormat('d F Y')) : '-' }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Agama</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->agama ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- 2. INFORMASI KONTAK --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">2. Informasi Kontak</h4>
                            <table class="w-full text-sm text-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500 w-44">Email</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->email ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Nomor HP</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->no_hp ?? $pegawai->telepon ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Alamat</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->alamat ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- 3. DATA KELUARGA --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">3. Data Keluarga</h4>
                            <table class="w-full text-sm text-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500 w-44">Status Pernikahan</td>
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

                        {{-- 4. DATA KEPEGAWAIAN --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">4. Data Kepegawaian</h4>
                            <table class="w-full text-sm text-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500 w-44">Unit Kerja</td>
                                        <td class="py-1.5 text-gray-900 font-semibold">{{ $pegawai->unitKerja->nama_unit ?? $pegawai->unitKerja->nama_unit_kerja ?? $pegawai->unit_kerja ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Jabatan</td>
                                        <td class="py-1.5 text-gray-900 font-semibold">{{ $pegawai->jabatan->nama_jabatan ?? $pegawai->jabatan->nama ?? $pegawai->jabatan ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Golongan</td>
                                        <td class="py-1.5 text-gray-900">
                                            {{ $pegawai->golongan->nama_golongan ?? $pegawai->golongan->nama ?? $pegawai->golongan ?? '-' }}
                                            @if(!empty($pegawai->golongan->nama_pangkat))
                                                ({{ $pegawai->golongan->nama_pangkat }})
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Jenis Pegawai / Status ASN</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->jenis_pegawai ?? '-' }} / {{ $pegawai->status_asn ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Pendidikan Terakhir</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->pendidikan_terakhir ?? '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">MKG (Masa Kerja Golongan)</td>
                                        <td class="py-1.5 text-gray-900">
                                            {{ $pegawai->mkg_tahun ?? 0 }} Thn {{ $pegawai->mkg_bulan ?? 0 }} Bln
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- 5. RIWAYAT PENDIDIKAN --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">5. Riwayat Pendidikan</h4>
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
                                <p class="text-sm text-gray-500 italic py-1">Belum ada data riwayat pendidikan.</p>
                            @endif
                        </div>

                        {{-- 6. RIWAYAT DIKLAT / PELATIHAN --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">6. Riwayat Diklat / Pelatihan</h4>
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
                                <p class="text-sm text-gray-500 italic py-1">Belum ada data riwayat diklat.</p>
                            @endif
                        </div>

                        {{-- 7. ADMINISTRASI KEPEGAWAIAN --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider mb-2 border-b pb-1">7. Administrasi Kepegawaian</h4>
                            <table class="w-full text-sm text-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500 w-44">Tanggal Masuk</td>
                                        <td class="py-1.5 text-gray-900">
                                            {{ $pegawai->tanggal_masuk ? (is_string($pegawai->tanggal_masuk) ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->translatedFormat('d F Y') : $pegawai->tanggal_masuk->translatedFormat('d F Y')) : '-' }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">TMT SK Pertama</td>
                                        <td class="py-1.5 text-gray-900">
                                            {{ $pegawai->tmt_sk_pertama ? (is_string($pegawai->tmt_sk_pertama) ? \Carbon\Carbon::parse($pegawai->tmt_sk_pertama)->translatedFormat('d F Y') : $pegawai->tmt_sk_pertama->translatedFormat('d F Y')) : '-' }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">TMT Pangkat Terakhir</td>
                                        <td class="py-1.5 text-gray-900">
                                            {{ $pegawai->tmt_pangkat_terakhir ? (is_string($pegawai->tmt_pangkat_terakhir) ? \Carbon\Carbon::parse($pegawai->tmt_pangkat_terakhir)->translatedFormat('d F Y') : $pegawai->tmt_pangkat_terakhir->translatedFormat('d F Y')) : '-' }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">TMT KGB Terakhir</td>
                                        <td class="py-1.5 text-gray-900">
                                            {{ $pegawai->tmt_kgb_terakhir ? (is_string($pegawai->tmt_kgb_terakhir) ? \Carbon\Carbon::parse($pegawai->tmt_kgb_terakhir)->translatedFormat('d F Y') : $pegawai->tmt_kgb_terakhir->translatedFormat('d F Y')) : '-' }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1.5 font-medium text-gray-500">Status Pegawai</td>
                                        <td class="py-1.5 text-gray-900">{{ $pegawai->status_pegawai ?? 'Aktif' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- 8. LEGALITAS PROFESI (STR & SIP / SIKP) --}}
                        <div>
                            <div class="flex items-center justify-between border-b pb-1 mb-2">
                                <h4 class="text-xs font-bold uppercase text-blue-600 tracking-wider">8. Legalitas Profesi (STR & SIP)</h4>
                                @if(Auth::user()->hasRole('admin'))
                                    <a href="{{ route('riwayat-str-sip.create', ['pegawai_id' => $pegawai->id]) }}"
                                       class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                        + Tambah STR/SIP
                                    </a>
                                @endif
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

                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>