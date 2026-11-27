<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>📖</span> Buku Kendali Cuti Pegawai (Self-Service)
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Catatan Saldo & Rekapitulasi Riwayat Transaksi Cuti Mandiri
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('pengajuan-cuti.buku-cuti.pdf', ['pegawai_id' => $pegawai->id, 'tahun' => $tahun]) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                    <span>📄</span> Unduh Buku Cuti (PDF)
                </a>
                <a href="{{ route('pengajuan-cuti.index') }}"
                   class="inline-flex items-center px-3.5 py-2 border border-gray-300 shadow-xs text-xs font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                    ← Kembali ke E-Cuti
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            {{-- KARTU PROFIL PEGAWAI & PILIHAN TAHUN --}}
            @php
                $isBukuPppk = $pegawai->isPppk();
            @endphp
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img class="h-14 w-14 rounded-full object-cover border-2 border-emerald-500 shadow-xs"
                         src="{{ $pegawai->foto_url }}" alt="{{ $pegawai->nama }}">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-bold text-gray-900">{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $isBukuPppk ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                {{ $pegawai->status_asn_cuti }} — {{ $isBukuPppk ? '4 Jenis Hak Cuti (Perbkn No. 7 Tahun 2022)' : '7 Jenis Hak Cuti ASN' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 font-mono">NIP. {{ $pegawai->nip ?? '-' }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $pegawai->jabatan_tampil }} — {{ $pegawai->unit_kerja_tampil }}</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('pengajuan-cuti.buku-cuti') }}" class="flex flex-wrap items-center gap-2">
                    @if(($canSelectPegawai ?? false) && isset($pegawaiList) && $pegawaiList->isNotEmpty())
                        <label class="text-xs font-bold text-gray-700 uppercase">Pegawai:</label>
                        <select name="pegawai_id" onchange="this.form.submit()"
                                class="rounded-lg border-gray-300 text-xs focus:ring-emerald-500 focus:border-emerald-500 font-semibold py-1.5 max-w-xs">
                            @foreach($pegawaiList as $pItem)
                                <option value="{{ $pItem->id }}" @selected($pegawai->id == $pItem->id)>
                                    [{{ $pItem->status_asn_cuti }}] {{ $pItem->nama_lengkap ?? $pItem->nama }}
                                </option>
                            @endforeach
                        </select>
                    @elseif(request()->filled('pegawai_id'))
                        <input type="hidden" name="pegawai_id" value="{{ $pegawai->id }}">
                    @endif
                    <label class="text-xs font-bold text-gray-700 uppercase">Tahun:</label>
                    <select name="tahun" onchange="this.form.submit()"
                            class="rounded-lg border-gray-300 text-xs focus:ring-emerald-500 focus:border-emerald-500 font-semibold py-1.5">
                        @for($y = now()->year + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" @selected($tahun == $y)>Tahun {{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>

            {{-- PANEL RINGKASAN SYARAT, KETENTUAN & PEMISAHAN HAK CUTI PNS vs PPPK --}}
            @include('pengajuan-cuti._ringkasan_regulasi_bkn', ['defaultOpen' => false])

            {{-- KARTU SALDO 3 TAHUN (N, N-1, N-2) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- N-2 --}}
                <div class="bg-blue-50/70 rounded-xl shadow-xs border border-blue-200 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-900 uppercase tracking-wider">Tahun {{ $tahun - 2 }} (N-2)</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">2 Tahun Lalu</span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-blue-950 font-mono">
                        {{ $catatanCuti['N-2']['sisa_formatted'] }}
                    </div>
                    <p class="text-[11px] text-blue-700 mt-1 leading-relaxed">
                        Sisa kuota cuti tahun {{ $tahun - 2 }} yang belum digunakan.
                    </p>
                </div>

                {{-- N-1 --}}
                <div class="bg-indigo-50/70 rounded-xl shadow-xs border border-indigo-200 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-indigo-900 uppercase tracking-wider">Tahun {{ $tahun - 1 }} (N-1)</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">1 Tahun Lalu</span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-indigo-950 font-mono">
                        {{ $catatanCuti['N-1']['sisa_formatted'] }}
                    </div>
                    <p class="text-[11px] text-indigo-700 mt-1 leading-relaxed">
                        Sisa kuota cuti tahun {{ $tahun - 1 }} yang dapat diakumulasikan.
                    </p>
                </div>

                {{-- N --}}
                <div class="bg-emerald-50/80 rounded-xl shadow-xs border border-emerald-300 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Tahun {{ $tahun }} (N)</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 text-emerald-900">Tahun Berjalan</span>
                    </div>
                    <div class="mt-2 text-3xl font-black text-emerald-950 font-mono">
                        {{ $catatanCuti['N']['sisa_formatted'] }}
                    </div>
                    <p class="text-[11px] text-emerald-700 mt-1 leading-relaxed">
                        Sisa hak kuota cuti tahun berjalan (dari total 12 hari kerja).
                    </p>
                </div>
            </div>

            {{-- BUKU KENDALI CUTI (LEAVE LEDGER) --}}
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                        <span>📋</span> Riwayat Transaksi Cuti Tahun {{ $tahun }}
                    </h3>
                    <span class="text-xs text-gray-500">Total Transaksi: <strong>{{ method_exists($riwayatCuti, 'total') ? $riwayatCuti->total() : $riwayatCuti->count() }}</strong></span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-gray-600">
                        <thead class="text-[11px] uppercase bg-gray-50 text-gray-700 border-b border-gray-200 font-bold">
                            <tr>
                                <th class="py-3 px-3 text-center w-10">No</th>
                                <th class="py-3 px-3">Jenis Cuti & No. Surat</th>
                                <th class="py-3 px-3">Tanggal Pelaksanaan</th>
                                <th class="py-3 px-3 text-center">Durasi</th>
                                <th class="py-3 px-3">Alasan Cuti</th>
                                <th class="py-3 px-3 text-center">Status</th>
                                <th class="py-3 px-3 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($riwayatCuti as $row)
                                <tr class="hover:bg-gray-50/70 transition">
                                    <td class="py-2.5 px-3 text-center font-mono text-gray-400">{{ method_exists($riwayatCuti, 'currentPage') ? (($riwayatCuti->currentPage() - 1) * $riwayatCuti->perPage() + $loop->iteration) : $loop->iteration }}</td>
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-gray-900">{{ $row->jenis_cuti }}</div>
                                        <div class="text-[10px] text-gray-500 font-mono">{{ $row->nomor_surat ?: '[Belum Terbit]' }}</div>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        {{ $row->tanggal_mulai ? $row->tanggal_mulai->format('d/m/Y') : '-' }} s.d.
                                        {{ $row->tanggal_selesai ? $row->tanggal_selesai->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-gray-900">
                                        {{ $row->jumlah_hari }} Hari
                                    </td>
                                    <td class="py-2.5 px-3 max-w-xs truncate" title="{{ $row->alasan }}">
                                        {{ $row->alasan }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $row->status_badge_class }}">
                                            {{ $row->status }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <a href="{{ route('pengajuan-cuti.show', $row->id) }}"
                                           class="inline-flex items-center px-2 py-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold text-[11px] transition">
                                            🔍 Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 italic">
                                        Belum ada catatan transaksi cuti pada tahun {{ $tahun }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($riwayatCuti, 'links') && $riwayatCuti->total() > 0)
                    <div class="p-4 border-t border-gray-100">
                        {{ $riwayatCuti->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
