<?php

namespace App\Http\Controllers;

use App\Models\Golongan;
use App\Models\Pegawai;
use App\Models\PengajuanKarir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengajuanKarirController extends Controller
{
    /**
     * Tampilkan daftar pengajuan mandiri pegawai atau daftar verifikasi jika admin/pimpinan
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isExecutive = $user->canAccessExecutiveKepegawaianMenus();
        $isPegawai = $user->hasRole('pegawai');

        $query = PengajuanKarir::with(['pegawai.unitKerja', 'pegawai.jabatan', 'pegawai.golongan', 'golonganLama', 'golonganTujuan']);

        // Jika hanya pegawai biasa, tampilkan miliknya sendiri
        if (!$isExecutive || $isPegawai) {
            $pegawaiId = $user->pegawai_id ?? $user->pegawai?->id;
            $query->where('pegawai_id', $pegawaiId);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_pengajuan', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuans = $query->latest()->paginate(15);
        $userPegawai = $user->pegawai;

        return view('pengajuan-karir.index', compact('pengajuans', 'isExecutive', 'userPegawai'));
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

        // Cek apakah pegawai PNS untuk KP (PPPK tidak memiliki KP reguler)
        $isPns = str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PNS') || 
                 (!str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PPPK') && !str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PHL'));

        if ($jenis === 'KP' && !$isPns) {
            return redirect()->route('pengajuan-karir.index')
                ->with('error', 'Sesuai UU ASN No. 20/2023, Kenaikan Pangkat reguler hanya berlaku bagi PNS. PPPK dapat mengajukan Kenaikan Gaji Berkala (KGB).');
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
     * Simpan Pengajuan Baru
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'jenis_pengajuan'    => 'required|in:KGB,KP',
            'periode_kp'         => 'nullable|string',
            'tahun_periode'      => 'nullable|integer',
            'golongan_tujuan_id' => 'nullable|exists:golongan,id',
            'tmt_baru'           => 'nullable|date',
            'file_sk_terakhir'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_skp_1'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_skp_2'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_karpeg'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_pak'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'file_pendukung'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'catatan_pegawai'    => 'nullable|string|max:500',
        ]);

        $data = [
            'pegawai_id'       => $pegawai->id,
            'jenis_pengajuan'  => $request->jenis_pengajuan,
            'status'           => 'diajukan',
            'catatan_pegawai'  => $request->catatan_pegawai,
        ];

        if ($request->jenis_pengajuan === 'KP') {
            $data['periode_kp']         = $request->periode_kp ?? 'Februari';
            $data['tahun_periode']      = $request->tahun_periode ?? date('Y');
            $data['golongan_lama_id']   = $pegawai->golongan_id;
            $data['golongan_tujuan_id'] = $request->golongan_tujuan_id;
            $data['tmt_lama']           = $pegawai->tmt_pangkat_terakhir;
        } else {
            $data['tmt_lama']           = $pegawai->tmt_kgb_terakhir;
            $data['tmt_baru']           = $request->tmt_baru ?? now()->toDateString();
            $data['mkg_tahun']          = $pegawai->mkg_tahun ?? 0;
            $data['mkg_bulan']          = $pegawai->mkg_bulan ?? 0;
            $data['gaji_pokok_lama']    = 4256600;
            $data['gaji_pokok_baru']    = 4390700;
        }

        // Upload berkas jika dilampirkan
        $uploadFields = ['file_sk_terakhir', 'file_skp_1', 'file_skp_2', 'file_karpeg', 'file_pak', 'file_pendukung'];
        foreach ($uploadFields as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('pengajuan_karir/' . strtolower($request->jenis_pengajuan), 'public');
                $data[$field] = $path;
            }
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

        return view('pengajuan-karir.show', compact('pengajuan', 'isExecutive'));
    }

    /**
     * Proses Verifikasi & Paraf Bertingkat (Kabag -> WD II -> Dekan)
     */
    public function verifikasi(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->canAccessExecutiveKepegawaianMenus()) {
            abort(403, 'Akses terbatas untuk Pimpinan dan Pengelola Kepegawaian.');
        }

        $pengajuan = PengajuanKarir::findOrFail($id);
        $tahap = $request->input('tahap'); // paraf_kabag, paraf_wd2, ttd_dekan, tolak
        $catatan = $request->input('catatan');

        $pejabatLogin = $user->pegawai ?? Pegawai::where('nama', 'like', '%' . $user->name . '%')->first();

        if ($tahap === 'paraf_kabag') {
            $pengajuan->update([
                'status'              => 'diverifikasi_kabag',
                'paraf_kabag_at'      => now(),
                'paraf_kabag_by'      => $pejabatLogin?->id,
                'catatan_verifikator' => $catatan ?? 'Telah diparaf dan diverifikasi kelengkapan berkas oleh Kepala Bagian Umum.',
            ]);
            $msg = 'Paraf Kepala Bagian Umum berhasil dibubuhkan.';
        } elseif ($tahap === 'paraf_wd2') {
            $pengajuan->update([
                'status'              => 'diverifikasi_wd2',
                'paraf_wd2_at'        => now(),
                'paraf_wd2_by'        => $pejabatLogin?->id,
                'catatan_verifikator' => $catatan ?? 'Telah diparaf dan disetujui oleh Wakil Dekan Bidang Keuangan dan Umum.',
            ]);
            $msg = 'Paraf Wakil Dekan II berhasil dibubuhkan.';
        } elseif ($tahap === 'ttd_dekan') {
            $pengajuan->update([
                'status'              => 'disetujui_dekan',
                'ttd_dekan_at'        => now(),
                'ttd_dekan_by'        => $pejabatLogin?->id,
                'catatan_verifikator' => $catatan ?? 'Disetujui secara resmi oleh Dekan Fakultas Keperawatan.',
            ]);
            $msg = 'Pengajuan resmi disetujui oleh Dekan.';
        } elseif ($tahap === 'tolak') {
            $pengajuan->update([
                'status'              => 'ditolak',
                'catatan_verifikator' => $catatan ?? 'Berkas dikembalikan untuk diperbaiki.',
            ]);
            $msg = 'Pengajuan ditolak / dikembalikan ke pegawai.';
        } else {
            return back()->with('error', 'Tahap verifikasi tidak dikenali.');
        }

        return back()->with('success', $msg);
    }
}
