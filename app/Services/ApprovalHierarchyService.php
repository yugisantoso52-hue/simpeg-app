<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\User;
use App\Services\Approval\ApprovalResolverInterface;
use App\Services\Approval\Resolvers\AbstractApprovalResolver;
use App\Services\Approval\Resolvers\AdministrasiApprovalResolver;
use App\Services\Approval\Resolvers\FakultasApprovalResolver;
use App\Services\Approval\Resolvers\JurusanProdiApprovalResolver;
use Illuminate\Support\Facades\Cache;

class ApprovalHierarchyService
{
    /**
     * In-memory memoization per request cycle
     */
    protected static array $memoizedJabatanPegawai = [];

    /**
     * Daftar resolver hierarki persetujuan modular
     *
     * @var ApprovalResolverInterface[]
     */
    protected array $resolvers = [];

    /**
     * Daftar 15+ Jabatan Pimpinan FKP UNRI
     */
    public const JABATAN_PIMPINAN = [
        'Dekan',
        'Wakil Dekan I (Bid. Akademik)',
        'Wakil Dekan II (Bid. Keuangan dan Umum)',
        'Wakil Dekan III (Bid. Kemahasiswaan, Alumni dan Kerjasama)',
        'Ketua Jurusan (Kajur)',
        'Ketua Jurusan Klinik dan Komunitas',
        'Ketua Jurusan Preklinik Keperawatan',
        'Koordinator Prodi S1 Keperawatan',
        'Koordinator Prodi S2 Keperawatan',
        'Koordinator Prodi S3 Keperawatan',
        'Koordinator Prodi Ners',
        'Kepala Bagian Umum',
        'Ka Pokja Akademik',
        'Ka Pokja Keu-Kepeg',
        'Ka Pokja Umum Sarana Akademik',
        'Kepala UPT Laboratorium Keperawatan',
        'Kepala SPMF / GPM',
        'Kepala Unit Fungsional',
    ];

    public function __construct(array $resolvers = [])
    {
        $this->resolvers = !empty($resolvers) ? $resolvers : [
            new FakultasApprovalResolver(),
            new JurusanProdiApprovalResolver(),
            new AdministrasiApprovalResolver(),
        ];
    }

