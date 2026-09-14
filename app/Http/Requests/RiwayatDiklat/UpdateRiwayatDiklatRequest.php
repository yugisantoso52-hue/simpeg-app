<?php

namespace App\Http\Requests\RiwayatDiklat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRiwayatDiklatRequest extends FormRequest
{
    /**
     * Authorization
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [

            'pegawai_id' => [
                'required',
                'exists:pegawai,id',
            ],

            'nama_diklat' => [
                'required',
                'string',
                'max:255',
            ],

            'penyelenggara' => [
                'nullable',
                'string',
                'max:255',
            ],

            'tanggal_mulai' => [
                'nullable',
                'date',
            ],

            'tanggal_selesai' => [
                'nullable',
                'date',
                'after_or_equal:tanggal_mulai',
            ],

            'jumlah_jam' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'nomor_sertifikat' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'nullable',
                'in:Aktif,Tidak Aktif',
            ],

            'keterangan' => [
                'nullable',
                'string',
            ],

            'file_sertifikat' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

        ];
    }

    /**
     * Attribute Names
     */
    public function attributes(): array
    {
        return [

            'pegawai_id'        => 'Pegawai',

            'nama_diklat'       => 'Nama Diklat',

            'penyelenggara'     => 'Penyelenggara',

            'tanggal_mulai'     => 'Tanggal Mulai',

            'tanggal_selesai'   => 'Tanggal Selesai',

            'jumlah_jam'        => 'Jumlah Jam',

            'nomor_sertifikat'  => 'Nomor Sertifikat',

            'status'            => 'Status',

            'file_sertifikat'   => 'File Sertifikat',

            'keterangan'        => 'Keterangan',

        ];
    }
}