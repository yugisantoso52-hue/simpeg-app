<?php

namespace App\Services;

use App\Models\Pegawai;

class PejabatService
{
    /**
     * In-memory memoization untuk pemanggilan dalam satu siklus request
     */
    protected static array $memoizedPejabat = [];

    /**
     * Dapatkan detail pejabat penandatangan berdasarkan kunci role (e.g. 'dekan', 'wadek2', 'kabag')
     *
     * @param string $key Kunci pejabat ('dekan', 'wadek1', 'wadek2', 'wadek3', 'kabag', 'kapokja')
     * @return array [
     *     'pegawai' => ?Pegawai,
     *     'nama'    => string,
     *     'nip'     => string,
     *     'jabatan' => string,
     *     'pangkat' => string,
     * ]
     */
    public function getPejabat(string $key): array
    {
        $key = strtolower(trim($key));

        if (array_key_exists($key, self::$memoizedPejabat)) {
            return self::$memoizedPejabat[$key];
        }

        $config = config("simpeg.pejabat.{$key}", []);
        $keywords = $config['jabatan_keywords'] ?? [];
        $excludeKeywords = $config['exclude_keywords'] ?? [];

        $pegawai = null;
        if (!empty($keywords)) {
            $pegawai = $this->findPegawaiByJabatanKeywords($keywords, $excludeKeywords);
        }

        if ($pegawai) {
            $result = [
                'pegawai' => $pegawai,
                'nama'    => $pegawai->nama_lengkap ?? $pegawai->nama,
                'nip'     => $pegawai->nip ?? ($config['nip'] ?? '-'),
                'jabatan' => $pegawai->jabatan?->nama_jabatan ?? ($config['jabatan'] ?? '-'),
                'pangkat' => $pegawai->golongan?->nama_pangkat ?? ($config['pangkat'] ?? '-'),
            ];
        } else {
            $result = [
                'pegawai' => null,
                'nama'    => $config['nama'] ?? 'Pejabat Belum Ditetapkan',
                'nip'     => $config['nip'] ?? '-',
                'jabatan' => $config['jabatan'] ?? '-',
                'pangkat' => $config['pangkat'] ?? '-',
            ];
        }

        self::$memoizedPejabat[$key] = $result;

        return $result;
    }

    /**
     * Cari pegawai aktif berdasarkan keyword nama jabatan
     */
    public function findPegawaiByJabatanKeywords(array $keywords, array $excludeKeywords = []): ?Pegawai
    {
        return Pegawai::where('status_pegawai', 'Aktif')
            ->whereHas('jabatan', function ($query) use ($keywords, $excludeKeywords) {
                $query->where(function ($subQ) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $subQ->orWhere('nama_jabatan', 'like', "%{$kw}%");
                    }
                });

                foreach ($excludeKeywords as $exc) {
                    $query->where('nama_jabatan', 'not like', "%{$exc}%");
                }
            })
            ->first();
    }

    /**
     * Shortcut untuk Dekan Fakultas Keperawatan
     */
    public function getDekan(): array
    {
        return $this->getPejabat('dekan');
    }

    public function getDekanPegawai(): ?Pegawai
    {
        return $this->getDekan()['pegawai'];
    }

    /**
     * Shortcut untuk Wakil Dekan I (Akademik)
     */
    public function getWadek1(): array
    {
        return $this->getPejabat('wadek1');
    }

    public function getWadek1Pegawai(): ?Pegawai
    {
        return $this->getWadek1()['pegawai'];
    }

    /**
     * Shortcut untuk Wakil Dekan II (Keuangan dan Umum)
     */
    public function getWadek2(): array
    {
        return $this->getPejabat('wadek2');
    }

    public function getWadek2Pegawai(): ?Pegawai
    {
        return $this->getWadek2()['pegawai'];
    }

    /**
     * Shortcut untuk Wakil Dekan III (Kemahasiswaan)
     */
    public function getWadek3(): array
    {
        return $this->getPejabat('wadek3');
    }

    public function getWadek3Pegawai(): ?Pegawai
    {
        return $this->getWadek3()['pegawai'];
    }

    /**
     * Shortcut untuk Kepala Bagian Umum / TU
     */
    public function getKabag(): array
    {
        return $this->getPejabat('kabag');
    }

    public function getKabagPegawai(): ?Pegawai
    {
        return $this->getKabag()['pegawai'];
    }

    /**
     * Shortcut untuk Ka Pokja Keu-Kepeg
     */
    public function getKaPokja(): array
    {
        return $this->getPejabat('kapokja');
    }

    public function getKaPokjaPegawai(): ?Pegawai
    {
        return $this->getKaPokja()['pegawai'];
    }
}
