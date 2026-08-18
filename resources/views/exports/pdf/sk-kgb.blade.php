@extends('exports.pdf.master')

@section('title', 'Surat Pemberitahuan Kenaikan Gaji Berkala')

@section('content')
<table style="width: 100%; font-size: 10pt; margin-bottom: 15px;">
    <tr>
        <td style="width: 12%;">Nomor</td>
        <td style="width: 2%;">:</td>
        <td style="width: 46%;">800 / KGB / {{ date('Y') }} / {{ $kgb->id }}</td>
        <td style="width: 40%; text-align: right;">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td>Sifat</td>
        <td>:</td>
        <td>Biasa</td>
        <td></td>
    </tr>
    <tr>
        <td>Lampiran</td>
        <td>:</td>
        <td>-</td>
        <td style="text-align: right;">Kepada Yth.</td>
    </tr>
    <tr>
        <td>Hal</td>
        <td>:</td>
        <td><strong>Kenaikan Gaji Berkala</strong></td>
        <td style="text-align: right;"><strong>Kepala BPKAD / Kasda</strong></td>
    </tr>
    <tr>
        <td colspan="3"></td>
        <td style="text-align: right;">di - Tempat</td>
    </tr>
</table>

<p style="text-indent: 30px; text-align: justify;">
    Dengan ini diberitahukan bahwa berhubung dengan telah dipenuhinya masa kerja dan syarat-syarat lainnya, kepada Pegawai Negeri Sipil yang tersebut di bawah ini:
</p>

<table style="width: 95%; margin: 10px auto; font-size: 10pt;">
    <tr>
        <td style="width: 30%;">1. Nama</td>
        <td style="width: 3%;">:</td>
        <td><strong>{{ $kgb->pegawai->nama }}</strong></td>
    </tr>
    <tr>
        <td>2. N I P</td>
        <td>:</td>
        <td>{{ $kgb->pegawai->nip }}</td>
    </tr>
    <tr>
        <td>3. Pangkat / Golongan</td>
        <td>:</td>
        <td>{{ $kgb->pegawai->golonganLatest->pangkat ?? '-' }} ({{ $kgb->pegawai->golonganLatest->nama ?? '-' }})</td>
    </tr>
    <tr>
        <td>4. Jabatan / Unit Kerja</td>
        <td>:</td>
        <td>{{ $kgb->pegawai->jabatanLatest->nama ?? '-' }} / {{ $kgb->pegawai->unitKerja->nama ?? '-' }}</td>
    </tr>
    <tr>
        <td>5. Gaji Pokok Lama</td>
        <td>:</td>
        <td>Rp {{ number_format($kgb->gaji_lama, 0, ',', '.') }}</td>
    </tr>
</table>

<p style="text-indent: 30px; text-align: justify;">
    Diberikan <strong>Kenaikan Gaji Berkala</strong> hingga memperoleh gaji pokok baru sebesar:
</p>

<div style="background-color: #f8f9fa; border: 1px solid #ccc; padding: 10px; margin: 10px 0; text-align: center; font-size: 12pt;">
    <strong>Rp {{ number_format($kgb->gaji_baru, 0, ',', '.') }}</strong><br>
    <span style="font-size: 9pt; font-style: italic;">( Terbilang: {{ ucwords(\Illuminate\Support\Str::headline($kgb->terbilang_gaji_baru ?? '')) }} Rupiah )</span>
</div>

<p style="text-indent: 30px; text-align: justify;">
    Berdasarkan Masa Kerja Golongan: <strong>{{ $kgb->masa_kerja_tahun }} Tahun {{ $kgb->masa_kerja_bulan }} Bulan</strong>, Terhitung Mulai Tanggal (TMT): <strong>{{ \Carbon\Carbon::parse($kgb->tmt_kgb_baru)->translatedFormat('d F Y') }}</strong>.
</p>
@endsection