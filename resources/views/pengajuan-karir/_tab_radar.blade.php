{{-- Partial Tab Radar Jatuh Tempo (Monitoring KP & KGB) di Pusat Verifikasi Usulan Karir --}}
<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden border border-slate-700">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-500/20 text-blue-300 border border-blue-500/30">
                    Radar Monitoring Otomatis BKN
                </span>
                <h2 class="text-xl md:text-2xl font-black mt-2 tracking-tight">Monitoring Jatuh Tempo Kenaikan Pangkat & Gaji Berkala</h2>
                <p class="text-slate-300 text-xs md:text-sm mt-1 max-w-2xl leading-relaxed">
                    Daftar ASN (PNS & PPPK) yang memasuki radar jatuh tempo 3 bulan ke depan berdasarkan siklus 4 tahunan (Kenaikan Pangkat) dan 2 tahunan (Kenaikan Gaji Berkala).
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a href="{{ route('kp.index', ['filter' => 'reminder']) }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 transition">
                    <span>🎖️ Buka Modul KP Lengkap</span>
                </a>
                <a href="{{ route('kgb.index', ['filter' => 'reminder']) }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl bg-amber-600 hover:bg-amber-500 text-white shadow-lg shadow-amber-600/30 transition">
                    <span>💰 Buka Modul KGB Lengkap</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Radar 1: Kenaikan Pangkat (KP) Jatuh Tempo 3 Bulan --}}
    <x-enterprise.card>
        <div class="p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>🎖️ Radar Jatuh Tempo Kenaikan Pangkat (KP) 3 Bulan ke Depan</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            {{ count($reminderKp ?? []) }} Pegawai
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pegawai PNS yang mendekati atau telah memasuki periode 4 tahun masa kerja pangkat</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-600 uppercase text-[11px] font-bold">
                            <th class="py-3 px-3">No</th>
                            <th class="py-3 px-3">Pegawai & NIP</th>
                            <th class="py-3 px-3">Golongan</th>
                            <th class="py-3 px-3 text-center">TMT Terakhir</th>
                            <th class="py-3 px-3 text-center">Jatuh Tempo KP</th>
                            <th class="py-3 px-3 text-center">Status Usulan Mandiri</th>
                            <th class="py-3 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reminderKp ?? [] as $row)
                            @php
                                $usulanKp = $row->pengajuanKarir?->firstWhere('jenis_pengajuan', 'KP');
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 font-medium text-slate-500">{{ $loop->iteration }}</td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $row->nama_lengkap ?? $row->nama }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $row->nip }}</div>
                                    <div class="text-[11px] text-slate-600">{{ $row->jabatan?->nama_jabatan ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-semibold text-slate-800">{{ $row->golongan?->nama_golongan ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $row->golongan?->nama_pangkat ?? '' }}</div>
                                </td>
                                <td class="py-3 px-3 text-center font-mono">
                                    {{ $row->tmt_pangkat_terakhir ? \Carbon\Carbon::parse($row->tmt_pangkat_terakhir)->format('d-m-Y') : '-' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 font-mono">
                                        🎯 {{ $row->kp_berikutnya ? \Carbon\Carbon::parse($row->kp_berikutnya)->format('d-m-Y') : '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($usulanKp)
                                        <a href="{{ route('pengajuan-karir.show', $usulanKp->id) }}" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition">
                                            <span>📋 {{ ucfirst(str_replace('_', ' ', $usulanKp->status)) }}</span>
                                        </a>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Belum Mengajukan Mandiri</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('kp.index') }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-2xs transition">
                                        <span>Proses KP</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-400">
                                    Tidak ada pegawai yang memasuki jatuh tempo Kenaikan Pangkat dalam 3 bulan ke depan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-enterprise.card>

    {{-- Radar 2: Kenaikan Gaji Berkala (KGB) Jatuh Tempo 3 Bulan --}}
    <x-enterprise.card>
        <div class="p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>💰 Radar Jatuh Tempo Kenaikan Gaji Berkala (KGB) 3 Bulan ke Depan</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            {{ count($reminderKgb ?? []) }} Pegawai
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pegawai ASN (PNS & PPPK) yang memasuki batas waktu 2 tahun evaluasi gaji berkala</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-600 uppercase text-[11px] font-bold">
                            <th class="py-3 px-3">No</th>
                            <th class="py-3 px-3">Pegawai & NIP</th>
                            <th class="py-3 px-3">Unit & Golongan</th>
                            <th class="py-3 px-3 text-center">TMT KGB Terakhir</th>
                            <th class="py-3 px-3 text-center">Jatuh Tempo KGB</th>
                            <th class="py-3 px-3 text-center">Status Usulan Mandiri</th>
                            <th class="py-3 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reminderKgb ?? [] as $row)
                            @php
                                $usulanKgb = $row->pengajuanKarir?->firstWhere('jenis_pengajuan', 'KGB');
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 font-medium text-slate-500">{{ $loop->iteration }}</td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $row->nama_lengkap ?? $row->nama }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $row->nip }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="text-slate-800">{{ $row->unitKerja?->nama_unit ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-500 font-semibold">{{ $row->golongan?->nama_golongan ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3 text-center font-mono">
                                    {{ $row->tmt_kgb_terakhir ? \Carbon\Carbon::parse($row->tmt_kgb_terakhir)->format('d-m-Y') : '-' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 font-mono">
                                        🎯 {{ $row->kgb_berikutnya ? \Carbon\Carbon::parse($row->kgb_berikutnya)->format('d-m-Y') : '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($usulanKgb)
                                        <a href="{{ route('pengajuan-karir.show', $usulanKgb->id) }}" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition">
                                            <span>📋 {{ ucfirst(str_replace('_', ' ', $usulanKgb->status)) }}</span>
                                        </a>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Belum Mengajukan Mandiri</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('kgb.index') }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-amber-600 hover:bg-amber-500 text-white shadow-2xs transition">
                                        <span>Proses KGB</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-400">
                                    Tidak ada pegawai yang memasuki jatuh tempo Kenaikan Gaji Berkala dalam 3 bulan ke depan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-enterprise.card>
</div>
