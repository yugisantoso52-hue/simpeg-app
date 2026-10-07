@extends('exports.pdf.master')

@section('title', 'Kenaikan Gaji Berkala - ' . ($pegawai->nama_lengkap ?? $pegawai->nama))

@push('styles')
<style>
    @page {
        size: A4 portrait;
        margin: 10mm 16mm 10mm 22mm !important;
    }
    body {
        font-family: 'Times New Roman', Times, serif;
        font-size: 10pt;
        line-height: 1.32;
        margin: 0;
        padding: 0;
    }
    .kop-surat-table {
        margin-bottom: 9px !important;
        padding-bottom: 4px !important;
    }
    .surat-header-table {
        width: 100%;
        margin-bottom: 8px;
        font-size: 10pt;
        line-height: 1.28;
    }
    .surat-header-table td {
        vertical-align: top;
        padding: 1.2px 0;
    }
    .p-isi {
        text-align: justify;
        line-height: 1.32;
        margin: 5px 0;
        font-size: 10pt;
    }
    .tabel-identitas {
        width: 100%;
        margin: 3.5px auto 6px auto;
        border-collapse: collapse;
        font-size: 10pt;
    }
    .tabel-identitas td {
        padding: 1.8px 2px;
        vertical-align: top;
        line-height: 1.28;
    }
    .tabel-sk-dasar {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1.5px;
        font-size: 9pt;
    }
    .tabel-sk-dasar td {
        padding: 1.2px 2px;
        vertical-align: top;
        line-height: 1.22;
    }
    .tembusan-box {
        font-size: 8pt;
        line-height: 1.22;
        margin-top: 7px;
        clear: both;
    }
</style>
@endpush

@section('content')
{{-- KEPALA SURAT --}}
<table class="surat-header-table">
    <tr>
        <td style="width: 12%;">Nomor</td>
        <td style="width: 2%;">:</td>
        <td style="width: 46%;">
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

<p class="p-isi" style="text-indent: 28px;">
    Dengan ini diberitahukan bahwa berhubung telah terpenuhinya masa kerja dan syarat-syarat lainnya kepada :
</p>

{{-- TABEL IDENTITAS PEGAWAI --}}
<table class="tabel-identitas">
    <tr>
        <td style="width: 27%;">Nama</td>
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
                    <td style="width: 20px;">a.</td>
                    <td style="width: 170px;">Oleh Pejabat</td>
                    <td style="width: 8px;">:</td>
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
                    <td>{{ $dasarSkTmt ?? '01 Oktober 2029' }}</td>
                </tr>
                <tr>
                    <td>e.</td>
                    <td>Masa Kerja Golongan pada tanggal tersebut</td>
                    <td>:</td>
                    <td>{{ $dasarMkgTahun ?? 0 }} tahun</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p class="p-isi" style="margin-top: 5px;">
    Diberikan kenaikan gaji berkala hingga memperoleh :
</p>

{{-- TABEL GAJI BARU & KETENTUAN --}}
<table class="tabel-identitas">
    <tr>
        <td style="width: 27%;">Gaji pokok baru</td>
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

<p class="p-isi" style="margin-top: 6px; text-indent: 28px;">
    @if($isPppk)
        Diharapkan agar sesuai dengan Peraturan Presiden Nomor 11 Tahun 2024 kepada pegawai tersebut dapat dibayarkan penghasilan berdasarkan gaji pokok baru.
    @else
        Diharapkan agar sesuai dengan Peraturan Pemerintah Nomor 5 Tahun 2024 kepada pegawai tersebut dapat dibayarkan penghasilan berdasarkan gaji pokok baru.
    @endif
</p>

{{-- AREA TANDA TANGAN & PARAF DIGITAL KOORDINASI TATA NASKAH --}}
<table style="width: 100%; margin-top: 10px; page-break-inside: avoid; border-collapse: collapse;">
    <tr>
        {{-- Sisi Kiri: Paraf Digital Tata Naskah (Ka Pokja & Kabag Umum) --}}
        <td style="width: 53%; vertical-align: top; padding-right: 10px;">
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

        {{-- Sisi Kanan: Tanda Tangan Manual Wakil Dekan II (Tanpa 'Ditetapkan di...') --}}
        <td style="width: 47%; text-align: left; vertical-align: top; padding-left: 8px;">
            <div style="font-size: 9.5pt; line-height: 1.25;">
                Wakil Dekan Bidang Keuangan dan Umum<br>
                Fakultas Keperawatan Universitas Riau<br>
                {{-- Ruang tanda tangan manual fisik basah --}}
                <div style="height: 48px;"></div>
                <strong style="text-decoration: underline;">{{ $pejabatWd2->nama_lengkap ?? ($pejabatWd2->nama ?? 'Dr. Safri, M.Kep., Sp.Kep.M.B') }}</strong><br>
                <span>NIP. {{ $pejabatWd2->nip ?? '198509092014041001' }}</span>
            </div>
        </td>
    </tr>
</table>

{{-- TEMBUSAN SURAT --}}
<div class="tembusan-box">
    <strong>Tembusan :</strong>
    <table style="width: 100%; border-collapse: collapse; margin-top: 1.5px; font-size: 8pt; line-height: 1.2;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 0;">
                1. Kemendiktisaintek & DIKTI di Jakarta<br>
                2. Dirjend Pendidikan Tinggi di Jakarta<br>
                3. Kepala BAKN di Jakarta<br>
                4. Rektor UNRI di Pekanbaru
            </td>
            <td style="width: 50%; vertical-align: top; padding: 0;">
                5. Bendaharawan UNRI di Pekanbaru<br>
                6. Pegawai Yang bersangkutan<br>
                7. Arsip
            </td>
        </tr>
    </table>
</div>

{{-- OTENTIKASI DIGITAL SIKAP FKP UNRI (PALING BAWAH, BEBAS OVERFLOW, TANPA TANDA '?') --}}
<div style="margin-top: 6px; border: 1px dashed #0284c7; padding: 3px 6px; border-radius: 4px; font-size: 6.5pt; line-height: 1.2; color: #0c4a6e; background-color: #f0f9ff; width: 100%; box-sizing: border-box; word-wrap: break-word; word-break: break-all;">
    <strong style="color: #0369a1; display: block; margin-bottom: 1px; font-size: 7pt;">OTENTIKASI DIGITAL SIKAP FKP UNRI:</strong>
    Dokumen resmi ini telah diverifikasi validitas kepegawaiannya dan diterbitkan secara elektronik melalui Sistem Informasi Kepegawaian (SIKAP) Fakultas Keperawatan Universitas Riau.<br>
    @if(isset($verifyUrl))
        Kode Verifikasi: <span style="font-family: monospace; font-size: 6pt; color: #0284c7;">{{ $verifyCode ?? 'SIKAP-KGB-VALID' }}</span> | 
        Tautan Verifikasi: <a href="{{ $verifyUrl }}" style="color: #0284c7; text-decoration: underline; font-size: 6pt;">{{ $verifyUrl }}</a>
    @endif
</div>
@endsection

{{-- Suppress default ttd from master layout --}}
@section('ttd')
@endsection