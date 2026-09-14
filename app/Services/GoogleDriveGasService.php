<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\ArsipDokumen;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GoogleDriveGasService
{
    protected string $webAppUrl;
    protected string $apiKey;
    protected string $rootFolderId;

    public function __construct()
    {
        $this->webAppUrl   = config('services.gas_drive.web_app_url') ?? env('GAS_DRIVE_WEBAPP_URL', '');
        $this->apiKey      = config('services.gas_drive.api_key') ?? env('GAS_DRIVE_API_KEY', 'SIKAP_UNRI_SECURE_KEY_2026');
        $this->rootFolderId = config('services.gas_drive.root_folder_id') ?? env('GAS_DRIVE_ROOT_FOLDER_ID', '1Q1pGfeA2brd3KPmErFNiRnzwGnjw0gex');
    }

    /**
     * Tentukan Nama Sub-Folder Kategori Kepegawaian (Level 1)
     */
    public function resolveKategoriFolder(Pegawai $pegawai): string
    {
        $kategori = strtolower((string)($pegawai->kategori_kepegawaian ?? ''));
        $jenis    = strtoupper((string)($pegawai->jenis_pegawai ?? ''));
        $asn      = strtoupper((string)($pegawai->status_asn ?? ''));
        $nip      = (string)($pegawai->nip ?? '');

        // PHL / Kontrak
        if ($kategori === 'phl' || in_array($jenis, ['PHL', 'HONORER', 'KONTRAK'])) {
            return '5. PHL';
        }

        // Dosen
        if ($kategori === 'dosen' || str_contains($jenis, 'DOSEN') || $pegawai->is_dosen) {
            if (str_contains($jenis, 'PPPK') || $asn === 'PPPK' || strlen($nip) === 21) {
                return '2. DOSEN PPPK';
            }
            return '1. DOSEN PNS';
        }

        // Tendik
        if (str_contains($jenis, 'PPPK') || $asn === 'PPPK' || strlen($nip) === 21) {
            return '4. TENDIK PPPK';
        }

        return '3. TENDIK PNS';
    }

    /**
     * Tentukan Nama Folder Individual Pegawai (Level 2)
     */
    public function resolvePegawaiFolderName(Pegawai $pegawai): string
    {
        $nip = $pegawai->nip ? trim($pegawai->nip) : 'ID-' . $pegawai->id;
        $nama = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $pegawai->nama_lengkap ?? $pegawai->nama);
        return $nip . ' - ' . trim($nama);
    }

    /**
     * Upload Dokumen Pegawai ke Google Drive via GAS Proxy & Simpan Record ArsipDokumen secara Non-Blocking (Background Sync)
     */
    public function uploadDokumen(
        Pegawai $pegawai,
        $file,
        string $jenisDokumen = 'KTP',
        string $subFolderCategory = '01_DOKUMEN_UTAMA',
        ?string $keterangan = null
    ): ArsipDokumen {
        $namaFileAsli = $file->getClientOriginalName();
        $extension    = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $mimeType     = $file->getMimeType() ?: 'application/octet-stream';
        $ukuranFile   = $file->getSize();
        $fileBytes    = file_get_contents($file->getRealPath());
        $checksum     = hash('sha256', $fileBytes);

        // Format nama file di sistem
        $cleanNama    = Str::slug($pegawai->nama, '_');
        $cleanNip     = $pegawai->nip ? Str::slug($pegawai->nip) : 'ID_' . $pegawai->id;
        $namaFileSistem = strtoupper($jenisDokumen) . '_' . $cleanNip . '_' . $cleanNama . '.' . $extension;

        // Simpan file lokal temporary buffer di storage/app/private/arsip
        $localPath = $file->storeAs('arsip/' . $pegawai->id, $namaFileSistem, 'local');

        $kategoriFolder    = $this->resolveKategoriFolder($pegawai);
        $namaFolderPegawai = $this->resolvePegawaiFolderName($pegawai);

        // 1. Buat DRAFT Record di DB Database (PENDING)
        $arsip = ArsipDokumen::create([
            'sync_uuid'            => (string) Str::uuid(),
            'pegawai_sync_uuid'    => $pegawai->sync_uuid ?? (string) Str::uuid(),
            'pegawai_id'           => $pegawai->id,
            'jenis_dokumen'        => $jenisDokumen,
            'kategori'             => $subFolderCategory,
            'nama_file_sistem'     => $namaFileSistem,
            'nama_file_asli'       => $namaFileAsli,
            'mime_type'            => $mimeType,
            'ukuran_file'          => $ukuranFile,
            'checksum'             => $checksum,
            'storage_driver'       => 'gdrive',
            'status_sync'          => 'PENDING',
            'keterangan'           => $keterangan,
            'created_by'           => auth()->id(),
            'created_by_sync_uuid' => auth()->user()->sync_uuid ?? null,
            'is_active'            => true,
        ]);

        // 2. Jika URL WebApp GAS dikonfigurasi, lakukan sync ke Google Drive via Shutdown Function / FastCGI Finish Request
        if (!empty($this->webAppUrl) && filter_var($this->webAppUrl, FILTER_VALIDATE_URL)) {
            $webAppUrl    = $this->webAppUrl;
            $apiKey       = $this->apiKey;
            $rootFolderId = $this->rootFolderId;

            register_shutdown_function(function () use ($arsip, $webAppUrl, $apiKey, $rootFolderId, $kategoriFolder, $namaFolderPegawai, $subFolderCategory, $namaFileSistem, $mimeType, $fileBytes) {
                if (function_exists('fastcgi_finish_request')) {
                    @fastcgi_finish_request();
                }

                try {
                    $payload = [
                        'api_key'              => $apiKey,
                        'root_folder_id'       => $rootFolderId,
                        'kategori_kepegawaian' => $kategoriFolder,
                        'nama_folder_pegawai'  => $namaFolderPegawai,
                        'sub_folder_dokumen'   => $subFolderCategory,
                        'nama_file'            => $namaFileSistem,
                        'mime_type'            => $mimeType,
                        'file_base64'          => base64_encode($fileBytes),
                    ];

                    $response = Http::timeout(60)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($webAppUrl, $payload);

                    if ($response->successful()) {
                        $json = $response->json();
                        if (isset($json['success']) && $json['success'] === true) {
                            $arsip->update([
                                'google_drive_file_id'   => $json['file_id'] ?? null,
                                'google_drive_folder_id' => $json['folder_id'] ?? null,
                                'status_sync'            => 'SYNCED',
                                'sync_error'             => null,
                            ]);
                        } else {
                            $arsip->update([
                                'status_sync' => 'FAILED',
                                'sync_error'  => $json['message'] ?? 'Remote GAS returned unsuccessful status',
                            ]);
                        }
                    } else {
                        $arsip->update([
                            'status_sync' => 'FAILED',
                            'sync_error'  => 'HTTP Response Error: ' . $response->status(),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('GAS Upload Failed for Arsip ID ' . $arsip->id . ': ' . $e->getMessage());
                    $arsip->update([
                        'status_sync' => 'FAILED',
                        'sync_error'  => $e->getMessage(),
                    ]);
                }
            });
        }

        return $arsip;
    }
}
