<?php

namespace App\Http\Requests\RiwayatJabatan;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiwayatJabatanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pegawai_id' => [
                'required',
                'exists:pegawai,id',
            ],

            'jabatan_id' => [
                'required',
                'exists:jabatan,id',
            ],

            'unit_kerja_id' => [
                'required',
                'exists:unit_kerja,id',
            ],

            'tmt_jabatan' => [
                'nullable',
                'date',
            ],

            'nomor_sk' => [
                'nullable',
                'string',
                'max:100',
            ],

            'file_sk' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048',
            ],

            'keterangan' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'pegawai_id'    => 'pegawai',
            'jabatan_id'    => 'jabatan',
            'unit_kerja_id' => 'unit kerja',
            'tmt_jabatan'   => 'TMT jabatan',
            'nomor_sk'      => 'nomor SK',
            'file_sk'       => 'file SK',
            'keterangan'    => 'keterangan',
        ];
    }
}