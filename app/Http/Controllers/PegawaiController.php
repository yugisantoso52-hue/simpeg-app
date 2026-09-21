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
        $filter = strtolower(trim((string)$request->get('filter', '')));
        $pegawai = $this->pegawaiService->search($search, $filter);
        $statistics = $this->pegawaiService->getStatistics();

        return view('pegawai.index', compact(
            'pegawai',
            'statistics',
            'search',
            'filter'
        ));
    }

    public function duk(Request $request)
    {
        $search = $request->get('search');
        $filter = strtolower(trim((string)$request->get('filter', '')));

        // 1 Query Utama untuk mengambil semua Pegawai beserta relasinya (Mengurangi dari 25 query menjadi 5 query)
        $query = Pegawai::with(['golongan', 'unitKerja', 'jabatan', 'riwayatPendidikan', 'riwayatDiklat']);

        if ($search) {
            $query->where(function($sq) use ($search) {
                $sq->where('nama', 'like', "%{$search}%")
                   ->orWhere('nip', 'like', "%{$search}%")
                   ->orWhere('nidn_nuptk', 'like', "%{$search}%");
            });
        }

        $allPegawai = $query->get();

        $isPns = function($p) {
            $jenis = strtoupper(trim((string)$p->jenis_pegawai));
            $asn   = strtoupper(trim((string)$p->status_asn));
            $cleanNip = preg_replace('/[^0-9]/', '', (string)$p->nip);
            return ($jenis === 'PNS' || (!str_contains($jenis, 'PPPK') && ($asn === 'ASN' || strlen($cleanNip) === 18)));
        };

        $isPppk = function($p) {
            $jenis = strtoupper(trim((string)$p->jenis_pegawai));
            $asn   = strtoupper(trim((string)$p->status_asn));
            $cleanNip = preg_replace('/[^0-9]/', '', (string)$p->nip);
            return (str_contains($jenis, 'PPPK') || $asn === 'PPPK' || strlen($cleanNip) === 21);
        };

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

        $dosenList = $allPegawai->filter(fn($p) => $p->kategori_kepegawaian === 'Dosen');
        $tendikList = $allPegawai->filter(fn($p) => $p->kategori_kepegawaian === 'Tendik');
        $phlListRaw = $allPegawai->filter(fn($p) => $p->kategori_kepegawaian === 'PHL');

        $dosenPnsList   = $sortDuk($dosenList->filter($isPns));
        $dosenPppkList  = $sortDuk($dosenList->filter($isPppk));
        $tendikPnsList  = $sortDuk($tendikList->filter($isPns));
        $tendikPppkList = $sortDuk($tendikList->filter($isPppk));
        $phlList        = $sortDuk($phlListRaw);

        $statistics = [
            'dosen_pns'   => count($dosenPnsList),
            'dosen_pppk'  => count($dosenPppkList),
            'tendik_pns'  => count($tendikPnsList),
            'tendik_pppk' => count($tendikPppkList),
            'phl'         => count($phlList),
            'total'       => count($dosenPnsList) + count($dosenPppkList) + count($tendikPnsList) + count($tendikPppkList) + count($phlList),
        ];

        return view('pegawai.duk', compact(
            'dosenPnsList', 'dosenPppkList', 'tendikPnsList', 'tendikPppkList', 'phlList',
            'statistics', 'search', 'filter'
        ));
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
            'atasanList'   => Pegawai::where('status_pegawai', 'Aktif')->orderBy('nama')->get(['id', 'nama', 'nip', 'jabatan_id']),
        ]);
    }

    public function store(StorePegawaiRequest $request)
    {
        $newPegawai = null;
        DB::transaction(function () use ($request, &$newPegawai) {
            $newPegawai = $this->pegawaiService->createPegawai(
                $request->validated(),
                $request->allFiles()
            );
        });

        \App\Services\ActivityLoggerService::logCreate(
            'Pegawai',
            $newPegawai->id ?? 0,
            "Menambahkan pegawai baru: " . ($request->input('nama') ?? 'Pegawai Baru')
        );

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
        $pegawai = $this->pegawaiService->find($id, false);

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
            'atasanList'   => Pegawai::where('status_pegawai', 'Aktif')
                ->where('id', '!=', $id)
                ->orderBy('nama')
                ->get(['id', 'nama', 'nip', 'jabatan_id']),
        ]);
    }

    public function update(UpdatePegawaiRequest $request, int $id)
    {
        $pegawai = $this->pegawaiService->find($id, false);

        // OTORISASI POLICY: Cek izin update data
        $this->authorize('update', $pegawai);

        $this->pegawaiService->updatePegawai(
            $id,
            $request->validated(),
            $request->allFiles()
        );

        \App\Services\ActivityLoggerService::logUpdate(
            'Pegawai',
            $id,
            "Memperbarui data profil pegawai: " . ($pegawai->nama ?? 'Pegawai')
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
        $pegawai = $this->pegawaiService->find($id, false);

        // OTORISASI POLICY: Cek izin hapus data
        $this->authorize('delete', $pegawai);

        \App\Services\ActivityLoggerService::logDelete(
            'Pegawai',
            $id,
            "Menghapus data pegawai ID {$id}: " . ($pegawai->nama ?? '-')
        );

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
        
        // 1 Query Utama untuk PDF Export (Mengurangi dari 25 query menjadi 5 query)
        $query = Pegawai::with(['golongan', 'unitKerja', 'jabatan', 'riwayatPendidikan', 'riwayatDiklat']);

        if ($search) {
            $query->where(function($sq) use ($search) {
                $sq->where('nama', 'like', "%{$search}%")
                   ->orWhere('nip', 'like', "%{$search}%")
                   ->orWhere('nidn_nuptk', 'like', "%{$search}%");
            });
        }

        $allPegawai = $query->get();

        $isPns = function($p) {
            $jenis = strtoupper(trim((string)$p->jenis_pegawai));
            $asn   = strtoupper(trim((string)$p->status_asn));
            $cleanNip = preg_replace('/[^0-9]/', '', (string)$p->nip);
            return ($jenis === 'PNS' || (!str_contains($jenis, 'PPPK') && ($asn === 'ASN' || strlen($cleanNip) === 18)));
        };

        $isPppk = function($p) {
            $jenis = strtoupper(trim((string)$p->jenis_pegawai));
            $asn   = strtoupper(trim((string)$p->status_asn));
            $cleanNip = preg_replace('/[^0-9]/', '', (string)$p->nip);
            return (str_contains($jenis, 'PPPK') || $asn === 'PPPK' || strlen($cleanNip) === 21);
        };

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

        $dosenList = $allPegawai->filter(fn($p) => $p->kategori_kepegawaian === 'Dosen');
        $tendikList = $allPegawai->filter(fn($p) => $p->kategori_kepegawaian === 'Tendik');
        $phlListRaw = $allPegawai->filter(fn($p) => $p->kategori_kepegawaian === 'PHL');

        $dosenPnsList   = $sortDuk($dosenList->filter($isPns));
        $dosenPppkList  = $sortDuk($dosenList->filter($isPppk));
        $tendikPnsList  = $sortDuk($tendikList->filter($isPns));
        $tendikPppkList = $sortDuk($tendikList->filter($isPppk));
        $phlList        = $sortDuk($phlListRaw);

        $pdf = Pdf::loadView('exports.pdf.duk', compact('dosenPnsList', 'dosenPppkList', 'tendikPnsList', 'tendikPppkList', 'phlList'))
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
            $normalizedPath = ltrim(str_replace('\\', '/', $pegawai->foto), '/');
            $cleanPath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $pegawai->foto), DIRECTORY_SEPARATOR);

            // 0. Cek via Storage disk cloud (Supabase / R2 / S3) jika disk utama adalah cloud
            $defaultDiskName = config('filesystems.default', 'public');
            if (in_array($defaultDiskName, ['supabase', 's3', 'r2'])) {
                $cloudDisk = \Illuminate\Support\Facades\Storage::disk($defaultDiskName);
                if ($cloudDisk->exists($normalizedPath)) {
                    return $cloudDisk->response($normalizedPath);
                }
            }

            // 1. Cek via Storage disk public (kompatibel dengan Storage::fake, local, dan S3/R2)
            $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($publicDisk->exists($normalizedPath) || $publicDisk->exists($cleanPath)) {
                $targetFile = $publicDisk->exists($normalizedPath) ? $normalizedPath : $cleanPath;
                if (config('filesystems.disks.public.driver') === 's3') {
                    $url = $publicDisk->url($targetFile);
                    if ($url && !str_starts_with($url, '/')) {
                        return redirect()->away($url);
                    }
                    return $publicDisk->response($targetFile);
                }
                return response()->file($publicDisk->path($targetFile));
            }

            // 2. Cek via Storage disk local
            $localDisk = \Illuminate\Support\Facades\Storage::disk('local');
            if ($localDisk->exists($normalizedPath) || $localDisk->exists($cleanPath)) {
                $targetFile = $localDisk->exists($normalizedPath) ? $normalizedPath : $cleanPath;
                if (config('filesystems.disks.local.driver') === 's3') {
                    return $localDisk->response($targetFile);
                }
                return response()->file($localDisk->path($targetFile));
            }

            // 3. Fallback direct file checks (khusus environment lokal)
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
        $filter = strtolower(trim((string)$request->get('filter', '')));
        $query = Pegawai::dosen()->with(['golongan', 'unitKerja', 'jabatan']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('nidn_nuptk', 'like', "%{$search}%");
            });
        }

        if ($filter === 'pns') {
            $query->where(function ($q) {
                $q->where('jenis_pegawai', 'not like', '%PPPK%')
                  ->where(function ($sq) {
                      $sq->where('status_asn', 'ASN')
                         ->orWhereRaw('CHAR_LENGTH(nip) = 18');
                  });
            });
        } elseif ($filter === 'pppk') {
            $query->where(function ($q) {
                $q->where('jenis_pegawai', 'like', '%PPPK%')
                  ->orWhere('status_asn', 'PPPK')
                  ->orWhereRaw('CHAR_LENGTH(nip) = 21');
            });
        } elseif ($filter === 'aktif') {
            $query->aktif();
        } elseif ($filter === 'tubel') {
            $query->where('status_pegawai', 'Tugas Belajar');
        }

        $pegawai = $query->latest('id')->paginate(10)->withQueryString();

        $dosenStats = Pegawai::dosen()->selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN status_pegawai = 'Aktif' THEN 1 ELSE 0 END), 0) as aktif,
            COALESCE(SUM(CASE WHEN (jenis_pegawai NOT LIKE '%PPPK%' AND (status_asn = 'ASN' OR CHAR_LENGTH(nip) = 18)) THEN 1 ELSE 0 END), 0) as pns,
            COALESCE(SUM(CASE WHEN (jenis_pegawai LIKE '%PPPK%' OR status_asn = 'PPPK' OR CHAR_LENGTH(nip) = 21) THEN 1 ELSE 0 END), 0) as pppk,
            COALESCE(SUM(CASE WHEN status_pegawai = 'Tugas Belajar' THEN 1 ELSE 0 END), 0) as tubel
        ")->first();

        $statistics = [
            'total' => (int)($dosenStats->total ?? 0),
            'aktif' => (int)($dosenStats->aktif ?? 0),
            'pns'   => (int)($dosenStats->pns ?? 0),
            'pppk'  => (int)($dosenStats->pppk ?? 0),
            'tubel' => (int)($dosenStats->tubel ?? 0),
        ];

        $kategoriTitle = 'Data Tenaga Pendidik / Dosen';
        $kategoriSubtitle = 'Pencatatan data dosen tetap, NIDN, jabatan fungsional akademik, dan kepangkatan';
        $kategori = 'Dosen';
        $badgeColor = 'blue';

        return view('pegawai.kategori', compact('pegawai', 'statistics', 'search', 'filter', 'kategoriTitle', 'kategoriSubtitle', 'kategori', 'badgeColor'));
    }

    /**
     * Tampilan Khusus Tenaga Kependidikan (Tendik)
     */
    public function tendik(Request $request)
    {
        $search = $request->get('search');
        $filter = strtolower(trim((string)$request->get('filter', '')));
        $query = Pegawai::tendik()->with(['golongan', 'unitKerja', 'jabatan']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($filter === 'pns') {
            $query->where(function ($q) {
                $q->where('jenis_pegawai', 'PNS')
                  ->orWhere(function ($sq) {
                      $sq->where('status_asn', 'ASN')
                         ->where('jenis_pegawai', 'not like', '%PPPK%');
                  });
            });
        } elseif ($filter === 'pppk') {
            $query->where(function ($q) {
                $q->where('jenis_pegawai', 'PPPK')
                  ->orWhere('status_asn', 'PPPK');
            });
        } elseif ($filter === 'aktif') {
            $query->aktif();
        } elseif ($filter === 'tubel') {
            $query->where('status_pegawai', 'Tugas Belajar');
        }

        $pegawai = $query->latest('id')->paginate(10)->withQueryString();

        $tendikStats = Pegawai::tendik()->selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN status_pegawai = 'Aktif' THEN 1 ELSE 0 END), 0) as aktif,
            COALESCE(SUM(CASE WHEN (jenis_pegawai = 'PNS' OR (status_asn = 'ASN' AND (jenis_pegawai NOT LIKE '%PPPK%' OR jenis_pegawai IS NULL))) THEN 1 ELSE 0 END), 0) as pns,
            COALESCE(SUM(CASE WHEN (jenis_pegawai = 'PPPK' OR status_asn = 'PPPK') THEN 1 ELSE 0 END), 0) as pppk,
            COALESCE(SUM(CASE WHEN status_pegawai = 'Tugas Belajar' THEN 1 ELSE 0 END), 0) as tubel
        ")->first();

        $statistics = [
            'total' => (int)($tendikStats->total ?? 0),
            'aktif' => (int)($tendikStats->aktif ?? 0),
            'pns'   => (int)($tendikStats->pns ?? 0),
            'pppk'  => (int)($tendikStats->pppk ?? 0),
            'tubel' => (int)($tendikStats->tubel ?? 0),
        ];

        $kategoriTitle = 'Data Tenaga Kependidikan (Tendik)';
        $kategoriSubtitle = 'Pencatatan data staf administrasi, laboran, teknisi, dan fungsional umum';
        $kategori = 'Tendik';
        $badgeColor = 'emerald';

        return view('pegawai.kategori', compact('pegawai', 'statistics', 'search', 'filter', 'kategoriTitle', 'kategoriSubtitle', 'kategori', 'badgeColor'));
    }

    /**
     * Tampilan Khusus Pegawai Harian Lepas (PHL / Kontrak)
     */
    public function phl(Request $request)
    {
        $search = $request->get('search');
        $filter = strtolower(trim((string)$request->get('filter', '')));
        $query = Pegawai::phl()->with(['golongan', 'unitKerja', 'jabatan']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($filter === 'aktif') {
            $query->aktif();
        } elseif ($filter === 'non_asn') {
            $query->where('status_asn', 'Non ASN');
        } elseif ($filter === 'kontrak') {
            $query->whereNotNull('jenis_kontrak');
        }

        $pegawai = $query->latest('id')->paginate(10)->withQueryString();

        $phlStats = Pegawai::phl()->selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN status_pegawai = 'Aktif' THEN 1 ELSE 0 END), 0) as aktif,
            COALESCE(SUM(CASE WHEN status_asn = 'Non ASN' THEN 1 ELSE 0 END), 0) as non_asn,
            COALESCE(SUM(CASE WHEN jenis_kontrak IS NOT NULL THEN 1 ELSE 0 END), 0) as kontrak
        ")->first();

        $statistics = [
            'total'   => (int)($phlStats->total ?? 0),
            'aktif'   => (int)($phlStats->aktif ?? 0),
            'non_asn' => (int)($phlStats->non_asn ?? 0),
            'kontrak' => (int)($phlStats->kontrak ?? 0),
        ];

        $kategoriTitle = 'Data Pegawai Harian Lepas (PHL) & Tenaga Kontrak';
        $kategoriSubtitle = 'Pencatatan data pegawai non-ASN, honorer, dan kontrak kerja institusi';
        $kategori = 'PHL';
        $badgeColor = 'amber';

        return view('pegawai.kategori', compact('pegawai', 'statistics', 'search', 'filter', 'kategoriTitle', 'kategoriSubtitle', 'kategori', 'badgeColor'));
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