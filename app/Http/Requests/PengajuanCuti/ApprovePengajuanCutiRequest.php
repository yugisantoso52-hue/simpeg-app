<?php

namespace App\Http\Requests\PengajuanCuti;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePengajuanCutiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Admin Screening (Tahap 1) dan Ka Pokja Keu-Kepeg (Tahap 2) menggunakan endpoint verifikasi,
        // BUKAN endpoint approve (Tahap 3 Pertimbangan Atasan & Tahap 4 Keputusan PYBMC)
        if ($user->isScreeningAdmin() || $user->isKaPokjaKepeg()) {
            return false;
        }

        if ($user->hasRole('pimpinan')) {
            return true;
        }

        // Atasan Langsung / Pejabat Berwenang
        return $user->isAtasan();
    }

    public function rules(): array
    {
        return [
            'status'                  => 'required|in:Disetujui,Ditolak',
            'catatan_atasan_langsung' => 'nullable|string|max:500',
            'catatan_pimpinan'        => 'nullable|string|max:500',
            'nomor_surat'             => 'nullable|string|max:100',
            'nomor_surat_input'       => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Keputusan persetujuan wajib ditentukan (Disetujui atau Ditolak).',
            'status.in'       => 'Status keputusan harus Disetujui atau Ditolak.',
        ];
    }
}
