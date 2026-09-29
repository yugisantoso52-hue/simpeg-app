<?php

namespace App\Http\Requests\Logbook;

use Illuminate\Foundation\Http\FormRequest;

class VerifyLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(['admin', 'pimpinan']) || $user->isPimpinan() || $user->isAtasan();
    }

    public function rules(): array
    {
        return [
            'status'         => ['required', 'in:disetujui,perlu_revisi,ditolak'],
            'catatan_atasan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status verifikasi wajib dipilih.',
            'status.in'       => 'Pilihan status verifikasi tidak valid.',
        ];
    }
}
