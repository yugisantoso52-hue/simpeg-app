<?php

namespace App\Http\Requests\Logbook;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'tanggal'            => ['required', 'date'],
            'jam_mulai'          => ['required', 'date_format:H:i'],
            'jam_selesai'        => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'kategori_kegiatan'  => ['required', 'string', 'max:100'],
            'aktivitas'          => ['required', 'string', 'max:255'],
            'deskripsi_kegiatan' => ['required', 'string'],
            'output_kegiatan'    => ['nullable', 'string', 'max:255'],
            'jumlah_output'      => ['required', 'integer', 'min:1'],
            'satuan_output'      => ['required', 'string', 'max:50'],
            'file_lampiran'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,docx,xlsx', 'max:10240'],
            'action'             => ['nullable', 'in:draft,diajukan'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required'       => 'Tanggal aktivitas wajib diisi.',
            'jam_mulai.required'     => 'Jam mulai wajib diisi.',
            'jam_selesai.required'   => 'Jam selesai wajib diisi.',
            'jam_selesai.after'      => 'Jam selesai harus lebih besar dari jam mulai.',
            'kategori_kegiatan.required' => 'Kategori kegiatan wajib dipilih.',
            'aktivitas.required'     => 'Ringkasan / nama aktivitas wajib diisi.',
            'deskripsi_kegiatan.required' => 'Deskripsi rincian kegiatan wajib diisi.',
            'jumlah_output.min'      => 'Jumlah output minimal 1.',
            'file_lampiran.max'      => 'Ukuran file lampiran maksimal 10 MB.',
            'file_lampiran.mimes'    => 'Format file lampiran harus PDF, JPG, PNG, DOCX, atau XLSX.',
        ];
    }
}
