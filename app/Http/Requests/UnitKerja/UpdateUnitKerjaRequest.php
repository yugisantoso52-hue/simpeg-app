<?php

namespace App\Http\Requests\UnitKerja;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitKerjaRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk mengirim request ini.
     */
    public function authorize(): bool
    {
        return true; // Diubah ke true agar request diproses
    }

    /**
     * Aturan validasi yang berlaku untuk request.
     */
    public function rules(): array
    {
        // Mendapatkan ID unit kerja dari parameter route resource
        $id = $this->route('unit_kerja') ?? $this->route('unit-kerja');

        return [
            'kode_unit' => [
                'required',
                'string',
                'max:20',
                Rule::unique('unit_kerja', 'kode_unit')->ignore($id),
            ],
            'nama_unit'  => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
        ];
    }

    /**
     * Nama kustom untuk atribut validasi.
     */
    public function attributes(): array
    {
        return [
            'kode_unit' => 'Kode Unit',
            'nama_unit' => 'Nama Unit Kerja',
        ];
    }
}