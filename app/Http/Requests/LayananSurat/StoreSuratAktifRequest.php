<?php

namespace App\Http\Requests\LayananSurat;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratAktifRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'keperluan'          => 'required|string|max:500',
            'pegawai_id'         => 'nullable|exists:pegawai,id',
            'nomor_surat_manual' => 'nullable|string|max:100',
            'penandatangan_role' => 'nullable|in:dekan,wadek2',
            'langsung_sahkan'    => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'keperluan.required' => 'Keperluan penerbitan Surat Keterangan Aktif Bekerja wajib diisi.',
            'keperluan.max'      => 'Keperluan maksimal 500 karakter.',
            'pegawai_id.exists'  => 'Pegawai yang dipilih tidak terdaftar.',
        ];
    }
}
