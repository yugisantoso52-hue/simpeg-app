<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Formulir Pengajuan {{ $jenis === 'KP' ? 'Kenaikan Pangkat (PNS)' : 'Kenaikan Gaji Berkala (KGB)' }}"
            subtitle="Pastikan data dan dokumen pendukung sesuai dengan peraturan BKN dan KemenPAN-RB"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">

            {{-- Kartu Identitas Pegawai Pengusul --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <span>👤</span> Data Identitas Pegawai Pengusul
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $jenis === 'KP' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        Usulan {{ $jenis }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Nama Lengkap:</span>
                        <strong class="text-slate-900 text-sm">{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">NIP / NI PPPK:</span>
                        <strong class="text-slate-900 font-mono">{{ $pegawai->nip }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Jabatan & Unit Kerja:</span>
                        <span class="text-slate-800">{{ $pegawai->jabatan?->nama_jabatan ?? '-' }} / {{ $pegawai->unitKerja?->nama_unit ?? 'Fakultas Keperawatan' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Pangkat / Golongan Saat Ini:</span>
                        <span class="text-slate-800 font-semibold">{{ $pegawai->golongan?->nama_pangkat ?? '-' }} ({{ $pegawai->golongan?->nama_golongan ?? '-' }})</span>
                    </div>
                </div>
            </div>

            {{-- FORM SUBMIT --}}
            <form action="{{ route('pengajuan-karir.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="jenis_pengajuan" value="{{ $jenis }}">

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">

                    @if($jenis === 'KP')
                        {{-- Komponen Terisolasi Form Input Kenaikan Pangkat (KP) --}}
                        <x-karir.form-kp-inputs
                            :pegawai="$pegawai"
                            :golongans="$golongans"
                            :periodeKpList="$periodeKpList"
                        />
                    @else
                        {{-- Komponen Terisolasi Form Input Kenaikan Gaji Berkala (KGB) --}}
                        <x-karir.form-kgb-inputs
                            :pegawai="$pegawai"
                        />
                    @endif

                    {{-- Catatan Pegawai --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan Pengusul</label>
                        <textarea name="catatan_pegawai" rows="3" placeholder="Tuliskan keterangan bila ada (misal: telah memenuhi masa kerja 2 tahun untuk KGB, atau kelengkapan PAK telah diverifikasi di Sister/SIASN)" class="w-full rounded-lg border-slate-300 text-xs focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    {{-- Alur Paraf & Penandatanganan Informasi --}}
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-200 text-xs text-slate-600 space-y-1.5">
                        <div class="font-bold text-slate-800">Alur Verifikasi Administratif Surat Resmi:</div>
                        <ol class="list-decimal list-inside space-y-1 text-[11px]">
                            <li><strong>Pegawai</strong> submit formulir dan berkas pengajuan secara mandiri.</li>
                            <li><strong>Ka Pokja Keu-Kepeg</strong> memverifikasi kelengkapan berkas fisik & administrasi kepegawaian.</li>
                            <li><strong>Kepala Bagian Umum</strong> memverifikasi koordinasi tata naskah & membubuhkan paraf administrasi.</li>
                            <li><strong>Wakil Dekan Bidang Keuangan dan Umum (WD II)</strong> menyetujui dan menandatangani surat resmi.</li>
                        </ol>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t">
                        <a href="{{ route('pengajuan-karir.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-md transition {{ $jenis === 'KP' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-amber-600 hover:bg-amber-700' }}">
                            🚀 Kirim Usulan {{ $jenis }}
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</x-app-layout>
