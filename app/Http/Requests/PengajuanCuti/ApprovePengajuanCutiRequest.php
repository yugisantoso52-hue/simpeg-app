<?php

namespace App\Http\Requests\PengajuanCuti;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePengajuanCutiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        // Admin dan Pimpinan selalu bisa
        if ($user->hasRole(['admin', 'pimpinan'])) {
            return true;
        }

        // Atasan Langsung juga bisa memberikan pertimbangan
        return $user->isAtasan();
    }

    public function rules(): array
    {
        return [
            'status'           => 'required|in:Disetujui,Ditolak',
            'catatan_pimpinan' => 'nullable|string|max:500',
            'nomor_surat'      => 'nullable|string|max:100',
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