    /**
     * Dapatkan Evaluasi Matriks 20 Aturan Persetujuan Logbook (Presisi Sesuai SK / Ketetapan FKP UNRI)
     */
    public function getApprovalRuleInfo(Pegawai $pegawai): array
    {
        // Prioritas Utama: Pejabat Penilai Kinerja yang Ditetapkan Khusus pada Profil Pegawai
        if (!empty($pegawai->pejabat_penilai_id) && $pegawai->pejabat_penilai_id !== $pegawai->id) {
            $penilai = $pegawai->relationLoaded('pejabatPenilai')
                ? $pegawai->pejabatPenilai
                : Pegawai::with(['jabatan', 'unitKerja'])->find($pegawai->pejabat_penilai_id);

            if ($penilai) {
                return [
                    'rule_number'                => 0,
                    'rule_label'                 => 'Penetapan Langsung Profil: Pejabat Penilai Kinerja Terpilih',
                    'subject_role'               => $pegawai->jabatan?->nama_jabatan ?? 'Pegawai',
                    'approver_title'             => $penilai->jabatan?->nama_jabatan ?? 'Pejabat Penilai Kinerja',
                    'approver_pegawai'           => $penilai,
                    'approver_name'              => $penilai->nama_lengkap ?? $penilai->nama,
                    'approver_nip'               => $penilai->nip ?? '-',
                    'secondary_approver_title'   => null,
                    'secondary_approver_pegawai' => null,
                ];
            }
        }

        // Delegasikan evaluasi ke resolver modular yang sesuai
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($pegawai)) {
                $rule = $resolver->resolveApprovalRule($pegawai);
                if ($rule !== null) {
                    return $rule;
                }
            }
        }

        // Fallback Umum: Dekan Fakultas Keperawatan
        $dekan = $this->findPegawaiByJabatan(['Dekan']);
        return [
            'rule_number'                => 99,
            'rule_label'                 => 'General Fallback ──► Disetujui Dekan Fakultas Keperawatan',
            'subject_role'               => $pegawai->jabatan?->nama_jabatan ?? 'Pegawai',
            'approver_title'             => 'Dekan Fakultas Keperawatan',
            'approver_pegawai'           => $dekan,
            'approver_name'              => $dekan ? ($dekan->nama_lengkap ?? $dekan->nama) : 'Dekan Fakultas Keperawatan',
            'approver_nip'               => $dekan?->nip ?? '-',
            'secondary_approver_title'   => null,
            'secondary_approver_pegawai' => null,
        ];
    }

    /**
     * Ambil Model Pegawai Atasan Langsung untuk Persetujuan Kinerja Logbook
     */
    public function getAtasanLangsung(Pegawai $pegawai): ?Pegawai
    {
        $info = $this->getApprovalRuleInfo($pegawai);

        if (!empty($info['approver_pegawai']) && $info['approver_pegawai'] instanceof Pegawai) {
            return $info['approver_pegawai'];
        }

        if (!empty($info['secondary_approver_pegawai']) && $info['secondary_approver_pegawai'] instanceof Pegawai) {
            return $info['secondary_approver_pegawai'];
        }

        if (!empty($pegawai->atasan_id)) {
            return $pegawai->relationLoaded('atasan')
                ? $pegawai->atasan
                : Pegawai::find($pegawai->atasan_id);
        }

        return $this->findPegawaiByJabatan(['Dekan']);
    }

    /**
     * Dapatkan daftar ID pegawai bawahan dari seorang pimpinan/atasan
     */
    public function getBawahanIdsForPegawai(Pegawai $pimpinan): array
    {
        $cacheKey = "bawahan_ids_pegawai_" . $pimpinan->id;

        return Cache::remember($cacheKey, 60, function () use ($pimpinan) {
            // 1. Bawahan eksplisit via atasan_id, pejabat_penilai_id, atau atasan_cuti_id di DB
            $directIds = Pegawai::where(function ($q) use ($pimpinan) {
                $q->where('atasan_id', $pimpinan->id)
                  ->orWhere('pejabat_penilai_id', $pimpinan->id)
                  ->orWhere('atasan_cuti_id', $pimpinan->id);
            })->pluck('id')->toArray();

            // 2. Delegasikan pencarian bawahan struktural/fungsional ke resolver modular
            $implicitIds = [];
            foreach ($this->resolvers as $resolver) {
                if ($resolver->supports($pimpinan)) {
                    $subIds = $resolver->getBawahanIds($pimpinan);
                    if (!empty($subIds)) {
                        $implicitIds = array_merge($implicitIds, $subIds);
                    }
                }
            }

            $allIds = array_unique(array_merge($directIds, $implicitIds));

            // Kecualikan diri sendiri dari daftar bawahan
            return array_values(array_filter($allIds, fn($id) => (int)$id !== (int)$pimpinan->id));
        });
    }

    /**
     * Dapatkan Atasan Langsung Cuti (VI. Pertimbangan Atasan Langsung)
     */
    public function getAtasanLangsungCuti(Pegawai $pegawai): ?Pegawai
    {
        // 1. Prioritas Utama: Atasan Langsung Pertimbangan Cuti yang Ditetapkan Eksplisit pada Profil Pegawai
        if (!empty($pegawai->atasan_cuti_id) && $pegawai->atasan_cuti_id !== $pegawai->id) {
            $atasanCuti = $pegawai->relationLoaded('atasanCuti')
                ? $pegawai->atasanCuti
                : Pegawai::with(['jabatan', 'unitKerja'])->find($pegawai->atasan_cuti_id);

            if ($atasanCuti) {
                return $atasanCuti;
            }
        }

        // 2. Delegasikan ke resolver modular
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($pegawai)) {
                $atasan = $resolver->resolveAtasanLangsungCuti($pegawai);
                if ($atasan !== null) {
                    return $atasan;
                }
            }
        }

        // Fallback default
        return $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum']);
    }

    /**
     * Dapatkan Pejabat Yang Berwenang Memberikan Cuti (PYBMC) - Bagian VII
     */
    public function getPybmcCuti(Pegawai $pegawai, ?string $jenisCuti = null): ?Pegawai
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($pegawai)) {
                $pybmc = $resolver->resolvePybmcCuti($pegawai, $jenisCuti);
                if ($pybmc !== null) {
                    return $pybmc;
                }
            }
        }

        // Fallback umum: Wakil Dekan II
        return $this->findPegawaiByJabatan([
            'Wakil Dekan II (Bid. Keuangan dan Umum)',
            'Wakil Dekan II',
        ]);
    }

    /**
     * Helper untuk mendapatkan teks Pejabat Cuti (Nama, Jabatan, NIP) untuk formulir PDF & cetak
     */
    public function getPejabatCutiInfo(?Pegawai $pegawai): array
    {
        $isDosen     = $pegawai ? $pegawai->isDosen() : false;
        $jabatanNama = strtoupper(trim((string)($pegawai?->jabatan?->nama_jabatan ?? '')));

        // KATEGORI E: DEKAN
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL')) {
            return [
                'is_dosen'       => false,
                'kategori'       => 'dekan',
                'atasan_jabatan' => 'Rektor Universitas Riau',
                'atasan_nama'    => 'Rektor Universitas Riau',
                'atasan_nip'     => '',
                'pybmc_jabatan'  => 'Rektor Universitas Riau',
                'pybmc_nama'     => 'Rektor Universitas Riau',
                'pybmc_nip'      => '',
            ];
        }

        // KATEGORI D: WAKIL DEKAN II
        if (str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II')) {
            $kabagUmum = $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum']);
            $dekan     = $this->findPegawaiByJabatan(['Dekan']);
            return [
                'is_dosen'       => false,
                'kategori'       => 'wadek2',
                'atasan_jabatan' => 'Kepala Bagian Umum',
                'atasan_nama'    => $kabagUmum ? ($kabagUmum->nama_lengkap ?? $kabagUmum->nama) : 'Kepala Bagian Umum',
                'atasan_nip'     => $kabagUmum?->nip ?? '',
                'pybmc_jabatan'  => 'Dekan Fakultas Keperawatan',
                'pybmc_nama'     => $dekan ? ($dekan->nama_lengkap ?? $dekan->nama) : 'Dekan Fakultas Keperawatan',
                'pybmc_nip'      => $dekan?->nip ?? '',
            ];
        }

        // KATEGORI C: PIMPINAN STRUKTURAL LAINNYA
        $isPimpinanStruktural =
            str_contains($jabatanNama, 'WAKIL DEKAN') ||
            str_contains($jabatanNama, 'WADEK') ||
            str_contains($jabatanNama, 'KETUA JURUSAN') ||
            str_contains($jabatanNama, 'KAJUR') ||
            str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') ||
            str_contains($jabatanNama, 'KABAG UMUM');

        if ($isPimpinanStruktural) {
            $wadek2 = $this->findPegawaiByJabatan(['Wakil Dekan II (Bid. Keuangan dan Umum)', 'Wakil Dekan II']);
            $dekan  = $this->findPegawaiByJabatan(['Dekan']);
            return [
                'is_dosen'       => $isDosen,
                'kategori'       => 'pimpinan_struktural',
                'atasan_jabatan' => 'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'atasan_nama'    => $wadek2 ? ($wadek2->nama_lengkap ?? $wadek2->nama) : 'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'atasan_nip'     => $wadek2?->nip ?? '',
                'pybmc_jabatan'  => 'Dekan Fakultas Keperawatan',
                'pybmc_nama'     => $dekan ? ($dekan->nama_lengkap ?? $dekan->nama) : 'Dekan Fakultas Keperawatan',
                'pybmc_nip'      => $dekan?->nip ?? '',
            ];
        }

        // KATEGORI A: DOSEN
        if ($isDosen) {
            $kajur  = $this->findPegawaiByJabatan(['Ketua Jurusan (Kajur)', 'Ketua Jurusan Preklinik Keperawatan', 'Ketua Jurusan Klinik dan Komunitas']);
            $wadek2 = $this->findPegawaiByJabatan(['Wakil Dekan II (Bid. Keuangan dan Umum)', 'Wakil Dekan II']);
            return [
                'is_dosen'       => true,
                'kategori'       => 'dosen',
                'atasan_jabatan' => 'Ketua Jurusan (Kajur)',
                'atasan_nama'    => $kajur ? ($kajur->nama_lengkap ?? $kajur->nama) : 'Ketua Jurusan',
                'atasan_nip'     => $kajur?->nip ?? '',
                'pybmc_jabatan'  => 'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'pybmc_nama'     => $wadek2 ? ($wadek2->nama_lengkap ?? $wadek2->nama) : 'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'pybmc_nip'      => $wadek2?->nip ?? '',
            ];
        }

        // KATEGORI B: TENDIK / STAFF / LABORAN
        $kabagUmum = $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum']);
        $wadek2    = $this->findPegawaiByJabatan(['Wakil Dekan II (Bid. Keuangan dan Umum)', 'Wakil Dekan II']);
        return [
            'is_dosen'       => false,
            'kategori'       => 'tendik',
            'atasan_jabatan' => 'Kepala Bagian Umum',
            'atasan_nama'    => $kabagUmum ? ($kabagUmum->nama_lengkap ?? $kabagUmum->nama) : 'Kepala Bagian Umum',
            'atasan_nip'     => $kabagUmum?->nip ?? '',
            'pybmc_jabatan'  => 'Wakil Dekan II (Bid. Keuangan dan Umum)',
            'pybmc_nama'     => $wadek2 ? ($wadek2->nama_lengkap ?? $wadek2->nama) : 'Wakil Dekan II (Bid. Keuangan dan Umum)',
            'pybmc_nip'      => $wadek2?->nip ?? '',
        ];
    }

    /**
     * Helper cari Pegawai berdasarkan list nama jabatan (dengan in-memory memoization)
     */
    public function findPegawaiByJabatan(array $jabatanNames): ?Pegawai
    {
        $cacheKey = implode('|', $jabatanNames);
        if (array_key_exists($cacheKey, self::$memoizedJabatanPegawai)) {
            return self::$memoizedJabatanPegawai[$cacheKey];
        }

        foreach ($jabatanNames as $name) {
            $pegawai = Pegawai::with(['jabatan', 'unitKerja'])
                ->whereHas('jabatan', function ($q) use ($name) {
                    $q->where('nama_jabatan', 'like', "%{$name}%");
                })->first();

            if ($pegawai) {
                return self::$memoizedJabatanPegawai[$cacheKey] = $pegawai;
            }
        }

        return self::$memoizedJabatanPegawai[$cacheKey] = null;
    }

    /**
     * Cek apakah $pimpinan adalah Atasan Langsung (Pertimbangan Tahap 1) untuk cuti $pemohon
     */
    public function isAtasanLangsungCuti(Pegawai $pimpinan, Pegawai $pemohon): bool
    {
        if ($pimpinan->id === $pemohon->id) {
            return false;
        }

        // Cek penugasan eksplisit di tabel pegawai
        if (!empty($pemohon->atasan_cuti_id)) {
            return (int)$pemohon->atasan_cuti_id === (int)$pimpinan->id;
        }

        $atasanLangsung = $this->getAtasanLangsungCuti($pemohon);

        return $atasanLangsung !== null && (int)$atasanLangsung->id === (int)$pimpinan->id;
    }

    /**
     * Cek apakah $pimpinan adalah Pejabat Yang Berwenang Memberikan Cuti (PYBMC - Tahap 2)
     */
    public function isPybmcCuti(Pegawai $pimpinan, Pegawai $pemohon): bool
    {
        if ($pimpinan->id === $pemohon->id) {
            return false;
        }

        $pybmc = $this->getPybmcCuti($pemohon);

        return $pybmc !== null && (int)$pybmc->id === (int)$pimpinan->id;
    }

    /**
     * Dapatkan daftar ID pegawai yang berada di bawah wewenang persetujuan cuti $pimpinan
     */
    public function getScopedCutiPegawaiIds(Pegawai $pimpinan): ?array
    {
        $jabatanNama = strtoupper(trim((string)($pimpinan->jabatan->nama_jabatan ?? '')));

        // 1. Dekan: Berwenang menyetujui PYBMC untuk pejabat struktural
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL') && !str_contains($jabatanNama, 'WADEK')) {
            return Pegawai::where(function ($q) {
                $q->whereHas('jabatan', function ($jq) {
                    $jq->where('nama_jabatan', 'like', '%Wakil Dekan%')
                       ->orWhere('nama_jabatan', 'like', '%Wadek%')
                       ->orWhere('nama_jabatan', 'like', '%Ketua Jurusan%')
                       ->orWhere('nama_jabatan', 'like', '%Kajur%')
                       ->orWhere('nama_jabatan', 'like', '%Kepala Bagian Umum%')
                       ->orWhere('nama_jabatan', 'like', '%Kabag Umum%');
                });
            })->pluck('id')->toArray();
        }

        // 2. Wakil Dekan II: PYBMC untuk seluruh Dosen dan Tendik
        if (str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II')
            || (str_contains($jabatanNama, 'WAKIL DEKAN') && str_contains($jabatanNama, 'KEUANGAN'))) {
            return null; // Null menandakan seluruh pegawai dalam domain Wadek II
        }

        // 3. Ketua Jurusan (Kajur): Atasan Langsung untuk seluruh Dosen
        if (str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) {
            return Pegawai::where(function ($q) {
                $q->where('jenis_pegawai', 'like', '%Dosen%')
                  ->orWhereNotNull('nidn_nuptk');
            })->pluck('id')->toArray();
        }

        // 4. Kepala Bagian Umum (Kabag Umum): Atasan Langsung untuk seluruh Tendik & Staff
        if (str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') || str_contains($jabatanNama, 'KABAG UMUM') || str_contains($jabatanNama, 'KABAG TU')) {
            return Pegawai::where(function ($q) {
                $q->where('jenis_pegawai', 'not like', '%Dosen%')
                  ->orWhereNull('jenis_pegawai');
            })->whereNull('nidn_nuptk')->pluck('id')->toArray();
        }

        // 5. Pimpinan Lainnya: Bawahan langsung berdasarkan penetapan atasan_cuti_id
        return Pegawai::where('atasan_cuti_id', $pimpinan->id)->pluck('id')->toArray();
    }

    /**
     * Hitung jumlah pengajuan cuti yang sedang menunggu persetujuan spesifik dari user pimpinan
     */
    public function countPendingCutiForUser(User $user): int
    {
        if ($user->hasRole('admin')) {
            return \App\Models\PengajuanCuti::whereIn('status', [
                'Menunggu Persetujuan',
                'Disetujui Atasan (Menunggu PYBMC)',
                'Disetujui Atasan (Menunggu Keputusan Akhir)',
            ])->count();
        }

        $pimpinan = $user->pegawai;
        if (!$pimpinan) {
            return 0;
        }

        $jabatanNama = strtoupper(trim((string)($pimpinan->jabatan->nama_jabatan ?? '')));
        $count = 0;

        $isDekan = str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL') && !str_contains($jabatanNama, 'WADEK');
        $isWadek2 = str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II')
            || (str_contains($jabatanNama, 'WAKIL DEKAN') && str_contains($jabatanNama, 'KEUANGAN'));
        $isKajur = str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR');
        $isKabag = str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') || str_contains($jabatanNama, 'KABAG UMUM') || str_contains($jabatanNama, 'KABAG TU');

        // Tahap 1: Menunggu Persetujuan Atasan Langsung
        $pendingTahap1 = \App\Models\PengajuanCuti::with(['pegawai.jabatan', 'pegawai.unitKerja'])
            ->where('status', 'Menunggu Persetujuan')
            ->where('pegawai_id', '!=', $pimpinan->id)
            ->get();

        foreach ($pendingTahap1 as $cuti) {
            if ($cuti->pegawai && $this->isAtasanLangsungCuti($pimpinan, $cuti->pegawai)) {
                $count++;
            }
        }

        // Tahap 2: Menunggu Persetujuan PYBMC
        $pendingTahap2 = \App\Models\PengajuanCuti::with(['pegawai.jabatan', 'pegawai.unitKerja'])
            ->whereIn('status', [
                'Disetujui Atasan (Menunggu PYBMC)',
                'Disetujui Atasan (Menunggu Keputusan Akhir)',
            ])
            ->where('pegawai_id', '!=', $pimpinan->id)
            ->get();

        foreach ($pendingTahap2 as $cuti) {
            if ($cuti->pegawai && $this->isPybmcCuti($pimpinan, $cuti->pegawai)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Dapatkan daftar ID Pegawai yang diizinkan untuk dipantau presensinya oleh $user
     */
    public function getScopedPresensiPegawaiIds(User $user): ?array
    {
        // 1. Admin Sistem: 100% Seluruh Pegawai
        if ($user->hasRole('admin')) {
            return null;
        }

        if (!$user->pegawai) {
            return $user->pegawai_id ? [$user->pegawai_id] : [];
        }

        $pegawai = $user->pegawai;
        $jabatan = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // 1. Dekan Fakultas Keperawatan: 100% Seluruh Pegawai
        if (str_contains($jabatan, 'DEKAN') && !str_contains($jabatan, 'WAKIL') && !str_contains($jabatan, 'WADEK')) {
            return null;
        }

        // 2. Wakil Dekan II: 100% Seluruh Pegawai
        if ((str_contains($jabatan, 'WAKIL DEKAN II') || str_contains($jabatan, 'WD II') || (str_contains($jabatan, 'WAKIL DEKAN') && str_contains($jabatan, 'KEUANGAN')))
            && !str_contains($jabatan, 'WAKIL DEKAN III') && !str_contains($jabatan, 'WD III')) {
            return null;
        }

        // 3. Kepala Bagian Umum (Kabag Umum): Seluruh Tendik & PHL
        if (str_contains($jabatan, 'KEPALA BAGIAN UMUM') || str_contains($jabatan, 'KABAG UMUM') || str_contains($jabatan, 'KABAG TU')) {
            return Pegawai::where(function ($q) {
                $q->whereNull('nidn_nuptk')->where('jenis_pegawai', '!=', 'Dosen');
            })->pluck('id')->toArray();
        }

        // 4. Wakil Dekan I: Seluruh Dosen Fakultas Keperawatan
        if ((str_contains($jabatan, 'WAKIL DEKAN I') || str_contains($jabatan, 'WD I') || (str_contains($jabatan, 'WAKIL DEKAN') && str_contains($jabatan, 'AKADEMIK')))
            && !str_contains($jabatan, 'WAKIL DEKAN II') && !str_contains($jabatan, 'WAKIL DEKAN III')
            && !str_contains($jabatan, 'WD II') && !str_contains($jabatan, 'WD III')) {
            return Pegawai::where('jenis_pegawai', 'Dosen')
                ->orWhereNotNull('nidn_nuptk')
                ->pluck('id')
                ->toArray();
        }

        // 5. Ketua Jurusan: Seluruh Dosen Jurusan Keperawatan
        if ((str_contains($jabatan, 'KETUA JURUSAN') || str_contains($jabatan, 'KAJUR'))
            && !str_contains($jabatan, 'PREKLINIK') && !str_contains($jabatan, 'KLINIK')) {
            return Pegawai::where('jenis_pegawai', 'Dosen')
                ->orWhereNotNull('nidn_nuptk')
                ->pluck('id')
                ->toArray();
        }

        // 6. Ka Pokja, Ka Lab, Koorprodi, Kajur Bagian, Wadek III
        $isSpecificSupervisor = str_contains($jabatan, 'POKJA')
            || str_contains($jabatan, 'LABORATORIUM')
            || str_contains($jabatan, 'LAB')
            || str_contains($jabatan, 'KOORDINATOR PRODI')
            || str_contains($jabatan, 'KOORPRODI')
            || str_contains($jabatan, 'WAKIL DEKAN III')
            || str_contains($jabatan, 'WD III')
            || str_contains($jabatan, 'PREKLINIK')
            || str_contains($jabatan, 'KLINIK');

        if ($isSpecificSupervisor) {
            $bawahanIds = $this->getBawahanIdsForPegawai($pegawai);
            if (!empty($bawahanIds)) {
                return $bawahanIds;
            }
            return [$pegawai->id];
        }

        // 7. General Atasan Fallback
        $bawahanIds = $this->getBawahanIdsForPegawai($pegawai);
        if (!empty($bawahanIds)) {
            return $bawahanIds;
        }

        // 8. Pegawai Biasa: Hanya melihat presensi diri sendiri
        return [$pegawai->id];
    }

    /**
     * Dapatkan Label Deskripsi Cakupan Presensi untuk UI Badge
     */
    public function getPresensiScopeLabel(User $user): string
    {
        if ($user->hasRole('admin')) {
            return 'Seluruh Pegawai Fakultas (Admin - 100%)';
        }

        if (!$user->pegawai) {
            return 'Presensi Mandiri';
        }

        $pegawai = $user->pegawai;
        $jabatan = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        if (str_contains($jabatan, 'DEKAN') && !str_contains($jabatan, 'WAKIL') && !str_contains($jabatan, 'WADEK')) {
            return 'Seluruh Pegawai Fakultas (Dekan - 100%)';
        }

        if ((str_contains($jabatan, 'WAKIL DEKAN II') || str_contains($jabatan, 'WD II') || (str_contains($jabatan, 'WAKIL DEKAN') && str_contains($jabatan, 'KEUANGAN')))
            && !str_contains($jabatan, 'WAKIL DEKAN III') && !str_contains($jabatan, 'WD III')) {
            return 'Seluruh Pegawai Fakultas (Wadek II - 100%)';
        }

        if (str_contains($jabatan, 'KEPALA BAGIAN UMUM') || str_contains($jabatan, 'KABAG UMUM') || str_contains($jabatan, 'KABAG TU')) {
            return 'Seluruh Tenaga Kependidikan & PHL (Kabag Umum)';
        }

        if ((str_contains($jabatan, 'WAKIL DEKAN I') || str_contains($jabatan, 'WD I') || (str_contains($jabatan, 'WAKIL DEKAN') && str_contains($jabatan, 'AKADEMIK')))
            && !str_contains($jabatan, 'WAKIL DEKAN II') && !str_contains($jabatan, 'WAKIL DEKAN III')
            && !str_contains($jabatan, 'WD II') && !str_contains($jabatan, 'WD III')) {
            return 'Seluruh Dosen Fakultas Keperawatan (Wadek I)';
        }

        if ((str_contains($jabatan, 'KETUA JURUSAN') || str_contains($jabatan, 'KAJUR'))
            && !str_contains($jabatan, 'PREKLINIK') && !str_contains($jabatan, 'KLINIK')) {
            return 'Seluruh Dosen Jurusan Keperawatan (Kajur)';
        }

        if (str_contains($jabatan, 'WAKIL DEKAN III') || str_contains($jabatan, 'WD III') || (str_contains($jabatan, 'WAKIL DEKAN') && str_contains($jabatan, 'KEMAHASISWAAN'))) {
            return 'Staf / Unit Kemahasiswaan (Wadek III)';
        }

        if (str_contains($jabatan, 'POKJA') || str_contains($jabatan, 'LABORATORIUM') || str_contains($jabatan, 'LAB') || str_contains($jabatan, 'KOORDINATOR')) {
            $namaJabatan = $pegawai->jabatan->nama_jabatan ?? 'Unit Kerja';
            return 'Bawahan ' . $namaJabatan;
        }

        $bawahanIds = $this->getBawahanIdsForPegawai($pegawai);
        if (!empty($bawahanIds)) {
            return 'Bawahan Langsung (' . count($bawahanIds) . ' Pegawai)';
        }

        return 'Presensi Mandiri (Diri Sendiri)';
    }
}
