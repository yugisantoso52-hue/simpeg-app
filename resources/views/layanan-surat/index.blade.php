<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Layanan E-Surat Mandiri"
            subtitle="Penerbitan Surat Keterangan Aktif Bekerja Mandiri Resmi ber-QR Code untuk Dosen & Tendik"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="rounded-xl border border-emerald-300 bg-emerald-50 px-5 py-4 text-emerald-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="text-emerald-700 font-bold hover:text-emerald-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-red-300 bg-red-50 px-5 py-4 text-red-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="text-red-700 font-bold hover:text-red-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- 1. FORM PENGAJUAN / PENERBITAN SURAT MANDIRI --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2.5 bg-blue-100 text-blue-700 rounded-lg text-xl">
                            📄
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Cetak Surat Keterangan Aktif Bekerja (Instan)</h3>
                            <p class="text-xs text-slate-500">
                                Diterbitkan secara elektronik dengan verifikasi QR Code resmi Fakultas Keperawatan Universitas Riau.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('layanan-surat.surat-aktif.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Pegawai Pemohon
                                </label>
                                @if($isExecutiveOrAdmin)
                                    <select name="pegawai_id" class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">-- Diri Sendiri ({{ Auth::user()->name }}) --</option>
                                        @foreach(\App\Models\Pegawai::where('status_pegawai', 'Aktif')->orderBy('nama')->get() as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama }} (NIP. {{ $p->nip }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-[11px] text-slate-400 mt-1">Sebagai Admin/Pimpinan, Anda dapat mencetak untuk diri sendiri atau pegawai lain.</p>
                                @else
                                    <input type="text" disabled 
                                           value="{{ $myPegawai ? ($myPegawai->nama_lengkap ?? $myPegawai->nama) . ' (NIP: ' . $myPegawai->nip . ')' : Auth::user()->name }}"
                                           class="w-full text-sm bg-slate-100 rounded-lg border-slate-300 text-slate-600">
                                @endif
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Nomor Surat <span class="text-xs font-normal text-slate-500">(Isi Manual)</span>
                                </label>
                                <div class="flex rounded-lg shadow-sm">
                                    <input type="text" name="nomor_surat_manual" 
                                           value="{{ old('nomor_surat_manual', $nextNomorPrefix ?? 'B/0001') }}"
                                           placeholder="Contoh: B/0001"
                                           class="flex-1 min-w-0 block w-full px-3 py-2 text-sm font-mono rounded-none rounded-l-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="inline-flex items-center px-2.5 rounded-r-lg border border-l-0 border-slate-300 bg-slate-100 text-slate-600 text-xs font-mono font-semibold select-none">
                                        /UN19.5.1.1.10/KP/{{ date('Y') }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Format: <em>(isi manual)/UN19.5.1.1.10/KP/{{ date('Y') }}</em></p>
                            </div>

                            <div class="md:col-span-2 lg:col-span-1">
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Keperluan Surat <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="keperluan" required
                                       placeholder="Contoh: Pengajuan KPR Bank / Persyaratan Beasiswa"
                                       class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-[11px] text-slate-400 mt-1">Tuliskan instansi atau tujuan penggunaan surat secara spesifik.</p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Terbitkan Surat Keterangan Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </x-enterprise.card>

            {{-- 2. DAFTAR RIWAYAT SURAT KETERANGAN --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Riwayat E-Surat Keterangan Diterbitkan</h3>
                            <p class="text-xs text-slate-500">Arsip surat digital yang telah diterbitkan dan dapat diunduh ulang sewaktu-waktu.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase">
                                <tr>
                                    <th class="px-4 py-3">Nomor Surat</th>
                                    <th class="px-4 py-3">Nama Pegawai & NIP</th>
                                    <th class="px-4 py-3">Keperluan</th>
                                    <th class="px-4 py-3 text-center">Tanggal Terbit</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi Unduh</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($surats as $item)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-800">
                                            {{ $item->nomor_surat }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-slate-900">{{ $item->pegawai->nama_lengkap ?? $item->pegawai->nama }}</div>
                                            <div class="text-xs text-slate-500">NIP: {{ $item->pegawai->nip }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-700 max-w-xs truncate" title="{{ $item->keperluan }}">
                                            {{ $item->keperluan }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-slate-600 text-xs font-medium">
                                            {{ $item->tanggal_surat ? $item->tanggal_surat->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                ● Sah & Aktif
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('layanan-surat.surat-aktif.cetak', $item->id) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                    Cetak PDF
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-sm">
                                            Belum ada arsip surat keterangan yang diterbitkan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($surats->hasPages())
                        <div class="mt-4">
                            {{ $surats->links() }}
                        </div>
                    @endif
                </div>
            </x-enterprise.card>

        </div>
    </div>
</x-app-layout>
