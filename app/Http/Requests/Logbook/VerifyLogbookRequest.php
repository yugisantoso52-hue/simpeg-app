<?php

namespace App\Http\Requests\Logbook;

use Illuminate\Foundation\Http\FormRequest;

class VerifyLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'pimpinan']);
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
