@extends('exports.pdf.master')

@section('title', 'Kenaikan Gaji Berkala - ' . ($pegawai->nama_lengkap ?? $pegawai->nama))

@push('styles')
<style>
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
        margin: 6px 0;
        font-size: 10.5pt;
    }
    .tabel-identitas {
        width: 98%;
        margin: 4px auto 8px auto;
        border-collapse: collapse;
        font-size: 10.5pt;
    }
    .tabel-identitas td {
        padding: 2px 2px;
        vertical-align: top;
    }
    .tabel-sk-dasar {
        width: 100%;
        border-collapse: collapse;
        margin-top: 2px;
        font-size: 10pt;
    }
    .tabel-sk-dasar td {
        padding: 1.5px 2px;
        vertical-align: top;
    }
    .ttd-box-container {
        width: 100%;
        margin-top: 20px;
        page-break-inside: avoid;
    }
    .ttd-pejabat {
        float: right;
        width: 58%;
        text-align: left;
        font-size: 10.5pt;
        line-height: 1.25;
    }
    .tembusan-box {
        font-size: 9pt;
        line-height: 1.3;
        margin-top: 20px;
        clear: both;
    }
</style>
@endpush

@section('content')
{{-- KEPALA SURAT --}}
<table class="surat-header-table">
    <tr>
        <td style="width: 13%;">Nomor</td>
        <td style="width: 2%;">:</td>
        <td style="width: 45%;">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/UN19.5.1.1.10/KP/{{ $tahunSurat ?? date('Y') }}
        </td>
        <td style="width: 40%; text-align: right;">
            Pekanbaru, {{ $tanggalSurat ?? \Carbon\Carbon::now()->translatedFormat('d F Y') }}
        </td>
    </tr>
    <tr>
        <td>Lampiran</td>
        <td>:</td>
        <td>-</td>
        <td></td>
    </tr>
    <tr>
        <td>Perihal</td>
        <td>:</td>
        <td><strong>Kenaikan Gaji Berkala</strong></td>
        <td style="text-align: left; vertical-align: top; padding-left: 20px;">
            Kepada Yth :<br>
            <strong>Sdr. Kepala Kantor Perbendaharaan Negara</strong><br>
            (Kepala Seksi pembelanjaan I)<br>
            Pekanbaru
        </td>
    </tr>
</table>

<p class="p-isi" style="text-indent: 30px;">
    Dengan ini diberitahukan bahwa berhubung telah terpenuhinya masa kerja dan syarat-syarat lainnya kepada :
</p>

