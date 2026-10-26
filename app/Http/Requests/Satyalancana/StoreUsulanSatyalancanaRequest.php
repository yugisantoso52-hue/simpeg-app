<?php

namespace App\Http\Requests\Satyalancana;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsulanSatyalancanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'kategori'       => 'nullable|string|in:10 Tahun,20 Tahun,30 Tahun,X Tahun,XX Tahun,XXX Tahun',
            'periode_usulan' => 'nullable|string|max:100',
            'catatan'        => 'nullable|string|max:500',
        ];
    }
}
