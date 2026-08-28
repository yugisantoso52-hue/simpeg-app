<?php

namespace App\Http\Requests\TugasBelajar;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTugasBelajarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['admin', 'pegawai']);
    }

    public function rules(): array
    {
        return [
            'pegawai_id'            => 'required|exists:pegawai,id',
            'jenis_pengembangan'    => 'required|in:Tugas Belajar,Izin Belajar',
            'jenjang_studi'         => 'required|in:S2,S3,Spesialis,Subspesialis,Post Doctoral',
            'program_studi'         => 'required|string|max:150',
            'perguruan_tinggi'      => 'required|string|max:150',
            'negara'                => 'required|string|max:100',
            'sumber_pembiayaan'     => 'required|string|max:100',
            'nama_sponsor'          => 'nullable|string|max:150',
            'nomor_sk'              => 'required|string|max:100',
            'tanggal_sk'            => 'nullable|date',
            'tanggal_mulai'         => 'required|date',
            'tanggal_selesai'       => 'required|date|after_or_equal:tanggal_mulai',
            'semester_berjalan'     => 'required|integer|min:1|max:20',
            'status_studi'          => 'required|in:Sedang Studi,Perpanjangan,Lulus,Dibatalkan / DO',
            'file_sk'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_laporan_progress' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'keterangan'            => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'pegawai_id.required'             => 'Pegawai wajib dipilih.',
            'jenis_pengembangan.required'     => 'Jenis pengembangan (Tugas / Izin Belajar) wajib dipilih.',
            'jenjang_studi.required'          => 'Jenjang studi wajib dipilih.',
            'program_studi.required'          => 'Program studi wajib diisi.',
            'perguruan_tinggi.required'       => 'Perguruan tinggi tujuan wajib diisi.',
            'nomor_sk.required'               => 'Nomor SK Tugas Belajar wajib diisi.',
            'tanggal_mulai.required'          => 'Tanggal mulai studi wajib diisi.',
            'tanggal_selesai.required'        => 'Target tanggal selesai studi wajib diisi.',
            'tanggal_selesai.after_or_equal'  => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'file_sk.mimes'                   => 'Format berkas SK harus PDF, JPG, JPEG, atau PNG.',
            'file_sk.max'                     => 'Ukuran berkas SK maksimal 4MB.',
        ];
    }
}