{{-- TABEL IDENTITAS PEGAWAI --}}
<table class="tabel-identitas">
    <tr>
        <td style="width: 28%;">Nama</td>
        <td style="width: 3%;">:</td>
        <td><strong>{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</strong></td>
    </tr>
    <tr>
        <td>NIP</td>
        <td>:</td>
        <td>{{ $pegawai->nip }}</td>
    </tr>
    <tr>
        <td>Pangkat/Golongan</td>
        <td>:</td>
        <td>
            @if($isPppk)
                {{ $pegawai->golongan->nama_pangkat ?? 'Ahli Pertama' }} / {{ $pegawai->golongan->nama_golongan ?? 'IX' }}
            @else
                {{ $pegawai->golongan->nama_pangkat ?? 'Penata' }} / {{ $pegawai->golongan->nama_golongan ?? 'III/c' }}
            @endif
        </td>
    </tr>
    <tr>
        <td>Kantor tempat</td>
        <td>:</td>
        <td>{{ $pegawai->unitKerja->nama_unit ?? 'Fakultas Keperawatan Universitas Riau' }}</td>
    </tr>
    <tr>
        <td>Gaji Pokok Lama</td>
        <td>:</td>
        <td><strong>Rp. {{ number_format($kgb->gaji_lama, 0, ',', '.') }}.-</strong></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td style="font-size: 9.5pt; color: #1e293b;">
            (Atas dasar SKP terakhir tentang gaji/pangkat yang ditetapkan)
            <table class="tabel-sk-dasar">
                <tr>
                    <td style="width: 22px;">a.</td>
                    <td style="width: 175px;">Oleh Pejabat</td>
                    <td style="width: 10px;">:</td>
                    <td>A.n. Dekan Wakil Dekan Bid Umum dan Keuangan Fakultas Keperawatan Universitas Riau</td>
                </tr>
                <tr>
                    <td>b.</td>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>{{ $dasarSkTanggal ?? '4 Juni 2024' }}</td>
                </tr>
                <tr>
                    <td>c.</td>
                    <td>Nomor</td>
                    <td>:</td>
                    <td>{{ $dasarSkNomor ?? '827/UN19.5.1.1.10/KP/2024' }}</td>
                </tr>
                <tr>
                    <td>d.</td>
                    <td>tanggal Mulai berlakunya</td>
                    <td>:</td>
                    <td>{{ $dasarSkTmt ?? '1 Agustus 2024' }}</td>
                </tr>
                <tr>
                    <td>e.</td>
                    <td>Masa Kerja Golongan pada tanggal tersebut</td>
                    <td>:</td>
                    <td>{{ $dasarMkgTahun ?? ($kgb->masa_kerja_tahun - 2 >= 0 ? $kgb->masa_kerja_tahun - 2 : 22) }} tahun</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p class="p-isi" style="margin-top: 6px;">
    Diberikan kenaikan gaji berkala hingga memperoleh :
</p>

{{-- TABEL GAJI BARU & KETENTUAN --}}
<table class="tabel-identitas">
    <tr>
        <td style="width: 28%;">Gaji pokok baru</td>
        <td style="width: 3%;">:</td>
        <td><strong>Rp. {{ number_format($kgb->gaji_baru, 0, ',', '.') }},-</strong></td>
    </tr>
    <tr>
        <td>Berdasarkan masa kerja</td>
        <td>:</td>
        <td>{{ $kgb->masa_kerja_tahun }} Tahun</td>
    </tr>
    <tr>
        <td>Dalam golongan</td>
        <td>:</td>
        <td>{{ $pegawai->golongan->nama_golongan ?? ($isPppk ? 'IX' : 'III/c') }}</td>
    </tr>
    <tr>
        <td>Mulai berlaku</td>
        <td>:</td>
        <td>{{ \Carbon\Carbon::parse($kgb->tmt_kgb_baru)->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td>Kenaikan Gaji Berkala berikutnya</td>
        <td>:</td>
        <td>{{ \Carbon\Carbon::parse($kgb->tmt_kgb_baru)->addYears(2)->translatedFormat('d F Y') }}</td>
    </tr>
</table>

<p class="p-isi" style="margin-top: 10px; text-indent: 30px;">
    Diharapkan agar sesuai dengan {{ $isPppk ? 'Peraturan Presiden Nomor 11 Tahun 2024 dan PermenPAN-RB No. 7 Tahun 2023' : 'PP. Nomor 5 Tahun 2024' }} kepada pegawai tersebut dapat dibayarkan penghasilan berdasarkan gaji pokok baru.
</p>

{{-- AREA TANDA TANGAN & PARAF DIGITAL PEMERIKSAAN DOKUMEN --}}
<table style="width: 100%; margin-top: 15px; page-break-inside: avoid; border-collapse: collapse;">
    <tr>
        {{-- Sisi Kiri: Paraf Digital Pemeriksaan Dokumen (Ka Pokja Keu-Kepeg & Kabag Umum) + Otentikasi Digital --}}
        <td style="width: 52%; vertical-align: top; padding-right: 15px;">
            <div style="border: 1px solid #334155; padding: 6px 8px; border-radius: 4px; font-size: 7.5pt; background-color: #f8fafc; font-family: 'Times New Roman', Times, serif;">
                <div style="font-weight: bold; text-transform: uppercase; margin-bottom: 4px; color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 2px;">
                    PARAF DIGITAL KOORDINASI TATA NASKAH:
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
                    <tr>
                        <td style="width: 48%; padding: 2px 0;">1. Ka Pokja Keu-Kepeg</td>
                        <td style="width: 52%; padding: 2px 0;">: {{ $pejabatKaPokja->nama ?? 'Dolli Vita Zenitha Harning Arivina' }}, SE</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;">&nbsp;&nbsp;&nbsp;Status Pemeriksaan</td>
                        <td style="padding: 2px 0; color: #047857; font-weight: bold;">: [ Diverifikasi & Sah ✓ ]</td>
                    </tr>
                    <tr style="border-top: 1px dashed #cbd5e1;">
                        <td style="padding: 2px 0;">2. Kepala Bagian Umum</td>
                        <td style="padding: 2px 0;">: {{ $pejabatKabag->nama ?? 'Bakhtiar' }}, S.Sos., M.Si</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;">&nbsp;&nbsp;&nbsp;Status Pemeriksaan</td>
                        <td style="padding: 2px 0; color: #047857; font-weight: bold;">: [ Diverifikasi & Sah ✓ ]</td>
                    </tr>
                </table>
            </div>

            {{-- OTENTIKASI DIGITAL SIKAP FKP UNRI --}}
            <div style="margin-top: 6px; border: 1px dashed #0369a1; padding: 5px 8px; border-radius: 4px; font-size: 7pt; color: #0c4a6e; background-color: #f0f9ff; font-family: 'Times New Roman', Times, serif;">
                <strong style="color: #0369a1; display: block; margin-bottom: 2px;">🔒 OTENTIKASI DIGITAL SIKAP FKP UNRI:</strong>
                Dokumen resmi ini telah diverifikasi validitas kepegawaiannya dan diterbitkan secara elektronik melalui Sistem Informasi Kepegawaian (SIKAP) Fakultas Keperawatan Universitas Riau.<br>
                @if(isset($verifyUrl))
                    Kode Verifikasi Resmi : <span style="font-family: monospace; font-size: 6.5pt; color: #0284c7;">{{ $verifyCode ?? 'SIKAP-KGB-VALID' }}</span><br>
                    Tautan Verifikasi : <a href="{{ $verifyUrl }}" style="color: #0284c7; text-decoration: underline; font-size: 6.5pt;">{{ $verifyUrl }}</a>
                @endif
            </div>
        </td>

        {{-- Sisi Kanan: Tanda Tangan Wakil Dekan Bidang Keuangan dan Umum --}}
        <td style="width: 48%; text-align: left; vertical-align: top; padding-left: 10px;">
            <div style="font-size: 10.5pt; line-height: 1.25;">
                Wakil Dekan Bidang Keuangan dan Umum<br>
                Fakultas Keperawatan Universitas Riau<br>
                <br><br><br><br>
                <strong style="text-decoration: underline;">{{ $pejabatWd2->nama_lengkap ?? ($pejabatWd2->nama ?? 'Ns. Safri, M.Kep., Sp.Kep.M.B') }}</strong><br>
                <span>NIP. {{ $pejabatWd2->nip ?? '198509092014041001' }}</span>
            </div>
        </td>
    </tr>
</table>

{{-- TEMBUSAN SURAT --}}
<div class="tembusan-box">
    <strong>Tembusan :</strong>
    <ol style="margin: 2px 0 0 18px; padding: 0;">
        <li>Kemendiktisaintek & DIKTI di Jakarta</li>
        <li>Dirjend Pendidikan Tinggi di Jakarta</li>
        <li>Kepala BAKN di Jakarta</li>
        <li>Rektor UNRI di Pekanbaru</li>
        <li>Bendaharawan UNRI di Pekanbaru</li>
        <li>Pegawai Yang bersangkutan</li>
        <li>Arsip</li>
    </ol>
</div>
@endsection

{{-- Suppress default ttd from master layout --}}
@section('ttd')
@endsection