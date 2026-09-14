<?php

namespace App\Http\Requests\RiwayatPublikasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiwayatPublikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pegawai_id'      => ['required', 'exists:pegawai,id'],
            'judul_publikasi' => ['required', 'string', 'max:1000'],
            'jenis_publikasi' => ['required', 'in:Jurnal,Prosiding,Buku,Book Chapter,Paten,HKI,Lainnya'],
            'nama_jurnal'     => ['nullable', 'string', 'max:200'],
            'penerbit'        => ['nullable', 'string', 'max:200'],
            'tahun_terbit'    => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'volume_nomor'    => ['nullable', 'string', 'max:50'],
            'url_doi'         => ['nullable', 'string', 'url', 'max:255'],
            'indeksasi'       => ['nullable', 'in:Scopus,WoS,SINTA 1,SINTA 2,SINTA 3,SINTA 4,SINTA 5,SINTA 6,Nasional Terakreditasi,Nasional Tidak Terakreditasi,Lainnya'],
            'keterangan'      => ['nullable', 'string'],
            'file_publikasi'  => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'pegawai_id'      => 'Pegawai',
            'judul_publikasi' => 'Judul Publikasi',
            'jenis_publikasi' => 'Jenis Publikasi',
            'nama_jurnal'     => 'Nama Jurnal / Prosiding',
            'penerbit'        => 'Penerbit',
            'tahun_terbit'    => 'Tahun Terbit',
            'volume_nomor'    => 'Volume / Nomor',
            'url_doi'         => 'URL / DOI',
            'indeksasi'       => 'Indeksasi',
            'file_publikasi'  => 'File Publikasi (PDF)',
            'keterangan'      => 'Keterangan',
        ];
    }
}
