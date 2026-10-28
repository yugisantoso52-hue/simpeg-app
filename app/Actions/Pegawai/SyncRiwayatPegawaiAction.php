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
     * Menyinkronkan seluruh riwayat kepegawaian (Pendidikan, Diklat, Jabatan, Pangkat, SKP, Satyalancana, Penghargaan, Organisasi, Publikasi, Tugas Belajar)
     */
    public function execute(Pegawai $pegawai, array $data, array $files = [], bool $isUpdate = false): void
    {
        $this->syncPendidikan($pegawai, $data, $files, $isUpdate);
        $this->syncDiklat($pegawai, $data, $files, $isUpdate);
        $this->syncJabatan($pegawai, $data, $files, $isUpdate);
        $this->syncPangkat($pegawai, $data, $files, $isUpdate);
        $this->syncSkp($pegawai, $data, $files, $isUpdate);
        $this->syncSatyalancana($pegawai, $data, $files, $isUpdate);
        $this->syncPenghargaanLainnya($pegawai, $data, $files, $isUpdate);
        $this->syncOrganisasi($pegawai, $data, $isUpdate);
        $this->syncPublikasi($pegawai, $data, $files, $isUpdate);
        $this->syncTugasBelajar($pegawai, $data, $files, $isUpdate);
    }

    /**
     * 1. Riwayat Pendidikan
     */
    protected function syncPendidikan(Pegawai $pegawai, array $data, array $files, bool $isUpdate): void
    {
        if (!array_key_exists('riwayat_pendidikan', $data)) {
            return;
        }
        $incoming = $data['riwayat_pendidikan'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $existingIds = $pegawai->riwayatPendidikan()->pluck('id')->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $deletedIds = array_diff($existingIds, $incomingIds);
            if (!empty($deletedIds)) {
                $toDelete = $pegawai->riwayatPendidikan()->whereIn('id', $deletedIds)->get();
                foreach ($toDelete as $item) {
                    if (!empty($item->ijazah)) {
                        PegawaiStorageService::delete($item->ijazah);
                    }
                    $item->delete();
                }
            }
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
        if (!array_key_exists('riwayat_diklat', $data)) {
            return;
        }
        $incoming = $data['riwayat_diklat'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $existingIds = $pegawai->riwayatDiklat()->pluck('id')->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $deletedIds = array_diff($existingIds, $incomingIds);
            if (!empty($deletedIds)) {
                $toDelete = $pegawai->riwayatDiklat()->whereIn('id', $deletedIds)->get();
                foreach ($toDelete as $item) {
                    if (!empty($item->file_sertifikat)) {
                        PegawaiStorageService::delete($item->file_sertifikat);
                    }
                    $item->delete();
                }
            }
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
                'jumlah_jam'       => isset($row['jumlah_jam']) && $row['jumlah_jam'] !== '' ? (int)$row['jumlah_jam'] : null,
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
        if (!array_key_exists('riwayat_jabatan', $data) || empty($data['riwayat_jabatan'])) {
            $activeJabatan = $pegawai->riwayatJabatan()->whereIn('status', ['aktif', 'Aktif'])->first();
            if ($activeJabatan) {
                $updates = [];
                if (!empty($data['unit_kerja_id'])) {
                    $updates['unit_kerja_id'] = $data['unit_kerja_id'];
                }
                if (!empty($data['jabatan_id'])) {
                    $updates['jabatan_id'] = $data['jabatan_id'];
                }
                if (!empty($data['nomor_sk_pertama'])) {
                    $updates['nomor_sk'] = $data['nomor_sk_pertama'];
                }
                if (!empty($data['tanggal_sk_pertama'])) {
                    $updates['tanggal_sk'] = $data['tanggal_sk_pertama'];
                }
                if (!empty($data['tmt_sk_pertama'])) {
                    $updates['tmt_jabatan'] = $data['tmt_sk_pertama'];
                }
                if (!empty($updates)) {
                    $activeJabatan->update($updates);
                }
            } elseif (!empty($data['jabatan_id']) && !empty($data['unit_kerja_id'])) {
                $pegawai->riwayatJabatan()->create([
                    'jabatan_id'    => $data['jabatan_id'],
                    'unit_kerja_id' => $data['unit_kerja_id'],
                    'tmt_jabatan'   => !empty($data['tmt_sk_pertama']) ? $data['tmt_sk_pertama'] : (!empty($data['tanggal_masuk']) ? $data['tanggal_masuk'] : now()),
                    'nomor_sk'      => $data['nomor_sk_pertama'] ?? null,
                    'tanggal_sk'    => $data['tanggal_sk_pertama'] ?? null,
                    'status'        => 'aktif',
                    'keterangan'    => 'Sinkronisasi otomatis data pegawai',
                ]);
            }
            return;
        }
        $incoming = $data['riwayat_jabatan'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $existingIds = $pegawai->riwayatJabatan()->pluck('id')->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $deletedIds = array_diff($existingIds, $incomingIds);
            if (!empty($deletedIds)) {
                $toDelete = $pegawai->riwayatJabatan()->whereIn('id', $deletedIds)->get();
                foreach ($toDelete as $item) {
                    if (!empty($item->file_sk)) {
                        PegawaiStorageService::delete($item->file_sk);
                    }
                    $item->delete();
                }
            }
        }

        $activeIndices = [];
        $validIndices = [];
        foreach ($incoming as $index => $row) {
            if (!empty($row['jabatan_id']) || !empty($row['unit_kerja_id'])) {
                $validIndices[] = $index;
            }
            if (!empty($row['jabatan_id']) && !empty($row['unit_kerja_id']) && in_array(strtolower($row['status'] ?? ''), ['aktif'])) {
                $activeIndices[] = $index;
            }
        }
        $lastActiveIndex = !empty($activeIndices) ? end($activeIndices) : (!empty($validIndices) ? end($validIndices) : null);

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

            // Jika baris riwayat ini aktif, utamakan unit_kerja_id dan jabatan_id dari form induk
            $finalUnitKerja = $row['unit_kerja_id'];
            $finalJabatan   = $row['jabatan_id'];
            if ($status === 'aktif') {
                if (!empty($data['unit_kerja_id'])) {
                    $finalUnitKerja = $data['unit_kerja_id'];
                }
                if (!empty($data['jabatan_id'])) {
                    $finalJabatan = $data['jabatan_id'];
                }
            }

            $payload = [
                'jabatan_id'    => $finalJabatan,
                'unit_kerja_id' => $finalUnitKerja,
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
        if (!array_key_exists('riwayat_pangkat', $data) || empty($data['riwayat_pangkat'])) {
            $activePangkat = $pegawai->riwayatPangkat()->whereIn('status', ['aktif', 'Aktif'])->first();
            if ($activePangkat && !empty($data['golongan_id'])) {
                $updates = ['golongan_id' => $data['golongan_id']];
                if (!empty($data['tmt_pangkat_terakhir'])) {
                    $updates['tmt'] = $data['tmt_pangkat_terakhir'];
                }
                if (!empty($data['nomor_sk_pangkat_terakhir'])) {
                    $updates['nomor_sk'] = $data['nomor_sk_pangkat_terakhir'];
                }
                if (!empty($data['tanggal_sk_pangkat_terakhir'])) {
                    $updates['tanggal_sk'] = $data['tanggal_sk_pangkat_terakhir'];
                }
                $activePangkat->update($updates);
            } elseif (!empty($data['golongan_id'])) {
                $pegawai->riwayatPangkat()->create([
                    'golongan_id' => $data['golongan_id'],
                    'tmt'         => !empty($data['tmt_pangkat_terakhir']) ? $data['tmt_pangkat_terakhir'] : now(),
                    'nomor_sk'    => $data['nomor_sk_pangkat_terakhir'] ?? null,
                    'tanggal_sk'  => $data['tanggal_sk_pangkat_terakhir'] ?? null,
                    'status'      => 'aktif',
                    'keterangan'  => 'Sinkronisasi otomatis data pegawai',
                ]);
            }
            return;
        }
        $incoming = $data['riwayat_pangkat'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $existingIds = $pegawai->riwayatPangkat()->pluck('id')->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $deletedIds = array_diff($existingIds, $incomingIds);
            if (!empty($deletedIds)) {
                $toDelete = $pegawai->riwayatPangkat()->whereIn('id', $deletedIds)->get();
                foreach ($toDelete as $item) {
                    if (!empty($item->file_sk)) {
                        PegawaiStorageService::delete($item->file_sk);
                    }
                    $item->delete();
                }
            }
        }

        $activeIndices = [];
        $validIndices = [];
        foreach ($incoming as $index => $row) {
            if (!empty($row['golongan_id'])) {
                $validIndices[] = $index;
            }
            if (!empty($row['golongan_id']) && in_array(strtolower($row['status'] ?? ''), ['aktif'])) {
                $activeIndices[] = $index;
            }
        }
        $lastActiveIndex = !empty($activeIndices) ? end($activeIndices) : (!empty($validIndices) ? end($validIndices) : null);

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

            // Jika baris riwayat ini aktif, utamakan golongan_id dari form induk
            $finalGolongan = $row['golongan_id'];
            if ($status === 'aktif' && !empty($data['golongan_id'])) {
                $finalGolongan = $data['golongan_id'];
            }

            $payload = [
                'golongan_id' => $finalGolongan,
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
     * 5. Riwayat SKP (2 Tahun)
     */
    protected function syncSkp(Pegawai $pegawai, array $data, array $files, bool $isUpdate): void
    {
        $incoming = $data['riwayat_skp'] ?? [];
        if (!is_array($incoming)) return;

        foreach ($incoming as $index => $row) {
            $hasRencanaFile = isset($files['riwayat_skp'][$index]['file_rencana_skp']);
            $hasEvaluasiFile = isset($files['riwayat_skp'][$index]['file_evaluasi_skp']);

            if (empty($row['predikat_kinerja']) && empty($row['pejabat_penilai']) && !$hasRencanaFile && !$hasEvaluasiFile && empty($row['id'])) {
                continue;
            }

            $tahunSkp = !empty($row['tahun']) ? (int)$row['tahun'] : ((int)date('Y') - ($index + 1));
            $fileRencana = null;
            $fileEvaluasi = null;

            if ($hasRencanaFile) {
                if ($isUpdate && !empty($row['id'])) {
                    $old = $pegawai->riwayatSkp()->where('id', $row['id'])->first();
                    if ($old?->file_rencana_skp) {
                        PegawaiStorageService::delete($old->file_rencana_skp);
                    }
                }
                $fileRencana = PegawaiStorageService::store($files['riwayat_skp'][$index]['file_rencana_skp'], $pegawai, 'skp', 'rencana_skp_' . $tahunSkp);
                $this->syncFileToDrive($pegawai, $files['riwayat_skp'][$index]['file_rencana_skp'], 'RENCANA SKP ' . $tahunSkp, '08_SKP');
            }

            if ($hasEvaluasiFile) {
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
            if ($fileRencana) {
                $payload['file_rencana_skp'] = $fileRencana;
            }
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
     * 7. Riwayat Penghargaan & Tanda Jasa Lainnya
     */
    protected function syncPenghargaanLainnya(Pegawai $pegawai, array $data, array $files, bool $isUpdate): void
    {
        if (!array_key_exists('riwayat_penghargaan', $data)) {
            return;
        }
        $incoming = $data['riwayat_penghargaan'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $satyalancanaId = $data['piagam_satyalancana']['id'] ?? $pegawai->riwayatPenghargaan()->where('jenis_penghargaan', 'Satyalancana')->value('id');
            $existingIds = $pegawai->riwayatPenghargaan()
                ->when($satyalancanaId, fn($q) => $q->where('id', '!=', $satyalancanaId))
                ->pluck('id')
                ->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $deletedIds = array_diff($existingIds, $incomingIds);
            if (!empty($deletedIds)) {
                $toDelete = $pegawai->riwayatPenghargaan()->whereIn('id', $deletedIds)->get();
                foreach ($toDelete as $item) {
                    if (!empty($item->file_sk)) {
                        PegawaiStorageService::delete($item->file_sk);
                    }
                    $item->delete();
                }
            }
        }

        foreach ($incoming as $index => $row) {
            if (empty($row['nama_penghargaan'])) {
                continue;
            }

            $fileSk = null;
            if (isset($files['riwayat_penghargaan'][$index]['file_sk'])) {
                if ($isUpdate && !empty($row['id'])) {
                    $old = $pegawai->riwayatPenghargaan()->where('id', $row['id'])->first();
                    if ($old?->file_sk) {
                        PegawaiStorageService::delete($old->file_sk);
                    }
                }
                $fileSk = PegawaiStorageService::store($files['riwayat_penghargaan'][$index]['file_sk'], $pegawai, 'penghargaan', $row['nama_penghargaan']);
                $this->syncFileToDrive($pegawai, $files['riwayat_penghargaan'][$index]['file_sk'], 'PENGHARGAAN ' . strtoupper($row['nama_penghargaan']), '16_PENGHARGAAN');
            }

            $payload = [
                'nama_penghargaan'  => $row['nama_penghargaan'],
                'jenis_penghargaan' => $row['jenis_penghargaan'] ?? 'Penghargaan',
                'instansi_pemberi'  => $row['instansi_pemberi'] ?? null,
                'nomor_sk'          => $row['nomor_sk'] ?? null,
                'tanggal_terima'    => !empty($row['tanggal_terima']) ? $row['tanggal_terima'] : null,
                'keterangan'        => $row['keterangan'] ?? null,
            ];
            if ($fileSk) {
                $payload['file_sk'] = $fileSk;
            }

            if ($isUpdate && !empty($row['id'])) {
                $pegawai->riwayatPenghargaan()->where('id', $row['id'])->update($payload);
            } else {
                $pegawai->riwayatPenghargaan()->create($payload);
            }
        }
    }

    /**
     * 8. Riwayat Keanggotaan Organisasi
     */
    protected function syncOrganisasi(Pegawai $pegawai, array $data, bool $isUpdate): void
    {
        if (!array_key_exists('riwayat_organisasi', $data)) {
            return;
        }
        $incoming = $data['riwayat_organisasi'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $existingIds = $pegawai->riwayatOrganisasi()->pluck('id')->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $pegawai->riwayatOrganisasi()->whereIn('id', array_diff($existingIds, $incomingIds))->delete();
        }

        foreach ($incoming as $row) {
            if (empty($row['nama_organisasi'])) {
                continue;
            }

            $payload = [
                'nama_organisasi'    => $row['nama_organisasi'],
                'jabatan_organisasi' => $row['jabatan_organisasi'] ?? null,
                'tahun_mulai'        => !empty($row['tahun_mulai']) ? (int)$row['tahun_mulai'] : null,
                'tahun_selesai'      => !empty($row['tahun_selesai']) ? (int)$row['tahun_selesai'] : null,
                'masih_aktif'        => isset($row['masih_aktif']) ? (bool)$row['masih_aktif'] : true,
                'keterangan'         => $row['keterangan'] ?? null,
            ];

            if ($isUpdate && !empty($row['id'])) {
                $pegawai->riwayatOrganisasi()->where('id', $row['id'])->update($payload);
            } else {
                $pegawai->riwayatOrganisasi()->create($payload);
            }
        }
    }

    /**
     * 9. Riwayat Publikasi Ilmiah & Karya
     */
    protected function syncPublikasi(Pegawai $pegawai, array $data, array $files, bool $isUpdate): void
    {
        if (!array_key_exists('riwayat_publikasi', $data)) {
            return;
        }
        $incoming = $data['riwayat_publikasi'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $existingIds = $pegawai->riwayatPublikasi()->pluck('id')->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $deletedIds = array_diff($existingIds, $incomingIds);
            if (!empty($deletedIds)) {
                $toDelete = $pegawai->riwayatPublikasi()->whereIn('id', $deletedIds)->get();
                foreach ($toDelete as $item) {
                    if (!empty($item->file_publikasi)) {
                        PegawaiStorageService::delete($item->file_publikasi);
                    }
                    $item->delete();
                }
            }
        }

        foreach ($incoming as $index => $row) {
            if (empty($row['judul_publikasi'])) {
                continue;
            }

            $filePub = null;
            if (isset($files['riwayat_publikasi'][$index]['file_publikasi'])) {
                if ($isUpdate && !empty($row['id'])) {
                    $old = $pegawai->riwayatPublikasi()->where('id', $row['id'])->first();
                    if ($old?->file_publikasi) {
                        PegawaiStorageService::delete($old->file_publikasi);
                    }
                }
                $filePub = PegawaiStorageService::store($files['riwayat_publikasi'][$index]['file_publikasi'], $pegawai, 'publikasi', $row['judul_publikasi']);
                $this->syncFileToDrive($pegawai, $files['riwayat_publikasi'][$index]['file_publikasi'], 'PUBLIKASI ILMIAH', '17_PUBLIKASI');
            }

            $validJenisPub = ['Jurnal', 'Prosiding', 'Buku', 'Book Chapter', 'Paten', 'HKI', 'Lainnya'];
            $rawJenisPub = $row['jenis_publikasi'] ?? 'Jurnal';
            $jenisPub = in_array($rawJenisPub, $validJenisPub, true)
                ? $rawJenisPub
                : (str_contains(strtolower($rawJenisPub), 'jurnal') ? 'Jurnal' : (str_contains(strtolower($rawJenisPub), 'prosiding') ? 'Prosiding' : (str_contains(strtolower($rawJenisPub), 'buku') ? 'Buku' : 'Lainnya')));

            $validIndeksasi = ['Scopus', 'WoS', 'SINTA 1', 'SINTA 2', 'SINTA 3', 'SINTA 4', 'SINTA 5', 'SINTA 6', 'Nasional Terakreditasi', 'Nasional Tidak Terakreditasi', 'Lainnya'];
            $rawIndeksasi = $row['indeksasi'] ?? null;
            if (!empty($rawIndeksasi) && !in_array($rawIndeksasi, $validIndeksasi, true)) {
                $rawIndeksasi = str_contains(strtolower($rawIndeksasi), 'scopus') ? 'Scopus' : (str_contains(strtolower($rawIndeksasi), 'wos') ? 'WoS' : 'Lainnya');
            }

            $payload = [
                'judul_publikasi' => $row['judul_publikasi'],
                'jenis_publikasi' => $jenisPub,
                'indeksasi'       => !empty($rawIndeksasi) ? $rawIndeksasi : null,
                'nama_jurnal'     => $row['nama_jurnal'] ?? null,
                'penerbit'        => $row['penerbit'] ?? ($row['nama_jurnal'] ?? null),
                'tahun_terbit'    => !empty($row['tahun_terbit']) ? (int)$row['tahun_terbit'] : null,
                'volume_nomor'    => $row['volume_nomor'] ?? null,
                'url_doi'         => $row['url_doi'] ?? null,
                'keterangan'      => $row['keterangan'] ?? null,
            ];
            if ($filePub) {
                $payload['file_publikasi'] = $filePub;
            }

            if ($isUpdate && !empty($row['id'])) {
                $pegawai->riwayatPublikasi()->where('id', $row['id'])->update($payload);
            } else {
                $pegawai->riwayatPublikasi()->create($payload);
            }
        }
    }

    /**
     * 10. Tugas Belajar & Izin Belajar (Studi Lanjut)
     */
    protected function syncTugasBelajar(Pegawai $pegawai, array $data, array $files, bool $isUpdate): void
    {
        if (!array_key_exists('tugas_belajar', $data)) {
            return;
        }
        $incoming = $data['tugas_belajar'] ?? [];
        if (!is_array($incoming)) return;

        if ($isUpdate) {
            $existingIds = $pegawai->tugasBelajar()->pluck('id')->toArray();
            $incomingIds = collect($incoming)->pluck('id')->filter()->toArray();
            $deletedIds = array_diff($existingIds, $incomingIds);
            if (!empty($deletedIds)) {
                $toDelete = $pegawai->tugasBelajar()->whereIn('id', $deletedIds)->get();
                foreach ($toDelete as $item) {
                    if (!empty($item->file_sk)) {
                        PegawaiStorageService::delete($item->file_sk);
                    }
                    if (!empty($item->file_laporan_progress)) {
                        PegawaiStorageService::delete($item->file_laporan_progress);
                    }
                    $item->delete();
                }
            }
        }

        foreach ($incoming as $index => $row) {
            if (empty($row['perguruan_tinggi']) && empty($row['program_studi'])) {
                continue;
            }

            $fileSk = null;
            if (isset($files['tugas_belajar'][$index]['file_sk'])) {
                if ($isUpdate && !empty($row['id'])) {
                    $old = $pegawai->tugasBelajar()->where('id', $row['id'])->first();
                    if ($old?->file_sk) {
                        PegawaiStorageService::delete($old->file_sk);
                    }
                }
                $fileSk = PegawaiStorageService::store($files['tugas_belajar'][$index]['file_sk'], $pegawai, 'tugas_belajar', 'sk_tubel');
                $this->syncFileToDrive($pegawai, $files['tugas_belajar'][$index]['file_sk'], 'SK TUGAS BELAJAR', '03_PENDIDIKAN_DIKLAT');
            }

            $validJenisTubel = ['Tugas Belajar', 'Izin Belajar'];
            $rawJenisTubel = $row['jenis_pengembangan'] ?? 'Tugas Belajar';
            $jenisTubel = in_array($rawJenisTubel, $validJenisTubel, true) ? $rawJenisTubel : 'Tugas Belajar';

            $validJenjang = ['S2', 'S3', 'Spesialis', 'Subspesialis', 'Post Doctoral'];
            $rawJenjang = $row['jenjang_studi'] ?? 'S2';
            $jenjangStudi = in_array($rawJenjang, $validJenjang, true)
                ? $rawJenjang
                : (str_contains($rawJenjang, 'S3') ? 'S3' : (str_contains($rawJenjang, 'S2') ? 'S2' : 'Spesialis'));

            $validStatusStudi = ['Sedang Studi', 'Perpanjangan', 'Lulus', 'Dibatalkan / DO'];
            $rawStatusStudi = $row['status_studi'] ?? 'Sedang Studi';
            $statusStudi = in_array($rawStatusStudi, $validStatusStudi, true) ? $rawStatusStudi : 'Sedang Studi';

            $tglMulai = !empty($row['tanggal_mulai']) ? $row['tanggal_mulai'] : now()->toDateString();
            $tglSelesai = !empty($row['tanggal_selesai']) ? $row['tanggal_selesai'] : \Carbon\Carbon::parse($tglMulai)->addYears(2)->toDateString();

            $payload = [
                'jenis_pengembangan' => $jenisTubel,
                'jenjang_studi'      => $jenjangStudi,
                'perguruan_tinggi'   => $row['perguruan_tinggi'] ?? '-',
                'program_studi'      => $row['program_studi'] ?? '-',
                'negara'             => $row['negara'] ?? 'Indonesia',
                'sumber_pembiayaan'  => $row['sumber_pembiayaan'] ?? 'Mandiri / Beasiswa',
                'nomor_sk'           => !empty($row['nomor_sk']) ? $row['nomor_sk'] : '-',
                'tanggal_mulai'      => $tglMulai,
                'tanggal_selesai'    => $tglSelesai,
                'semester_berjalan'  => !empty($row['semester_berjalan']) ? (int)$row['semester_berjalan'] : 1,
                'status_studi'       => $statusStudi,
                'keterangan'         => $row['keterangan'] ?? null,
            ];
            if ($fileSk) {
                $payload['file_sk'] = $fileSk;
            }

            if ($isUpdate && !empty($row['id'])) {
                $pegawai->tugasBelajar()->where('id', $row['id'])->update($payload);
            } else {
                $pegawai->tugasBelajar()->create($payload);
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
