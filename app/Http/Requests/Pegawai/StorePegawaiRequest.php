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
            'nomor_sk_pertama'           => 'nullable|string|max:100',
            'tanggal_sk_pertama'         => 'nullable|date',
            'nomor_sk_pangkat_terakhir'   => 'nullable|string|max:100',
            'tanggal_sk_pangkat_terakhir' => 'nullable|date',
            'status_pegawai'           => 'nullable|in:Aktif,Non Aktif,Pensiun',
            'foto'                     => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'file_sk_pertama'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_sk_pangkat_terakhir' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_sk_kgb_terakhir'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'gol_darah'            => 'nullable|in:A,B,AB,O',
            'tinggi_badan'         => 'nullable|integer|min:0',
            'berat_badan'          => 'nullable|integer|min:0',

            // Validasi Input Multi-Riwayat Pendidikan
            'riwayat_pendidikan'                       => 'nullable|array',
            'riwayat_pendidikan.*.id'                  => 'nullable|integer',
            'riwayat_pendidikan.*.jenjang'             => 'required_with:riwayat_pendidikan.*.institusi|string|max:50',
            'riwayat_pendidikan.*.institusi'           => 'required_with:riwayat_pendidikan.*.jenjang|string|max:255',
            'riwayat_pendidikan.*.fakultas'            => 'nullable|string|max:150',
            'riwayat_pendidikan.*.jurusan'             => 'nullable|string|max:150',
            'riwayat_pendidikan.*.tahun_lulus'         => 'nullable|integer',
            'riwayat_pendidikan.*.ijazah'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Input Multi-Riwayat Pangkat
            'riwayat_pangkat'                          => 'nullable|array',
            'riwayat_pangkat.*.id'                     => 'nullable|integer',
            'riwayat_pangkat.*.golongan_id'            => 'required|exists:golongan,id',
            'riwayat_pangkat.*.tmt'                    => 'required|date',
            'riwayat_pangkat.*.nomor_sk'               => 'nullable|string|max:100',
            'riwayat_pangkat.*.tanggal_sk'             => 'nullable|date',
            'riwayat_pangkat.*.status'                 => 'required|in:aktif,riwayat,Aktif',
            'riwayat_pangkat.*.file_sk'                => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Input Multi-Riwayat Jabatan
            'riwayat_jabatan'                          => 'nullable|array',
            'riwayat_jabatan.*.id'                     => 'nullable|integer',
            'riwayat_jabatan.*.jabatan_id'             => 'required|exists:jabatan,id',
            'riwayat_jabatan.*.unit_kerja_id'          => 'required|exists:unit_kerja,id',
            'riwayat_jabatan.*.eselon'                 => 'nullable|string|max:50',
            'riwayat_jabatan.*.tmt_jabatan'            => 'required|date',
            'riwayat_jabatan.*.nomor_sk'               => 'nullable|string|max:100',
            'riwayat_jabatan.*.tanggal_sk'             => 'nullable|date',
            'riwayat_jabatan.*.status'                 => 'required|in:aktif,riwayat,Aktif',
            'riwayat_jabatan.*.file_sk'                => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Input Multi-Riwayat Diklat
            'riwayat_diklat'                           => 'nullable|array',
            'riwayat_diklat.*.id'                      => 'nullable|integer',
            'riwayat_diklat.*.nama_diklat'             => 'required|string|max:255',
            'riwayat_diklat.*.penyelenggara'           => 'nullable|string|max:255',
            'riwayat_diklat.*.jenis_diklat'            => 'nullable|string|max:100',
            'riwayat_diklat.*.tanggal_mulai'           => 'required|date',
            'riwayat_diklat.*.tanggal_selesai'         => 'required|date',
            'riwayat_diklat.*.jumlah_jam'              => 'nullable|integer|min:0',
            'riwayat_diklat.*.status'                  => 'required|string|max:50',
            'riwayat_diklat.*.file_sertifikat'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
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
            'nomor_sk_pertama'           => 'Nomor SK Pertama',
            'tanggal_sk_pertama'         => 'Tanggal SK Pertama',
            'nomor_sk_pangkat_terakhir'   => 'Nomor SK Pangkat Terakhir',
            'tanggal_sk_pangkat_terakhir' => 'Tanggal SK Pangkat Terakhir',
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