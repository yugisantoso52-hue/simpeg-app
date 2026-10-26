<?php

namespace App\Actions\Pegawai;

use App\Models\Pegawai;
use App\Models\RiwayatStrSip;
use App\Repositories\Contracts\PegawaiRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UpsertDataDiriPegawaiAction
{
    protected PegawaiRepositoryInterface $pegawaiRepository;

    public function __construct(PegawaiRepositoryInterface $pegawaiRepository)
    {
        $this->pegawaiRepository = $pegawaiRepository;
    }

    /**
     * Membuat atau memperbarui data induk profil pegawai
     */
    public function execute(array $data, ?Pegawai $pegawai = null): Pegawai
    {
        // 1. Sanitasi NIP (hanya angka)
        if (!empty($data['nip'])) {
            $data['nip'] = preg_replace('/[^0-9]/', '', (string)$data['nip']);
        }

        // 2. Kalkulasi KGB, KP, dan Satyalancana (pertahankan nilai lama jika partial update)
        $tmtKgb     = array_key_exists('tmt_kgb_terakhir', $data) ? $data['tmt_kgb_terakhir'] : $pegawai?->tmt_kgb_terakhir;
        $tmtPangkat = array_key_exists('tmt_pangkat_terakhir', $data) ? $data['tmt_pangkat_terakhir'] : $pegawai?->tmt_pangkat_terakhir;
        $tmtPertama = array_key_exists('tmt_sk_pertama', $data) ? $data['tmt_sk_pertama'] : $pegawai?->tmt_sk_pertama;

        $data['kgb_berikutnya'] = $this->generateKGB($tmtKgb);
        $data['kp_berikutnya']  = $this->generateKP($tmtPangkat);

        $satyalancana = $this->generateSatyalancana($tmtPertama);
        $data['satyalancana_terakhir']   = $satyalancana['terakhir'] ?? $pegawai?->satyalancana_terakhir;
        $data['satyalancana_berikutnya'] = $satyalancana['berikutnya'] ?? $pegawai?->satyalancana_berikutnya;

        // 3. Default values
        $data['jumlah_anak']    = $data['jumlah_anak'] ?? ($pegawai?->jumlah_anak ?? 0);
        $data['status_pegawai'] = $data['status_pegawai'] ?? ($pegawai?->status_pegawai ?? 'Aktif');

        $filteredData = $this->filterPegawaiColumns($data);

        // 4. Update atau Create
        if ($pegawai) {
            $pegawaiUpdated = $this->pegawaiRepository->update($pegawai->id, $filteredData);

            // Audit log perubahan data
            $changes = $pegawaiUpdated->getChanges();
            if (!empty($changes)) {
                $user = auth()->user();
                $modifier = $user ? "User ID {$user->id} ({$user->name})" : "System/Console";
                Log::info(sprintf(
                    "Audit Pegawai: Pegawai ID %s (%s) diubah oleh %s.",
                    $pegawaiUpdated->id,
                    $pegawaiUpdated->nama,
                    $modifier
                ), ['changes' => $changes]);
            }
        } else {
            $pegawaiUpdated = $this->pegawaiRepository->create($filteredData);
        }

        // 5. Sinkronisasi Data STR & SIP ke Riwayat Terkait jika diisi
        $this->syncStrSip($pegawaiUpdated);

        return $pegawaiUpdated;
    }

    /**
     * Sinkronisasikan nomor STR dan SIP ke tabel riwayat_str_sip
     */
    protected function syncStrSip(Pegawai $pegawai): void
    {
        if (!empty($pegawai->nomor_str)) {
            $existingStr = RiwayatStrSip::where('pegawai_id', $pegawai->id)->where('jenis_dokumen', 'STR')->first();
            RiwayatStrSip::updateOrCreate(
                ['pegawai_id' => $pegawai->id, 'jenis_dokumen' => 'STR'],
                [
                    'nomor_registrasi'  => $pegawai->nomor_str,
                    'nama_dokumen'      => $existingStr?->nama_dokumen ?: 'Surat Tanda Registrasi (STR)',
                    'instansi_penerbit' => $existingStr?->instansi_penerbit ?: 'KTKI / Kemenkes RI',
                    'tanggal_berakhir'  => $pegawai->tanggal_kadaluarsa_str,
                    'status'            => 'aktif',
                    'file_dokumen'      => $pegawai->file_str ?: $existingStr?->file_dokumen,
                    'keterangan'        => 'Tersinkronisasi otomatis dari profil pegawai',
                ]
            );
        }

        if (!empty($pegawai->nomor_sip)) {
            $existingSip = RiwayatStrSip::where('pegawai_id', $pegawai->id)->where('jenis_dokumen', 'SIP')->first();
            RiwayatStrSip::updateOrCreate(
                ['pegawai_id' => $pegawai->id, 'jenis_dokumen' => 'SIP'],
                [
                    'nomor_registrasi'  => $pegawai->nomor_sip,
                    'nama_dokumen'      => $existingSip?->nama_dokumen ?: 'Surat Izin Praktik (SIP)',
                    'instansi_penerbit' => $existingSip?->instansi_penerbit ?: 'DPMPTSP / Dinas Kesehatan',
                    'tanggal_berakhir'  => $pegawai->tanggal_kadaluarsa_sip,
                    'status'            => 'aktif',
                    'file_dokumen'      => $pegawai->file_sip ?: $existingSip?->file_dokumen,
                    'keterangan'        => 'Tersinkronisasi otomatis dari profil pegawai',
                ]
            );
        }
    }

    /**
     * Generate Kenaikan Gaji Berkala (KGB) - Berkelanjutan 2 Tahun
     */
    public function generateKGB($tmt): ?string
    {
        return !blank($tmt) ? Carbon::parse($tmt)->addYears(2)->format('Y-m-d') : null;
    }

    /**
     * Generate Kenaikan Pangkat (KP) - Berkelanjutan 4 Tahun
     */
    public function generateKP($tmt): ?string
    {
        return !blank($tmt) ? Carbon::parse($tmt)->addYears(4)->format('Y-m-d') : null;
    }

    /**
     * Hitung Prediksi Satyalancana Berdasarkan TMT SK Pertama
     */
    public function generateSatyalancana($tmtPertama): array
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

    /**
     * Filter payload ke kolom yang benar-benar ada di tabel database
     */
    public function filterPegawaiColumns(array $data): array
    {
        $columns = $this->getPegawaiColumns();

        if (!empty($columns)) {
            return array_filter($data, function ($key) use ($columns) {
                return in_array($key, $columns, true);
            }, ARRAY_FILTER_USE_KEY);
        }

        return $data;
    }

    protected function getPegawaiColumns(): array
    {
        static $pegawaiColumns = null;
        if ($pegawaiColumns === null) {
            try {
                $pegawaiColumns = Schema::getColumnListing('pegawai');
            } catch (\Throwable $e) {
                $pegawaiColumns = [];
            }
        }
        return $pegawaiColumns;
    }
}
