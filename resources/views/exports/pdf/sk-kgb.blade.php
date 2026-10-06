@extends('exports.pdf.master')

@section('title', 'Surat Pemberitahuan Kenaikan Gaji Berkala - ' . ($pegawai->nama_lengkap ?? $pegawai->nama))

@push('styles')
<style>
    .surat-header-table {
        width: 100%;
        margin-bottom: 12px;
        font-size: 10pt;
    }
    .surat-header-table td {
        vertical-align: top;
        padding: 1px 0;
    }
    .p-isi {
        text-align: justify;
        line-height: 1.3;
        margin: 6px 0;
        text-indent: 30px;
        font-size: 10pt;
    }
    .tabel-identitas {
        width: 96%;
        margin: 6px auto;
        border-collapse: collapse;
        font-size: 10pt;
    }
    .tabel-identitas td {
        padding: 2.5px 2px;
        vertical-align: top;
    }
    .box-gaji-baru {
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 8px;
        margin: 8px 0;
        text-align: center;
        font-size: 11pt;
    }
    .ttd-paraf-table {
        width: 100%;
        margin-top: 15px;
        page-break-inside: avoid;
        border-collapse: collapse;
    }
    .paraf-hierarki-box {
        border: 1px solid #475569;
        padding: 4px 6px;
        border-radius: 4px;
        font-size: 7.5pt;
        width: 90%;
        background-color: #fafafa;
    }
    .paraf-hierarki-box table {
        width: 100%;
        border-collapse: collapse;
        font-size: 7.5pt;
    }
    .paraf-hierarki-box td {
        padding: 2px 2px;
        border-bottom: 1px dashed #cbd5e1;
    }
    .paraf-hierarki-box tr:last-child td {
        border-bottom: none;
    }
    .badge-paraf {
        color: #047857;
        font-weight: bold;
    }
    .tembusan-box {
        font-size: 8.5pt;
        line-height: 1.2;
        margin-top: 12px;
    }
</style>
@endpush

@section('content')
<table class="surat-header-table">
    <tr>
        <td style="width: 13%;">Nomor</td>
        <td style="width: 2%;">:</td>
        <td style="width: 45%;">B/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/UN19.5.1.1.10/KP/{{ date('Y') }}</td>
        <td style="width: 40%; text-align: right;">Pekanbaru, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td>Lampiran</td>
        <td>:</td>
        <td>-</td>
        <td></td>
    </tr>
    <tr>
        <td>Hal</td>
        <td>:</td>
        <td><strong>Kenaikan Gaji Berkala {{ $isPppk ? 'PPPK' : 'PNS' }}</strong></td>
        <td style="text-align: right; vertical-align: top;">
            Kepada Yth.<br>
            <strong>Kepala Kantor Pelayanan Perbendaharaan Negara (KPPN) Pekanbaru</strong><br>
            (Kepala Seksi Pencairan Dana / Pembelanjaan)<br>
            di Pekanbaru
        </td>
    </tr>
</table>

<p class="p-isi">
    Dengan ini diberitahukan bahwa berhubung telah dipenuhinya masa kerja dan syarat-syarat lainnya sesuai dengan ketentuan perundang-undangan yang berlaku, kepada {{ $isPppk ? 'Pegawai Pemerintah dengan Perjanjian Kerja (PPPK)' : 'Pegawai Negeri Sipil' }}:
</p>

