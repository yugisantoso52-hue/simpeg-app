<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\PengajuanJabatan;
use App\Models\RiwayatJabatan;
use App\Models\UnitKerja;
use App\Services\PegawaiService;
use App\Services\PegawaiStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanJabatanController extends Controller
{
    /**
     * Tampilkan daftar pengajuan jabatan
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $canVerify = $user->canVerifyJabatan();

        if ($canVerify) {
            $status = $request->get('status', 'menunggu');
            $query = PengajuanJabatan::with(['pegawai', 'jabatan', 'unitKerja', 'verifikator']);

            if ($status !== 'semua') {
                $query->where('status', $status);
            }

            $pengajuans = $query->latest()->paginate(15);
        } else {
            $pengajuans = PengajuanJabatan::with(['jabatan', 'unitKerja', 'verifikator'])
                ->where('pegawai_id', $user->pegawai_id)
                ->latest()
                ->paginate(15);
        }

        $jabatanList = Jabatan::orderBy('nama_jabatan')->get();
        $unitKerjaList = UnitKerja::orderBy('urutan')->get();

        return view('kepegawaian.pengajuan_jabatan.index', compact(
            'pengajuans',
            'canVerify',
            'jabatanList',
            'unitKerjaList'
        ));
    }

    /**
     * Simpan pengajuan jabatan baru oleh Pegawai / Admin
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $pegawaiId = $user->hasRole('admin') ? ($request->input('pegawai_id') ?? $user->pegawai_id) : $user->pegawai_id;

        if (!$pegawaiId) {
            return back()->with('error', 'Akun Anda tidak terhubung dengan profil pegawai.');
        }

        $validated = $request->validate([
            'jabatan_id'    => 'required|exists:jabatan,id',
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'nomor_sk'      => 'required|string|max:100',
            'tanggal_sk'    => 'required|date',
            'tmt_jabatan'   => 'required|date',
            'file_sk'       => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'catatan'       => 'nullable|string|max:500',
        ]);

        $pegawai = Pegawai::findOrFail($pegawaiId);

        $path = null;
        if ($request->hasFile('file_sk')) {
            $path = PegawaiStorageService::store($request->file('file_sk'), $pegawai, 'sk_jabatan');
        }

        PengajuanJabatan::create([
            'pegawai_id'    => $pegawai->id,
            'jabatan_id'    => $validated['jabatan_id'],
            'unit_kerja_id' => $validated['unit_kerja_id'],
            'nomor_sk'      => $validated['nomor_sk'],
            'tanggal_sk'    => $validated['tanggal_sk'],
            'tmt_jabatan'   => $validated['tmt_jabatan'],
            'file_sk'       => $path,
            'status'        => PengajuanJabatan::STATUS_MENUNGGU,
            'diajukan_oleh' => $user->id,
            'catatan'       => $validated['catatan'] ?? null,
        ]);

        return back()->with('success', 'Pengajuan jabatan struktural berhasil dikirim. Menunggu verifikasi SK oleh Admin / Ka Pokja Kepegawaian.');
    }

    /**
     * Verifikasi pengajuan jabatan (Disetujui / Ditolak) oleh Admin atau Ka Pokja Keu-Kepeg
     */
    public function verifikasi(Request $request, int $id)
    {
        $user = auth()->user();
        if (!$user->canVerifyJabatan()) {
            abort(403, 'Hanya Administrator dan Ka Pokja Kepegawaian & Keuangan yang berhak memverifikasi SK Jabatan.');
        }

        $request->validate([
            'aksi'    => 'required|in:setujui,tolak',
            'catatan' => 'nullable|string|max:500',
        ]);

        $pengajuan = PengajuanJabatan::with('pegawai')->findOrFail($id);

        if ($pengajuan->status !== PengajuanJabatan::STATUS_MENUNGGU) {
            return back()->with('error', 'Pengajuan ini sudah pernah diproses sebelumnya.');
        }

        $aksi = $request->input('aksi');
        $catatan = $request->input('catatan');

        DB::transaction(function () use ($pengajuan, $aksi, $catatan, $user) {
            if ($aksi === 'setujui') {
                $pengajuan->update([
                    'status'            => PengajuanJabatan::STATUS_DISETUJUI,
                    'diverifikasi_oleh' => $user->id,
                    'diverifikasi_at'   => now(),
                    'catatan'           => $catatan,
                ]);

                $pegawai = $pengajuan->pegawai;

                // 1. Nonaktifkan jabatan aktif sebelumnya di riwayat
                $pegawai->riwayatJabatan()->update(['status' => 'nonaktif']);

                // 2. Buat riwayat jabatan aktif baru
                $pegawai->riwayatJabatan()->create([
                    'jabatan_id'    => $pengajuan->jabatan_id,
                    'unit_kerja_id' => $pengajuan->unit_kerja_id,
                    'nomor_sk'      => $pengajuan->nomor_sk,
                    'tanggal_sk'    => $pengajuan->tanggal_sk,
                    'tmt_jabatan'   => $pengajuan->tmt_jabatan,
                    'file_sk'       => $pengajuan->file_sk,
                    'status'        => 'aktif',
                    'keterangan'    => 'Diverifikasi dari pengajuan SK jabatan #' . $pengajuan->id,
                ]);

                // 3. Sinkronkan ke data induk pegawai
                $pegawai->jabatan_id = $pengajuan->jabatan_id;
                $pegawai->unit_kerja_id = $pengajuan->unit_kerja_id;
                $pegawai->save();

                app(PegawaiService::class)->syncPegawaiFromHistories($pegawai);
            } else {
                $pengajuan->update([
                    'status'            => PengajuanJabatan::STATUS_DITOLAK,
                    'diverifikasi_oleh' => $user->id,
                    'diverifikasi_at'   => now(),
                    'catatan'           => $catatan ?: 'Pengajuan ditolak setelah verifikasi berkas SK.',
                ]);
            }
        });

        $msg = $aksi === 'setujui'
            ? 'Pengajuan jabatan berhasil disetujui! Data jabatan pegawai dan struktur organisasi telah otomatis terupdate.'
            : 'Pengajuan jabatan telah ditolak.';

        return back()->with('success', $msg);
    }
}
