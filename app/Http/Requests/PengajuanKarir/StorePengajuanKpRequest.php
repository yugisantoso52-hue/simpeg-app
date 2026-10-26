<?php

namespace App\Http\Requests\PengajuanKarir;

use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanKpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->pegawai !== null;
    }

    public function rules(): array
    {
        return [
            'jenis_pengajuan'          => 'required|in:KP',
            'periode_kp'               => 'required|string|in:Februari,April,Juni,Agustus,Oktober,Desember',
            'tahun_periode'            => 'required|integer|min:2020|max:2100',
            'golongan_tujuan_id'       => 'required|exists:golongan,id',
            'file_sk_pangkat_terakhir' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_sk_terakhir'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_skp_1'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_skp_2'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_karpeg'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_pak'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_pendukung'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'catatan_pegawai'          => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'periode_kp.required'         => 'Periode Kenaikan Pangkat wajib dipilih.',
            'tahun_periode.required'      => 'Tahun pengusulan Kenaikan Pangkat wajib diisi.',
            'golongan_tujuan_id.required' => 'Golongan ruang yang diusulkan wajib dipilih.',
            'golongan_tujuan_id.exists'   => 'Golongan ruang tujuan tidak valid.',
        ];
    }
}
