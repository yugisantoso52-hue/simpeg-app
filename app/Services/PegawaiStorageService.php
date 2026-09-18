<?php

namespace App\Services;

use App\Models\Pegawai;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PegawaiStorageService
{
    /**
     * Peta kategori ke nomor & nama folder arsip digital
     */
    private const CATEGORY_FOLDERS = [
        'foto'              => '01_FOTO',
        'identitas'         => '02_IDENTITAS',
        'karpeg'            => '02_IDENTITAS',
        'sk_pertama'        => '03_SK_PENGANGKATAN',
        'sk_pengangkatan'   => '03_SK_PENGANGKATAN',
        'sk_cpns'           => '03_SK_PENGANGKATAN',
        'sk_pns'            => '03_SK_PENGANGKATAN',
        'sk_pangkat'        => '04_SK_PANGKAT',
        'pangkat'           => '04_SK_PANGKAT',
        'sk_jabatan'        => '05_SK_JABATAN',
        'jabatan'           => '05_SK_JABATAN',
        'ijazah'            => '06_IJAZAH',
        'pendidikan'        => '06_IJAZAH',
        'kgb'               => '07_KGB',
        'sk_kgb'            => '07_KGB',
        'skp'               => '08_SKP',
        'rencana_skp'       => '08_SKP',
        'evaluasi_skp'      => '08_SKP',
        'diklat'            => '09_SERTIFIKAT_DIKLAT',
        'sertifikat'        => '09_SERTIFIKAT_DIKLAT',
        'pak'               => '10_PAK',
        'str_sip'           => '11_STR_SIP',
        'str'               => '11_STR_SIP',
        'sip'               => '11_STR_SIP',
        'tugas_belajar'     => '12_TUGAS_BELAJAR',
        'tubel'             => '12_TUGAS_BELAJAR',
        'cuti'              => '13_CUTI',
        'mutasi'            => '14_MUTASI',
        'publikasi'         => '15_PUBLIKASI',
        'penghargaan'       => '16_PENGHARGAAN',
        'logbook'           => '17_LOGBOOK',
    ];

    /**
     * Simpan file berkas pegawai ke struktur folder hierarkis yang rapi & otomatis
     *
     * @param UploadedFile $file File unggahan dari request
     * @param Pegawai|int|null $pegawai Model pegawai atau ID pegawai
     * @param string $category Kategori dokumen (misal: 'foto', 'sk_pangkat', 'ijazah')
     * @param string|null $customTitle Keterangan tambahan untuk penamaan file
     * @return string Path relatif file yang disimpan
     */
    public static function store(UploadedFile $file, $pegawai = null, string $category = 'lainnya', ?string $customTitle = null): string
    {
        // 1. Ekstrak data pegawai baik dari Model maupun Array
        $jp = '';
        $statusAsn = '';
        $rawNip = '';
        $rawName = '';

        if (is_numeric($pegawai)) {
            $pegawai = Pegawai::find($pegawai);
        }

        if ($pegawai instanceof Pegawai) {
            $jp        = (string)($pegawai->jenis_pegawai ?? '');
            $statusAsn = (string)($pegawai->status_asn ?? '');
            $rawNip    = (string)($pegawai->nip ?? '');
            $rawName   = (string)($pegawai->nama_lengkap ?? $pegawai->nama ?? '');
        } elseif (is_array($pegawai)) {
            $jp        = (string)($pegawai['jenis_pegawai'] ?? '');
            $statusAsn = (string)($pegawai['status_asn'] ?? '');
            $rawNip    = (string)($pegawai['nip'] ?? '');
            $rawName   = (string)($pegawai['nama_lengkap'] ?? $pegawai['nama'] ?? '');
        }

        // 2. Tentukan Klasifikasi: DOSEN vs TENDIK
        $klasifikasi = 'TENDIK';
        if (stripos($jp, 'dosen') !== false) {
            $klasifikasi = 'DOSEN';
        }

        // 3. Tentukan Status Kepegawaian: PNS vs PPPK vs NON_ASN
        $status = 'PNS';
        $jpLower  = strtolower($jp);
        $asnLower = strtolower($statusAsn);
        if (str_contains($jpLower, 'pppk') || str_contains($asnLower, 'pppk')) {
            $status = 'PPPK';
        } elseif (str_contains($jpLower, 'phl') || str_contains($jpLower, 'honorer') || str_contains($asnLower, 'non')) {
            $status = 'NON_ASN';
        }

        // 4. Tentukan Folder Identitas Pegawai: [NIP] - [Nama Pegawai]
        $cleanNip  = preg_replace('/[^0-9]/', '', $rawNip);
        $cleanName = preg_replace('/[\\\\\/:\*\?"<>\|]/', '_', $rawName);
        $cleanName = trim(preg_replace('/\s+/', ' ', $cleanName));

        if (!empty($cleanNip) || !empty($cleanName)) {
            $folderNip = !empty($cleanNip) ? $cleanNip : 'TANPA_NIP';
            $folderName = !empty($cleanName) ? $cleanName : 'Pegawai';
            $folderPegawai = "{$folderNip} - {$folderName}";
        } else {
            $folderNip = 'UMUM';
            $folderPegawai = '_ARSIP_UMUM';
        }

        // 5. Tentukan Subfolder Dokumen
        $cleanCategory = strtolower(trim($category));
        $subfolder = self::CATEGORY_FOLDERS[$cleanCategory] ?? '99_DOKUMEN_LAINNYA';

        // 6. Buat Nama Berkas Otomatis yang Informatif (Smart Naming)
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $prefix = strtoupper(str_replace('-', '_', Str::slug($category, '_')));
        $timeToken = date('Ymd_His');

        if ($customTitle) {
            $cleanTitle = strtoupper(str_replace('-', '_', Str::slug($customTitle, '_')));
            $filename = "{$prefix}_{$cleanTitle}_{$folderNip}_{$timeToken}.{$ext}";
        } else {
            $filename = "{$prefix}_{$folderNip}_{$timeToken}.{$ext}";
        }

        // 7. Susun Jalur Lengkap (Path)
        $targetDirectory = "{$klasifikasi}/{$status}/{$folderPegawai}/{$subfolder}";

        // 8. Tentukan Disk Penyimpanan
        $defaultDisk = config('filesystems.default', 'local');
        $uploadDisk  = in_array($defaultDisk, ['supabase', 's3', 'r2']) ? $defaultDisk : 'public';

        // Simpan file
        $storedPath = $file->storeAs($targetDirectory, $filename, $uploadDisk);

        return $storedPath;
    }

    /**
     * Hapus file secara aman dari disk yang tepat
     */
    public static function delete(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $defaultDisk = config('filesystems.default', 'local');
        $cloudDisk   = in_array($defaultDisk, ['supabase', 's3', 'r2']) ? $defaultDisk : null;

        if ($cloudDisk && Storage::disk($cloudDisk)->exists($path)) {
            Storage::disk($cloudDisk)->delete($path);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }
}
