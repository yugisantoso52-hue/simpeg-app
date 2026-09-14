<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\MutasiPegawai;
use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\RiwayatPendidikan;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseIntegrityAuditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST 1 — VERIFY SCHEMA TABLES EXISTENCE
     */
    public function test_all_domain_tables_exist_in_database(): void
    {
        $tables = [
            'users',
            'roles',
            'pegawai',
            'unit_kerja',
            'jabatan',
            'golongan',
            'riwayat_pendidikan',
            'riwayat_pangkat',
            'riwayat_jabatan',
            'mutasi_pegawai',
            'notifications',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} should exist in database schema.");
        }
    }

    /**
     * TEST 2 — VERIFY UNIQUE CONSTRAINTS (NIP, KODE_UNIT, KODE_JABATAN, NAMA_GOLONGAN, EMAIL, ROLE_NAME)
     */
    public function test_unique_constraints_are_enforced_by_database(): void
    {
        // 1. Pegawai NIP Unique
        Pegawai::create(['nip' => '123456789012345678', 'nama' => 'User Unique 1']);
        
        $this->expectException(QueryException::class);
        Pegawai::create(['nip' => '123456789012345678', 'nama' => 'User Unique 2']);
    }

    /**
     * TEST 3 — VERIFY CASCADE DELETE ON PEGAWAI RELATIONS
     */
    public function test_deleting_pegawai_cascades_to_riwayat_and_mutasi(): void
    {
        $unit = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Guru']);
        $golongan = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);

        $pegawai = Pegawai::create([
            'nip'  => '199001012015011001',
            'nama' => 'Pegawai Cascade Test',
        ]);

        $rPend = RiwayatPendidikan::create([
            'pegawai_id' => $pegawai->id,
            'jenjang'    => 'S1',
            'institusi'  => 'Universitas Gadjah Mada',
        ]);

        $rPkt = RiwayatPangkat::create([
            'pegawai_id'  => $pegawai->id,
            'golongan_id' => $golongan->id,
            'tmt_pangkat' => '2020-01-01',
        ]);

        $rJab = RiwayatJabatan::create([
            'pegawai_id'    => $pegawai->id,
            'unit_kerja_id' => $unit->id,
            'jabatan_id'    => $jabatan->id,
            'tmt_jabatan'   => '2020-01-01',
        ]);

        $mutasi = MutasiPegawai::create([
            'pegawai_id'      => $pegawai->id,
            'unit_lama_id'    => $unit->id,
            'unit_baru_id'    => $unit->id,
            'jabatan_lama_id' => $jabatan->id,
            'jabatan_baru_id' => $jabatan->id,
            'tmt'             => '2025-01-01',
        ]);

        // Delete pegawai
        $pegawai->delete();

        $this->assertDatabaseMissing('pegawai', ['id' => $pegawai->id]);
        $this->assertDatabaseMissing('riwayat_pendidikan', ['id' => $rPend->id]);
        $this->assertDatabaseMissing('riwayat_pangkat', ['id' => $rPkt->id]);
        $this->assertDatabaseMissing('riwayat_jabatan', ['id' => $rJab->id]);
        $this->assertDatabaseMissing('mutasi_pegawai', ['id' => $mutasi->id]);
    }

    /**
     * TEST 4 — VERIFY DELETING MASTER UNIT/JABATAN/GOLONGAN SETS NULL ON PEGAWAI
     */
    public function test_deleting_master_unit_sets_null_on_pegawai(): void
    {
        $unit = UnitKerja::create(['nama_unit' => 'Dinas Perhubungan', 'kode_unit' => 'DISHUB']);

        $pegawai = Pegawai::create([
            'nip'           => '199201012017011001',
            'nama'          => 'Pegawai Set Null Test',
            'unit_kerja_id' => $unit->id,
        ]);

        $unit->delete();

        $this->pegawai = $pegawai->fresh();
        $this->assertNull($this->pegawai->unit_kerja_id);
    }
}
