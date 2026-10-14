<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('nama_lengkap') && !$this->filled('nama')) {
            $this->merge([
                'nama' => $this->input('nama_lengkap'),
            ]);
        } elseif ($this->filled('nama') && !$this->filled('nama_lengkap')) {
            $this->merge([
                'nama_lengkap' => $this->input('nama'),
            ]);
        }
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Keamanan: Jika pengguna bukan Admin, cegah privilege escalation
        // Pegawai biasa hanya boleh memperbarui identitas pribadi, kontak, domisili, keluarga & foto
        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            unset(
                $data['nip'],
                $data['unit_kerja_id'],
                $data['jabatan_id'],
                $data['golongan_id'],
                $data['atasan_id'],
                $data['pejabat_penilai_id'],
                $data['atasan_cuti_id'],
                $data['status_pegawai'],
                $data['status_asn'],
                $data['jenis_pegawai'],
                $data['jenis_jabatan'],
                $data['angka_kredit'],
                $data['batas_usia_pensiun'],
                $data['tanggal_pensiun'],
                $data['no_sk_pensiun'],
                $data['tmt_pensiun'],
                $data['jenis_kontrak'],
                $data['tanggal_kontrak_mulai'],
                $data['tanggal_kontrak_selesai'],
                $data['tmt_sk_pertama'],
                $data['tmt_pangkat_terakhir'],
                $data['tmt_kgb_terakhir'],
                $data['nomor_sk_pertama'],
                $data['tanggal_sk_pertama'],
                $data['nomor_sk_pangkat_terakhir'],
                $data['tanggal_sk_pangkat_terakhir'],
                $data['nomor_pak'],
                $data['tanggal_pak'],
                $data['file_sk_pertama'],
                $data['file_sk_pangkat_terakhir'],
                $data['file_sk_kgb_terakhir'],
                $data['file_pak']
            );
        }

        return $data;
    }

    public function rules(): array
    {
        // Mengambil ID dari parameter route binding (/pegawai/{id} atau /pegawai/{pegawai})
        $pegawai = $this->route('pegawai');
        $pegawaiId = is_object($pegawai) ? $pegawai->id : $pegawai;

        return [
            // Wajib (Required): Hanya NIP dan Nama Lengkap / Nama
            'nip'                  => ['required', 'string', 'max:50', Rule::unique('pegawai', 'nip')->ignore($pegawaiId)],
            'nama'                 => 'required|string|max:150',
            'nama_lengkap'         => 'nullable|string|max:150',

            // Opsional (Nullable): Seluruh kolom identitas & kontak
            'nik'                  => ['nullable', 'string', 'max:20', Rule::unique('pegawai', 'nik')->ignore($pegawaiId)],
            'npwp'                 => 'nullable|string|max:30',
            'bpjs_kesehatan'       => 'nullable|string|max:30',
            'bpjs_ketenagakerjaan' => 'nullable|string|max:30',
            'karpeg_karis_karsu'   => 'nullable|string|max:50',
            'nidn_nuptk'           => 'nullable|string|max:20',
            'gelar_depan'          => 'nullable|string|max:20',
            'gelar_belakang'       => 'nullable|string|max:20',
            'tempat_lahir'         => 'nullable|string|max:100',
            'tanggal_lahir'        => 'nullable|date',
            'jenis_kelamin'        => 'nullable|in:L,P',
            'agama'                => 'nullable|string|max:30',
            'email'                => 'nullable|email|max:100',
            'no_hp'                => 'nullable|string|max:20',
            'alamat'               => 'nullable|string',
            'pendidikan_terakhir'  => 'nullable|string|max:100',
            'jenis_pegawai'        => 'nullable|in:Dosen,PNS,PPPK,PHL',
            'status_asn'           => 'nullable|in:ASN,Non ASN',
            'status_pernikahan'    => 'nullable|in:Belum Menikah,Menikah,Cerai',
            'nama_pasangan'        => 'nullable|string|max:150',
            'jumlah_anak'          => 'nullable|integer|min:0',
            'mkg_tahun'            => 'nullable|integer|min:0|max:40',
            'mkg_bulan'            => 'nullable|integer|min:0|max:11',

            // Kontak Tambahan & Domisili
            'no_hp_darurat'            => 'nullable|string|max:20',
            'nama_kontak_darurat'      => 'nullable|string|max:100',
            'hubungan_kontak_darurat'  => 'nullable|string|max:50',
            'alamat_domisili'          => 'nullable|string',
            'kode_pos'                 => 'nullable|string|max:10',
            'kota_domisili'            => 'nullable|string|max:100',
            'provinsi'                 => 'nullable|string|max:100',

            // Kepegawaian Teknis
            'jenis_jabatan'            => 'nullable|string|max:100',
            'angka_kredit'             => 'nullable|numeric|min:0',
            'batas_usia_pensiun'       => 'nullable|integer|in:56,58,60,65',
            'tanggal_pensiun'          => 'nullable|date',
            'no_sk_pensiun'            => 'nullable|string|max:100',
            'tmt_pensiun'              => 'nullable|date',
            'jenis_kontrak'            => 'nullable|string|max:100',
            'tanggal_kontrak_mulai'    => 'nullable|date',
            'tanggal_kontrak_selesai'  => 'nullable|date|after_or_equal:tanggal_kontrak_mulai',

            // Relasi Utama & Alur Persetujuan (Approval Flow)
            'unit_kerja_id'        => 'nullable|exists:unit_kerja,id',
            'jabatan_id'           => 'nullable|exists:jabatan,id',
            'atasan_id'            => 'nullable|exists:pegawai,id',
            'pejabat_penilai_id'   => 'nullable|exists:pegawai,id',
            'atasan_cuti_id'       => 'nullable|exists:pegawai,id',
            'golongan_id'          => 'nullable|exists:golongan,id',

            // Legalitas Profesi Kesehatan (STR & SIP Dosen/Nakes)
            'nomor_str'                => 'nullable|string|max:50',
            'tanggal_kadaluarsa_str'   => 'nullable|date',
            'file_str'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'nomor_sip'                => 'nullable|string|max:50',
            'tanggal_kadaluarsa_sip'   => 'nullable|date',
            'file_sip'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Tanggal, SK & Dokumen
            'tanggal_masuk'        => 'nullable|date',
            'tmt_sk_pertama'       => 'nullable|date',
            'tmt_pangkat_terakhir' => 'nullable|date',
            'tmt_kgb_terakhir'     => 'nullable|date',
            'nomor_sk_pertama'           => 'nullable|string|max:100',
            'tanggal_sk_pertama'         => 'nullable|date',
            'nomor_sk_pangkat_terakhir'   => 'nullable|string|max:100',
            'tanggal_sk_pangkat_terakhir' => 'nullable|date',
            'status_pegawai'       => 'nullable|in:Aktif,Tugas Belajar,Non Aktif,Pensiun',
            
            // Upload Berkas (Opsional)
            'foto'                 => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
            'file_sk_pertama'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'file_sk_pangkat_terakhir' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'file_sk_kgb_terakhir'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'file_karpeg'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'file_pak'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'nomor_pak'                => 'nullable|string|max:100',
            'tanggal_pak'              => 'nullable|date',

            // Validasi Input Multi-Riwayat Pendidikan (Opsional)
            'riwayat_pendidikan'                       => 'nullable|array',
            'riwayat_pendidikan.*.id'                  => 'nullable|integer',
            'riwayat_pendidikan.*.jenjang'             => 'nullable|string|max:50',
            'riwayat_pendidikan.*.institusi'           => 'nullable|string|max:255',
            'riwayat_pendidikan.*.fakultas'            => 'nullable|string|max:150',
            'riwayat_pendidikan.*.jurusan'             => 'nullable|string|max:150',
            'riwayat_pendidikan.*.tahun_lulus'         => 'nullable|integer',
            'riwayat_pendidikan.*.ijazah'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Input Multi-Riwayat Pangkat (Opsional)
            'riwayat_pangkat'                          => 'nullable|array',
            'riwayat_pangkat.*.id'                     => 'nullable|integer',
            'riwayat_pangkat.*.golongan_id'            => 'nullable|exists:golongan,id',
            'riwayat_pangkat.*.tmt'                    => 'nullable|date',
            'riwayat_pangkat.*.nomor_sk'               => 'nullable|string|max:100',
            'riwayat_pangkat.*.tanggal_sk'             => 'nullable|date',
            'riwayat_pangkat.*.status'                 => 'nullable|string|max:50',
            'riwayat_pangkat.*.file_sk'                => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Input Multi-Riwayat Jabatan (Opsional)
            'riwayat_jabatan'                          => 'nullable|array',
            'riwayat_jabatan.*.id'                     => 'nullable|integer',
            'riwayat_jabatan.*.jabatan_id'             => 'nullable|exists:jabatan,id',
            'riwayat_jabatan.*.unit_kerja_id'          => 'nullable|exists:unit_kerja,id',
            'riwayat_jabatan.*.tmt_jabatan'            => 'nullable|date',
            'riwayat_jabatan.*.nomor_sk'               => 'nullable|string|max:100',
            'riwayat_jabatan.*.tanggal_sk'             => 'nullable|date',
            'riwayat_jabatan.*.status'                 => 'nullable|string|max:50',
            'riwayat_jabatan.*.file_sk'                => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Input Multi-Riwayat Diklat (Opsional)
            'riwayat_diklat'                           => 'nullable|array',
            'riwayat_diklat.*.id'                      => 'nullable|integer',
            'riwayat_diklat.*.nama_diklat'             => 'nullable|string|max:255',
            'riwayat_diklat.*.penyelenggara'           => 'nullable|string|max:255',
            'riwayat_diklat.*.jenis_diklat'            => 'nullable|string|max:100',
            'riwayat_diklat.*.tanggal_mulai'           => 'nullable|date',
            'riwayat_diklat.*.tanggal_selesai'         => 'nullable|date',
            'riwayat_diklat.*.jumlah_jam'              => 'nullable|integer|min:0',
            'riwayat_diklat.*.status'                  => 'nullable|string|max:50',
            'riwayat_diklat.*.keterangan'              => 'nullable|string',
            'riwayat_diklat.*.file_sertifikat'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Penilaian Kinerja (SKP 2 Tahun Lalu)
            'riwayat_skp'                              => 'nullable|array',
            'riwayat_skp.*.id'                         => 'nullable|integer',
            'riwayat_skp.*.tahun'                      => 'nullable|integer',
            'riwayat_skp.*.predikat_kinerja'           => 'nullable|string|max:50',
            'riwayat_skp.*.pejabat_penilai'            => 'nullable|string|max:255',
            'riwayat_skp.*.file_evaluasi_skp'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Piagam Satyalancana Terdahulu
            'piagam_satyalancana'                      => 'nullable|array',
            'piagam_satyalancana.id'                   => 'nullable|integer',
            'piagam_satyalancana.nama_penghargaan'     => 'nullable|string|max:255',
            'piagam_satyalancana.nomor_sk'              => 'nullable|string|max:100',
            'piagam_satyalancana.tanggal_sk'            => 'nullable|date',
            'piagam_satyalancana.instansi_pemberi'      => 'nullable|string|max:255',
            'piagam_satyalancana.file_sk'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
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
            'nik'                         => 'NIK (Nomor Induk Kependudukan)',
            'npwp'                        => 'NPWP',
            'bpjs_kesehatan'              => 'Nomor BPJS Kesehatan',
            'bpjs_ketenagakerjaan'        => 'Nomor BPJS Ketenagakerjaan',
            'pejabat_penilai_id'          => 'Pejabat Penilai Kinerja',
            'atasan_cuti_id'              => 'Atasan Langsung Cuti',
            'nomor_str'                   => 'Nomor STR',
            'nomor_sip'                   => 'Nomor SIP',
        ];
    }

    public function messages(): array
    {
        return [
            'nip.required'           => 'NIP wajib diisi.',
            'nip.unique'             => 'NIP sudah terdaftar pada pegawai lain.',
            'nama.required'          => 'Nama Lengkap wajib diisi.',
            'nama_lengkap.required'  => 'Nama Lengkap wajib diisi.',
        ];
    }
}