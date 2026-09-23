<?php

namespace App\Services;

use App\Models\Logbook;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LogbookService
{
    /**
     * Hitung selisih durasi dalam menit antara jam mulai dan selesai
     */
    public function calculateDurasi(string $jamMulai, string $jamSelesai): int
    {
        $start = Carbon::createFromTimeString($jamMulai);
        $end = Carbon::createFromTimeString($jamSelesai);

        if ($end->lt($start)) {
            return 0;
        }

        return (int) $start->diffInMinutes($end);
    }

    /**
     * Filter query untuk daftar logbook pegawai pribadi
     */
    public function getPegawaiQuery(int $pegawaiId, ?int $month = null, ?int $year = null, ?string $status = null, ?string $kategori = null): Builder
    {
        $query = Logbook::with(['verifikator'])
            ->where('pegawai_id', $pegawaiId);

        if ($month && $year) {
            $query->periode($month, $year);
        }

        if ($status && $status !== 'semua') {
            $query->status($status);
        }

        if ($kategori && $kategori !== 'semua') {
            $query->kategori($kategori);
        }

        return $query->orderBy('tanggal', 'desc')->orderBy('jam_mulai', 'desc');
    }

    /**
     * Filter query untuk monitoring Admin & Pimpinan / Atasan Langsung
     */
    public function getAdminQuery(?int $month = null, ?int $year = null, ?int $unitKerjaId = null, ?string $kategoriPegawai = null, ?string $status = null, ?string $search = null, ?array $bawahanIds = null): Builder
    {
        $query = Logbook::with(['pegawai.unitKerja', 'pegawai.jabatan', 'user', 'verifikator']);

        if ($bawahanIds !== null) {
            $query->whereIn('pegawai_id', $bawahanIds);
        }

        if ($month && $year) {
            $query->periode($month, $year);
        }

        if ($unitKerjaId) {
            $query->whereHas('pegawai', function ($q) use ($unitKerjaId) {
                $q->where('unit_kerja_id', $unitKerjaId);
            });
        }

        if ($kategoriPegawai && $kategoriPegawai !== 'semua') {
            $query->whereHas('pegawai', function ($q) use ($kategoriPegawai) {
                $q->where('jenis_pegawai', $kategoriPegawai);
            });
        }

        if ($status && $status !== 'semua') {
            $query->status($status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('aktivitas', 'like', "%{$search}%")
                  ->orWhere('deskripsi_kegiatan', 'like', "%{$search}%")
                  ->orWhere('output_kegiatan', 'like', "%{$search}%")
                  ->orWhereHas('pegawai', function ($pq) use ($search) {
                      $pq->where('nama', 'like', "%{$search}%")
                         ->orWhere('nip', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('tanggal', 'desc')->orderBy('jam_mulai', 'desc');
    }

    /**
     * Hitung statistik bulanan logbook untuk seorang pegawai
     */
    public function getPegawaiStatistics(int $pegawaiId, int $month, int $year): array
    {
        $base = Logbook::where('pegawai_id', $pegawaiId)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);

        $totalMenit = (clone $base)->sum('durasi_menit');
        $totalJam = round($totalMenit / 60, 1);

        return [
            'total_aktivitas'   => (clone $base)->count(),
            'total_menit'       => $totalMenit,
            'total_jam'         => $totalJam,
            'total_output'      => (clone $base)->sum('jumlah_output'),
            'draft'             => (clone $base)->where('status', Logbook::STATUS_DRAFT)->count(),
            'diajukan'          => (clone $base)->where('status', Logbook::STATUS_DIAJUKAN)->count(),
            'disetujui'         => (clone $base)->where('status', Logbook::STATUS_DISETUJUI)->count(),
            'perlu_revisi'      => (clone $base)->where('status', Logbook::STATUS_PERLU_REVISI)->count(),
            'ditolak'           => (clone $base)->where('status', Logbook::STATUS_DITOLAK)->count(),
        ];
    }

    /**
     * Hitung statistik untuk pimpinan/admin / atasan langsung
     */
    public function getAdminStatistics(?int $month = null, ?int $year = null, ?int $unitKerjaId = null, ?array $bawahanIds = null): array
    {
        $query = Logbook::query();

        if ($bawahanIds !== null) {
            $query->whereIn('pegawai_id', $bawahanIds);
        }

        if ($month && $year) {
            $query->periode($month, $year);
        }

        if ($unitKerjaId) {
            $query->whereHas('pegawai', function ($q) use ($unitKerjaId) {
                $q->where('unit_kerja_id', $unitKerjaId);
            });
        }

        return [
            'total'        => (clone $query)->count(),
            'menunggu'     => (clone $query)->where('status', Logbook::STATUS_DIAJUKAN)->count(),
            'disetujui'    => (clone $query)->where('status', Logbook::STATUS_DISETUJUI)->count(),
            'perlu_revisi' => (clone $query)->where('status', Logbook::STATUS_PERLU_REVISI)->count(),
            'ditolak'      => (clone $query)->where('status', Logbook::STATUS_DITOLAK)->count(),
            'total_jam'    => round(((clone $query)->sum('durasi_menit')) / 60, 1),
        ];
    }

    /**
     * Simpan entri logbook baru
     */
    public function create(array $data, int $pegawaiId, int $userId, ?UploadedFile $file = null): Logbook
    {
        return DB::transaction(function () use ($data, $pegawaiId, $userId, $file) {
            $durasi = $this->calculateDurasi($data['jam_mulai'], $data['jam_selesai']);

            $filePath = null;
            if ($file) {
                $filePath = PegawaiStorageService::store(
                    $file,
                    $pegawaiId,
                    'logbook',
                    'bukti_' . Carbon::parse($data['tanggal'])->format('Ymd')
                );
            }

            $status = isset($data['action']) && $data['action'] === 'diajukan' 
                ? Logbook::STATUS_DIAJUKAN 
                : Logbook::STATUS_DRAFT;

            return Logbook::create([
                'pegawai_id'         => $pegawaiId,
                'user_id'            => $userId,
                'tanggal'            => $data['tanggal'],
                'jam_mulai'          => $data['jam_mulai'],
                'jam_selesai'        => $data['jam_selesai'],
                'durasi_menit'       => $durasi,
                'kategori_kegiatan'  => $data['kategori_kegiatan'],
                'aktivitas'          => $data['aktivitas'],
                'deskripsi_kegiatan' => $data['deskripsi_kegiatan'],
                'output_kegiatan'    => $data['output_kegiatan'] ?? null,
                'jumlah_output'      => $data['jumlah_output'] ?? 1,
                'satuan_output'      => $data['satuan_output'] ?? 'Kegiatan',
                'file_lampiran'      => $filePath,
                'status'             => $status,
            ]);
        });
    }

    /**
     * Update logbook yang ada
     */
    public function update(Logbook $logbook, array $data, ?UploadedFile $file = null): Logbook
    {
        return DB::transaction(function () use ($logbook, $data, $file) {
            $durasi = $this->calculateDurasi($data['jam_mulai'], $data['jam_selesai']);

            $updateData = [
                'tanggal'            => $data['tanggal'],
                'jam_mulai'          => $data['jam_mulai'],
                'jam_selesai'        => $data['jam_selesai'],
                'durasi_menit'       => $durasi,
                'kategori_kegiatan'  => $data['kategori_kegiatan'],
                'aktivitas'          => $data['aktivitas'],
                'deskripsi_kegiatan' => $data['deskripsi_kegiatan'],
                'output_kegiatan'    => $data['output_kegiatan'] ?? null,
                'jumlah_output'      => $data['jumlah_output'] ?? 1,
                'satuan_output'      => $data['satuan_output'] ?? 'Kegiatan',
            ];

            if ($file) {
                $updateData['file_lampiran'] = PegawaiStorageService::store(
                    $file,
                    $logbook->pegawai_id,
                    'logbook',
                    'bukti_' . Carbon::parse($data['tanggal'])->format('Ymd')
                );
            }

            if (isset($data['action']) && $data['action'] === 'diajukan') {
                $updateData['status'] = Logbook::STATUS_DIAJUKAN;
                $updateData['catatan_atasan'] = null; // reset catatan lama saat diajukan ulang
            }

            $logbook->update($updateData);

            return $logbook;
        });
    }

    /**
     * Ajukan 1 logbook ke atasan
     */
    public function submitSingle(Logbook $logbook): bool
    {
        if (in_array($logbook->status, [Logbook::STATUS_DRAFT, Logbook::STATUS_PERLU_REVISI], true)) {
            $updated = $logbook->update([
                'status'         => Logbook::STATUS_DIAJUKAN,
                'catatan_atasan' => null,
            ]);

            if ($updated) {
                try {
                    $pegawai = $logbook->pegawai;
                    if ($pegawai) {
                        $targets = collect();
                        if ($pegawai->atasan_id) {
                            $atasanUser = User::where('pegawai_id', $pegawai->atasan_id)->first();
                            if ($atasanUser) {
                                $targets->push($atasanUser);
                            }
                        }

                        // Jika belum ada atasan langsung terdaftar, kirim ke admin/pimpinan sebagai fallback
                        if ($targets->isEmpty()) {
                            $targets = User::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'pimpinan']))->get();
                        }

                        \Illuminate\Support\Facades\Notification::send($targets, new \App\Notifications\LogbookSubmittedNotification($pegawai, 1));
                    }
                } catch (\Throwable $e) {
                    // Ignore notification errors
                }
            }

            return $updated;
        }
        return false;
    }

    /**
     * Ajukan semua draft/revisi pegawai untuk suatu periode bulan
     */
    public function submitBulk(int $pegawaiId, int $month, int $year): int
    {
        $count = Logbook::where('pegawai_id', $pegawaiId)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->whereIn('status', [Logbook::STATUS_DRAFT, Logbook::STATUS_PERLU_REVISI])
            ->update([
                'status'         => Logbook::STATUS_DIAJUKAN,
                'catatan_atasan' => null,
            ]);

        if ($count > 0) {
            try {
                $pegawai = Pegawai::find($pegawaiId);
                if ($pegawai) {
                    $targets = collect();
                    if ($pegawai->atasan_id) {
                        $atasanUser = User::where('pegawai_id', $pegawai->atasan_id)->first();
                        if ($atasanUser) {
                            $targets->push($atasanUser);
                        }
                    }

                    if ($targets->isEmpty()) {
                        $targets = User::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'pimpinan']))->get();
                    }

                    \Illuminate\Support\Facades\Notification::send($targets, new \App\Notifications\LogbookSubmittedNotification($pegawai, $count));
                }
            } catch (\Throwable $e) {
                // Ignore notification errors
            }
        }

        return $count;
    }

    /**
     * Hapus logbook
     */
    public function delete(Logbook $logbook): bool
    {
        if ($logbook->file_lampiran && Storage::disk('local')->exists($logbook->file_lampiran)) {
            Storage::disk('local')->delete($logbook->file_lampiran);
        }

        return $logbook->delete();
    }

    /**
     * Verifikasi logbook oleh atasan (setujui / tolak / perlu revisi)
     */
    public function verify(Logbook $logbook, string $status, ?string $catatanAtasan, int $verifierUserId): bool
    {
        $updated = $logbook->update([
            'status'            => $status,
            'catatan_atasan'    => $catatanAtasan,
            'diverifikasi_oleh' => $verifierUserId,
            'diverifikasi_pada' => Carbon::now(),
        ]);

        if ($updated) {
            try {
                $verifier = User::find($verifierUserId);
                $owner = $logbook->user;
                if ($owner && $verifier) {
                    $owner->notify(new \App\Notifications\LogbookVerifiedNotification($logbook, $verifier, $status));
                }
            } catch (\Throwable $e) {
                // Ignore notification errors
            }
        }

        return $updated;
    }

    /**
     * Verifikasi massal (Bulk Approve)
     */
    public function verifyBulk(array $ids, string $status, ?string $catatanAtasan, int $verifierUserId): int
    {
        $logbooks = Logbook::whereIn('id', $ids)->where('status', Logbook::STATUS_DIAJUKAN)->get();

        $count = Logbook::whereIn('id', $ids)
            ->where('status', Logbook::STATUS_DIAJUKAN)
            ->update([
                'status'            => $status,
                'catatan_atasan'    => $catatanAtasan,
                'diverifikasi_oleh' => $verifierUserId,
                'diverifikasi_pada' => Carbon::now(),
            ]);

        if ($count > 0) {
            try {
                $verifier = User::find($verifierUserId);
                foreach ($logbooks as $lb) {
                    $owner = $lb->user;
                    if ($owner && $verifier) {
                        $owner->notify(new \App\Notifications\LogbookVerifiedNotification($lb, $verifier, $status));
                    }
                }
            } catch (\Throwable $e) {
                // Ignore notification errors
            }
        }

        return $count;
    }

    /**
     * Mengambil data rekapitulasi logbook yang dikelompokkan per pegawai untuk memudahkan evaluasi bulanan atasan
     */
    public function getAdminPegawaiRecap(?int $month = null, ?int $year = null, ?int $unitKerjaId = null, ?string $kategoriPegawai = null, ?string $status = null, ?string $search = null, ?array $bawahanIds = null): array
    {
        $logbooks = $this->getAdminQuery($month, $year, $unitKerjaId, $kategoriPegawai, $status, $search, $bawahanIds)->get();

        $grouped = $logbooks->groupBy('pegawai_id');

        $recap = [];
        foreach ($grouped as $pegawaiId => $items) {
            $pegawai = $items->first()->pegawai;
            if (!$pegawai) continue;

            $totalMenit = (int) $items->sum('durasi_menit');
            $totalJam = round($totalMenit / 60, 1);
            $diajukanCount = $items->where('status', Logbook::STATUS_DIAJUKAN)->count();
            $disetujuiCount = $items->where('status', Logbook::STATUS_DISETUJUI)->count();
            $revisiCount = $items->where('status', Logbook::STATUS_PERLU_REVISI)->count();
            $ditolakCount = $items->where('status', Logbook::STATUS_DITOLAK)->count();
            $draftCount = $items->where('status', Logbook::STATUS_DRAFT)->count();

            // Hitung hari kerja unik
            $hariKerjaCount = $items->pluck('tanggal')->map(function ($t) {
                return is_string($t) ? substr($t, 0, 10) : $t->format('Y-m-d');
            })->unique()->count();

            $diajukanIds = $items->where('status', Logbook::STATUS_DIAJUKAN)->pluck('id')->values()->all();

            $recap[] = [
                'pegawai'          => $pegawai,
                'pegawai_id'       => (int) $pegawaiId,
                'total_kegiatan'   => $items->count(),
                'total_output'     => (int) $items->sum('jumlah_output'),
                'total_hari_kerja' => $hariKerjaCount,
                'total_jam'        => $totalJam,
                'total_menit'      => $totalMenit,
                'diajukan_count'   => $diajukanCount,
                'disetujui_count'  => $disetujuiCount,
                'revisi_count'     => $revisiCount,
                'ditolak_count'    => $ditolakCount,
                'draft_count'      => $draftCount,
                'diajukan_ids'     => $diajukanIds,
                'items'            => $items->sortByDesc('tanggal')->values(),
            ];
        }

        // Urutkan pegawai yang memiliki pengajuan menunggu verifikasi terbanyak di paling atas
        usort($recap, function ($a, $b) {
            if ($a['diajukan_count'] !== $b['diajukan_count']) {
                return $b['diajukan_count'] <=> $a['diajukan_count'];
            }
            return strcmp($a['pegawai']->nama ?? '', $b['pegawai']->nama ?? '');
        });

        return $recap;
    }

    /**
     * Verifikasi seluruh logbook bulan berjalan dari seorang pegawai tertentu (Persetujuan Bulanan Cepat)
     */
    public function verifyPegawaiBulanan(int $pegawaiId, int $month, int $year, string $status, ?string $catatanAtasan, int $verifierUserId): int
    {
        $ids = Logbook::where('pegawai_id', $pegawaiId)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->where('status', Logbook::STATUS_DIAJUKAN)
            ->pluck('id')
            ->toArray();

        if (empty($ids)) {
            return 0;
        }

        return $this->verifyBulk($ids, $status, $catatanAtasan, $verifierUserId);
    }
}
