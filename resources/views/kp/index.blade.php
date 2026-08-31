<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Monitoring Kenaikan Pangkat (KP)"
            subtitle="Daftar pegawai yang telah memenuhi masa kerja pangkat (>= 4 Tahun) dan verifikasi 5 berkas persyaratan"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="text-green-600 text-lg">✅</span>
                        <span class="font-medium text-green-800">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 font-bold">✕</button>
                </div>
            @endif

            {{-- Ringkasan Informasi 5 Syarat KP --}}
            <div class="rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="rounded-lg bg-blue-600 p-2 text-white text-lg">📋</div>
                    <div class="space-y-1">
                        <h4 class="font-bold text-slate-800 text-sm">Persyaratan Standar Usulan Kenaikan Pangkat (KP)</h4>
                        <p class="text-xs text-slate-600">Pastikan seluruh berkas terverifikasi lengkap sebelum menerbitkan update pangkat pegawai:</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-2 pt-2 text-xs">
                            <div class="flex items-center gap-1.5 font-medium text-slate-700 bg-white/70 px-2.5 py-1.5 rounded-lg border border-blue-100">
                                <span class="text-blue-600 font-bold">1.</span> SK Pangkat Terakhir
                            </div>
                            <div class="flex items-center gap-1.5 font-medium text-slate-700 bg-white/70 px-2.5 py-1.5 rounded-lg border border-blue-100">
                                <span class="text-blue-600 font-bold">2.</span> SKP 2 Tahun Terakhir
                            </div>
                            <div class="flex items-center gap-1.5 font-medium text-slate-700 bg-white/70 px-2.5 py-1.5 rounded-lg border border-blue-100">
                                <span class="text-blue-600 font-bold">3.</span> Surat KGB Terakhir
                            </div>
                            <div class="flex items-center gap-1.5 font-medium text-slate-700 bg-white/70 px-2.5 py-1.5 rounded-lg border border-blue-100">
                                <span class="text-blue-600 font-bold">4.</span> Fotocopy KARPEG
                            </div>
                            <div class="flex items-center gap-1.5 font-medium text-slate-700 bg-white/70 px-2.5 py-1.5 rounded-lg border border-blue-100">
                                <span class="text-blue-600 font-bold">5.</span> PAK (Dosen & PLP)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <x-enterprise.card>
                <div class="p-6">
                    <x-enterprise.data-table title="Pegawai Layak Naik Pangkat (Masa Kerja >= 4 Tahun)">
                        <x-slot name="head">
                            <tr>
                                <th class="w-12 px-3 py-3 text-center">No</th>
                                <th class="px-4 py-3 text-left">Pegawai</th>
                                <th class="px-3 py-3 text-left">Golongan</th>
                                <th class="px-3 py-3 text-center">TMT Terakhir</th>
                                <th class="px-4 py-3 text-left">Ceklis 5 Persyaratan Berkas</th>
                                <th class="w-40 px-3 py-3 text-center">Aksi</th>
                            </tr>
                        </x-slot>

                        @forelse($layakKp as $row)
                            @php
                                $syarat = $row->evaluasiSyaratKp();
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-3 py-3 text-center font-medium text-slate-600">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ $row->nama_lengkap ?? $row->nama }}</div>
                                    <div class="text-xs text-slate-500 font-mono">NIP: {{ $row->nip }}</div>
                                    <div class="text-xs text-slate-600 mt-0.5">
                                        {{ $row->jabatan->nama_jabatan ?? '-' }}
                                        @if($row->isDosen())
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-100 text-blue-800">Dosen</span>
                                        @elseif($row->isPlp())
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-purple-100 text-purple-800">PLP</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="font-semibold text-slate-800">{{ $row->golongan->nama_golongan ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $row->golongan->nama_pangkat ?? '' }}</div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <x-enterprise.badge color="blue">
                                        {{ $row->tmt_pangkat_terakhir ? \Carbon\Carbon::parse($row->tmt_pangkat_terakhir)->format('d-m-Y') : ($row->tmt_kp_terakhir ? \Carbon\Carbon::parse($row->tmt_kp_terakhir)->format('d-m-Y') : '-') }}
                                    </x-enterprise.badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="space-y-2">
                                        {{-- Skor Kelengkapan --}}
                                        <div class="flex items-center gap-2">
                                            @if($syarat['is_lengkap'])
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 border border-emerald-300 shadow-sm">
                                                    ✓ Berkas Lengkap ({{ $syarat['skor'] }})
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 border border-amber-300 shadow-sm">
                                                    ⚠️ Belum Lengkap ({{ $syarat['skor'] }})
                                                </span>
                                            @endif
                                        </div>

                                        {{-- 5 Syarat Badges --}}
                                        <div class="flex flex-wrap gap-1.5">
                                            {{-- 1. SK Pangkat --}}
                                            @if($syarat['sk_pangkat']['status'])
                                                <a href="{{ $syarat['sk_pangkat']['file_url'] }}" target="_blank" title="Lihat SK Pangkat"
                                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition">
                                                    ✓ SK Pangkat
                                                </a>
                                            @else
                                                <span title="Belum diunggah" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-600 border border-red-200">
                                                    ✗ SK Pangkat
                                                </span>
                                            @endif

                                            {{-- 2. SKP 2 Tahun --}}
                                            @if($syarat['skp_2_tahun']['status'])
                                                <a href="{{ route('pegawai.show', $row->id) }}#skp" target="_blank" title="Arsip SKP Lengkap (2 Tahun Terakhir)"
                                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition">
                                                    ✓ SKP ({{ $syarat['skp_2_tahun']['count'] }} Thn)
                                                </a>
                                            @else
                                                <a href="{{ route('pegawai.show', $row->id) }}#skp" target="_blank" title="Arsip SKP Belum 2 Tahun"
                                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition">
                                                    ✗ SKP ({{ $syarat['skp_2_tahun']['count'] }}/2)
                                                </a>
                                            @endif

                                            {{-- 3. KGB --}}
                                            @if($syarat['sk_kgb']['status'])
                                                <a href="{{ $syarat['sk_kgb']['file_url'] }}" target="_blank" title="Lihat Surat KGB"
                                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition">
                                                    ✓ KGB
                                                </a>
                                            @else
                                                <span title="Belum diunggah" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-600 border border-red-200">
                                                    ✗ KGB
                                                </span>
                                            @endif

                                            {{-- 4. KARPEG --}}
                                            @if($syarat['karpeg']['status'])
                                                <a href="{{ $syarat['karpeg']['file_url'] }}" target="_blank" title="Lihat Fotocopy KARPEG"
                                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition">
                                                    ✓ KARPEG
                                                </a>
                                            @else
                                                <span title="Fotocopy KARPEG belum diunggah" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-600 border border-red-200">
                                                    ✗ KARPEG
                                                </span>
                                            @endif

                                            {{-- 5. PAK (Dosen & PLP) --}}
                                            @if($syarat['is_wajib_pak'])
                                                @if($syarat['pak']['status'])
                                                    @if($syarat['pak']['file_url'])
                                                        <a href="{{ $syarat['pak']['file_url'] }}" target="_blank" title="Lihat Berkas PAK"
                                                           class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition">
                                                            ✓ PAK ({{ number_format((float)($syarat['pak']['angka_kredit'] ?? 0), 1) }})
                                                        </a>
                                                    @else
                                                        <span title="Nilai PAK ada: {{ $syarat['pak']['angka_kredit'] }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                            ✓ PAK ({{ number_format((float)($syarat['pak']['angka_kredit'] ?? 0), 1) }})
                                                        </span>
                                                    @endif
                                                @else
                                                    <span title="Dokumen/Nilai PAK belum ada" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-600 border border-red-200">
                                                        ✗ PAK
                                                    </span>
                                                @endif
                                            @else
                                                <span title="Bukan Dosen/PLP (Pelaksana/Staf)" class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-normal bg-slate-100 text-slate-500 border border-slate-200">
                                                    - PAK (N/A)
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <button 
                                        type="button"
                                        onclick='openKpModal(@json($row->id), @json($row->nama_lengkap ?? $row->nama), @json($syarat))'
                                        class="rounded-lg bg-green-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-green-700 active:scale-95 transition">
                                        Proses Naik Pangkat
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-enterprise.empty-state
                                        title="Belum Ada Usulan Kenaikan Pangkat"
                                        description="Tidak ada pegawai aktif yang memenuhi kriteria masa kerja pangkat (>= 4 tahun)."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-enterprise.data-table>
                </div>
            </x-enterprise.card>
        </div>
    </div>

    <!-- Modal Form Naik Pangkat Lengkap Dengan Ceklis Syarat -->
    <div id="kpModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl transition-all">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-lg font-bold text-gray-900" id="modalKpNama">Usulan Kenaikan Pangkat</h3>
                <button type="button" onclick="closeKpModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
            </div>

            {{-- Ringkasan Verifikasi Berkas --}}
            <div class="mt-4 rounded-xl bg-slate-50 p-4 border border-slate-200 space-y-2">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600 flex justify-between items-center">
                    <span>Verifikasi 5 Berkas Persyaratan:</span>
                    <span id="modalSkorSyarat" class="font-mono text-xs px-2 py-0.5 rounded-full font-bold"></span>
                </div>
                <div id="modalChecklistSyarat" class="space-y-1.5 text-xs">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

            <form id="kpForm" method="POST" action="" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Pilih Golongan Baru <span class="text-red-500">*</span></label>
                    <select name="golongan_baru_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">-- Pilih Golongan Baru --</option>
                        @foreach($golongans as $g)
                            <option value="{{ $g->id }}">{{ $g->nama_golongan }} - {{ $g->nama_pangkat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">TMT Pangkat Baru <span class="text-red-500">*</span></label>
                    <input type="date" name="tmt_pangkat_baru" required value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t">
                    <button type="button" onclick="closeKpModal()" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg">Batal</button>
                    <button type="submit" class="rounded-lg bg-green-600 px-5 py-2 text-sm font-bold text-white shadow hover:bg-green-700 active:scale-95 transition">
                        Konfirmasi & Update Pangkat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openKpModal(id, nama, syarat) {
            document.getElementById('modalKpNama').innerText = 'Naik Pangkat: ' + nama;
            document.getElementById('kpForm').action = '/kenaikan-pangkat/proses/' + id;

            // Render Checklist Syarat
            const checklistContainer = document.getElementById('modalChecklistSyarat');
            const skorBadge = document.getElementById('modalSkorSyarat');

            skorBadge.innerText = syarat.skor + ' Terpenuhi';
            if (syarat.is_lengkap) {
                skorBadge.className = 'font-mono text-xs px-2 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
            } else {
                skorBadge.className = 'font-mono text-xs px-2 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800 border border-amber-300';
            }

            let html = '';

            // 1. SK Pangkat
            html += `<div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                <span>1. Fotocopy SK Pangkat Terakhir</span>
                ${syarat.sk_pangkat.status ? `<a href="${syarat.sk_pangkat.file_url}" target="_blank" class="text-emerald-700 font-semibold hover:underline">✓ Ada (Lihat)</a>` : '<span class="text-red-500 font-semibold">✗ Belum Diunggah</span>'}
            </div>`;

            // 2. SKP 2 Tahun
            html += `<div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                <span>2. Fotocopy SKP 2 Tahun Terakhir</span>
                ${syarat.skp_2_tahun.status ? `<span class="text-emerald-700 font-semibold">✓ Ada (${syarat.skp_2_tahun.count} Tahun)</span>` : `<span class="text-red-500 font-semibold">✗ Belum Lengkap (${syarat.skp_2_tahun.count}/2)</span>`}
            </div>`;

            // 3. KGB
            html += `<div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                <span>3. Fotocopy Surat KGB Terakhir</span>
                ${syarat.sk_kgb.status ? `<a href="${syarat.sk_kgb.file_url}" target="_blank" class="text-emerald-700 font-semibold hover:underline">✓ Ada (Lihat)</a>` : '<span class="text-red-500 font-semibold">✗ Belum Diunggah</span>'}
            </div>`;

            // 4. KARPEG
            html += `<div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                <span>4. Fotocopy KARPEG</span>
                ${syarat.karpeg.status ? `<a href="${syarat.karpeg.file_url}" target="_blank" class="text-emerald-700 font-semibold hover:underline">✓ Ada (Lihat)</a>` : '<span class="text-red-500 font-semibold">✗ Belum Diunggah</span>'}
            </div>`;

            // 5. PAK
            if (syarat.is_wajib_pak) {
                html += `<div class="flex items-center justify-between py-1">
                    <span>5. Penetapan Angka Kredit (PAK)</span>
                    ${syarat.pak.status ? `${syarat.pak.file_url ? `<a href="${syarat.pak.file_url}" target="_blank" class="text-emerald-700 font-semibold hover:underline">✓ Ada (Lihat)</a>` : `<span class="text-emerald-700 font-semibold">✓ Nilai: ${syarat.pak.angka_kredit}</span>`}` : '<span class="text-red-500 font-semibold">✗ Wajib (Belum Ada)</span>'}
                </div>`;
            } else {
                html += `<div class="flex items-center justify-between py-1 text-slate-400">
                    <span>5. Penetapan Angka Kredit (PAK)</span>
                    <span>- Tidak Wajib (Pelaksana)</span>
                </div>`;
            }

            checklistContainer.innerHTML = html;
            document.getElementById('kpModal').classList.remove('hidden');
        }

        function closeKpModal() {
            document.getElementById('kpModal').classList.add('hidden');
        }
    </script>
</x-app-layout>