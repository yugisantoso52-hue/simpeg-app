<?php

namespace App\Http\Requests\LayananSurat;

use Illuminate\Foundation\Http\FormRequest;

class VerifikasiSuratAktifRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->canAccessExecutiveKepegawaianMenus();
    }

    public function rules(): array
    {
        return [
            'tahap'              => 'required|string|in:verifikasi_admin,paraf_kapokja,paraf_kabag,sahkan_pimpinan,ttd_wd2,ttd_dekan,tolak',
            'catatan'            => 'nullable|string|max:500',
            'nomor_surat_manual' => 'nullable|string|max:100',
        ];
    }
}
