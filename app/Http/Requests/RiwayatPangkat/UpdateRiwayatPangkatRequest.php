<?php

namespace App\Http\Requests\RiwayatPangkat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRiwayatPangkatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole(['pegawai', 'pimpinan']) && $user->pegawai_id) {
            $id = $this->route('riwayat_pangkat') ?? $this->route('id');
            $riwayat = \App\Models\RiwayatPangkat::find($id);
            return $riwayat && (int) $riwayat->pegawai_id === (int) $user->pegawai_id;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'pegawai_id'       => 'required|exists:pegawai,id',
            'golongan_id'      => 'required|exists:golongan,id',
            'tmt'              => 'nullable|date',
            'nomor_sk'         => 'nullable|string|max:100',
            'tanggal_sk'       => 'nullable|date',
            'status'           => 'nullable|in:aktif,nonaktif',
            'tanggal_berakhir' => 'nullable|date',
            'keterangan'       => 'nullable|string|max:255',
            'file_sk'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Maksimal 5MB
        ];
    }

    public function attributes(): array
    {
        return [
            'pegawai_id'  => 'Pegawai',
            'golongan_id' => 'Golongan',
            'tmt'         => 'TMT Pangkat',
            'nomor_sk'    => 'Nomor SK',
            'tanggal_sk'  => 'Tanggal SK',
            'file_sk'     => 'File SK Pangkat',
        ];
    }
}