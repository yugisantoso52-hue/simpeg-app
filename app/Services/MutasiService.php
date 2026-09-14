namespace App\Services;

use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\UnitKerja;
use App\Helpers\TanggalHelper;
use Illuminate\Support\Facades\DB;
use Exception;

class MutasiService
{
    public function prosesMutasiPegawai(array $data)
    {
        // Gunakan Transaction: Jika salah satu gagal, semua dibatalkan (mencegah data korup)
        return DB::transaction(function () use ($data) {
            
            // 1. Update data pada tabel induk (Pegawai)
            $pegawai = Pegawai::findOrFail($data['pegawai_id']);
            $pegawai->update([
                'unit_kerja_id' => $data['unit_kerja_id'],
                'jabatan_id'    => $data['jabatan_id'],
            ]);

            // 2. Catat ke dalam tabel Riwayat Jabatan
            $riwayat = RiwayatJabatan::create([
                'pegawai_id'    => $data['pegawai_id'],
                'jabatan_id'    => $data['jabatan_id'],
                'unit_kerja_id' => $data['unit_kerja_id'],
                'nomor_sk'      => $data['nomor_sk'],
                'tanggal_sk'    => $data['tanggal_sk'],
                'tmt'           => $data['tmt_mutasi'],
                'status'        => 'Aktif'
            ]);

            // Contoh pemanfaatan TanggalHelper (jika diperlukan logika tambahan)
            // $masaKerja = TanggalHelper::hitungMasaKerja($data['tmt_mutasi']);

            return $riwayat;
        });
    }
}