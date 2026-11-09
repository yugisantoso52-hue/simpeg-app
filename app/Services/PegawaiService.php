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

            // 5b. Simpan penugasan / keanggotaan non-struktural (KJFD, Unit Fungsional, Laboratorium)
            $this->syncPenugasan($pegawai, $data);

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

            // Hapus berkas dan data riwayat lainnya (STR/SIP, SKP, Penghargaan, Publikasi, Organisasi, Tugas Belajar, Penugasan)
            foreach ($pegawai->riwayatStrSip as $strSip) {
                if ($strSip->file_dokumen) {
                    $this->deleteSK($strSip->file_dokumen);
                }
            }
            $pegawai->riwayatStrSip()->delete();

            foreach ($pegawai->riwayatSkp as $skp) {
                if ($skp->file_rencana_skp) {
                    $this->deleteSK($skp->file_rencana_skp);
                }
                if ($skp->file_evaluasi_skp) {
                    $this->deleteSK($skp->file_evaluasi_skp);
                }
            }
            $pegawai->riwayatSkp()->delete();

            foreach ($pegawai->riwayatPenghargaan as $penghargaan) {
                if ($penghargaan->file_sk) {
                    $this->deleteSK($penghargaan->file_sk);
                }
            }
            $pegawai->riwayatPenghargaan()->delete();

            foreach ($pegawai->riwayatPublikasi as $publikasi) {
                if ($publikasi->file_publikasi) {
                    $this->deleteSK($publikasi->file_publikasi);
                }
            }
            $pegawai->riwayatPublikasi()->delete();

            foreach ($pegawai->tugasBelajar as $tubel) {
                if ($tubel->file_sk) {
                    $this->deleteSK($tubel->file_sk);
                }
                if ($tubel->file_laporan_progress) {
                    $this->deleteSK($tubel->file_laporan_progress);
                }
            }
            $pegawai->tugasBelajar()->delete();

            $pegawai->riwayatOrganisasi()->delete();
            $pegawai->penugasan()->delete();

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
                Pegawai::whereIn('id', $ids)->pluck('file_str')->filter()->toArray(),
                Pegawai::whereIn('id', $ids)->pluck('file_sip')->filter()->toArray(),
                RiwayatPendidikan::whereIn('pegawai_id', $ids)->pluck('ijazah')->filter()->toArray(),
                RiwayatDiklat::whereIn('pegawai_id', $ids)->pluck('file_sertifikat')->filter()->toArray(),
                RiwayatPangkat::whereIn('pegawai_id', $ids)->pluck('file_sk')->filter()->toArray(),
                RiwayatJabatan::whereIn('pegawai_id', $ids)->pluck('file_sk')->filter()->toArray(),
                \App\Models\RiwayatStrSip::whereIn('pegawai_id', $ids)->pluck('file_dokumen')->filter()->toArray(),
                \App\Models\RiwayatSkp::whereIn('pegawai_id', $ids)->pluck('file_rencana_skp')->filter()->toArray(),
                \App\Models\RiwayatSkp::whereIn('pegawai_id', $ids)->pluck('file_evaluasi_skp')->filter()->toArray(),
                \App\Models\RiwayatPenghargaan::whereIn('pegawai_id', $ids)->pluck('file_sk')->filter()->toArray(),
                \App\Models\RiwayatPublikasi::whereIn('pegawai_id', $ids)->pluck('file_publikasi')->filter()->toArray(),
                \App\Models\TugasBelajar::whereIn('pegawai_id', $ids)->pluck('file_sk')->filter()->toArray(),
                \App\Models\TugasBelajar::whereIn('pegawai_id', $ids)->pluck('file_laporan_progress')->filter()->toArray(),
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
            \App\Models\RiwayatStrSip::whereIn('pegawai_id', $ids)->delete();
            \App\Models\RiwayatSkp::whereIn('pegawai_id', $ids)->delete();
            \App\Models\RiwayatPenghargaan::whereIn('pegawai_id', $ids)->delete();
            \App\Models\RiwayatPublikasi::whereIn('pegawai_id', $ids)->delete();
            \App\Models\RiwayatOrganisasi::whereIn('pegawai_id', $ids)->delete();
            \App\Models\TugasBelajar::whereIn('pegawai_id', $ids)->delete();
            \App\Models\PegawaiPenugasan::whereIn('pegawai_id', $ids)->delete();
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

        // 4. Sinkronisasi Legalitas Profesi (STR & SIP) ke Kolom Induk Pegawai
        try {
            if (method_exists($pegawai, 'riwayatStrSip')) {
                $strSipRecords = $pegawai->riwayatStrSip()->orderByDesc('tanggal_terbit')->orderByDesc('id')->get();

                $latestStr = $strSipRecords->first(fn ($item) => str_contains(strtoupper((string) $item->jenis_dokumen), 'STR'));
                if ($latestStr) {
                    if ($this->hasPegawaiColumn('nomor_str', $columns) && !empty($latestStr->nomor_registrasi)) {
                        $pegawai->nomor_str = $latestStr->nomor_registrasi;
                    }
                    if ($this->hasPegawaiColumn('tanggal_kadaluarsa_str', $columns)) {
                        $pegawai->tanggal_kadaluarsa_str = $latestStr->is_seumur_hidup ? null : $latestStr->tanggal_berakhir;
                    }
                    if ($this->hasPegawaiColumn('file_str', $columns) && !empty($latestStr->file_dokumen)) {
                        $pegawai->file_str = $latestStr->file_dokumen;
                    }
                }

                $latestSip = $strSipRecords->first(fn ($item) => str_contains(strtoupper((string) $item->jenis_dokumen), 'SIP'));
                if ($latestSip) {
                    if ($this->hasPegawaiColumn('nomor_sip', $columns) && !empty($latestSip->nomor_registrasi)) {
                        $pegawai->nomor_sip = $latestSip->nomor_registrasi;
                    }
                    if ($this->hasPegawaiColumn('tanggal_kadaluarsa_sip', $columns)) {
                        $pegawai->tanggal_kadaluarsa_sip = $latestSip->is_seumur_hidup ? null : $latestSip->tanggal_berakhir;
                    }
                    if ($this->hasPegawaiColumn('file_sip', $columns) && !empty($latestSip->file_dokumen)) {
                        $pegawai->file_sip = $latestSip->file_dokumen;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika tabel riwayat_str_sip belum termigrasi
        }

        $pegawai->save();

        // 5. Otomatisasi Sinkronisasi Matriks 9-Kotak Talenta ASN (Pendidikan, Pangkat, Jabatan)
        try {
            app(TalentScoringService::class)->calculatePegawaiTalent($pegawai);
        } catch (\Throwable $e) {
            // Abaikan jika tabel talent_mappings belum termigrasi
        }

        // Bersihkan cache lintas menu yang bergantung pada data induk pegawai
        \Illuminate\Support\Facades\Cache::forget('dashboard_abk_summary_v2');
        \Illuminate\Support\Facades\Cache::forget('dashboard_statistics');
        \Illuminate\Support\Facades\Cache::forget('dashboard_akademik_stats');
        \Illuminate\Support\Facades\Cache::forget('dashboard_reminder');
        \Illuminate\Support\Facades\Cache::forget('dashboard_grafik_golongan');
        \Illuminate\Support\Facades\Cache::forget('dashboard_grafik_pendidikan');
        \Illuminate\Support\Facades\Cache::forget('dashboard_grafik_unit');
    }

    /**
     * Ambil & susun data Daftar Urut Kepangkatan (DUK) terkelompok berdasarkan kategori ASN
     * beserta kalkulasi ranking resmi dan pembatasan privasi ASN.
     */
    public function getDukData(?string $search = null, ?User $user = null, bool $enforcePrivacy = true): array
    {
        $query = Pegawai::with(['golongan', 'unitKerja', 'jabatan', 'riwayatPendidikan', 'riwayatDiklat']);

        if ($search) {
            $query->where(function ($sq) use ($search) {
                $sq->where('nama', 'like', "%{$search}%")
                   ->orWhere('nip', 'like', "%{$search}%")
                   ->orWhere('nidn_nuptk', 'like', "%{$search}%");
            });
        }

        $allPegawai = $query->get();

        $isPns = function ($p) {
            $jenis    = strtoupper(trim((string) $p->jenis_pegawai));
            $asn      = strtoupper(trim((string) $p->status_asn));
            $cleanNip = preg_replace('/[^0-9]/', '', (string) $p->nip);
            return ($jenis === 'PNS' || (!str_contains($jenis, 'PPPK') && ($asn === 'ASN' || strlen($cleanNip) === 18)));
        };

        $isPppk = function ($p) {
            $jenis    = strtoupper(trim((string) $p->jenis_pegawai));
            $asn      = strtoupper(trim((string) $p->status_asn));
            $cleanNip = preg_replace('/[^0-9]/', '', (string) $p->nip);
            return (str_contains($jenis, 'PPPK') || $asn === 'PPPK' || strlen($cleanNip) === 21);
        };

        $dosenList  = $allPegawai->filter(fn ($p) => $p->kategori_kepegawaian === 'Dosen');
        $tendikList = $allPegawai->filter(fn ($p) => $p->kategori_kepegawaian === 'Tendik');
        $phlListRaw = $allPegawai->filter(fn ($p) => $p->kategori_kepegawaian === 'PHL');

        $assignRank = function ($collection) {
            $rank = 1;
            foreach ($collection as $item) {
                $item->official_duk_rank = $rank++;
            }
            return $collection;
        };

        $dosenPnsList   = $assignRank(Pegawai::sortDukCollection($dosenList->filter($isPns)));
        $dosenPppkList  = $assignRank(Pegawai::sortDukCollection($dosenList->filter($isPppk)));
        $tendikPnsList  = $assignRank(Pegawai::sortDukCollection($tendikList->filter($isPns)));
        $tendikPppkList = $assignRank(Pegawai::sortDukCollection($tendikList->filter($isPppk)));
        $phlList        = $assignRank(Pegawai::sortDukCollection($phlListRaw));

        $isExecutive = $user && $user->canAccessExecutiveKepegawaianMenus();
        $pegawaiId   = $user?->pegawai_id;

        if ($enforcePrivacy && !$isExecutive) {
            $dosenPnsList   = $dosenPnsList->filter(fn ($p) => (int) $p->id === (int) $pegawaiId);
            $dosenPppkList  = $dosenPppkList->filter(fn ($p) => (int) $p->id === (int) $pegawaiId);
            $tendikPnsList  = $tendikPnsList->filter(fn ($p) => (int) $p->id === (int) $pegawaiId);
            $tendikPppkList = $tendikPppkList->filter(fn ($p) => (int) $p->id === (int) $pegawaiId);
            $phlList        = $phlList->filter(fn ($p) => (int) $p->id === (int) $pegawaiId);
        }

        $statistics = [
            'dosen_pns'   => count($dosenPnsList),
            'dosen_pppk'  => count($dosenPppkList),
            'tendik_pns'  => count($tendikPnsList),
            'tendik_pppk' => count($tendikPppkList),
            'phl'         => count($phlList),
            'total'       => count($dosenPnsList) + count($dosenPppkList) + count($tendikPnsList) + count($tendikPppkList) + count($phlList),
        ];

        return [
            'dosenPnsList'   => $dosenPnsList,
            'dosenPppkList'  => $dosenPppkList,
            'tendikPnsList'  => $tendikPnsList,
            'tendikPppkList' => $tendikPppkList,
            'phlList'        => $phlList,
            'statistics'     => $statistics,
            'isExecutive'    => $isExecutive,
        ];
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