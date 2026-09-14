<?php

namespace App\Http\Requests\RiwayatStrSip;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiwayatStrSipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pegawai_id'        => 'required|exists:pegawai,id',
            'jenis_dokumen'     => 'required|in:STR,SIP,SIKP',
            'nomor_registrasi'  => 'required|string|max:100',
            'nama_dokumen'      => 'nullable|string|max:150',
            'instansi_penerbit' => 'nullable|string|max:150',
            'tanggal_terbit'    => 'required|date',
            'tanggal_berakhir'  => 'nullable|required_if:is_seumur_hidup,0|date|after_or_equal:tanggal_terbit',
            'is_seumur_hidup'   => 'nullable|boolean',
            'status'            => 'nullable|string|max:40',
            'file_dokumen'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'keterangan'        => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'pegawai_id.required'              => 'Pegawai wajib dipilih.',
            'jenis_dokumen.required'           => 'Jenis dokumen (STR / SIP / SIKP) wajib dipilih.',
            'nomor_registrasi.required'        => 'Nomor STR / SIP wajib diisi.',
            'tanggal_terbit.required'          => 'Tanggal terbit wajib diisi.',
            'tanggal_berakhir.required_if'     => 'Tanggal berakhir wajib diisi jika bukan STR Seumur Hidup.',
            'tanggal_berakhir.after_or_equal'  => 'Tanggal berakhir tidak boleh sebelum tanggal terbit.',
            'file_dokumen.mimes'               => 'Format berkas harus PDF, JPG, JPEG, atau PNG.',
            'file_dokumen.max'                 => 'Ukuran berkas maksimal 4MB.',
        ];
    }
}
