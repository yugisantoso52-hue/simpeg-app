<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pegawai') || !Schema::hasTable('users')) {
            return;
        }

        // 1. Pastikan Bakhtiar, S.Sos., M.Si (NIP 197507122005011002) terhubung ke jabatan Kepala Bagian Umum
        $jabatanKabagId = DB::table('jabatan')
            ->where('nama_jabatan', 'like', '%Kepala Bagian Umum%')
            ->orWhere('nama_jabatan', 'like', '%Kabag Umum%')
            ->value('id');

        $pegawaiKabag = Pegawai::where('nip', '197507122005011002')
            ->orWhere('nama', 'like', '%Bakhtiar%')
            ->first();

        $userKabag = null;
        if ($pegawaiKabag) {
            $updateKabag = [];
            if ($jabatanKabagId) {
                $updateKabag['jabatan_id'] = $jabatanKabagId;
            }
            if ($pegawaiKabag->gelar_belakang === 'S,Sos., M.Si' || empty($pegawaiKabag->gelar_belakang)) {
                $updateKabag['gelar_belakang'] = 'S.Sos., M.Si';
            }
            if (!empty($updateKabag)) {
                DB::table('pegawai')->where('id', $pegawaiKabag->id)->update($updateKabag);
            }

            $userKabag = User::where('pegawai_id', $pegawaiKabag->id)->first();
            if (!$userKabag) {
                $rolePimpinan = DB::table('roles')->where('name', 'pimpinan')->value('id')
                    ?? DB::table('roles')->where('name', 'pegawai')->value('id')
                    ?? 2;

                $existingByEmail = User::where('email', '197507122005011002@simpeg.test')
                    ->orWhere('name', '197507122005011002')
                    ->first();

                if ($existingByEmail) {
                    $existingByEmail->update(['pegawai_id' => $pegawaiKabag->id]);
                    $userKabag = $existingByEmail;
                } else {
                    $userKabag = User::create([
                        'name'                 => $pegawaiKabag->nama,
                        'email'                => '197507122005011002@simpeg.test',
                        'password'             => Hash::make('password'),
                        'role_id'              => $rolePimpinan,
                        'pegawai_id'           => $pegawaiKabag->id,
                        'must_change_password' => false,
                    ]);
                }
            }
        }

        // 2. Pastikan Dolli Vita Zenitha Harning Arivina, SE (NIP 197605212014092001) terhubung ke jabatan Ka Pokja Keu-Kepeg
        $jabatanPokjaKeuId = DB::table('jabatan')
            ->where('nama_jabatan', 'like', '%Pokja Keuangan%')
            ->orWhere('nama_jabatan', 'like', '%Ka Pokja Keu%')
            ->value('id');

        $pegawaiPokja = Pegawai::where('nip', '197605212014092001')
            ->orWhere('nama', 'like', '%Dolli Vita%')
            ->first();

        if ($pegawaiPokja && $jabatanPokjaKeuId) {
            DB::table('pegawai')->where('id', $pegawaiPokja->id)->update([
                'jabatan_id' => $jabatanPokjaKeuId,
            ]);
        }

        // 3. Bersihkan atasan_cuti_id pada pegawai yang menunjuk ke Ka Pokja (karena Ka Pokja hanya Paraf Koordinasi, bukan Atasan Langsung Cuti)
        if (Schema::hasColumn('pegawai', 'atasan_cuti_id')) {
            $pokjaPegawaiIds = Pegawai::whereHas('jabatan', function ($q) {
                $q->where('nama_jabatan', 'like', '%Pokja%');
            })->pluck('id')->toArray();

            if (!empty($pokjaPegawaiIds)) {
                DB::table('pegawai')
                    ->whereIn('atasan_cuti_id', $pokjaPegawaiIds)
                    ->update(['atasan_cuti_id' => $pegawaiKabag?->id]);
            }
        }

        // 4. Perbaiki data pengajuan_cuti yang atasan_langsung_id-nya terisi ke Ka Pokja (seperti Dolli Vita)
        if (Schema::hasTable('pengajuan_cuti')) {
            $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
            $cutiList = PengajuanCuti::with(['pegawai.jabatan', 'atasanLangsung.pegawai.jabatan'])->get();

            foreach ($cutiList as $cuti) {
                if (!$cuti->pegawai) {
                    continue;
                }

                $expectedAtasanPegawai = $hierarchyService->getAtasanLangsungCuti($cuti->pegawai);
                $expectedAtasanUser = $expectedAtasanPegawai
                    ? User::where('pegawai_id', $expectedAtasanPegawai->id)->first()
                    : $userKabag;

                $currentAtasanUser = $cuti->atasanLangsung;
                $currentAtasanJabatan = strtoupper(trim((string)($currentAtasanUser?->pegawai?->jabatan?->nama_jabatan ?? '')));
                $isCurrentAtasanPokja = str_contains($currentAtasanJabatan, 'POKJA')
                    || ($currentAtasanUser && $currentAtasanUser->isKaPokjaKepeg())
                    || ($pegawaiPokja && $currentAtasanUser && (int)$currentAtasanUser->pegawai_id === (int)$pegawaiPokja->id);

                $updates = [];
                if ($expectedAtasanUser && (!$cuti->atasan_langsung_id || $isCurrentAtasanPokja)) {
                    $updates['atasan_langsung_id'] = $expectedAtasanUser->id;
                }

                // Jika Kabag Umum sudah melakukan paraf pada kolom paraf_kabag_at, gunakan timestamp tersebut untuk TTE Pertimbangan Atasan Langsung
                if (!empty($cuti->paraf_kabag_at) && $isCurrentAtasanPokja) {
                    $updates['pertimbangan_atasan_at'] = $cuti->paraf_kabag_at;
                }

                if (!empty($updates)) {
                    DB::table('pengajuan_cuti')->where('id', $cuti->id)->update($updates);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe non-destructive migration
    }
};
