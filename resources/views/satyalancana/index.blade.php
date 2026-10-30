<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Penghargaan Satyalancana"
            subtitle="Daftar pegawai yang berhak menerima penghargaan Satyalancana Karya Satya (10, 20, 30 Tahun) & Manajemen Usulan Resmi"
        >
            <div class="flex flex-wrap items-center gap-2">
                {{-- TOMBOL UNDUH SYARAT & JUKNIS RESMI --}}
                <a href="{{ route('satyalancana.download-syarat') }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Syarat & Dokumen Pengusulan (PDF)
                </a>

                {{-- TOMBOL CETAK SURAT PENGANTAR KOLEKTIF KE REKTORAT (MEMBUKA MODAL FORM DINAMIS) --}}
                @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                    <button type="button"
                            @click="$dispatch('open-modal-surat-pengantar')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak Surat Pengantar Usulan (Kolektif)
                    </button>
                @endif
            </div>
        </x-enterprise.page-header>
    </x-slot>

    <div class="py-6" x-data="{ 
        modalSuratOpen: false,
        modalBerkasOpen: false,
        currentPegawai: null
    }" 
    @open-modal-surat-pengantar.window="modalSuratOpen = true"
    @open-modal-berkas.window="currentPegawai = $event.detail; modalBerkasOpen = true">

        <div class="w-full max-w-[1920px] mx-auto space-y-6 px-3 sm:px-6 lg:px-8 xl:px-10">

            @if(session('success'))
                <div class="rounded-xl border border-emerald-300 bg-emerald-50 px-5 py-4 text-emerald-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="text-emerald-700 font-bold hover:text-emerald-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @if(session('warning'))
                <div class="rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 text-amber-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button type="button" class="text-amber-700 font-bold hover:text-amber-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- NAVIGASI TAB CLUSTER LAYANAN KARIR & SATYALANCANA --}}
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
                <a href="{{ route('pengajuan-karir.index') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs bg-white text-slate-700 hover:bg-slate-50 border border-slate-200">
                    <span>📋 Pusat Verifikasi Usulan Karir</span>
                </a>
                <a href="{{ route('kp.index') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs bg-white text-slate-700 hover:bg-slate-50 border border-slate-200">
                    <span>🎖️ Kenaikan Pangkat (KP)</span>
                </a>
                <a href="{{ route('kgb.index') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs bg-white text-slate-700 hover:bg-slate-50 border border-slate-200">
                    <span>💰 Gaji Berkala (KGB)</span>
                </a>
                <a href="{{ route('satyalancana.index') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs bg-indigo-700 text-white shadow-indigo-600/20">
                    <span>🏆 Satyalancana Karya Satya</span>
                </a>
            </div>

            {{-- ACTION CARD: TOMBOL CEPAT & INFORMASI DOKUMEN KOLEKTIF --}}
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 rounded-2xl p-6 text-white shadow-lg border border-slate-700">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                    <div class="space-y-1.5 max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-500/30 text-blue-200 border border-blue-400/30">
                            🏛️ Prosedur Resmi Pengusulan Tanda Kehormatan Satyalancana Karya Satya
                        </div>
                        <h3 class="text-lg font-bold text-white tracking-wide">Penerbitan Dokumen Pengusulan & Koordinasi Tata Naskah</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Alur berjenjang: <strong>Admin (Screening 7 Berkas Syarat) &rarr; Ka Pokja Keu-Kepeg (Paraf 1) &rarr; Kepala Bagian Umum (Paraf 2) &rarr; Wakil Dekan II (TTE Pengesahan) &rarr; Pengiriman Berkas Kolektif ke Rektorat / BKN</strong>.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                                @click="modalSuratOpen = true"
                                class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-500 text-white shadow-md transition transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Format Surat Pengantar Kolektif
                        </button>
                        <a href="{{ route('satyalancana.download-syarat') }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-600 transition">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Juknis & Persyaratan
                        </a>
                    </div>
                </div>
            </div>

            {{-- 2. DAFTAR PEGAWAI LAYAK MENDAPATKAN PENGHARGAAN (RADAR SISTEM) --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Daftar Nominasi Pegawai Memenuhi Kriteria</h3>
                            <p class="text-xs text-slate-500">Pegawai yang telah mengabdi tanpa cela selama 10, 20, atau 30 tahun dan berhak diusulkan.</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            {{ count($layakSatyalancana) }} Pegawai Memenuhi Syarat
                        </span>
                    </div>

                    <x-enterprise.data-table title="Daftar Nominasi Satyalancana">
                        <x-slot name="head">
                            <tr>
                                <th class="w-12 px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3 text-left">NIP & Nama Pegawai</th>
                                <th class="px-4 py-3 text-left">Unit Kerja & Golongan</th>
                                <th class="px-4 py-3 text-center">TMT CPNS / Pengangkatan</th>
                                <th class="px-4 py-3 text-center">Kategori Kelayakan</th>
                                <th class="px-4 py-3 text-center">Pemeriksaan Berkas & Aksi</th>
                            </tr>
                        </x-slot>

                        @php
                            $cekFileFisikValid = function(?string $path): bool {
                                if (empty($path) || $path === '-') return false;
                                $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
                                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($cleanPath)) {
                                    try { if (\Illuminate\Support\Facades\Storage::disk('local')->size($cleanPath) > 200) return true; } catch (\Throwable $e) {}
                                }
                                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                                    try { if (\Illuminate\Support\Facades\Storage::disk('public')->size($cleanPath) > 200) return true; } catch (\Throwable $e) {}
                                }
                                $p1 = storage_path('app/private/' . $cleanPath);
                                if (file_exists($p1) && @filesize($p1) > 200) return true;
                                $p2 = storage_path('app/public/' . $cleanPath);
                                if (file_exists($p2) && @filesize($p2) > 200) return true;
                                return false;
                            };
                        @endphp

                        @forelse($layakSatyalancana as $row)
                            @php
                                $skpTerbaru = $row->riwayatSkp->take(2);
                                $skpCount = $skpTerbaru->count();
                                $skpItems = [];
                                foreach ($skpTerbaru as $skp) {
                                    $evalValid = $cekFileFisikValid($skp->file_evaluasi_skp);
                                    $rencValid = $cekFileFisikValid($skp->file_rencana_skp);
                                    $skpItems[] = [
                                        'tahun' => $skp->tahun,
                                        'predikat' => $skp->predikat_kinerja,
                                        'has_file' => $evalValid || $rencValid,
                                        'evaluasi_url' => $evalValid ? route('document.preview', ['path' => $skp->file_evaluasi_skp]) : null,
                                        'rencana_url' => $rencValid ? route('document.preview', ['path' => $skp->file_rencana_skp]) : null,
                                    ];
                                }
                                $skpStatusText = $skpCount >= 2 
                                    ? ($skpTerbaru->pluck('predikat_kinerja')->join(', ')) 
                                    : ($skpCount == 1 ? $skpTerbaru->first()->predikat_kinerja . ' (1 Thn)' : 'Belum Terdata');

                                $pangkatTerakhir = $row->riwayatPangkat->first();
                                $jabatanTerakhir = $row->riwayatJabatan->first();
                                $piagamList = $row->riwayatPenghargaan->where('jenis_penghargaan', 'Satyalancana');
                                $latestPiagam = $piagamList->first();

                                $skCpnsPath = $row->file_sk_pertama;
                                $skCpnsUrl = $cekFileFisikValid($skCpnsPath) ? route('document.preview', ['path' => $skCpnsPath]) : null;

                                $pangkatRaw = $pangkatTerakhir?->file_sk ?: $row->file_sk_pangkat_terakhir;
                                $skPangkatUrl = $cekFileFisikValid($pangkatRaw) ? route('document.preview', ['path' => $pangkatRaw]) : null;

                                $jabatanRaw = $jabatanTerakhir?->file_sk;
                                $skJabatanUrl = $cekFileFisikValid($jabatanRaw) ? route('document.preview', ['path' => $jabatanRaw]) : null;

                                $piagamRaw = $latestPiagam?->file_sk;
                                $piagamUrl = $cekFileFisikValid($piagamRaw) ? route('document.preview', ['path' => $piagamRaw]) : null;

                                $usulanAktif = $row->usulan_aktif;
                                $usulanId = $usulanAktif?->id;
                                $usulanStatusRaw = $usulanAktif ? $usulanAktif->status : 'belum_diajukan';
                                $usulanStatusLabel = $usulanAktif ? ucwords(str_replace('_', ' ', $usulanAktif->status)) : 'Belum Diajukan';
                                
                                $payloadPegawai = [
                                    'id' => $row->id,
                                    'nama' => $row->nama_lengkap ?? $row->nama,
                                    'nip' => $row->nip,
                                    'kategori' => $row->kategori_satyalancana,
                                    'unit' => $row->unitKerja?->nama_unit ?? '-',
                                    'golongan' => ($row->golongan?->nama_golongan ?? '-') . ' (' . ($row->golongan?->kode_golongan ?? '-') . ')',
                                    'tmt_cpns' => $row->tmt_sk_pertama ? \Carbon\Carbon::parse($row->tmt_sk_pertama)->format('d-m-Y') : ($row->tanggal_masuk ? \Carbon\Carbon::parse($row->tanggal_masuk)->format('d-m-Y') : '-'),
                                    'no_sk_cpns' => $row->nomor_sk_pertama ?? '19597/A4/KP/2011',
                                    'pangkat_terakhir' => ($pangkatTerakhir?->golongan?->nama_pangkat ?? ($row->golongan?->nama_pangkat ?? '-')) . ' / SK: ' . ($pangkatTerakhir?->nomor_sk ?? ($row->nomor_sk_pangkat_terakhir ?? '-')),
                                    'jabatan_terakhir' => ($jabatanTerakhir?->jabatan?->nama_jabatan ?? ($row->jabatan?->nama_jabatan ?? '-')) . ' / SK: ' . ($jabatanTerakhir?->nomor_sk ?? '-'),
                                    'skp_status' => $skpStatusText,
                                    'skp_count' => $skpCount,
                                    'skp_items' => $skpItems,
                                    'hukdis_status' => 'Tidak Pernah dijatuhi Hukuman Disiplin Tingkat Sedang/Berat (Memenuhi Syarat)',
                                    'piagam_sebelumnya' => $piagamList->count() > 0 ? $piagamList->pluck('nama_penghargaan')->join(', ') : 'Belum ada / Usulan Pertama',
                                    'file_sk_cpns_url' => $skCpnsUrl,
                                    'file_sk_pangkat_url' => $skPangkatUrl,
                                    'file_sk_jabatan_url' => $skJabatanUrl,
                                    'file_piagam_url' => $piagamUrl,
                                    'url_upload_cpns' => route('pegawai.edit', $row->id),
                                    'url_upload_pangkat' => route('riwayat-pangkat.index'),
                                    'url_upload_jabatan' => route('riwayat-jabatan.index'),
                                    'url_upload_skp' => route('riwayat-skp.index'),
                                    'url_upload_piagam' => route('riwayat-penghargaan.index'),
                                    'url_tambah_piagam' => route('riwayat-penghargaan.create', ['pegawai_id' => $row->id]),
                                    'usulan_id' => $usulanId,
                                    'usulan_status' => $usulanStatusLabel,
                                    'usulan_status_raw' => $usulanStatusRaw,
                                    'verifikasi_admin_at' => $usulanAktif?->verifikasi_admin_at ? $usulanAktif->verifikasi_admin_at->format('d/m/Y H:i') : null,
                                    'paraf_kapokja_at' => $usulanAktif?->paraf_kapokja_at ? $usulanAktif->paraf_kapokja_at->format('d/m/Y H:i') : null,
                                    'paraf_kabag_at' => $usulanAktif?->paraf_kabag_at ? $usulanAktif->paraf_kabag_at->format('d/m/Y H:i') : null,
                                    'ttd_wd2_at' => $usulanAktif?->ttd_wd2_at ? $usulanAktif->ttd_wd2_at->format('d/m/Y H:i') : null,
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-center font-medium">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $row->nama_lengkap ?? $row->nama }}</div>
                                    <div class="text-xs text-slate-500">NIP: {{ $row->nip }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-800">{{ $row->unitKerja?->nama_unit ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $row->golongan?->nama_golongan ?? '-' }} ({{ $row->golongan?->kode_golongan ?? '-' }})</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm font-medium text-slate-700">
                                        {{ $row->tmt_sk_pertama ? \Carbon\Carbon::parse($row->tmt_sk_pertama)->format('d-m-Y') : ($row->tanggal_masuk ? \Carbon\Carbon::parse($row->tanggal_masuk)->format('d-m-Y') : '-') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-enterprise.badge 
                                        color="{{ $row->kategori_satyalancana == '30 Tahun' ? 'amber' : ($row->kategori_satyalancana == '20 Tahun' ? 'blue' : 'green') }}">
                                        Satyalancana {{ $row->kategori_satyalancana }}
                                    </x-enterprise.badge>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        {{-- TOMBOL CEK SYARAT BERKAS 7 POIN RESMI --}}
                                        <button type="button"
                                                @click="$dispatch('open-modal-berkas', {{ json_encode($payloadPegawai) }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 border border-slate-300 hover:bg-slate-200 transition shadow-2xs"
                                                title="Periksa 7 Berkas Persyaratan Satyalancana">
                                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Cek Berkas Syarat
                                        </button>

                                        {{-- CETAK DOKUMEN DRH RESMI DENGAN KOP STANDAR CUTI & TTE --}}
                                        <a href="{{ route('satyalancana.cetak-drh', $row->id) }}"
                                           target="_blank"
                                           title="Cetak Daftar Riwayat Hidup Format 12 Poin BKN/Setmilpres"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            Cetak DRH
                                        </a>

                                        {{-- AJUKAN USULAN RESMI HANYA OLEH ADMIN KEPEGAWAIAN --}}
                                        @if($row->usulan_aktif)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-100 text-emerald-800">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                {{ ucfirst($row->usulan_aktif->status) }}
                                            </span>
                                        @elseif($isAdmin)
                                            <form method="POST" action="{{ route('satyalancana.ajukan', $row->id) }}" onsubmit="return confirm('Ajukan usulan resmi tanda kehormatan Satyalancana {{ $row->kategori_satyalancana }} untuk {{ $row->nama }}?')">
                                                @csrf
                                                <input type="hidden" name="kategori" value="{{ $row->kategori_satyalancana }}">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Ajukan Usulan
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-medium rounded-lg bg-slate-100 text-slate-500 border border-slate-200">
                                                Belum Diusulkan
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-enterprise.empty-state
                                        title="Tidak Ada Usulan Satyalancana"
                                        description="Belum ada pegawai yang memenuhi kriteria pengabdian 10, 20, atau 30 tahun."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-enterprise.data-table>
                </div>
            </x-enterprise.card>

            {{-- 3. RIWAYAT USULAN RESMI & WORKFLOW APPROVAL / PARAF --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Tracking Berkas & Koordinasi Tata Naskah Usulan</h3>
                            <p class="text-xs text-slate-500">Monitoring status verifikasi berkas: Admin &rarr; Ka Pokja Keu-Kepeg (Paraf 1) &rarr; Kabag Umum (Paraf 2) &rarr; TTE WD2.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase">
                                <tr>
                                    <th class="px-4 py-3">No. Usulan & Periode</th>
                                    <th class="px-4 py-3">Nama Pegawai & NIP</th>
                                    <th class="px-4 py-3 text-center">Kategori</th>
                                    <th class="px-4 py-3 text-center">Progress Paraf & TTE</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi & Paraf Digital</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($daftarUsulan as $usulan)
                                    @php
                                        $pPegawai = $usulan->pegawai;
                                        $uSkpTerbaru = $pPegawai->riwayatSkp->take(2);
                                        $uSkpCount = $uSkpTerbaru->count();
                                        $uSkpItems = [];
                                        foreach ($uSkpTerbaru as $skp) {
                                            $uEvalValid = $cekFileFisikValid($skp->file_evaluasi_skp);
                                            $uRencValid = $cekFileFisikValid($skp->file_rencana_skp);
                                            $uSkpItems[] = [
                                                'tahun' => $skp->tahun,
                                                'predikat' => $skp->predikat_kinerja,
                                                'has_file' => $uEvalValid || $uRencValid,
                                                'evaluasi_url' => $uEvalValid ? route('document.preview', ['path' => $skp->file_evaluasi_skp]) : null,
                                                'rencana_url' => $uRencValid ? route('document.preview', ['path' => $skp->file_rencana_skp]) : null,
                                            ];
                                        }
                                        $uSkpStatusText = $uSkpCount >= 2 
                                            ? ($uSkpTerbaru->pluck('predikat_kinerja')->join(', ')) 
                                            : ($uSkpCount == 1 ? $uSkpTerbaru->first()->predikat_kinerja . ' (1 Thn)' : 'Belum Terdata');

                                        $uPangkatTerakhir = $pPegawai->riwayatPangkat->first();
                                        $uJabatanTerakhir = $pPegawai->riwayatJabatan->first();
                                        $uPiagamList = $pPegawai->riwayatPenghargaan->where('jenis_penghargaan', 'Satyalancana');
                                        $uLatestPiagam = $uPiagamList->first();

                                        $uSkCpnsPath = $pPegawai->file_sk_pertama;
                                        $uSkCpnsUrl = $cekFileFisikValid($uSkCpnsPath) ? route('document.preview', ['path' => $uSkCpnsPath]) : null;

                                        $uPangkatRaw = $uPangkatTerakhir?->file_sk ?: $pPegawai->file_sk_pangkat_terakhir;
                                        $uSkPangkatUrl = $cekFileFisikValid($uPangkatRaw) ? route('document.preview', ['path' => $uPangkatRaw]) : null;

                                        $uJabatanRaw = $uJabatanTerakhir?->file_sk;
                                        $uSkJabatanUrl = $cekFileFisikValid($uJabatanRaw) ? route('document.preview', ['path' => $uJabatanRaw]) : null;

                                        $uPiagamRaw = $uLatestPiagam?->file_sk;
                                        $uPiagamUrl = $cekFileFisikValid($uPiagamRaw) ? route('document.preview', ['path' => $uPiagamRaw]) : null;

                                        $payloadUsulan = [
                                            'id' => $pPegawai->id,
                                            'nama' => $pPegawai->nama_lengkap ?? $pPegawai->nama,
                                            'nip' => $pPegawai->nip,
                                            'kategori' => $usulan->kategori,
                                            'unit' => $pPegawai->unitKerja?->nama_unit ?? '-',
                                            'golongan' => ($pPegawai->golongan?->nama_golongan ?? '-') . ' (' . ($pPegawai->golongan?->kode_golongan ?? '-') . ')',
                                            'tmt_cpns' => $pPegawai->tmt_sk_pertama ? \Carbon\Carbon::parse($pPegawai->tmt_sk_pertama)->format('d-m-Y') : ($pPegawai->tanggal_masuk ? \Carbon\Carbon::parse($pPegawai->tanggal_masuk)->format('d-m-Y') : '-'),
                                            'no_sk_cpns' => $pPegawai->nomor_sk_pertama ?? '19597/A4/KP/2011',
                                            'pangkat_terakhir' => ($uPangkatTerakhir?->golongan?->nama_pangkat ?? ($pPegawai->golongan?->nama_pangkat ?? '-')) . ' / SK: ' . ($uPangkatTerakhir?->nomor_sk ?? ($pPegawai->nomor_sk_pangkat_terakhir ?? '-')),
                                            'jabatan_terakhir' => ($uJabatanTerakhir?->jabatan?->nama_jabatan ?? ($pPegawai->jabatan?->nama_jabatan ?? '-')) . ' / SK: ' . ($uJabatanTerakhir?->nomor_sk ?? '-'),
                                            'skp_status' => $uSkpStatusText,
                                            'skp_count' => $uSkpCount,
                                            'skp_items' => $uSkpItems,
                                            'hukdis_status' => 'Tidak Pernah dijatuhi Hukuman Disiplin Tingkat Sedang/Berat (Memenuhi Syarat)',
                                            'piagam_sebelumnya' => $uPiagamList->count() > 0 ? $uPiagamList->pluck('nama_penghargaan')->join(', ') : 'Belum ada / Usulan Pertama',
                                            'file_sk_cpns_url' => $uSkCpnsUrl,
                                            'file_sk_pangkat_url' => $uSkPangkatUrl,
                                            'file_sk_jabatan_url' => $uSkJabatanUrl,
                                            'file_piagam_url' => $uPiagamUrl,
                                            'url_upload_cpns' => route('pegawai.edit', $pPegawai->id),
                                            'url_upload_pangkat' => route('riwayat-pangkat.index'),
                                            'url_upload_jabatan' => route('riwayat-jabatan.index'),
                                            'url_upload_skp' => route('riwayat-skp.index'),
                                            'url_upload_piagam' => route('riwayat-penghargaan.index'),
                                            'url_tambah_piagam' => route('riwayat-penghargaan.create', ['pegawai_id' => $pPegawai->id]),
                                            'usulan_id' => $usulan->id,
                                            'usulan_status' => ucwords(str_replace('_', ' ', $usulan->status)),
                                            'usulan_status_raw' => $usulan->status,
                                            'verifikasi_admin_at' => $usulan->verifikasi_admin_at ? $usulan->verifikasi_admin_at->format('d/m/Y H:i') : null,
                                            'paraf_kapokja_at' => $usulan->paraf_kapokja_at ? $usulan->paraf_kapokja_at->format('d/m/Y H:i') : null,
                                            'paraf_kabag_at' => $usulan->paraf_kabag_at ? $usulan->paraf_kabag_at->format('d/m/Y H:i') : null,
                                        ];
                                    @endphp
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3" x-data="{ editNomorSL: false, nomorInputSL: '{{ explode('/UN19', $usulan->nomor_surat_usulan)[0] }}' }">
                                            <div class="flex items-center gap-1.5 font-mono text-xs font-bold text-slate-800">
                                                <span>{{ $usulan->nomor_surat_usulan }}</span>
                                                @if($isAdmin ?? false)
                                                    <button type="button" @click="editNomorSL = true" class="text-blue-600 hover:text-blue-800 p-0.5 rounded hover:bg-blue-50 text-[11px]" title="Ubah Nomor Surat Usulan (Admin)">
                                                        ✏️
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-blue-600 font-medium">{{ $usulan->periode_usulan ?? ('Tahun ' . $usulan->tahun_usulan) }}</div>

                                            @if($isAdmin ?? false)
                                                <!-- Modal Edit Nomor Surat Satyalancana Khusus Admin -->
                                                <div x-show="editNomorSL" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                                                    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-4 text-left" @click.outside="editNomorSL = false">
                                                        <h4 class="text-sm font-bold text-slate-800 mb-1">Penomoran Usulan Satyalancana (Admin)</h4>
                                                        <p class="text-xs text-slate-500 mb-3">Tuliskan nomor urut dinas resmi untuk usulan an. <strong>{{ $usulan->pegawai?->nama ?? '-' }}</strong>.</p>
                                                        <form action="{{ route('satyalancana.update-nomor', $usulan->id) }}" method="POST">
                                                            @csrf
                                                            <div class="flex rounded-lg shadow-sm mb-3">
                                                                <input type="text" name="nomor_surat_input" x-model="nomorInputSL" required
                                                                       class="flex-1 min-w-0 px-3 py-2 text-sm font-mono rounded-l-lg border-slate-300">
                                                                <span class="inline-flex items-center px-2 rounded-r-lg border border-l-0 border-slate-300 bg-slate-100 text-slate-600 text-xs font-mono font-semibold">
                                                                    /UN19.5.1.1.10/KP/{{ $usulan->tahun_usulan ?: date('Y') }}
                                                                </span>
                                                            </div>
                                                            <div class="flex justify-end gap-2">
                                                                <button type="button" @click="editNomorSL = false" class="px-3 py-1.5 rounded-lg text-xs bg-slate-100 text-slate-700 hover:bg-slate-200">Batal</button>
                                                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 text-white hover:bg-blue-700">Simpan Nomor</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-slate-900">{{ $usulan->pegawai?->nama_lengkap ?? $usulan->pegawai?->nama ?? '-' }}</div>
                                            <div class="text-xs text-slate-500">NIP: {{ $usulan->pegawai?->nip ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <x-enterprise.badge color="{{ $usulan->kategori == '30 Tahun' ? 'amber' : ($usulan->kategori == '20 Tahun' ? 'blue' : 'green') }}">
                                                {{ $usulan->kategori }}
                                            </x-enterprise.badge>
                                        </td>
                                        
                                        {{-- STEP-BY-STEP PROGRESS INDIKATOR PARAF: ADMIN -> POKJA -> KABAG -> WD2 --}}
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1 text-[10px]">
                                                {{-- 1. Verifikasi Admin --}}
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded font-semibold {{ $usulan->verifikasi_admin_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}" title="1. Screening Admin">
                                                    Admin {{ $usulan->verifikasi_admin_at ? '✓' : '-' }}
                                                </span>
                                                &rarr;
                                                {{-- 2. Paraf Ka Pokja Keu-Kepeg --}}
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded font-semibold {{ $usulan->paraf_kapokja_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}" title="2. Paraf 1: Ka Pokja Keu-Kepeg">
                                                    Pokja {{ $usulan->paraf_kapokja_at ? '✓' : '-' }}
                                                </span>
                                                &rarr;
                                                {{-- 3. Paraf Kabag Umum --}}
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded font-semibold {{ $usulan->paraf_kabag_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}" title="3. Paraf 2: Kepala Bagian Umum">
                                                    Kabag {{ $usulan->paraf_kabag_at ? '✓' : '-' }}
                                                </span>
                                                &rarr;
                                                {{-- 4. TTE WD2 --}}
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded font-semibold {{ $usulan->ttd_wd2_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}" title="4. TTE Pimpinan: Wakil Dekan II">
                                                    WD2 {{ $usulan->ttd_wd2_at ? '✓' : '-' }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            @if($usulan->status === 'disetujui_wd2' || $usulan->status === 'selesai')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                    ● Sah (Disetujui WD2)
                                                </span>
                                            @elseif($usulan->status === 'diparaf_kabag')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                    ● Menunggu TTE WD2
                                                </span>
                                            @elseif($usulan->status === 'diparaf_kapokja')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                    ● Menunggu Paraf Kabag
                                                </span>
                                            @elseif($usulan->status === 'diverifikasi_admin')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                    ● Menunggu Paraf Pokja
                                                </span>
                                            @elseif($usulan->status === 'ditolak')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                    ● Ditolak
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                    ● Diajukan
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                {{-- TOMBOL CEK BERKAS PERSYARATAN --}}
                                                <button type="button"
                                                        @click="$dispatch('open-modal-berkas', {{ json_encode($payloadUsulan) }})"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded bg-slate-100 text-slate-700 border border-slate-300 hover:bg-slate-200 transition shadow-2xs"
                                                        title="Periksa 7 Berkas Persyaratan & Verifikasi Langsung">
                                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    Cek Berkas
                                                </button>

                                                {{-- CETAK DRH DENGAN TTE & PARAF --}}
                                                <a href="{{ route('satyalancana.cetak-drh', $usulan->pegawai_id) }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded hover:bg-indigo-100 shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                    DRH PDF
                                                </a>

                                                {{-- TOMBOL HAPUS PERMANEN KHUSUS ADMIN (RESET UJI COBA) --}}
                                                @if($isAdmin)
                                                    <form method="POST" action="{{ route('satyalancana.destroy', $usulan->id) }}" onsubmit="return confirm('Hapus permanen berkas usulan Satyalancana ini?')" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1 rounded text-xs font-semibold bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition shadow-2xs" title="Hapus Berkas / Reset Uji Coba (Admin)">
                                                            🗑️
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- URUTAN TOMBOL VERIFIKASI BERTINGKAT: ADMIN -> POKJA -> KABAG -> WD2 (TERKUNCI HINGGA TAHAP SEBELUMNYA SELESAI) --}}
                                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                                    {{-- 1. ADMIN SCREENING BERKAS --}}
                                                    @if($usulan->verifikasi_admin_at)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="1. Admin Screening Berkas: Selesai pada {{ $usulan->verifikasi_admin_at->format('d/m/Y H:i') }}">
                                                            ✓ 1. Admin
                                                        </span>
                                                    @elseif($isAdmin)
                                                        <form method="POST" action="{{ route('satyalancana.verifikasi', $usulan->id) }}" onsubmit="return confirm('Verifikasi kelengkapan 7 berkas persyaratan oleh Admin Kepegawaian?')">
                                                            @csrf
                                                            <input type="hidden" name="tahap" value="verifikasi_admin">
                                                            <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-2xs transition" title="1. Verifikasi berkas kelayakan oleh Admin">
                                                                ✓ 1. Screening
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" disabled class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu screening oleh Admin Kepegawaian">
                                                            ⏳ 1. Admin
                                                        </button>
                                                    @endif

                                                    {{-- 2. PARAF POKJA --}}
                                                    @if($usulan->paraf_kapokja_at)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="2. Paraf Ka Pokja: Selesai pada {{ $usulan->paraf_kapokja_at->format('d/m/Y H:i') }}">
                                                            ✓ 2. Pokja
                                                        </span>
                                                    @elseif(!$usulan->verifikasi_admin_at)
                                                        <button type="button" disabled class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 cursor-not-allowed opacity-60" title="🔒 Terkunci: Menunggu Admin Screening Berkas">
                                                            🔒 2. Pokja
                                                        </button>
                                                    @elseif($isKaPokja)
                                                        <form method="POST" action="{{ route('satyalancana.verifikasi', $usulan->id) }}" onsubmit="return confirm('Bubuhkan Paraf 1 (Ka Pokja Keu-Kepeg)?')">
                                                            @csrf
                                                            <input type="hidden" name="tahap" value="paraf_kapokja">
                                                            <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-teal-600 hover:bg-teal-700 text-white shadow-2xs transition" title="2. Bubuhkan Paraf Ka Pokja Keu-Kepeg">
                                                                ✒ 2. Pokja
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" disabled class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu tindakan Ka Pokja Keu-Kepeg">
                                                            ⏳ 2. Pokja
                                                        </button>
                                                    @endif

                                                    {{-- 3. PARAF KABAG --}}
                                                    @if($usulan->paraf_kabag_at)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="3. Paraf Kabag Umum: Selesai pada {{ $usulan->paraf_kabag_at->format('d/m/Y H:i') }}">
                                                            ✓ 3. Kabag
                                                        </span>
                                                    @elseif(!$usulan->paraf_kapokja_at)
                                                        <button type="button" disabled class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 cursor-not-allowed opacity-60" title="🔒 Terkunci: Menunggu Paraf Ka Pokja Keu-Kepeg">
                                                            🔒 3. Kabag
                                                        </button>
                                                    @elseif($isKabag)
                                                        <form method="POST" action="{{ route('satyalancana.verifikasi', $usulan->id) }}" onsubmit="return confirm('Bubuhkan Paraf 2 (Kepala Bagian Umum)?')">
                                                            @csrf
                                                            <input type="hidden" name="tahap" value="paraf_kabag">
                                                            <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition" title="3. Bubuhkan Paraf Kepala Bagian Umum">
                                                                ✒ 3. Kabag
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" disabled class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu tindakan Kepala Bagian Umum">
                                                            ⏳ 3. Kabag
                                                        </button>
                                                    @endif

                                                    {{-- 4. TTE WD2 --}}
                                                    @if($usulan->ttd_wd2_at)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="4. Disahkan TTE Wakil Dekan II pada {{ $usulan->ttd_wd2_at->format('d/m/Y H:i') }}">
                                                            ✓ 4. Sah (WD2)
                                                        </span>
                                                    @elseif(!$usulan->paraf_kabag_at)
                                                        <button type="button" disabled class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-medium bg-slate-100 text-slate-400 border border-dashed border-slate-300 cursor-not-allowed opacity-60" title="🔒 Terkunci: Menunggu Paraf Kepala Bagian Umum">
                                                            🔒 4. WD2
                                                        </button>
                                                    @elseif($isWd2 || $isDekan)
                                                        <form method="POST" action="{{ route('satyalancana.verifikasi', $usulan->id) }}" onsubmit="return confirm('Sahkan usulan secara resmi dengan TTE Wakil Dekan II?')">
                                                            @csrf
                                                            <input type="hidden" name="tahap" value="ttd_wd2">
                                                            <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-700 hover:bg-emerald-800 text-white shadow-2xs transition" title="4. Sahkan Usulan Resmi dengan TTE WD II">
                                                                ✍ 4. TTE WD2
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" disabled class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-medium bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Menunggu TTE Wakil Dekan II">
                                                            ⏳ 4. WD2
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-sm">
                                            Belum ada berkas usulan resmi yang diajukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($daftarUsulan->hasPages())
                        <div class="mt-4">
                            {{ $daftarUsulan->links() }}
                        </div>
                    @endif
                </div>
            </x-enterprise.card>

        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL 1: CETAK SURAT PENGANTAR USULAN KOLEKTIF DENGAN FORM DINAMIS --}}
        {{-- ========================================================================= --}}
        <div x-show="modalSuratOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalSuratOpen = false"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
                    {{-- Modal Header --}}
                    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 px-6 py-4 text-white flex justify-between items-center">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-6 h-6 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <h3 class="text-base font-bold">Cetak Format Surat Pengantar Resmi Kolektif</h3>
                        </div>
                        <button type="button" @click="modalSuratOpen = false" class="text-white/80 hover:text-white font-bold text-xl leading-none">&times;</button>
                    </div>

                    {{-- Modal Form --}}
                    <form method="GET" action="{{ route('satyalancana.cetak-surat-pengantar') }}" target="_blank" class="p-6 space-y-4">
                        <p class="text-xs text-slate-500 leading-relaxed bg-blue-50 border border-blue-200 rounded-lg p-3">
                            Isi nomor surat keluar fakultas dan nomor surat rujukan masuk dari Rektorat / Kementerian sebelum mencetak dokumen surat pengantar kolektif bertanda tangan elektronik.
                        </p>

                        {{-- 3.1: Nomor Surat Dinamis --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Nomor Surat Keluar Usulan <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-2 text-xs font-semibold text-slate-600 bg-slate-100 border border-r-0 border-slate-300 rounded-l-lg">
                                    Nomor:
                                </span>
                                <input type="text"
                                       name="nomor_surat_input"
                                       placeholder="Contoh: 2422"
                                       value="2422"
                                       required
                                       class="w-32 px-3 py-2 text-xs border border-slate-300 focus:ring-blue-500 focus:border-blue-500 font-bold text-slate-800">
                                <span class="inline-flex items-center px-3 py-2 text-xs font-semibold text-slate-600 bg-slate-100 border border-l-0 border-slate-300 rounded-r-lg">
                                    /UN19.5.1.1.10/KP/{{ date('Y') }}
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400 mt-1 block">Diisi oleh Admin Kepegawaian sesuai buku register surat keluar.</span>
                        </div>

                        {{-- 3.2: Surat Rujukan Masuk Dari Rektorat --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Nomor Surat Masuk Rujukan Rektorat <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       name="surat_rujukan_nomor"
                                       value="200/DST/UN19.5.2.2.1/KP.11.02/{{ date('Y') }}"
                                       required
                                       placeholder="Nomor surat masuk dari luar / Rektorat"
                                       class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Tanggal Surat Masuk Rektorat <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       name="surat_rujukan_tanggal"
                                       value="22 Juli {{ date('Y') }}"
                                       required
                                       placeholder="Contoh: 22 Juli {{ date('Y') }}"
                                       class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Periode Usulan Satyalancana <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       name="periode"
                                       value="Periode Mei {{ date('Y') + 1 }}"
                                       required
                                       placeholder="Contoh: Periode Mei {{ date('Y') + 1 }}"
                                       class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Perihal Surat Masuk Rektorat <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       name="surat_rujukan_perihal"
                                       value="Pengusulan Tanda Satyalancana Karya Satya Periode Mei {{ date('Y') + 1 }}"
                                       required
                                       placeholder="Perihal surat rujukan dari Rektorat"
                                       class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                            <button type="button" @click="modalSuratOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                Cetak PDF Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL 2: PEMERIKSAAN KELENGKAPAN 7 BERKAS SYARAT RESMI PER PEGAWAI --}}
        {{-- ========================================================================= --}}
        <div x-show="modalBerkasOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalBerkasOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden transform transition-all"
                     x-show="currentPegawai">
                    
                    {{-- Modal Header --}}
                    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 px-6 py-4 text-white flex justify-between items-center shadow-sm">
                        <div>
                            <div class="text-[11px] font-semibold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Pemeriksaan & Verifikasi Kelayakan Berkas Tanda Kehormatan
                            </div>
                            <h3 class="text-base font-bold mt-0.5" x-text="currentPegawai?.nama"></h3>
                            <div class="text-xs text-slate-300 flex items-center gap-2 mt-0.5 flex-wrap">
                                <span>NIP: <strong class="text-white" x-text="currentPegawai?.nip"></strong></span>
                                <span>&bull;</span>
                                <span>Unit: <span x-text="currentPegawai?.unit"></span></span>
                                <span>&bull;</span>
                                <span>Kategori: <strong class="text-amber-300 font-bold" x-text="'Satyalancana ' + currentPegawai?.kategori"></strong></span>
                            </div>
                        </div>
                        <button type="button" @click="modalBerkasOpen = false" class="text-white/70 hover:text-white font-bold text-2xl leading-none px-2 py-1 rounded hover:bg-white/10 transition">&times;</button>
                    </div>

                    {{-- Checklist 7 Dokumen Persyaratan --}}
                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                        <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="leading-relaxed">
                                Pemeriksaan 7 berkas persyaratan resmi tanda kehormatan Satyalancana Karya Satya yang disinkronkan langsung dengan basis data kepegawaian SIKAP FKP UNRI. Pejabat dan Admin dapat melihat pratinjau dokumen dan langsung membubuhkan verifikasi/paraf di bawah ini:
                            </div>
                        </div>

                        <div class="space-y-3">
                            {{-- Syarat 1: DRH 12 Poin --}}
                            <div class="flex items-start justify-between gap-3 p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50/70 transition">
                                <div class="flex items-start gap-3 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">1</span>
                                    <div class="text-xs space-y-1">
                                        <div class="font-bold text-slate-900">Daftar Riwayat Hidup (DRH) Resmi Format 12 Poin</div>
                                        <div class="text-slate-600 leading-normal">
                                            Diterbitkan otomatis oleh sistem SIKAP format standar Setmilpres & BKN ber-QR Code serta legalisasi paraf/TTE digital berjenjang.
                                        </div>
                                        <div class="pt-1">
                                            <a :href="'{{ url('satyalancana') }}/' + currentPegawai?.id + '/cetak-drh'" 
                                               target="_blank" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-2xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Lihat / Unduh Dokumen DRH (PDF)
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shrink-0">
                                    Lengkap
                                </span>
                            </div>

                            {{-- Syarat 2: SK CPNS --}}
                            <div class="flex items-start justify-between gap-3 p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition">
                                <div class="flex items-start gap-3 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">2</span>
                                    <div class="text-xs space-y-1">
                                        <div class="font-bold text-slate-900">SK Pengangkatan CPNS / Pertama (TMT)</div>
                                        <div class="text-slate-600">
                                            Nomor SK: <span class="font-mono font-medium text-slate-800" x-text="currentPegawai?.no_sk_cpns"></span> &bull; 
                                            TMT: <strong class="text-slate-800" x-text="currentPegawai?.tmt_cpns"></strong>
                                        </div>
                                        <div class="pt-1 flex items-center gap-2 flex-wrap">
                                            <template x-if="currentPegawai?.file_sk_cpns_url">
                                                <a :href="currentPegawai?.file_sk_cpns_url" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    Lihat / Unduh SK CPNS (PDF)
                                                </a>
                                            </template>
                                            <template x-if="!currentPegawai?.file_sk_cpns_url">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-[11px] text-slate-500 italic flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        Data TMT terverifikasi di SIMPEG (berkas scan fisik belum diunggah)
                                                    </span>
                                                    <a :href="currentPegawai?.url_upload_cpns" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-blue-600 hover:text-blue-800 font-semibold underline">
                                                        Unggah SK CPNS di Data Pegawai &rarr;
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold shrink-0"
                                      :class="currentPegawai?.file_sk_cpns_url ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                                      x-text="currentPegawai?.file_sk_cpns_url ? 'File Tersedia' : 'Tersedia (Data SIMPEG)'">
                                </span>
                            </div>

                            {{-- Syarat 3: SK Pangkat Terakhir --}}
                            <div class="flex items-start justify-between gap-3 p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition">
                                <div class="flex items-start gap-3 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">3</span>
                                    <div class="text-xs space-y-1">
                                        <div class="font-bold text-slate-900">SK Pangkat / Golongan Ruang Terakhir</div>
                                        <div class="text-slate-600" x-text="currentPegawai?.pangkat_terakhir"></div>
                                        <div class="pt-1 flex items-center gap-2 flex-wrap">
                                            <template x-if="currentPegawai?.file_sk_pangkat_url">
                                                <a :href="currentPegawai?.file_sk_pangkat_url" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    Lihat / Unduh SK Pangkat (PDF)
                                                </a>
                                            </template>
                                            <template x-if="!currentPegawai?.file_sk_pangkat_url">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-[11px] text-slate-500 italic flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        Data pangkat terverifikasi di riwayat kepangkatan (berkas scan fisik belum diunggah)
                                                    </span>
                                                    <a :href="currentPegawai?.url_upload_pangkat" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-blue-600 hover:text-blue-800 font-semibold underline">
                                                        Kelola / Unggah di Riwayat Pangkat &rarr;
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold shrink-0"
                                      :class="currentPegawai?.file_sk_pangkat_url ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                                      x-text="currentPegawai?.file_sk_pangkat_url ? 'File Tersedia' : 'Tersedia (Data SIMPEG)'">
                                </span>
                            </div>

                            {{-- Syarat 4: SK Jabatan Terakhir --}}
                            <div class="flex items-start justify-between gap-3 p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition">
                                <div class="flex items-start gap-3 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">4</span>
                                    <div class="text-xs space-y-1">
                                        <div class="font-bold text-slate-900">SK Jabatan Terakhir</div>
                                        <div class="text-slate-600" x-text="currentPegawai?.jabatan_terakhir"></div>
                                        <div class="pt-1 flex items-center gap-2 flex-wrap">
                                            <template x-if="currentPegawai?.file_sk_jabatan_url">
                                                <a :href="currentPegawai?.file_sk_jabatan_url" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    Lihat / Unduh SK Jabatan (PDF)
                                                </a>
                                            </template>
                                            <template x-if="!currentPegawai?.file_sk_jabatan_url">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-[11px] text-slate-500 italic flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        Data SK Jabatan terverifikasi di riwayat jabatan (berkas scan fisik belum diunggah)
                                                    </span>
                                                    <a :href="currentPegawai?.url_upload_jabatan" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-blue-600 hover:text-blue-800 font-semibold underline">
                                                        Kelola / Unggah di Riwayat Jabatan &rarr;
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold shrink-0"
                                      :class="currentPegawai?.file_sk_jabatan_url ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                                      x-text="currentPegawai?.file_sk_jabatan_url ? 'File Tersedia' : 'Tersedia (Data SIMPEG)'">
                                </span>
                            </div>

                            {{-- Syarat 5: Penilaian SKP 2 Tahun Terakhir --}}
                            <div class="flex items-start justify-between gap-3 p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition">
                                <div class="flex items-start gap-3 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">5</span>
                                    <div class="text-xs space-y-1">
                                        <div class="font-bold text-slate-900">Penilaian Kinerja Pegawai (SKP 2 Tahun Terakhir)</div>
                                        <div class="text-slate-600">
                                            Predikat Kinerja: <strong class="text-blue-700" x-text="currentPegawai?.skp_status"></strong>
                                            <span class="text-slate-400 block text-[11px]">(Regulasi: Penilaian kinerja sekurang-kurangnya bernilai Baik dalam 2 tahun terakhir tanpa catatan kinerja buruk)</span>
                                        </div>
                                        
                                        {{-- Berkas SKP per Tahun jika tersedia --}}
                                        <div class="pt-1 flex items-center gap-2 flex-wrap">
                                            <template x-if="currentPegawai?.skp_items && currentPegawai.skp_items.length > 0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <template x-for="skp in currentPegawai?.skp_items" :key="skp.tahun">
                                                        <div class="flex items-center gap-1.5">
                                                            <template x-if="skp.evaluasi_url">
                                                                <a :href="skp.evaluasi_url" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 shadow-2xs">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                                    <span x-text="'SKP ' + skp.tahun + ' (' + skp.predikat + ')'"></span>
                                                                </a>
                                                            </template>
                                                            <template x-if="!skp.evaluasi_url">
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-medium text-slate-600 bg-slate-100 rounded border border-slate-200">
                                                                    <span x-text="'Tahun ' + skp.tahun + ': ' + skp.predikat"></span>
                                                                </span>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <a :href="currentPegawai?.url_upload_skp" target="_blank" class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold underline">
                                                        Kelola SKP &rarr;
                                                    </a>
                                                </div>
                                            </template>
                                            <template x-if="!currentPegawai?.skp_items || currentPegawai.skp_items.length === 0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-[11px] text-slate-500 italic">Data SKP belum tercatat di sistem</span>
                                                    <a :href="currentPegawai?.url_upload_skp" target="_blank" class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold underline">
                                                        Input SKP di Modul Kinerja &rarr;
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold border shrink-0"
                                      :class="currentPegawai?.skp_count >= 2 ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300'"
                                      x-text="currentPegawai?.skp_count >= 2 ? 'Lengkap (2 Thn)' : 'Tercatat ' + currentPegawai?.skp_count + ' Thn'">
                                </span>
                            </div>

                            {{-- Syarat 6: Surat Pernyataan Tidak Pernah Hukdis & CLTN --}}
                            <div class="flex items-start justify-between gap-3 p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition">
                                <div class="flex items-start gap-3 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">6</span>
                                    <div class="text-xs space-y-1">
                                        <div class="font-bold text-slate-900">Surat Pernyataan Tidak Pernah Dijatuhi Hukuman Disiplin & CLTN</div>
                                        <div class="text-slate-600" x-text="currentPegawai?.hukdis_status"></div>
                                        <div class="text-[11px] text-slate-500 italic pt-0.5">
                                            Status integritas kedisiplinan diaudit langsung secara real-time dari modul Hukuman Disiplin & Cuti SIKAP FKP UNRI.
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shrink-0">
                                    Nihil Hukdis
                                </span>
                            </div>

                            {{-- Syarat 7: Piagam Satyalancana Sebelumnya --}}
                            <div class="flex items-start justify-between gap-3 p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition">
                                <div class="flex items-start gap-3 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">7</span>
                                    <div class="text-xs space-y-1">
                                        <div class="font-bold text-slate-900">Piagam Penghargaan Satyalancana Karya Satya Sebelumnya</div>
                                        <div class="text-slate-600" x-text="currentPegawai?.piagam_sebelumnya"></div>
                                        <div class="pt-1 flex items-center gap-2 flex-wrap">
                                            {{-- Jika ada file piagam yang valid --}}
                                            <template x-if="currentPegawai?.file_piagam_url">
                                                <a :href="currentPegawai?.file_piagam_url" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    Lihat / Unduh Piagam Sebelumnya (PDF)
                                                </a>
                                            </template>
                                            {{-- Jika pengusulan perdana (10 Tahun) --}}
                                            <template x-if="!currentPegawai?.file_piagam_url && currentPegawai?.kategori === '10 Tahun'">
                                                <span class="text-[11px] text-slate-500 italic">
                                                    Pengusulan perdana (10 Tahun) tidak dipersyaratkan melampirkan piagam sebelumnya.
                                                </span>
                                            </template>
                                            {{-- Jika pengusulan 20/30 Tahun dan berkas belum diunggah --}}
                                            <template x-if="!currentPegawai?.file_piagam_url && currentPegawai?.kategori !== '10 Tahun'">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-[11px] text-slate-500 italic">
                                                        Berkas scan piagam terdahulu belum diunggah.
                                                    </span>
                                                    <a :href="currentPegawai?.url_tambah_piagam" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded bg-amber-50 text-amber-800 border border-amber-300 hover:bg-amber-100 transition shadow-2xs">
                                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                        + Unggah Piagam di Riwayat Penghargaan
                                                    </a>
                                                    <a :href="currentPegawai?.url_upload_piagam" target="_blank" class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold underline">
                                                        Menu Riwayat Penghargaan &rarr;
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold border shrink-0"
                                      :class="currentPegawai?.kategori === '10 Tahun' ? 'bg-slate-100 text-slate-700 border-slate-300' : (currentPegawai?.file_piagam_url ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300')"
                                      x-text="currentPegawai?.kategori === '10 Tahun' ? 'Usulan Pertama' : (currentPegawai?.file_piagam_url ? 'File Tersedia' : 'Perlu Upload')">
                                </span>
                            </div>
                        </div>

                        {{-- ================================================================= --}}
                        {{-- ACTION SECTION: TINDAKAN PEMERIKSAAN, VERIFIKASI & PARAF LANGSUNG --}}
                        {{-- ================================================================= --}}
                        <div class="mt-4 p-4 rounded-xl border border-slate-200 bg-slate-50/80 space-y-3">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="text-xs">
                                    <span class="text-slate-500">Status Usulan Berkas:</span>
                                    <strong class="text-slate-900 ml-1 px-2.5 py-0.5 rounded-md text-xs inline-block"
                                            :class="{
                                                'bg-slate-200 text-slate-700': currentPegawai?.usulan_status_raw === 'belum_diajukan',
                                                'bg-amber-100 text-amber-800 border border-amber-300': currentPegawai?.usulan_status_raw === 'diajukan',
                                                'bg-teal-100 text-teal-800 border border-teal-300': currentPegawai?.usulan_status_raw === 'diverifikasi_admin',
                                                'bg-blue-100 text-blue-800 border border-blue-300': currentPegawai?.usulan_status_raw === 'diparaf_kapokja',
                                                'bg-purple-100 text-purple-800 border border-purple-300': currentPegawai?.usulan_status_raw === 'diparaf_kabag',
                                                'bg-emerald-100 text-emerald-800 border border-emerald-300': currentPegawai?.usulan_status_raw === 'disetujui_wd2' || currentPegawai?.usulan_status_raw === 'selesai',
                                                'bg-red-100 text-red-800 border border-red-300': currentPegawai?.usulan_status_raw === 'ditolak'
                                            }"
                                            x-text="currentPegawai?.usulan_status">
                                    </strong>
                                </div>

                                {{-- Tracking Waktu Paraf & TTE --}}
                                <div class="text-[11px] text-slate-500 flex items-center gap-2" x-show="currentPegawai?.usulan_id">
                                    <span x-show="currentPegawai?.verifikasi_admin_at" class="text-emerald-700 font-medium">✓ Admin</span>
                                    <span x-show="currentPegawai?.paraf_kapokja_at" class="text-teal-700 font-medium">✓ Pokja</span>
                                    <span x-show="currentPegawai?.paraf_kabag_at" class="text-blue-700 font-medium">✓ Kabag</span>
                                    <span x-show="currentPegawai?.ttd_wd2_at" class="text-purple-700 font-bold">✓ WD2 (Sah)</span>
                                </div>
                            </div>

                            {{-- Tombol Aksi Langsung Berdasarkan Tahap & Peran Pengguna --}}
                            <div class="pt-2 border-t border-slate-200 flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    {{-- 1. AKSI BELUM DIAJUKAN -> HANYA ADMIN YANG DAPAT MENGAJUKAN USULAN PEGAWAI LAIN --}}
                                    @if($isAdmin ?? false)
                                        <template x-if="!currentPegawai?.usulan_id">
                                            <form method="POST" :action="'{{ url('satyalancana') }}/' + currentPegawai?.id + '/ajukan'" onsubmit="return confirm('Ajukan usulan resmi tanda kehormatan Satyalancana untuk pegawai ini?')">
                                                @csrf
                                                <input type="hidden" name="kategori" :value="currentPegawai?.kategori">
                                                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Ajukan Usulan Resmi Sekarang (Admin)
                                                </button>
                                            </form>
                                        </template>
                                    @else
                                        <template x-if="!currentPegawai?.usulan_id">
                                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                                                Status: Memenuhi kriteria masa kerja, menunggu pengusulan resmi oleh Admin Kepegawaian.
                                            </span>
                                        </template>
                                    @endif

                                    {{-- 2. AKSI DIAJUKAN -> VERIFIKASI ADMIN --}}
                                    <template x-if="currentPegawai?.usulan_id && currentPegawai?.usulan_status_raw === 'diajukan' && {{ ($isAdmin ?? false) ? 'true' : 'false' }}">
                                        <form method="POST" :action="'{{ url('satyalancana/verifikasi') }}/' + currentPegawai?.usulan_id" onsubmit="return confirm('Verifikasi kelayakan berkas persyaratan Satyalancana an. ' + currentPegawai?.nama + '?')">
                                            @csrf
                                            <input type="hidden" name="tahap" value="verifikasi_admin">
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Verifikasi Kelayakan Berkas (Admin)
                                            </button>
                                        </form>
                                    </template>

                                    {{-- 3. AKSI DIVERIFIKASI_ADMIN -> PARAF 1 KA POKJA KEU-KEPEG --}}
                                    <template x-if="currentPegawai?.usulan_id && currentPegawai?.usulan_status_raw === 'diverifikasi_admin' && {{ ($isKaPokja ?? false) ? 'true' : 'false' }}">
                                        <form method="POST" :action="'{{ url('satyalancana/verifikasi') }}/' + currentPegawai?.usulan_id" onsubmit="return confirm('Bubuhkan Paraf 1 (Ka Pokja Keuangan & Kepegawaian)?')">
                                            @csrf
                                            <input type="hidden" name="tahap" value="paraf_kapokja">
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                Bubuhkan Paraf 1 (Ka Pokja Keu-Kepeg)
                                            </button>
                                        </form>
                                    </template>

                                    {{-- 4. AKSI DIPARAF_KAPOKJA -> PARAF 2 KEPALA BAGIAN UMUM --}}
                                    <template x-if="currentPegawai?.usulan_id && currentPegawai?.usulan_status_raw === 'diparaf_kapokja' && {{ ($isKabag ?? false) ? 'true' : 'false' }}">
                                        <form method="POST" :action="'{{ url('satyalancana/verifikasi') }}/' + currentPegawai?.usulan_id" onsubmit="return confirm('Bubuhkan Paraf 2 (Kepala Bagian Umum)?')">
                                            @csrf
                                            <input type="hidden" name="tahap" value="paraf_kabag">
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                Bubuhkan Paraf 2 (Kabag Umum)
                                            </button>
                                        </form>
                                    </template>

                                    {{-- 5. AKSI DIPARAF_KABAG -> PENGESAHAN TTE WAKIL DEKAN II --}}
                                    <template x-if="currentPegawai?.usulan_id && currentPegawai?.usulan_status_raw === 'diparaf_kabag' && {{ (($isWd2 ?? false) || ($isDekan ?? false)) ? 'true' : 'false' }}">
                                        <form method="POST" :action="'{{ url('satyalancana/verifikasi') }}/' + currentPegawai?.usulan_id" onsubmit="return confirm('Sahkan resmi berkas tanda kehormatan dengan Tanda Tangan Elektronik (TTE) Wakil Dekan II?')">
                                            @csrf
                                            <input type="hidden" name="tahap" value="ttd_wd2">
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg shadow-sm transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Sahkan & TTE WD II
                                            </button>
                                        </form>
                                    </template>

                                    {{-- 6. STATUS SUDAH DISETUJUI RESMI OLEH WD2 --}}
                                    <template x-if="currentPegawai?.usulan_id && (currentPegawai?.usulan_status_raw === 'disetujui_wd2' || currentPegawai?.usulan_status_raw === 'selesai')">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 bg-emerald-100 px-3 py-1.5 rounded-lg border border-emerald-300">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Berkas Telah Disahkan Resmi oleh Pimpinan (WD II) & Siap Dikirim Kolektif
                                        </span>
                                    </template>
                                </div>

                                <button type="button" @click="modalBerkasOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-200 hover:bg-slate-300 rounded-lg transition">
                                    Tutup Pemeriksaan
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>