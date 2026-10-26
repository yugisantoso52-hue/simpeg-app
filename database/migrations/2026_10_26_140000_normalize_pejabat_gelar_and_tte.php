<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pegawais')) {
            // Normalisasi penulisan gelar Wakil Dekan II (Dr. Safri, M.Kep, Sp.Kep.M.B)
            $safri = DB::table('pegawais')->where('nip', '198509092014041001')->first();
            if ($safri) {
                $updates = [];
                if (!empty($safri->gelar_belakang)) {
                    $updates['gelar_belakang'] = str_replace(
                        ['M.kep', 'Sp.kep', 'Sp. Kep.M.B'],
                        ['M.Kep', 'Sp.Kep', 'Sp.Kep.M.B'],
                        $safri->gelar_belakang
                    );
                } else {
                    $updates['gelar_belakang'] = 'M.Kep, Sp.Kep.M.B';
                }

                if (!empty($safri->nama)) {
                    $updates['nama'] = str_replace(
                        ['M.kep', 'Sp.kep', 'Sp. Kep.M.B'],
                        ['M.Kep', 'Sp.Kep', 'Sp.Kep.M.B'],
                        $safri->nama
                    );
                }

                if (!empty($updates)) {
                    DB::table('pegawais')->where('id', $safri->id)->update($updates);
                }
            }
        }

        if (Schema::hasTable('layanan_surats')) {
            // Normalisasi penandatangan_nama pada layanan_surats yang sudah terbit
            DB::table('layanan_surats')
                ->where('penandatangan_nama', 'like', '%M.kep%')
                ->get()
                ->each(function ($row) {
                    DB::table('layanan_surats')
                        ->where('id', $row->id)
                        ->update([
                            'penandatangan_nama' => str_replace(
                                ['M.kep', 'Sp.kep', 'Sp. Kep.M.B'],
                                ['M.Kep', 'Sp.Kep', 'Sp.Kep.M.B'],
                                $row->penandatangan_nama
                            ),
                        ]);
                });
        }

        if (Schema::hasTable('pengajuan_cutis') && Schema::hasTable('pegawais')) {
            $dolli = DB::table('pegawais')->where('nip', '197605212014092001')->first();
            // Pastikan pengajuan cuti yang sudah melewati tahap paraf memiliki paraf_kapokja_at
            DB::table('pengajuan_cutis')
                ->whereNull('paraf_kapokja_at')
                ->where(function ($q) {
                    $q->whereNotNull('paraf_kabag_at')
                      ->orWhereIn('status', ['Disetujui', 'Disetujui Atasan (Menunggu PYBMC)', 'Disetujui Atasan (Menunggu Keputusan Akhir)']);
                })
                ->get()
                ->each(function ($cuti) use ($dolli) {
                    DB::table('pengajuan_cutis')
                        ->where('id', $cuti->id)
                        ->update([
                            'paraf_kapokja_at' => $cuti->verifikasi_admin_at ?? ($cuti->paraf_kabag_at ?? $cuti->updated_at),
                            'paraf_kapokja_by' => $dolli?->id ?? $cuti->paraf_kapokja_by,
                        ]);
                });
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
