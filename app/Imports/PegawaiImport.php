<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Models\Golongan;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PegawaiImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public function collection(Collection $rows)
    {
        // 1. Preload master data to prevent N+1 queries during Excel parsing
        $unitKerjaMap = [];
        UnitKerja::all()->each(function ($item) {
            $unitKerjaMap[(int)$item->id] = (int)$item->id;
            $unitKerjaMap[strtolower(trim($item->nama_unit))] = (int)$item->id;
            if (!empty($item->kode_unit)) {
                $unitKerjaMap[strtolower(trim($item->kode_unit))] = (int)$item->id;
            }
        });

        $jabatanMap = [];
        Jabatan::all()->each(function ($item) {
            $jabatanMap[(int)$item->id] = (int)$item->id;
            $jabatanMap[strtolower(trim($item->nama_jabatan))] = (int)$item->id;
            if (!empty($item->kode_jabatan)) {
                $jabatanMap[strtolower(trim($item->kode_jabatan))] = (int)$item->id;
            }
        });

        $golonganMap = [];
        Golongan::all()->each(function ($item) {
            $golonganMap[(int)$item->id] = (int)$item->id;
            $golonganMap[strtolower(trim($item->nama_golongan))] = (int)$item->id;
            if (!empty($item->nama_pangkat)) {
                $golonganMap[strtolower(trim($item->nama_pangkat))] = (int)$item->id;
            }
        });

        $existingEmails = User::pluck('id', 'email')
            ->mapWithKeys(fn($val, $key) => [strtolower($key) => $val])
            ->toArray();

        $rolePegawai = Role::where('name', 'pegawai')->first();
        $rolePegawaiId = $rolePegawai ? $rolePegawai->id : null;

        $pegawaiToUpsert = [];
        $validRows = [];

        // Loop 1: Parse and validate all rows, collect Pegawai records
        foreach ($rows as $row) {
            $nipRaw  = $row['nip'] ?? $row[0] ?? '';
            $nip     = preg_replace('/[^0-9]/', '', (string)$nipRaw);
            $nama = trim((string)($row['nama_lengkap'] ?? $row['nama'] ?? $row['nama_pegawai'] ?? ''));

            if (empty($nip) && empty($nama)) {
                continue;
            }

            if (empty($nip)) {
                throw new \Exception("NIP tidak boleh kosong pada salah satu baris data.");
            }
            if (empty($nama)) {
                throw new \Exception("Nama Lengkap tidak boleh kosong untuk NIP: " . $nip);
            }

            $nik = !empty($row['nik']) ? trim((string)$row['nik']) : null;

            // Date parsing
            $tanggalLahir = $this->parseDateSafe($row['tanggal_lahir_yyyy_mm_dd'] ?? $row['tanggal_lahir'] ?? null);
            $tanggalMasuk = $this->parseDateSafe($row['tanggal_masuk_yyyy_mm_dd'] ?? $row['tanggal_masuk'] ?? null);
            $tmtSkPertama = $this->parseDateSafe($row['tmt_sk_pertama_yyyy_mm_dd'] ?? $row['tmt_sk_pertama'] ?? null);
            $tmtPangkat   = $this->parseDateSafe($row['tmt_pangkat_terakhir_yyyy_mm_dd'] ?? $row['tmt_pangkat_terakhir'] ?? null);
            $tmtKgb       = $this->parseDateSafe($row['tmt_kgb_terakhir_yyyy_mm_dd'] ?? $row['tmt_kgb_terakhir'] ?? null);

            $kgbBerikutnya = $tmtKgb ? Carbon::parse($tmtKgb)->addYears(2)->toDateString() : null;
            $kpBerikutnya  = $tmtPangkat ? Carbon::parse($tmtPangkat)->addYears(4)->toDateString() : null;

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

            // Reference resolution in memory
            $unitKerjaRaw = $row['id_unit_kerja'] ?? $row['unit_kerja_id'] ?? $row['unit_kerja'] ?? $row['nama_unit'] ?? $row['unit'] ?? null;
            $jabatanRaw   = $row['id_jabatan'] ?? $row['jabatan_id'] ?? $row['jabatan'] ?? $row['nama_jabatan'] ?? null;
            $golonganRaw  = $row['id_golongan'] ?? $row['golongan_id'] ?? $row['golongan'] ?? $row['nama_golongan'] ?? $row['pangkat'] ?? $row['nama_pangkat'] ?? null;

            $unitKerjaId = $this->resolveId($unitKerjaRaw, $unitKerjaMap);
            $jabatanId   = $this->resolveId($jabatanRaw, $jabatanMap);
            $golonganId  = $this->resolveId($golonganRaw, $golonganMap);

            $pegawaiData = [
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
                'created_at'              => now(),
                'updated_at'              => now(),
            ];

            $pegawaiToUpsert[] = $pegawaiData;
            $validRows[] = [
                'nip'          => $nip,
                'nama'         => $nama,
                'unit_kerja_id'=> $unitKerjaId,
                'jabatan_id'   => $jabatanId,
                'golongan_id'  => $golonganId,
                'tanggal_masuk'=> $tanggalMasuk,
                'tmt_pangkat'  => $tmtPangkat,
                'pendidikan'   => !empty($row['pendidikan']) ? trim($row['pendidikan']) : null,
                'tanggal_lahir'=> $tanggalLahir,
            ];
        }

        if (empty($pegawaiToUpsert)) {
            return;
        }

        // 3. Bulk Upsert Pegawai
        Pegawai::upsert($pegawaiToUpsert, ['nip'], [
            'nik', 'nama', 'gelar_depan', 'gelar_belakang', 'tempat_lahir', 'tanggal_lahir',
            'jenis_kelamin', 'agama', 'pendidikan', 'jenis_pegawai', 'status_asn',
            'unit_kerja_id', 'jabatan_id', 'golongan_id', 'tanggal_masuk', 'tmt_sk_pertama',
            'tmt_pangkat_terakhir', 'tmt_kgb_terakhir', 'kgb_berikutnya', 'kp_berikutnya',
            'satyalancana_berikutnya', 'status_pegawai', 'updated_at'
        ]);

        // 4. Load all newly upserted pegawai IDs
        $nips = collect($pegawaiToUpsert)->pluck('nip')->toArray();
        $pegawaiMap = Pegawai::whereIn('nip', $nips)->pluck('id', 'nip')->toArray();

        // 5. Gather existing relation records to prevent duplicates
        $existingJabatan = RiwayatJabatan::whereIn('pegawai_id', array_values($pegawaiMap))
            ->get()
            ->groupBy('pegawai_id')
            ->map(fn($items) => $items->pluck('jabatan_id')->toArray())
            ->toArray();

        $existingPangkat = RiwayatPangkat::whereIn('pegawai_id', array_values($pegawaiMap))
            ->get()
            ->groupBy('pegawai_id')
            ->map(fn($items) => $items->pluck('golongan_id')->toArray())
            ->toArray();

        $existingPendidikan = \App\Models\RiwayatPendidikan::whereIn('pegawai_id', array_values($pegawaiMap))
            ->get()
            ->groupBy('pegawai_id')
            ->map(fn($items) => $items->pluck('jenjang')->map(fn($j) => strtoupper($j))->toArray())
            ->toArray();

        $usersToInsert = [];
        $riwayatJabatanToInsert = [];
        $riwayatPangkatToInsert = [];
        $riwayatPendidikanToInsert = [];

        // Loop 2: Build histories and users
        foreach ($validRows as $row) {
            $nip = $row['nip'];
            $pegawaiId = $pegawaiMap[$nip] ?? null;
            if (!$pegawaiId) continue;

            $nama = $row['nama'];
            $unitKerjaId = $row['unit_kerja_id'];
            $jabatanId = $row['jabatan_id'];
            $golonganId = $row['golongan_id'];
            $tanggalMasuk = $row['tanggal_masuk'];
            $tmtPangkat = $row['tmt_pangkat'];
            $pendidikan = $row['pendidikan'];
            $tanggalLahir = $row['tanggal_lahir'];

            // A. User creation
            if ($rolePegawaiId) {
                $emailLogin = $nip . '@staff.unri.ac.id';

                if (!isset($existingEmails[strtolower($emailLogin)])) {
                    $dob = '19900101';
                    if (!empty($tanggalLahir)) {
                        try {
                            $dob = Carbon::parse($tanggalLahir)->format('Ymd');
                        } catch (\Exception $e) {
                            $dob = '19900101';
                        }
                    }
                    $usersToInsert[] = [
                        'name'                 => $nama,
                        'email'                => $emailLogin,
                        'password'             => Hash::make($dob),
                        'role_id'              => $rolePegawaiId,
                        'pegawai_id'           => $pegawaiId,
                        'must_change_password' => true,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                    $existingEmails[strtolower($emailLogin)] = true;
                }
            }

            // B. Riwayat Jabatan
            if ($jabatanId && $unitKerjaId) {
                if (!isset($existingJabatan[$pegawaiId]) || !in_array($jabatanId, $existingJabatan[$pegawaiId])) {
                    $riwayatJabatanToInsert[] = [
                        'pegawai_id'    => $pegawaiId,
                        'jabatan_id'    => $jabatanId,
                        'unit_kerja_id' => $unitKerjaId,
                        'tmt_jabatan'   => $tanggalMasuk ?? now()->toDateString(),
                        'keterangan'    => 'Disinkronkan dari Impor Excel',
                        'status'        => 'aktif',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            }

            // C. Riwayat Pangkat
            if ($golonganId) {
                if (!isset($existingPangkat[$pegawaiId]) || !in_array($golonganId, $existingPangkat[$pegawaiId])) {
                    $riwayatPangkatToInsert[] = [
                        'pegawai_id'  => $pegawaiId,
                        'golongan_id' => $golonganId,
                        'tmt'         => $tmtPangkat ?? now()->toDateString(),
                        'keterangan'  => 'Disinkronkan dari Impor Excel',
                        'status'      => 'aktif',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }

            // D. Riwayat Pendidikan
            if (!empty($pendidikan)) {
                $jenjangUpper = strtoupper($pendidikan);
                if (!isset($existingPendidikan[$pegawaiId]) || !in_array($jenjangUpper, $existingPendidikan[$pegawaiId])) {
                    $riwayatPendidikanToInsert[] = [
                        'pegawai_id' => $pegawaiId,
                        'jenjang'    => $pendidikan,
                        'institusi'  => 'Universitas / Sekolah',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // 6. Bulk Insert Users & Histories in chunks
        if (!empty($usersToInsert)) {
            foreach (array_chunk($usersToInsert, 100) as $chunk) {
                DB::table('users')->insert($chunk);
            }
        }
        if (!empty($riwayatJabatanToInsert)) {
            foreach (array_chunk($riwayatJabatanToInsert, 100) as $chunk) {
                DB::table('riwayat_jabatan')->insert($chunk);
            }
        }
        if (!empty($riwayatPangkatToInsert)) {
            foreach (array_chunk($riwayatPangkatToInsert, 100) as $chunk) {
                DB::table('riwayat_pangkat')->insert($chunk);
            }
        }
        if (!empty($riwayatPendidikanToInsert)) {
            foreach (array_chunk($riwayatPendidikanToInsert, 100) as $chunk) {
                DB::table('riwayat_pendidikan')->insert($chunk);
            }
        }
    }

    private function resolveId($val, array $map): ?int
    {
        if (empty($val)) return null;
        $val = trim((string)$val);
        if (is_numeric($val) && isset($map[(int)$val])) {
            return (int)$val;
        }

        $key = strtolower($val);
        if (isset($map[$key])) {
            return $map[$key];
        }

        foreach ($map as $name => $id) {
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
}