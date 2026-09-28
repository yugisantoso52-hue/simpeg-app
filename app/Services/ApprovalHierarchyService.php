<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ApprovalHierarchyService
{
    /**
     * Daftar 15 Jabatan Pimpinan FKP UNRI
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
        'Koordinator Prodi Ners',
        'Kepala Bagian Umum',
        'Ka Pokja Akademik',
        'Ka Pokja Keu-Kepeg',
        'Ka Pokja Umum Sarana Akademik',
        'Kepala UPT Laboratorium Keperawatan',
    ];

    /**
     * Dapatkan Pegawai Atasan Langsung untuk Persetujuan Logbook Kinerja
     * Berdasarkan bagan hierarki resmi FKP UNRI
     */
    public function getAtasanLangsung(Pegawai $pegawai): ?Pegawai
    {
        // 1. Cek jika atasan_id sudah terisi manual di DB
        if (!empty($pegawai->atasan_id)) {
            $atasan = Pegawai::with(['jabatan', 'unitKerja'])->find($pegawai->atasan_id);
            if ($atasan && $atasan->id !== $pegawai->id) {
                return $atasan;
            }
        }

        // 2. Evaluasi berdasarkan Matriks Hirarki Jabatan & Unit Kerja
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));
        $unitNama    = strtoupper(trim((string)($pegawai->unitKerja->nama_unit ?? '')));

        // Rule 0: Dekan tidak memiliki atasan di lingkungan fakultas (Atasan langsung adalah Rektor)
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL') && !str_contains($jabatanNama, 'WADEK')) {
            return null;
        }

        // Rule 19: Para Wadek (WD I, WD II, WD III) ──► Approved By ──► Dekan Fakultas Keperawatan
        if (str_contains($jabatanNama, 'WAKIL DEKAN') || str_contains($jabatanNama, 'WADEK')) {
            $atasan = $this->findPegawaiByJabatan(['Dekan']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 16: Kabag Umum ──► Approved By ──► Dekan Fakultas Keperawatan / Wadek II (Bid. Keuangan dan Umum)
        if (str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') || str_contains($jabatanNama, 'KABAG UMUM') || str_contains($jabatanNama, 'KABAG TU')) {
            $atasan = $this->findPegawaiByJabatan([
                'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'Wakil Dekan II',
                'Dekan'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 18: Kepala Lab & Kepala Unit ──► Approved By ──► Wakil Dekan I (Bid. Akedemik) / Wadek II (Bid. Keuangan dan Umum)
        if (str_contains($jabatanNama, 'KEPALA UPT') || str_contains($jabatanNama, 'KEPALA LABORATORIUM') || str_contains($jabatanNama, 'KEPALA LAB') || str_contains($jabatanNama, 'KEPALA UNIT')) {
            $atasan = $this->findPegawaiByJabatan([
                'Wakil Dekan I (Bid. Akademik)',
                'Wakil Dekan I',
                'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'Wakil Dekan II',
                'Dekan'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 6 & 7: Ketua Jurusan Klinik dan Komunitas & Ketua Jurusan Preklinik Keperawatan ──► Approved By ──► Ketua Jurusan (Kajur)
        if (str_contains($jabatanNama, 'KETUA JURUSAN PREKLINIK') || str_contains($jabatanNama, 'KETUA JURUSAN KLINIK')
            || str_contains($jabatanNama, 'KAJUR PREKLINIK') || str_contains($jabatanNama, 'KAJUR KLINIK')) {
            $atasan = $this->findPegawaiByJabatan([
                'Ketua Jurusan (Kajur)',
                'Ketua Jurusan',
                'Wakil Dekan I (Bid. Akademik)',
                'Wakil Dekan I'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 5: Ketua Jurusan (Kajur) ──► Approved By ──► Wakil Dekan I (Bid. Akedemik)
        if ((str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) 
            && !str_contains($jabatanNama, 'PREKLINIK') && !str_contains($jabatanNama, 'KLINIK')) {
            $atasan = $this->findPegawaiByJabatan([
                'Wakil Dekan I (Bid. Akademik)',
                'Wakil Dekan I',
                'Dekan'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 4: Koordinator Prodi S1, S2 dan Ners Keperawatan ──► Approved By ──► Ketua Jurusan
        if (str_contains($jabatanNama, 'KOORDINATOR PRODI') || str_contains($jabatanNama, 'KOORPRODI') || str_contains($jabatanNama, 'KOORDINATOR PROGRAM STUDI')) {
            $atasan = $this->findPegawaiByJabatan([
                'Ketua Jurusan (Kajur)',
                'Ketua Jurusan',
                'Ketua Jurusan Preklinik Keperawatan',
                'Ketua Jurusan Klinik dan Komunitas',
                'Wakil Dekan I (Bid. Akademik)',
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 13, 14, 15: Ka Pokja (Akademik, Keu-Kepeg, Umum Sarana) ──► Approved By ──► Kabag Umum
        if (str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA')) {
            $atasan = $this->findPegawaiByJabatan([
                'Kepala Bagian Umum',
                'Kabag Umum',
                'Kabag TU',
                'Wakil Dekan II (Bid. Keuangan dan Umum)'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 17: PLP / Laboran Lab ──► Approved By ──► Kabag Umum / Kepala UPT Laboratorium Keperawatan
        if (str_contains($jabatanNama, 'PLP') || str_contains($jabatanNama, 'LABORAN') || str_contains($jabatanNama, 'PRANATA LABORATORIUM') || str_contains($unitNama, 'LABORATORIUM')) {
            $atasan = $this->findPegawaiByJabatan([
                'Kepala UPT Laboratorium Keperawatan',
                'Kepala Laboratorium',
                'Kepala Bagian Umum',
                'Kabag Umum'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 10: Staff Pokja Akademik dan Kemahasiswaan ──► Approved By ──► Ka Pokja Akademik
        if (str_contains($jabatanNama, 'STAFF POKJA AKADEMIK') || (str_contains($unitNama, 'AKADEMIK') && (str_contains($unitNama, 'POKJA') || str_contains($jabatanNama, 'STAFF')))) {
            $atasan = $this->findPegawaiByJabatan([
                'Ka Pokja Akademik',
                'Kepala Pokja Akademik',
                'Ketua Pokja Akademik',
                'Kepala Bagian Umum'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 11: Staff Pokja Keu & Kepeg ──► Approved By ──► Ka Pokja Keu-Kepeg
        if (str_contains($jabatanNama, 'STAFF POKJA KEU') || (str_contains($unitNama, 'KEUANGAN') && (str_contains($unitNama, 'POKJA') || str_contains($jabatanNama, 'STAFF')))) {
            $atasan = $this->findPegawaiByJabatan([
                'Ka Pokja Keu-Kepeg',
                'Kepala Pokja Keu',
                'Ketua Pokja Keuangan',
                'Kepala Bagian Umum'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 12: Staff Pokja Umum Sarana Akedemik ──► Approved By ──► Ka Pokja Umum Sarana Akedemik
        if (str_contains($jabatanNama, 'STAFF POKJA UMUM') || (str_contains($unitNama, 'SARANA') && (str_contains($unitNama, 'POKJA') || str_contains($jabatanNama, 'STAFF')))) {
            $atasan = $this->findPegawaiByJabatan([
                'Ka Pokja Umum Sarana Akademik',
                'Ka Pokja Umum',
                'Ketua Pokja Umum',
                'Kepala Bagian Umum'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 8: Dosen Jurusan Klinik dan Komunitas ──► Approved By ──► Ketua Jurusan Klinik dan Komunitas
        if (str_contains($jabatanNama, 'DOSEN JURUSAN KLINIK') || (str_contains($unitNama, 'KLINIK') && !str_contains($unitNama, 'NERS') && !str_contains($jabatanNama, 'NERS'))) {
            $atasan = $this->findPegawaiByJabatan([
                'Ketua Jurusan Klinik dan Komunitas',
                'Ketua Jurusan (Kajur)',
                'Wakil Dekan I (Bid. Akademik)'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 9: Dosen Jurusan Preklinik Keperawatan ──► Approved By ──► Ketua Jurusan Preklinik Keperwatan
        if (str_contains($jabatanNama, 'DOSEN JURUSAN PREKLINIK') || (str_contains($unitNama, 'PREKLINIK') && !str_contains($unitNama, 'S1') && !str_contains($unitNama, 'S2') && !str_contains($jabatanNama, 'S1') && !str_contains($jabatanNama, 'S2'))) {
            $atasan = $this->findPegawaiByJabatan([
                'Ketua Jurusan Preklinik Keperawatan',
                'Ketua Jurusan (Kajur)',
                'Wakil Dekan I (Bid. Akademik)'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 1: Dosen S2 Keperawatan ──► Approved By ──► Koordinator Prodi S2 Keperawatan
        if (str_contains($jabatanNama, 'S2') || str_contains($unitNama, 'S2')) {
            $atasan = $this->findPegawaiByJabatan([
                'Koordinator Prodi S2 Keperawatan',
                'Koorprodi S2',
                'Ketua Jurusan Preklinik Keperawatan',
                'Ketua Jurusan (Kajur)'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 2: Dosen S1 Keperawatan ──► Approved By ──► Koordinator Prodi S1 Keperawatan
        if (str_contains($jabatanNama, 'S1') || (str_contains($unitNama, 'S1') && !str_contains($unitNama, 'S2'))) {
            $atasan = $this->findPegawaiByJabatan([
                'Koordinator Prodi S1 Keperawatan',
                'Koorprodi S1',
                'Ketua Jurusan Preklinik Keperawatan',
                'Ketua Jurusan (Kajur)'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 3: Dosen Profesi Ners ──► Approved By ──► Koordinator Prodi Ners
        if (str_contains($jabatanNama, 'NERS') || str_contains($unitNama, 'NERS')) {
            $atasan = $this->findPegawaiByJabatan([
                'Koordinator Prodi Ners',
                'Koorprodi Ners',
                'Ketua Jurusan Klinik dan Komunitas',
                'Ketua Jurusan (Kajur)'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Fallback untuk Dosen Umum
        if ($pegawai->isDosen()) {
            $atasan = $this->findPegawaiByJabatan([
                'Ketua Jurusan (Kajur)',
                'Koordinator Prodi S1 Keperawatan',
                'Ketua Jurusan Preklinik Keperawatan',
                'Wakil Dekan I (Bid. Akademik)',
                'Dekan'
            ]);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Fallback untuk Tendik / Staff Umum
        $atasan = $this->findPegawaiByJabatan([
            'Kepala Bagian Umum',
            'Wakil Dekan II (Bid. Keuangan dan Umum)',
            'Dekan'
        ]);
        if ($atasan && $atasan->id !== $pegawai->id) return $atasan;

        return null;
    }

    /**
     * Dapatkan ID seluruh pegawai bawahan langsung & sub-bawahan dari seorang pimpinan
     * Sesuai hierarki 15 Jabatan Pimpinan
     */
    public function getBawahanIdsForPegawai(Pegawai $pimpinan): array
    {
        $cacheKey = "bawahan_ids_pegawai_" . $pimpinan->id;
        
        return Cache::remember($cacheKey, 60, function () use ($pimpinan) {
            // 1. Bawahan eksplisit via atasan_id
            $directIds = Pegawai::where('atasan_id', $pimpinan->id)->pluck('id')->toArray();

            // 2. Bawahan implisit via Hirarki Jabatan & Unit Kerja
            $jabatanNama = strtoupper(trim((string)($pimpinan->jabatan->nama_jabatan ?? '')));

            $implicitIds = [];

            // 1. Dekan: Mengawasi seluruh pegawai di lingkungan Fakultas Keperawatan
            if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL') && !str_contains($jabatanNama, 'WADEK')) {
                return Pegawai::where('id', '!=', $pimpinan->id)->pluck('id')->toArray();
            }

            // 2. Wakil Dekan I (Bid. Akademik): Mengawasi Kajur, Koorprodi, Dosen, Ka UPT Lab, Kepala Unit
            elseif (str_contains($jabatanNama, 'WAKIL DEKAN I') || str_contains($jabatanNama, 'WD I') || (str_contains($jabatanNama, 'WAKIL DEKAN') && str_contains($jabatanNama, 'AKADEMIK'))) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Kajur%')
                           ->orWhere('nama_jabatan', 'like', '%Ketua Jurusan%')
                           ->orWhere('nama_jabatan', 'like', '%Koordinator Prodi%')
                           ->orWhere('nama_jabatan', 'like', '%Dosen%')
                           ->orWhere('nama_jabatan', 'like', '%Kepala UPT%')
                           ->orWhere('nama_jabatan', 'like', '%Kepala Lab%')
                           ->orWhere('nama_jabatan', 'like', '%Kepala Unit%');
                    })->orWhere('jenis_pegawai', 'Dosen');
                })->pluck('id')->toArray();
            }

            // 3. Wakil Dekan II (Bid. Keuangan dan Umum): Mengawasi Kabag Umum, Ka Pokja Keu-Kepeg, Ka Pokja Umum, Staff Pokja, PLP
            elseif (str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II') || (str_contains($jabatanNama, 'WAKIL DEKAN') && str_contains($jabatanNama, 'KEUANGAN'))) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Kabag%')
                           ->orWhere('nama_jabatan', 'like', '%Bagian Umum%')
                           ->orWhere('nama_jabatan', 'like', '%Ka Pokja%')
                           ->orWhere('nama_jabatan', 'like', '%Pokja%')
                           ->orWhere('nama_jabatan', 'like', '%Staff%')
                           ->orWhere('nama_jabatan', 'like', '%PLP%')
                           ->orWhere('nama_jabatan', 'like', '%Laboran%')
                           ->orWhere('nama_jabatan', 'like', '%Kepala UPT%');
                    })->orWhere('jenis_pegawai', '!=', 'Dosen');
                })->pluck('id')->toArray();
            }

            // 4. Wakil Dekan III (Bid. Kemahasiswaan): Mengawasi Ka Pokja & Staff Pokja Akademik/Kemahasiswaan & Unit Kemahasiswaan
            elseif (str_contains($jabatanNama, 'WAKIL DEKAN III') || str_contains($jabatanNama, 'WD III') || (str_contains($jabatanNama, 'WAKIL DEKAN') && str_contains($jabatanNama, 'KEMAHASISWAAN'))) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Akademik%')
                           ->orWhere('nama_jabatan', 'like', '%Kemahasiswaan%');
                    })->orWhereHas('unitKerja', function ($uq) {
                        $uq->where('nama_unit', 'like', '%Akademik%')
                           ->orWhere('nama_unit', 'like', '%Kemahasiswaan%');
                    });
                })->pluck('id')->toArray();
            }

            // 5. Ketua Jurusan (Kajur): Mengawasi Kajur Klinik, Kajur Preklinik, Koorprodi S1, S2, Ners & Seluruh Dosen
            elseif ((str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) 
                    && !str_contains($jabatanNama, 'PREKLINIK') && !str_contains($jabatanNama, 'KLINIK')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Jurusan%')
                           ->orWhere('nama_jabatan', 'like', '%Koordinator Prodi%')
                           ->orWhere('nama_jabatan', 'like', '%Dosen%');
                    })->orWhere('jenis_pegawai', 'Dosen');
                })->pluck('id')->toArray();
            }

            // 6. Ketua Jurusan Klinik dan Komunitas: Mengawasi Dosen Jurusan Klinik & Komunitas, Koorprodi Ners & Dosen Profesi Ners
            elseif (str_contains($jabatanNama, 'KLINIK')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Ners%')
                           ->orWhere('nama_jabatan', 'like', '%Klinik%')
                           ->orWhere('nama_jabatan', 'like', '%Komunitas%');
                    })->orWhereHas('unitKerja', function ($uq) {
                        $uq->where('nama_unit', 'like', '%Ners%')
                           ->orWhere('nama_unit', 'like', '%Klinik%');
                    });
                })->pluck('id')->toArray();
            }

            // 7. Ketua Jurusan Preklinik Keperawatan: Mengawasi Dosen Preklinik, Koorprodi S1, S2 & Dosen S1/S2
            elseif (str_contains($jabatanNama, 'PREKLINIK')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%S1%')
                           ->orWhere('nama_jabatan', 'like', '%S2%')
                           ->orWhere('nama_jabatan', 'like', '%Preklinik%');
                    })->orWhereHas('unitKerja', function ($uq) {
                        $uq->where('nama_unit', 'like', '%S1%')
                           ->orWhere('nama_unit', 'like', '%S2%')
                           ->orWhere('nama_unit', 'like', '%Preklinik%');
                    });
                })->pluck('id')->toArray();
            }

            // 8. Koordinator Prodi S1 Keperawatan: Mengawasi Dosen S1 Keperawatan
            elseif (str_contains($jabatanNama, 'KOORDINATOR PRODI S1') || str_contains($jabatanNama, 'KOORPRODI S1') || str_contains($jabatanNama, 'PRODI S1')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Dosen S1%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%S1%')->where('nama_unit', 'not like', '%S2%'));
                })->pluck('id')->toArray();
            }

            // 9. Koordinator Prodi S2 Keperawatan: Mengawasi Dosen S2 Keperawatan
            elseif (str_contains($jabatanNama, 'KOORDINATOR PRODI S2') || str_contains($jabatanNama, 'KOORPRODI S2') || str_contains($jabatanNama, 'PRODI S2')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Dosen S2%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%S2%'));
                })->pluck('id')->toArray();
            }

            // 10. Koordinator Prodi Ners: Mengawasi Dosen Profesi Ners
            elseif (str_contains($jabatanNama, 'NERS')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Ners%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Ners%'));
                })->pluck('id')->toArray();
            }

            // 11. Kepala Bagian Umum: Mengawasi Ka Pokja (Akademik, Keu, Umum), PLP & seluruh Staff Pokja
            elseif (str_contains($jabatanNama, 'KABAG') || str_contains($jabatanNama, 'KEPALA BAGIAN UMUM')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Pokja%')
                           ->orWhere('nama_jabatan', 'like', '%PLP%')
                           ->orWhere('nama_jabatan', 'like', '%Laboran%')
                           ->orWhere('nama_jabatan', 'like', '%Staff%')
                           ->orWhere('nama_jabatan', 'like', '%Pengolah%')
                           ->orWhere('nama_jabatan', 'like', '%Pengadministrasi%')
                           ->orWhere('nama_jabatan', 'like', '%Penata%')
                           ->orWhere('nama_jabatan', 'like', '%Operator%')
                           ->orWhere('nama_jabatan', 'like', '%PHL%');
                    })->orWhere('jenis_pegawai', '!=', 'Dosen');
                })->pluck('id')->toArray();
            }

            // 12. Ka Pokja Akademik: Mengawasi Staff Pokja Akademik dan Kemahasiswaan
            elseif (str_contains($jabatanNama, 'KA POKJA AKADEMIK') || str_contains($jabatanNama, 'POKJA AKADEMIK')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Akademik%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Akademik%'));
                })->pluck('id')->toArray();
            }

            // 13. Ka Pokja Keu-Kepeg: Mengawasi Staff Pokja Keu & Kepeg
            elseif (str_contains($jabatanNama, 'KA POKJA KEU') || str_contains($jabatanNama, 'POKJA KEUANGAN')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Keu%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Keuangan%'));
                })->pluck('id')->toArray();
            }

            // 14. Ka Pokja Umum Sarana Akademik: Mengawasi Staff Pokja Umum Sarana Akademik
            elseif (str_contains($jabatanNama, 'KA POKJA UMUM') || str_contains($jabatanNama, 'POKJA UMUM') || str_contains($jabatanNama, 'SARANA')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Umum%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Sarana%'));
                })->pluck('id')->toArray();
            }

            // 15. Kepala UPT Laboratorium Keperawatan: Mengawasi PLP / Laboran Lab
            elseif (str_contains($jabatanNama, 'KEPALA UPT LAB') || str_contains($jabatanNama, 'KEPALA LABORATORIUM') || str_contains($jabatanNama, 'LABORATORIUM')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%PLP%')
                           ->orWhere('nama_jabatan', 'like', '%Laboran%')
                           ->orWhere('nama_jabatan', 'like', '%Pranata Laboratorium%');
                    })->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Laboratorium%'));
                })->pluck('id')->toArray();
            }

            $allIds = array_unique(array_merge($directIds, $implicitIds));
            
            // Kecualikan diri sendiri dari daftar bawahan
            return array_values(array_filter($allIds, fn($id) => (int)$id !== (int)$pimpinan->id));
        });
    }

    /**
     * Dapatkan Atasan Langsung untuk Permohonan Cuti (Bagian VI)
     * Aturan Cuti PNS / PPPK FKP UNRI:
     * - Dosen ──► Ketua Jurusan (Kajur)
     * - Tendik ──► Kepala Bagian Umum (Kabag Umum)
     */
    public function getAtasanLangsungCuti(Pegawai $pegawai): ?Pegawai
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // Jika pemohon adalah Dekan, Atasan Langsung adalah Rektor (fallback Dekan)
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL')) {
            return $pegawai;
        }

        // Jika pemohon adalah Wadek, Atasan Langsung adalah Dekan
        if (str_contains($jabatanNama, 'WAKIL DEKAN') || str_contains($jabatanNama, 'WADEK')) {
            return $this->findPegawaiByJabatan(['Dekan']);
        }

        // Jika pemohon adalah Ketua Jurusan, Atasan Langsung adalah Wakil Dekan I / Wadek II
        if (str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) {
            return $this->findPegawaiByJabatan(['Wakil Dekan I (Bid. Akademik)', 'Wakil Dekan II (Bid. Keuangan dan Umum)', 'Dekan']);
        }

        // Jika pemohon adalah Kepala Bagian Umum, Atasan Langsung adalah Wakil Dekan II
        if (str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') || str_contains($jabatanNama, 'KABAG UMUM')) {
            return $this->findPegawaiByJabatan(['Wakil Dekan II (Bid. Keuangan dan Umum)', 'Dekan']);
        }

        // Jika Dosen: VI. Pertimbangan Atasan Langsung = Ketua Jurusan
        if ($pegawai->isDosen()) {
            return $this->findPegawaiByJabatan([
                'Ketua Jurusan (Kajur)',
                'Ketua Jurusan Preklinik Keperawatan',
                'Ketua Jurusan Klinik dan Komunitas',
            ]);
        }

        // Jika Tendik (dan PHL/pegawai lainnya): VI. Pertimbangan Atasan Langsung = Kepala Bagian Umum
        return $this->findPegawaiByJabatan([
            'Kepala Bagian Umum',
            'Kabag Umum',
        ]);
    }

    /**
     * Dapatkan Pejabat Yang Berwenang Memberikan Cuti (PYBMC) - Bagian VII
     * Aturan Resmi SIKAP FKP UNRI:
     * Baik Dosen maupun Tendik ──► Wakil Dekan II (Bid. Keuangan dan Umum)
     */
    public function getPybmcCuti(Pegawai $pegawai, ?string $jenisCuti = null): ?Pegawai
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // 1. Jika pemohon adalah Dekan, PYBMC adalah Rektor (fallback ke diri sendiri / admin)
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL')) {
            return $pegawai;
        }

        // 2. Jika pemohon adalah Wadek II, PYBMC dialihkan ke Dekan
        if (str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II')) {
            return $this->findPegawaiByJabatan(['Dekan']);
        }

        // 3. Ketetapan Resmi: Wakil Dekan II (Bid. Keuangan dan Umum)
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
        $isDosen = $pegawai ? $pegawai->isDosen() : false;
        $jabatanNama = strtoupper(trim((string)($pegawai?->jabatan?->nama_jabatan ?? '')));

        $atasanPegawai = $pegawai ? $this->getAtasanLangsungCuti($pegawai) : null;
        $pybmcPegawai  = $pegawai ? $this->getPybmcCuti($pegawai) : null;

        // Label & Nama Atasan Langsung (Bagian VI)
        if ($isDosen) {
            $atasanJabatan = 'Ketua Jurusan';
            $atasanNama    = $atasanPegawai ? ($atasanPegawai->nama_lengkap ?? $atasanPegawai->nama) : 'Ketua Jurusan';
            $atasanNip     = $atasanPegawai?->nip ?? '.....................................................';
        } else {
            $atasanJabatan = 'Kepala Bagian Umum';
            $atasanNama    = $atasanPegawai ? ($atasanPegawai->nama_lengkap ?? $atasanPegawai->nama) : 'Kepala Bagian Umum';
            $atasanNip     = $atasanPegawai?->nip ?? '.....................................................';
        }

        // Label & Nama PYBMC (Bagian VII)
        $pybmcJabatan = 'Wakil Dekan II (Bid. Keuangan dan Umum)';
        $pybmcNama    = $pybmcPegawai ? ($pybmcPegawai->nama_lengkap ?? $pybmcPegawai->nama) : 'Wakil Dekan II';
        $pybmcNip     = $pybmcPegawai?->nip ?? '.....................................................';

        // Pengecualian khusus Pejabat Pimpinan Tertinggi:
        // 1. Jika pemohon adalah Dekan: Atasan & PYBMC adalah Rektor
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL')) {
            $atasanJabatan = 'Rektor Universitas Riau';
            $atasanNama    = 'Rektor Universitas Riau';
            $atasanNip     = '';
            $pybmcJabatan  = 'Rektor Universitas Riau';
            $pybmcNama     = 'Rektor Universitas Riau';
            $pybmcNip      = '';
        }
        // 2. Jika pemohon adalah Wadek II: PYBMC adalah Dekan
        elseif (str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II')) {
            $pybmcJabatan = 'Dekan Fakultas Keperawatan';
            $dekan = $this->findPegawaiByJabatan(['Dekan']);
            if ($dekan) {
                $pybmcNama = $dekan->nama_lengkap ?? $dekan->nama;
                $pybmcNip  = $dekan->nip ?? '';
            }
        }

        return [
            'is_dosen'       => $isDosen,
            'atasan_jabatan' => $atasanJabatan,
            'atasan_nama'    => $atasanNama,
            'atasan_nip'     => $atasanNip,
            'pybmc_jabatan'  => $pybmcJabatan,
            'pybmc_nama'     => $pybmcNama,
            'pybmc_nip'      => $pybmcNip,
        ];
    }

    /**
     * Helper cari Pegawai berdasarkan list nama jabatan
     */
    public function findPegawaiByJabatan(array $jabatanNames): ?Pegawai
    {
        foreach ($jabatanNames as $name) {
            $pegawai = Pegawai::whereHas('jabatan', function ($q) use ($name) {
                $q->where('nama_jabatan', 'like', "%{$name}%");
            })->first();

            if ($pegawai) {
                return $pegawai;
            }
        }

        return null;
    }
}
