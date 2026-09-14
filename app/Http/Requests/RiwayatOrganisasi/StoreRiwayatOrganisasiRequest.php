<?php

namespace App\Http\Requests\RiwayatOrganisasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiwayatOrganisasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pegawai_id'          => ['required', 'exists:pegawai,id'],
            'nama_organisasi'     => ['required', 'string', 'max:150'],
            'jabatan_organisasi'  => ['nullable', 'string', 'max:100'],
            'tahun_mulai'         => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'tahun_selesai'       => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1), 'gte:tahun_mulai'],
            'masih_aktif'         => ['boolean'],
            'keterangan'          => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'pegawai_id'         => 'Pegawai',
            'nama_organisasi'    => 'Nama Organisasi',
            'jabatan_organisasi' => 'Jabatan / Peran dalam Organisasi',
            'tahun_mulai'        => 'Tahun Mulai',
            'tahun_selesai'      => 'Tahun Selesai',
            'masih_aktif'        => 'Masih Aktif',
            'keterangan'         => 'Keterangan',
        ];
    }
}
