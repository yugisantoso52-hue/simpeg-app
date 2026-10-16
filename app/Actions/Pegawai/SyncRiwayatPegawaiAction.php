<?php

namespace App\Actions\Pegawai;

use App\Models\Pegawai;
use App\Services\GoogleDriveGasService;
use App\Services\PegawaiStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class SyncRiwayatPegawaiAction
{
    protected ?GoogleDriveGasService $gasDriveService;

    public function __construct(?GoogleDriveGasService $gasDriveService = null)
    {
        $this->gasDriveService = $gasDriveService;
    }

    /**
     * Menyinkronkan seluruh riwayat kepegawaian (Pendidikan, Diklat, Jabatan, Pangkat, SKP, Satyalancana)
     */
    public function execute(Pegawai $pegawai, array $data, array $files = [], bool $isUpdate = false): void
    {
        $this->syncPendidikan($pegawai, $data, $files, $isUpdate);
        $this->syncDiklat($pegawai, $data, $files, $isUpdate);
        $this->syncJabatan($pegawai, $data, $files, $isUpdate);
        $this->syncPangkat($pegawai, $data, $files, $isUpdate);
        $this->syncSkp($pegawai, $data, $files, $isUpdate);
        $this->syncSatyalancana($pegawai, $data, $files, $isUpdate);
    }

    /**
     * 1. Riwayat Pendidikan
     */
    protected function syncPendidikan(Pegawai $pegawai, array $data, array $files, bool $isUpdate): void
    {
        $incoming = $data['riwayat_pendidikan'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $existingIds = $pegawai->riwayatPendidikan()->pluck('id')->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $pegawai->riwayatPendidikan()->whereIn('id', array_diff($existingIds, $incomingIds))->delete();
        }

        foreach ($incoming as $index => $row) {
            if (empty($row['jenjang']) && empty($row['institusi'])) {
                continue;
            }

            $fileIjazah = null;
            if (isset($files['riwayat_pendidikan'][$index]['ijazah'])) {
                if ($isUpdate && !empty($row['id'])) {
                    $old = $pegawai->riwayatPendidikan()->where('id', $row['id'])->first();
                    if ($old?->ijazah) {
                        PegawaiStorageService::delete($old->ijazah);
                    }
                }
                $fileIjazah = PegawaiStorageService::store($files['riwayat_pendidikan'][$index]['ijazah'], $pegawai, 'ijazah', $row['jenjang'] ?? 'ijazah');
                $this->syncFileToDrive($pegawai, $files['riwayat_pendidikan'][$index]['ijazah'], 'IJAZAH ' . strtoupper($row['jenjang'] ?? 'S1'), '03_PENDIDIKAN_DIKLAT');
            }

            $payload = [
                'jenjang'     => $row['jenjang'] ?? 'S1',
                'institusi'   => $row['institusi'] ?? 'Universitas / Sekolah',
                'fakultas'    => $row['fakultas'] ?? null,
                'jurusan'     => $row['jurusan'] ?? null,
                'tahun_lulus' => !empty($row['tahun_lulus']) ? (int)$row['tahun_lulus'] : null,
            ];
            if ($fileIjazah) {
                $payload['ijazah'] = $fileIjazah;
            }

            if ($isUpdate && !empty($row['id'])) {
                $pegawai->riwayatPendidikan()->where('id', $row['id'])->update($payload);
            } else {
                $pegawai->riwayatPendidikan()->create($payload);
            }
        }
    }

    /**
     * 2. Riwayat Diklat
     */
    protected function syncDiklat(Pegawai $pegawai, array $data, array $files, bool $isUpdate): void
    {
        $incoming = $data['riwayat_diklat'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $existingIds = $pegawai->riwayatDiklat()->pluck('id')->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $pegawai->riwayatDiklat()->whereIn('id', array_diff($existingIds, $incomingIds))->delete();
        }

        foreach ($incoming as $index => $row) {
            if (empty($row['nama_diklat'])) {
                continue;
            }

            $fileSertifikat = null;
            if (isset($files['riwayat_diklat'][$index]['file_sertifikat'])) {
                if ($isUpdate && !empty($row['id'])) {
                    $old = $pegawai->riwayatDiklat()->where('id', $row['id'])->first();
                    if ($old?->file_sertifikat) {
                        PegawaiStorageService::delete($old->file_sertifikat);
                    }
                }
                $fileSertifikat = PegawaiStorageService::store($files['riwayat_diklat'][$index]['file_sertifikat'], $pegawai, 'diklat', $row['nama_diklat'] ?? 'diklat');
                $this->syncFileToDrive($pegawai, $files['riwayat_diklat'][$index]['file_sertifikat'], 'SERTIFIKAT DIKLAT', '03_PENDIDIKAN_DIKLAT');
            }

            $tanggalMulai   = !empty($row['tanggal_mulai']) ? $row['tanggal_mulai'] : date('Y-m-d');
            $tanggalSelesai = !empty($row['tanggal_selesai']) ? $row['tanggal_selesai'] : $tanggalMulai;

            $payload = [
                'nama_diklat'      => $row['nama_diklat'],
                'jenis_diklat'     => $row['jenis_diklat'] ?? null,
                'penyelenggara'    => $row['penyelenggara'] ?? null,
                'nomor_sertifikat' => $row['nomor_sertifikat'] ?? null,
                'tanggal_mulai'    => $tanggalMulai,
                'tanggal_selesai'  => $tanggalSelesai,
                'status'           => $row['status'] ?? 'Aktif',
                'keterangan'       => $row['keterangan'] ?? null,
            ];
            if ($fileSertifikat) {
                $payload['file_sertifikat'] = $fileSertifikat;
            }

            if ($isUpdate && !empty($row['id'])) {
                $pegawai->riwayatDiklat()->where('id', $row['id'])->update($payload);
            } else {
                $pegawai->riwayatDiklat()->create($payload);
            }
        }
    }

    /**
     * 3. Riwayat Jabatan
     */
    protected function syncJabatan(Pegawai $pegawai, array $data, array $files, bool $isUpdate): void
    {
        $incoming = $data['riwayat_jabatan'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $existingIds = $pegawai->riwayatJabatan()->pluck('id')->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $pegawai->riwayatJabatan()->whereIn('id', array_diff($existingIds, $incomingIds))->delete();
        }

        $activeIndices = [];
        foreach ($incoming as $index => $row) {
            if (!empty($row['jabatan_id']) && !empty($row['unit_kerja_id']) && in_array(strtolower($row['status'] ?? ''), ['aktif'])) {
                $activeIndices[] = $index;
            }
        }
        $lastActiveIndex = !empty($activeIndices) ? end($activeIndices) : null;

        foreach ($incoming as $index => $row) {
            if (empty($row['jabatan_id']) || empty($row['unit_kerja_id'])) {
                continue;
            }

            $fileSk = null;
            if (isset($files['riwayat_jabatan'][$index]['file_sk'])) {
                if ($isUpdate && !empty($row['id'])) {
                    $old = $pegawai->riwayatJabatan()->where('id', $row['id'])->first();
                    if ($old?->file_sk) {
                        PegawaiStorageService::delete($old->file_sk);
                    }
                }
                $fileSk = PegawaiStorageService::store($files['riwayat_jabatan'][$index]['file_sk'], $pegawai, 'sk_jabatan');
                $this->syncFileToDrive($pegawai, $files['riwayat_jabatan'][$index]['file_sk'], 'SK JABATAN', '02_RIWAYAT_SK');
            }

            $status = ($lastActiveIndex !== null && $index === $lastActiveIndex) ? 'aktif' : 'nonaktif';

            $payload = [
                'jabatan_id'    => $row['jabatan_id'],
                'unit_kerja_id' => $row['unit_kerja_id'],
                'tmt_jabatan'   => !empty($row['tmt_jabatan']) ? $row['tmt_jabatan'] : now(),
                'nomor_sk'      => $row['nomor_sk'] ?? null,
                'tanggal_sk'    => $row['tanggal_sk'] ?? null,
                'status'        => $status,
                'keterangan'    => $row['keterangan'] ?? ($isUpdate ? 'Update dari form edit pegawai' : 'Input dari form tambah pegawai'),
            ];
            if ($fileSk) {
                $payload['file_sk'] = $fileSk;
            }

            if ($isUpdate && !empty($row['id'])) {
                $pegawai->riwayatJabatan()->where('id', $row['id'])->update($payload);
            } else {
                $pegawai->riwayatJabatan()->create($payload);
            }
        }
    }

    /**
     * 4. Riwayat Pangkat
     */
    protected function syncPangkat(Pegawai $pegawai, array $data, array $files, bool $isUpdate): void
    {
        $incoming = $data['riwayat_pangkat'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $existingIds = $pegawai->riwayatPangkat()->pluck('id')->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $pegawai->riwayatPangkat()->whereIn('id', array_diff($existingIds, $incomingIds))->delete();
        }

        $activeIndices = [];
        foreach ($incoming as $index => $row) {
            if (!empty($row['golongan_id']) && in_array(strtolower($row['status'] ?? ''), ['aktif'])) {
                $activeIndices[] = $index;
            }
        }
        $lastActiveIndex = !empty($activeIndices) ? end($activeIndices) : null;

        foreach ($incoming as $index => $row) {
            if (empty($row['golongan_id'])) {
                continue;
            }

            $fileSk = null;
            if (isset($files['riwayat_pangkat'][$index]['file_sk'])) {
                if ($isUpdate && !empty($row['id'])) {
                    $old = $pegawai->riwayatPangkat()->where('id', $row['id'])->first();
                    if ($old?->file_sk) {
                        PegawaiStorageService::delete($old->file_sk);
                    }
                }
                $fileSk = PegawaiStorageService::store($files['riwayat_pangkat'][$index]['file_sk'], $pegawai, 'sk_pangkat');
                $this->syncFileToDrive($pegawai, $files['riwayat_pangkat'][$index]['file_sk'], 'SK PANGKAT', '02_RIWAYAT_SK');
            }

            $status = ($lastActiveIndex !== null && $index === $lastActiveIndex) ? 'aktif' : 'nonaktif';

            $payload = [
                'golongan_id' => $row['golongan_id'],
                'tmt'         => !empty($row['tmt']) ? $row['tmt'] : now(),
                'nomor_sk'    => $row['nomor_sk'] ?? null,
                'tanggal_sk'  => $row['tanggal_sk'] ?? null,
                'status'      => $status,
                'keterangan'  => $row['keterangan'] ?? ($isUpdate ? 'Update dari form edit pegawai' : 'Input dari form tambah pegawai'),
            ];
            if ($fileSk) {
                $payload['file_sk'] = $fileSk;
            }

            if ($isUpdate && !empty($row['id'])) {
                $pegawai->riwayatPangkat()->where('id', $row['id'])->update($payload);
            } else {
                $pegawai->riwayatPangkat()->create($payload);
            }
        }
    }

    /**
     * 5. Riwayat SKP (2 Tahun Lalu)
     */
    protected function syncSkp(Pegawai $pegawai, array $data, array $files, bool $isUpdate): void
    {
        $incoming = $data['riwayat_skp'] ?? [];
        if (!is_array($incoming)) return;

        foreach ($incoming as $index => $row) {
            if (empty($row['predikat_kinerja']) && empty($row['pejabat_penilai']) && !isset($files['riwayat_skp'][$index]['file_evaluasi_skp']) && empty($row['id'])) {
                continue;
            }

            $tahunSkp = !empty($row['tahun']) ? (int)$row['tahun'] : ((int)date('Y') - ($index + 1));
            $fileEvaluasi = null;

            if (isset($files['riwayat_skp'][$index]['file_evaluasi_skp'])) {
                if ($isUpdate && !empty($row['id'])) {
                    $old = $pegawai->riwayatSkp()->where('id', $row['id'])->first();
                    if ($old?->file_evaluasi_skp) {
                        PegawaiStorageService::delete($old->file_evaluasi_skp);
                    }
                }
                $fileEvaluasi = PegawaiStorageService::store($files['riwayat_skp'][$index]['file_evaluasi_skp'], $pegawai, 'skp', 'skp_' . $tahunSkp);
                $this->syncFileToDrive($pegawai, $files['riwayat_skp'][$index]['file_evaluasi_skp'], 'EVALUASI SKP ' . $tahunSkp, '08_SKP');
            }

            $payload = [
                'tahun'            => $tahunSkp,
                'predikat_kinerja' => $row['predikat_kinerja'] ?? null,
                'pejabat_penilai'  => $row['pejabat_penilai'] ?? null,
                'keterangan'       => $isUpdate ? 'Update dari formulir profil pegawai' : 'Input otomatis dari formulir pegawai',
            ];
            if ($fileEvaluasi) {
                $payload['file_evaluasi_skp'] = $fileEvaluasi;
            }

            if ($isUpdate && !empty($row['id'])) {
                $pegawai->riwayatSkp()->where('id', $row['id'])->update($payload);
            } else {
                $existing = $pegawai->riwayatSkp()->where('tahun', $tahunSkp)->first();
                if ($existing) {
                    $existing->update($payload);
                } else {
                    $pegawai->riwayatSkp()->create($payload);
                }
            }
        }
    }

    /**
     * 6. Piagam Satyalancana
     */
    protected function syncSatyalancana(Pegawai $pegawai, array $data, array $files, bool $isUpdate): void
    {
        $piagamData = $data['piagam_satyalancana'] ?? null;
        if (!is_array($piagamData)) return;

        if (!empty($piagamData['nama_penghargaan']) || !empty($piagamData['id'])) {
            $filePiagam = null;
            if (isset($files['piagam_satyalancana']['file_sk'])) {
                if ($isUpdate && !empty($piagamData['id'])) {
                    $old = $pegawai->riwayatPenghargaan()->where('id', $piagamData['id'])->first();
                    if ($old?->file_sk) {
                        PegawaiStorageService::delete($old->file_sk);
                    }
                }
                $filePiagam = PegawaiStorageService::store($files['piagam_satyalancana']['file_sk'], $pegawai, 'penghargaan', 'piagam_satyalancana');
                $this->syncFileToDrive($pegawai, $files['piagam_satyalancana']['file_sk'], 'PIAGAM ' . strtoupper($piagamData['nama_penghargaan'] ?? 'SATYALANCANA'), '16_PENGHARGAAN');
            }

            $payload = [
                'nama_penghargaan'  => $piagamData['nama_penghargaan'] ?? 'Satyalancana Karya Satya',
                'jenis_penghargaan' => 'Satyalancana',
                'instansi_pemberi'  => $piagamData['instansi_pemberi'] ?? 'Presiden Republik Indonesia',
                'tanggal_terima'    => !empty($piagamData['tanggal_sk']) ? $piagamData['tanggal_sk'] : null,
                'nomor_sk'          => $piagamData['nomor_sk'] ?? null,
                'keterangan'        => $isUpdate ? 'Update dari profil pegawai' : 'Tercatat dari profil awal kepegawaian',
            ];
            if ($filePiagam) {
                $payload['file_sk'] = $filePiagam;
            }

            if ($isUpdate && !empty($piagamData['id'])) {
                $pegawai->riwayatPenghargaan()->where('id', $piagamData['id'])->update($payload);
            } else {
                $existing = $pegawai->riwayatPenghargaan()->where('jenis_penghargaan', 'Satyalancana')->first();
                if ($existing) {
                    $existing->update($payload);
                } else {
                    $pegawai->riwayatPenghargaan()->create($payload);
                }
            }

            if (!empty($piagamData['nama_penghargaan'])) {
                $pegawai->update(['satyalancana_terakhir' => $piagamData['nama_penghargaan']]);
            }
        }
    }

    /**
     * Otomatisasi Sinkronisasi Berkas ke Google Drive GAS
     */
    protected function syncFileToDrive(?Pegawai $pegawai, $file, string $tipeDokumen, string $kategoriFolder = '00_BERKAS_LAINNYA'): void
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
