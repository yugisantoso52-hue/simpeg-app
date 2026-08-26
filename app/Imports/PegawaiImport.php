<?php

namespace App\Imports;

use App\Models\Pegawai;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PegawaiImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $tanggalLahir = !empty($row['tanggal_lahir_yyyy_mm_dd']) ? Carbon::parse($row['tanggal_lahir_yyyy_mm_dd'])->toDateString() : null;
        $tanggalMasuk = !empty($row['tanggal_masuk_yyyy_mm_dd']) ? Carbon::parse($row['tanggal_masuk_yyyy_mm_dd'])->toDateString() : null;
        $tmtSkPertama = !empty($row['tmt_sk_pertama_yyyy_mm_dd']) ? Carbon::parse($row['tmt_sk_pertama_yyyy_mm_dd'])->toDateString() : null;
        $tmtPangkat   = !empty($row['tmt_pangkat_terakhir_yyyy_mm_dd']) ? Carbon::parse($row['tmt_pangkat_terakhir_yyyy_mm_dd'])->toDateString() : null;
        $tmtKgb       = !empty($row['tmt_kgb_terakhir_yyyy_mm_dd']) ? Carbon::parse($row['tmt_kgb_terakhir_yyyy_mm_dd'])->toDateString() : null;

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
        $jenisPegawai = $row['jenis_pegawai_dosenpnspppkphl'] ?? $row['jenis_pegawai_pnspppkdosenphl'] ?? 'PNS';
        if (strtolower($jenisPegawai) === 'honorer') {
            $jenisPegawai = 'PHL';
        }

        return new Pegawai([
            'nip'                     => $row['nip'],
            'karpeg_karis_karsu'      => $row['karpeg_karis_karsu'] ?? null,
            'nidn_nuptk'              => $row['nidn_nuptk'] ?? null,
            'nama'                    => $row['nama_lengkap'],
            'gelar_depan'             => $row['gelar_depan'] ?? null,
            'gelar_belakang'          => $row['gelar_belakang'] ?? null,
            'tempat_lahir'            => $row['tempat_lahir'] ?? null,
            'tanggal_lahir'           => $tanggalLahir,
            'jenis_kelamin'           => strtoupper($row['jenis_kelamin_lp'] ?? 'L'),
            'agama'                   => $row['agama'] ?? null,
            'pendidikan_terakhir'     => $row['pendidikan_terakhir_sdsmpsmad1d2d3d4s1s2s3profesi'] ?? $row['pendidikan'] ?? null,
            'jenis_pegawai'           => $jenisPegawai,
            'status_asn'              => $row['status_asn_asnnon_asn'] ?? 'ASN',
            'unit_kerja_id'           => $row['id_unit_kerja'] ?? null,
            'jabatan_id'              => $row['id_jabatan'] ?? null,
            'golongan_id'             => $row['id_golongan'] ?? null,
            'mkg_tahun'               => (int)($row['mkg_tahun'] ?? 0),
            'mkg_bulan'               => (int)($row['mkg_bulan'] ?? 0),
            'tanggal_masuk'           => $tanggalMasuk,
            'tmt_sk_pertama'          => $tmtSkPertama,
            'tmt_pangkat_terakhir'    => $tmtPangkat,
            'tmt_kgb_terakhir'        => $tmtKgb,
            'kgb_berikutnya'          => $kgbBerikutnya,
            'kp_berikutnya'           => $kpBerikutnya,
            'satyalancana_berikutnya' => $satyalancanaBerikutnya,
            'status_pegawai'          => $row['status_pegawai_aktiftugas_belajarnon_aktifpensiun'] ?? $row['status_pegawai_aktifpensiun'] ?? 'Aktif',
        ]);
    }

    public function rules(): array
    {
        return [
            'nip'          => 'required|unique:pegawai,nip',
            'nama_lengkap' => 'required',
        ];
    }
}