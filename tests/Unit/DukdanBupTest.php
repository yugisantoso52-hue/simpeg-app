<?php

namespace Tests\Unit;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use Carbon\Carbon;
use Tests\TestCase;

class DukdanBupTest extends TestCase
{
    /**
     * Test BUP 70 tahun untuk Guru Besar / Profesor
     */
    public function test_bup_profesor_or_guru_besar_is_70(): void
    {
        $jabatanProf = new Jabatan(['nama_jabatan' => 'Profesor']);
        $pegawai1 = new Pegawai(['jenis_pegawai' => 'Dosen']);
        $pegawai1->setRelation('jabatan', $jabatanProf);

        $this->assertEquals(70, $pegawai1->bup);

        $jabatanGb = new Jabatan(['nama_jabatan' => 'Guru Besar']);
        $pegawai2 = new Pegawai(['jenis_pegawai' => 'Dosen']);
        $pegawai2->setRelation('jabatan', $jabatanGb);

        $this->assertEquals(70, $pegawai2->bup);
    }

    /**
     * Test BUP 65 tahun untuk Dosen, Lektor, Asisten Ahli
     */
    public function test_bup_dosen_lektor_asisten_ahli_is_65(): void
    {
        $jabatanLektor = new Jabatan(['nama_jabatan' => 'Lektor Kepala']);
        $pegawai1 = new Pegawai(['jenis_pegawai' => 'Dosen']);
        $pegawai1->setRelation('jabatan', $jabatanLektor);

        $this->assertEquals(65, $pegawai1->bup);

        $jabatanAa = new Jabatan(['nama_jabatan' => 'Asisten Ahli']);
        $pegawai2 = new Pegawai(['jenis_pegawai' => 'Dosen']);
        $pegawai2->setRelation('jabatan', $jabatanAa);

        $this->assertEquals(65, $pegawai2->bup);

        // Jika hanya jenis_pegawai = Dosen tanpa relasi jabatan khusus
        $pegawai3 = new Pegawai(['jenis_pegawai' => 'Dosen']);
        $this->assertEquals(65, $pegawai3->bup);
    }

    /**
     * Test BUP 60 tahun untuk Pimpinan Tinggi (JPT), Kabag, Ahli Madya
     */
    public function test_bup_pimpinan_tinggi_kabag_is_60(): void
    {
        $jabatanKabag = new Jabatan(['nama_jabatan' => 'Kepala Bagian Umum']);
        $pegawai1 = new Pegawai(['jenis_pegawai' => 'Tendik']);
        $pegawai1->setRelation('jabatan', $jabatanKabag);

        $this->assertEquals(60, $pegawai1->bup);

        $pegawai2 = new Pegawai([
            'jenis_pegawai' => 'PNS',
            'jenis_jabatan' => 'Pimpinan Tinggi Pratama',
        ]);
        $this->assertEquals(60, $pegawai2->bup);
    }

    /**
     * Test BUP default 58 tahun untuk Tenaga Kependidikan / Pelaksana
     */
    public function test_bup_tendik_pelaksana_is_58(): void
    {
        $jabatanPelaksana = new Jabatan(['nama_jabatan' => 'Pengadministrasi Umum']);
        $pegawai = new Pegawai(['jenis_pegawai' => 'Tendik']);
        $pegawai->setRelation('jabatan', $jabatanPelaksana);

        $this->assertEquals(58, $pegawai->bup);
    }

    /**
     * Test jika batas_usia_pensiun diisi manual pada database maka mengabaikan kalkulasi otomatis
     */
    public function test_explicit_batas_usia_pensiun_overrides_automatic(): void
    {
        $pegawai = new Pegawai([
            'jenis_pegawai' => 'Tendik',
            'batas_usia_pensiun' => 62,
        ]);

        $this->assertEquals(62, $pegawai->bup);
    }

