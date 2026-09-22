<?php

namespace App\Http\Controllers;

use App\Exports\LogbookExport;
use App\Http\Requests\Logbook\StoreLogbookRequest;
use App\Http\Requests\Logbook\UpdateLogbookRequest;
use App\Models\Logbook;
use App\Models\Pegawai;
use App\Services\LogbookService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LogbookController extends Controller
{
    public function __construct(
        protected LogbookService $service
    ) {}

    /**
     * Tampilan Logbook Kinerja Pegawai
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isPegawaiOnly = $user->hasRole('pegawai') && !$user->hasRole(['admin', 'pimpinan']);

        // Pastikan akun terhubung dengan pegawai
        $pegawaiId = $user->pegawai_id;
        if (!$pegawaiId) {
            // Jika admin dan tidak ada pegawai_id, arahkan ke dashboard verifikasi admin
            if ($user->hasRole(['admin', 'pimpinan'])) {
                return redirect()->route('admin.logbook.index');
            }
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        $pegawai = Pegawai::with(['unitKerja', 'jabatan'])->find($pegawaiId);
        if (!$pegawai) {
            return redirect()->route('dashboard')->with('error', 'Data pegawai tidak ditemukan.');
        }

        $month = (int) $request->get('bulan', Carbon::now()->month);
        $year = (int) $request->get('tahun', Carbon::now()->year);
        $status = $request->get('status', 'semua');
        $kategori = $request->get('kategori', 'semua');

        $statistics = $this->service->getPegawaiStatistics($pegawaiId, $month, $year);
        $logbooks = $this->service->getPegawaiQuery($pegawaiId, $month, $year, $status, $kategori)
            ->paginate(15)
            ->withQueryString();

        $kategoriList = Logbook::KATEGORI_LIST;

        return view('logbook.index', compact(
            'pegawai',
            'statistics',
            'logbooks',
            'month',
            'year',
            'status',
            'kategori',
            'kategoriList',
            'isPegawaiOnly'
        ));
    }

    /**
     * Form Tambah Logbook Baru
     */
    public function create(Request $request)
    {
        $user = $request->user();
        if (!$user->pegawai_id) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        $pegawai = Pegawai::find($user->pegawai_id);
        $kategoriList = Logbook::KATEGORI_LIST;
        $defaultDate = Carbon::now()->toDateString();

        return view('logbook.create', compact('pegawai', 'kategoriList', 'defaultDate'));
    }

    /**
     * Simpan Entri Logbook Baru
     */
    public function store(StoreLogbookRequest $request)
    {
        $user = $request->user();
        $pegawaiId = (int) $user->pegawai_id;

        if (!$pegawaiId) {
            return redirect()->back()->with('error', 'Akun belum terhubung dengan data pegawai.');
        }

        $logbook = $this->service->create(
            $request->validated(),
            $pegawaiId,
            $user->id,
            $request->file('file_lampiran')
        );

        $msg = $logbook->status === Logbook::STATUS_DIAJUKAN
            ? 'Aktivitas berhasil dicatat dan diajukan ke atasan untuk verifikasi.'
            : 'Aktivitas berhasil disimpan sebagai Draft.';

        return redirect()->route('logbook.index', [
            'bulan' => Carbon::parse($logbook->tanggal)->month,
            'tahun' => Carbon::parse($logbook->tanggal)->year,
        ])->with('success', $msg);
    }

    /**
     * Tampilkan Rincian Logbook
     */
    public function show(int $id, Request $request)
    {
        $logbook = Logbook::with(['pegawai.unitKerja', 'pegawai.jabatan', 'user', 'verifikator'])->findOrFail($id);
        $user = $request->user();

        // Otorisasi: Pegawai hanya boleh melihat miliknya sendiri, KECUALI jika atasan langsung dari pegawai tersebut
        if ($user->hasRole('pegawai') && !$user->hasRole(['admin', 'pimpinan'])) {
            $isOwn = $logbook->user_id === $user->id || $logbook->pegawai_id === $user->pegawai_id;
            $isAtasan = $user->isAtasan() && in_array($logbook->pegawai_id, $user->getBawahanIds());

            if (!$isOwn && !$isAtasan) {
                abort(403, 'Anda tidak memiliki hak akses melihat catatan logbook ini.');
            }
        }

        return view('logbook.show', compact('logbook'));
    }

    /**
     * Form Edit Logbook
     */
    public function edit(int $id, Request $request)
    {
        $logbook = Logbook::findOrFail($id);
        $user = $request->user();

        if (!$logbook->canEditBy($user)) {
            return redirect()->route('logbook.index')
                ->with('error', 'Catatan logbook ini tidak dapat diubah karena sudah diverifikasi atau dikunci.');
        }

        $kategoriList = Logbook::KATEGORI_LIST;

        return view('logbook.edit', compact('logbook', 'kategoriList'));
    }

    /**
     * Simpan Perubahan Logbook
     */
    public function update(int $id, UpdateLogbookRequest $request)
    {
        $logbook = Logbook::findOrFail($id);
        $user = $request->user();

        if (!$logbook->canEditBy($user)) {
            return redirect()->route('logbook.index')
                ->with('error', 'Catatan logbook ini tidak dapat diubah.');
        }

        $this->service->update(
            $logbook,
            $request->validated(),
            $request->file('file_lampiran')
        );

        $msg = $request->get('action') === 'diajukan'
            ? 'Perubahan logbook berhasil disimpan dan diajukan ulang ke atasan.'
            : 'Perubahan logbook berhasil disimpan.';

        return redirect()->route('logbook.index', [
            'bulan' => Carbon::parse($logbook->tanggal)->month,
            'tahun' => Carbon::parse($logbook->tanggal)->year,
        ])->with('success', $msg);
    }

    /**
     * Hapus Logbook (Draft Only)
     */
    public function destroy(int $id, Request $request)
    {
        $logbook = Logbook::findOrFail($id);
        $user = $request->user();

        if (!$logbook->canDeleteBy($user)) {
            return redirect()->back()
                ->with('error', 'Hanya catatan berstatus Draft yang dapat dihapus.');
        }

        $this->service->delete($logbook);

        return redirect()->back()->with('success', 'Catatan aktivitas logbook berhasil dihapus.');
    }

    /**
     * Ajukan 1 Logbook ke Atasan
     */
    public function submit(int $id, Request $request)
    {
        $logbook = Logbook::findOrFail($id);
        $user = $request->user();

        if ($logbook->user_id !== $user->id && !$user->hasRole('admin')) {
            abort(403);
        }

        $success = $this->service->submitSingle($logbook);
        if ($success) {
            return redirect()->back()->with('success', 'Aktivitas logbook berhasil diajukan ke atasan untuk verifikasi.');
        }

        return redirect()->back()->with('error', 'Logbook tidak dapat diajukan pada status saat ini.');
    }

    /**
     * Ajukan Semua Logbook Draft/Revisi Bulan Ini Sekaligus (Bulk Submit)
     */
    public function submitBulk(Request $request)
    {
        $user = $request->user();
        $pegawaiId = (int) $user->pegawai_id;

        if (!$pegawaiId) {
            return redirect()->back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $month = (int) $request->get('bulan', Carbon::now()->month);
        $year = (int) $request->get('tahun', Carbon::now()->year);

        $count = $this->service->submitBulk($pegawaiId, $month, $year);

        if ($count > 0) {
            return redirect()->back()->with('success', "Sebanyak {$count} aktivitas logbook berhasil diajukan ke atasan.");
        }

        return redirect()->back()->with('info', 'Tidak ada catatan draft atau revisi yang perlu diajukan pada periode ini.');
    }

    /**
     * Cetak PDF Laporan Logbook Bulanan Pegawai
     */
    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $pegawaiId = (int) ($request->get('pegawai_id') && $user->hasRole(['admin', 'pimpinan'])
            ? $request->get('pegawai_id')
            : $user->pegawai_id);

        if (!$pegawaiId) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');
        }

        $pegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan'])->findOrFail($pegawaiId);
        $month = (int) $request->get('bulan', Carbon::now()->month);
        $year = (int) $request->get('tahun', Carbon::now()->year);

        $logbooks = Logbook::with(['verifikator'])
            ->where('pegawai_id', $pegawaiId)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $statistics = $this->service->getPegawaiStatistics($pegawaiId, $month, $year);
        $namaBulan = Carbon::createFromDate($year, $month, 1)->locale('id')->isoFormat('MMMM Y');

        $pdf = Pdf::loadView('logbook.pdf', compact('pegawai', 'logbooks', 'statistics', 'month', 'year', 'namaBulan'))
            ->setPaper('a4', 'portrait');

        $filename = 'Logbook_' . str_replace(' ', '_', $pegawai->nama) . '_' . $namaBulan . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Ekspor Logbook ke File Excel
     */
    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $pegawaiId = (int) ($request->get('pegawai_id') && $user->hasRole(['admin', 'pimpinan'])
            ? $request->get('pegawai_id')
            : $user->pegawai_id);

        $month = (int) $request->get('bulan', Carbon::now()->month);
        $year = (int) $request->get('tahun', Carbon::now()->year);
        $status = $request->get('status');

        $filename = 'Logbook_' . ($pegawaiId ? "Pegawai_{$pegawaiId}_" : '') . "{$year}_{$month}.xlsx";

        return Excel::download(new LogbookExport($pegawaiId, $month, $year, null, $status), $filename);
    }
}
