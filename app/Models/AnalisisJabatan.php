<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalisisJabatan extends Model
{
    use HasFactory;

    protected $table = 'analisis_jabatan';

    protected $fillable = [
        'jabatan_id',
        'unit_kerja_id',
        'kode_anjab',
        'ikhtisar_jabatan',
        'kualifikasi_pendidikan',
        'kualifikasi_pelatihan',
        'kualifikasi_pengalaman',
        'bahan_kerja',
        'perangkat_kerja',
        'tanggung_jawab',
        'wewenang',
        'korelasi_jabatan',
        'kondisi_lingkungan',
        'resiko_bahaya',
        'syarat_keterampilan',
        'syarat_bakat',
        'syarat_temperamen',
        'syarat_minat',
        'syarat_upaya_fisik',
        'kondisi_fisik',
        'prestasi_diharapkan',
        'kelas_jabatan',
        'status',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function uraianTugas()
    {
        return $this->hasMany(AbkUraianTugas::class, 'analisis_jabatan_id')->orderBy('urutan');
    }

    /**
     * Total Waktu Beban Kerja dalam Menit (Norma Waktu * Volume)
     */
    public function getTotalWaktuBebanMenitAttribute(): float
    {
        return (float) $this->uraianTugas->sum('waktu_beban_menit');
    }

    /**
     * Total Waktu Beban Kerja dalam Jam
     */
    public function getTotalJamBebanAttribute(): float
    {
        return round($this->total_waktu_beban_menit / 60, 2);
    }

    /**
     * Kebutuhan Pegawai berdasarkan Standar WKE 1.250 Jam (75.000 Menit/Tahun)
     */
    public function getKebutuhanPegawaiAttribute(): float
    {
        return round($this->total_waktu_beban_menit / 75000, 2);
    }

    /**
     * Pembulatan Formasi BKN
     */
    public function getFormasiPembulatanAttribute(): int
    {
        $kebutuhan = $this->kebutuhan_pegawai;
        return (int) max(1, round($kebutuhan));
    }

    /**
     * Jumlah Pegawai Riil Saat Ini (Bezetting)
     */
    public function getBezettingAttribute(): int
    {
        $query = Pegawai::where('jabatan_id', $this->jabatan_id)
            ->where('status_pegawai', 'Aktif');

        if ($this->unit_kerja_id) {
            $query->where(function($q) {
                $q->where('unit_kerja_id', $this->unit_kerja_id)
                  ->orWhereNull('unit_kerja_id');
            });
        }

        return $query->count();
    }

    /**
     * Selisih Formasi: Bezetting - Formasi Pembulatan
     */
    public function getSelisihFormasiAttribute(): int
    {
        return $this->bezetting - $this->formasi_pembulatan;
    }

    /**
     * Status Formasi Pegawai
     */
    public function getStatusFormasiAttribute(): string
    {
        $selisih = $this->selisih_formasi;

        if ($selisih < 0) {
            return 'Kurang Pegawai (' . abs($selisih) . ')';
        }

        if ($selisih > 0) {
            return 'Kelebihan Pegawai (+' . $selisih . ')';
        }

        return 'Ideal / Cukup';
    }

    /**
     * Warna Indikator Status Formasi
     */
    public function getStatusColorAttribute(): string
    {
        $selisih = $this->selisih_formasi;

        if ($selisih < 0) {
            return 'rose'; // Merah / Kurang
        }

        if ($selisih > 0) {
            return 'amber'; // Kuning / Lebih
        }

        return 'emerald'; // Hijau / Ideal
    }

    /**
     * Hitung bobot hierarki jabatan struktural & fungsional
     * Acuan: Bagan Struktur Organisasi Fakultas Keperawatan UNRI & PermenPAN-RB No. 1/2020
     */
    public static function hierarchyOrderWeight(?string $namaJabatan, $kelasJabatan = 0): int
    {
        $nama = strtolower(trim($namaJabatan ?? ''));
        $kelas = (int) $kelasJabatan;

        // 1. Pimpinan Tertinggi Fakultas (Dekan)
        if ($nama === 'dekan') {
            return 1000;
        }

        // 2. Wakil Dekan (Cek III dan II sebelum I untuk kehati-hatian substring)
        if (str_contains($nama, 'wakil dekan iii') || str_contains($nama, 'wakil dekan 3') || (str_contains($nama, 'wakil dekan') && str_contains($nama, 'kemahasiswaan'))) {
            return 970;
        }
        if (str_contains($nama, 'wakil dekan ii') || str_contains($nama, 'wakil dekan 2') || (str_contains($nama, 'wakil dekan') && str_contains($nama, 'keuangan'))) {
            return 980;
        }
        if (str_contains($nama, 'wakil dekan i') || str_contains($nama, 'wakil dekan 1') || (str_contains($nama, 'wakil dekan') && str_contains($nama, 'akademik'))) {
            return 990;
        }

        // 3. Kepala Bagian Umum (Pimpinan Administrasi Fakultas)
        if (str_contains($nama, 'kepala bagian') || str_contains($nama, 'kabag umum')) {
            return 950;
        }

        // 4. Jurusan Keperawatan (Kajur & Sekjur Utama Fakultas)
        if ($nama === 'ketua jurusan (kajur)' || $nama === 'ketua jurusan keperawatan') {
            return 920;
        }
        if ($nama === 'sekretaris jurusan' || $nama === 'sekretaris jurusan keperawatan') {
            return 910;
        }

        // 5. Jurusan Preklinik & Jurusan Klinik
        if (str_contains($nama, 'ketua jurusan preklinik')) {
            return 880;
        }
        if (str_contains($nama, 'sekretaris jurusan preklinik')) {
            return 870;
        }
        if (str_contains($nama, 'ketua jurusan klinik')) {
            return 860;
        }
        if (str_contains($nama, 'sekretaris jurusan klinik')) {
            return 850;
        }

        // 6. Koordinator Program Studi (S1, S2, S3, Profesi Ners)
        if (str_contains($nama, 'prodi s1')) {
            return 800;
        }
        if (str_contains($nama, 'prodi s2')) {
            return 790;
        }
        if (str_contains($nama, 'prodi s3')) {
            return 780;
        }
        if (str_contains($nama, 'prodi ners')) {
            return 770;
        }

        // 7. Penjaminan Mutu (SPMF & GPM)
        if (str_contains($nama, 'spmf') || str_contains($nama, 'penjamin mutu')) {
            return 750;
        }
        if (str_contains($nama, 'gpm')) {
            return 740;
        }

        // 8. Kepala Pokja / Sub-Koordinator
        if (str_contains($nama, 'ka pokja akademik') || str_contains($nama, 'ketua pokja akademik')) {
            return 700;
        }
        if (str_contains($nama, 'ka pokja keu') || str_contains($nama, 'ketua pokja keu')) {
            return 690;
        }
        if (str_contains($nama, 'ka pokja umum') || str_contains($nama, 'ketua pokja umum')) {
            return 680;
        }

        // 9. Kepala Laboratorium & Unit-Unit Fungsional / Khusus
        if (str_contains($nama, 'kepala upt laboratorium') || str_contains($nama, 'kepala laboratorium')) {
            return 650;
        }
        if (str_contains($nama, 'komite etik')) {
            return 640;
        }
        if (str_contains($nama, 'cbt')) {
            return 630;
        }
        if (str_contains($nama, 'kerjasama')) {
            return 620;
        }
        if (str_contains($nama, 'penelitian') || str_contains($nama, 'pengabmasy')) {
            return 610;
        }
        if (str_contains($nama, 'nedu')) {
            return 600;
        }
        if (str_contains($nama, 'konseling') || str_contains($nama, 'bimbingan')) {
            return 590;
        }

        // 10. Jabatan Fungsional Dosen
        if (str_contains($nama, 'dosen') || str_contains($nama, 'lektor') || str_contains($nama, 'asisten ahli') || str_contains($nama, 'profesor') || str_contains($nama, 'guru besar')) {
            return 500;
        }

        // 11. Jabatan Fungsional PLP / Laboran
        if (str_contains($nama, 'plp') || str_contains($nama, 'pranata laboratorium') || str_contains($nama, 'laboran')) {
            return 400;
        }

        // 12. Pelaksana / Staf Administrasi
        if (str_contains($nama, 'staff pokja akademik') || str_contains($nama, 'staf pokja akademik')) {
            return 300;
        }
        if (str_contains($nama, 'staff pokja keu') || str_contains($nama, 'staf pokja keu')) {
            return 290;
        }
        if (str_contains($nama, 'staff') || str_contains($nama, 'staf') || str_contains($nama, 'pelaksana') || str_contains($nama, 'pengadministrasi')) {
            return 280;
        }

        return $kelas > 0 ? ($kelas * 10) : 100;
    }

    /**
     * Bobot hierarki model instance untuk keperluan sorting koleksi
     */
    public function getHierarchyOrderAttribute(): int
    {
        return self::hierarchyOrderWeight(
            $this->jabatan?->nama_jabatan,
            $this->kelas_jabatan ?? $this->jabatan?->kelas_jabatan ?? 0
        );
    }
}
