<?php

namespace App\Http\Requests\RiwayatSkp;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRiwayatSkpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'pegawai_id'        => 'required|exists:pegawai,id',
            'tahun'             => [
                'required',
                'integer',
                'min:1990',
                'max:2099',
                Rule::unique('riwayat_skp', 'tahun')->where(function ($query) {
                    return $query->where('pegawai_id', $this->pegawai_id);
                }),
            ],
            'predikat_kinerja'  => 'nullable|in:Sangat Baik,Baik,Butuh Perbaikan,Kurang,Sangat Kurang',
            'file_rencana_skp'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'file_evaluasi_skp' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'pejabat_penilai'   => 'nullable|string|max:150',
            'keterangan'        => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'pegawai_id.required'        => 'Pegawai wajib dipilih.',
            'tahun.required'             => 'Tahun SKP wajib diisi.',
            'tahun.unique'               => 'Data SKP untuk pegawai dan tahun tersebut sudah pernah dicatat. Silakan lakukan edit data.',
            'predikat_kinerja.in'        => 'Predikat kinerja harus Sangat Baik, Baik, Butuh Perbaikan, Kurang, atau Sangat Kurang.',
            'file_rencana_skp.mimes'     => 'Berkas Rencana SKP harus berformat PDF, JPG, JPEG, atau PNG.',
            'file_rencana_skp.max'       => 'Ukuran Berkas Rencana SKP maksimal 5MB.',
            'file_evaluasi_skp.mimes'    => 'Berkas Evaluasi SKP harus berformat PDF, JPG, JPEG, atau PNG.',
            'file_evaluasi_skp.max'      => 'Ukuran Berkas Evaluasi SKP maksimal 5MB.',
        ];
    }
}
