<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Format Kode Klasifikasi Surat Dinas SIKAP UNRI
    |--------------------------------------------------------------------------
    | Sesuai Pedoman Tata Naskah Dinas Kementerian / Universitas Riau
    | Template Standar: {nomor}/UN19.5.1.1.10/{kode}/{tahun}
    */
    'nomor_surat' => [
        'prefix_univ'    => env('SIMPEG_SURAT_PREFIX_UNIV', 'UN19.5.1.1.10'),
        'default_kode'   => env('SIMPEG_SURAT_DEFAULT_KODE', 'KP'),
        'kode_cuti'      => 'KP',
        'kode_karir'     => 'KP',
        'kode_satyalancana' => 'KP',
        'kode_aktif'     => 'KP',
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Pejabat Penandatangan Default (Fallback)
    |--------------------------------------------------------------------------
    | Digunakan jika pejabat aktif struktural belum diset pada modul data pegawai
    */
    'pejabat' => [
        'dekan' => [
            'jabatan_keywords' => ['Dekan'],
            'exclude_keywords' => ['Wakil', 'Wadek'],
            'nama'             => env('SIMPEG_DEKAN_NAMA', 'Prof. Dr. Wan Nishfa Dewi, S.Kp., M.Ng., Ph.D'),
            'nip'              => env('SIMPEG_DEKAN_NIP', '197508222001122001'),
            'jabatan'          => 'Dekan Fakultas Keperawatan',
            'pangkat'          => 'Pembina Utama Madya / IV/d',
        ],
        'wadek1' => [
            'jabatan_keywords' => ['Wakil Dekan I', 'WD I', 'Bid. Akademik'],
            'exclude_keywords' => ['Wakil Dekan II', 'Wakil Dekan III', 'WD II', 'WD III'],
            'nama'             => env('SIMPEG_WADEK1_NAMA', 'Ns. Riri Novelni, M.Kep'),
            'nip'              => env('SIMPEG_WADEK1_NIP', '198205122008122001'),
            'jabatan'          => 'Wakil Dekan Bidang Akademik',
            'pangkat'          => 'Pembina / IV/a',
        ],
        'wadek2' => [
            'jabatan_keywords' => ['Wakil Dekan II', 'WD II', 'Bid. Keuangan'],
            'exclude_keywords' => ['Wakil Dekan I', 'Wakil Dekan III', 'WD I', 'WD III'],
            'nama'             => env('SIMPEG_WADEK2_NAMA', 'Dr. Safri, M.Kep, Sp.Kep.M.B'),
            'nip'              => env('SIMPEG_WADEK2_NIP', '198509092014041001'),
            'jabatan'          => 'Wakil Dekan Bidang Keuangan dan Umum',
            'pangkat'          => 'Pembina / IV/a',
        ],
        'wadek3' => [
            'jabatan_keywords' => ['Wakil Dekan III', 'WD III', 'Kemahasiswaan'],
            'exclude_keywords' => ['Wakil Dekan I', 'Wakil Dekan II', 'WD I', 'WD II'],
            'nama'             => env('SIMPEG_WADEK3_NAMA', 'Ns. Gamya Tri Utami, M.Kep, Sp.Kep.J'),
            'nip'              => env('SIMPEG_WADEK3_NIP', '198403212008122003'),
            'jabatan'          => 'Wakil Dekan Bidang Kemahasiswaan, Alumni dan Kerjasama',
            'pangkat'          => 'Penata Tingkat I / III/d',
        ],
        'kabag' => [
            'jabatan_keywords' => ['Kepala Bagian Umum', 'Kabag Umum', 'Kabag TU', 'Kepala Bagian Tata Usaha'],
            'exclude_keywords' => [],
            'nama'             => env('SIMPEG_KABAG_NAMA', 'Bakhtiar, S.Sos., M.Si'),
            'nip'              => env('SIMPEG_KABAG_NIP', '197507122005011002'),
            'jabatan'          => 'Kepala Bagian Umum',
            'pangkat'          => 'Penata Tingkat I / III/d',
        ],
        'kapokja' => [
            'jabatan_keywords' => ['Ka Pokja Keu-Kepeg', 'Ka Pokja Kepegawaian', 'Pokja Keuangan dan Kepegawaian', 'Ka Pokja'],
            'exclude_keywords' => ['Akademik', 'Sarana'],
            'nama'             => env('SIMPEG_KAPOKJA_NAMA', 'Dolli Vita Zenitha Harning Arivina, SE'),
            'nip'              => env('SIMPEG_KAPOKJA_NIP', '197605212014092001'),
            'jabatan'          => 'Ka Pokja Keu-Kepeg',
            'pangkat'          => 'Penata / III/c',
        ],
    ],

];
