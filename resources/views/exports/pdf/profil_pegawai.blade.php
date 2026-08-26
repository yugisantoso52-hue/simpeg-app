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

    <!-- 2. INFORMASI KONTAK -->
    <div class="section-header">2. INFORMASI KONTAK</div>
    <table class="form-table">
        <tr>
            <td style="width: 32%;" class="bold">Email</td>
            <td style="width: 3%;">:</td>
            <td>{{ $pegawai->email ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Nomor HP</td>
            <td>:</td>
            <td>{{ $pegawai->no_hp ?? $pegawai->telepon ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Alamat</td>
            <td>:</td>
            <td>{{ $pegawai->alamat ?? '-' }}</td>
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
            <td>{{ $pegawai->unitKerja->nama_unit ?? $pegawai->unit_kerja ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Jabatan</td>
            <td>:</td>
            <td>{{ $pegawai->jabatan->nama_jabatan ?? $pegawai->jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Golongan</td>
            <td>:</td>
            <td>
                {{ $pegawai->golongan->nama_golongan ?? $pegawai->golongan ?? '-' }}
                @if(!empty($pegawai->golongan->nama_pangkat))
                    ({{ $pegawai->golongan->nama_pangkat }})
                @endif
            </td>
        </tr>
        <tr>
            <td class="bold">Jenis Pegawai / Status ASN</td>
            <td>:</td>
            <td>{{ $pegawai->jenis_pegawai ?? '-' }} / {{ $pegawai->status_asn ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Pendidikan Terakhir</td>
            <td>:</td>
            <td>{{ $pegawai->pendidikan_terakhir ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">MKG (Masa Kerja Golongan)</td>
            <td>:</td>
            <td>{{ $pegawai->mkg_tahun ?? 0 }} Thn {{ $pegawai->mkg_bulan ?? 0 }} Bln</td>
        </tr>
    </table>

    <!-- 5. ADMINISTRASI KEPEGAWAIAN -->
    <div class="section-header">5. ADMINISTRASI KEPEGAWAIAN</div>
    <table class="form-table">
        <tr>
            <td style="width: 32%;" class="bold">Tanggal Masuk</td>
            <td style="width: 3%;">:</td>
            <td>{{ $pegawai->tanggal_masuk ? (is_string($pegawai->tanggal_masuk) ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->translatedFormat('d F Y') : $pegawai->tanggal_masuk->translatedFormat('d F Y')) : '-' }}</td>
        </tr>
        <tr>
            <td class="bold">TMT SK Pertama</td>
            <td>:</td>
            <td>{{ $pegawai->tmt_sk_pertama ? (is_string($pegawai->tmt_sk_pertama) ? \Carbon\Carbon::parse($pegawai->tmt_sk_pertama)->translatedFormat('d F Y') : $pegawai->tmt_sk_pertama->translatedFormat('d F Y')) : '-' }}</td>
        </tr>
        <tr>
            <td class="bold">TMT Pangkat Terakhir</td>
            <td>:</td>
            <td>{{ $pegawai->tmt_pangkat_terakhir ? (is_string($pegawai->tmt_pangkat_terakhir) ? \Carbon\Carbon::parse($pegawai->tmt_pangkat_terakhir)->translatedFormat('d F Y') : $pegawai->tmt_pangkat_terakhir->translatedFormat('d F Y')) : '-' }}</td>
        </tr>
        <tr>
            <td class="bold">TMT KGB Terakhir</td>
            <td>:</td>
            <td>{{ $pegawai->tmt_kgb_terakhir ? (is_string($pegawai->tmt_kgb_terakhir) ? \Carbon\Carbon::parse($pegawai->tmt_kgb_terakhir)->translatedFormat('d F Y') : $pegawai->tmt_kgb_terakhir->translatedFormat('d F Y')) : '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Status Pegawai</td>
            <td>:</td>
            <td>{{ $pegawai->status_pegawai ?? 'Aktif' }}</td>
        </tr>
    </table>

@endsection