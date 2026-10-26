<?php

namespace App\Http\Requests\PengajuanKarir;

use Illuminate\Foundation\Http\FormRequest;

class VerifikasiPengajuanKarirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->canAccessExecutiveKepegawaianMenus();
    }

    public function rules(): array
    {
        return [
            'tahap'              => 'required|string|in:verifikasi_admin,paraf_kapokja,paraf_kabag,paraf_wd2,ttd_wd2,ttd_dekan,tolak',
            'catatan'            => 'nullable|string|max:500',
            'nomor_surat_input'  => 'nullable|string|max:100',
            'nomor_surat_manual' => 'nullable|string|max:100',
        ];
    }
}
