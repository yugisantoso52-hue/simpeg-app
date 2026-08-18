<?php

namespace App\Http\Requests\RiwayatPangkat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRiwayatPangkatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pegawai_id'       => 'required|exists:pegawai,id',
            'golongan_id'      => 'required|exists:golongan,id',
            'tmt'              => 'nullable|date',
            'nomor_sk'         => 'nullable|string|max:100',
            'status'           => 'nullable|in:aktif,nonaktif',
            'tanggal_berakhir' => 'nullable|date',
            'keterangan'       => 'nullable|string|max:255',
            'file_sk'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Maksimal 5MB
        ];
    }

    public function attributes(): array
    {
        return [
            'pegawai_id'  => 'Pegawai',
            'golongan_id' => 'Golongan',
            'tmt'         => 'TMT Pangkat',
            'nomor_sk'    => 'Nomor SK',
            'file_sk'     => 'File SK Pangkat',
        ];
    }
}