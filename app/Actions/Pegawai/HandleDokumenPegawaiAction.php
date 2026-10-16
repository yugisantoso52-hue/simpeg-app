<?php

namespace App\Actions\Pegawai;

use App\Models\Pegawai;
use App\Services\GoogleDriveGasService;
use App\Services\PegawaiStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class HandleDokumenPegawaiAction
{
    protected ?GoogleDriveGasService $gasDriveService;

    public function __construct(?GoogleDriveGasService $gasDriveService = null)
    {
        $this->gasDriveService = $gasDriveService;
    }

    /**
     * Memproses berkas foto dan seluruh SK utama pegawai (upload, hapus berkas lama, dan sinkronisasi ke cloud drive)
     *
     * @param array $files Berkas dari request ($request->allFiles())
     * @param Pegawai|null $pegawai Model pegawai saat ini (jika proses update)
     * @return array Atribut path file yang telah diproses untuk disimpan ke database
     */
    public function execute(array $files, ?Pegawai $pegawai = null): array
    {
        $processed = [];
        $isUpdate = $pegawai !== null;

        // 1. Foto Profil
        if (!empty($files['foto'])) {
            if ($isUpdate && $pegawai->foto) {
                $this->deleteFile($pegawai->foto);
            }
            $processed['foto'] = $this->uploadFoto($files['foto'], $pegawai);
        }

        // 2. Berkas SK Utama
        $skFields = [
            'file_sk_pertama'          => ['type' => 'sk_pertama', 'folder' => '02_RIWAYAT_SK', 'doc_title' => 'SK CPNS'],
            'file_sk_pangkat_terakhir' => ['type' => 'sk_pangkat', 'folder' => '02_RIWAYAT_SK', 'doc_title' => 'SK PANGKAT'],
            'file_sk_kgb_terakhir'     => ['type' => 'sk_kgb',     'folder' => '02_RIWAYAT_SK', 'doc_title' => 'SK KGB'],
            'file_karpeg'              => ['type' => 'karpeg',     'folder' => '01_DOKUMEN_UTAMA', 'doc_title' => 'KARPEG'],
            'file_pak'                 => ['type' => 'pak',        'folder' => '04_KINERJA_PENILAIAN', 'doc_title' => 'PAK'],
            'file_str'                 => ['type' => 'str_sip',    'folder' => '01_DOKUMEN_UTAMA', 'doc_title' => 'STR', 'extra' => 'STR'],
            'file_sip'                 => ['type' => 'str_sip',    'folder' => '01_DOKUMEN_UTAMA', 'doc_title' => 'SIP', 'extra' => 'SIP'],
        ];

        foreach ($skFields as $field => $config) {
            if (!empty($files[$field])) {
                if ($isUpdate && !empty($pegawai->{$field})) {
                    $this->deleteFile($pegawai->{$field});
                }
                $extra = $config['extra'] ?? null;
                $processed[$field] = $this->uploadSK($files[$field], $pegawai, $config['type'], $extra);

                // Sinkronisasi otomatis ke Google Drive SIKAP-ARSIP
                if ($pegawai) {
                    $this->syncFileToDrive($pegawai, $files[$field], $config['doc_title'], $config['folder']);
                }
            }
        }

        return $processed;
    }

    /**
     * Upload foto profil pegawai
     */
    protected function uploadFoto(UploadedFile $foto, ?Pegawai $pegawai = null): ?string
    {
        return PegawaiStorageService::store($foto, $pegawai, 'foto');
    }

    /**
     * Upload SK/Dokumen pegawai
     */
    protected function uploadSK(UploadedFile $file, $pegawai, string $tipe, ?string $extra = null): ?string
    {
        return PegawaiStorageService::store($file, $pegawai, $tipe, $extra);
    }

    /**
     * Hapus berkas fisik
     */
    public function deleteFile(?string $path): void
    {
        if ($path) {
            PegawaiStorageService::delete($path);
        }
    }

    /**
     * Sinkronisasikan berkas ke Google Drive GAS
     */
    public function syncFileToDrive(?Pegawai $pegawai, $file, string $tipeDokumen, string $kategoriFolder = '00_BERKAS_LAINNYA'): void
    {
        if (!$file || !($file instanceof UploadedFile) || !$pegawai) {
            return;
        }

        try {
            $driveService = $this->gasDriveService ?? app(GoogleDriveGasService::class);
            if ($driveService) {
                $driveService->uploadEmployeeFile($pegawai, $file, $tipeDokumen, $kategoriFolder);
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal auto-sync berkas [{$tipeDokumen}] ke Google Drive untuk Pegawai ID {$pegawai->id}: " . $e->getMessage());
        }
    }
}
