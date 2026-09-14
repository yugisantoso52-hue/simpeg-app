<?php

namespace App\Http\Requests\RiwayatPenghargaan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRiwayatPenghargaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pegawai_id'       => ['required', 'exists:pegawai,id'],
            'nama_penghargaan' => ['required', 'string', 'max:150'],
            'jenis_penghargaan'=> ['nullable', 'string', 'max:100'],
            'instansi_pemberi' => ['nullable', 'string', 'max:150'],
            'tanggal_terima'   => ['nullable', 'date'],
            'nomor_sk'         => ['nullable', 'string', 'max:100'],
            'keterangan'       => ['nullable', 'string'],
            'file_sk'          => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'pegawai_id'       => 'Pegawai',
            'nama_penghargaan' => 'Nama Penghargaan',
            'jenis_penghargaan'=> 'Jenis Penghargaan',
            'instansi_pemberi' => 'Instansi Pemberi',
            'tanggal_terima'   => 'Tanggal Diterima',
            'nomor_sk'         => 'Nomor SK',
            'file_sk'          => 'File SK / Sertifikat',
            'keterangan'       => 'Keterangan',
        ];
    }
}
