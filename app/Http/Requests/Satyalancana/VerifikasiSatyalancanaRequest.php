<?php

namespace App\Http\Requests\Satyalancana;

use Illuminate\Foundation\Http\FormRequest;

class VerifikasiSatyalancanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->canAccessExecutiveKepegawaianMenus();
    }

    public function rules(): array
    {
        return [
            'tahap'             => 'required|string|in:verifikasi_admin,paraf_kapokja,paraf_kabag,ttd_wd2,setujui,tolak',
            'catatan'           => 'nullable|string|max:500',
            'nomor_surat_input' => 'nullable|string|max:100',
        ];
    }
}
