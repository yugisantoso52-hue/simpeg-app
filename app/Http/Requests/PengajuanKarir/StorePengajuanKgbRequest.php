<?php

namespace App\Http\Requests\PengajuanKarir;

use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanKgbRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->pegawai !== null;
    }

    public function rules(): array
    {
        return [
            'jenis_pengajuan'          => 'required|in:KGB',
            'tmt_baru'                 => 'nullable|date',
            'file_sk_pangkat_terakhir' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_sk_kgb_terakhir'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_sk_terakhir'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_skp_1'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_skp_2'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_pendukung'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'catatan_pegawai'          => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'tmt_baru.date'                   => 'Format tanggal TMT KGB Baru tidak valid.',
            'file_sk_pangkat_terakhir.mimes'  => 'Format berkas SK Pangkat harus PDF, JPG, JPEG, atau PNG.',
            'file_sk_kgb_terakhir.mimes'      => 'Format berkas SK KGB harus PDF, JPG, JPEG, atau PNG.',
        ];
    }
}
