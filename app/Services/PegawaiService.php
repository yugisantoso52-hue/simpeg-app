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

            $pendidikanInput = $data['pendidikan'] ?? null;
            $diklatInput     = $data['diklat'] ?? null;

            if (is_array($pendidikanInput)) {
                $data['pendidikan'] = $pendidikanInput['jenjang'] ?? $pendidikanInput['institusi'] ?? null;
            } elseif (is_string($pendidikanInput)) {
                $data['pendidikan'] = $pendidikanInput;
            } else {
                unset($data['pendidikan']);
            }
            unset($data['diklat']);

            // 1. Simpan data pegawai utama
            $pegawai = $this->pegawaiRepository->create($data);

            // 2. OTOMATISASI: Buat Akun User Login untuk Pegawai Baru (NIP Tanpa Spasi)
            $rolePegawai = Role::where('name', 'pegawai')->first();
            if ($rolePegawai) {
                $emailLogin = $pegawai->email;
                if (empty($emailLogin)) {
                    $nipClean = str_replace(' ', '', trim($pegawai->nip ?? ''));
                    $identifier = !empty($nipClean) ? $nipClean : 'pegawai_' . $pegawai->id;
                    $emailLogin = $identifier . '@simpeg.test';
                }

                User::create([
                    'name'       => $pegawai->nama,
                    'email'      => $emailLogin,
                    'password'   => Hash::make('password123'), // Password default awal pegawai
                    'role_id'    => $rolePegawai->id,
                    'pegawai_id' => $pegawai->id,
                ]);
            }

            // 3. Simpan Riwayat Pendidikan awal
            if (is_array($pendidikanInput) && (!empty($pendidikanInput['jenjang']) || !empty($pendidikanInput['institusi']))) {
                $fileIjazah = null;
                if (!empty($files['pendidikan_ijazah'])) {
                    $fileIjazah = $files['pendidikan_ijazah']->store('ijazah', 'local');
                }

                $pegawai->riwayatPendidikan()->create([
                    'jenjang'     => $pendidikanInput['jenjang'] ?? 'S1',
                    'institusi'   => $pendidikanInput['institusi'] ?? 'Universitas / Sekolah',
                    'fakultas'    => $pendidikanInput['fakultas'] ?? null,
                    'jurusan'     => $pendidikanInput['jurusan'] ?? null,
                    'tahun_lulus' => !empty($pendidikanInput['tahun_lulus']) ? (int)$pendidikanInput['tahun_lulus'] : null,
                    'ijazah'      => $fileIjazah,
                ]);
            } elseif (!empty($pegawai->pendidikan)) {
                $pegawai->riwayatPendidikan()->create([
                    'jenjang'   => $pegawai->pendidikan,
                    'institusi' => 'Universitas / Sekolah',
                ]);
            }

            // 4. Simpan Riwayat Diklat awal
            if (!empty($diklatInput['nama_diklat'])) {
                $fileSertifikat = null;
                if (!empty($files['diklat_sertifikat'])) {
                    $fileSertifikat = $files['diklat_sertifikat']->store('sertifikat_diklat', 'local');
                }

                $tanggalMulai   = !empty($diklatInput['tanggal_mulai']) ? $diklatInput['tanggal_mulai'] : date('Y-m-d');
                $tanggalSelesai = !empty($diklatInput['tanggal_selesai']) ? $diklatInput['tanggal_selesai'] : $tanggalMulai;
                $tahunDiklat    = Carbon::parse($tanggalMulai)->year;

                $pegawai->riwayatDiklat()->create([
                    'nama_diklat'      => $diklatInput['nama_diklat'],
                    'jenis_diklat'     => $diklatInput['jenis_diklat'] ?? null,
                    'penyelenggara'    => $diklatInput['penyelenggara'] ?? null,
                    'nomor_sertifikat' => $diklatInput['nomor_sertifikat'] ?? null,
                    'tanggal_mulai'    => $tanggalMulai,
                    'tanggal_selesai'  => $tanggalSelesai,
                    'tahun'            => $tahunDiklat,
                    'status'           => $diklatInput['status'] ?? 'Aktif',
                    'keterangan'       => $diklatInput['keterangan'] ?? null,
                    'file_sertifikat'  => $fileSertifikat,
                ]);
            }

            // 5. Buat Riwayat Jabatan Awal
            if ($pegawai->jabatan_id && $pegawai->unit_kerja_id) {
                RiwayatJabatan::create([
                    'pegawai_id'    => $pegawai->id,
                    'jabatan_id'    => $pegawai->jabatan_id,
                    'unit_kerja_id' => $pegawai->unit_kerja_id,
                    'tmt_jabatan'   => $pegawai->tanggal_masuk ?? now(),
                    'nomor_sk'      => $data['nomor_sk_pertama'] ?? null,
                    'tanggal_sk'    => $data['tanggal_sk_pertama'] ?? null,
                    'file_sk'       => $pegawai->file_sk_pertama ?? null,
                    'keterangan'    => 'Riwayat awal saat pegawai dibuat',
                    'status'        => 'Aktif'
                ]);
            }

            // 6. Buat Riwayat Pangkat Awal
            if ($pegawai->golongan_id) {
                RiwayatPangkat::create([
                    'pegawai_id'  => $pegawai->id,
                    'golongan_id' => $pegawai->golongan_id,
                    'tmt'         => $pegawai->tmt_pangkat_terakhir ?? now(),
                    'nomor_sk'    => $data['nomor_sk_pangkat_terakhir'] ?? null,
                    'tanggal_sk'  => $data['tanggal_sk_pangkat_terakhir'] ?? null,
                    'file_sk'     => $pegawai->file_sk_pangkat_terakhir ?? null,
                    'keterangan'  => 'Riwayat awal saat pegawai dibuat',
                    'status'      => 'aktif'
                ]);
            }

            return $pegawai;
        });
    }

    /**
     * Memperbarui data pegawai, berkas SK, dan menyinkronkan seluruh riwayat terkait
     */
    public function updatePegawai(int|string $id, array $data, array $files = []): Pegawai
    {
        return DB::transaction(function () use ($id, $data, $files) {
            $pegawai = $this->pegawaiRepository->findOrFail($id);

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

            // Ambil data riwayat pendidikan & diklat dari input Edit
            $pendidikanInput = $data['pendidikan'] ?? null;
            $diklatInput     = $data['diklat'] ?? null;

            if (is_array($pendidikanInput)) {
                $data['pendidikan'] = $pendidikanInput['jenjang'] ?? $pendidikanInput['institusi'] ?? null;
            } elseif (is_string($pendidikanInput)) {
                $data['pendidikan'] = $pendidikanInput;
            } else {
                unset($data['pendidikan']);
            }
            unset($data['diklat']);

            // Update tabel pegawai utama
            $pegawaiUpdated = $this->pegawaiRepository->update($pegawai->id, $data);

            // Synchronize User table name and email if user exists
            if ($pegawaiUser = User::where('pegawai_id', $pegawaiUpdated->id)->first()) {
                $pegawaiUser->update([
                    'name'  => $pegawaiUpdated->nama,
                    'email' => $pegawaiUpdated->email ?: $pegawaiUser->email,
                ]);
            }

            // 1. UPDATE / SIMPAN RIWAYAT PENDIDIKAN SAAT EDIT
            if (is_array($pendidikanInput) && (!empty($pendidikanInput['jenjang']) || !empty($pendidikanInput['institusi']))) {
                $fileIjazah = null;
                if (!empty($files['pendidikan_ijazah'])) {
                    $fileIjazah = $files['pendidikan_ijazah']->store('ijazah', 'local');
                }

                $pendidikanPayload = [
                    'jenjang'     => $pendidikanInput['jenjang'] ?? 'S1',
                    'institusi'   => $pendidikanInput['institusi'] ?? 'Universitas / Sekolah',
                    'fakultas'    => $pendidikanInput['fakultas'] ?? null,
                    'jurusan'     => $pendidikanInput['jurusan'] ?? null,
                    'tahun_lulus' => !empty($pendidikanInput['tahun_lulus']) ? (int)$pendidikanInput['tahun_lulus'] : null,
                ];

                if ($fileIjazah) {
                    $pendidikanPayload['ijazah'] = $fileIjazah;
                }

                $riwayatPendidikanExist = $pegawai->riwayatPendidikan()->first();
                if ($riwayatPendidikanExist) {
                    $riwayatPendidikanExist->update($pendidikanPayload);
                } else {
                    $pegawai->riwayatPendidikan()->create($pendidikanPayload);
                }
            } elseif (!empty($pegawaiUpdated->pendidikan)) {
                $riwayatPendidikanExist = $pegawai->riwayatPendidikan()->first();
                if ($riwayatPendidikanExist) {
                    $riwayatPendidikanExist->update(['jenjang' => $pegawaiUpdated->pendidikan]);
                } else {
                    $pegawai->riwayatPendidikan()->create([
                        'jenjang'   => $pegawaiUpdated->pendidikan,
                        'institusi' => 'Universitas / Sekolah',
                    ]);
                }
            }

            // 2. UPDATE / SIMPAN RIWAYAT DIKLAT SAAT EDIT
            if (!empty($diklatInput['nama_diklat'])) {
                $fileSertifikat = null;
                if (!empty($files['diklat_sertifikat'])) {
                    $fileSertifikat = $files['diklat_sertifikat']->store('sertifikat_diklat', 'local');
                }

                $tanggalMulai   = !empty($diklatInput['tanggal_mulai']) ? $diklatInput['tanggal_mulai'] : date('Y-m-d');
                $tanggalSelesai = !empty($diklatInput['tanggal_selesai']) ? $diklatInput['tanggal_selesai'] : $tanggalMulai;
                $tahunDiklat    = Carbon::parse($tanggalMulai)->year;

                $diklatPayload = [
                    'nama_diklat'      => $diklatInput['nama_diklat'],
                    'jenis_diklat'     => $diklatInput['jenis_diklat'] ?? null,
                    'penyelenggara'    => $diklatInput['penyelenggara'] ?? null,
                    'nomor_sertifikat' => $diklatInput['nomor_sertifikat'] ?? null,
                    'tanggal_mulai'    => $tanggalMulai,
                    'tanggal_selesai'  => $tanggalSelesai,
                    'tahun'            => $tahunDiklat,
                    'status'           => $diklatInput['status'] ?? 'Aktif',
                    'keterangan'       => $diklatInput['keterangan'] ?? null,
                ];

                if ($fileSertifikat) {
                    $diklatPayload['file_sertifikat'] = $fileSertifikat;
                }

                $riwayatDiklatExist = $pegawai->riwayatDiklat()->first();
                if ($riwayatDiklatExist) {
                    $riwayatDiklatExist->update($diklatPayload);
                } else {
                    $pegawai->riwayatDiklat()->create($diklatPayload);
                }
            }

            // 3. SINKRONKAN RIWAYAT JABATAN SAAT EDIT PEGAWAI
            if ($pegawaiUpdated->jabatan_id && $pegawaiUpdated->unit_kerja_id) {
                $riwayatJabatanExist = RiwayatJabatan::where('pegawai_id', $pegawaiUpdated->id)
                    ->whereIn('status', ['aktif', 'Aktif'])
                    ->first();
                if ($riwayatJabatanExist) {
                    $riwayatJabatanExist->update([
                        'jabatan_id'    => $pegawaiUpdated->jabatan_id,
                        'unit_kerja_id' => $pegawaiUpdated->unit_kerja_id,
                        'nomor_sk'      => $data['nomor_sk_pertama'] ?? $riwayatJabatanExist->nomor_sk,
                        'tanggal_sk'    => $data['tanggal_sk_pertama'] ?? $riwayatJabatanExist->tanggal_sk,
                        'file_sk'       => $pegawaiUpdated->file_sk_pertama ?? $riwayatJabatanExist->file_sk,
                        'status'        => 'Aktif',
                    ]);
                } else {
                    RiwayatJabatan::create([
                        'pegawai_id'    => $pegawaiUpdated->id,
                        'jabatan_id'    => $pegawaiUpdated->jabatan_id,
                        'unit_kerja_id' => $pegawaiUpdated->unit_kerja_id,
                        'tmt_jabatan'   => $pegawaiUpdated->tanggal_masuk ?? now(),
                        'nomor_sk'      => $data['nomor_sk_pertama'] ?? null,
                        'tanggal_sk'    => $data['tanggal_sk_pertama'] ?? null,
                        'file_sk'       => $pegawaiUpdated->file_sk_pertama ?? null,
                        'keterangan'    => 'Riwayat jabatan disinkronkan saat edit pegawai',
                        'status'        => 'Aktif'
                    ]);
                }
            }

            // 4. SINKRONKAN RIWAYAT PANGKAT SAAT EDIT PEGAWAI
            if ($pegawaiUpdated->golongan_id) {
                $riwayatPangkatExist = RiwayatPangkat::where('pegawai_id', $pegawaiUpdated->id)
                    ->whereIn('status', ['aktif', 'Aktif'])
                    ->first();
                if ($riwayatPangkatExist) {
                    $riwayatPangkatExist->update([
                        'golongan_id' => $pegawaiUpdated->golongan_id,
                        'tmt'         => $pegawaiUpdated->tmt_pangkat_terakhir ?? now(),
                        'nomor_sk'    => $data['nomor_sk_pangkat_terakhir'] ?? $riwayatPangkatExist->nomor_sk,
                        'tanggal_sk'  => $data['tanggal_sk_pangkat_terakhir'] ?? $riwayatPangkatExist->tanggal_sk,
                        'file_sk'     => $pegawaiUpdated->file_sk_pangkat_terakhir ?? $riwayatPangkatExist->file_sk,
                        'status'      => 'aktif',
                    ]);
                } else {
                    RiwayatPangkat::create([
                        'pegawai_id'  => $pegawaiUpdated->id,
                        'golongan_id' => $pegawaiUpdated->golongan_id,
                        'tmt'         => $pegawaiUpdated->tmt_pangkat_terakhir ?? now(),
                        'nomor_sk'    => $data['nomor_sk_pangkat_terakhir'] ?? null,
                        'tanggal_sk'  => $data['tanggal_sk_pangkat_terakhir'] ?? null,
                        'file_sk'     => $pegawaiUpdated->file_sk_pangkat_terakhir ?? null,
                        'keterangan'  => 'Riwayat pangkat disinkronkan saat edit pegawai',
                        'status'      => 'aktif',
                    ]);
                }
            }

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

    public function getStatistics(): array
    {
        return $this->pegawaiRepository->getStatistics();
    }
}