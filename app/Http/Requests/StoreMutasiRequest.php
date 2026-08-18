<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMutasiRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        // Diubah menjadi true agar request tidak diblokir oleh sistem otentikasi FormRequest
        return true; 
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request ini.
     */
    public function rules(): array
    {
        return [
            'pegawai_id'      => ['required', 'exists:pegawai,id'],
            'unit_lama_id'    => ['required', 'exists:unit_kerja,id'],
            'unit_baru_id'    => ['required', 'exists:unit_kerja,id'],
            'jabatan_lama_id' => ['required', 'exists:jabatan,id'],
            'jabatan_baru_id' => ['required', 'exists:jabatan,id'],
            'tmt'             => ['required', 'date'],
            'nomor_sk'        => ['nullable', 'string', 'max:100'],
            'file_sk'         => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'keterangan'      => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Kustomisasi pesan error (Menampilkan pesan informatif berbahasa Indonesia).
     */
    public function messages(): array
    {
        return [
            'pegawai_id.required'      => 'Pegawai harus dipilih.',
            'pegawai_id.exists'        => 'Pegawai tidak terdaftar di sistem.',
            'unit_lama_id.required'    => 'Unit kerja asal harus dipilih.',
            'unit_lama_id.exists'      => 'Unit kerja asal tidak valid.',
            'unit_baru_id.required'    => 'Unit kerja tujuan harus dipilih.',
            'unit_baru_id.exists'      => 'Unit kerja tujuan tidak valid.',
            'jabatan_lama_id.required' => 'Jabatan asal harus dipilih.',
            'jabatan_lama_id.exists'   => 'Jabatan asal tidak valid.',
            'jabatan_baru_id.required' => 'Jabatan baru harus dipilih.',
            'jabatan_baru_id.exists'   => 'Jabatan baru tidak valid.',
            'tmt.required'             => 'TMT Mutasi wajib diisi.',
            'tmt.date'                 => 'Format tanggal TMT tidak valid.',
            'file_sk.file'             => 'File SK harus berupa berkas dokumen.',
            'file_sk.mimes'            => 'File SK harus berformat PDF, JPG, JPEG, atau PNG.',
            'file_sk.max'              => 'Ukuran file SK tidak boleh lebih dari 2MB.',
            'keterangan.max'           => 'Keterangan tidak boleh lebih dari 255 karakter.',
        ];
    }
}