    /**
     * Test kalkulasi tanggal pensiun resmi faktual (tanggal_pensiun_calculated)
     */
    public function test_tanggal_pensiun_calculated(): void
    {
        $jabatanLektor = new Jabatan(['nama_jabatan' => 'Lektor']);
        $pegawai = new Pegawai([
            'tanggal_lahir' => '1970-05-15',
            'jenis_pegawai' => 'Dosen',
        ]);
        $pegawai->setRelation('jabatan', $jabatanLektor);

        // BUP 65 tahun dari 1970-05-15 => 2035-05-15
        $this->assertNotNull($pegawai->tanggal_pensiun_calculated);
        $this->assertEquals('2035-05-15', $pegawai->tanggal_pensiun_calculated->format('Y-m-d'));

        // Jika tanggal_pensiun di-set manual
        $pegawaiExplicit = new Pegawai([
            'tanggal_lahir' => '1970-05-15',
            'tanggal_pensiun' => '2032-12-31',
        ]);
        $this->assertEquals('2032-12-31', $pegawaiExplicit->tanggal_pensiun_calculated->format('Y-m-d'));
    }

    /**
     * Test pengurutan DUK (Daftar Urut Kepangkatan) standar BKN
     */
    public function test_duk_collection_sorting(): void
    {
        $gol4e = new Golongan(['id' => 1, 'nama_golongan' => 'IV/e', 'urutan' => 17]);
        $gol4a = new Golongan(['id' => 2, 'nama_golongan' => 'IV/a', 'urutan' => 13]);
        $gol3d = new Golongan(['id' => 3, 'nama_golongan' => 'III/d', 'urutan' => 12]);

        // Pegawai 1: Golongan 3/d (urutan 12)
        $p1 = new Pegawai([
            'nama' => 'Pegawai Tiga D',
            'tmt_pangkat_terakhir' => Carbon::parse('2015-04-01'),
            'tanggal_lahir' => Carbon::parse('1980-01-01'),
        ]);
        $p1->setRelation('golongan', $gol3d);

        // Pegawai 2: Golongan 4/e (urutan 17) -> Paling Tinggi
        $p2 = new Pegawai([
            'nama' => 'Pegawai Empat E',
            'tmt_pangkat_terakhir' => Carbon::parse('2020-04-01'),
            'tanggal_lahir' => Carbon::parse('1965-05-01'),
        ]);
        $p2->setRelation('golongan', $gol4e);

        // Pegawai 3: Golongan 4/a (urutan 13), TMT 2018 (lebih lama dari p4)
        $p3 = new Pegawai([
            'nama' => 'Pegawai Empat A Senior',
            'tmt_pangkat_terakhir' => Carbon::parse('2018-04-01'),
            'tanggal_lahir' => Carbon::parse('1975-01-01'),
        ]);
        $p3->setRelation('golongan', $gol4a);

        // Pegawai 4: Golongan 4/a (urutan 13), TMT 2021 (lebih baru dari p3)
        $p4 = new Pegawai([
            'nama' => 'Pegawai Empat A Junior',
            'tmt_pangkat_terakhir' => Carbon::parse('2021-04-01'),
            'tanggal_lahir' => Carbon::parse('1974-01-01'),
        ]);
        $p4->setRelation('golongan', $gol4a);

        // Pegawai 5: Golongan 4/a (urutan 13), TMT 2018 (sama dengan p3), tetapi usia lebih tua
        $p5 = new Pegawai([
            'nama' => 'Pegawai Empat A Tertua',
            'tmt_pangkat_terakhir' => Carbon::parse('2018-04-01'),
            'tanggal_lahir' => Carbon::parse('1970-01-01'), // Lebih tua dari p3 (1975)
        ]);
        $p5->setRelation('golongan', $gol4a);

        $rawList = collect([$p1, $p3, $p2, $p4, $p5]);
        $sorted = Pegawai::sortDukCollection($rawList);

        // Urutan yang diharapkan:
        // 1. p2 (Gol 4/e, urutan 17)
        // 2. p5 (Gol 4/a, TMT 2018, Lahir 1970)
        // 3. p3 (Gol 4/a, TMT 2018, Lahir 1975)
        // 4. p4 (Gol 4/a, TMT 2021)
        // 5. p1 (Gol 3/d, urutan 12)
        $this->assertEquals('Pegawai Empat E', $sorted[0]->nama);
        $this->assertEquals('Pegawai Empat A Tertua', $sorted[1]->nama);
        $this->assertEquals('Pegawai Empat A Senior', $sorted[2]->nama);
        $this->assertEquals('Pegawai Empat A Junior', $sorted[3]->nama);
        $this->assertEquals('Pegawai Tiga D', $sorted[4]->nama);
    }
}
