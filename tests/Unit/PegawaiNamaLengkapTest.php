<?php

namespace Tests\Unit;

use App\Models\Pegawai;
use Tests\TestCase;

class PegawaiNamaLengkapTest extends TestCase
{
    /**
     * Test formatting gelar depan with automatic dot (.)
     */
    public function test_gelar_depan_automatic_dot(): void
    {
        // Kasus 1: Gelar depan tanpa titik (Ns)
        $p1 = new Pegawai([
            'nama'           => 'Cia Novita',
            'gelar_depan'    => 'Ns',
            'gelar_belakang' => 'S.Kep',
        ]);
        $this->assertEquals('Ns. Cia Novita, S.Kep', $p1->nama_lengkap);

        // Kasus 2: Gelar depan sudah bertitik (Ns.)
        $p2 = new Pegawai([
            'nama'           => 'Cia Novita',
            'gelar_depan'    => 'Ns.',
            'gelar_belakang' => 'S.Kep',
        ]);
        $this->assertEquals('Ns. Cia Novita, S.Kep', $p2->nama_lengkap);

        // Kasus 3: Gelar Dr
        $p3 = new Pegawai([
            'nama'           => 'Rahmad Hidayat',
            'gelar_depan'    => 'Dr',
            'gelar_belakang' => 'M.Kom',
        ]);
        $this->assertEquals('Dr. Rahmad Hidayat, M.Kom', $p3->nama_lengkap);

        // Kasus 4: Tanpa gelar depan
        $p4 = new Pegawai([
            'nama'           => 'Siti Rahma',
            'gelar_depan'    => null,
            'gelar_belakang' => 'S.Kep',
        ]);
        $this->assertEquals('Siti Rahma, S.Kep', $p4->nama_lengkap);

        // Kasus 5: Tanpa gelar sama sekali
        $p5 = new Pegawai([
            'nama'           => 'Budi Santoso',
            'gelar_depan'    => null,
            'gelar_belakang' => null,
        ]);
        $this->assertEquals('Budi Santoso', $p5->nama_lengkap);
    }
}
