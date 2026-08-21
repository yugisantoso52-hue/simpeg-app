<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\RiwayatPendidikan;
use App\Models\RiwayatDiklat;
use App\Models\User;
use App\Models\Role;
use App\Repositories\Contracts\PegawaiRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PegawaiService
{
    protected PegawaiRepositoryInterface $pegawaiRepository;

    public function __construct(PegawaiRepositoryInterface $pegawaiRepository)
    {
        $this->pegawaiRepository = $pegawaiRepository;
    }   

    /**
     * Upload foto pegawai ke storage public
     */
    private function uploadFoto(?UploadedFile $foto): ?string
    {
        if (!$foto) return null;
        return $foto->store('pegawai_foto', 'public');
    }

    /**
     * Hapus foto pegawai dari storage public
     */
    private function deleteFoto(?string $foto): void
    {
        if ($foto && Storage::disk('public')->exists($foto)) {
            Storage::disk('public')->delete($foto);
        }
    }

    /**
     * Upload berkas SK pegawai ke storage privat (local)
     */
    private function uploadSK(?UploadedFile $file): ?string
    {
        if (!$file) return null;
        return $file->store('pegawai/sk', 'local');
    }

    /**
     * Hapus berkas SK pegawai dari storage privat / public
     */
    private function deleteSK(?string $filePath): void
    {
        if ($filePath) {
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            } elseif (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        }
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
        if (blank($tmtPertama)) {
            return [
                'terakhir' => null,
                'berikutnya' => null
            ];
        }

        $start = Carbon::parse($tmtPertama);
        $masaKerja = $start->diffInYears(now());

        if ($masaKerja < 10) {
            return [
                'terakhir' => '-',
                'berikutnya' => $start->copy()->addYears(10)->format('Y-m-d')
            ];
        }

        if ($masaKerja < 20) {
            return [
                'terakhir' => 'Perunggu',
                'berikutnya' => $start->copy()->addYears(20)->format('Y-m-d')
            ];
        }

        if ($masaKerja < 30) {
            return [
                'terakhir' => 'Perak',
                'berikutnya' => $start->copy()->addYears(30)->format('Y-m-d')
            ];
        }

        return [
            'terakhir' => 'Emas',
            'berikutnya' => null
        ];
    }

    public function search(?string $search)
    {
        return $this->pegawaiRepository->search($search);
    }

    public function find(string|int $id): Pegawai
    {
        return $this->pegawaiRepository
            ->findOrFail($id)
            ->load([
                'unitKerja',
                'jabatan',
                'golongan',
                'riwayatPendidikan',
                'riwayatDiklat',
            ]);
    }

    /**
     * Membuat data pegawai baru beserta akun user login, riwayat awal, dan berkas SK
     */
    public function createPegawai(array $data, array $files = []): Pegawai
    {
        if (!empty($data['nip'])) {
            $data['nip'] = preg_replace('/[^0-9]/', '', $data['nip']);
        }
        return DB::transaction(function () use ($data, $files) {
            if (!empty($files['foto'])) {
                $data['foto'] = $this->uploadFoto($files['foto']);
            }

            if (!empty($files['file_sk_pertama'])) {
                $data['file_sk_pertama'] = $this->uploadSK($files['file_sk_pertama']);
            }
            if (!empty($files['file_sk_pangkat_terakhir'])) {
                $data['file_sk_pangkat_terakhir'] = $this->uploadSK($files['file_sk_pangkat_terakhir']);
            }
            if (!empty($files['file_sk_kgb_terakhir'])) {
                $data['file_sk_kgb_terakhir'] = $this->uploadSK($files['file_sk_kgb_terakhir']);
            }

            $data['kgb_berikutnya'] = $this->generateKGB($data['tmt_kgb_terakhir'] ?? null);
            $data['kp_berikutnya'] = $this->generateKP($data['tmt_pangkat_terakhir'] ?? null);

            $satyalancana = $this->generateSatyalancana($data['tmt_sk_pertama'] ?? null);
            $data['satyalancana_terakhir'] = $satyalancana['terakhir'];
            $data['satyalancana_berikutnya'] = $satyalancana['berikutnya'];

            $data['jumlah_anak'] = $data['jumlah_anak'] ?? 0;
            $data['tinggi_badan'] = $data['tinggi_badan'] ?? 0;
            $data['berat_badan'] = $data['berat_badan'] ?? 0;
            $data['status_pegawai'] = $data['status_pegawai'] ?? 'Aktif';

            // 1. Simpan data pegawai utama
            $pegawai = $this->pegawaiRepository->create($data);

            // 2. OTOMATISASI: Buat Akun User Login untuk Pegawai Baru (NIP Tanpa Spasi)
            $rolePegawai = Role::where('name', 'pegawai')->first();
            if ($rolePegawai) {
                $nipClean = preg_replace('/[^0-9]/', '', $pegawai->nip ?? '');
                $identifier = !empty($nipClean) ? $nipClean : 'pegawai_' . $pegawai->id;
                $emailLogin = $identifier . '@staff.unri.ac.id';

                $passwordDefault = '19900101';
                if (!empty($pegawai->tanggal_birth) || !empty($pegawai->tanggal_lahir)) {
                    try {
                        $passwordDefault = Carbon::parse($pegawai->tanggal_lahir)->format('Ymd');
                    } catch (\Exception $e) {
                        $passwordDefault = '19900101';
                    }
                }

                User::create([
                    'name'                 => $pegawai->nama,
                    'email'                => $emailLogin,
                    'password'             => Hash::make($passwordDefault), // Password default awal pegawai
                    'role_id'              => $rolePegawai->id,
                    'pegawai_id'           => $pegawai->id,
                    'must_change_password' => true,
                ]);
            }

            // 3. Simpan Riwayat Pendidikan
            if (isset($data['riwayat_pendidikan']) && is_array($data['riwayat_pendidikan'])) {
                foreach ($data['riwayat_pendidikan'] as $index => $row) {
                    if (empty($row['jenjang']) && empty($row['institusi'])) {
                        continue;
                    }
                    $fileIjazah = null;
                    if (isset($files['riwayat_pendidikan'][$index]['ijazah'])) {
                        $fileIjazah = $files['riwayat_pendidikan'][$index]['ijazah']->store('ijazah', 'local');
                    }
                    $pegawai->riwayatPendidikan()->create([
                        'jenjang'     => $row['jenjang'] ?? 'S1',
                        'institusi'   => $row['institusi'] ?? 'Universitas / Sekolah',
                        'fakultas'    => $row['fakultas'] ?? null,
                        'jurusan'     => $row['jurusan'] ?? null,
                        'tahun_lulus' => !empty($row['tahun_lulus']) ? (int)$row['tahun_lulus'] : null,
                        'ijazah'      => $fileIjazah,
                    ]);
                }
            }

            // 4. Simpan Riwayat Diklat
            if (isset($data['riwayat_diklat']) && is_array($data['riwayat_diklat'])) {
                foreach ($data['riwayat_diklat'] as $index => $row) {
                    if (empty($row['nama_diklat'])) {
                        continue;
                    }
                    $fileSertifikat = null;
                    if (isset($files['riwayat_diklat'][$index]['file_sertifikat'])) {
                        $fileSertifikat = $files['riwayat_diklat'][$index]['file_sertifikat']->store('sertifikat_diklat', 'local');
                    }
                    $tanggalMulai   = !empty($row['tanggal_mulai']) ? $row['tanggal_mulai'] : date('Y-m-d');
                    $tanggalSelesai = !empty($row['tanggal_selesai']) ? $row['tanggal_selesai'] : $tanggalMulai;

                    $pegawai->riwayatDiklat()->create([
                        'nama_diklat'      => $row['nama_diklat'],
                        'jenis_diklat'     => $row['jenis_diklat'] ?? null,
                        'penyelenggara'    => $row['penyelenggara'] ?? null,
                        'nomor_sertifikat' => $row['nomor_sertifikat'] ?? null,
                        'tanggal_mulai'    => $tanggalMulai,
                        'tanggal_selesai'  => $tanggalSelesai,
                        'status'           => $row['status'] ?? 'Aktif',
                        'keterangan'       => $row['keterangan'] ?? null,
                        'file_sertifikat'  => $fileSertifikat,
                    ]);
                }
            }

            // 5. Simpan Riwayat Jabatan
            if (isset($data['riwayat_jabatan']) && is_array($data['riwayat_jabatan'])) {
                foreach ($data['riwayat_jabatan'] as $index => $row) {
                    if (empty($row['jabatan_id']) || empty($row['unit_kerja_id'])) {
                        continue;
                    }
                    $fileSk = null;
                    if (isset($files['riwayat_jabatan'][$index]['file_sk'])) {
                        $fileSk = $files['riwayat_jabatan'][$index]['file_sk']->store('sk_jabatan', 'local');
                    }
                    $pegawai->riwayatJabatan()->create([
                        'jabatan_id'    => $row['jabatan_id'],
                        'unit_kerja_id' => $row['unit_kerja_id'],
                        'tmt_jabatan'   => $row['tmt_jabatan'] ?? now(),
                        'nomor_sk'      => $row['nomor_sk'] ?? null,
                        'tanggal_sk'    => $row['tanggal_sk'] ?? null,
                        'status'        => $row['status'] ?? 'riwayat',
                        'file_sk'       => $fileSk,
                        'keterangan'    => $row['keterangan'] ?? 'Input dari form tambah pegawai',
                    ]);
                }
            }

            // 6. Simpan Riwayat Pangkat
            if (isset($data['riwayat_pangkat']) && is_array($data['riwayat_pangkat'])) {
                foreach ($data['riwayat_pangkat'] as $index => $row) {
                    if (empty($row['golongan_id'])) {
                        continue;
                    }
                    $fileSk = null;
                    if (isset($files['riwayat_pangkat'][$index]['file_sk'])) {
                        $fileSk = $files['riwayat_pangkat'][$index]['file_sk']->store('sk_pangkat', 'local');
                    }
                    $pegawai->riwayatPangkat()->create([
                        'golongan_id' => $row['golongan_id'],
                        'tmt'         => $row['tmt'] ?? now(),
                        'nomor_sk'    => $row['nomor_sk'] ?? null,
                        'tanggal_sk'  => $row['tanggal_sk'] ?? null,
                        'status'      => $row['status'] ?? 'riwayat',
                        'file_sk'     => $fileSk,
                        'keterangan'  => $row['keterangan'] ?? 'Input dari form tambah pegawai',
                    ]);
                }
            }

            // 7. Sinkronisasikan data ke pegawai utama
            $this->syncPegawaiFromHistories($pegawai);

            return $pegawai;
        });
    }

    /**
     * Memperbarui data pegawai, berkas SK, dan menyinkronkan seluruh riwayat terkait
     */
    public function updatePegawai(int|string $id, array $data, array $files = []): Pegawai
    {
        if (!empty($data['nip'])) {
            $data['nip'] = preg_replace('/[^0-9]/', '', $data['nip']);
        }
        return DB::transaction(function () use ($id, $data, $files) {
            $pegawai = $this->pegawaiRepository->findOrFail($id);
            $oldNip = $pegawai->nip;

            if (!empty($files['foto'])) {
                $this->deleteFoto($pegawai->foto);
                $data['foto'] = $this->uploadFoto($files['foto']);
            } else {
                unset($data['foto']);
            }

            if (!empty($files['file_sk_pertama'])) {
                $this->deleteSK($pegawai->file_sk_pertama);
                $data['file_sk_pertama'] = $this->uploadSK($files['file_sk_pertama']);
            } else {
                unset($data['file_sk_pertama']);
            }

            if (!empty($files['file_sk_pangkat_terakhir'])) {
                $this->deleteSK($pegawai->file_sk_pangkat_terakhir);
                $data['file_sk_pangkat_terakhir'] = $this->uploadSK($files['file_sk_pangkat_terakhir']);
            } else {
                unset($data['file_sk_pangkat_terakhir']);
            }

            if (!empty($files['file_sk_kgb_terakhir'])) {
                $this->deleteSK($pegawai->file_sk_kgb_terakhir);
                $data['file_sk_kgb_terakhir'] = $this->uploadSK($files['file_sk_kgb_terakhir']);
            } else {
                unset($data['file_sk_kgb_terakhir']);
            }

            $data['kgb_berikutnya'] = $this->generateKGB($data['tmt_kgb_terakhir'] ?? null);
            $data['kp_berikutnya'] = $this->generateKP($data['tmt_pangkat_terakhir'] ?? null);

            $satyalancana = $this->generateSatyalancana($data['tmt_sk_pertama'] ?? null);
            $data['satyalancana_terakhir'] = $satyalancana['terakhir'];
            $data['satyalancana_berikutnya'] = $satyalancana['berikutnya'];

            // Update tabel pegawai utama
            $pegawaiUpdated = $this->pegawaiRepository->update($pegawai->id, $data);

            if ($oldNip !== $pegawaiUpdated->nip) {
                $user = User::where('pegawai_id', $pegawaiUpdated->id)->first();
                if ($user) {
                    $user->email = $pegawaiUpdated->nip . '@staff.unri.ac.id';
                    $user->save();
                }
            }

            // Catat log perubahan data (timestamp dan identitas pengubah)
            $changes = $pegawaiUpdated->getChanges();
            if (!empty($changes)) {
                $user = auth()->user();
                $modifier = $user ? "User ID {$user->id} ({$user->name})" : "System/Console";
                $logFilePath = storage_path('logs/audit_pegawai.log');
                $logMessage = sprintf(
                    "[%s] Pegawai ID %s (%s) diubah oleh %s. Perubahan: %s\n",
                    now()->toDateTimeString(),
                    $pegawaiUpdated->id,
                    $pegawaiUpdated->nama,
                    $modifier,
                    json_encode($changes, JSON_UNESCAPED_UNICODE)
                );
                @file_put_contents($logFilePath, $logMessage, FILE_APPEND);
            }

            // Synchronize User table name and email if user exists
            if ($pegawaiUser = User::where('pegawai_id', $pegawaiUpdated->id)->first()) {
                $pegawaiUser->update([
                    'name'  => $pegawaiUpdated->nama,
                    'email' => $pegawaiUpdated->email ?: $pegawaiUser->email,
                ]);
            }

            // 1. UPDATE / SIMPAN RIWAYAT PENDIDIKAN
            $existingPendidikanIds = $pegawai->riwayatPendidikan()->pluck('id')->toArray();
            $incomingPendidikan = $data['riwayat_pendidikan'] ?? [];
            $incomingPendidikanIds = collect($incomingPendidikan)->pluck('id')->filter()->toArray();
            $pegawai->riwayatPendidikan()->whereIn('id', array_diff($existingPendidikanIds, $incomingPendidikanIds))->delete();
            foreach ($incomingPendidikan as $index => $row) {
                if (empty($row['jenjang']) && empty($row['institusi'])) {
                    continue;
                }
                $fileIjazah = null;
                if (isset($files['riwayat_pendidikan'][$index]['ijazah'])) {
                    $fileIjazah = $files['riwayat_pendidikan'][$index]['ijazah']->store('ijazah', 'local');
                }
                
                $payload = [
                    'jenjang'     => $row['jenjang'] ?? 'S1',
                    'institusi'   => $row['institusi'] ?? 'Universitas / Sekolah',
                    'fakultas'    => $row['fakultas'] ?? null,
                    'jurusan'     => $row['jurusan'] ?? null,
                    'tahun_lulus' => !empty($row['tahun_lulus']) ? (int)$row['tahun_lulus'] : null,
                ];
                if ($fileIjazah) {
                    $payload['ijazah'] = $fileIjazah;
                }

                if (!empty($row['id'])) {
                    $pegawai->riwayatPendidikan()->where('id', $row['id'])->update($payload);
                } else {
                    $pegawai->riwayatPendidikan()->create($payload);
                }
            }

            // 2. UPDATE / SIMPAN RIWAYAT DIKLAT
            $existingDiklatIds = $pegawai->riwayatDiklat()->pluck('id')->toArray();
            $incomingDiklat = $data['riwayat_diklat'] ?? [];
            $incomingDiklatIds = collect($incomingDiklat)->pluck('id')->filter()->toArray();
            $pegawai->riwayatDiklat()->whereIn('id', array_diff($existingDiklatIds, $incomingDiklatIds))->delete();
            foreach ($incomingDiklat as $index => $row) {
                if (empty($row['nama_diklat'])) {
                    continue;
                }
                $fileSertifikat = null;
                if (isset($files['riwayat_diklat'][$index]['file_sertifikat'])) {
                    $fileSertifikat = $files['riwayat_diklat'][$index]['file_sertifikat']->store('sertifikat_diklat', 'local');
                }
                $tanggalMulai   = !empty($row['tanggal_mulai']) ? $row['tanggal_mulai'] : date('Y-m-d');
                $tanggalSelesai = !empty($row['tanggal_selesai']) ? $row['tanggal_selesai'] : $tanggalMulai;

                $payload = [
                    'nama_diklat'      => $row['nama_diklat'],
                    'jenis_diklat'     => $row['jenis_diklat'] ?? null,
                    'penyelenggara'    => $row['penyelenggara'] ?? null,
                    'nomor_sertifikat' => $row['nomor_sertifikat'] ?? null,
                    'tanggal_mulai'    => $tanggalMulai,
                    'tanggal_selesai'  => $tanggalSelesai,
                    'status'           => $row['status'] ?? 'Aktif',
                    'keterangan'       => $row['keterangan'] ?? null,
                ];
                if ($fileSertifikat) {
                    $payload['file_sertifikat'] = $fileSertifikat;
                }

                if (!empty($row['id'])) {
                    $pegawai->riwayatDiklat()->where('id', $row['id'])->update($payload);
                } else {
                    $pegawai->riwayatDiklat()->create($payload);
                }
            }

            // 3. UPDATE / SIMPAN RIWAYAT JABATAN
            $existingJabatanIds = $pegawai->riwayatJabatan()->pluck('id')->toArray();
            $incomingJabatan = $data['riwayat_jabatan'] ?? [];
            $incomingJabatanIds = collect($incomingJabatan)->pluck('id')->filter()->toArray();
            $pegawai->riwayatJabatan()->whereIn('id', array_diff($existingJabatanIds, $incomingJabatanIds))->delete();
            foreach ($incomingJabatan as $index => $row) {
                if (empty($row['jabatan_id']) || empty($row['unit_kerja_id'])) {
                    continue;
                }
                $fileSk = null;
                if (isset($files['riwayat_jabatan'][$index]['file_sk'])) {
                    $fileSk = $files['riwayat_jabatan'][$index]['file_sk']->store('sk_jabatan', 'local');
                }

                $payload = [
                    'jabatan_id'    => $row['jabatan_id'],
                    'unit_kerja_id' => $row['unit_kerja_id'],
                    'tmt_jabatan'   => $row['tmt_jabatan'] ?? now(),
                    'nomor_sk'      => $row['nomor_sk'] ?? null,
                    'tanggal_sk'    => $row['tanggal_sk'] ?? null,
                    'status'        => $row['status'] ?? 'riwayat',
                    'keterangan'    => $row['keterangan'] ?? 'Update dari form edit pegawai',
                ];
                if ($fileSk) {
                    $payload['file_sk'] = $fileSk;
                }

                if (!empty($row['id'])) {
                    $pegawai->riwayatJabatan()->where('id', $row['id'])->update($payload);
                } else {
                    $pegawai->riwayatJabatan()->create($payload);
                }
            }

            // 4. UPDATE / SIMPAN RIWAYAT PANGKAT
            $existingPangkatIds = $pegawai->riwayatPangkat()->pluck('id')->toArray();
            $incomingPangkat = $data['riwayat_pangkat'] ?? [];
            $incomingPangkatIds = collect($incomingPangkat)->pluck('id')->filter()->toArray();
            $pegawai->riwayatPangkat()->whereIn('id', array_diff($existingPangkatIds, $incomingPangkatIds))->delete();
            foreach ($incomingPangkat as $index => $row) {
                if (empty($row['golongan_id'])) {
                    continue;
                }
                $fileSk = null;
                if (isset($files['riwayat_pangkat'][$index]['file_sk'])) {
                    $fileSk = $files['riwayat_pangkat'][$index]['file_sk']->store('sk_pangkat', 'local');
                }

                $payload = [
                    'golongan_id' => $row['golongan_id'],
                    'tmt'         => $row['tmt'] ?? now(),
                    'nomor_sk'    => $row['nomor_sk'] ?? null,
                    'tanggal_sk'  => $row['tanggal_sk'] ?? null,
                    'status'      => $row['status'] ?? 'riwayat',
                    'keterangan'  => $row['keterangan'] ?? 'Update dari form edit pegawai',
                ];
                if ($fileSk) {
                    $payload['file_sk'] = $fileSk;
                }

                if (!empty($row['id'])) {
                    $pegawai->riwayatPangkat()->where('id', $row['id'])->update($payload);
                } else {
                    $pegawai->riwayatPangkat()->create($payload);
                }
            }

            // 5. Sinkronisasikan data ke pegawai utama
            $this->syncPegawaiFromHistories($pegawaiUpdated);

            return $pegawaiUpdated;
        });
    }

    public function deletePegawai(int|string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $pegawai = $this->pegawaiRepository->findOrFail($id);

            $this->deleteFoto($pegawai->foto);
            $this->deleteSK($pegawai->file_sk_pertama);
            $this->deleteSK($pegawai->file_sk_pangkat_terakhir);
            $this->deleteSK($pegawai->file_sk_kgb_terakhir);

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
            // 1. Ambil daftar path file yang perlu dihapus dalam satu query batch
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
                RiwayatPendidikan::whereIn('pegawai_id', $ids)->pluck('ijazah')->filter()->toArray(),
                RiwayatJabatan::whereIn('pegawai_id', $ids)->pluck('file_sk')->filter()->toArray(),
                RiwayatPangkat::whereIn('pegawai_id', $ids)->pluck('file_sk')->filter()->toArray(),
                RiwayatDiklat::whereIn('pegawai_id', $ids)->pluck('file_sertifikat')->filter()->toArray(),
                \App\Models\MutasiPegawai::whereIn('pegawai_id', $ids)->pluck('file_sk')->filter()->toArray()
            );

            // Pisahkan berdasarkan keberadaan disk (local vs public)
            foreach ($otherFiles as $file) {
                if (Storage::disk('local')->exists($file)) {
                    $privateFiles[] = $file;
                } elseif (Storage::disk('public')->exists($file)) {
                    $publicFiles[] = $file;
                }
            }

            // Hapus berkas sekaligus menggunakan batch delete
            if (!empty($publicFiles)) {
                Storage::disk('public')->delete($publicFiles);
            }
            if (!empty($privateFiles)) {
                Storage::disk('local')->delete($privateFiles);
            }

            // 2. Hapus data relasi dan pegawai dengan query langsung (bulk database delete)
            User::whereIn('pegawai_id', $ids)->delete();
            RiwayatPendidikan::whereIn('pegawai_id', $ids)->delete();
            RiwayatJabatan::whereIn('pegawai_id', $ids)->delete();
            RiwayatPangkat::whereIn('pegawai_id', $ids)->delete();
            RiwayatDiklat::whereIn('pegawai_id', $ids)->delete();
            \App\Models\MutasiPegawai::whereIn('pegawai_id', $ids)->delete();

            $count = Pegawai::whereIn('id', $ids)->delete();

            // 3. Reset cache sekali saja di akhir
            \Illuminate\Support\Facades\Cache::flush();

            return $count;
        });
    }

    public function getStatistics(): array
    {
        return $this->pegawaiRepository->getStatistics();
    }

    public function syncPegawaiFromHistories(Pegawai $pegawai): void
    {
        // 1. Sinkronisasi Jabatan Terakhir
        $activeJabatan = $pegawai->riwayatJabatan()
            ->whereIn('status', ['aktif', 'Aktif'])
            ->first();
        if (!$activeJabatan) {
            $activeJabatan = $pegawai->riwayatJabatan()
                ->orderBy('tmt_jabatan', 'desc')
                ->first();
        }

        if ($activeJabatan) {
            $pegawai->jabatan_id = $activeJabatan->jabatan_id;
            $pegawai->unit_kerja_id = $activeJabatan->unit_kerja_id;
            if ($activeJabatan->status === 'aktif' || $activeJabatan->status === 'Aktif') {
                $pegawai->file_sk_pertama = $activeJabatan->file_sk;
            }
        }

        // 2. Sinkronisasi Pangkat Terakhir
        $activePangkat = $pegawai->riwayatPangkat()
            ->whereIn('status', ['aktif', 'Aktif'])
            ->first();
        if (!$activePangkat) {
            $activePangkat = $pegawai->riwayatPangkat()
                ->orderBy('tmt', 'desc')
                ->first();
        }

        if ($activePangkat) {
            $pegawai->golongan_id = $activePangkat->golongan_id;
            $pegawai->tmt_pangkat_terakhir = $activePangkat->tmt;
            if ($activePangkat->status === 'aktif' || $activePangkat->status === 'Aktif') {
                $pegawai->file_sk_pangkat_terakhir = $activePangkat->file_sk;
            }
        }

        // 3. Sinkronisasi Pendidikan Terakhir (Kualifikasi Tertinggi)
        $jenjangOrder = [
            'SD' => 1,
            'SMP' => 2,
            'SMA' => 3,
            'D3' => 4,
            'S1' => 5,
            'S2' => 6,
            'S3' => 7,
            'PROFESOR' => 8,
            'PROF' => 8,
        ];
        
        $highestPendidikan = $pegawai->riwayatPendidikan()
            ->get()
            ->sortByDesc(function ($item) use ($jenjangOrder) {
                $key = strtoupper($item->jenjang ?? '');
                return $jenjangOrder[$key] ?? 0;
            })
            ->first();

        if ($highestPendidikan) {
            $pegawai->pendidikan = $highestPendidikan->jenjang;
        }

        $pegawai->save();
    }
}