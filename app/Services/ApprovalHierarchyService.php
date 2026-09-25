<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ApprovalHierarchyService
{
    /**
     * Dapatkan Pegawai Atasan Langsung berdasarkan atasan_id DB atau Matriks Hirarki 15 Jabatan
     */
    public function getAtasanLangsung(Pegawai $pegawai): ?Pegawai
    {
        // 1. Cek jika atasan_id sudah terisi manual di DB
        if (!empty($pegawai->atasan_id)) {
            $atasan = Pegawai::with(['jabatan', 'unitKerja'])->find($pegawai->atasan_id);
            if ($atasan) {
                return $atasan;
            }
        }

        // 2. Fallback berdasarkan Matriks Hirarki Jabatan & Unit Kerja
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));
        $unitNama    = strtoupper(trim((string)($pegawai->unitKerja->nama_unit ?? '')));

        // Rule 1: Dosen S2 Keperawatan -> Koordinator Prodi S2 Keperawatan
        if (str_contains($jabatanNama, 'S2') || str_contains($unitNama, 'S2')) {
            $atasan = $this->findPegawaiByJabatan(['Koordinator Prodi S2 Keperawatan', 'Koorprodi S2']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 2: Dosen S1 Keperawatan -> Koordinator Prodi S1 Keperawatan
        if (str_contains($jabatanNama, 'S1') || (str_contains($unitNama, 'S1') && !str_contains($unitNama, 'S2'))) {
            $atasan = $this->findPegawaiByJabatan(['Koordinator Prodi S1 Keperawatan', 'Koorprodi S1']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 3: Dosen Profesi Ners -> Koordinator Prodi Ners
        if (str_contains($jabatanNama, 'NERS') || str_contains($unitNama, 'NERS')) {
            $atasan = $this->findPegawaiByJabatan(['Koordinator Prodi Ners', 'Koorprodi Ners']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 4: Koordinator Prodi S1, S2, Ners -> Ketua Jurusan (Kajur)
        if (str_contains($jabatanNama, 'KOORDINATOR PRODI') || str_contains($jabatanNama, 'KOORPRODI')) {
            if (str_contains($jabatanNama, 'NERS') || str_contains($unitNama, 'NERS')) {
                $atasan = $this->findPegawaiByJabatan(['Ketua Jurusan Klinik dan Komunitas', 'Kajur Klinik']);
            } else {
                $atasan = $this->findPegawaiByJabatan(['Ketua Jurusan Preklinik Keperawatan', 'Kajur Preklinik']);
            }
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 5: Ketua Jurusan (Kajur) -> Wakil Dekan I (Bid. Akademik)
        if (str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) {
            $atasan = $this->findPegawaiByJabatan(['Wakil Dekan I (Bid. Akademik)', 'Wakil Dekan I']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 6: Staff Pokja Akademik -> Ka Pokja Akademik
        if (str_contains($jabatanNama, 'STAFF POKJA AKADEMIK') || (str_contains($unitNama, 'AKADEMIK') && str_contains($unitNama, 'POKJA'))) {
            $atasan = $this->findPegawaiByJabatan(['Ka Pokja Akademik', 'Kepala Pokja Akademik']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 7: Staff Pokja Keu & Kepeg -> Ka Pokja Keu-Kepeg
        if (str_contains($jabatanNama, 'STAFF POKJA KEU') || (str_contains($unitNama, 'KEUANGAN') && str_contains($unitNama, 'POKJA'))) {
            $atasan = $this->findPegawaiByJabatan(['Ka Pokja Keu-Kepeg', 'Kepala Pokja Keu']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 8: Staff Pokja Umum Sarana -> Ka Pokja Umum Sarana
        if (str_contains($jabatanNama, 'STAFF POKJA UMUM') || (str_contains($unitNama, 'SARANA') && str_contains($unitNama, 'POKJA'))) {
            $atasan = $this->findPegawaiByJabatan(['Ka Pokja Umum Sarana Akademik', 'Ka Pokja Umum']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 9, 10, 11: Ka Pokja (Akademik, Keu, Umum) -> Kabag Umum
        if (str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA')) {
            $atasan = $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum', 'Kabag TU']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 12: Kabag Umum -> Dekan / Wadek II
        if (str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') || str_contains($jabatanNama, 'KABAG UMUM')) {
            $atasan = $this->findPegawaiByJabatan(['Wakil Dekan II (Bid. Keuangan dan Umum)', 'Dekan']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 13: PLP / Laboran -> Kepala UPT Lab / Kabag Umum
        if (str_contains($jabatanNama, 'PLP') || str_contains($jabatanNama, 'LABORAN') || str_contains($unitNama, 'LABORATORIUM')) {
            $atasan = $this->findPegawaiByJabatan(['Kepala UPT Laboratorium Keperawatan', 'Kepala Bagian Umum']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 14: Kepala Lab & Kepala Unit -> Wakil Dekan I / Wadek II
        if (str_contains($jabatanNama, 'KEPALA UPT') || str_contains($jabatanNama, 'KEPALA UNIT')) {
            $atasan = $this->findPegawaiByJabatan(['Wakil Dekan I (Bid. Akademik)', 'Wakil Dekan II (Bid. Keuangan dan Umum)']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Rule 15: Para Wadek (WD I, WD II, WD III) -> Dekan
        if (str_contains($jabatanNama, 'WAKIL DEKAN') || str_contains($jabatanNama, 'WADEK')) {
            $atasan = $this->findPegawaiByJabatan(['Dekan']);
            if ($atasan && $atasan->id !== $pegawai->id) return $atasan;
        }

        // Default Fallback jika tidak terpetakan khusus: Dekan atau Kabag Umum
        return $this->findPegawaiByJabatan(['Dekan', 'Kepala Bagian Umum']);
    }

    /**
     * Dapatkan ID seluruh pegawai bawahan langsung & sub-bawahan dari seorang pimpinan
     */
    public function getBawahanIdsForPegawai(Pegawai $pimpinan): array
    {
        $cacheKey = "bawahan_ids_pegawai_" . $pimpinan->id;
        
        return Cache::remember($cacheKey, 60, function () use ($pimpinan) {
            // 1. Bawahan eksplisit via atasan_id
            $directIds = Pegawai::where('atasan_id', $pimpinan->id)->pluck('id')->toArray();

            // 2. Bawahan implisit via Hirarki Jabatan & Unit Kerja
            $jabatanNama = strtoupper(trim((string)($pimpinan->jabatan->nama_jabatan ?? '')));
            $unitNama    = strtoupper(trim((string)($pimpinan->unitKerja->nama_unit ?? '')));

            $implicitIds = [];

            // A. Dekan: Bawahan seluruh pegawai di Fakultas Keperawatan
            if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL')) {
                return Pegawai::where('id', '!=', $pimpinan->id)->pluck('id')->toArray();
            }

            // B. Wadek I (Akademik): Mengawasi Kajur, Koorprodi, Dosen, Kepala UPT Lab, Kepala Unit
            if (str_contains($jabatanNama, 'WAKIL DEKAN I') || str_contains($jabatanNama, 'WD I')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Kajur%')
                           ->orWhere('nama_jabatan', 'like', '%Ketua Jurusan%')
                           ->orWhere('nama_jabatan', 'like', '%Koordinator Prodi%')
                           ->orWhere('nama_jabatan', 'like', '%Dosen%')
                           ->orWhere('nama_jabatan', 'like', '%Kepala UPT%')
                           ->orWhere('nama_jabatan', 'like', '%Kepala Unit%');
                    });
                })->pluck('id')->toArray();
            }

            // C. Wadek II (Keuangan & Umum): Mengawasi Kabag Umum, Ka Pokja Keu-Kepeg, Ka Pokja Umum Sarana, Staff Pokja
            elseif (str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Kabag%')
                           ->orWhere('nama_jabatan', 'like', '%Ka Pokja%')
                           ->orWhere('nama_jabatan', 'like', '%Staff Pokja%')
                           ->orWhere('nama_jabatan', 'like', '%PLP%')
                           ->orWhere('nama_jabatan', 'like', '%Laboran%');
                    });
                })->pluck('id')->toArray();
            }

            // D. Wadek III (Kemahasiswaan): Mengawasi Ka Pokja Akademik & Kemahasiswaan & Staff Pokja
            elseif (str_contains($jabatanNama, 'WAKIL DEKAN III') || str_contains($jabatanNama, 'WD III')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Akademik%')
                           ->orWhere('nama_jabatan', 'like', '%Kemahasiswaan%');
                    });
                })->pluck('id')->toArray();
            }

            // E. Ketua Jurusan Preklinik: Mengawasi Koorprodi S1, S2 & Dosen S1/S2
            elseif (str_contains($jabatanNama, 'PREKLINIK')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%S1%')
                           ->orWhere('nama_jabatan', 'like', '%S2%')
                           ->orWhere('nama_jabatan', 'like', '%Preklinik%');
                    });
                })->pluck('id')->toArray();
            }

            // F. Ketua Jurusan Klinik & Komunitas: Mengawasi Koorprodi Ners & Dosen Ners
            elseif (str_contains($jabatanNama, 'KLINIK')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Ners%')
                           ->orWhere('nama_jabatan', 'like', '%Klinik%');
                    });
                })->pluck('id')->toArray();
            }

            // G. Koorprodi S1: Mengawasi Dosen S1
            elseif (str_contains($jabatanNama, 'KOORDINATOR PRODI S1') || str_contains($jabatanNama, 'PRODI S1')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Dosen S1%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%S1%'));
                })->pluck('id')->toArray();
            }

            // H. Koorprodi S2: Mengawasi Dosen S2
            elseif (str_contains($jabatanNama, 'KOORDINATOR PRODI S2') || str_contains($jabatanNama, 'PRODI S2')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Dosen S2%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%S2%'));
                })->pluck('id')->toArray();
            }

            // I. Koorprodi Ners: Mengawasi Dosen Ners
            elseif (str_contains($jabatanNama, 'NERS')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Ners%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Ners%'));
                })->pluck('id')->toArray();
            }

            // J. Kabag Umum: Mengawasi Ka Pokja (Akademik, Keu, Umum), PLP, Staff Pokja
            elseif (str_contains($jabatanNama, 'KABAG') || str_contains($jabatanNama, 'KEPALA BAGIAN UMUM')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Pokja%')
                           ->orWhere('nama_jabatan', 'like', '%PLP%')
                           ->orWhere('nama_jabatan', 'like', '%Laboran%')
                           ->orWhere('nama_jabatan', 'like', '%Staff%');
                    });
                })->pluck('id')->toArray();
            }

            // K. Ka Pokja Akademik: Mengawasi Staff Pokja Akademik
            elseif (str_contains($jabatanNama, 'KA POKJA AKADEMIK')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Akademik%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Akademik%'));
                })->pluck('id')->toArray();
            }

            // L. Ka Pokja Keu-Kepeg: Mengawasi Staff Pokja Keu-Kepeg
            elseif (str_contains($jabatanNama, 'KA POKJA KEU')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Keu%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Keuangan%'));
                })->pluck('id')->toArray();
            }

            // M. Ka Pokja Umum Sarana: Mengawasi Staff Pokja Umum Sarana
            elseif (str_contains($jabatanNama, 'KA POKJA UMUM')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Umum%'))
                       ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Sarana%'));
                })->pluck('id')->toArray();
            }

            // N. Kepala UPT Lab: Mengawasi PLP / Laboran Lab
            elseif (str_contains($jabatanNama, 'KEPALA UPT LAB') || str_contains($jabatanNama, 'LABORATORIUM')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%PLP%')
                           ->orWhere('nama_jabatan', 'like', '%Laboran%');
                    })->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Laboratorium%'));
                })->pluck('id')->toArray();
            }

            $allIds = array_unique(array_merge($directIds, $implicitIds));
            
            // Kecualikan diri sendiri dari daftar bawahan
            return array_values(array_filter($allIds, fn($id) => (int)$id !== (int)$pimpinan->id));
        });
    }

    /**
     * Dapatkan Pejabat Yang Berwenang Memberikan Cuti (PYBMC) sesuai PerBKN 7/2022
     */
    public function getPybmc(Pegawai $pegawai, string $jenisCuti): ?Pegawai
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // 1. Jika pemohon adalah Dekan, PYBMC adalah Rektor (fallback ke Dekan / Admin)
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL')) {
            return $pegawai; 
        }

        // 2. Jenis Cuti Besar, CLTN, Cuti Melahirkan, Cuti Alasan Penting -> PYBMC Keputusan Dekan
        if (in_array($jenisCuti, ['Cuti Besar', 'Cuti Di Luar Tanggungan Negara', 'CLTN', 'Cuti Melahirkan', 'Cuti Alasan Penting'])) {
            return $this->findPegawaiByJabatan(['Dekan']);
        }

        // 3. Cuti Tahunan & Cuti Sakit -> PYBMC bisa delegasi ke Kabag Umum / Wadek II / Dekan
        return $this->findPegawaiByJabatan(['Dekan', 'Wakil Dekan II (Bid. Keuangan dan Umum)', 'Kepala Bagian Umum']);
    }

    /**
     * Helper cari Pegawai berdasarkan list nama jabatan
     */
    protected function findPegawaiByJabatan(array $jabatanNames): ?Pegawai
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