<table class="tabel-identitas">
    <tr>
        <td style="width: 32%;">1. Nama Pegawai</td>
        <td style="width: 3%;">:</td>
        <td><strong>{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</strong></td>
    </tr>
    <tr>
        <td>2. {{ $isPppk ? 'Nomor Induk PPPK' : 'N I P' }}</td>
        <td>:</td>
        <td>{{ $pegawai->nip }}</td>
    </tr>
    <tr>
        <td>3. {{ $isPppk ? 'Golongan / Jabatan' : 'Pangkat / Golongan Ruang' }}</td>
        <td>:</td>
        <td>
            @if($isPppk)
                {{ $pegawai->golongan->nama_golongan ?? 'Golongan IX' }} / {{ $pegawai->jabatan->nama_jabatan ?? 'Tenaga Fungsional' }}
            @else
                {{ $pegawai->golongan->nama_pangkat ?? 'Penata' }} / {{ $pegawai->golongan->nama_golongan ?? 'III/c' }}
            @endif
        </td>
    </tr>
    <tr>
        <td>4. Unit Kerja</td>
        <td>:</td>
        <td>{{ $pegawai->unitKerja->nama_unit ?? 'Fakultas Keperawatan Universitas Riau' }}</td>
    </tr>
    <tr>
        <td>5. Gaji Pokok Lama</td>
        <td>:</td>
        <td><strong>Rp {{ number_format($kgb->gaji_lama, 0, ',', '.') }}</strong></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td style="font-size: 9pt; color: #475569;">
            (Atas dasar penetapan gaji / SK terakhir nomor: {{ $pegawai->nomor_sk_kgb_terakhir ?? '827/UN19.5.1.1.10/KP/2024' }} tanggal {{ $pegawai->tanggal_sk_kgb_terakhir ? \Carbon\Carbon::parse($pegawai->tanggal_sk_kgb_terakhir)->translatedFormat('d F Y') : '-' }} mulai berlaku TMT {{ $pegawai->tmt_kgb_terakhir ? \Carbon\Carbon::parse($pegawai->tmt_kgb_terakhir)->translatedFormat('d F Y') : '-' }} masa kerja {{ $kgb->masa_kerja_tahun - 2 >= 0 ? $kgb->masa_kerja_tahun - 2 : 0 }} Tahun)
        </td>
    </tr>
</table>

<p class="p-isi" style="margin-top: 4px;">
    Diberikan <strong>Kenaikan Gaji Berkala</strong> hingga memperoleh gaji pokok baru sebesar:
</p>

<div class="box-gaji-baru">
    <strong style="font-size: 13pt; color: #0f172a;">Rp {{ number_format($kgb->gaji_baru, 0, ',', '.') }}</strong><br>
    <span style="font-size: 9pt; font-style: italic; color: #475569;">
        ( Terbilang: {{ ucwords(\Illuminate\Support\Str::headline($kgb->terbilang_gaji_baru ?? '')) }} Rupiah )
    </span>
</div>

<table class="tabel-identitas" style="margin-top: 4px;">
    <tr>
        <td style="width: 32%;">6. Berdasarkan Masa Kerja</td>
        <td style="width: 3%;">:</td>
        <td><strong>{{ $kgb->masa_kerja_tahun }} Tahun {{ $kgb->masa_kerja_bulan }} Bulan</strong></td>
    </tr>
    <tr>
        <td>7. Dalam Golongan Ruang</td>
        <td>:</td>
        <td><strong>{{ $pegawai->golongan->nama_golongan ?? ($isPppk ? 'Golongan IX' : 'III/c') }}</strong></td>
    </tr>
    <tr>
        <td>8. Terhitung Mulai Tanggal (TMT)</td>
        <td>:</td>
        <td><strong>{{ \Carbon\Carbon::parse($kgb->tmt_kgb_baru)->translatedFormat('d F Y') }}</strong></td>
    </tr>
    <tr>
        <td>9. Kenaikan Gaji Berkala Berikutnya</td>
        <td>:</td>
        <td><strong>{{ \Carbon\Carbon::parse($kgb->tmt_kgb_baru)->addYears(2)->translatedFormat('d F Y') }}</strong></td>
    </tr>
</table>

<p class="p-isi" style="margin-top: 8px;">
    Diharapkan agar sesuai dengan {{ $isPppk ? 'Peraturan Presiden Nomor 11 Tahun 2024 dan PermenPAN-RB No. 7 Tahun 2023' : 'Peraturan Pemerintah Nomor 5 Tahun 2024' }}, kepada pegawai tersebut dapat dibayarkan penghasilan berdasarkan gaji pokok yang baru.
</p>

{{-- AREA PENANDATANGANAN DEKAN & PARAF KOORDINASI TATA USAHA --}}
<table class="ttd-paraf-table">
    <tr>
        {{-- Sisi Kiri: Kotak Paraf Koordinasi --}}
        <td style="width: 52%; vertical-align: bottom;">
            <div class="paraf-hierarki-box">
                <div style="font-weight: bold; margin-bottom: 2px; color: #0f172a;">
                    PARAF KOORDINASI HIRARKI ADMINISTRASI:
                </div>
                <table>
                    <tr>
                        <td style="width: 35%;">1. Kabag Umum</td>
                        <td style="width: 45%;">: {{ $pejabatKabag->nama ?? 'Bakhtiar' }}, S.Sos., M.Si</td>
                        <td style="width: 20%; text-align: right;">
                            @if(isset($pengajuan) && $pengajuan->paraf_kabag_at)
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
                            @if(isset($pengajuan) && $pengajuan->paraf_wd2_at)
                                <span class="badge-paraf">[ Paraf ✓ ]</span>
                            @else
                                <span style="color: #64748b;">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            @if(isset($verifyUrl))
            <div style="margin-top: 5px; font-size: 7pt; color: #475569; border-left: 2px solid #2563eb; padding-left: 5px;">
                <strong>Otentikasi Digital SIKAP FKP UNRI:</strong><br>
                <span>Dokumen sah terverifikasi: <a href="{{ $verifyUrl }}" style="color: #2563eb;">{{ $verifyUrl }}</a></span>
            </div>
            @endif
        </td>

        {{-- Sisi Kanan: Tanda Tangan Dekan --}}
        <td style="width: 48%; text-align: center; vertical-align: top;">
            <div style="font-size: 10pt; line-height: 1.25;">
                Dekan Fakultas Keperawatan<br>
                Universitas Riau,
                <br><br><br><br>
                <strong style="text-decoration: underline;">Prof. {{ $pejabatDekan->nama ?? 'Wan Nishfa Dewi' }}, S.Kp., MNg., PhD</strong><br>
                <span>NIP. {{ $pejabatDekan->nip ?? '197508222001122001' }}</span>
            </div>
        </td>
    </tr>
</table>

<div class="tembusan-box">
    <strong>Tembusan Yth:</strong>
    <ol style="margin: 2px 0 0 16px; padding: 0;">
        <li>Dirjen Diktiristek Kemendiktisaintek di Jakarta;</li>
        <li>Kepala Kantor Regional XII BKN di Pekanbaru;</li>
        <li>Rektor Universitas Riau di Pekanbaru;</li>
        <li>Bendaharawan Universitas Riau di Pekanbaru;</li>
        <li>Pegawai yang bersangkutan;</li>
        <li>Arsip.</li>
    </ol>
</div>
@endsection