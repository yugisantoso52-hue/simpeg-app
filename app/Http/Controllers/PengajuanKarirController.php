<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengajuanKarir\StorePengajuanKgbRequest;
use App\Http\Requests\PengajuanKarir\StorePengajuanKpRequest;
use App\Http\Requests\PengajuanKarir\VerifikasiPengajuanKarirRequest;
use App\Models\Golongan;
use App\Models\Pegawai;
use App\Models\PengajuanKarir;
use App\Models\UsulanSatyalancana;
use App\Services\SatyalancanaService;
use App\Services\KpService;
use App\Services\KgbService;
use App\Services\KelayakanKarirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PengajuanKarirController extends Controller
{
    /**
     * Tampilkan daftar pengajuan mandiri pegawai atau daftar verifikasi jika admin/pimpinan
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isExecutive = $user->canAccessExecutiveKepegawaianMenus();
        $tab = strtolower((string)$request->query('tab', 'all'));

        // Gunakan state lokal ($filterJenis) tanpa memutasi global $request
        $filterJenis = $request->query('jenis');
        if (empty($filterJenis)) {
            if ($tab === 'kp') {
                $filterJenis = 'KP';
            } elseif ($tab === 'kgb') {
                $filterJenis = 'KGB';
            }
        }

        $query = PengajuanKarir::with(['pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan', 'golonganLama', 'golonganTujuan']);

        // Jika bukan pimpinan / executive kepegawaian, batasi hanya melihat berkas miliknya sendiri
        // Jika executive ingin melihat berkas pribadinya, bisa gunakan filter ?scope=saya
        if (!$isExecutive || $request->query('scope') === 'saya') {
            $pegawaiId = $user->pegawai_id ?? $user->pegawai?->id;
            $query->where('pegawai_id', $pegawaiId);
        }

        if (!empty($filterJenis)) {
            $query->where('jenis_pengajuan', $filterJenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuans = $query->latest()->paginate(15);
        $userPegawai = $user->pegawai;

        $pegawaiId = $user->pegawai_id ?? $user->pegawai?->id;

        // Badge counters untuk notifikasi usulan yang memerlukan verifikasi
        if ($isExecutive && $request->query('scope') !== 'saya') {
            $countKpPending = PengajuanKarir::where('jenis_pengajuan', 'KP')->whereNotIn('status', ['disetujui_dekan', 'disetujui_wd2', 'ditolak'])->count();
            $countKgbPending = PengajuanKarir::where('jenis_pengajuan', 'KGB')->whereNotIn('status', ['disetujui_dekan', 'disetujui_wd2', 'ditolak'])->count();
            $countSatyalancanaPending = UsulanSatyalancana::whereNotIn('status', ['disetujui_wd2', 'ditolak'])->count();
        } else {
            $countKpPending = PengajuanKarir::where('pegawai_id', $pegawaiId)->where('jenis_pengajuan', 'KP')->whereNotIn('status', ['disetujui_dekan', 'disetujui_wd2', 'ditolak'])->count();
            $countKgbPending = PengajuanKarir::where('pegawai_id', $pegawaiId)->where('jenis_pengajuan', 'KGB')->whereNotIn('status', ['disetujui_dekan', 'disetujui_wd2', 'ditolak'])->count();
            $countSatyalancanaPending = UsulanSatyalancana::where('pegawai_id', $pegawaiId)->whereNotIn('status', ['disetujui_wd2', 'ditolak'])->count();
        }

        // Data Usulan & Nominasi Satyalancana (jika tab aktif atau executive)
        $layakSatyalancana = [];
        $daftarUsulanSatyalancana = null;
        if ($tab === 'satyalancana' || $isExecutive) {
            $satyalancanaService = app(SatyalancanaService::class);
            $tahunIni = (int) date('Y');
            $usulanTerkini = UsulanSatyalancana::where('tahun_usulan', $tahunIni)
                ->get()
                ->keyBy('pegawai_id');

            $pegawaiQuery = Pegawai::with([
                'unitKerja', 'jabatan', 'golongan',
                'riwayatSkp' => fn($q) => $q->orderBy('tahun', 'desc'),
                'riwayatPangkat' => fn($q) => $q->orderBy('tmt', 'desc'),
                'riwayatJabatan' => fn($q) => $q->orderBy('tmt_jabatan', 'desc'),
                'riwayatPenghargaan' => fn($q) => $q->orderBy('tanggal_terima', 'desc'),
            ])->where('status_pegawai', 'Aktif');

            if (!$isExecutive || $request->query('scope') === 'saya') {
                $pegawaiQuery->where('id', $pegawaiId);
            }

            $allPegawaiSatyalancana = $pegawaiQuery->get();

            foreach ($allPegawaiSatyalancana as $p) {
                $kat = $satyalancanaService->cekKelayakanSatyalancana($p);
                if ($kat) {
                    $p->kategori_satyalancana = $kat;
                    $p->usulan_aktif = $usulanTerkini->get($p->id);
                    $layakSatyalancana[] = $p;
                }
            }

            $usulanQuery = UsulanSatyalancana::with([
                'pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan',
                'pegawai.riwayatSkp', 'pegawai.riwayatPangkat.golongan', 'pegawai.riwayatJabatan.jabatan', 'pegawai.riwayatPenghargaan',
                'diajukanOleh', 'verifikasiAdminBy', 'parafKabagBy', 'parafKaPokjaBy', 'ttdWd2By'
            ]);

            if (!$isExecutive || $request->query('scope') === 'saya') {
                $usulanQuery->where('pegawai_id', $pegawaiId);
            }

            $daftarUsulanSatyalancana = $usulanQuery->orderByDesc('id')->paginate(15, ['*'], 'sl_page');
        }

        // Data Radar Jatuh Tempo (Monitoring KP & KGB)
        $reminderKp = collect();
        $reminderKgb = collect();
        if ($tab === 'radar' || $isExecutive) {
            $hariIni = Carbon::now()->startOfDay();
            $hariTarget = Carbon::now()->addMonths(3)->endOfDay();
            $allPegawaiRadar = Pegawai::with(['unitKerja', 'jabatan', 'golongan', 'pengajuanKarir'])
                ->where('status_pegawai', 'Aktif')
                ->when(!$isExecutive || $request->query('scope') === 'saya', function ($q) use ($pegawaiId) {
                    $q->where('id', $pegawaiId ?: 0);
                })
                ->get();
            $reminderKp = $allPegawaiRadar->filter(function ($p) use ($hariIni, $hariTarget) {
                if (!$p->kp_berikutnya) return false;
                $tgl = Carbon::parse($p->kp_berikutnya);
                return $tgl->between($hariIni, $hariTarget);
            })->sortBy('kp_berikutnya')->values();

            $reminderKgb = $allPegawaiRadar->filter(function ($p) use ($hariIni, $hariTarget) {
                if (!$p->kgb_berikutnya) return false;
                $tgl = Carbon::parse($p->kgb_berikutnya);
                return $tgl->between($hariIni, $hariTarget);
            })->sortBy('kgb_berikutnya')->values();
        }

        // Jabatan & wewenang login (eksklusif sesuai tahapan masing-masing)
        $isKaPokja = $user->isKaPokjaKepeg();
        $isKabag   = $user->isKabagUmum();
        $isWd2     = $user->isWadek2();
        $isDekan   = $user->isDekan();
        $isAdmin   = $user->isScreeningAdmin();

        // Evaluasi Kepatuhan Regulasi Waktu & Periode serta Kelengkapan Berkas Mandiri Pegawai
        $kelayakanService = app(KelayakanKarirService::class);
        $statusKp = $userPegawai ? $kelayakanService->evaluasiKp($userPegawai) : null;
        $statusKgb = $userPegawai ? $kelayakanService->evaluasiKgb($userPegawai) : null;
        $statusSatya = $userPegawai ? $kelayakanService->evaluasiSatyalancana($userPegawai) : null;

        return view('pengajuan-karir.index', compact(
            'pengajuans', 'isExecutive', 'userPegawai', 'tab',
            'countKpPending', 'countKgbPending', 'countSatyalancanaPending',
            'layakSatyalancana', 'daftarUsulanSatyalancana',
            'reminderKp', 'reminderKgb',
            'isAdmin', 'isKaPokja', 'isKabag', 'isWd2', 'isDekan',
            'statusKp', 'statusKgb', 'statusSatya'
        ));
    }

    /**
     * Form Pengajuan Mandiri (KGB atau KP)
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum ditautkan dengan data induk Pegawai.');
        }

        $jenis = strtoupper($request->query('jenis', 'KGB'));
        if (!in_array($jenis, ['KGB', 'KP'])) {
            $jenis = 'KGB';
        }

        // Verifikasi kepatuhan waktu dan periode pengusulan sesuai peraturan perundangan
        $kelayakanService = app(KelayakanKarirService::class);
        if ($jenis === 'KP') {
            $eval = $kelayakanService->evaluasiKp($pegawai);
            if (!$eval['can_submit'] && !$user->canAccessExecutiveKepegawaianMenus()) {
                $pesan = implode(' ', $eval['alasan_kunci']);
                return redirect()->route('pengajuan-karir.index', ['tab' => 'kp', 'jenis' => 'KP'])
                    ->with('error', "Pengajuan Kenaikan Pangkat belum dapat dibuka: {$pesan}");
            }
        } else {
            $eval = $kelayakanService->evaluasiKgb($pegawai);
            if (!$eval['can_submit'] && !$user->canAccessExecutiveKepegawaianMenus()) {
                $pesan = implode(' ', $eval['alasan_kunci']);
                return redirect()->route('pengajuan-karir.index', ['tab' => 'kgb', 'jenis' => 'KGB'])
                    ->with('error', "Pengajuan Kenaikan Gaji Berkala (KGB) belum dapat dibuka: {$pesan}");
            }
        }

        $golongans = Golongan::orderBy('id')->get();
        $syaratKp = $jenis === 'KP' ? $pegawai->evaluasiSyaratKp() : null;

        // 6 Periode Kenaikan Pangkat BKN
        $periodeKpList = [
            'Februari'  => 'Februari (Pengusulan: 15 Des s.d. 15 Jan)',
            'April'     => 'April (Pengusulan: 1 s.d. 28 Feb)',
            'Juni'      => 'Juni (Pengusulan: 1 s.d. 30 Apr)',
            'Agustus'   => 'Agustus (Pengusulan: 1 s.d. 30 Jun)',
            'Oktober'   => 'Oktober (Pengusulan: 1 s.d. 31 Ags)',
            'Desember'  => 'Desember (Pengusulan: 1 s.d. 31 Okt)',
        ];

        return view('pengajuan-karir.create', compact('pegawai', 'jenis', 'golongans', 'syaratKp', 'periodeKpList', 'isPns'));
    }

    /**
     * Simpan Pengajuan Baru (Skema Validasi Terpisah antara KP dan KGB)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            abort(403, 'Akses ditolak.');
        }

        $jenisPengajuan = strtoupper((string) $request->input('jenis_pengajuan', 'KGB'));
        if ($jenisPengajuan === 'KP') {
            $formRequest = new StorePengajuanKpRequest();
        } else {
            $formRequest = new StorePengajuanKgbRequest();
        }

        $validated = Validator::make(
            $request->all(),
            $formRequest->rules(),
            $formRequest->messages()
        )->validate();

        // Verifikasi kepatuhan waktu dan periode pengusulan
        $kelayakanService = app(KelayakanKarirService::class);
        if ($jenisPengajuan === 'KP') {
            $eval = $kelayakanService->evaluasiKp($pegawai);
            if (!$eval['can_submit'] && !$user->canAccessExecutiveKepegawaianMenus()) {
                $pesan = implode(' ', $eval['alasan_kunci']);
                return redirect()->route('pengajuan-karir.index', ['tab' => 'kp', 'jenis' => 'KP'])
                    ->with('error', "Pengajuan Kenaikan Pangkat tidak dapat diproses: {$pesan}");
            }
        } else {
            $eval = $kelayakanService->evaluasiKgb($pegawai);
            if (!$eval['can_submit'] && !$user->canAccessExecutiveKepegawaianMenus()) {
                $pesan = implode(' ', $eval['alasan_kunci']);
                return redirect()->route('pengajuan-karir.index', ['tab' => 'kgb', 'jenis' => 'KGB'])
                    ->with('error', "Pengajuan Kenaikan Gaji Berkala (KGB) tidak dapat diproses: {$pesan}");
            }
        }

        $data = [
            'pegawai_id'       => $pegawai->id,
            'jenis_pengajuan'  => $validated['jenis_pengajuan'],
            'status'           => 'diajukan',
            'catatan_pegawai'  => $validated['catatan_pegawai'] ?? null,
        ];

        if ($request->jenis_pengajuan === 'KP') {
            $data['periode_kp']         = $request->periode_kp ?? 'Februari';
            $data['tahun_periode']      = $request->tahun_periode ?? date('Y');
            $data['golongan_lama_id']   = $pegawai->golongan_id;
            $data['golongan_tujuan_id'] = $request->golongan_tujuan_id;
            $data['tmt_lama']           = $pegawai->tmt_pangkat_terakhir;
        } else {
            $mkgLama = (int) ($pegawai->mkg_tahun ?? 0);
            $mkgBaru = $mkgLama + 2;

            $data['tmt_lama']           = $pegawai->tmt_kgb_terakhir;
            $data['tmt_baru']           = $request->tmt_baru ?? now()->toDateString();
            $data['mkg_tahun']          = $mkgBaru;
            $data['mkg_bulan']          = $pegawai->mkg_bulan ?? 0;
            $data['gaji_pokok_lama']    = \App\Services\GajiService::hitungGajiPegawai($pegawai, $mkgLama);
            $data['gaji_pokok_baru']    = \App\Services\GajiService::hitungGajiPegawai($pegawai, $mkgBaru);
        }

        // Upload berkas jika dilampirkan
        $uploadFields = [
            'file_sk_terakhir',
            'file_sk_pangkat_terakhir',
            'file_sk_kgb_terakhir',
            'file_skp_1',
            'file_skp_2',
            'file_karpeg',
            'file_pak',
            'file_pendukung'
        ];

        $defaultDisk = config('filesystems.default', 'local');
        $uploadDisk  = in_array($defaultDisk, ['supabase', 's3', 'r2']) ? $defaultDisk : 'local';

        foreach ($uploadFields as $field) {
            if ($request->hasFile($field)) {
                try {
                    $path = $request->file($field)->store('pengajuan_karir/' . strtolower($request->jenis_pengajuan), $uploadDisk);
                } catch (\Throwable $e) {
                    $path = $request->file($field)->store('pengajuan_karir/' . strtolower($request->jenis_pengajuan), 'local');
                }
                $data[$field] = $path;
            }
        }

        // Fallback file_sk_terakhir jika pengusul mengisi file_sk_pangkat_terakhir
        if (empty($data['file_sk_terakhir']) && !empty($data['file_sk_pangkat_terakhir'])) {
            $data['file_sk_terakhir'] = $data['file_sk_pangkat_terakhir'];
        }

        PengajuanKarir::create($data);

        return redirect()->route('pengajuan-karir.index')
            ->with('success', 'Permohonan pengajuan ' . $request->jenis_pengajuan . ' berhasil dikirim ke Bagian Kepegawaian.');
    }

    /**
     * Detail Pengajuan Karir & Tracking Paraf
     */
    public function show($id)
    {
        $pengajuan = PengajuanKarir::with([
            'pegawai.unitKerja', 
            'pegawai.jabatan', 
            'pegawai.golongan', 
            'golonganLama', 
            'golonganTujuan',
            'verifikatorKaPokja',
            'verifikatorKabag',
            'verifikatorWd2',
            'penandatanganDekan'
        ])->findOrFail($id);

        $user = Auth::user();
        $isExecutive = $user->canAccessExecutiveKepegawaianMenus();
        $pegawaiId = $user->pegawai_id ?? $user->pegawai?->id;

        if (!$isExecutive && $pengajuan->pegawai_id !== $pegawaiId) {
            abort(403, 'Anda tidak memiliki hak akses melihat berkas pengajuan ini.');
        }

        $isKaPokja = $user->isKaPokjaKepeg();
        $isKabag   = $user->isKabagUmum();
        $isWd2     = $user->isWadek2();
        $isDekan   = $user->isDekan();
        $isAdmin   = $user->isScreeningAdmin();

        return view('pengajuan-karir.show', compact('pengajuan', 'isExecutive', 'isAdmin', 'isKaPokja', 'isKabag', 'isWd2', 'isDekan'));
    }

    /**
     * Proses Verifikasi & Paraf Bertingkat (Ka Pokja -> Kabag -> WD II -> Dekan)
     */
    public function verifikasi(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->canAccessExecutiveKepegawaianMenus()) {
            abort(403, 'Akses terbatas untuk Pimpinan dan Pengelola Kepegawaian.');
        }

        $pengajuan = PengajuanKarir::findOrFail($id);
        $tahap = $request->input('tahap'); // paraf_kapokja, paraf_kabag, paraf_wd2, ttd_dekan, tolak
        $catatan = $request->input('catatan');

        $isKaPokja = $user->isKaPokjaKepeg();
        $isKabag   = $user->isKabagUmum();
        $isWd2     = $user->isWadek2();
        $isDekan   = $user->isDekan();
        $isAdmin   = $user->isScreeningAdmin();

        $pejabatLogin = $user->pegawai ?? Pegawai::where('nama', 'like', '%' . $user->name . '%')->first();

        DB::beginTransaction();
        try {
            if ($tahap === 'verifikasi_admin') {
                if (!$isAdmin) {
                    DB::rollBack();
                    return back()->with('error', 'Otorisasi Ditolak: Tahap 1 (Admin Screening & Penomoran) hanya berhak dilakukan oleh akun Admin Kepegawaian.');
                }

                $updateData = [
                    'status'              => 'diverifikasi_admin',
                    'verifikasi_admin_at' => now(),
                    'verifikasi_admin_by' => $user->id,
                    'catatan_verifikator' => $catatan ?? 'Telah diverifikasi kelengkapan berkas usulan karir (SK terakhir, SKP, dll) oleh Admin Kepegawaian.',
                ];

                if ($request->filled('nomor_surat_input')) {
                    $nomorInput = trim((string) $request->input('nomor_surat_input'));
                    $tahun = $pengajuan->tahun_periode ?: ($pengajuan->created_at ? $pengajuan->created_at->format('Y') : date('Y'));
                    $isKp = ($pengajuan->jenis_pengajuan === 'KP');
                    $updateData['nomor_surat'] = app(\App\Services\NomorSuratService::class)->formatKarir($nomorInput, (int) $tahun, $isKp);
                }

                $pengajuan->update($updateData);
                $msg = '1. Verifikasi Screening Berkas & Penomoran oleh Admin Kepegawaian berhasil.';
            } elseif ($tahap === 'paraf_kapokja') {
                if (!$isKaPokja) {
                    DB::rollBack();
                    return back()->with('error', 'Otorisasi Ditolak: Tahap 2 (Paraf Ka Pokja) hanya berhak dilakukan oleh Kepala Pokja Keuangan & Kepegawaian.');
                }

                if (!$pengajuan->verifikasi_admin_at) {
                    DB::rollBack();
                    return back()->with('error', 'SOP Tata Naskah: Berkas usulan harus diperiksa kelengkapannya (Admin Screening Berkas) terlebih dahulu sebelum diparaf Ka Pokja.');
                }

                $dataPokja = [
                    'status'              => 'diverifikasi_kapokja',
                    'paraf_kapokja_at'    => now(),
                    'paraf_kapokja_by'    => $pejabatLogin?->id,
                    'catatan_verifikator' => $catatan ?? 'Telah diperiksa kelengkapan berkas administrasi dan diverifikasi sah oleh Ka Pokja Keu-Kepeg.',
                ];

                $pengajuan->update($dataPokja);
                $msg = '2. Paraf Ka Pokja Keuangan dan Kepegawaian berhasil dibubuhkan.';
            } elseif ($tahap === 'paraf_kabag') {
                if (!$isKabag) {
                    DB::rollBack();
                    return back()->with('error', 'Otorisasi Ditolak: Tahap 3 (Paraf Kabag Umum) hanya berhak dilakukan oleh Kepala Bagian Umum.');
                }

                if (!$pengajuan->paraf_kapokja_at) {
                    DB::rollBack();
                    return back()->with('error', 'SOP Tata Naskah: Berkas harus diperiksa kelengkapannya dan diparaf terlebih dahulu oleh Ka Pokja Keu-Kepeg sebelum ditelaah oleh Kabag Umum.');
                }

                $dataKabag = [
                    'status'              => 'diverifikasi_kabag',
                    'paraf_kabag_at'      => now(),
                    'paraf_kabag_by'      => $pejabatLogin?->id,
                    'catatan_verifikator' => $catatan ?? 'Telah ditelaah format tata naskah dinas dan diparaf oleh Kepala Bagian Umum.',
                ];

                $pengajuan->update($dataKabag);
                $msg = '3. Paraf Kepala Bagian Umum berhasil dibubuhkan.';
            } elseif ($tahap === 'paraf_wd2' || $tahap === 'ttd_wd2') {
                if (!$isWd2 && !$isDekan) {
                    DB::rollBack();
                    return back()->with('error', 'Otorisasi Ditolak: Tahap 4 (TTE Pengesahan Usulan) hanya berhak disahkan oleh Wakil Dekan II atau Dekan.');
                }

                if (!$pengajuan->paraf_kabag_at) {
                    DB::rollBack();
                    return back()->with('error', 'SOP Tata Naskah: Naskah dinas harus melalui telaah paraf koordinasi Kepala Bagian Umum sebelum ditandatangani Wakil Dekan II.');
                }

                $dataWd2 = [
                    'status'              => 'disetujui_wd2',
                    'paraf_wd2_at'        => now(),
                    'paraf_wd2_by'        => $pejabatLogin?->id,
                    'catatan_verifikator' => $catatan ?? 'Telah ditandatangani dan disetujui secara resmi oleh Wakil Dekan Bidang Keuangan dan Umum.',
                ];

                $pengajuan->update($dataWd2);

                // SINKRONISASI OTOMATIS: Eksekusi perubahan ke master data pegawai & riwayat
                $this->sinkronisasiKeMasterPegawai($pengajuan);

                $msg = '4. Pengajuan resmi ditandatangani dan disetujui oleh Wakil Dekan Bidang Keuangan dan Umum, serta disinkronkan ke master data kepegawaian.';
            } elseif ($tahap === 'ttd_dekan') {
                if (!$isDekan) {
                    DB::rollBack();
                    return back()->with('error', 'Otorisasi Ditolak: Pengesahan Dekan hanya berhak dilakukan oleh Dekan Fakultas Keperawatan.');
                }

                $pengajuan->update([
                    'status'              => 'disetujui_dekan',
                    'ttd_dekan_at'        => now(),
                    'ttd_dekan_by'        => $pejabatLogin?->id,
                    'catatan_verifikator' => $catatan ?? 'Disetujui secara resmi oleh Dekan Fakultas Keperawatan.',
                ]);

                // SINKRONISASI OTOMATIS: Eksekusi perubahan ke master data pegawai & riwayat
                $this->sinkronisasiKeMasterPegawai($pengajuan);

                $msg = 'Pengajuan resmi disetujui oleh Dekan dan data induk pegawai telah diperbarui.';
            } elseif ($tahap === 'tolak') {
                $canUserReject = false;
                if (!$pengajuan->verifikasi_admin_at && $isAdmin) {
                    $canUserReject = true;
                } elseif ($pengajuan->verifikasi_admin_at && !$pengajuan->paraf_kapokja_at && $isKaPokja) {
                    $canUserReject = true;
                } elseif ($pengajuan->paraf_kapokja_at && !$pengajuan->paraf_kabag_at && $isKabag) {
                    $canUserReject = true;
                } elseif ($pengajuan->paraf_kabag_at && $pengajuan->status !== 'disetujui_wd2' && ($isWd2 || $isDekan)) {
                    $canUserReject = true;
                }

                if (!$canUserReject) {
                    DB::rollBack();
                    return back()->with('error', 'Otorisasi Ditolak: Anda tidak memiliki wewenang untuk menolak / mengembalikan usulan pada tahapan alur saat ini.');
                }

                $pengajuan->update([
                    'status'              => 'ditolak',
                    'catatan_verifikator' => $catatan ?? 'Berkas dikembalikan untuk diperbaiki.',
                ]);
                $msg = 'Pengajuan ditolak / dikembalikan ke pegawai.';
            } else {
                DB::rollBack();
                return back()->with('error', 'Tahap verifikasi tidak dikenali.');
            }

            DB::commit();
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal verifikasi pengajuan karir ID {$pengajuan->id}: " . $e->getMessage(), [
                'pengajuan_id' => $pengajuan->id,
                'tahap'        => $tahap,
                'trace'        => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memproses verifikasi pengajuan karir: ' . $e->getMessage());
        }
    }

    /**
     * Sinkronisasi hasil pengesahan karir (KP atau KGB) ke data master Pegawai & Riwayat Terkait
     */
    private function sinkronisasiKeMasterPegawai(PengajuanKarir $pengajuan): void
    {
        $pegawai = $pengajuan->pegawai;
        if (!$pegawai) return;

        if ($pengajuan->jenis_pengajuan === 'KP' && $pengajuan->golongan_tujuan_id) {
            $tahun = $pengajuan->tahun_periode ?? date('Y');
            $bulanMap = [
                'Februari' => '02-01',
                'April'    => '04-01',
                'Juni'     => '06-01',
                'Agustus'  => '08-01',
                'Oktober'  => '10-01',
                'Desember' => '12-01',
            ];
            $bulanTmt = $bulanMap[$pengajuan->periode_kp] ?? '04-01';
            $tmtPangkatBaru = "{$tahun}-{$bulanTmt}";

            app(KpService::class)->prosesKp($pegawai, [
                'golongan_baru_id' => $pengajuan->golongan_tujuan_id,
                'tmt_pangkat_baru' => $tmtPangkatBaru,
            ]);

            if ($pengajuan->file_sk_pangkat_terakhir || $pengajuan->file_sk_terakhir) {
                $fileSk = $pengajuan->file_sk_pangkat_terakhir ?? $pengajuan->file_sk_terakhir;
                $pegawai->update(['file_sk_pangkat_terakhir' => $fileSk]);
            }
        } elseif ($pengajuan->jenis_pengajuan === 'KGB' && $pengajuan->tmt_baru) {
            $tmtKgb = is_string($pengajuan->tmt_baru) ? $pengajuan->tmt_baru : $pengajuan->tmt_baru->format('Y-m-d');
            app(KgbService::class)->prosesKgb($pegawai, [
                'tmt_kgb_baru'    => $tmtKgb,
                'gaji_pokok_baru' => $pengajuan->gaji_pokok_baru,
            ]);

            if ($pengajuan->file_sk_kgb_terakhir || $pengajuan->file_sk_terakhir) {
                $fileSk = $pengajuan->file_sk_kgb_terakhir ?? $pengajuan->file_sk_terakhir;
                $pegawai->update(['file_sk_kgb_terakhir' => $fileSk]);
            }
        }
    }

    /**
     * Update Nomor Surat Usulan Karir Khusus Admin
     */
    public function updateNomorSurat(Request $request, $id)
    {
        $user = $request->user() ?? Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Hanya Admin Kepegawaian yang berwenang mengisi atau mengubah nomor surat.');
        }

        $pengajuan = PengajuanKarir::findOrFail($id);
        $nomorInput = trim((string) $request->input('nomor_surat_input', ''));
        $tahun = $pengajuan->tahun_periode ?: ($pengajuan->created_at ? $pengajuan->created_at->format('Y') : date('Y'));

        if ($nomorInput !== '') {
            $isKp = ($pengajuan->jenis_pengajuan === 'KP');
            $nomorSuratFormatted = app(\App\Services\NomorSuratService::class)->formatKarir($nomorInput, (int) $tahun, $isKp);
        } else {
            $nomorSuratFormatted = null;
        }

        $pengajuan->update(['nomor_surat' => $nomorSuratFormatted]);

        return back()->with('success', 'Nomor surat usulan karir berhasil disimpan oleh Admin: ' . ($nomorSuratFormatted ?? '(Dikosongkan)'));
    }

    /**
     * Upload & Lengkapi Berkas Persyaratan Mandiri oleh Pegawai
     */
    public function uploadSyarat(Request $request)
    {
        $request->validate([
            'jenis_berkas' => 'required|string|in:file_sk_pangkat_terakhir,file_sk_kgb_terakhir,file_karpeg,file_pak,file_sk_pertama',
            'berkas_file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'redirect_tab' => 'nullable|string',
        ]);

        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return back()->with('error', 'Data induk pegawai Anda tidak ditemukan.');
        }

        $field = $request->input('jenis_berkas');
        $path = $request->file('berkas_file')->store('pegawai/sk', 'local');

        $pegawai->update([$field => $path]);

        if ($field === 'file_sk_pangkat_terakhir') {
            $pangkatTerakhir = $pegawai->riwayatPangkat()->latest('tmt')->first();
            if ($pangkatTerakhir) {
                $pangkatTerakhir->update(['file_sk' => $path]);
            }
        }

        $labels = [
            'file_sk_pangkat_terakhir' => 'SK Pangkat Terakhir',
            'file_sk_kgb_terakhir'     => 'SK KGB Terakhir',
            'file_karpeg'              => 'Fotocopy Karpeg / Identitas ASN',
            'file_pak'                 => 'Dokumen PAK (Angka Kredit)',
            'file_sk_pertama'          => 'SK Pengangkatan Pertama / CPNS',
        ];

        $label = $labels[$field] ?? 'Dokumen';
        $tab = $request->input('redirect_tab', 'all');

        return redirect()->route('pengajuan-karir.index', ['tab' => $tab])
            ->with('success', "Berkas {$label} berhasil diunggah dan disimpan ke profil kepegawaian Anda.");
    }

    /**
     * Batalkan pengajuan karir oleh pegawai pemohon
     */
    public function cancel(Request $request, $id)
    {
        $user = Auth::user();
        $pengajuan = PengajuanKarir::findOrFail($id);

        $isAdmin = $user->hasRole('admin') || $user->isScreeningAdmin();
        $isOwner = (int)$user->pegawai_id === (int)$pengajuan->pegawai_id;

        if (!$isAdmin && !$isOwner) {
            abort(403, 'Anda tidak memiliki hak untuk membatalkan pengajuan ini.');
        }

        if (in_array($pengajuan->status, ['disetujui_wd2', 'disetujui_dekan', 'selesai'])) {
            return back()->with('error', 'Pengajuan telah disahkan dan tidak dapat dibatalkan.');
        }

        // Hapus atau ubah status ke dibatalkan
        $pengajuan->update([
            'status' => 'dibatalkan',
            'catatan' => 'Dibatalkan oleh ' . ($isOwner ? 'pemohon' : 'admin') . ' pada ' . now()->format('d/m/Y H:i')
        ]);

        \Illuminate\Support\Facades\Cache::forget('dashboard_workflow_stats');
        \Illuminate\Support\Facades\Cache::forget('dashboard_reminder');

        $tab = $pengajuan->jenis_pengajuan === 'KP' ? 'kp' : 'kgb';
        return redirect()->route('pengajuan-karir.index', ['tab' => $tab])
            ->with('success', "Pengajuan {$pengajuan->jenis_pengajuan} berhasil dibatalkan.");
    }

    /**
     * Hapus pengajuan karir secara permanen (Khusus Admin / Reset Uji Coba)
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->isScreeningAdmin()) {
            abort(403, 'Hanya Administrator yang berwenang menghapus data pengajuan karir.');
        }

        $pengajuan = PengajuanKarir::findOrFail($id);
        $jenis = $pengajuan->jenis_pengajuan;
        $namaPegawai = $pengajuan->pegawai?->nama ?? 'Pegawai';

        $pengajuan->delete();

        \Illuminate\Support\Facades\Cache::forget('dashboard_workflow_stats');
        \Illuminate\Support\Facades\Cache::forget('dashboard_reminder');

        return back()->with('success', "Data pengajuan {$jenis} untuk {$namaPegawai} berhasil dihapus permanen.");
    }
}
