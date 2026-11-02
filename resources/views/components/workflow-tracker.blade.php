@props([
    'model' => null, // Instance of PengajuanKarir or PengajuanCuti
    'type' => 'karir', // 'karir' or 'cuti'
    'title' => 'Alur Tracking Verifikasi Berjenjang & TTE Resmi',
    'compact' => false,
])

@php
    $isCuti = ($type === 'cuti') || ($model instanceof \App\Models\PengajuanCuti);
    
    // Status umum
    $statusRaw = strtolower($model->status ?? '');
    $isRejected = in_array($statusRaw, ['ditolak', 'reject', 'rejected']);
    $isCanceled = in_array($statusRaw, ['dibatalkan', 'canceled', 'batal']);
    $isFinished = in_array($statusRaw, ['selesai', 'disetujui', 'disetujui_wd2', 'disetujui_dekan']);
    
    $catatanTolak = $model->catatan_verifikator 
        ?? $model->catatan_pimpinan 
        ?? $model->catatan_atasan_langsung 
        ?? $model->catatan 
        ?? null;

    // Define steps
    $steps = [];

    if ($isCuti) {
        // TAHAP CUTI:
        // 1. Pengajuan oleh Pegawai
        // 2. Verifikasi Admin Kepegawaian
        // 3. Paraf Ka. Pokja Keu-Kepeg
        // 4. Pertimbangan Atasan Langsung (Kajur / Kabag)
        // 5. Keputusan & TTE PYBMC (Wadek II / Dekan)
        
        $step1Done = !empty($model->created_at);
        $step2Done = !empty($model->verifikasi_admin_at);
        $step3Done = !empty($model->paraf_kapokja_at);
        $step4Done = !empty($model->pertimbangan_atasan_at) || (!empty($model->pertimbangan_atasan) && $model->pertimbangan_atasan !== 'Menunggu Persetujuan');
        $step5Done = in_array($model->status, ['Disetujui', 'Ditolak']) || !empty($model->approved_at);

        $steps = [
            [
                'number' => 1,
                'title' => 'Pengajuan Berkas',
                'role' => 'Pemohon Cuti',
                'person' => $model->pegawai?->nama_lengkap ?? $model->pegawai?->nama ?? 'Pegawai',
                'date' => $model->created_at ? $model->created_at->format('d/m/Y H:i') : null,
                'done' => $step1Done,
                'active' => false,
                'desc' => 'Permohonan berhasil didaftarkan ke sistem',
                'icon' => '📝',
            ],
            [
                'number' => 2,
                'title' => 'Screening & Kuota',
                'role' => 'Admin Kepegawaian',
                'person' => 'Subbag Kepegawaian',
                'date' => $model->verifikasi_admin_at ? $model->verifikasi_admin_at->format('d/m/Y H:i') : null,
                'done' => $step2Done,
                'active' => $step1Done && !$step2Done && !$isRejected && !$isCanceled,
                'desc' => 'Verifikasi kuota sisa cuti & naskah dinas',
                'icon' => '🔍',
            ],
            [
                'number' => 3,
                'title' => 'Paraf Koordinasi',
                'role' => 'Ka. Pokja Keu-Kepeg',
                'person' => 'Dolli Vita Zenitha, SE',
                'date' => $model->paraf_kapokja_at ? $model->paraf_kapokja_at->format('d/m/Y H:i') : null,
                'done' => $step3Done,
                'active' => $step2Done && !$step3Done && !$isRejected && !$isCanceled,
                'desc' => 'Koreksi administratif tata naskah kepegawaian',
                'icon' => '📑',
            ],
            [
                'number' => 4,
                'title' => 'Atasan Langsung',
                'role' => 'Kajur / Kabag Umum',
                'person' => $model->atasanLangsung?->nama_lengkap ?? 'Atasan Langsung Unit',
                'date' => $model->pertimbangan_atasan_at ? $model->pertimbangan_atasan_at->format('d/m/Y H:i') : null,
                'done' => $step4Done,
                'active' => $step3Done && !$step4Done && !$isRejected && !$isCanceled,
                'desc' => 'Persetujuan pertimbangan kelayakan operasional',
                'icon' => '✒️',
            ],
            [
                'number' => 5,
                'title' => 'TTE Keputusan Cuti',
                'role' => 'Pejabat Berwenang (PYBMC)',
                'person' => 'Wakil Dekan II / Dekan',
                'date' => $model->approved_at ? $model->approved_at->format('d/m/Y H:i') : null,
                'done' => $step5Done && !$isRejected,
                'active' => $step4Done && !$step5Done && !$isRejected && !$isCanceled,
                'desc' => 'Penerbitan Surat Keputusan Izin Cuti Sah',
                'icon' => '🏛️',
            ],
        ];
    } else {
        // TAHAP KARIR (KP, KGB, SATYALANCANA):
        // 1. Usulan Pegawai
        // 2. Admin Screening & Penomoran Naskah
        // 3. Paraf Ka. Pokja Keu-Kepeg
        // 4. Paraf Kabag Umum
        // 5. Penetapan & TTE Wakil Dekan II
        
        $step1Done = !empty($model->created_at);
        $step2Done = !empty($model->verifikasi_admin_at);
        $step3Done = !empty($model->paraf_kapokja_at);
        $step4Done = !empty($model->paraf_kabag_at);
        $isSahTte = !empty($model->paraf_wd2_at) || !empty($model->ttd_wd2_at) || !empty($model->ttd_dekan_at) || in_array($model->status, ['disetujui_wd2', 'disetujui_dekan', 'selesai']);
        $step5Done = $isSahTte;

        $tglWd2 = $model->paraf_wd2_at ?? $model->ttd_wd2_at ?? $model->ttd_dekan_at;

        $labelLayanan = $model->jenis_pengajuan === 'SATYALANCANA' ? 'Satyalancana' : ($model->jenis_pengajuan === 'KP' ? 'Kenaikan Pangkat' : 'KGB');

        $steps = [
            [
                'number' => 1,
                'title' => 'Pengajuan Usulan',
                'role' => 'Pemohon Layanan',
                'person' => $model->pegawai?->nama_lengkap ?? $model->pegawai?->nama ?? 'Pegawai',
                'date' => $model->created_at ? $model->created_at->format('d/m/Y H:i') : null,
                'done' => $step1Done,
                'active' => false,
                'desc' => 'Berkas persyaratan diunggah oleh pemohon',
                'icon' => '📝',
            ],
            [
                'number' => 2,
                'title' => 'Screening & Penomoran',
                'role' => 'Admin Kepegawaian',
                'person' => 'Subbag Kepegawaian FKep',
                'date' => $model->verifikasi_admin_at ? $model->verifikasi_admin_at->format('d/m/Y H:i') : null,
                'done' => $step2Done,
                'active' => $step1Done && !$step2Done && !$isRejected && !$isCanceled,
                'desc' => 'Validasi SK terakhir & penerbitan nomor naskah',
                'icon' => '🔍',
            ],
            [
                'number' => 3,
                'title' => 'Paraf Pokja Kepeg',
                'role' => 'Ka. Pokja Keu-Kepeg',
                'person' => 'Dolli Vita Zenitha, SE',
                'date' => $model->paraf_kapokja_at ? $model->paraf_kapokja_at->format('d/m/Y H:i') : null,
                'done' => $step3Done,
                'active' => $step2Done && !$step3Done && !$isRejected && !$isCanceled,
                'desc' => 'Pemeriksaan kepatuhan syarat BKN/Kemenkeu',
                'icon' => '📑',
            ],
            [
                'number' => 4,
                'title' => 'Paraf Kabag Umum',
                'role' => 'Kepala Bagian Umum',
                'person' => 'Bakhtiar, S.Sos., M.Si',
                'date' => $model->paraf_kabag_at ? $model->paraf_kabag_at->format('d/m/Y H:i') : null,
                'done' => $step4Done,
                'active' => $step3Done && !$step4Done && !$isRejected && !$isCanceled,
                'desc' => 'Telaah naskah dinas & disposisi ke pimpinan',
                'icon' => '✒️',
            ],
            [
                'number' => 5,
                'title' => 'TTE Wakil Dekan II',
                'role' => 'Pimpinan Fakultas',
                'person' => 'Dr. Safri, M.Kep / WD II',
                'date' => $tglWd2 ? $tglWd2->format('d/m/Y H:i') : null,
                'done' => $step5Done && !$isRejected,
                'active' => $step4Done && !$step5Done && !$isRejected && !$isCanceled,
                'desc' => 'Penetapan resmi & TTE keabsahan dokumen usulan',
                'icon' => '🏛️',
            ],
        ];
    }

    // Hitung persentase progres
    $completedCount = 0;
    $activeStepIndex = 0;
    foreach ($steps as $idx => $st) {
        if ($st['done']) {
            $completedCount++;
        }
        if ($st['active']) {
            $activeStepIndex = $idx + 1;
        }
    }
    
    $totalSteps = count($steps);
    if ($isRejected || $isCanceled) {
        $progressPercent = min(100, round(($completedCount / $totalSteps) * 100));
    } else {
        $progressPercent = round(($completedCount / $totalSteps) * 100);
    }
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs overflow-hidden">
    {{-- Header Tracker: Judul, Status & Persentase --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-4 mb-5">
        <div>
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <span class="text-base">🚀</span>
                <span>{{ $title }}</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Pemantauan real-time status alur verifikasi berkas dari pengajuan awal hingga TTE resmi pimpinan
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            @if($isRejected)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                    Berkas Ditolak / Perlu Perbaikan
                </span>
            @elseif($isCanceled)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    Permohonan Dibatalkan
                </span>
            @elseif($completedCount >= $totalSteps)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                    Persetujuan Selesai (100%)
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                    Tahap {{ $activeStepIndex ?: ($completedCount + 1) }} dari {{ $totalSteps }} ({{ $progressPercent }}%)
                </span>
            @endif
        </div>
    </div>

    {{-- Progress Bar Visual --}}
    <div class="relative w-full mb-6">
        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden shadow-inner">
            <div class="h-2 rounded-full transition-all duration-700 ease-out {{ $isRejected ? 'bg-rose-500' : ($isCanceled ? 'bg-slate-400' : 'bg-gradient-to-r from-teal-500 via-indigo-600 to-emerald-600') }}"
                 style="width: {{ max(10, $progressPercent) }}%"></div>
        </div>
    </div>

    {{-- Alert Catatan Penolakan / Revisi Bila Ada --}}
    @if($isRejected && $catatanTolak)
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50/80 p-4 text-xs text-rose-900 shadow-xs">
            <div class="flex items-start gap-2.5">
                <span class="text-rose-600 text-base font-bold">⚠️</span>
                <div class="flex-1">
                    <strong class="font-bold uppercase tracking-wider text-rose-800 block mb-1">Catatan Verifikator / Alasan Pengembalian:</strong>
                    <p class="leading-relaxed font-medium bg-white/70 p-2.5 rounded-lg border border-rose-200/60 font-mono text-slate-800">
                        {{ $catatanTolak }}
                    </p>
                    <span class="text-[11px] text-rose-700 mt-1.5 block">
                        ℹ️ Silakan periksa kembali kelengkapan dokumen atau hubungi Admin Subbag Kepegawaian untuk informasi perbaikan usulan.
                    </span>
                </div>
            </div>
        </div>
    @endif

    {{-- Stepper Connected Line: Responsive Horizontal Desktop / Vertical Mobile --}}
    <div class="relative">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 relative">
            @foreach($steps as $idx => $step)
                @php
                    $isDone = $step['done'];
                    $isActive = $step['active'];
                    $isPending = !$isDone && !$isActive;
                @endphp

                <div class="relative flex md:flex-col items-start md:items-center text-left md:text-center group">
                    {{-- Connected Line between items (desktop horizontal) --}}
                    @if(!$loop->last)
                        <div class="hidden md:block absolute top-5 left-1/2 w-full h-0.5 z-0 transition-colors {{ $isDone ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>
                    @endif

                    {{-- Connected Line (mobile vertical) --}}
                    @if(!$loop->last)
                        <div class="md:hidden absolute left-5 top-10 bottom-0 w-0.5 -ml-px z-0 {{ $isDone ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>
                    @endif

                    {{-- Circle Icon --}}
                    <div class="relative z-10 flex items-center justify-center shrink-0 w-10 h-10 rounded-full font-bold text-sm transition-all duration-300 shadow-xs {{ $isDone ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : ($isActive ? 'bg-indigo-600 text-white ring-4 ring-indigo-100 animate-pulse' : ($isRejected && $loop->last ? 'bg-rose-600 text-white ring-4 ring-rose-100' : 'bg-slate-100 text-slate-400 border border-slate-300')) }}">
                        @if($isDone)
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @elseif($isActive)
                            <span class="text-sm font-black">{{ $step['number'] }}</span>
                        @elseif($isRejected && $loop->last)
                            <span>✕</span>
                        @else
                            <span class="text-xs">{{ $step['number'] }}</span>
                        @endif
                    </div>

                    {{-- Content labels --}}
                    <div class="ml-4 md:ml-0 md:mt-3 flex-1 min-w-0">
                        <div class="flex items-center md:justify-center gap-1">
                            <span class="text-xs font-bold {{ $isDone ? 'text-emerald-950' : ($isActive ? 'text-indigo-950' : 'text-slate-600') }}">
                                {{ $step['title'] }}
                            </span>
                        </div>

                        <div class="text-[11px] font-semibold {{ $isDone ? 'text-emerald-800' : ($isActive ? 'text-indigo-700' : 'text-slate-500') }} truncate">
                            {{ $step['role'] }}
                        </div>

                        <div class="text-[11px] text-slate-500 truncate" title="{{ $step['person'] }}">
                            {{ $step['person'] }}
                        </div>

                        @if($step['date'])
                            <div class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <span>✓</span> {{ $step['date'] }}
                            </div>
                        @elseif($isActive)
                            <div class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-ping"></span>
                                Sedang Diproses
                            </div>
                        @else
                            <div class="mt-1 text-[10px] text-slate-400">
                                Menunggu Giliran
                            </div>
                        @endif

                        <p class="text-[10px] text-slate-400 mt-1 hidden lg:block leading-tight">
                            {{ $step['desc'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
