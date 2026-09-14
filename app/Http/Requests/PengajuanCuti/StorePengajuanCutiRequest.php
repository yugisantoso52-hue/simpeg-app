<?php

namespace App\Http\Requests\PengajuanCuti;

use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanCutiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'jenis_cuti'         => 'required|in:Cuti Tahunan,Cuti Sakit,Cuti Melahirkan,Cuti Alasan Penting,Cuti Besar,Cuti di Luar Tanggungan Negara',
            'tanggal_mulai'      => 'required|date',
            'tanggal_selesai'    => 'required|date|after_or_equal:tanggal_mulai',
            'alasan'             => 'required|string|min:5',
            'alamat_selama_cuti' => 'nullable|string|max:255',
            'nomor_telepon'      => 'nullable|string|max:30',
            'file_lampiran'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ];

        // Jika user adalah admin, boleh memilih pegawai_id
        if ($this->user()->hasRole('admin')) {
            $rules['pegawai_id'] = 'required|exists:pegawai,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'jenis_cuti.required'             => 'Jenis cuti wajib dipilih.',
            'tanggal_mulai.required'          => 'Tanggal mulai cuti wajib diisi.',
            'tanggal_selesai.required'        => 'Tanggal selesai cuti wajib diisi.',
            'tanggal_selesai.after_or_equal'  => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'alasan.required'                 => 'Alasan cuti wajib diisi.',
            'alasan.min'                      => 'Alasan cuti minimal 5 karakter.',
            'file_lampiran.mimes'             => 'Format berkas lampiran harus PDF, JPG, JPEG, atau PNG.',
            'file_lampiran.max'               => 'Ukuran berkas lampiran maksimal 4MB.',
            'pegawai_id.required'             => 'Pegawai wajib dipilih.',
        ];
    }
}
