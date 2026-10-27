<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;

class StorePegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            // Wajib (Required): Hanya NIP dan Nama Lengkap / Nama
            'nip'                  => 'required|string|max:50|unique:pegawai,nip',
            'nama'                 => 'required|string|max:150',
            'nama_lengkap'         => 'nullable|string|max:150',

            // Opsional (Nullable): Seluruh kolom identitas & kontak
            'nik'                  => 'nullable|string|max:20|unique:pegawai,nik',
            'npwp'                 => 'nullable|string|max:30',
            'bpjs_kesehatan'       => 'nullable|string|max:30',
            'bpjs_ketenagakerjaan' => 'nullable|string|max:30',
            'karpeg_karis_karsu'   => 'nullable|string|max:50',
            'nidn_nuptk'           => 'nullable|string|max:20',
            'gelar_depan'          => 'nullable|string|max:100',
            'gelar_belakang'       => 'nullable|string|max:100',
            'tempat_lahir'         => 'nullable|string|max:100',
            'tanggal_lahir'        => 'nullable|date',
            'jenis_kelamin'        => 'nullable|in:L,P',
            'agama'                => 'nullable|string|max:30',
            'pendidikan_terakhir'  => 'nullable|string|max:100',
            'email'                => 'nullable|email|max:100',
            'no_hp'                => 'nullable|string|max:20',
            'alamat'               => 'nullable|string',
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
            'status_pegawai'           => 'nullable|in:Aktif,Tugas Belajar,Non Aktif,Pensiun',
            
            // Upload Berkas (Opsional)
            'foto'                     => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
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
            'riwayat_skp.*.file_rencana_skp'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'riwayat_skp.*.file_evaluasi_skp'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Piagam Satyalancana Terdahulu
            'piagam_satyalancana'                      => 'nullable|array',
            'piagam_satyalancana.id'                   => 'nullable|integer',
            'piagam_satyalancana.nama_penghargaan'     => 'nullable|string|max:255',
            'piagam_satyalancana.nomor_sk'              => 'nullable|string|max:100',
            'piagam_satyalancana.tanggal_sk'            => 'nullable|date',
            'piagam_satyalancana.instansi_pemberi'      => 'nullable|string|max:255',
            'piagam_satyalancana.file_sk'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Input Multi-Riwayat Penghargaan & Tanda Jasa Lainnya (Opsional)
            'riwayat_penghargaan'                      => 'nullable|array',
            'riwayat_penghargaan.*.id'                 => 'nullable|integer',
            'riwayat_penghargaan.*.nama_penghargaan'   => 'nullable|string|max:255',
            'riwayat_penghargaan.*.jenis_penghargaan'  => 'nullable|string|max:100',
            'riwayat_penghargaan.*.instansi_pemberi'   => 'nullable|string|max:255',
            'riwayat_penghargaan.*.nomor_sk'           => 'nullable|string|max:100',
            'riwayat_penghargaan.*.tanggal_terima'     => 'nullable|date',
            'riwayat_penghargaan.*.file_sk'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Input Multi-Riwayat Keanggotaan Organisasi (Opsional)
            'riwayat_organisasi'                       => 'nullable|array',
            'riwayat_organisasi.*.id'                  => 'nullable|integer',
            'riwayat_organisasi.*.nama_organisasi'     => 'nullable|string|max:255',
            'riwayat_organisasi.*.jabatan_organisasi'  => 'nullable|string|max:150',
            'riwayat_organisasi.*.tahun_mulai'         => 'nullable|integer',
            'riwayat_organisasi.*.tahun_selesai'       => 'nullable|integer',
            'riwayat_organisasi.*.masih_aktif'         => 'nullable|boolean',

            // Validasi Input Multi-Riwayat Publikasi Ilmiah & Karya (Opsional)
            'riwayat_publikasi'                        => 'nullable|array',
            'riwayat_publikasi.*.id'                   => 'nullable|integer',
            'riwayat_publikasi.*.judul_publikasi'      => 'nullable|string|max:500',
            'riwayat_publikasi.*.jenis_publikasi'      => 'nullable|string|max:100',
            'riwayat_publikasi.*.indeksasi'            => 'nullable|string|max:100',
            'riwayat_publikasi.*.nama_jurnal'          => 'nullable|string|max:255',
            'riwayat_publikasi.*.penerbit'             => 'nullable|string|max:255',
            'riwayat_publikasi.*.tahun_terbit'         => 'nullable|integer',
            'riwayat_publikasi.*.volume_nomor'         => 'nullable|string|max:100',
            'riwayat_publikasi.*.url_doi'              => 'nullable|string|max:500',
            'riwayat_publikasi.*.file_publikasi'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Validasi Input Multi-Riwayat Tugas Belajar & Izin Belajar (Opsional)
            'tugas_belajar'                            => 'nullable|array',
            'tugas_belajar.*.id'                       => 'nullable|integer',
            'tugas_belajar.*.jenis_pengembangan'       => 'nullable|string|max:100',
            'tugas_belajar.*.jenjang_studi'            => 'nullable|string|max:100',
            'tugas_belajar.*.perguruan_tinggi'         => 'nullable|string|max:255',
            'tugas_belajar.*.program_studi'            => 'nullable|string|max:255',
            'tugas_belajar.*.negara'                   => 'nullable|string|max:100',
            'tugas_belajar.*.sumber_pembiayaan'        => 'nullable|string|max:150',
            'tugas_belajar.*.nomor_sk'                 => 'nullable|string|max:100',
            'tugas_belajar.*.tanggal_mulai'            => 'nullable|date',
            'tugas_belajar.*.tanggal_selesai'          => 'nullable|date',
            'tugas_belajar.*.semester_berjalan'        => 'nullable|integer|min:1',
            'tugas_belajar.*.status_studi'             => 'nullable|string|max:100',
            'tugas_belajar.*.file_sk'                  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
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

    public function attributes(): array
    {
        return [
            'nip'                         => 'NIP',
            'nama_lengkap'                => 'Nama Lengkap',
            'nama'                        => 'Nama Lengkap',
            'unit_kerja_id'               => 'Unit Kerja',
            'jabatan_id'                  => 'Jabatan',
            'golongan_id'                 => 'Golongan/Pangkat',
            'status_pegawai'              => 'Status Kepegawaian',
            'foto'                        => 'Foto Pegawai',
            'file_sk_pertama'             => 'Berkas SK Pengangkatan Pertama',
            'file_sk_pangkat_terakhir'    => 'Berkas SK Pangkat Terakhir',
            'file_sk_kgb_terakhir'        => 'Berkas SK KGB Terakhir',
            'file_karpeg'                 => 'Berkas KARPEG',
            'file_pak'                    => 'Berkas Dokumen PAK',
            'file_str'                    => 'Berkas Dokumen STR',
            'file_sip'                    => 'Berkas Dokumen SIP',
            'pendidikan_ijazah'           => 'File Ijazah Pendidikan',
            'diklat_sertifikat'           => 'File Sertifikat Diklat',
            'nomor_sk_pertama'            => 'Nomor SK Pertama',
            'tanggal_sk_pertama'          => 'Tanggal SK Pertama',
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
            'riwayat_pendidikan.*.ijazah' => 'Berkas Ijazah',
            'riwayat_diklat.*.file_sertifikat' => 'Berkas Sertifikat Diklat',
            'riwayat_jabatan.*.file_sk'   => 'Berkas SK Jabatan',
            'riwayat_pangkat.*.file_sk'   => 'Berkas SK Pangkat',
            'riwayat_skp.*.file_rencana_skp' => 'Berkas Rencana SKP',
            'riwayat_skp.*.file_evaluasi_skp' => 'Berkas Evaluasi SKP',
            'piagam_satyalancana.file_sk' => 'Berkas Piagam Satyalancana',
            'riwayat_penghargaan.*.file_sk' => 'Berkas SK Penghargaan',
            'riwayat_publikasi.*.file_publikasi' => 'Berkas Publikasi Ilmiah',
            'tugas_belajar.*.file_sk'     => 'Berkas SK Tugas Belajar',
        ];
    }

    public function messages(): array
    {
        return [
            'nip.required'           => 'NIP wajib diisi.',
            'nip.unique'             => 'NIP sudah terdaftar.',
            'nama.required'          => 'Nama Lengkap wajib diisi.',
            'nama_lengkap.required'  => 'Nama Lengkap wajib diisi.',

            // Pesan Validasi Berkas Unggahan (MIME Type, Ekstensi & Ukuran Maksimal)
            'foto.mimes'             => 'Format foto profil harus berupa file gambar: JPG, JPEG, PNG, atau PDF.',
            'foto.max'               => 'Ukuran file foto profil tidak boleh melebihi 10 MB.',
            'foto.file'              => 'Berkas foto profil harus berupa file yang valid.',

            'file_sk_pertama.mimes'          => 'Format Berkas SK Pertama harus berupa dokumen PDF atau gambar (JPG, JPEG, PNG).',
            'file_sk_pertama.max'            => 'Ukuran Berkas SK Pertama tidak boleh melebihi 10 MB.',
            'file_sk_pangkat_terakhir.mimes' => 'Format Berkas SK Pangkat Terakhir harus berupa dokumen PDF atau gambar (JPG, JPEG, PNG).',
            'file_sk_pangkat_terakhir.max'   => 'Ukuran Berkas SK Pangkat Terakhir tidak boleh melebihi 10 MB.',
            'file_sk_kgb_terakhir.mimes'     => 'Format Berkas SK KGB Terakhir harus berupa dokumen PDF atau gambar (JPG, JPEG, PNG).',
            'file_sk_kgb_terakhir.max'       => 'Ukuran Berkas SK KGB Terakhir tidak boleh melebihi 10 MB.',
            'file_karpeg.mimes'              => 'Format Berkas KARPEG harus berupa dokumen PDF atau gambar (JPG, JPEG, PNG).',
            'file_karpeg.max'                => 'Ukuran Berkas KARPEG tidak boleh melebihi 10 MB.',
            'file_pak.mimes'                 => 'Format Berkas PAK harus berupa dokumen PDF atau gambar (JPG, JPEG, PNG).',
            'file_pak.max'                   => 'Ukuran Berkas PAK tidak boleh melebihi 10 MB.',
            'file_str.mimes'                 => 'Format Berkas STR harus berupa dokumen PDF atau gambar (JPG, JPEG, PNG).',
            'file_str.max'                   => 'Ukuran Berkas STR tidak boleh melebihi 10 MB.',
            'file_sip.mimes'                 => 'Format Berkas SIP harus berupa dokumen PDF atau gambar (JPG, JPEG, PNG).',
            'file_sip.max'                   => 'Ukuran Berkas SIP tidak boleh melebihi 10 MB.',

            // Pesan Validasi Berkas Multi-Riwayat
            'riwayat_pendidikan.*.ijazah.mimes'          => 'Berkas ijazah harus berformat PDF, JPG, JPEG, atau PNG.',
            'riwayat_pendidikan.*.ijazah.max'            => 'Ukuran berkas ijazah maksimal adalah 10 MB.',
            'riwayat_diklat.*.file_sertifikat.mimes'     => 'Berkas sertifikat diklat harus berformat PDF, JPG, JPEG, atau PNG.',
            'riwayat_diklat.*.file_sertifikat.max'       => 'Ukuran berkas sertifikat diklat maksimal adalah 10 MB.',
            'riwayat_jabatan.*.file_sk.mimes'            => 'Berkas SK jabatan harus berformat PDF, JPG, JPEG, atau PNG.',
            'riwayat_jabatan.*.file_sk.max'              => 'Ukuran berkas SK jabatan maksimal adalah 10 MB.',
            'riwayat_pangkat.*.file_sk.mimes'            => 'Berkas SK pangkat harus berformat PDF, JPG, JPEG, atau PNG.',
            'riwayat_pangkat.*.file_sk.max'              => 'Ukuran berkas SK pangkat maksimal adalah 10 MB.',
            'riwayat_skp.*.file_rencana_skp.mimes'       => 'Berkas rencana SKP harus berformat PDF, JPG, JPEG, atau PNG.',
            'riwayat_skp.*.file_rencana_skp.max'         => 'Ukuran berkas rencana SKP maksimal adalah 10 MB.',
            'riwayat_skp.*.file_evaluasi_skp.mimes'      => 'Berkas evaluasi SKP harus berformat PDF, JPG, JPEG, atau PNG.',
            'riwayat_skp.*.file_evaluasi_skp.max'        => 'Ukuran berkas evaluasi SKP maksimal adalah 10 MB.',
            'piagam_satyalancana.file_sk.mimes'          => 'Berkas piagam satyalancana harus berformat PDF, JPG, JPEG, atau PNG.',
            'piagam_satyalancana.file_sk.max'            => 'Ukuran berkas piagam satyalancana maksimal adalah 10 MB.',
            'riwayat_penghargaan.*.file_sk.mimes'        => 'Berkas SK penghargaan harus berformat PDF, JPG, JPEG, atau PNG.',
            'riwayat_penghargaan.*.file_sk.max'          => 'Ukuran berkas SK penghargaan maksimal adalah 10 MB.',
            'riwayat_publikasi.*.file_publikasi.mimes'   => 'Berkas dokumen publikasi harus berformat PDF, JPG, JPEG, atau PNG.',
            'riwayat_publikasi.*.file_publikasi.max'     => 'Ukuran berkas dokumen publikasi maksimal adalah 10 MB.',
            'tugas_belajar.*.file_sk.mimes'              => 'Berkas SK tugas/izin belajar harus berformat PDF, JPG, JPEG, atau PNG.',
            'tugas_belajar.*.file_sk.max'                => 'Ukuran berkas SK tugas/izin belajar maksimal adalah 10 MB.',

            // Pesan fallback umum
            'mimes'                  => 'Format berkas :attribute tidak didukung. Harap unggah dokumen PDF atau gambar (JPG, JPEG, PNG).',
            'max'                    => 'Ukuran berkas :attribute terlalu besar. Batas maksimal yang diizinkan adalah 10 MB.',
            'file'                   => 'Unggahan :attribute harus berupa file dokumen atau gambar yang sah.',
        ];
    }
}