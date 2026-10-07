@extends('exports.pdf.master')

@section('title', 'Usul Kenaikan Pangkat ' . ($pengajuan->pegawai->nama_lengkap ?? $pengajuan->pegawai->nama))

@push('styles')
<style>
    /* Format Surat Resmi Permendikti Saintek No. 42/2025 & Tata Naskah Dinas */
    .surat-header-table {
        width: 100%;
        margin-bottom: 12px;
        font-size: 10.5pt;
    }
    .surat-header-table td {
        vertical-align: top;
        padding: 1.5px 0;
    }
    .p-isi {
        text-align: justify;
        line-height: 1.35;
        margin: 8px 0;
        text-indent: 32px;
        font-size: 10.5pt;
    }
    .tabel-identitas {
        width: 95%;
        margin: 8px auto 10px auto;
        border-collapse: collapse;
        font-size: 10.5pt;
    }
    .tabel-identitas td {
        padding: 3px 2px;
        vertical-align: top;
    }
    .tabel-syarat {
        width: 95%;
        margin: 6px auto 10px auto;
        font-size: 10pt;
    }
    .tabel-syarat td {
        padding: 2px 2px;
        vertical-align: top;
    }
    .tembusan-box {
        font-size: 9pt;
        line-height: 1.25;
        margin-top: 15px;
    }
    
    /* Tabel Tanda Tangan Dekan dengan Kolom Paraf Hirarkis di sebelah kiri */
    .ttd-paraf-table {
        width: 100%;
        margin-top: 20px;
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

{{-- AREA PENANDATANGANAN & PARAF HIRARKIS KABAG UMUM + WADEK II --}}
<table class="ttd-paraf-table">
    <tr>
        {{-- Sisi Kiri: Kotak Paraf Hirarki Sesuai Tata Naskah Dinas --}}
        <td style="width: 50%; vertical-align: bottom;">
            <div class="paraf-hierarki-box">
                <div style="font-weight: bold; margin-bottom: 3px; color: #1e293b;">
                    PARAF KOORDINASI HIRARKI TATA USAHA:
                </div>
                <table>
                    <tr>
                        <td style="width: 32%;">1. Kabag Umum</td>
                        <td style="width: 48%;">: {{ $pejabatKabag->nama ?? 'Bakhtiar' }}, S.Sos., M.Si</td>
                        <td style="width: 20%; text-align: right;">
                            @if($pengajuan->paraf_kabag_at)
                                <span class="badge-paraf">[ Paraf ✓ ]</span>
                            @else
                                <span style="color: #64748b;">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>2. Wadek II (Keu & Umum)</td>
                        <td>: Dr. {{ $pejabatWd2->nama ?? 'Safri' }}, M.Kep., Sp.Kep.M.B</td>
                        <td style="text-align: right;">
                            @if($pengajuan->paraf_wd2_at)
                                <span class="badge-paraf">[ Paraf ✓ ]</span>
                            @else
                                <span style="color: #64748b;">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</span>
                            @endif
                        </td>
                    </tr>
                </table>
                <div style="font-size: 7pt; color: #64748b; margin-top: 3px; font-style: italic;">
                    * Sesuai Peraturan Tata Naskah Dinas Kemendiktisaintek No. 42 Tahun 2025.
                </div>
            </div>
        </td>

        {{-- Sisi Kanan: Tanda Tangan Dekan --}}
        <td style="width: 50%; text-align: center; vertical-align: top;">
            <div style="font-size: 10.5pt; line-height: 1.25;">
                Dekan Fakultas Keperawatan<br>
                Universitas Riau,
                <br><br><br><br><br>
                <strong style="text-decoration: underline;">Prof. {{ $pejabatDekan->nama ?? 'Wan Nishfa Dewi' }}, S.Kp., MNg., PhD</strong><br>
                <span>NIP. {{ $pejabatDekan->nip ?? '197508222001122001' }}</span>
            </div>
        </td>
    </tr>
</table>

{{-- Bagian Tembusan --}}
<div class="tembusan-box">
    <strong>Tembusan Yth:</strong>
    <ol style="margin: 2px 0 0 16px; padding: 0;">
        <li>Dirjen Diktiristek Kemendiktisaintek di Jakarta;</li>
        <li>Kepala Kantor Regional XII BKN di Pekanbaru;</li>
        <li>Wakil Rektor Bidang Kepegawaian dan Umum Universitas Riau;</li>
        <li>Bendaharawan Universitas Riau di Pekanbaru;</li>
        <li>Pegawai yang bersangkutan;</li>
        <li>Arsip.</li>
    </ol>
</div>
@endsection

{{-- Suppress default ttd from master layout --}}
@section('ttd')
@endsection
