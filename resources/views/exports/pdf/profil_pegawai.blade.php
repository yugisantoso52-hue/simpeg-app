@extends('exports.pdf.master')

@section('title', 'Profil Pegawai - ' . ($pegawai->nama_lengkap ?? $pegawai->nama))

@section('content')

    <div class="document-title">
        <h4>PROFIL LENGKAP PEGAWAI</h4>
        <p>NIP: {{ $pegawai->nip ?? '-' }}</p>
    </div>

    @php
        $namaCore = trim($pegawai->nama_lengkap ?? $pegawai->nama);
        $gelarDepan = trim($pegawai->gelar_depan ?? '');
        $gelarBelakang = trim($pegawai->gelar_belakang ?? '');

        if ($gelarDepan !== '' && !str_starts_with($namaCore, $gelarDepan)) {
            $namaCore = $gelarDepan . ' ' . $namaCore;
        }

        if ($gelarBelakang !== '' && !str_contains($namaCore, $gelarBelakang)) {
            $namaCore = $namaCore . ', ' . $gelarBelakang;
        }
    @endphp

    <!-- 1. DATA PRIBADI -->
    <div class="section-header">1. DATA PRIBADI</div>
    <table class="form-table">
        <tr>
            <td style="width: 32%;" class="bold">NIP / NIK</td>
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

    <!-- 4. DATA KEPEGAWAIAN & JABATAN -->
    <div class="section-header">4. DATA KEPEGAWAIAN & JABATAN</div>
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

    <!-- 5. ADMINISTRASI KEPEGAWAIAN & LEGALITAS -->
    <div class="section-header">5. ADMINISTRASI KEPEGAWAIAN & LEGALITAS</div>
    <table class="form-table">
        <tr>
            <td style="width: 32%;" class="bold">Tanggal Masuk / TMT Awal</td>
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
                @if($pegawai->nomor_sk_pertama) (No. SK: {{ $pegawai->nomor_sk_pertama }}) @endif
            </td>
        </tr>
        <tr>
            <td class="bold">TMT Pangkat Terakhir</td>
            <td>:</td>
            <td>
                {{ $pegawai->tmt_pangkat_terakhir ? (is_string($pegawai->tmt_pangkat_terakhir) ? \Carbon\Carbon::parse($pegawai->tmt_pangkat_terakhir)->translatedFormat('d F Y') : $pegawai->tmt_pangkat_terakhir->translatedFormat('d F Y')) : '-' }}
                @if($pegawai->nomor_sk_pangkat_terakhir) (No. SK: {{ $pegawai->nomor_sk_pangkat_terakhir }}) @endif
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
            </td>
        </tr>
        @endif
    </table>

    <!-- 6. RIWAYAT PANGKAT / GOLONGAN -->
    <div class="section-header">6. RIWAYAT PANGKAT / GOLONGAN</div>
    @if($pegawai->riwayatPangkat && $pegawai->riwayatPangkat->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Golongan / Pangkat</th>
                    <th style="width: 15%;">TMT Pangkat</th>
                    <th style="width: 30%;">Nomor SK</th>
                    <th style="width: 15%;">Tanggal SK</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatPangkat as $rp)
                    <tr>
                        <td class="bold">{{ $rp->golongan->nama_golongan ?? '-' }} ({{ $rp->golongan->nama_pangkat ?? '-' }})</td>
                        <td class="text-center">{{ $rp->tmt ? \Carbon\Carbon::parse($rp->tmt)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $rp->nomor_sk ?? '-' }}</td>
                        <td class="text-center">{{ $rp->tanggal_sk ? \Carbon\Carbon::parse($rp->tanggal_sk)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ ucfirst($rp->status ?? 'Riwayat') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat pangkat yang tercatat.</p>
    @endif

    <!-- 7. RIWAYAT JABATAN -->
    <div class="section-header">7. RIWAYAT JABATAN</div>
    @if($pegawai->riwayatJabatan && $pegawai->riwayatJabatan->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Nama Jabatan</th>
                    <th style="width: 25%;">Unit Kerja</th>
                    <th style="width: 15%;">TMT Jabatan</th>
                    <th style="width: 20%;">Nomor SK</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatJabatan as $rj)
                    <tr>
                        <td class="bold">{{ $rj->jabatan->nama_jabatan ?? $rj->nama_jabatan ?? '-' }}</td>
                        <td>{{ $rj->unitKerja->nama_unit ?? '-' }}</td>
                        <td class="text-center">{{ $rj->tmt_jabatan ? \Carbon\Carbon::parse($rj->tmt_jabatan)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $rj->nomor_sk ?? '-' }}</td>
                        <td class="text-center">{{ ucfirst($rj->status ?? 'Riwayat') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat jabatan yang tercatat.</p>
    @endif

    <!-- 8. RIWAYAT PENDIDIKAN -->
    <div class="section-header">8. RIWAYAT PENDIDIKAN</div>
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
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat pendidikan yang tercatat.</p>
    @endif

    <!-- 9. RIWAYAT DIKLAT / PELATIHAN -->
    <div class="section-header">9. RIWAYAT DIKLAT / PELATIHAN</div>
    @if($pegawai->riwayatDiklat && $pegawai->riwayatDiklat->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="center">No</th>
                    <th style="width: 35%;">Nama Diklat / Pelatihan</th>
                    <th style="width: 30%;">Penyelenggara</th>
                    <th style="width: 15%;" class="center">Tahun</th>
                    <th style="width: 15%;" class="center">Jumlah Jam</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatDiklat as $diklat)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $diklat->nama_diklat }}</td>
                        <td>{{ $diklat->penyelenggara ?? '-' }}</td>
                        <td class="center">{{ $diklat->tahun ?? ($diklat->tanggal_mulai ? \Carbon\Carbon::parse($diklat->tanggal_mulai)->year : '-') }}</td>
                        <td class="center">{{ $diklat->jumlah_jam ? $diklat->jumlah_jam . ' Jam' : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat diklat yang tercatat.</p>
    @endif

    <!-- 10. LEGALITAS PROFESI (STR & SIP) -->
    <div class="section-header">10. LEGALITAS PROFESI (STR & SIP)</div>
    @if($pegawai->riwayatStrSip && $pegawai->riwayatStrSip->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Jenis</th>
                    <th style="width: 25%;">Nomor Registrasi</th>
                    <th style="width: 30%;">Kualifikasi / Penerbit</th>
                    <th style="width: 15%;">Masa Berlaku</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatStrSip as $doc)
                    <tr>
                        <td class="bold">{{ $doc->jenis_dokumen }}</td>
                        <td>{{ $doc->nomor_registrasi }}</td>
                        <td>{{ $doc->nama_dokumen ?? $doc->instansi_penerbit ?? '-' }}</td>
                        <td class="text-center">{{ $doc->is_seumur_hidup ? 'Seumur Hidup' : ($doc->tanggal_berakhir ? $doc->tanggal_berakhir->format('d/m/Y') : '-') }}</td>
                        <td class="text-center">{{ $doc->status_label }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada data STR / SIP tercatat.</p>
    @endif

    <!-- 11. PENGARSIPAN SKP (SASARAN KINERJA PEGAWAI - 2 TAHUN) -->
    <div class="section-header">11. PENGARSIPAN SKP (SASARAN KINERJA PEGAWAI - 2 TAHUN)</div>
    @if($pegawai->riwayatSkp && $pegawai->riwayatSkp->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Tahun</th>
                    <th style="width: 25%;">Predikat Kinerja</th>
                    <th style="width: 35%;">Pejabat Penilai</th>
                    <th style="width: 25%;">Status Berkas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatSkp as $skp)
                    <tr>
                        <td class="bold text-center">{{ $skp->tahun }}</td>
                        <td class="text-center">{{ $skp->predikat_kinerja ?: 'Sedang Berjalan' }}</td>
                        <td>{{ $skp->pejabat_penilai ?: '-' }}</td>
                        <td class="text-center">
                            @if($skp->file_rencana_skp && $skp->file_evaluasi_skp)
                                Lengkap (Rencana & Evaluasi)
                            @elseif($skp->file_rencana_skp)
                                Rencana Terunggah
                            @else
                                Belum Lengkap
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada arsip SKP yang tercatat.</p>
    @endif

    <!-- 12. RIWAYAT PENGHARGAAN & TANDA JASA -->
    <div class="section-header">12. RIWAYAT PENGHARGAAN & TANDA JASA</div>
    @if($pegawai->riwayatPenghargaan && $pegawai->riwayatPenghargaan->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Nama Penghargaan</th>
                    <th style="width: 20%;">Jenis</th>
                    <th style="width: 25%;">Instansi Pemberi</th>
                    <th style="width: 20%;">Tanggal Terima</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatPenghargaan as $p)
                    <tr>
                        <td class="bold">{{ $p->nama_penghargaan }}</td>
                        <td>{{ $p->jenis_penghargaan ?? '-' }}</td>
                        <td>{{ $p->instansi_pemberi ?? '-' }}</td>
                        <td class="text-center">{{ $p->tanggal_terima ? $p->tanggal_terima->format('d/m/Y') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat penghargaan yang tercatat.</p>
    @endif

    <!-- 13. RIWAYAT KEANGGOTAAN ORGANISASI -->
    <div class="section-header">13. RIWAYAT KEANGGOTAAN ORGANISASI</div>
    @if($pegawai->riwayatOrganisasi && $pegawai->riwayatOrganisasi->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Nama Organisasi</th>
                    <th style="width: 25%;">Jabatan / Peran</th>
                    <th style="width: 20%;">Periode</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatOrganisasi as $org)
                    <tr>
                        <td class="bold">{{ $org->nama_organisasi }}</td>
                        <td>{{ $org->jabatan_organisasi ?? '-' }}</td>
                        <td class="text-center">{{ $org->tahun_mulai ?? '?' }} - {{ $org->tahun_selesai ?? 'sekarang' }}</td>
                        <td class="text-center">{{ $org->masih_aktif ? 'Aktif' : 'Selesai' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat organisasi yang tercatat.</p>
    @endif

    <!-- 14. RIWAYAT PUBLIKASI ILMIAH & KARYA -->
    <div class="section-header">14. RIWAYAT PUBLIKASI ILMIAH & KARYA</div>
    @if($pegawai->riwayatPublikasi && $pegawai->riwayatPublikasi->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Judul Publikasi</th>
                    <th style="width: 20%;">Jenis & Indeksasi</th>
                    <th style="width: 28%;">Jurnal / Penerbit</th>
                    <th style="width: 12%;">Tahun</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->riwayatPublikasi as $pub)
                    <tr>
                        <td class="bold">{{ $pub->judul_publikasi }}</td>
                        <td>{{ $pub->jenis_publikasi }} @if($pub->indeksasi) ({{ $pub->indeksasi }}) @endif</td>
                        <td>{{ $pub->nama_jurnal ?? $pub->penerbit ?? '-' }}</td>
                        <td class="text-center">{{ $pub->tahun_terbit ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat publikasi yang tercatat.</p>
    @endif

    <!-- 15. TUGAS BELAJAR & IZIN BELAJAR -->
    <div class="section-header">15. TUGAS BELAJAR & IZIN BELAJAR (STUDI LANJUT)</div>
    @if($pegawai->tugasBelajar && $pegawai->tugasBelajar->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Jenis & Jenjang</th>
                    <th style="width: 35%;">Universitas & Prodi</th>
                    <th style="width: 20%;">Beasiswa / SK</th>
                    <th style="width: 20%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->tugasBelajar as $tb)
                    <tr>
                        <td class="bold">{{ $tb->jenis_pengembangan }} ({{ $tb->jenjang_studi }})</td>
                        <td>{{ $tb->program_studi }} - {{ $tb->perguruan_tinggi }}</td>
                        <td>{{ $tb->sumber_pembiayaan }} ({{ $tb->nomor_sk }})</td>
                        <td class="text-center">{{ $tb->status_studi }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat tugas belajar / izin belajar yang tercatat.</p>
    @endif

    <!-- 16. RIWAYAT MUTASI PEGAWAI -->
    <div class="section-header">16. RIWAYAT MUTASI PEGAWAI</div>
    @if($pegawai->mutasi && $pegawai->mutasi->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Jenis Mutasi</th>
                    <th style="width: 25%;">Asal</th>
                    <th style="width: 25%;">Tujuan</th>
                    <th style="width: 25%;">TMT Mutasi & No. SK</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->mutasi as $m)
                    <tr>
                        <td class="bold">{{ $m->jenis_mutasi ?? 'Mutasi Internal' }}</td>
                        <td>{{ $m->unitKerjaAsal->nama_unit ?? $m->instansi_asal ?? '-' }}</td>
                        <td>{{ $m->unitKerjaTujuan->nama_unit ?? $m->instansi_tujuan ?? '-' }}</td>
                        <td class="text-center">{{ $m->tmt_mutasi ? \Carbon\Carbon::parse($m->tmt_mutasi)->format('d/m/Y') : '-' }} ({{ $m->nomor_sk ?? '-' }})</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada data mutasi yang tercatat.</p>
    @endif

    <!-- 17. RIWAYAT CUTI PEGAWAI -->
    <div class="section-header">17. RIWAYAT CUTI PEGAWAI</div>
    @if($pegawai->pengajuanCuti && $pegawai->pengajuanCuti->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Jenis Cuti</th>
                    <th style="width: 30%;">Periode Cuti</th>
                    <th style="width: 15%;">Jumlah Hari</th>
                    <th style="width: 15%;">Alasan</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->pengajuanCuti as $cuti)
                    <tr>
                        <td class="bold">{{ $cuti->jenis_cuti }}</td>
                        <td class="text-center">{{ $cuti->tanggal_mulai ? \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d/m/Y') : '-' }} s.d. {{ $cuti->tanggal_selesai ? \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $cuti->jumlah_hari }} Hari</td>
                        <td>{{ $cuti->alasan ?? '-' }}</td>
                        <td class="text-center">{{ $cuti->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 9pt; font-style: italic; color: #555; margin: 4px 0 10px 0;">Belum ada riwayat cuti yang tercatat.</p>
    @endif

@endsection