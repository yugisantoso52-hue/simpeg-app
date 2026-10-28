<?php

namespace App\Services;

use App\Actions\Pegawai\HandleDokumenPegawaiAction;
use App\Actions\Pegawai\SyncAkunUserPegawaiAction;
use App\Actions\Pegawai\SyncRiwayatPegawaiAction;
use App\Actions\Pegawai\UpsertDataDiriPegawaiAction;
use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\RiwayatPendidikan;
use App\Models\RiwayatDiklat;
use App\Models\User;
use App\Repositories\Contracts\PegawaiRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PegawaiService
{
    protected PegawaiRepositoryInterface $pegawaiRepository;
    protected HandleDokumenPegawaiAction $dokumenAction;
    protected UpsertDataDiriPegawaiAction $upsertAction;
    protected SyncAkunUserPegawaiAction $userAction;
    protected SyncRiwayatPegawaiAction $riwayatAction;

    public function __construct(
        PegawaiRepositoryInterface $pegawaiRepository,
        ?HandleDokumenPegawaiAction $dokumenAction = null,
        ?UpsertDataDiriPegawaiAction $upsertAction = null,
        ?SyncAkunUserPegawaiAction $userAction = null,
        ?SyncRiwayatPegawaiAction $riwayatAction = null
    ) {
        $this->pegawaiRepository = $pegawaiRepository;
        $this->dokumenAction   = $dokumenAction ?? app(HandleDokumenPegawaiAction::class);
        $this->upsertAction    = $upsertAction ?? app(UpsertDataDiriPegawaiAction::class);
        $this->userAction      = $userAction ?? app(SyncAkunUserPegawaiAction::class);
        $this->riwayatAction   = $riwayatAction ?? app(SyncRiwayatPegawaiAction::class);
    }

    /**
     * Upload foto pegawai ke storage arsip hierarkis
     */
    private function uploadFoto(?UploadedFile $foto, $pegawai = null): ?string
    {
        if (!$foto) return null;
        return PegawaiStorageService::store($foto, $pegawai, 'foto');
    }

    /**
     * Hapus foto pegawai dari storage
     */
    private function deleteFoto(?string $foto): void
    {
        PegawaiStorageService::delete($foto);
    }

    /**
     * Upload berkas SK pegawai ke storage arsip hierarkis
     */
    private function uploadSK(?UploadedFile $file, $pegawai = null, string $category = 'sk_pertama', ?string $title = null): ?string
    {
        if (!$file) return null;
        return PegawaiStorageService::store($file, $pegawai, $category, $title);
    }

    /**
     * Hapus berkas SK pegawai dari storage
     */
    private function deleteSK(?string $filePath): void
    {
        PegawaiStorageService::delete($filePath);
    }

    /**
     * Generate Kenaikan Gaji Berkala (KGB) - Berkelanjutan 2 Tahun
     */
    private function generateKGB($tmt): ?string
    {
        return !blank($tmt) ? Carbon::parse($tmt)->addYears(2)->format('Y-m-d') : null;
    }

    /**
     * Generate Kenaikan Pangkat (KP) - Berkelanjutan 4 Tahun
     */
    private function generateKP($tmt): ?string
    {
        return !blank($tmt) ? Carbon::parse($tmt)->addYears(4)->format('Y-m-d') : null;
    }

    /**
     * Hitung Prediksi Satyalancana Berdasarkan TMT SK Pertama
     */
    private function generateSatyalancana($tmtPertama): array
    {
        return $this->upsertAction->generateSatyalancana($tmtPertama);
    }

    public function search(?string $search, ?string $filter = null)
    {
        return $this->pegawaiRepository->searchFiltered($search, $filter);
    }

    public function find(string|int $id, bool $withRelations = true): Pegawai
    {
        $pegawai = $this->pegawaiRepository->findOrFail($id);

        if ($withRelations) {
            $pegawai->load([
                'unitKerja',
                'jabatan',
                'golongan',
                'atasan.jabatan',
                'pejabatPenilai.jabatan',
                'atasanCuti.jabatan',
                'riwayatPendidikan',
                'riwayatDiklat',
                'riwayatJabatan.jabatan',
                'riwayatJabatan.unitKerja',
                'riwayatPangkat.golongan',
                'riwayatStrSip',
                'tugasBelajar',
                'riwayatSkp',
                'riwayatPenghargaan',
                'riwayatOrganisasi',
                'riwayatPublikasi',
                'mutasi.unitKerjaAsal',
                'mutasi.unitKerjaTujuan',
                'pengajuanCuti',
            ]);
        } else {
            $pegawai->load(['unitKerja', 'jabatan', 'golongan', 'atasan', 'pejabatPenilai', 'atasanCuti']);
        }

        return $pegawai;
    }

    /**
     * Membuat data pegawai baru beserta akun user login, riwayat awal, dan berkas SK (Atomic Transaction)
     */
    public function createPegawai(array $data, array $files = []): Pegawai
    {
        return DB::transaction(function () use ($data, $files) {
            // 1. Proses upload dokumen utama (gunakan array data calon pegawai agar penamaan folder sesuai NIP & Nama)
            $docPaths = $this->dokumenAction->execute($files, $data);
            $data = array_merge($data, $docPaths);

            // 2. Simpan profil data diri pegawai
            $pegawai = $this->upsertAction->execute($data);

            // 3. Auto-sync file dokumen ke Drive setelah pegawai ID terbentuk
            foreach ($files as $field => $file) {
                if ($file instanceof UploadedFile) {
                    $this->dokumenAction->syncFileToDrive($pegawai, $file, strtoupper($field));
                }
            }

            // 4. Buat akun login user pegawai baru
            $this->userAction->execute($pegawai, null, true);

            // 5. Simpan seluruh riwayat awal (Pendidikan, Diklat, Jabatan, Pangkat, SKP, Satyalancana)
            $this->riwayatAction->execute($pegawai, $data, $files, false);

            // 6. Sinkronisasi data induk dari riwayat aktif
            $this->syncPegawaiFromHistories($pegawai);

            return $pegawai;
        });
    }

    /**
     * Memperbarui data pegawai, berkas SK, dan menyinkronkan seluruh riwayat terkait (Atomic Transaction)
     */
    public function updatePegawai(int|string $id, array $data, array $files = []): Pegawai
    {
        return DB::transaction(function () use ($id, $data, $files) {
            $pegawai = $this->pegawaiRepository->findOrFail($id);
            $oldNip = $pegawai->nip;

            // 1. Proses upload dan perbarui dokumen utama (hapus berkas lama)
            $docPaths = $this->dokumenAction->execute($files, $pegawai);
            $data = array_merge($data, $docPaths);

            // 2. Update data diri utama pegawai
            $pegawaiUpdated = $this->upsertAction->execute($data, $pegawai);

            // 3. Sinkronisasikan akun user login (NIP, Nama, Email)
            $this->userAction->execute($pegawaiUpdated, $oldNip, false);

            // 4. Sinkronisasikan seluruh riwayat terkait
            $this->riwayatAction->execute($pegawaiUpdated, $data, $files, true);

            // 4b. Simpan penugasan / keanggotaan non-struktural (KJFD, Unit Fungsional, Laboratorium)
            $this->syncPenugasan($pegawaiUpdated, $data);

            // 5. Sinkronisasi data induk dari riwayat aktif
            $this->syncPegawaiFromHistories($pegawaiUpdated);

            return $pegawaiUpdated;
        });
    }

    /**
     * Simpan penugasan / keanggotaan pegawai. Hanya dijalankan jika form mengirim bagian penugasan
     * (penugasan_submitted), sehingga update parsial tidak menghapus data yang sudah ada.
     */
    protected function syncPenugasan(Pegawai $pegawai, array $data): void
    {
        if (empty($data['penugasan_submitted'])) {
            return;
        }

        $incoming = collect($data['penugasan'] ?? [])
            ->filter(fn ($row) => !empty($row['kelompok_penugasan_id']))
            ->keyBy(fn ($row) => (int) $row['kelompok_penugasan_id']);

        $pegawai->penugasan()
            ->whereNotIn('kelompok_penugasan_id', $incoming->keys()->all())
            ->delete();

        foreach ($incoming as $kelompokId => $row) {
            $pegawai->penugasan()->updateOrCreate(
                ['kelompok_penugasan_id' => $kelompokId],
                ['peran' => $row['peran'] ?? 'Anggota']
            );
        }
    }

    public function deletePegawai(int|string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $pegawai = $this->pegawaiRepository->findOrFail($id);

            $this->deleteFoto($pegawai->foto);
            $this->deleteSK($pegawai->file_sk_pertama);
            $this->deleteSK($pegawai->file_sk_pangkat_terakhir);
            $this->deleteSK($pegawai->file_sk_kgb_terakhir);
            $this->deleteSK($pegawai->file_karpeg);
            $this->deleteSK($pegawai->file_pak);
            $this->deleteSK($pegawai->file_str);
            $this->deleteSK($pegawai->file_sip);

            // Hapus akun user pegawai jika ada
            User::where('pegawai_id', $pegawai->id)->delete();

            // Hapus berkas ijazah di riwayat pendidikan
            $riwayatPendidikan = RiwayatPendidikan::where('pegawai_id', $pegawai->id)->get();
            foreach ($riwayatPendidikan as $rp) {
                if ($rp->ijazah) {
                    $this->deleteSK($rp->ijazah);
                }
            }
            RiwayatPendidikan::where('pegawai_id', $pegawai->id)->delete();

            // Hapus berkas sertifikat di riwayat diklat
            $riwayatDiklat = RiwayatDiklat::where('pegawai_id', $pegawai->id)->get();
            foreach ($riwayatDiklat as $rd) {
                if ($rd->file_sertifikat) {
                    $this->deleteSK($rd->file_sertifikat);
                }
            }
            RiwayatDiklat::where('pegawai_id', $pegawai->id)->delete();

            // Hapus berkas SK di riwayat pangkat
            $riwayatPangkat = RiwayatPangkat::where('pegawai_id', $pegawai->id)->get();
            foreach ($riwayatPangkat as $rp) {
                if ($rp->file_sk) {
                    $this->deleteSK($rp->file_sk);
                }
            }
            RiwayatPangkat::where('pegawai_id', $pegawai->id)->delete();

            // Hapus berkas SK di riwayat jabatan
            $riwayatJabatan = RiwayatJabatan::where('pegawai_id', $pegawai->id)->get();
            foreach ($riwayatJabatan as $rj) {
                if ($rj->file_sk) {
                    $this->deleteSK($rj->file_sk);
                }
            }
            RiwayatJabatan::where('pegawai_id', $pegawai->id)->delete();

            // Hapus berkas SK dan data mutasi jika ada
            $mutasi = \App\Models\MutasiPegawai::where('pegawai_id', $pegawai->id)->get();
            foreach ($mutasi as $m) {
                if ($m->file_sk) {
                    $this->deleteSK($m->file_sk);
                }
            }
            \App\Models\MutasiPegawai::where('pegawai_id', $pegawai->id)->delete();

            return $this->pegawaiRepository->delete($pegawai->id);
        });
    }

    public function bulkDeletePegawai(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $publicFiles = [];
            $privateFiles = [];

            // Foto pegawai (selalu di public disk)
            $photos = Pegawai::whereIn('id', $ids)->pluck('foto')->filter()->toArray();
            foreach ($photos as $photo) {
                $publicFiles[] = $photo;
            }

            // Kumpulkan file lainnya (SK, Ijazah, Sertifikat)
            $otherFiles = array_merge(
                Pegawai::whereIn('id', $ids)->pluck('file_sk_pertama')->filter()->toArray(),
                Pegawai::whereIn('id', $ids)->pluck('file_sk_pangkat_terakhir')->filter()->toArray(),
                Pegawai::whereIn('id', $ids)->pluck('file_sk_kgb_terakhir')->filter()->toArray(),
                Pegawai::whereIn('id', $ids)->pluck('file_karpeg')->filter()->toArray(),
                Pegawai::whereIn('id', $ids)->pluck('file_pak')->filter()->toArray(),
                RiwayatPendidikan::whereIn('pegawai_id', $ids)->pluck('ijazah')->filter()->toArray(),
                RiwayatDiklat::whereIn('pegawai_id', $ids)->pluck('file_sertifikat')->filter()->toArray(),
                RiwayatPangkat::whereIn('pegawai_id', $ids)->pluck('file_sk')->filter()->toArray(),
                RiwayatJabatan::whereIn('pegawai_id', $ids)->pluck('file_sk')->filter()->toArray(),
                \App\Models\MutasiPegawai::whereIn('pegawai_id', $ids)->pluck('file_sk')->filter()->toArray()
            );

            foreach ($otherFiles as $filePath) {
                if (str_starts_with($filePath, 'pegawai/foto/')) {
                    $publicFiles[] = $filePath;
                } else {
                    $privateFiles[] = $filePath;
                }
            }

            // Hapus file secara fisik dari storage
            if (!empty($publicFiles)) {
                Storage::disk('public')->delete(array_unique($publicFiles));
            }
            if (!empty($privateFiles)) {
                Storage::disk('private')->delete(array_unique($privateFiles));
            }

            // Hapus data relasi dan akun login
            User::whereIn('pegawai_id', $ids)->delete();
            RiwayatPendidikan::whereIn('pegawai_id', $ids)->delete();
            RiwayatDiklat::whereIn('pegawai_id', $ids)->delete();
            RiwayatPangkat::whereIn('pegawai_id', $ids)->delete();
            RiwayatJabatan::whereIn('pegawai_id', $ids)->delete();
            \App\Models\MutasiPegawai::whereIn('pegawai_id', $ids)->delete();

            return $this->pegawaiRepository->deleteBulk($ids);
        });
    }

    public function getStatistics(): array
    {
        return $this->pegawaiRepository->getStatistics();
    }

    /**
     * Menyelaraskan data induk Pegawai berdasarkan riwayat aktif
     */
    public function syncPegawaiFromHistories(Pegawai $pegawai): void
    {
        $columns = $this->getPegawaiColumns();

        // 1. Sinkronisasi Jabatan Terakhir
        $activeJabatan = $pegawai->riwayatJabatan()
            ->whereIn('status', ['aktif', 'Aktif'])
            ->orderByDesc('tmt_jabatan')
            ->first() 
            ?? $pegawai->riwayatJabatan()->orderByDesc('tmt_jabatan')->first();

        if ($activeJabatan) {
            $pegawai->jabatan_id = $activeJabatan->jabatan_id;
            $pegawai->unit_kerja_id = $activeJabatan->unit_kerja_id;
            if (!empty($activeJabatan->tmt_jabatan)) {
                if ($this->hasPegawaiColumn('tmt_jabatan_terakhir', $columns)) {
                    $pegawai->tmt_jabatan_terakhir = $activeJabatan->tmt_jabatan;
                }
                if ($this->hasPegawaiColumn('tmt_sk_pertama', $columns) && empty($pegawai->tmt_sk_pertama)) {
                    $pegawai->tmt_sk_pertama = $activeJabatan->tmt_jabatan;
                }
            }
            if (!empty($activeJabatan->nomor_sk)) {
                if ($this->hasPegawaiColumn('nomor_sk_jabatan_terakhir', $columns)) {
                    $pegawai->nomor_sk_jabatan_terakhir = $activeJabatan->nomor_sk;
                }
                if ($this->hasPegawaiColumn('nomor_sk_pertama', $columns) && empty($pegawai->nomor_sk_pertama)) {
                    $pegawai->nomor_sk_pertama = $activeJabatan->nomor_sk;
                }
            }
            if (!empty($activeJabatan->tanggal_sk)) {
                if ($this->hasPegawaiColumn('tanggal_sk_jabatan_terakhir', $columns)) {
                    $pegawai->tanggal_sk_jabatan_terakhir = $activeJabatan->tanggal_sk;
                }
                if ($this->hasPegawaiColumn('tanggal_sk_pertama', $columns) && empty($pegawai->tanggal_sk_pertama)) {
                    $pegawai->tanggal_sk_pertama = $activeJabatan->tanggal_sk;
                }
            }
            if (!empty($activeJabatan->file_sk)) {
                if ($this->hasPegawaiColumn('file_sk_jabatan_terakhir', $columns)) {
                    $pegawai->file_sk_jabatan_terakhir = $activeJabatan->file_sk;
                }
                if ($this->hasPegawaiColumn('file_sk_pertama', $columns) && empty($pegawai->file_sk_pertama)) {
                    $pegawai->file_sk_pertama = $activeJabatan->file_sk;
                }
            }
        } elseif ($pegawai->jabatan_id && $pegawai->unit_kerja_id) {
            $pegawai->riwayatJabatan()->create([
                'jabatan_id'    => $pegawai->jabatan_id,
                'unit_kerja_id' => $pegawai->unit_kerja_id,
                'tmt_jabatan'   => $pegawai->tmt_sk_pertama ?? $pegawai->tanggal_masuk ?? now(),
                'nomor_sk'      => $pegawai->nomor_sk_pertama ?? null,
                'tanggal_sk'    => $pegawai->tanggal_sk_pertama ?? null,
                'file_sk'       => $pegawai->file_sk_pertama ?? null,
                'status'        => 'aktif',
                'keterangan'    => 'Sinkronisasi otomatis dari data utama pegawai',
            ]);
        }

        // 2. Sinkronisasi Pangkat Terakhir
        $activePangkat = $pegawai->riwayatPangkat()
            ->whereIn('status', ['aktif', 'Aktif'])
            ->orderByDesc('tmt')
            ->first()
            ?? $pegawai->riwayatPangkat()->orderByDesc('tmt')->first();

        if ($activePangkat) {
            $pegawai->golongan_id = $activePangkat->golongan_id;
            $pegawai->tmt_pangkat_terakhir = $activePangkat->tmt;
            if (!empty($activePangkat->nomor_sk) && $this->hasPegawaiColumn('nomor_sk_pangkat_terakhir', $columns)) {
                $pegawai->nomor_sk_pangkat_terakhir = $activePangkat->nomor_sk;
            }
            if (!empty($activePangkat->tanggal_sk) && $this->hasPegawaiColumn('tanggal_sk_pangkat_terakhir', $columns)) {
                $pegawai->tanggal_sk_pangkat_terakhir = $activePangkat->tanggal_sk;
            }
            if (!empty($activePangkat->file_sk) && $this->hasPegawaiColumn('file_sk_pangkat_terakhir', $columns)) {
                $pegawai->file_sk_pangkat_terakhir = $activePangkat->file_sk;
            }
            if (!empty($activePangkat->tmt)) {
                $pegawai->kp_berikutnya = Carbon::parse($activePangkat->tmt)->addYears(4)->toDateString();
            }
        } elseif ($pegawai->golongan_id) {
            $pegawai->riwayatPangkat()->create([
                'golongan_id' => $pegawai->golongan_id,
                'tmt'         => $pegawai->tmt_pangkat_terakhir ?? now(),
                'nomor_sk'    => $pegawai->nomor_sk_pangkat_terakhir ?? null,
                'tanggal_sk'  => $pegawai->tanggal_sk_pangkat_terakhir ?? null,
                'file_sk'     => $pegawai->file_sk_pangkat_terakhir ?? null,
                'status'      => 'aktif',
                'keterangan'  => 'Sinkronisasi otomatis dari data utama pegawai',
            ]);
            if (!empty($pegawai->tmt_pangkat_terakhir)) {
                $pegawai->kp_berikutnya = Carbon::parse($pegawai->tmt_pangkat_terakhir)->addYears(4)->toDateString();
            }
        } elseif ($pegawai->riwayatPangkat()->count() === 0) {
            $pegawai->golongan_id = null;
            $pegawai->tmt_pangkat_terakhir = null;
            $pegawai->kp_berikutnya = null;
        }

        // 3. Sinkronisasi Pendidikan Terakhir (Kualifikasi Tertinggi)
        $jenjangOrder = [
            'SD'       => 1,
            'SMP'      => 2,
            'SMA'      => 3,
            'D1'       => 4,
            'D2'       => 5,
            'D3'       => 6,
            'D4'       => 7,
            'S1'       => 8,
            'PROFESI'  => 9,
            'S2'       => 10,
            'S3'       => 11,
            'PROFESOR' => 12,
            'PROF'     => 12,
        ];
        
        $highestPendidikan = $pegawai->riwayatPendidikan()
            ->get()
            ->sortByDesc(function ($item) use ($jenjangOrder) {
                $key = strtoupper($item->jenjang ?? '');
                return $jenjangOrder[$key] ?? 0;
            })
            ->first();

        if ($highestPendidikan && !empty($highestPendidikan->jenjang)) {
            $pegawai->pendidikan_terakhir = $highestPendidikan->jenjang;
        }

        $pegawai->save();
    }

    /**
     * Filter payload ke kolom yang benar-benar ada di tabel database
     */
    protected function filterPegawaiColumns(array $data): array
    {
        return $this->upsertAction->filterPegawaiColumns($data);
    }

    protected function getPegawaiColumns(): array
    {
        static $pegawaiColumns = null;
        if ($pegawaiColumns === null) {
            try {
                $pegawaiColumns = \Illuminate\Support\Facades\Schema::getColumnListing('pegawai');
            } catch (\Throwable $e) {
                $pegawaiColumns = [];
            }
        }
        return $pegawaiColumns;
    }

    protected function hasPegawaiColumn(string $column, array $columns): bool
    {
        return empty($columns) || in_array($column, $columns, true);
    }

    /**
     * Otomatisasi Sinkronisasi Berkas Terunggah ke Google Drive (SIKAP-ARSIP) via GAS Proxy
     */
    private function syncFileToDrive(Pegawai $pegawai, ?UploadedFile $file, string $jenisDokumen, string $subFolderCategory): void
    {
        $this->dokumenAction->syncFileToDrive($pegawai, $file, $jenisDokumen, $subFolderCategory);
    }
}