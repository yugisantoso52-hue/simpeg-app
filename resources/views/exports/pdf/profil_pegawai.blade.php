@extends('exports.pdf.master')

@section('title', 'Profil Pegawai - ' . ($pegawai->nama_lengkap ?? $pegawai->nama))

@section('content')

    <div class="document-title">
        <h4>PROFIL PEGAWAI</h4>
        <p>NIP: {{ $pegawai->nip ?? '-' }}</p>
    </div>

    @php
        // Logika pencegahan duplikasi Gelar Depan dan Gelar Belakang
        $namaCore = trim($pegawai->nama_lengkap ?? $pegawai->nama);
        $gelarDepan = trim($pegawai->gelar_depan ?? '');
        $gelarBelakang = trim($pegawai->gelar_belakang ?? '');

        // Tambah gelar depan jika belum ada di dalam string nama
        if ($gelarDepan !== '' && !str_starts_with($namaCore, $gelarDepan)) {
            $namaCore = $gelarDepan . ' ' . $namaCore;
        }

        // Tambah gelar belakang jika belum ada di dalam string nama
        if ($gelarBelakang !== '' && !str_contains($namaCore, $gelarBelakang)) {
            $namaCore = $namaCore . ', ' . $gelarBelakang;
        }
    @endphp

    <!-- 1. DATA PRIBADI -->
    <div class="section-header">1. DATA PRIBADI</div>
    <table class="form-table">
        <tr>
            <td style="width: 32%;" class="bold">NIP</td>
            <td style="width: 3%;">:</td>
            <td>{{ $pegawai->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">KARPEG / KARIS / KARSU</td>
            <td>:</td>
            <td>{{ $pegawai->karpeg_karis_karsu ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">NIDN / NUPTK</td>
            <td>:</td>
            <td>{{ $pegawai->nidn_nuptk ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Nama Lengkap</td>
            <td>:</td>
            <td>{{ $namaCore }}</td>
        </tr>
        <tr>
            <td class="bold">Jenis Kelamin</td>
            <td>:</td>
            <td>{{ $pegawai->jenis_kelamin == 'L' ? 'Laki-laki' : ($pegawai->jenis_kelamin == 'P' ? 'Perempuan' : ($pegawai->jenis_kelamin ?? '-')) }}</td>
        </tr>
        <tr>
            <td class="bold">Tempat, Tanggal Lahir</td>
            <td>:</td>
            <td>
                {{ $pegawai->tempat_lahir ?? '-' }}, 
                {{ $pegawai->tanggal_lahir ? (is_string($pegawai->tanggal_lahir) ? \Carbon\Carbon::parse($pegawai->tanggal_lahir)->translatedFormat('d F Y') : $pegawai->tanggal_lahir->translatedFormat('d F Y')) : '-' }}
            </td>
        </tr>
        <tr>
            <td class="bold">Agama</td>
            <td>:</td>
            <td>{{ $pegawai->agama ?? '-' }}</td>
        </tr>
    </table>

    <!-- 2. INFORMASI KONTAK & DOMISILI -->
    <div class="section-header">2. INFORMASI KONTAK & DOMISILI</div>
    <table class="form-table">
        <tr>
            <td style="width: 32%;" class="bold">Email</td>
            <td style="width: 3%;">:</td>
            <td>{{ $pegawai->email ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Nomor HP / WhatsApp</td>
            <td>:</td>
            <td>{{ $pegawai->no_hp ?? $pegawai->telepon ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Nama Kontak Darurat</td>
            <td>:</td>
            <td>{{ $pegawai->nama_kontak_darurat ?? $pegawai->kontak_darurat_nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Hubungan Kontak Darurat</td>
            <td>:</td>
            <td>{{ $pegawai->hubungan_kontak_darurat ?? $pegawai->kontak_darurat_hubungan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Nomor HP Darurat</td>
            <td>:</td>
            <td>{{ $pegawai->no_hp_darurat ?? $pegawai->kontak_darurat_hp ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Alamat Sesuai KTP / Asal</td>
            <td>:</td>
            <td>{{ $pegawai->alamat ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Alamat Domisili Saat Ini</td>
            <td>:</td>
            <td>{{ $pegawai->alamat_domisili ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Kota / Kabupaten Domisili</td>
            <td>:</td>
            <td>{{ $pegawai->kota_domisili ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Provinsi Domisili</td>
            <td>:</td>
            <td>{{ $pegawai->provinsi ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Kode Pos</td>
            <td>:</td>
            <td>{{ $pegawai->kode_pos ?? '-' }}</td>
        </tr>
    </table>

    <!-- 3. DATA KELUARGA -->
    <div class="section-header">3. DATA KELUARGA</div>
    <table class="form-table">
        <tr>
            <td style="width: 32%;" class="bold">Status Pernikahan</td>
            <td style="width: 3%;">:</td>
            <td>{{ $pegawai->status_pernikahan ?? $pegawai->status_kawin ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Nama Pasangan</td>
            <td>:</td>
            <td>{{ $pegawai->nama_pasangan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Jumlah Anak</td>
            <td>:</td>
            <td>{{ $pegawai->jumlah_anak ?? 0 }} Orang</td>
        </tr>
    </table>

    <!-- 4. DATA KEPEGAWAIAN -->
    <div class="section-header">4. DATA KEPEGAWAIAN</div>
    <table class="form-table">
        <tr>
            <td style="width: 32%;" class="bold">Unit Kerja</td>
            <td style="width: 3%;">:</td>
            <td>{{ $pegawai->unitKerja->nama_unit ?? $pegawai->unitKerja->nama_unit_kerja ?? $pegawai->unit_kerja ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Jabatan</td>
            <td>:</td>
            <td>{{ $pegawai->jabatan->nama_jabatan ?? $pegawai->jabatan->nama ?? $pegawai->jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Golongan / Pangkat</td>
            <td>:</td>
            <td>
                {{ $pegawai->golongan->nama_golongan ?? $pegawai->golongan->nama ?? $pegawai->golongan ?? '-' }}
                @if(!empty($pegawai->golongan->nama_pangkat))
                    ({{ $pegawai->golongan->nama_pangkat }})
                @endif
            </td>
        </tr>
        <tr>
            <td class="bold">Jenis Jabatan</td>
            <td>:</td>
            <td>{{ $pegawai->jenis_jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Angka Kredit Kumulatif (PAK)</td>
            <td>:</td>
            <td>{{ number_format($pegawai->angka_kredit ?? 0, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="bold">Jenis Pegawai</td>
            <td>:</td>
            <td>{{ $pegawai->jenis_pegawai ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Status ASN</td>
            <td>:</td>
            <td>{{ $pegawai->status_asn ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Pendidikan Terakhir</td>
            <td>:</td>
            <td>{{ $pegawai->pendidikan_terakhir ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">MKG (Masa Kerja Golongan) Tahun</td>
            <td>:</td>
            <td>{{ $pegawai->mkg_tahun ?? 0 }} Tahun</td>
        </tr>
        <tr>
            <td class="bold">MKG (Masa Kerja Golongan) Bulan</td>
            <td>:</td>
            <td>{{ $pegawai->mkg_bulan ?? 0 }} Bulan</td>
        </tr>
    </table>

    <!-- 5. RIWAYAT PENDIDIKAN -->
    <div class="section-header">5. RIWAYAT PENDIDIKAN</div>
    @if($pegawai->riwayatPendidikan && $pegawai->riwayatPendidikan->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Tingkat</th>
                    <th style="width: 45%;">Nama Institusi / Sekolah</th>
                    <th style="width: 25%;">Jurusan</th>
                    <th style="width: 15%;">Tahun Lulus</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatPendidikan as $pendidikan)
                    <tr>
                        <td class="bold text-center">{{ $pendidikan->tingkat_pendidikan ?? $pendidikan->jenjang ?? '-' }}</td>
                        <td>{{ $pendidikan->nama_institusi ?? $pendidikan->institusi ?? '-' }}</td>
                        <td>{{ $pendidikan->jurusan ?? '-' }}</td>
                        <td class="text-center">{{ $pendidikan->tahun_lulus ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat pendidikan yang tercatat.</p>
    @endif

    <!-- 6. RIWAYAT DIKLAT / PELATIHAN -->
    <div class="section-header">6. RIWAYAT DIKLAT / PELATIHAN</div>
    @if($pegawai->riwayatDiklat && $pegawai->riwayatDiklat->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Nama Diklat / Pelatihan</th>
                    <th style="width: 30%;">Penyelenggara</th>
                    <th style="width: 12%;">Tahun</th>
                    <th style="width: 13%;">Jumlah Jam</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatDiklat as $diklat)
                    <tr>
                        <td class="bold">{{ $diklat->nama_diklat ?? '-' }}</td>
                        <td>{{ $diklat->penyelenggara ?? '-' }}</td>
                        <td class="text-center">{{ $diklat->tahun ?? ($diklat->tanggal_mulai ? \Carbon\Carbon::parse($diklat->tanggal_mulai)->year : '-') }}</td>
                        <td class="text-center">{{ $diklat->jumlah_jam ? $diklat->jumlah_jam . ' JP' : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat diklat / pelatihan yang tercatat.</p>
    @endif

    <!-- 7. ADMINISTRASI KEPEGAWAIAN -->
    <div class="section-header">7. ADMINISTRASI KEPEGAWAIAN</div>
    <table class="form-table">
        <tr>
            <td style="width: 32%;" class="bold">Tanggal Masuk</td>
            <td style="width: 3%;">:</td>
            <td>{{ $pegawai->tanggal_masuk ? (is_string($pegawai->tanggal_masuk) ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->translatedFormat('d F Y') : $pegawai->tanggal_masuk->translatedFormat('d F Y')) : '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Status Pegawai</td>
            <td>:</td>
            <td>{{ $pegawai->status_pegawai ?? 'Aktif' }}</td>
        </tr>
        <tr>
            <td class="bold">TMT SK Pertama</td>
            <td>:</td>
            <td>
                {{ $pegawai->tmt_sk_pertama ? (is_string($pegawai->tmt_sk_pertama) ? \Carbon\Carbon::parse($pegawai->tmt_sk_pertama)->translatedFormat('d F Y') : $pegawai->tmt_sk_pertama->translatedFormat('d F Y')) : '-' }}
                @if($pegawai->nomor_sk_pertama || $pegawai->tanggal_sk_pertama)
                    <br><span style="font-size: 9pt; color: #333;">(No. SK: {{ $pegawai->nomor_sk_pertama ?? '-' }} {{ $pegawai->tanggal_sk_pertama ? 'tgl ' . (is_string($pegawai->tanggal_sk_pertama) ? \Carbon\Carbon::parse($pegawai->tanggal_sk_pertama)->translatedFormat('d/m/Y') : $pegawai->tanggal_sk_pertama->translatedFormat('d/m/Y')) : '' }})</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="bold">TMT Pangkat Terakhir</td>
            <td>:</td>
            <td>
                {{ $pegawai->tmt_pangkat_terakhir ? (is_string($pegawai->tmt_pangkat_terakhir) ? \Carbon\Carbon::parse($pegawai->tmt_pangkat_terakhir)->translatedFormat('d F Y') : $pegawai->tmt_pangkat_terakhir->translatedFormat('d F Y')) : '-' }}
                @if($pegawai->nomor_sk_pangkat_terakhir || $pegawai->tanggal_sk_pangkat_terakhir)
                    <br><span style="font-size: 9pt; color: #333;">(No. SK: {{ $pegawai->nomor_sk_pangkat_terakhir ?? '-' }} {{ $pegawai->tanggal_sk_pangkat_terakhir ? 'tgl ' . (is_string($pegawai->tanggal_sk_pangkat_terakhir) ? \Carbon\Carbon::parse($pegawai->tanggal_sk_pangkat_terakhir)->translatedFormat('d/m/Y') : $pegawai->tanggal_sk_pangkat_terakhir->translatedFormat('d/m/Y')) : '' }})</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="bold">TMT KGB Terakhir</td>
            <td>:</td>
            <td>{{ $pegawai->tmt_kgb_terakhir ? (is_string($pegawai->tmt_kgb_terakhir) ? \Carbon\Carbon::parse($pegawai->tmt_kgb_terakhir)->translatedFormat('d F Y') : $pegawai->tmt_kgb_terakhir->translatedFormat('d F Y')) : '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Batas Usia Pensiun (BUP)</td>
            <td>:</td>
            <td>{{ $pegawai->batas_usia_pensiun ? $pegawai->batas_usia_pensiun . ' Tahun' : '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Tanggal Pensiun</td>
            <td>:</td>
            <td>{{ $pegawai->tanggal_pensiun ? (is_string($pegawai->tanggal_pensiun) ? \Carbon\Carbon::parse($pegawai->tanggal_pensiun)->translatedFormat('d F Y') : $pegawai->tanggal_pensiun->translatedFormat('d F Y')) : '-' }}</td>
        </tr>
        @if($pegawai->jenis_pegawai === 'PHL' || $pegawai->jenis_kontrak || $pegawai->tanggal_kontrak_mulai)
        <tr>
            <td class="bold">Jenis Kontrak (PHL)</td>
            <td>:</td>
            <td>{{ $pegawai->jenis_kontrak ?? 'Kontrak Kerja' }}</td>
        </tr>
        <tr>
            <td class="bold">Masa Kontrak Kerja</td>
            <td>:</td>
            <td>
                {{ $pegawai->tanggal_kontrak_mulai ? (is_string($pegawai->tanggal_kontrak_mulai) ? \Carbon\Carbon::parse($pegawai->tanggal_kontrak_mulai)->format('d/m/Y') : $pegawai->tanggal_kontrak_mulai->format('d/m/Y')) : '?' }} 
                s.d. 
                {{ $pegawai->tanggal_kontrak_selesai ? (is_string($pegawai->tanggal_kontrak_selesai) ? \Carbon\Carbon::parse($pegawai->tanggal_kontrak_selesai)->format('d/m/Y') : $pegawai->tanggal_kontrak_selesai->format('d/m/Y')) : '?' }}
                @if($pegawai->status_kontrak)
                    - [{{ $pegawai->status_kontrak }}]
                @endif
            </td>
        </tr>
        @endif
    </table>

    <!-- 8. LEGALITAS PROFESI (STR & SIP / SIKP) -->
    <div class="section-header">8. LEGALITAS PROFESI (STR & SIP / SIKP)</div>
    @if($pegawai->riwayatStrSip && $pegawai->riwayatStrSip->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Jenis Dokumen</th>
                    <th style="width: 30%;">Nomor Registrasi</th>
                    <th style="width: 25%;">Kualifikasi / Instansi</th>
                    <th style="width: 15%;">Masa Berlaku</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatStrSip as $str)
                    <tr>
                        <td class="bold">{{ $str->jenis_dokumen }}</td>
                        <td>{{ $str->nomor_registrasi }}</td>
                        <td>{{ $str->nama_dokumen ?? $str->instansi_penerbit ?? '-' }}</td>
                        <td class="text-center">
                            {{ $str->is_seumur_hidup ? 'Seumur Hidup' : ($str->tanggal_berakhir ? (is_string($str->tanggal_berakhir) ? \Carbon\Carbon::parse($str->tanggal_berakhir)->translatedFormat('d/m/Y') : $str->tanggal_berakhir->translatedFormat('d/m/Y')) : '-') }}
                        </td>
                        <td class="text-center">{{ $str->status_label }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat STR / SIP yang tercatat.</p>
    @endif

    <!-- 9. TUGAS BELAJAR & IZIN BELAJAR -->
    <div class="section-header">9. TUGAS BELAJAR & IZIN BELAJAR (STUDI LANJUT)</div>
    @if($pegawai->tugasBelajar && $pegawai->tugasBelajar->count() > 0)
        <table class="form-table">
            @foreach($pegawai->tugasBelajar as $tb)
                <tr>
                    <td style="width: 32%;" class="bold">{{ $tb->jenis_pengembangan }} ({{ $tb->jenjang_studi }})</td>
                    <td style="width: 3%;">:</td>
                    <td>
                        <strong>{{ $tb->program_studi }}</strong> - {{ $tb->perguruan_tinggi }} ({{ $tb->negara }})<br>
                        Beasiswa: {{ $tb->sumber_pembiayaan }} | Semester: {{ $tb->semester_berjalan }} | Status: <strong>{{ $tb->status_studi }}</strong><br>
                        Masa Studi: {{ $tb->tanggal_mulai ? (is_string($tb->tanggal_mulai) ? \Carbon\Carbon::parse($tb->tanggal_mulai)->translatedFormat('d F Y') : $tb->tanggal_mulai->translatedFormat('d F Y')) : '-' }} s.d. {{ $tb->tanggal_selesai ? (is_string($tb->tanggal_selesai) ? \Carbon\Carbon::parse($tb->tanggal_selesai)->translatedFormat('d F Y') : $tb->tanggal_selesai->translatedFormat('d F Y')) : '-' }} (SK: {{ $tb->nomor_sk }})
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <p style="font-size: 10pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat tugas belajar / izin belajar yang tercatat.</p>
    @endif

    <!-- 10. EVALUASI KINERJA (SASARAN KINERJA PEGAWAI) -->
    <div class="section-header">10. PENGARSIPAN SKP (SASARAN KINERJA PEGAWAI)</div>
    @if($pegawai->riwayatSkp && $pegawai->riwayatSkp->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Tahun</th>
                    <th style="width: 25%;">Predikat Kinerja</th>
                    <th style="width: 35%;">Pejabat Penilai</th>
                    <th style="width: 25%;">Kelengkapan Berkas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatSkp as $skp)
                    <tr>
                        <td class="bold text-center">{{ $skp->tahun }}</td>
                        <td class="text-center">{{ $skp->predikat_kinerja ?: 'Sedang Berjalan' }}</td>
                        <td>{{ $skp->pejabat_penilai ?: '-' }}</td>
                        <td class="text-center">{{ $skp->is_lengkap ? 'Lengkap (Rencana & Evaluasi)' : ($skp->file_rencana_skp ? 'Rencana Saja' : ($skp->file_evaluasi_skp ? 'Evaluasi Saja' : 'Belum Ada')) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada pengarsipan SKP yang tercatat.</p>
    @endif

    <!-- 11. RIWAYAT PENGHARGAAN & TANDA JASA -->
    <div class="section-header">11. RIWAYAT PENGHARGAAN & TANDA JASA</div>
    @if($pegawai->riwayatPenghargaan && $pegawai->riwayatPenghargaan->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Nama Penghargaan</th>
                    <th style="width: 25%;">Jenis</th>
                    <th style="width: 25%;">Instansi Pemberi</th>
                    <th style="width: 15%;">Tanggal Terima</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatPenghargaan as $p)
                    <tr>
                        <td class="bold">{{ $p->nama_penghargaan }}</td>
                        <td>{{ $p->jenis_penghargaan ?? '-' }}</td>
                        <td>{{ $p->instansi_pemberi ?? '-' }}</td>
                        <td class="text-center">{{ $p->tanggal_terima ? (is_string($p->tanggal_terima) ? \Carbon\Carbon::parse($p->tanggal_terima)->translatedFormat('d/m/Y') : $p->tanggal_terima->translatedFormat('d/m/Y')) : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat penghargaan / tanda jasa yang tercatat.</p>
    @endif

    <!-- 12. RIWAYAT KEANGGOTAAN ORGANISASI -->
    <div class="section-header">12. RIWAYAT KEANGGOTAAN ORGANISASI</div>
    @if($pegawai->riwayatOrganisasi && $pegawai->riwayatOrganisasi->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Nama Organisasi</th>
                    <th style="width: 30%;">Jabatan / Peran</th>
                    <th style="width: 15%;">Periode</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatOrganisasi as $org)
                    <tr>
                        <td class="bold">{{ $org->nama_organisasi }}</td>
                        <td>{{ $org->jabatan_organisasi ?? '-' }}</td>
                        <td class="text-center">{{ $org->tahun_mulai ?? '?' }} - {{ $org->tahun_selesai ?? 'Sekarang' }}</td>
                        <td class="text-center">{{ $org->masih_aktif ? 'Aktif' : 'Selesai' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat keanggotaan organisasi yang tercatat.</p>
    @endif

    <!-- 13. RIWAYAT PUBLIKASI ILMIAH & KARYA -->
    <div class="section-header">13. RIWAYAT PUBLIKASI ILMIAH & KARYA</div>
    @if($pegawai->riwayatPublikasi && $pegawai->riwayatPublikasi->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Judul Publikasi / Karya</th>
                    <th style="width: 20%;">Kategori</th>
                    <th style="width: 25%;">Penerbit / Jurnal</th>
                    <th style="width: 10%;">Tahun</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatPublikasi as $pub)
                    <tr>
                        <td class="bold">
                            {{ $pub->judul_publikasi }}
                            @if($pub->doi_link_publikasi)
                                <br><span style="font-size: 8.5pt; font-weight: normal; color: #444;">DOI/Link: {{ $pub->doi_link_publikasi }}</span>
                            @endif
                        </td>
                        <td>{{ $pub->kategori_publikasi ?? '-' }}</td>
                        <td>{{ $pub->penerbit_jurnal ?? '-' }}</td>
                        <td class="text-center">{{ $pub->tahun_publikasi ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat publikasi ilmiah / karya yang tercatat.</p>
    @endif

@endsection