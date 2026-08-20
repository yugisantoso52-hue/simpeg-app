<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Models\Golongan;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PegawaiImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithChunkReading
{
    private array $unitKerjas = [];
    private array $jabatans = [];
    private array $golongans = [];

    public function __construct()
    {
        // Preload Master Data to prevent N+1 database lookups in loop
        
        // 1. Unit Kerja
        UnitKerja::all()->each(function ($item) {
            $this->unitKerjas[(int)$item->id] = (int)$item->id;
            $this->unitKerjas[strtolower(trim($item->nama_unit))] = (int)$item->id;
            if (!empty($item->kode_unit)) {
                $this->unitKerjas[strtolower(trim($item->kode_unit))] = (int)$item->id;
            }
        });

        // 2. Jabatan
        Jabatan::all()->each(function ($item) {
            $this->jabatans[(int)$item->id] = (int)$item->id;
            $this->jabatans[strtolower(trim($item->nama_jabatan))] = (int)$item->id;
            if (!empty($item->kode_jabatan)) {
                $this->jabatans[strtolower(trim($item->kode_jabatan))] = (int)$item->id;
            }
        });

        // 3. Golongan
        Golongan::all()->each(function ($item) {
            $this->golongans[(int)$item->id] = (int)$item->id;
            $this->golongans[strtolower(trim($item->nama_golongan))] = (int)$item->id;
            if (!empty($item->nama_pangkat)) {
                $this->golongans[strtolower(trim($item->nama_pangkat))] = (int)$item->id;
            }
        });
    }

    public function model(array $row)
    {
        // 1. Ambil NIP & Nama Lengkap (Mendukung berbagai kemungkinan nama kolom header Excel)
        $nip  = trim((string)($row['nip'] ?? $row[0] ?? ''));
        $nama = trim((string)($row['nama_lengkap'] ?? $row['nama'] ?? $row['nama_pegawai'] ?? ''));

        // Jika NIP dan Nama kosong (baris kosong di bawah Excel), lewati secara aman
        if (empty($nip) && empty($nama)) {
            return null;
        }

        // Validasi input wajib
        if (empty($nip)) {
            throw new \Exception("NIP tidak boleh kosong pada salah satu baris data.");
        }
        if (empty($nama)) {
            throw new \Exception("Nama Lengkap tidak boleh kosong untuk NIP: " . $nip);
        }

        $nik = !empty($row['nik']) ? trim((string)$row['nik']) : null;

        // Pembacaan Tanggal Aman
        $tanggalLahir = $this->parseDateSafe($row['tanggal_lahir_yyyy_mm_dd'] ?? $row['tanggal_lahir'] ?? null);
        $tanggalMasuk = $this->parseDateSafe($row['tanggal_masuk_yyyy_mm_dd'] ?? $row['tanggal_masuk'] ?? null);
        $tmtSkPertama = $this->parseDateSafe($row['tmt_sk_pertama_yyyy_mm_dd'] ?? $row['tmt_sk_pertama'] ?? null);
        $tmtPangkat   = $this->parseDateSafe($row['tmt_pangkat_terakhir_yyyy_mm_dd'] ?? $row['tmt_pangkat_terakhir'] ?? null);
        $tmtKgb       = $this->parseDateSafe($row['tmt_kgb_terakhir_yyyy_mm_dd'] ?? $row['tmt_kgb_terakhir'] ?? null);

        // Auto Hitung KGB (+2 Thn) & KP (+4 Thn) Berikutnya
        $kgbBerikutnya = $tmtKgb ? Carbon::parse($tmtKgb)->addYears(2)->toDateString() : null;
        $kpBerikutnya  = $tmtPangkat ? Carbon::parse($tmtPangkat)->addYears(4)->toDateString() : null;

        // Auto Hitung Satyalancana Berikutnya (10/20/30 Thn)
        $satyalancanaBerikutnya = null;
        $tmtAwal = $tanggalMasuk ?? $tmtSkPertama;
        if ($tmtAwal) {
            $start = Carbon::parse($tmtAwal);
            $years = $start->diffInYears(now());
            if ($years < 10) {
                $satyalancanaBerikutnya = $start->copy()->addYears(10)->toDateString();
            } elseif ($years < 20) {
                $satyalancanaBerikutnya = $start->copy()->addYears(20)->toDateString();
            } elseif ($years < 30) {
                $satyalancanaBerikutnya = $start->copy()->addYears(30)->toDateString();
            }
        }

        // Resolusi Cerdas Unit Kerja, Jabatan, & Golongan dari cache memori
        $unitKerjaRaw = $row['id_unit_kerja'] ?? $row['unit_kerja_id'] ?? $row['unit_kerja'] ?? $row['nama_unit'] ?? $row['unit'] ?? null;
        $jabatanRaw   = $row['id_jabatan'] ?? $row['jabatan_id'] ?? $row['jabatan'] ?? $row['nama_jabatan'] ?? null;
        $golonganRaw  = $row['id_golongan'] ?? $row['golongan_id'] ?? $row['golongan'] ?? $row['nama_golongan'] ?? $row['pangkat'] ?? $row['nama_pangkat'] ?? null;

        $unitKerjaId = $this->resolveUnitKerjaId($unitKerjaRaw);
        $jabatanId   = $this->resolveJabatanId($jabatanRaw);
        $golonganId  = $this->resolveGolonganId($golonganRaw);

        $dataPegawai = [
            'nip'                     => $nip,
            'nik'                     => $nik,
            'nama'                    => $nama,
            'gelar_depan'             => !empty($row['gelar_depan']) ? trim($row['gelar_depan']) : null,
            'gelar_belakang'          => !empty($row['gelar_belakang']) ? trim($row['gelar_belakang']) : null,
            'tempat_lahir'            => !empty($row['tempat_lahir']) ? trim($row['tempat_lahir']) : null,
            'tanggal_lahir'           => $tanggalLahir,
            'jenis_kelamin'           => strtoupper($row['jenis_kelamin_lp'] ?? $row['jenis_kelamin'] ?? 'L'),
            'agama'                   => !empty($row['agama']) ? trim($row['agama']) : null,
            'pendidikan'              => !empty($row['pendidikan']) ? trim($row['pendidikan']) : null,
            'jenis_pegawai'           => !empty($row['jenis_pegawai_pnspppkhonorer']) ? trim($row['jenis_pegawai_pnspppkhonorer']) : (!empty($row['jenis_pegawai']) ? trim($row['jenis_pegawai']) : 'PNS'),
            'status_asn'              => !empty($row['status_asn_asnnon_asn']) ? trim($row['status_asn_asnnon_asn']) : (!empty($row['status_asn']) ? trim($row['status_asn']) : 'ASN'),
            'unit_kerja_id'           => $unitKerjaId,
            'jabatan_id'              => $jabatanId,
            'golongan_id'             => $golonganId,
            'tanggal_masuk'           => $tanggalMasuk,
            'tmt_sk_pertama'          => $tmtSkPertama,
            'tmt_pangkat_terakhir'    => $tmtPangkat,
            'tmt_kgb_terakhir'        => $tmtKgb,
            'kgb_berikutnya'          => $kgbBerikutnya,
            'kp_berikutnya'           => $kpBerikutnya,
            'satyalancana_berikutnya' => $satyalancanaBerikutnya,
            'status_pegawai'          => !empty($row['status_pegawai_aktifpensiun']) ? trim($row['status_pegawai_aktifpensiun']) : (!empty($row['status_pegawai']) ? trim($row['status_pegawai']) : 'Aktif'),
        ];

        // Simpan data baru atau perbarui jika NIP sudah ada
        $pegawai = Pegawai::updateOrCreate(
            ['nip' => $nip],
            $dataPegawai
        );

        // Otomatis Sinkronkan Riwayat Jabatan
        if ($pegawai->jabatan_id && $pegawai->unit_kerja_id) {
            RiwayatJabatan::firstOrCreate(
                [
                    'pegawai_id'    => $pegawai->id,
                    'jabatan_id'    => $pegawai->jabatan_id,
                    'unit_kerja_id' => $pegawai->unit_kerja_id,
                ],
                [
                    'tmt_jabatan'   => $pegawai->tanggal_masuk ?? now(),
                    'keterangan'    => 'Disinkronkan dari Impor Excel',
                    'status'        => 'aktif',
                ]
            );
        }

        // Otomatis Sinkronkan Riwayat Pangkat
        if ($pegawai->golongan_id) {
            RiwayatPangkat::firstOrCreate(
                [
                    'pegawai_id'  => $pegawai->id,
                    'golongan_id' => $pegawai->golongan_id,
                ],
                [
                    'tmt'         => $pegawai->tmt_pangkat_terakhir ?? now(),
                    'keterangan'  => 'Disinkronkan dari Impor Excel',
                    'status'      => 'aktif',
                ]
            );
        }

        // Otomatis Sinkronkan Riwayat Pendidikan
        if (!empty($pegawai->pendidikan)) {
            \App\Models\RiwayatPendidikan::firstOrCreate(
                [
                    'pegawai_id' => $pegawai->id,
                    'jenjang'    => $pegawai->pendidikan,
                ],
                [
                    'institusi'  => 'Universitas / Sekolah',
                ]
            );
        }

        return $pegawai;
    }

    private function resolveUnitKerjaId($val): ?int
    {
        if (empty($val)) return null;
        $val = trim((string)$val);
        if (is_numeric($val) && isset($this->unitKerjas[(int)$val])) {
            return (int)$val;
        }

        $key = strtolower($val);
        if (isset($this->unitKerjas[$key])) {
            return $this->unitKerjas[$key];
        }

        foreach ($this->unitKerjas as $name => $id) {
            if (is_string($name) && str_contains($name, $key)) {
                return $id;
            }
        }
        return null;
    }

    private function resolveJabatanId($val): ?int
    {
        if (empty($val)) return null;
        $val = trim((string)$val);
        if (is_numeric($val) && isset($this->jabatans[(int)$val])) {
            return (int)$val;
        }

        $key = strtolower($val);
        if (isset($this->jabatans[$key])) {
            return $this->jabatans[$key];
        }

        foreach ($this->jabatans as $name => $id) {
            if (is_string($name) && str_contains($name, $key)) {
                return $id;
            }
        }
        return null;
    }

    private function resolveGolonganId($val): ?int
    {
        if (empty($val)) return null;
        $val = trim((string)$val);
        if (is_numeric($val) && isset($this->golongans[(int)$val])) {
            return (int)$val;
        }

        $key = strtolower($val);
        if (isset($this->golongans[$key])) {
            return $this->golongans[$key];
        }

        foreach ($this->golongans as $name => $id) {
            if (is_string($name) && str_contains($name, $key)) {
                return $id;
            }
        }
        return null;
    }

    private function parseDateSafe($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return Carbon::parse(str_replace('/', '-', trim($value)))->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}