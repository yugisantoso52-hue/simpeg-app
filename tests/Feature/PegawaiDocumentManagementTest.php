<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PegawaiDocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Pegawai $regularPegawai;
    protected UnitKerja $unitKerja;
    protected Jabatan $jabatan;
    protected Golongan $golongan;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->unitKerja = UnitKerja::firstOrCreate(['kode_unit' => 'FKP-01'], ['nama_unit' => 'Bagian Kepegawaian FKp']);
        $this->jabatan = Jabatan::firstOrCreate(['nama_jabatan' => 'Dosen Keperawatan'], ['jenis_jabatan' => 'Fungsional', 'unit_kerja_id' => $this->unitKerja->id]);
        $this->golongan = Golongan::firstOrCreate(['nama_golongan' => 'III/a'], ['nama_pangkat' => 'Penata Muda']);

        $this->adminUser = User::factory()->create([
            'role_id'              => $adminRole->id,
            'must_change_password' => false,
        ]);

        $this->regularPegawai = Pegawai::create([
            'nip'             => '199001012022031001',
            'nama'            => 'Dr. Siti Rahma',
            'email'           => 'siti.rahma@fkp.unri.ac.id',
            'jenis_pegawai'   => 'Dosen',
            'status_asn'      => 'PNS',
            'status_pegawai'  => 'Aktif',
            'unit_kerja_id'   => $this->unitKerja->id,
            'jabatan_id'      => $this->jabatan->id,
            'golongan_id'     => $this->golongan->id,
        ]);

        $this->regularUser = User::factory()->create([
            'pegawai_id'           => $this->regularPegawai->id,
            'role_id'              => $pegawaiRole->id,
            'email'                => 'siti.rahma@fkp.unri.ac.id',
            'must_change_password' => false,
        ]);
    }

    /**
     * 1. Test penyimpanan dokumen baru ke storage & DB dengan struktur direktori hierarkis
     */
    public function test_can_upload_documents_on_create_pegawai_and_store_in_storage_and_db(): void
    {
        $fotoFile = UploadedFile::fake()->image('profil.jpg', 300, 400);
        $skPertamaFile = UploadedFile::fake()->create('sk_cpns.pdf', 500, 'application/pdf');
        $skPangkatFile = UploadedFile::fake()->create('sk_pangkat.pdf', 600, 'application/pdf');
        $karpegFile = UploadedFile::fake()->create('karpeg.pdf', 400, 'application/pdf');
        $ijazahFile = UploadedFile::fake()->create('ijazah_s1.pdf', 700, 'application/pdf');

        $nip = '199505052024011002';
        $nama = 'Ahmad Fauzi, S.Kep';

        $payload = [
            'nip'                  => $nip,
            'nama'                 => $nama,
            'email'                => 'ahmad.fauzi@fkp.unri.ac.id',
            'jenis_pegawai'        => 'Dosen',
            'status_asn'           => 'ASN',
            'unit_kerja_id'        => $this->unitKerja->id,
            'jabatan_id'           => $this->jabatan->id,
            'golongan_id'          => $this->golongan->id,
            'foto'                 => $fotoFile,
            'file_sk_pertama'      => $skPertamaFile,
            'file_sk_pangkat_terakhir' => $skPangkatFile,
            'file_karpeg'          => $karpegFile,
            'riwayat_pendidikan'   => [
                0 => [
                    'jenjang'     => 'S1',
                    'institusi'   => 'Universitas Riau',
                    'tahun_lulus' => 2018,
                    'ijazah'      => $ijazahFile,
                ]
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('pegawai.store'), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('pegawai.index'));

        $pegawai = Pegawai::where('nip', $nip)->first();
        $this->assertNotNull($pegawai);

        // Foto tersimpan di disk public
        $this->assertNotNull($pegawai->foto);
        $this->assertTrue(Storage::disk('public')->exists($pegawai->foto));

        // SK Pertama tersimpan di disk local dengan penamaan hierarkis sesuai NIP
        $this->assertNotNull($pegawai->file_sk_pertama);
        $this->assertTrue(Storage::disk('local')->exists($pegawai->file_sk_pertama));
        $this->assertStringContainsString($nip, $pegawai->file_sk_pertama);

        // SK Pangkat & Karpeg tersimpan
        $this->assertNotNull($pegawai->file_sk_pangkat_terakhir);
        $this->assertTrue(Storage::disk('local')->exists($pegawai->file_sk_pangkat_terakhir));
        $this->assertNotNull($pegawai->file_karpeg);
        $this->assertTrue(Storage::disk('local')->exists($pegawai->file_karpeg));

        // Ijazah pada riwayat pendidikan tersimpan
        $pendidikan = $pegawai->riwayatPendidikan()->first();
        $this->assertNotNull($pendidikan);
        $this->assertNotNull($pendidikan->ijazah);
        $this->assertTrue(Storage::disk('local')->exists($pendidikan->ijazah));

        // Memastikan view profil menampilkan tautan dokumen dengan benar
        $showResponse = $this->actingAs($this->adminUser)->get(route('pegawai.show', $pegawai->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee(route('document.preview', ['path' => $pegawai->file_sk_pertama]));
        $showResponse->assertSee(route('document.preview', ['path' => $pendidikan->ijazah]));
    }

    /**
     * 2. Test preview dokumen inline dan download dokumen via query ?download=1
     */
    public function test_can_preview_and_download_uploaded_documents_via_document_preview_route(): void
    {
        // Simpan file dummy untuk pegawai reguler
        $dummyPdfPath = 'DOSEN/PNS/199001012022031001 - Dr. Siti Rahma/03_SK_PENGANGKATAN/SK_CPNS_199001012022031001.pdf';
        Storage::disk('local')->put($dummyPdfPath, '%PDF-1.4 dummy pdf content for testing');

        $this->regularPegawai->update([
            'file_sk_pertama' => $dummyPdfPath,
        ]);

        // A. Akses Preview oleh Admin (Status 200, Content-Type: application/pdf, Disposition: inline)
        $adminResponse = $this->actingAs($this->adminUser)->get(route('document.preview', ['path' => $dummyPdfPath]));
        $adminResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $adminResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $adminResponse->headers->get('Content-Disposition'));

        // B. Akses Preview oleh Pemilik Dokumen (Pegawai bersangkutan)
        $ownerResponse = $this->actingAs($this->regularUser)->get(route('document.preview', ['path' => $dummyPdfPath]));
        $ownerResponse->assertStatus(200);

        // C. Akses Download dengan query ?download=1 (Disposition: attachment)
        $downloadResponse = $this->actingAs($this->regularUser)->get(route('document.preview', ['path' => $dummyPdfPath, 'download' => 1]));
        $downloadResponse->assertStatus(200);
        $this->assertStringContainsString('attachment', $downloadResponse->headers->get('Content-Disposition'));

        // D. Proteksi IDOR: Pegawai asing yang tidak berhak ditolak (Status 403)
        $strangerPegawai = Pegawai::create([
            'nip'            => '199101012023011009',
            'nama'           => 'Pegawai Asing Lain',
            'email'          => 'stranger@fkp.unri.ac.id',
            'status_pegawai' => 'Aktif',
        ]);
        $strangerUser = User::factory()->create([
            'pegawai_id'           => $strangerPegawai->id,
            'role_id'              => Role::where('name', 'pegawai')->value('id'),
            'email'                => 'stranger@fkp.unri.ac.id',
            'must_change_password' => false,
        ]);

        $unauthorizedResponse = $this->actingAs($strangerUser)->get(route('document.preview', ['path' => $dummyPdfPath]));
        $unauthorizedResponse->assertStatus(403);
    }

    /**
     * 3. Test pembaruan berkas (replace/update): Berkas baru menggantikan berkas lama & berkas fisik lama dihapus
     */
    public function test_can_replace_and_update_documents_and_delete_old_physical_files(): void
    {
        // Setup file lama
        $oldPath = 'DOSEN/PNS/199001012022031001 - Dr. Siti Rahma/03_SK_PENGANGKATAN/SK_LAMA.pdf';
        Storage::disk('local')->put($oldPath, 'OLD FILE CONTENT');

        $this->regularPegawai->update([
            'file_sk_pertama' => $oldPath,
        ]);
        $this->assertTrue(Storage::disk('local')->exists($oldPath));

        // Upload file pengganti baru
        $newFile = UploadedFile::fake()->create('SK_BARU_2026.pdf', 600, 'application/pdf');

        $updatePayload = [
            'nip'             => $this->regularPegawai->nip,
            'nama'            => $this->regularPegawai->nama,
            'email'           => $this->regularPegawai->email,
            'file_sk_pertama' => $newFile,
        ];

        $updateResponse = $this->actingAs($this->adminUser)->put(route('pegawai.update', $this->regularPegawai->id), $updatePayload);
        $updateResponse->assertSessionHasNoErrors();

        $this->regularPegawai->refresh();

        // Path di database telah berganti
        $this->assertNotEquals($oldPath, $this->regularPegawai->file_sk_pertama);
        $this->assertTrue(Storage::disk('local')->exists($this->regularPegawai->file_sk_pertama));

        // Berkas fisik lama telah dibersihkan dari disk storage
        $this->assertFalse(Storage::disk('local')->exists($oldPath));

        // Jika form edit disimpan kembali TANPA mengunggah berkas baru, berkas saat ini tidak hilang
        $currentPath = $this->regularPegawai->file_sk_pertama;
        $retainResponse = $this->actingAs($this->adminUser)->put(route('pegawai.update', $this->regularPegawai->id), [
            'nip'  => $this->regularPegawai->nip,
            'nama' => $this->regularPegawai->nama,
        ]);
        $retainResponse->assertSessionHasNoErrors();

        $this->regularPegawai->refresh();
        $this->assertEquals($currentPath, $this->regularPegawai->file_sk_pertama);
        $this->assertTrue(Storage::disk('local')->exists($currentPath));
    }

    /**
     * 4. Test penanganan anggun saat berkas fisik belum diunggah/hilang (Graceful 404 tanpa fatal error)
     */
    public function test_graceful_handling_when_document_file_is_missing(): void
    {
        $nonExistentPath = 'DOSEN/PNS/199001012022031001 - Dr. Siti Rahma/03_SK_PENGANGKATAN/FILE_HILANG.pdf';
        $this->regularPegawai->update([
            'file_sk_pertama' => $nonExistentPath,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('document.preview', ['path' => $nonExistentPath]));
        $response->assertStatus(404);
        $response->assertSee('Berkas Scan Belum Diunggah');

        // Untuk request berformat JSON
        $jsonResponse = $this->actingAs($this->adminUser)->getJson(route('document.preview', ['path' => $nonExistentPath]));
        $jsonResponse->assertStatus(404);
        $jsonResponse->assertJsonFragment(['success' => false]);
    }

    /**
     * 5. Test link preview pada halaman Edit Profil: link preview muncul saat file valid dan tidak muncul saat null / '-'
     */
    public function test_edit_profile_renders_document_preview_links_correctly_and_ignores_dash(): void
    {
        // Kondisi awal: file_sk_pertama adalah '-' (legacy placeholder) dan file_karpeg adalah file valid
        $karpegPath = 'DOSEN/PNS/199001012022031001 - Dr. Siti Rahma/01_IDENTITAS/karpeg.pdf';
        Storage::disk('local')->put($karpegPath, '%PDF-1.4 dummy karpeg');

        $this->regularPegawai->update([
            'file_sk_pertama' => '-',
            'file_karpeg'     => $karpegPath,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('pegawai.edit', $this->regularPegawai->id));
        $response->assertStatus(200);

        // Preview untuk karpeg harus ada
        $response->assertSee(route('document.preview', ['path' => $karpegPath]));
        // Preview untuk '-' TIDAK boleh dirender
        $response->assertDontSee(route('document.preview', ['path' => '-']));
    }

    /**
     * 6. Test keterhubungan berkas lampiran Edit Profil ke modul KP, KGB, dan Satyalancana
     */
    public function test_attachment_files_in_profile_connect_to_kp_kgb_and_satyalancana(): void
    {
        $skPangkatPath = 'DOSEN/PNS/199001012022031001 - Dr. Siti Rahma/04_SK_PANGKAT/SK_PANGKAT.pdf';
        $karpegPath = 'DOSEN/PNS/199001012022031001 - Dr. Siti Rahma/01_IDENTITAS/KARPEG.pdf';
        $skKgbPath = 'DOSEN/PNS/199001012022031001 - Dr. Siti Rahma/05_SK_KGB/SK_KGB.pdf';

        Storage::disk('local')->put($skPangkatPath, '%PDF-1.4 dummy sk pangkat');
        Storage::disk('local')->put($karpegPath, '%PDF-1.4 dummy karpeg');
        Storage::disk('local')->put($skKgbPath, '%PDF-1.4 dummy kgb');

        $this->regularPegawai->update([
            'file_sk_pangkat_terakhir' => $skPangkatPath,
            'file_karpeg'              => $karpegPath,
            'file_sk_kgb_terakhir'     => $skKgbPath,
            'tmt_pangkat'              => now()->subYears(4)->format('Y-m-d'),
            'tmt_kgb'                  => now()->subYears(2)->format('Y-m-d'),
        ]);

        // 1. Cek Kenaikan Pangkat (KP)
        $evaluasiKp = $this->regularPegawai->evaluasiSyaratKp();
        $this->assertIsArray($evaluasiKp);
        $this->assertArrayHasKey('sk_pangkat', $evaluasiKp);
        $this->assertTrue($evaluasiKp['sk_pangkat']['status']);
        $this->assertEquals(route('document.preview', ['path' => $skPangkatPath]), $evaluasiKp['sk_pangkat']['file_url']);
        $this->assertTrue($evaluasiKp['karpeg']['status']);
        $this->assertEquals(route('document.preview', ['path' => $karpegPath]), $evaluasiKp['karpeg']['file_url']);

        // 2. Cek KGB via KelayakanKarirService
        $karirService = app(\App\Services\KelayakanKarirService::class);
        $evaluasiKgb = $karirService->evaluasiKgb($this->regularPegawai);
        $this->assertArrayHasKey('syarat_berkas', $evaluasiKgb);
        $kgbItems = collect($evaluasiKgb['syarat_berkas']['items'])->keyBy('field');
        $this->assertTrue($kgbItems['file_sk_kgb_terakhir']['status']);
        $this->assertEquals(route('document.preview', ['path' => $skKgbPath]), $kgbItems['file_sk_kgb_terakhir']['file_url']);

        // 3. Cek Satyalancana via KelayakanKarirService
        $evaluasiSatya = $karirService->evaluasiSatyalancana($this->regularPegawai);
        $this->assertArrayHasKey('syarat_berkas', $evaluasiSatya);
        $satyaItems = collect($evaluasiSatya['syarat_berkas']['items'])->keyBy('field');
        $this->assertTrue($satyaItems['file_sk_pangkat_terakhir']['status']);
        $this->assertEquals(route('document.preview', ['path' => $skPangkatPath]), $satyaItems['file_sk_pangkat_terakhir']['file_url']);
    }

    /**
     * 7. Test validasi unggahan berkas: penolakan ekstensi terlarang dan ukuran melebihi batas (10MB) dengan pesan error informatif
     */
    public function test_rejects_invalid_file_extension_and_oversized_file_with_informative_messages(): void
    {
        // File ekstensi terlarang (.exe atau .txt)
        $invalidExtensionFile = UploadedFile::fake()->create('virus_script.exe', 100, 'application/x-msdownload');
        // File melebihi batas 10MB (11MB = 11264 KB)
        $oversizedFile = UploadedFile::fake()->create('buku_besar.pdf', 11500, 'application/pdf');

        $payload = [
            'nip'                      => $this->regularPegawai->nip,
            'nama'                     => $this->regularPegawai->nama,
            'file_sk_pertama'          => $invalidExtensionFile,
            'file_sk_pangkat_terakhir' => $oversizedFile,
        ];

        $response = $this->actingAs($this->adminUser)->put(route('pegawai.update', $this->regularPegawai->id), $payload);

        $response->assertSessionHasErrors([
            'file_sk_pertama',
            'file_sk_pangkat_terakhir',
        ]);

        $errors = session('errors')->getBag('default');
        // Pesan error ramah Bahasa Indonesia
        $this->assertStringContainsString('Format Berkas SK Pertama harus berupa dokumen PDF atau gambar', $errors->first('file_sk_pertama'));
        $this->assertStringContainsString('Ukuran Berkas SK Pangkat Terakhir tidak boleh melebihi 10 MB', $errors->first('file_sk_pangkat_terakhir'));
    }
}
