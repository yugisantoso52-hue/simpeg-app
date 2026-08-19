<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;

class StorePegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Disesuaikan agar menerima 'nama_lengkap' atau 'nama'
            'nip'                  => 'required|string|max:50|unique:pegawai,nip',
            'nama_lengkap'         => 'nullable|string|max:150', 
            'nama'                 => 'nullable|string|max:150',
            'nik'                  => 'nullable|string|max:30',
            'gelar_depan'          => 'nullable|string|max:20',
            'gelar_belakang'       => 'nullable|string|max:20',
            'tempat_lahir'         => 'nullable|string|max:100',
            'tanggal_lahir'        => 'nullable|date',
            'jenis_kelamin'        => 'nullable|in:L,P',
            'agama'                => 'nullable|string|max:30',
            'pendidikan'           => 'nullable|string|max:100',
            'email'                => 'nullable|email|max:100',
            'no_hp'                => 'nullable|string|max:20',
            'alamat'               => 'nullable|string',
            'npwp'                 => 'nullable|string|max:50',
            'bpjs'                 => 'nullable|string|max:50',
            'jenis_pegawai'        => 'nullable|in:PNS,PPPK,Honorer',
            'status_asn'           => 'nullable|in:ASN,Non ASN',
            'status_pernikahan'    => 'nullable|in:Belum Menikah,Menikah,Cerai',
            'nama_pasangan'        => 'nullable|string|max:150',
            'jumlah_anak'          => 'nullable|integer|min:0',
            
            // Relasi Utama
            'unit_kerja_id'        => 'required|exists:unit_kerja,id',
            'jabatan_id'           => 'required|exists:jabatan,id',
            'golongan_id'          => 'nullable|exists:golongan,id',
            
            'tanggal_masuk'        => 'nullable|date',
            'tmt_sk_pertama'       => 'nullable|date',
            'tmt_pangkat_terakhir' => 'nullable|date',
            'tmt_kgb_terakhir'     => 'nullable|date',
            'status_pegawai'           => 'nullable|in:Aktif,Non Aktif,Pensiun',
            'foto'                     => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'file_sk_pertama'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'file_sk_pangkat_terakhir' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'file_sk_kgb_terakhir'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'gol_darah'            => 'nullable|in:A,B,AB,O',
            'tinggi_badan'         => 'nullable|integer|min:0',
            'berat_badan'          => 'nullable|integer|min:0',

            // Validasi Input Array Riwayat Pendidikan & File
            'pendidikan'                     => 'nullable|array',
            'pendidikan.jenjang'             => 'nullable|string|max:50',
            'pendidikan.institusi'           => 'nullable|string|max:255',
            'pendidikan.fakultas'            => 'nullable|string|max:150',
            'pendidikan.jurusan'             => 'nullable|string|max:150',
            'pendidikan.tahun_lulus'         => 'nullable|integer|digits:4',
            'pendidikan_ijazah'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Input Array Riwayat Diklat & File
            'diklat'                         => 'nullable|array',
            'diklat.nama_diklat'             => 'nullable|string|max:255',
            'diklat.jenis_diklat'            => 'nullable|string|max:100',
            'diklat.penyelenggara'           => 'nullable|string|max:255',
            'diklat.nomor_sertifikat'        => 'nullable|string|max:100',
            'diklat.tanggal_mulai'           => 'nullable|date',
            'diklat.tanggal_selesai'         => 'nullable|date',
            'diklat.status'                  => 'nullable|string|max:50',
            'diklat.keterangan'              => 'nullable|string',
            'diklat_sertifikat'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nama_lengkap') && !$this->has('nama')) {
            $this->merge([
                'nama' => $this->input('nama_lengkap'),
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'nip'               => 'NIP',
            'nama_lengkap'      => 'Nama Lengkap',
            'nama'              => 'Nama Lengkap',
            'unit_kerja_id'     => 'Unit Kerja',
            'jabatan_id'        => 'Jabatan',
            'golongan_id'       => 'Golongan/Pangkat',
            'status_pegawai'    => 'Status Kepegawaian',
            'foto'              => 'Foto Pegawai',
            'pendidikan_ijazah' => 'File Ijazah Pendidikan',
            'diklat_sertifikat' => 'File Sertifikat Diklat',
        ];
    }

    public function messages(): array
    {
        return [
            'nip.required'           => 'NIP wajib diisi.',
            'nip.unique'             => 'NIP sudah terdaftar.',
            'unit_kerja_id.required' => 'Unit Kerja wajib dipilih.',
            'jabatan_id.required'    => 'Jabatan wajib dipilih.',
        ];
    }
}