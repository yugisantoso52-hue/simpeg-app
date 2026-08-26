<?php

namespace App\Imports;

use App\Models\Pegawai;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PegawaiImport implements ToModel, WithHeadingRow, WithValidation
{
    private function parseDate($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function model(array $row)
    {
        $tanggalLahir = $this->parseDate($row['tanggal_lahir_yyyy_mm_dd'] ?? $row['tanggal_lahir'] ?? null);
        $tanggalMasuk = $this->parseDate($row['tanggal_masuk_yyyy_mm_dd'] ?? $row['tanggal_masuk'] ?? null);
        $tmtSkPertama = $this->parseDate($row['tmt_sk_pertama_yyyy_mm_dd'] ?? $row['tmt_sk_pertama'] ?? null);
        $tmtPangkat   = $this->parseDate($row['tmt_pangkat_terakhir_yyyy_mm_dd'] ?? $row['tmt_pangkat_terakhir'] ?? null);
        $tmtKgb       = $this->parseDate($row['tmt_kgb_terakhir_yyyy_mm_dd'] ?? $row['tmt_kgb_terakhir'] ?? null);

        // Auto Hitung KGB (+2 thn) & KP (+4 thn) Berikutnya
        $kgbBerikutnya = $tmtKgb ? Carbon::parse($tmtKgb)->addYears(2)->toDateString() : null;
        $kpBerikutnya  = $tmtPangkat ? Carbon::parse($tmtPangkat)->addYears(4)->toDateString() : null;

        // Auto Hitung Satyalancana Berikutnya (10/20/30 thn)
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

        // Normalisasi jenis_pegawai: backward-compat Honorer → PHL
        $rawJenis = $row['jenis_pegawai_dosen_pns_pppk_phl'] 
            ?? $row['jenis_pegawai_dosenpnspppkphl'] 
            ?? $row['jenis_pegawai_pnspppkdosenphl'] 
            ?? $row['jenis_pegawai'] 
            ?? 'PNS';
            
        $jenisPegawai = trim($rawJenis);
        if (strcasecmp($jenisPegawai, 'honorer') === 0) {
            $jenisPegawai = 'PHL';
        }

        // Resolusi kolom pendukung dengan berbagai format slug header
        $karpeg = $row['karpeg_karis_karsu'] ?? $row['karpeg'] ?? null;
        $nidn = $row['nidn_nuptk'] ?? $row['nidn'] ?? $row['nuptk'] ?? null;
        $pendidikanTerakhir = $row['pendidikan_terakhir_sd_smp_sma_d1_d2_d3_d4_s1_s2_s3_profesi'] 
            ?? $row['pendidikan_terakhir_sdsmpsmad1d2d3d4s1s2s3profesi'] 
            ?? $row['pendidikan_terakhir'] 
            ?? $row['pendidikan'] 
            ?? null;
        $statusAsn = $row['status_asn_asn_non_asn'] 
            ?? $row['status_asn_asnnon_asn'] 
            ?? $row['status_asn'] 
            ?? 'ASN';
        $statusPegawai = $row['status_pegawai_aktif_tugas_belajar_non_aktif_pensiun'] 
            ?? $row['status_pegawai_aktiftugas_belajarnon_aktifpensiun'] 
            ?? $row['status_pegawai_aktifpensiun'] 
            ?? $row['status_pegawai'] 
            ?? 'Aktif';
        $jenisKelamin = strtoupper(trim($row['jenis_kelamin_l_p'] ?? $row['jenis_kelamin_lp'] ?? $row['jenis_kelamin'] ?? 'L'));

        return new Pegawai([
            'nip'                     => trim((string)$row['nip']),
            'karpeg_karis_karsu'      => $karpeg ? trim((string)$karpeg) : null,
            'nidn_nuptk'              => $nidn ? trim((string)$nidn) : null,
            'nama'                    => trim($row['nama_lengkap'] ?? $row['nama'] ?? ''),
            'gelar_depan'             => !empty($row['gelar_depan']) ? trim($row['gelar_depan']) : null,
            'gelar_belakang'          => !empty($row['gelar_belakang']) ? trim($row['gelar_belakang']) : null,
            'tempat_lahir'            => !empty($row['tempat_lahir']) ? trim($row['tempat_lahir']) : null,
            'tanggal_lahir'           => $tanggalLahir,
            'jenis_kelamin'           => in_array($jenisKelamin, ['L', 'P']) ? $jenisKelamin : 'L',
            'agama'                   => !empty($row['agama']) ? trim($row['agama']) : null,
            'pendidikan_terakhir'     => !empty($pendidikanTerakhir) ? trim($pendidikanTerakhir) : null,
            'jenis_pegawai'           => $jenisPegawai,
            'status_asn'              => $statusAsn,
            'unit_kerja_id'           => !empty($row['id_unit_kerja']) ? (int)$row['id_unit_kerja'] : null,
            'jabatan_id'              => !empty($row['id_jabatan']) ? (int)$row['id_jabatan'] : null,
            'golongan_id'             => !empty($row['id_golongan']) ? (int)$row['id_golongan'] : null,
            'mkg_tahun'               => (int)($row['mkg_tahun'] ?? 0),
            'mkg_bulan'               => (int)($row['mkg_bulan'] ?? 0),
            'tanggal_masuk'           => $tanggalMasuk,
            'tmt_sk_pertama'          => $tmtSkPertama,
            'tmt_pangkat_terakhir'    => $tmtPangkat,
            'tmt_kgb_terakhir'        => $tmtKgb,
            'kgb_berikutnya'          => $kgbBerikutnya,
            'kp_berikutnya'           => $kpBerikutnya,
            'satyalancana_berikutnya' => $satyalancanaBerikutnya,
            'status_pegawai'          => $statusPegawai,
        ]);
    }

    public function rules(): array
    {
        return [
            'nip' => 'required|unique:pegawai,nip',
        ];
    }
}