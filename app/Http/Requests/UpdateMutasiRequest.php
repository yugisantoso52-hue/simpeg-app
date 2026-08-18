<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMutasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pegawai_id'      => ['required', 'exists:pegawai,id'],
            'unit_lama_id'    => ['required', 'exists:unit_kerja,id'],
            'unit_baru_id'    => ['required', 'exists:unit_kerja,id'],
            'jabatan_lama_id' => ['required', 'exists:jabatan,id'],
            'jabatan_baru_id' => ['required', 'exists:jabatan,id'],
            'tmt'             => ['required', 'date'],
            'nomor_sk'        => ['nullable', 'string', 'max:255'],
            'keterangan'      => ['nullable', 'string'],
            'file_sk'         => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'pegawai_id.required'      => 'Pegawai wajib dipilih.',
            'unit_lama_id.required'    => 'Unit kerja lama wajib diisi.',
            'unit_baru_id.required'    => 'Unit kerja baru wajib dipilih.',
            'jabatan_lama_id.required' => 'Jabatan lama wajib diisi.',
            'jabatan_baru_id.required' => 'Jabatan baru wajib dipilih.',
            'tmt.required'             => 'TMT mutasi wajib diisi.',
            'tmt.date'                 => 'Format TMT mutasi tidak valid.',
        ];
    }
}