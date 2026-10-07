@extends('exports.pdf.master')

@section('title', 'Usul Kenaikan Pangkat ' . ($pengajuan->pegawai->nama_lengkap ?? $pengajuan->pegawai->nama))

@push('styles')
<style>
    @page {
        size: a4 portrait;
        margin: 10mm 16mm 10mm 20mm !important;
    }
    .surat-header-table {
        width: 100%;
        margin-bottom: 8px;
        font-size: 10pt;
    }
    .surat-header-table td {
        vertical-align: top;
        padding: 1px 0;
    }
    .p-isi {
        text-align: justify;
        line-height: 1.25;
        margin: 4px 0;
        text-indent: 28px;
        font-size: 10pt;
    }
    .tabel-identitas {
        width: 96%;
        margin: 3px auto 4px auto;
        border-collapse: collapse;
        font-size: 9.5pt;
    }
    .tabel-identitas td {
        padding: 1.5px 2px;
        vertical-align: top;
    }
    .tabel-syarat {
        width: 96%;
        margin: 2px auto 4px auto;
        font-size: 9pt;
        border-collapse: collapse;
    }
    .tabel-syarat td {
        padding: 1px 2px;
        vertical-align: top;
    }
    .tembusan-box {
        font-size: 7.5pt;
        line-height: 1.2;
        margin-top: 6px;
    }
    
    /* Tabel Tanda Tangan Dekan dengan Kolom Paraf Hirarkis di sebelah kiri */
    .ttd-paraf-table {
        width: 100%;
        margin-top: 8px;
        page-break-inside: avoid;
        border-collapse: collapse;
    }
    .paraf-hierarki-box {
        border: 1px solid #333;
        padding: 5px 8px;
        border-radius: 4px;
        font-size: 8pt;
        width: 90%;
        background-color: #fafafa;
    }
    .paraf-hierarki-box table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
    }
    .paraf-hierarki-box td {
        padding: 3px 2px;
        border-bottom: 1px dashed #bbb;
    }
    .paraf-hierarki-box tr:last-child td {
        border-bottom: none;
    }
    .badge-paraf {
        color: #047857;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<table class="surat-header-table">
    <tr>
        <td style="width: 13%;">Nomor</td>
        <td style="width: 2%;">:</td>
        <td style="width: 45%;">B/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/UN19.5.1.1.10/KP/{{ $pengajuan->tahun_periode ?? date('Y') }}</td>
        <td style="width: 40%; text-align: right;">Pekanbaru, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td>Lampiran</td>
        <td>:</td>
        <td>1 (satu) Berkas</td>
        <td></td>
    </tr>
    <tr>
        <td>Hal</td>
        <td>:</td>
        <td><strong>Usul Kenaikan Pangkat Periode {{ $pengajuan->periode_kp ?? 'Pilihan' }} {{ $pengajuan->tahun_periode ?? date('Y') }}<br>a.n. {{ $pengajuan->pegawai->nama_lengkap ?? $pengajuan->pegawai->nama }}</strong></td>
        <td style="text-align: right; vertical-align: top;">
            Kepada Yth.<br>
            <strong>Rektor Universitas Riau</strong><br>
            di Pekanbaru
        </td>
    </tr>
</table>

<p class="p-isi">
    Dengan hormat, sehubungan dengan Peraturan Badan Kepegawaian Negara Nomor 4 Tahun 2023 tentang Periodisasi Kenaikan Pangkat Pegawai Negeri Sipil serta telah terpenuhinya masa kerja dan syarat-syarat yang ditentukan, bersama ini kami sampaikan usulan Kenaikan Pangkat salah seorang {{ $pengajuan->pegawai->isDosen() ? 'Dosen' : 'Tenaga Kependidikan' }} Fakultas Keperawatan Universitas Riau:
</p>

<table class="tabel-identitas">
    <tr>
        <td style="width: 32%;">1. Nama Pegawai</td>
        <td style="width: 3%;">:</td>
        <td><strong>{{ $pengajuan->pegawai->nama_lengkap ?? $pengajuan->pegawai->nama }}</strong></td>
    </tr>
    <tr>
        <td>2. N I P</td>
        <td>:</td>
        <td>{{ $pengajuan->pegawai->nip }}</td>
    </tr>
    <tr>
        <td>3. Pangkat / Gol. Ruang / TMT Lama</td>
        <td>:</td>
        <td>
            {{ $pengajuan->golonganLama->nama_pangkat ?? ($pengajuan->pegawai->golongan->nama_pangkat ?? '-') }} / 
            {{ $pengajuan->golonganLama->nama_golongan ?? ($pengajuan->pegawai->golongan->nama_golongan ?? '-') }} 
            (TMT: {{ $pengajuan->tmt_lama ? \Carbon\Carbon::parse($pengajuan->tmt_lama)->translatedFormat('d F Y') : ($pengajuan->pegawai->tmt_pangkat_terakhir ? \Carbon\Carbon::parse($pengajuan->pegawai->tmt_pangkat_terakhir)->translatedFormat('d F Y') : '-') }})
        </td>
    </tr>
    <tr>
        <td>4. Jabatan / Unit Kerja</td>
        <td>:</td>
        <td>{{ $pengajuan->pegawai->jabatan->nama_jabatan ?? '-' }} / {{ $pengajuan->pegawai->unitKerja->nama_unit ?? 'Fakultas Keperawatan' }}</td>
    </tr>
    <tr>
        <td>5. Pangkat / Gol. Ruang Diusulkan</td>
        <td>:</td>
        <td>
            <strong>{{ $pengajuan->golonganTujuan->nama_pangkat ?? '-' }} ({{ $pengajuan->golonganTujuan->nama_golongan ?? '-' }})</strong>
        </td>
    </tr>
    <tr>
        <td>6. Periode / TMT Kenaikan Pangkat</td>
        <td>:</td>
        <td><strong>1 {{ $pengajuan->periode_kp ?? 'Februari' }} {{ $pengajuan->tahun_periode ?? date('Y') }}</strong></td>
    </tr>
</table>

<p class="p-isi" style="margin-bottom: 4px;">
    Sebagai bahan pertimbangan dan kelengkapan administrasi pada Sistem Informasi Aparatur Sipil Negara (SIASN BKN), bersama ini kami lampirkan dokumen persyaratan sebagai berikut:
</p>

<table class="tabel-syarat">
    <tr>
        <td style="width: 5%;">1.</td>
        <td style="width: 95%;">Salinan sah Keputusan Kenaikan Pangkat Terakhir;</td>
    </tr>
    <tr>
        <td>2.</td>
        <td>Salinan sah Penilaian Kinerja Pegawai (SKP) 2 (dua) tahun terakhir bernilai minimal predikat "Baik";</td>
    </tr>
    <tr>
        <td>3.</td>
        <td>Salinan sah Surat Pemberitahuan Kenaikan Gaji Berkala (KGB) terakhir;</td>
    </tr>
    <tr>
        <td>4.</td>
        <td>Salinan sah Kartu Pegawai (KARPEG) / Identitas ASN;</td>
    </tr>
    @if($pengajuan->pegawai->isDosen() || $pengajuan->pegawai->isPlp())
    <tr>
        <td>5.</td>
        <td>Salinan sah Penetapan Angka Kredit (PAK) / Konversi Predikat Kinerja Fungsional;</td>
    </tr>
    @endif
</table>

<p class="p-isi">
    Demikian usulan ini kami sampaikan, atas bantuan, kerja sama dan perkenan Bapak Rektor kami ucapkan terima kasih.
</p>

{{-- AREA PENANDATANGANAN & PARAF DIGITAL KOORDINASI TATA NASKAH --}}
<table class="ttd-paraf-table">
    <tr>
        {{-- Sisi Kiri: Kotak Paraf Koordinasi Tata Naskah (Ka Pokja & Kabag Umum) --}}
        <td style="width: 53%; vertical-align: bottom; padding-right: 12px;">
            <div style="border: 1px solid #1e293b; padding: 4px 6px; border-radius: 4px; background-color: #f8fafc; font-family: 'Times New Roman', Times, serif;">
                <div style="font-weight: bold; text-transform: uppercase; margin-bottom: 3px; color: #0f172a; border-bottom: 1px solid #94a3b8; padding-bottom: 2px; font-size: 7pt; letter-spacing: 0.2px;">
                    PARAF DIGITAL KOORDINASI TATA NASKAH:
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 7pt; line-height: 1.15;">
                    {{-- 1. Ka Pokja Keu-Kepeg --}}
                    <tr style="border-bottom: 1px dashed #cbd5e1;">
                        <td style="width: 32px; vertical-align: middle; padding: 2px 4px 2px 0; text-align: center;">
                            @if(isset($qrPokjaUri))
                                <img src="{{ $qrPokjaUri }}" style="width: 28px; height: 28px; display: block; margin: 0 auto;" />
                            @endif
                        </td>
                        <td style="vertical-align: middle; padding: 2px 0;">
                            <div style="font-weight: bold; color: #0f172a; font-size: 7pt;">1. Ka Pokja Keu-Kepeg</div>
                            <div style="color: #1e293b; font-size: 6.8pt;">{{ $pejabatKaPokja->nama ?? 'Dolli Vita Zenitha Harning Arivina' }}, SE</div>
                            <div style="color: #047857; font-weight: bold; font-size: 6.2pt;">
                                [ Terverifikasi & Diparaf Digital ] &bull; {{ $parafPokjaAt ?? '07/10/2026' }}
                            </div>
                        </td>
                    </tr>
                    {{-- 2. Kepala Bagian Umum --}}
                    <tr>
                        <td style="width: 32px; vertical-align: middle; padding: 3px 4px 2px 0; text-align: center;">
                            @if(isset($qrKabagUri))
                                <img src="{{ $qrKabagUri }}" style="width: 28px; height: 28px; display: block; margin: 0 auto;" />
                            @endif
                        </td>
                        <td style="vertical-align: middle; padding: 3px 0;">
                            <div style="font-weight: bold; color: #0f172a; font-size: 7pt;">2. Kepala Bagian Umum</div>
                            <div style="color: #1e293b; font-size: 6.8pt;">{{ $pejabatKabag->nama ?? 'Bakhtiar' }}, S.Sos., M.Si</div>
                            <div style="color: #047857; font-weight: bold; font-size: 6.2pt;">
                                [ Terverifikasi & Diparaf Digital ] &bull; {{ $parafKabagAt ?? '07/10/2026' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </td>

        {{-- Sisi Kanan: Tanda Tangan Manual Wakil Dekan Bidang Keuangan dan Umum --}}
        <td style="width: 47%; text-align: left; vertical-align: top; padding-left: 8px;">
            <div style="font-size: 10pt; line-height: 1.25;">
                Wakil Dekan Bidang Keuangan dan Umum<br>
                Fakultas Keperawatan Universitas Riau<br>
                {{-- Ruang untuk tanda tangan manual basah --}}
                <div style="height: 48px;"></div>
                <strong style="text-decoration: underline;">{{ $pejabatWd2->nama_lengkap ?? ($pejabatWd2->nama ?? 'Dr. Safri, M.Kep., Sp.Kep.M.B') }}</strong><br>
                <span>NIP. {{ $pejabatWd2->nip ?? '198509092014041001' }}</span>
            </div>
        </td>
    </tr>
</table>

{{-- Bagian Tembusan (2 Kolom Hemat Ruang) --}}
<div class="tembusan-box">
    <strong>Tembusan Yth:</strong>
    <table style="width: 100%; border-collapse: collapse; margin-top: 1.5px; font-size: 7.5pt; line-height: 1.2;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 0;">
                1. Dirjen Diktiristek Kemendiktisaintek di Jakarta<br>
                2. Kepala Kantor Regional XII BKN di Pekanbaru<br>
                3. Wakil Rektor Bidang Kepegawaian dan Umum Universitas Riau
            </td>
            <td style="width: 50%; vertical-align: top; padding: 0;">
                4. Bendaharawan Universitas Riau di Pekanbaru<br>
                5. Pegawai yang bersangkutan<br>
                6. Arsip
            </td>
        </tr>
    </table>
</div>
@endsection

{{-- Suppress default ttd from master layout --}}
@section('ttd')
@endsection
