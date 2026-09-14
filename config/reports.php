<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Informasi Instansi (Kop Surat)
    |--------------------------------------------------------------------------
    */
    'instansi' => [
        'pemerintah'      => 'KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI',
        'dinas'           => 'UNIVERSITAS RIAU',
        'fakultas'        => 'FAKULTAS KEPERAWATAN',
        'pemerintah_kota' => 'Pekanbaru',
        'alamat'          => 'Kampus Bina Widya Gedung Health Studies Complex KM. 12,5 Simpang Baru',
        'telepon'         => '-',
        'email'           => 'keperawatan@unri.co.id',
        'website'         => 'www.keperawatan.unri.ac.id',
        'kode_pos'        => '28293',
        'logo_path'       => public_path('images/logo-instansi.png'), // Pastikan file logo UNRI diletakkan di public/images/logo-instansi.png
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Pejabat Penandatangan SK / Laporan
    |--------------------------------------------------------------------------
    */
    'penandatangan' => [
        'jabatan' => 'Wakil Dekan Bidang Keuangan dan Umum',
        'unit'    => 'Fakultas Keperawatan Universitas Riau',
        'nama'    => 'Ns. Safri, M.Kep., Sp.Kep.M.B',
        'nip'     => '19850909 201404 1 001',
        'pangkat' => '-',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pengaturan Layout PDF Default
    |--------------------------------------------------------------------------
    */
    'pdf_settings' => [
        'paper_portrait'  => 'A4',
        'paper_landscape' => 'A4',
        'orientation'     => 'portrait',
        'margin_top'      => 15,
        'margin_right'    => 15,
        'margin_bottom'   => 15,
        'margin_left'     => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Daftar Kolom Laporan Rekap DUK (Daftar Urut Kepangkatan)
    |--------------------------------------------------------------------------
    */
    'duk' => [
        'kolom_wajib' => [
            'no'          => 'NO',
            'nama_nip'    => 'NAMA / NIP',
            'pangkat_gol' => 'PANGKAT / GOL. RUANG',
            'jabatan'     => 'JABATAN',
            'tmt_jabatan' => 'TMT JABATAN',
            'masa_kerja'  => 'MASA KERJA',
            'pendidikan'  => 'PENDIDIKAN TERAKHIR',
            'unit_kerja'  => 'UNIT KERJA',
        ],
    ],
];