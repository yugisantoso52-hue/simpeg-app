<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Pegawai;
use App\Models\UnitKerja;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $unitKeu = UnitKerja::where('kode_unit', 'UK-012')->orWhere('nama_unit', 'like', '%Keuangan dan Kepe%')->first();
        if ($unitKeu) {
            Pegawai::where('nama', 'like', '%Dolli%')->update(['unit_kerja_id' => $unitKeu->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
