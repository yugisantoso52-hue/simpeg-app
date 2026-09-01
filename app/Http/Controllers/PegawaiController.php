<?php

namespace App\Http\Controllers;

use App\Exports\DukExport;
use App\Exports\PegawaiTemplateExport;
use App\Imports\PegawaiImport;
use App\Http\Requests\Pegawai\StorePegawaiRequest;
use App\Http\Requests\Pegawai\UpdatePegawaiRequest;
use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\JenisJabatan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Services\PegawaiService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Pdf;

class PegawaiController extends Controller
{
    use AuthorizesRequests;

    private PegawaiService $pegawaiService;

    public function __construct(PegawaiService $pegawaiService)
    {
        $this->pegawaiService = $pegawaiService;
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $pegawai = $this->pegawaiService->search($search);
        $statistics = $this->pegawaiService->getStatistics();

        return view('pegawai.index', compact(
            'pegawai',
            'statistics',
            'search'
        ));
    }

    public function duk(Request $request)
    {
        $search = $request->get('search');
        
        $dosenQuery = Pegawai::dosen()->with(['golongan', 'unitKerja', 'jabatan', 'riwayatPendidikan', 'riwayatDiklat']);
        $tendikQuery = Pegawai::tendik()->with(['golongan', 'unitKerja', 'jabatan', 'riwayatPendidikan', 'riwayatDiklat']);
        $phlQuery = Pegawai::phl()->with(['golongan', 'unitKerja', 'jabatan', 'riwayatPendidikan', 'riwayatDiklat']);

        if ($search) {
            $filterSearch = function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where('nama', 'like', "%{$search}%")
                       ->orWhere('nip', 'like', "%{$search}%")
                       ->orWhere('nidn_nuptk', 'like', "%{$search}%");
                });
            };
            $dosenQuery->where($filterSearch);
            $tendikQuery->where($filterSearch);
            $phlQuery->where($filterSearch);
        }

        $sortDuk = function($collection) {
            return $collection->sort(function($a, $b) {
                $golA = $a->golongan->urutan ?? $a->golongan_id ?? 0;
                $golB = $b->golongan->urutan ?? $b->golongan_id ?? 0;
                if ($golA !== $golB) return $golB <=> $golA;

                $tmtPangkatA = $a->tmt_pangkat_terakhir ? $a->tmt_pangkat_terakhir->timestamp : 0;
                $tmtPangkatB = $b->tmt_pangkat_terakhir ? $b->tmt_pangkat_terakhir->timestamp : 0;
                if ($tmtPangkatA !== $tmtPangkatB) return $tmtPangkatA <=> $tmtPangkatB;

                $tglLahirA = $a->tanggal_lahir ? $a->tanggal_lahir->timestamp : 0;
                $tglLahirB = $b->tanggal_lahir ? $b->tanggal_lahir->timestamp : 0;
                return $tglLahirA <=> $tglLahirB;
            })->values();
        };

        $dosenList = $sortDuk($dosenQuery->get());
        $tendikList = $sortDuk($tendikQuery->get());
        $phlList = $sortDuk($phlQuery->get());

        $statistics = $this->pegawaiService->getStatistics();

        return view('pegawai.duk', compact('dosenList', 'tendikList', 'phlList', 'statistics', 'search'));
    }

    public function create(Request $request)
    {
        $kategori = strtolower((string)$request->get('kategori', 'all'));

        return view('pegawai.create', [
            'kategori'     => $kategori,
            'unitKerja'    => UnitKerja::orderBy('nama_unit')->get(),
            'jabatan'      => Jabatan::orderBy('nama_jabatan')->get(),
            'golongan'     => Golongan::orderBy('nama_golongan')->get(),
            'jenisJabatan' => JenisJabatan::orderBy('nama_jenis_jabatan')->get(),
        ]);
    }

    public function store(StorePegawaiRequest $request)
    {
        DB::transaction(function () use ($request) {
            $this->pegawaiService->createPegawai(
                $request->validated(),
                $request->allFiles()
            );
        });

        $kategori = strtolower((string)$request->get('kategori', ''));
        $jenisPegawai = strtoupper((string)$request->get('jenis_pegawai', ''));

        if ($kategori === 'dosen' || $jenisPegawai === 'DOSEN') {
            return redirect()
                ->route('kepegawaian.dosen.index')
                ->with('success', 'Data Dosen berhasil ditambahkan.');
        } elseif ($kategori === 'tendik' || in_array($jenisPegawai, ['PNS', 'PPPK'])) {
            return redirect()
                ->route('kepegawaian.tendik.index')
                ->with('success', 'Data Tenaga Kependidikan berhasil ditambahkan.');
        } elseif ($kategori === 'phl' || in_array($jenisPegawai, ['PHL', 'HONORER'])) {
            return redirect()
                ->route('kepegawaian.phl.index')
                ->with('success', 'Data PHL / Kontrak berhasil ditambahkan.');
        }

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    public function show(int $id)
    {
        $pegawai = $this->pegawaiService->find($id);

        // OTORISASI POLICY: Cek izin melihat detail data
        $this->authorize('view', $pegawai);

        return view('pegawai.show', compact('pegawai'));
    }

    public function edit(Request $request, int $id)
    {
        $pegawai = $this->pegawaiService->find($id);

        // OTORISASI POLICY: Cek izin edit data
        $this->authorize('update', $pegawai);

        $defaultKategori = strtolower($pegawai->kategori_kepegawaian ?? 'all');
        $kategori = strtolower((string)$request->get('kategori', $defaultKategori));

        return view('pegawai.edit', [
            'pegawai'      => $pegawai,
            'kategori'     => $kategori,
            'unitKerja'    => UnitKerja::orderBy('nama_unit')->get(),
            'jabatan'      => Jabatan::orderBy('nama_jabatan')->get(),
            'golongan'     => Golongan::orderBy('nama_golongan')->get(),
            'jenisJabatan' => JenisJabatan::orderBy('nama_jenis_jabatan')->get(),
        ]);
    }

    public function update(UpdatePegawaiRequest $request, int $id)
    {
        $pegawai = $this->pegawaiService->find($id);

        // OTORISASI POLICY: Cek izin update data
        $this->authorize('update', $pegawai);

        $this->pegawaiService->updatePegawai(
            $id,
            $request->validated(),
            $request->allFiles()
        );

        // Pengarahan halaman berdasarkan Role
        if (auth()->user()->hasRole('pegawai')) {
            return redirect()
                ->route('pegawai.show', $id)
                ->with('success', 'Data profil pribadi Anda berhasil diperbarui.');
        }

        $kategori = strtolower((string)$request->get('kategori', $pegawai->kategori_kepegawaian ?? ''));
        $jenisPegawai = strtoupper((string)$request->get('jenis_pegawai', $pegawai->jenis_pegawai ?? ''));

        if ($kategori === 'dosen' || $jenisPegawai === 'DOSEN') {
            return redirect()
                ->route('kepegawaian.dosen.index')
                ->with('success', 'Data Dosen berhasil diperbarui.');
        } elseif ($kategori === 'tendik' || in_array($jenisPegawai, ['PNS', 'PPPK'])) {
            return redirect()
                ->route('kepegawaian.tendik.index')
                ->with('success', 'Data Tenaga Kependidikan berhasil diperbarui.');
        } elseif ($kategori === 'phl' || in_array($jenisPegawai, ['PHL', 'HONORER'])) {
            return redirect()
                ->route('kepegawaian.phl.index')
                ->with('success', 'Data PHL / Kontrak berhasil diperbarui.');
        }

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $pegawai = $this->pegawaiService->find($id);

        // OTORISASI POLICY: Cek izin hapus data
        $this->authorize('delete', $pegawai);

        $this->pegawaiService->deletePegawai($id);

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        ini_set('max_execution_time', 300);
        set_time_limit(300);

        $request->validate([
            'pegawai_ids' => 'required|array',
            'pegawai_ids.*' => 'required|integer|exists:pegawai,id',
        ]);

        $ids = $request->input('pegawai_ids');

        foreach ($ids as $id) {
            $pegawai = $this->pegawaiService->find($id);
            $this->authorize('delete', $pegawai);
        }

        $count = $this->pegawaiService->bulkDeletePegawai($ids);

        return redirect()
            ->route('pegawai.index')
            ->with('success', "Berhasil menghapus {$count} data pegawai secara massal.");
    }

    /**
     * Unduh Template Excel Impor Pegawai
     */
    public function downloadTemplate()
    {
        return Excel::download(new PegawaiTemplateExport, 'template_import_pegawai.xlsx');
    }

    /**
     * Proses Impor Masal Data Pegawai dari Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            // Increase timeouts & memory limit for mass import execution
            ini_set('max_execution_time', 300);
            set_time_limit(300);
            ini_set('memory_limit', '512M');

            DB::transaction(function () use ($request) {
                Pegawai::withoutEvents(function () use ($request) {
                    Excel::import(new PegawaiImport, $request->file('file'));
                });
            });
            
            // Clean up cache once at the end
            Cache::flush();

            return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diimpor secara masal!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    public function exportDukPdf(Request $request)
    {
        $search = $request->get('search');
        
        $dosenQuery = Pegawai::dosen()->with(['golongan', 'unitKerja', 'jabatan', 'riwayatPendidikan', 'riwayatDiklat']);
        $tendikQuery = Pegawai::tendik()->with(['golongan', 'unitKerja', 'jabatan', 'riwayatPendidikan', 'riwayatDiklat']);
        $phlQuery = Pegawai::phl()->with(['golongan', 'unitKerja', 'jabatan', 'riwayatPendidikan', 'riwayatDiklat']);

        if ($search) {
            $filterSearch = function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where('nama', 'like', "%{$search}%")
                       ->orWhere('nip', 'like', "%{$search}%")
                       ->orWhere('nidn_nuptk', 'like', "%{$search}%");
                });
            };
            $dosenQuery->where($filterSearch);
            $tendikQuery->where($filterSearch);
            $phlQuery->where($filterSearch);
        }

        $sortDuk = function($collection) {
            return $collection->sort(function($a, $b) {
                $golA = $a->golongan->urutan ?? $a->golongan_id ?? 0;
                $golB = $b->golongan->urutan ?? $b->golongan_id ?? 0;
                if ($golA !== $golB) return $golB <=> $golA;

                $tmtPangkatA = $a->tmt_pangkat_terakhir ? $a->tmt_pangkat_terakhir->timestamp : 0;
                $tmtPangkatB = $b->tmt_pangkat_terakhir ? $b->tmt_pangkat_terakhir->timestamp : 0;
                if ($tmtPangkatA !== $tmtPangkatB) return $tmtPangkatA <=> $tmtPangkatB;

                $tglLahirA = $a->tanggal_lahir ? $a->tanggal_lahir->timestamp : 0;
                $tglLahirB = $b->tanggal_lahir ? $b->tanggal_lahir->timestamp : 0;
                return $tglLahirA <=> $tglLahirB;
            })->values();
        };

        $dosenList = $sortDuk($dosenQuery->get());
        $tendikList = $sortDuk($tendikQuery->get());
        $phlList = $sortDuk($phlQuery->get());

        $pdf = Pdf::loadView('exports.pdf.duk', compact('dosenList', 'tendikList', 'phlList'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('DUK_Pegawai_' . date('Y-m-d') . '.pdf');
    }

    public function exportDukExcel(Request $request)
    {
        $search = $request->get('search');
        return Excel::download(new DukExport($search), 'DUK_Pegawai_' . date('Y-m-d') . '.xlsx');
    }

    public function exportProfilPdf(int $id)
    {
        $pegawai = $this->pegawaiService->find($id);

        // OTORISASI POLICY: Cek izin mengunduh PDF profil
        $this->authorize('view', $pegawai);

        $pdf = Pdf::loadView('exports.pdf.profil_pegawai', compact('pegawai'))
            ->setPaper('a4', 'portrait');

        $namaClean = str_replace([' ', '/', '\\'], '_', $pegawai->nama_lengkap ?? $pegawai->nama);
        return $pdf->download('Profil_Pegawai_' . $namaClean . '_' . date('Ymd') . '.pdf');
    }

    /**
     * Stream / Tampilkan Foto Pegawai secara aman & reliabel tanpa bergantung pada symlink
     */
    public function foto(Pegawai $pegawai)
    {
        if ($pegawai->foto) {
            $cleanPath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $pegawai->foto), DIRECTORY_SEPARATOR);

            // 1. Cek via Storage disk public (kompatibel dengan Storage::fake & real storage)
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                return response()->file(\Illuminate\Support\Facades\Storage::disk('public')->path($cleanPath));
            }

            // 2. Cek via Storage disk local
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($cleanPath)) {
                return response()->file(\Illuminate\Support\Facades\Storage::disk('local')->path($cleanPath));
            }

            // 3. Fallback direct file checks
            $publicPath = storage_path('app/public' . DIRECTORY_SEPARATOR . $cleanPath);
            if (file_exists($publicPath) && is_file($publicPath)) {
                return response()->file($publicPath);
            }

            $privatePath = storage_path('app/private' . DIRECTORY_SEPARATOR . $cleanPath);
            if (file_exists($privatePath) && is_file($privatePath)) {
                return response()->file($privatePath);
            }

            $publicStoragePath = public_path('storage' . DIRECTORY_SEPARATOR . $cleanPath);
            if (file_exists($publicStoragePath) && is_file($publicStoragePath)) {
                return response()->file($publicStoragePath);
            }
        }

        // Fallback jika file fisik tidak ditemukan atau belum ada foto
        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($pegawai->nama_lengkap ?? $pegawai->nama ?? 'User') . '&color=7F9CF5&background=EBF4FF';
        return redirect()->away($avatarUrl);
    }

    /**
     * Tampilan Khusus Tenaga Pendidik (Dosen)
     */
    public function dosen(Request $request)
    {
        $search = $request->get('search');
        $query = Pegawai::dosen()->with(['golongan', 'unitKerja', 'jabatan']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('nidn_nuptk', 'like', "%{$search}%");
            });
        }

        $pegawai = $query->latest('id')->paginate(10)->withQueryString();

        $statistics = [
            'total' => Pegawai::dosen()->count(),
            'aktif' => Pegawai::dosen()->aktif()->count(),
            'pns'   => Pegawai::dosen()->where('status_asn', 'ASN')->count(),
            'tubel' => Pegawai::dosen()->where('status_pegawai', 'Tugas Belajar')->count(),
        ];

        $kategoriTitle = 'Data Tenaga Pendidik / Dosen';
        $kategoriSubtitle = 'Pencatatan data dosen tetap, NIDN, jabatan fungsional akademik, dan kepangkatan';
        $kategori = 'Dosen';
        $badgeColor = 'blue';

        return view('pegawai.kategori', compact('pegawai', 'statistics', 'search', 'kategoriTitle', 'kategoriSubtitle', 'kategori', 'badgeColor'));
    }

    /**
     * Tampilan Khusus Tenaga Kependidikan (Tendik)
     */
    public function tendik(Request $request)
    {
        $search = $request->get('search');
        $query = Pegawai::tendik()->with(['golongan', 'unitKerja', 'jabatan']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $pegawai = $query->latest('id')->paginate(10)->withQueryString();

        $statistics = [
            'total' => Pegawai::tendik()->count(),
            'aktif' => Pegawai::tendik()->aktif()->count(),
            'pns'   => Pegawai::tendik()->where('status_asn', 'ASN')->count(),
            'tubel' => Pegawai::tendik()->where('status_pegawai', 'Tugas Belajar')->count(),
        ];

        $kategoriTitle = 'Data Tenaga Kependidikan (Tendik)';
        $kategoriSubtitle = 'Pencatatan data staf administrasi, laboran, teknisi, dan fungsional umum';
        $kategori = 'Tendik';
        $badgeColor = 'emerald';

        return view('pegawai.kategori', compact('pegawai', 'statistics', 'search', 'kategoriTitle', 'kategoriSubtitle', 'kategori', 'badgeColor'));
    }

    /**
     * Tampilan Khusus Pegawai Harian Lepas (PHL / Kontrak)
     */
    public function phl(Request $request)
    {
        $search = $request->get('search');
        $query = Pegawai::phl()->with(['golongan', 'unitKerja', 'jabatan']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $pegawai = $query->latest('id')->paginate(10)->withQueryString();

        $statistics = [
            'total'   => Pegawai::phl()->count(),
            'aktif'   => Pegawai::phl()->aktif()->count(),
            'non_asn' => Pegawai::phl()->where('status_asn', 'Non ASN')->count(),
            'kontrak' => Pegawai::phl()->whereNotNull('jenis_kontrak')->count(),
        ];

        $kategoriTitle = 'Data Pegawai Harian Lepas (PHL) & Tenaga Kontrak';
        $kategoriSubtitle = 'Pencatatan data pegawai non-ASN, honorer, dan kontrak kerja institusi';
        $kategori = 'PHL';
        $badgeColor = 'amber';

        return view('pegawai.kategori', compact('pegawai', 'statistics', 'search', 'kategoriTitle', 'kategoriSubtitle', 'kategori', 'badgeColor'));
    }

    /**
     * Halaman Fallback / Coming Soon untuk modul yang sedang dikembangkan
     */
    public function comingSoon(Request $request, $module = null)
    {
        $moduleNames = [
            'gaji'          => 'Penggajian & Tunjangan Kinerja (Remunerasi)',
            'evaluasi'      => 'Evaluasi Kinerja & Angka Kredit Otomatis',
            'presensi'      => 'Integrasi Presensi Fingerprint & GPS',
            'arsip-digital' => 'E-Arsip Dokumen Kepegawaian Cloud',
            'konseling'     => 'Konseling & Bimbingan Karir Pegawai',
            'skp-tahunan'   => 'Penilaian SKP Tahunan Terintegrasi BKN',
            'beban-kerja'   => 'Beban Kerja Dosen (BKD / SISTER)',
        ];

        $moduleTitle = $moduleNames[$module] ?? ucwords(str_replace('-', ' ', (string)($module ?? 'Fitur SIKAP Enterprise')));

        return view('pages.coming-soon', compact('moduleTitle'));
    }
}