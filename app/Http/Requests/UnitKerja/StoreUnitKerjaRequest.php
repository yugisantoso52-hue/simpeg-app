<?php

namespace App\Http\Requests\UnitKerja;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitKerjaRequest extends FormRequest
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
        return [
            'kode_unit'  => 'required|string|max:20|unique:unit_kerja,kode_unit',
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